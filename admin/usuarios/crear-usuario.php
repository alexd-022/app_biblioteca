<?php
require_once __DIR__ . '/../../admin/implements/conexion.php';
exigirAdmin();
$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarPost();
    $nombre = trim($_POST['nombre'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $rol = $_POST['rol'] ?? 'Usuario';
    $clave = $_POST['clave'] ?? '';
    if ($nombre === '' || $documento === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($clave) < 8 || !in_array($rol, ['Usuario','Admin'], true)) {
        $errores[] = 'Revisa los campos; la clave debe tener al menos 8 caracteres.';
    } else {
        try {
            $pdo->prepare('INSERT INTO usuarios(nombre,documento,correo,password_hash,rol) VALUES(?,?,?,?,?)')->execute([$nombre,$documento,$correo,password_hash($clave,PASSWORD_DEFAULT),$rol]);
            mensaje('ok', 'Usuario creado correctamente.');
            header('Location: listado.php'); exit;
        } catch (PDOException $exception) {
            $errores[] = $exception->getCode() === '23000' ? 'El documento o correo ya está registrado.' : 'No se pudo crear el usuario.';
        }
    }
}
iniciarPagina('Crear usuario');barraNavegacion('usuarios-listado.php');
?>
<div class="encabezado-pagina"><div><span class="sobrelinea">GESTIÓN DE CUENTAS</span><h1>Crear usuario</h1><p>Los registros públicos no pueden asignarse el rol Admin.</p></div></div><section class="panel formulario-panel">
<?php foreach ($errores as $error): ?><div class="alert alert-danger"><?= escapar($error); ?></div><?php endforeach; ?>
<form method="post" class="row g-3"><input type="hidden" name="token_csrf" value="<?= escapar(tokenCsrf()); ?>"><div class="col-md-6"><label class="form-label">Nombre</label><input class="form-control" name="nombre" required maxlength="100" value="<?= escapar($_POST['nombre']??''); ?>"></div><div class="col-md-6"><label class="form-label">Documento</label><input class="form-control" name="documento" required maxlength="30" value="<?= escapar($_POST['documento']??''); ?>"></div><div class="col-md-6"><label class="form-label">Correo electrónico</label><input class="form-control" type="email" name="correo" required value="<?= escapar($_POST['correo']??''); ?>"></div><div class="col-md-6"><label class="form-label">Rol</label><select class="form-select" name="rol"><option value="Usuario">Usuario</option><option value="Admin">Admin</option></select></div><div class="col-md-6"><label class="form-label">Contraseña</label><input class="form-control" type="password" name="clave" minlength="8" required autocomplete="new-password"></div><div class="col-12"><button class="btn btn-primary">Crear cuenta</button> <a class="btn btn-outline-secondary" href="listado.php">Volver</a></div></form></section><?php finPagina(); ?>
