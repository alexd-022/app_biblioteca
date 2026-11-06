<?php
require_once __DIR__ . '/admin/implements/conexion.php';
if (!empty($_SESSION['usuario']['id_usuario'])) {
    header('Location: ' . ($_SESSION['usuario']['rol'] === 'Admin' ? '/app_biblioteca/admin/dashboard.php' : '/app_biblioteca/admin/usuarios/mi-cuenta.php'));
    exit;
}
header('Location: /app_biblioteca/log-in.php');
exit;
