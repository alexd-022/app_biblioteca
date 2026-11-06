<?php
require_once __DIR__ . '/../../admin/implements/conexion.php';
require_once __DIR__ . '/funciones.php';
exigirAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarPost();
    $id = (int)($_POST['id_usuario'] ?? 0);

    if (($_POST['accion'] ?? '') === 'eliminar') {
        if ($id === (int)$_SESSION['usuario']['id_usuario']) {
            mensaje('error', 'No puedes eliminar tu propia cuenta.');
        } else {
            $totalAdmins = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='Admin'")->fetchColumn();
            $usuario = obtenerUsuarioPorId($id);
            if ($usuario && $usuario['rol'] === 'Admin' && $totalAdmins <= 1) {
                mensaje('error', 'Debe permanecer al menos un administrador.');
            } else {
                try {
                    if (eliminarUsuario($id)) {
                        mensaje('ok', 'Usuario eliminado. Su historial de préstamos devueltos también se eliminó.');
                    } else {
                        mensaje('error', 'No se puede eliminar el usuario porque todavía tiene préstamos activos. Registra primero las devoluciones pendientes.');
                    }
                } catch (Throwable $exception) {
                    mensaje('error', 'No se pudo eliminar el usuario. Revisa si tiene registros relacionados.');
                }
            }
        }
        header('Location: listado.php');
        exit;
    }

    $accion = $_POST['accion'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $rol = $_POST['rol'] ?? 'Usuario';
    $clave = $_POST['clave'] ?? '';

    if ($nombre === '' || $documento === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || !in_array($rol, ['Usuario', 'Admin'], true)) {
        mensaje('error', 'Completa los datos con valores válidos.');
    } else {
        try {
            if ($accion === 'crear') {
                if (strlen($clave) < 8) {
                    throw new RuntimeException('La contraseña debe tener al menos 8 caracteres.');
                }
                crearUsuario($nombre, $documento, $correo, $clave, $rol);
                mensaje('ok', 'Usuario creado correctamente.');
            } elseif ($accion === 'editar' && $id > 0) {
                if ($id === (int)$_SESSION['usuario']['id_usuario'] && $rol !== 'Admin') {
                    throw new RuntimeException('No puedes quitarte el rol de administrador.');
                }
                if ($clave !== '' && strlen($clave) < 8) {
                    throw new RuntimeException('La contraseña debe tener al menos 8 caracteres.');
                }
                actualizarUsuario($id, $nombre, $documento, $correo, $rol, $clave === '' ? null : $clave);
                if ($id === (int)$_SESSION['usuario']['id_usuario']) {
                    $_SESSION['usuario']['nombre'] = $nombre;
                }
                mensaje('ok', 'Usuario actualizado correctamente.');
            }
        } catch (Throwable $exception) {
            mensaje('error', $exception instanceof RuntimeException ? $exception->getMessage() : ($exception->getCode() === '23000' ? 'El documento o correo ya está registrado.' : 'No se pudo guardar el usuario.'));
        }
    }

    header('Location: listado.php');
    exit;
}

$busqueda = trim($_GET['buscar'] ?? '');
$usuarios = obtenerUsuarios($busqueda);
$editar = isset($_GET['editar']) ? obtenerUsuarioPorId((int)$_GET['editar']) : null;
iniciarPagina('Administrar usuarios');
barraNavegacion('usuarios-listado.php');
?>
<div class="encabezado-pagina"><div><span class="sobrelinea">GESTIÓN DE CUENTAS</span><h1>Usuarios</h1><p>Administra usuarios y asigna roles desde un solo lugar.</p></div><a class="btn btn-primary" href="crear-usuario.php">Crear usuario</a></div>
<?php if ($editar): ?>
<section class="panel mb-4"><h2>Editar usuario</h2><form method="post" action="listado.php" class="row g-3"><input type="hidden" name="token_csrf" value="<?= escapar(tokenCsrf()); ?>"><input type="hidden" name="accion" value="editar"><input type="hidden" name="id_usuario" value="<?= (int)$editar['id_usuario']; ?>"><div class="col-md-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" maxlength="100" required value="<?= escapar($editar['nombre']); ?>"></div><div class="col-md-2"><label class="form-label">Documento</label><input class="form-control" name="documento" maxlength="30" required value="<?= escapar($editar['documento']); ?>"></div><div class="col-md-3"><label class="form-label">Correo</label><input class="form-control" type="email" name="correo" required value="<?= escapar($editar['correo']); ?>"></div><div class="col-md-2"><label class="form-label">Rol</label><select class="form-select" name="rol"><option <?= $editar['rol'] === 'Usuario' ? 'selected' : ''; ?>>Usuario</option><option <?= $editar['rol'] === 'Admin' ? 'selected' : ''; ?>>Admin</option></select></div><div class="col-md-2"><label class="form-label">Nueva clave</label><input class="form-control" type="password" name="clave" minlength="8" autocomplete="new-password"></div><div class="col-12"><button class="btn btn-primary">Guardar cambios</button> <a class="btn btn-outline-secondary" href="listado.php">Cancelar</a></div></form></section>
<?php endif; ?>
<section class="panel"><form method="get" class="row g-2 mb-3"><div class="col-md-8"><input class="form-control" name="buscar" placeholder="Buscar nombre, documento o correo" value="<?= escapar($busqueda); ?>"></div><div class="col-md-4"><button class="btn btn-outline-primary">Buscar</button> <a class="btn btn-outline-secondary" href="listado.php">Limpiar</a></div></form><div class="table-responsive"><table class="table align-middle"><thead><tr><th>ID</th><th>Nombre</th><th>Documento</th><th>Correo</th><th>Rol</th><th>Acciones</th></tr></thead><tbody><?php foreach ($usuarios as $usuario): ?><tr><td><?= (int)$usuario['id_usuario']; ?></td><td><?= escapar($usuario['nombre']); ?></td><td><?= escapar($usuario['documento']); ?></td><td><?= escapar($usuario['correo']); ?></td><td><span class="badge text-bg-<?= $usuario['rol'] === 'Admin' ? 'dark' : 'secondary'; ?>"><?= escapar($usuario['rol']); ?></span></td><td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="listado.php?editar=<?= (int)$usuario['id_usuario']; ?>">Editar</a><form method="post" onsubmit="return confirm('¿Eliminar este usuario? Los préstamos devueltos también se borrarán.')"><input type="hidden" name="token_csrf" value="<?= escapar(tokenCsrf()); ?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id_usuario" value="<?= (int)$usuario['id_usuario']; ?>"><button class="btn btn-sm btn-outline-danger">Eliminar</button></form></td></tr><?php endforeach; ?><?php if (!$usuarios): ?><tr><td colspan="6" class="text-center">No hay usuarios registrados.</td></tr><?php endif; ?></tbody></table></div></section>
<?php finPagina(); ?>
