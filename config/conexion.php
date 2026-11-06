<?php
$dbHost = '127.0.0.1';
$dbName = 'biblioteca';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    exit('No se pudo conectar con MySQL. Verifica config/conexion.php y que la base biblioteca exista.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function require_login(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Solicitud inválida. Recarga la página e inténtalo nuevamente.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        $item = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $type = $item['type'] === 'success' ? 'success' : 'danger';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">' . e($item['message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>';
    }
}

function nav(string $current): void
{
    $links = [
        'index.php' => ['Inicio', 'bi-house'],
        'libros.php' => ['Libros', 'bi-book'],
        'usuarios.php' => ['Usuarios', 'bi-people'],
        'prestamos.php' => ['Préstamos', 'bi-arrow-left-right'],
        'devoluciones.php' => ['Devoluciones', 'bi-arrow-counterclockwise'],
        'historial.php' => ['Historial', 'bi-clock-history'],
        'atrasos.php' => ['Atrasos', 'bi-exclamation-triangle'],
        'admins.php' => ['Administradores', 'bi-shield-lock'],
    ];
    echo '<div class="topbar"><a class="brand" href="index.php"><i class="bi bi-book-half"></i> Biblioteca</a><div class="navlinks">';
    foreach ($links as $href => [$label, $icon]) {
        echo '<a class="nav-item ' . ($current === $href ? 'active' : '') . '" href="' . e($href) . '"><i class="bi ' . e($icon) . '"></i><span>' . e($label) . '</span></a>';
    }
    echo '</div><div class="account"><span>' . e($_SESSION['admin_name'] ?? 'Administrador') . '</span><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '"><button class="btn btn-sm btn-outline-light" type="submit">Salir</button></form></div></div>';
}

function page_start(string $title, string $current, bool $authenticated = true): void
{
    if ($authenticated) {
        require_login();
    }
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . e($title) . ' | Biblioteca</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="assets/css/style.css" rel="stylesheet"></head><body>';
    if ($authenticated) {
        nav($current);
    }
    echo '<main class="container-fluid page-wrap">';
    if ($authenticated) {
        show_flash();
    }
}

function page_end(): void
{
    echo '</main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>';
}

function post_only(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método no permitido.');
    }
    verify_csrf();
}

function valid_date(string $date): bool
{
    $parts = explode('-', $date);
    return count($parts) === 3 && checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}
