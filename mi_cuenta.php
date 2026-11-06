<?php
require_once __DIR__ . '/config/conexion.php';
require_authenticated();
if (($_SESSION['user_role'] ?? '') !== 'Usuario') {
    header('Location: index.php');
    exit;
}
$stmt = $pdo->prepare("SELECT p.*,l.titulo,l.autor,IF(p.estado_prestamo='Activo' AND p.fecha_devolucion<CURDATE(),'Atrasado',p.estado_prestamo) estado_vista FROM prestamos p JOIN libros l ON l.id_libro=p.id_libro WHERE p.id_usuario=? ORDER BY p.id_prestamo DESC");
$stmt->execute([(int)$_SESSION['user_id']]);
$loans = $stmt->fetchAll();
page_start('Mis préstamos','mi_cuenta.php',true,false);
?>
<div class="page-heading"><div><span class="eyebrow">MI CUENTA</span><h1>Mis préstamos</h1><p>Hola, <?= e($_SESSION['user_name']); ?>. Aquí puedes consultar tus préstamos actuales e historial.</p></div></div>
<section class="panel"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Libro</th><th>Autor</th><th>Fecha préstamo</th><th>Fecha límite</th><th>Estado</th></tr></thead><tbody><?php foreach($loans as $loan): ?><tr><td><?= e($loan['titulo']); ?></td><td><?= e($loan['autor']); ?></td><td><?= e($loan['fecha_prestamo']); ?></td><td><?= e($loan['fecha_devolucion']); ?></td><td><span class="badge rounded-pill text-bg-<?= $loan['estado_vista']==='Atrasado'?'danger':($loan['estado_vista']==='Devuelto'?'success':'warning'); ?>"><?= e($loan['estado_vista']); ?></span></td></tr><?php endforeach; ?><?php if(!$loans): ?><tr><td colspan="5" class="text-center text-secondary py-4">No tienes préstamos registrados.</td></tr><?php endif; ?></tbody></table></div></section>
<?php page_end(); ?>
