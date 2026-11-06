<?php
require_once __DIR__ . '/../../admin/implements/conexion.php';

if (!empty($_SESSION['usuario']['id_usuario'])) {
    header('Location: ' . (($_SESSION['usuario']['rol'] ?? '') === 'Admin' ? '/app_biblioteca/admin/dashboard.php' : '/app_biblioteca/admin/usuarios/mi-cuenta.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $clave = $_POST['clave'] ?? '';
    $consulta = $pdo->prepare('SELECT id_usuario,nombre,documento,correo,password_hash,rol FROM usuarios WHERE correo = ?');
    $consulta->execute([$correo]);
    $usuario = $consulta->fetch();
    if ($usuario && !empty($usuario['password_hash']) && password_verify($clave, $usuario['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id_usuario' => (int)$usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'correo' => $usuario['correo'],
            'rol' => $usuario['rol'],
        ];
        $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
        header('Location: ' . ($usuario['rol'] === 'Admin' ? '/app_biblioteca/admin/dashboard.php' : '/app_biblioteca/admin/usuarios/mi-cuenta.php'));
        exit;
    }
    mensaje('error', 'Correo o contraseña incorrectos.');
    header('Location: /app_biblioteca/log-in.php');
    exit;
}
