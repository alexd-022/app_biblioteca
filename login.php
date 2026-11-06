<?php
require_once __DIR__ . '/config/conexion.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . (($_SESSION['user_role'] ?? '') === 'Admin' ? 'index.php' : 'mi_cuenta.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id_usuario, nombre, password_hash, rol FROM usuarios WHERE correo = ?');
    $stmt->execute([$correo]);
    $user = $stmt->fetch();

    if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id_usuario'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_role'] = $user['rol'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: ' . ($user['rol'] === 'Admin' ? 'index.php' : 'mi_cuenta.php'));
        exit;
    }
    flash('error', 'Correo o contraseña incorrectos.');
}

page_start('Iniciar sesión', '', false);
show_flash();
?>
<section class="auth-card"><div class="auth-icon"><i class="bi bi-book-half"></i></div><h1>Biblioteca</h1><p class="text-secondary">Inicia sesión para continuar.</p>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>"><div class="mb-3"><label class="form-label">Correo</label><input class="form-control" type="email" name="correo" required autocomplete="username" value="<?= e($_POST['correo'] ?? ''); ?>"></div><div class="mb-4"><label class="form-label">Contraseña</label><input class="form-control" type="password" name="password" required autocomplete="current-password"></div><button class="btn btn-primary w-100" type="submit">Iniciar sesión</button></form><p class="mt-3 mb-0 text-center">¿No tienes cuenta? <a href="register.php">Regístrate</a></p></section>
<?php page_end(); ?>
