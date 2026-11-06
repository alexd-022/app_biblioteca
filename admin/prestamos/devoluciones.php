<?php
require_once __DIR__ . '/../../admin/implements/conexion.php';
require_once __DIR__ . '/funciones.php';
exigirAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarPost();
    $idPrestamo = (int)($_POST['id_prestamo'] ?? 0);

    try {
        $pdo->beginTransaction();
        $consulta = $pdo->prepare("SELECT id_libro FROM prestamos WHERE id_prestamo = ? AND estado_prestamo = 'Activo' FOR UPDATE");
        $consulta->execute([$idPrestamo]);
        $prestamo = $consulta->fetch();

        if (!$prestamo) {
            throw new RuntimeException('El préstamo ya fue devuelto o no existe.');
        }

        $actualizarPrestamo = $pdo->prepare("UPDATE prestamos SET estado_prestamo = 'Devuelto' WHERE id_prestamo = ? AND estado_prestamo = 'Activo'");
        $actualizarPrestamo->execute([$idPrestamo]);
        if ($actualizarPrestamo->rowCount() !== 1) {
            throw new RuntimeException('No se pudo actualizar el estado del préstamo. Actualiza la página e inténtalo nuevamente.');
        }

        $actualizarLibro = $pdo->prepare("UPDATE libros SET estado = 'Disponible' WHERE id_libro = ?");
        $actualizarLibro->execute([(int)$prestamo['id_libro']]);

        $pdo->commit();
        mensaje('ok', 'Devolución registrada. El préstamo quedó como Devuelto y el libro está Disponible.');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        mensaje('error', $exception instanceof RuntimeException ? $exception->getMessage() : 'No se pudo registrar la devolución.');
    }

    header('Location: devoluciones.php');
    exit;
}

$prestamos = obtenerActivos();
iniciarPagina('Devoluciones');
barraNavegacion('devoluciones.php');
?>
<div class="encabezado-pagina">
    <div><span class="sobrelinea">CIRCULACIÓN</span><h1>Devoluciones</h1><p>Registra la devolución de préstamos activos.</p></div>
</div>
<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Usuario</th><th>Libro</th><th>Fecha préstamo</th><th>Fecha límite</th><th>Estado</th><th>Acción</th></tr></thead>
            <tbody>
            <?php foreach ($prestamos as $prestamo): ?>
                <tr>
                    <td><?= escapar($prestamo['usuario']); ?></td>
                    <td><?= escapar($prestamo['libro']); ?></td>
                    <td><?= escapar($prestamo['fecha_prestamo']); ?></td>
                    <td><?= escapar($prestamo['fecha_devolucion']); ?></td>
                    <td><span class="badge text-bg-<?= $prestamo['estado_vista'] === 'Atrasado' ? 'danger' : 'warning'; ?>"><?= escapar($prestamo['estado_vista']); ?></span></td>
                    <td>
                        <form method="post" onsubmit="return confirm('¿Registrar la devolución de este libro?')">
                            <input type="hidden" name="token_csrf" value="<?= escapar(tokenCsrf()); ?>">
                            <input type="hidden" name="id_prestamo" value="<?= (int)$prestamo['id_prestamo']; ?>">
                            <button class="btn btn-success btn-sm" type="submit">Registrar devolución</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$prestamos): ?><tr><td colspan="6" class="text-center">No hay préstamos activos.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php finPagina(); ?>
