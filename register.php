<?php
require_once __DIR__ . '/config/conexion.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . (($_SESSION['user_role'] ?? '') === 'Admin' ? 'index.php' : 'mi_cuenta.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nombre = trim($_POST['nombre'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar_password'] ?? '';

    if ($nombre === '' || mb_strlen($nombre) > 100 || $documento === '' || mb_strlen($documento) > 30 || !filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 150) {
        flash('error', 'Completa nombre, documento y un correo válido.');
    } elseif (strlen($password) < 8) {
        flash('error', 'La contraseña debe tener al menos 8 caracteres.');
    } elseif ($password !== $confirmar) {
        flash('error', 'Las contraseñas no coinciden.');
    } else {
        $lock = (int)$pdo->query("SELECT GET_LOCK('biblioteca_first_admin', 5)")->fetchColumn();
        if ($lock !== 1) {
            flash('error', 'No se pudo iniciar el registro. Inténtalo nuevamente.');
        } else {
            try {
                $pdo->beginTransaction();
                $countAdmins = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='Admin'")->fetchColumn();
                $role = $countAdmins === 0 ? 'Admin' : 'Usuario';
                $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, documento, correo, password_hash, rol) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$nombre, $documento, $correo, password_hash($password, PASSWORD_DEFAULT), $role]);
                $pdo->commit();
                flash('success', $role === 'Admin' ? 'Cuenta de administrador creada. Inicia sesión.' : 'Cuenta creada. Inicia sesión.');
                header('Location: login.php');
                exit;
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                flash('error', $exception->getCode() === '23000' ? 'Ese documento o correo ya está registrado.' : 'No se pudo crear la cuenta.');
            } finally {
                $pdo->query("SELECT RELEASE_LOCK('biblioteca_first_admin')");
            }
        }
    }
}

page_start('Registro', '', false);
show_flash();
?>
<section class="auth-card"><div class="auth-icon"><i class="bi bi-person-plus"></i></div><h1>Crear cuenta</h1><p class="text-secondary">Regístrate para acceder a la biblioteca.</p>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
<div class="mb-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" maxlength="100" required value="<?= e($_POST['nombre'] ?? ''); ?>"></div>
<div class="mb-3"><label class="form-label">Documento</label><input class="form-control" name="documento" maxlength="30" required value="<?= e($_POST['documento'] ?? ''); ?>"></div>
<div class="mb-3"><label class="form-label">Correo</label><input class="form-control" type="email" name="correo" maxlength="150" required value="<?= e($_POST['correo'] ?? ''); ?>"></div>
<div class="mb-3"><label class="form-label">Contraseña (mínimo 8 caracteres)</label><input class="form-control" type="password" name="password" minlength="8" required autocomplete="new-password"></div>
<div class="mb-4"><label class="form-label">Confirmar contraseña</label><input class="form-control" type="password" name="confirmar_password" minlength="8" required autocomplete="new-password"></div>
<button class="btn btn-primary w-100" type="submit">Registrarme</button></form><p class="mt-3 mb-0 text-center">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p></section>
<?php page_end(); ?>
