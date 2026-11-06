<?php
require_once __DIR__ . '/admin/implements/conexion.php';
require_once __DIR__ . '/admin/login/procesar_login.php';

if (!empty($_SESSION['usuario']['id_usuario'])) {
    header('Location: ' . (($_SESSION['usuario']['rol'] ?? '') === 'Admin'
        ? '/app_biblioteca/admin/dashboard.php'
        : '/app_biblioteca/admin/usuarios/mi-cuenta.php'));
    exit;
}

iniciarPagina('Iniciar sesión');
mostrarMensaje();
?>
<section class="auth-card"><div class="auth-icon"><i class="bi bi-book-half"></i></div><h1>Iniciar sesión</h1><p>Accede a tu cuenta de biblioteca.</p><form action="/app_biblioteca/admin/login/procesar_login.php" method="post"><input type="hidden" name="token_csrf" value="<?= escapar(tokenCsrf()); ?>"><div class="mb-3"><label class="form-label" for="correo">Correo</label><input class="form-control" id="correo" type="email" name="correo" required autocomplete="username" value="<?= escapar($_POST['correo'] ?? ''); ?>"></div><div class="mb-4"><label class="form-label" for="clave">Contraseña</label><input class="form-control" id="clave" type="password" name="clave" required autocomplete="current-password"></div><button class="btn btn-primary w-100" type="submit">Iniciar sesión</button></form><p class="mt-3 mb-0 text-center">¿No tienes cuenta? <a href="/app_biblioteca/registrarse.php">Regístrate</a></p></section>
<?php finPagina(); ?>
