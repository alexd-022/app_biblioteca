<?php
require_once __DIR__ . '/../../admin/implements/conexion.php';
require_once __DIR__ . '/funciones.php';
if (!empty($_SESSION['usuario']['id_usuario'])) { header('Location: ' . (($_SESSION['usuario']['rol'] ?? '') === 'Admin' ? '/app_biblioteca/admin/dashboard.php' : '/app_biblioteca/admin/usuarios/mi-cuenta.php')); exit; }
$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();
    $nombre=trim($_POST['nombre']??'');$documento=trim($_POST['documento']??'');$correo=strtolower(trim($_POST['correo']??''));$clave=$_POST['clave']??'';$confirmar=$_POST['confirmar_clave']??'';
    if($nombre===''||mb_strlen($nombre)>100||$documento===''||mb_strlen($documento)>30||!filter_var($correo,FILTER_VALIDATE_EMAIL)||mb_strlen($correo)>150)$errores[]='Ingresa nombre, documento y correo válidos.';
    elseif(strlen($clave)<8)$errores[]='La contraseña debe tener al menos 8 caracteres.';
    elseif($clave!==$confirmar)$errores[]='Las contraseñas no coinciden.';
    else{
        $lock=(int)$pdo->query("SELECT GET_LOCK('app_biblioteca_primer_admin',5)")->fetchColumn();
        if($lock!==1)$errores[]='No se pudo iniciar el registro. Inténtalo nuevamente.';
        else{try{$pdo->beginTransaction();$admins=(int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='Admin'")->fetchColumn();$rol=$admins===0?'Admin':'Usuario';crearUsuario($nombre,$documento,$correo,$clave,$rol);$pdo->commit();mensaje('ok',$rol==='Admin'?'Primera cuenta creada como Admin. Ya puedes iniciar sesión.':'Cuenta creada correctamente. Ya puedes iniciar sesión.');header('Location: /app_biblioteca/log-in.php');exit;}catch(PDOException $exception){if($pdo->inTransaction())$pdo->rollBack();$errores[]=$exception->getCode()==='23000'?'El documento o correo ya existe.':'No se pudo crear la cuenta.';}finally{$pdo->query("SELECT RELEASE_LOCK('app_biblioteca_primer_admin')");}}
    }
}
iniciarPagina('Registro');
?>
<section class="auth-card"><div class="auth-icon"><i class="bi bi-person-plus"></i></div><h1>Crear cuenta</h1><p>Regístrate para acceder a la biblioteca. El primer registro obtiene el rol Admin.</p><?php foreach($errores as $error): ?><div class="alert alert-danger"><?= escapar($error); ?></div><?php endforeach; ?>
<form method="post"><input type="hidden" name="token_csrf" value="<?= escapar(tokenCsrf()); ?>"><div class="mb-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" maxlength="100" required value="<?= escapar($_POST['nombre']??''); ?>"></div><div class="mb-3"><label class="form-label">Documento</label><input class="form-control" name="documento" maxlength="30" required value="<?= escapar($_POST['documento']??''); ?>"></div><div class="mb-3"><label class="form-label">Correo</label><input class="form-control" type="email" name="correo" maxlength="150" required value="<?= escapar($_POST['correo']??''); ?>"></div><div class="mb-3"><label class="form-label">Contraseña (mínimo 8 caracteres)</label><input class="form-control" type="password" name="clave" minlength="8" required autocomplete="new-password"></div><div class="mb-4"><label class="form-label">Confirmar contraseña</label><input class="form-control" type="password" name="confirmar_clave" minlength="8" required autocomplete="new-password"></div><button class="btn btn-primary w-100">Registrarme</button></form><p class="mt-3 mb-0 text-center">¿Ya tienes cuenta? <a href="/app_biblioteca/log-in.php">Inicia sesión</a></p></section><?php finPagina(); ?>
