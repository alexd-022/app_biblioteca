<?php
require_once __DIR__ . '/admin/implements/conexion.php';
if (!empty($_SESSION['usuario']['id_usuario'])) { header('Location: ' . ($_SESSION['usuario']['rol'] === 'Admin' ? '/app_biblioteca/admin/dashboard.php' : '/app_biblioteca/admin/usuarios/mi-cuenta.php')); exit; }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Iniciar sesión | Biblioteca</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="/app_biblioteca/css/estilos.css" rel="stylesheet"></head><body><?php mostrarMensaje(); ?></body></html>
<?php require __DIR__ . '/admin/login/login.php'; ?>
