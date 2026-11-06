<?php
require_once __DIR__ . '/../implements/conexion.php';
validarPost();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $cookies = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookies['path'], $cookies['domain'], $cookies['secure'], $cookies['httponly']);
}
session_destroy();
header('Location: /app_biblioteca/log-in.php');
exit;
