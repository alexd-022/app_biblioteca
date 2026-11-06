<?php
const DB_HOST = '127.0.0.1';
const DB_NAME = 'biblioteca';
const DB_USER = 'root';
const DB_PASS = '';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    exit('No se pudo conectar con MySQL. Revisa las credenciales de admin/implements/conexion.php y la base biblioteca.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function escapar($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function tokenCsrf(): string
{
    if (empty($_SESSION['token_csrf'])) {
        $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['token_csrf'];
}

function validarCsrf(): void
{
    $token = $_POST['token_csrf'] ?? '';
    if (!is_string($token) || !hash_equals(tokenCsrf(), $token)) {
        http_response_code(400);
        exit('Solicitud inválida. Recarga la página e inténtalo de nuevo.');
    }
}

function validarPost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método no permitido.');
    }
    validarCsrf();
}

function exigirSesion(): void
{
    if (empty($_SESSION['usuario']['id_usuario'])) {
        header('Location: /app_biblioteca/log-in.php');
        exit;
    }
}

function exigirAdmin(): void
{
    exigirSesion();
    if (($_SESSION['usuario']['rol'] ?? '') !== 'Admin') {
        http_response_code(403);
        exit('No tienes permisos para acceder a esta sección.');
    }
}

function mensaje(string $tipo, string $texto): void
{
    $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $texto];
}

function mostrarMensaje(): void
{
    if (!empty($_SESSION['mensaje'])) {
        $item = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
        $tipo = $item['tipo'] === 'ok' ? 'success' : 'danger';
        echo '<div class="alert alert-' . $tipo . ' alert-dismissible fade show" role="alert">' . escapar($item['texto']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>';
    }
}

function enlaceActivo(string $pagina): string
{
    return basename($_SERVER['PHP_SELF']) === $pagina ? 'active' : '';
}

function obtenerEstiloPagina(): string
{
    $ruta = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $archivo = basename($ruta);
    $mapa = [
        '/index.php' => 'inicio.css',
        '/log-in.php' => 'login.css',
        '/registrarse.php' => 'registro.css',
        '/admin/dashboard.php' => 'dashboard.css',
        '/admin/libros/listado.php' => 'libros.css',
        '/admin/usuarios/listado.php' => 'usuarios.css',
        '/admin/usuarios/crear-usuario.php' => 'usuarios.css',
        '/admin/usuarios/mi-cuenta.php' => 'mi-cuenta.css',
        '/admin/prestamos/listado.php' => 'prestamos.css',
        '/admin/prestamos/devoluciones.php' => 'devoluciones.css',
        '/admin/prestamos/historial.php' => 'historial.css',
        '/admin/prestamos/atrasos.php' => 'atrasos.css',
    ];
    foreach ($mapa as $sufijo => $estilo) {
        if (str_ends_with($ruta, $sufijo)) {
            return $estilo;
        }
    }
    return $archivo === 'mi-cuenta.php' ? 'mi-cuenta.css' : 'dashboard.css';
}

function iniciarPagina(string $titulo): void
{
    $estiloPagina = obtenerEstiloPagina();
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . escapar($titulo) . ' | Biblioteca</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="/app_biblioteca/css/estilos.css" rel="stylesheet"><link href="/app_biblioteca/css/' . escapar($estiloPagina) . '" rel="stylesheet"></head><body>';
}

function barraNavegacion(string $actual): void
{
    echo '<nav class="navbar navbar-expand-xl navbar-dark barra"><div class="container-fluid"><a class="navbar-brand" href="/app_biblioteca/admin/dashboard.php"><i class="bi bi-book-half"></i> Biblioteca</a><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuBiblioteca" aria-controls="menuBiblioteca" aria-expanded="false" aria-label="Abrir menú"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="menuBiblioteca"><ul class="navbar-nav me-auto">';
    $enlaces = [
        'dashboard.php' => ['Inicio', '/app_biblioteca/admin/dashboard.php', 'bi-house'],
        'listado.php' => ['Libros', '/app_biblioteca/admin/libros/listado.php', 'bi-book'],
        'usuarios-listado.php' => ['Usuarios', '/app_biblioteca/admin/usuarios/listado.php', 'bi-people'],
        'prestamos-listado.php' => ['Préstamos', '/app_biblioteca/admin/prestamos/listado.php', 'bi-arrow-left-right'],
        'devoluciones.php' => ['Devoluciones', '/app_biblioteca/admin/prestamos/devoluciones.php', 'bi-arrow-counterclockwise'],
        'historial.php' => ['Historial', '/app_biblioteca/admin/prestamos/historial.php', 'bi-clock-history'],
        'atrasos.php' => ['Atrasos', '/app_biblioteca/admin/prestamos/atrasos.php', 'bi-exclamation-triangle'],
    ];
    foreach ($enlaces as $key => [$nombre, $url, $icono]) {
        $clase = $actual === $key ? ' active' : '';
        echo '<li class="nav-item"><a class="nav-link' . $clase . '" href="' . $url . '"><i class="bi ' . $icono . '"></i> ' . $nombre . '</a></li>';
    }
    echo '</ul><span class="navbar-text me-3">' . escapar($_SESSION['usuario']['nombre'] ?? '') . ' · ' . escapar($_SESSION['usuario']['rol'] ?? '') . '</span><form action="/app_biblioteca/admin/login/log-out.php" method="post"><input type="hidden" name="token_csrf" value="' . escapar(tokenCsrf()) . '"><button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button></form></div></div></nav><main class="container-fluid contenido">';
    mostrarMensaje();
}

function finPagina(): void
{
    echo '</main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>';
}

function fechaValida(string $fecha): bool
{
    $partes = explode('-', $fecha);
    return count($partes) === 3 && checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0]);
}
