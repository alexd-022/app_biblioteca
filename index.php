<?php
require_once __DIR__ . '/config/conexion.php';
require_login();
$totalLibros = (int)$pdo->query('SELECT COUNT(*) FROM libros')->fetchColumn();
$disponibles = (int)$pdo->query("SELECT COUNT(*) FROM libros WHERE estado='Disponible'")->fetchColumn();
$prestados = (int)$pdo->query("SELECT COUNT(*) FROM libros WHERE estado='Prestado'")->fetchColumn();
$usuarios = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$atrasos = (int)$pdo->query("SELECT COUNT(*) FROM prestamos WHERE estado_prestamo='Activo' AND fecha_devolucion<CURDATE()")->fetchColumn();
$recientes = $pdo->query("SELECT p.*,u.nombre usuario,l.titulo libro,IF(p.estado_prestamo='Activo' AND p.fecha_devolucion<CURDATE(),'Atrasado',p.estado_prestamo) estado_vista FROM prestamos p JOIN usuarios u ON u.id_usuario=p.id_usuario JOIN libros l ON l.id_libro=p.id_libro ORDER BY p.id_prestamo DESC LIMIT 8")->fetchAll();
page_start('Inicio', 'index.php');
?>
<div class="page-heading"><div><span class="eyebrow">RESUMEN</span><h1>Panel de biblioteca</h1><p>Bienvenido, <?= e($_SESSION['admin_name']); ?>.</p></div><div class="d-flex gap-2"><a class="btn btn-primary" href="libros.php">Registrar libro</a><a class="btn btn-outline-primary" href="usuarios.php">Registrar usuario</a><a class="btn btn-outline-primary" href="prestamos.php">Nuevo préstamo</a></div></div>
<div class="stats-grid">
<?php foreach ([['Total de libros',$totalLibros,'primary','bi-book'],['Disponibles',$disponibles,'success','bi-check-circle'],['Prestados',$prestados,'info','bi-arrow-left-right'],['Atrasados',$atrasos,'danger','bi-exclamation-triangle'],['Usuarios',$usuarios,'warning','bi-people']] as [$label,$value,$color,$icon]): ?>
<div class="stat-card border-<?= e($color); ?>"><i class="bi <?= e($icon); ?> text-<?= e($color); ?>"></i><div><span><?= e($label); ?></span><strong><?= (int)$value; ?></strong></div></div>
<?php endforeach; ?>
</div>
<section class="panel"><div class="section-heading"><h2>Préstamos recientes</h2><a href="historial.php">Ver historial</a></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Usuario</th><th>Libro</th><th>Préstamo</th><th>Devolución</th><th>Estado</th></tr></thead><tbody>
<?php foreach ($recientes as $row): ?><tr><td><?= e($row['usuario']); ?></td><td><?= e($row['libro']); ?></td><td><?= e($row['fecha_prestamo']); ?></td><td><?= e($row['fecha_devolucion']); ?></td><td><span class="badge rounded-pill text-bg-<?= $row['estado_vista']==='Atrasado'?'danger':($row['estado_vista']==='Devuelto'?'success':'warning'); ?>"><?= e($row['estado_vista']); ?></span></td></tr><?php endforeach; ?>
<?php if (!$recientes): ?><tr><td colspan="5" class="text-center text-secondary py-4">Todavía no hay préstamos registrados.</td></tr><?php endif; ?>
</tbody></table></div></section>
<?php page_end(); ?>
