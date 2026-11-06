<?php
require_once __DIR__ . '/config/conexion.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar_password'] ?? '';

    if ($nombre === '' || strlen($nombre) > 100 || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 150) {
        flash('error', 'Ingresa un nombre y un correo válidos.');
    } elseif (strlen($password) < 8) {
        flash('error', 'La contraseña debe tener al menos 8 caracteres.');
    } elseif ($password !== $confirmar) {
        flash('error', 'Las contraseñas no coinciden.');
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO administradores (nombre, correo, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$nombre, $correo, password_hash($password, PASSWORD_DEFAULT)]);
            flash('success', 'Cuenta creada. Ya puedes iniciar sesión.');
            header('Location: login.php');
            exit;
        } catch (PDOException $exception) {
            flash('error', $exception->getCode() === '23000' ? 'Ya existe una cuenta con ese correo.' : 'No se pudo crear la cuenta.');
        }
    }
}

page_start('Registro de administrador', '', false);
show_flash();
?>
<section class="auth-card"><div class="auth-icon"><i class="bi bi-person-plus"></i></div><h1>Crear cuenta</h1><p class="text-secondary">Registra una cuenta de administrador para gestionar la biblioteca.</p>
<form method="post" data-validate>
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
<div class="mb-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" maxlength="100" required value="<?= e($_POST['nombre'] ?? ''); ?>"></div>
<div class="mb-3"><label class="form-label">Correo</label><input class="form-control" type="email" name="correo" maxlength="150" required value="<?= e($_POST['correo'] ?? ''); ?>"></div>
<div class="mb-3"><label class="form-label">Contraseña (mínimo 8 caracteres)</label><input class="form-control" type="password" name="password" minlength="8" required autocomplete="new-password"></div>
<div class="mb-4"><label class="form-label">Confirmar contraseña</label><input class="form-control" type="password" name="confirmar_password" minlength="8" required autocomplete="new-password"></div>
<button class="btn btn-primary w-100" type="submit">Registrarme</button></form><p class="mt-3 mb-0 text-center">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p></section>
<?php page_end(); ?>
