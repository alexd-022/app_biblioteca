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
<header class="portada"><div class="portada-contenido"><span class="sobrelinea">GESTIÓN BIBLIOTECARIA</span><h1>Biblioteca</h1><p>Consulta y organiza libros, usuarios, préstamos y devoluciones desde un solo lugar.</p><div class="d-flex gap-2 flex-wrap"><a class="btn btn-primary btn-lg" href="/app_biblioteca/log-in.php">Iniciar sesión</a><a class="btn btn-outline-primary btn-lg" href="/app_biblioteca/registrarse.php">Crear cuenta</a></div></div><div class="portada-icono"><i class="bi bi-book-half"></i></div></header>
<section class="row g-3 mt-4"><div class="col-md-4"><article class="panel h-100"><i class="bi bi-journal-bookmark fs-2 text-primary"></i><h2 class="mt-3">Catálogo organizado</h2><p class="mb-0 text-secondary">Administra títulos, autores, categorías y disponibilidad.</p></article></div><div class="col-md-4"><article class="panel h-100"><i class="bi bi-arrow-left-right fs-2 text-success"></i><h2 class="mt-3">Préstamos y devoluciones</h2><p class="mb-0 text-secondary">Registra movimientos con actualización automática del estado del libro.</p></article></div><div class="col-md-4"><article class="panel h-100"><i class="bi bi-calendar2-week fs-2 text-danger"></i><h2 class="mt-3">Control de atrasos</h2><p class="mb-0 text-secondary">Identifica préstamos activos que superaron su fecha límite.</p></article></div></section>
<?php finPagina(); ?>
