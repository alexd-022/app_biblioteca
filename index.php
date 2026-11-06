<?php
require_once __DIR__ . '/admin/implements/conexion.php';

if (!empty($_SESSION['usuario']['id_usuario'])) {
    $destino = ($_SESSION['usuario']['rol'] ?? '') === 'Admin'
        ? '/app_biblioteca/admin/dashboard.php'
        : '/app_biblioteca/admin/usuarios/mi-cuenta.php';
    header('Location: ' . $destino);
    exit;
}

iniciarPagina('Biblioteca');
?>
<main class="inicio">
    <section class="portada">
        <p class="sobrelinea">GESTIÓN BIBLIOTECARIA</p>
        <h1>Biblioteca</h1>
        <p class="descripcion">Consulta y organiza libros, usuarios, préstamos y devoluciones desde un solo lugar.</p>
        <div class="botones-inicio">
            <a class="btn btn-primary" href="/app_biblioteca/log-in.php">Iniciar sesión</a>
            <a class="btn btn-outline-secondary" href="/app_biblioteca/registrarse.php">Crear cuenta</a>
        </div>
    </section>

    <section class="funciones-inicio">
        <article class="funcion"><h2>Catálogo organizado</h2><p>Administra títulos, autores, categorías y disponibilidad.</p></article>
        <article class="funcion"><h2>Préstamos y devoluciones</h2><p>Registra movimientos y mantén actualizado el estado de cada libro.</p></article>
        <article class="funcion"><h2>Control de atrasos</h2><p>Identifica préstamos activos que superaron su fecha límite.</p></article>
    </section>
</main>
<?php finPagina(); ?>
