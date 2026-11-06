<?php
require_once __DIR__ . '/config/conexion.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    post_only();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id_libro'] ?? 0);
    if ($action === 'delete') {
        $check = $pdo->prepare('SELECT COUNT(*) FROM prestamos WHERE id_libro=?'); $check->execute([$id]);
        if (!$id || (int)$check->fetchColumn() > 0) { flash('error', 'No se puede eliminar el libro porque tiene préstamos asociados.'); }
        else { $pdo->prepare('DELETE FROM libros WHERE id_libro=?')->execute([$id]); flash('success', 'Libro eliminado.'); }
    } else {
        $titulo=trim($_POST['titulo']??''); $autor=trim($_POST['autor']??''); $categoria=trim($_POST['categoria']??'');
        if ($titulo===''||$autor===''||$categoria===''||mb_strlen($titulo)>200||mb_strlen($autor)>150||mb_strlen($categoria)>100) { flash('error','Completa los campos con valores válidos.'); }
        elseif ($action==='edit' && $id>0) { $pdo->prepare('UPDATE libros SET titulo=?,autor=?,categoria=? WHERE id_libro=?')->execute([$titulo,$autor,$categoria,$id]); flash('success','Libro actualizado.'); }
        elseif ($action==='create') { $pdo->prepare("INSERT INTO libros(titulo,autor,categoria) VALUES(?,?,?)")->execute([$titulo,$autor,$categoria]); flash('success','Libro registrado.'); }
        else { flash('error','Operación inválida.'); }
    }
    header('Location: libros.php'); exit;
}
$q=trim($_GET['q']??''); $estado=$_GET['estado']??''; $sql='SELECT * FROM libros WHERE 1=1'; $params=[];
if($q!==''){$sql.=' AND (titulo LIKE ? OR autor LIKE ? OR categoria LIKE ?)';$params=array_merge($params,array_fill(0,3,'%'.$q.'%'));}
if(in_array($estado,['Disponible','Prestado'],true)){$sql.=' AND estado=?';$params[]=$estado;}
$sql.=' ORDER BY id_libro DESC';$stmt=$pdo->prepare($sql);$stmt->execute($params);$libros=$stmt->fetchAll();
$edit=null;if(isset($_GET['editar'])){$st=$pdo->prepare('SELECT * FROM libros WHERE id_libro=?');$st->execute([(int)$_GET['editar']]);$edit=$st->fetch();}
page_start('Libros','libros.php');
?>
<div class="page-heading"><div><span class="eyebrow">CATÁLOGO</span><h1>Libros</h1><p>Administra el catálogo y consulta disponibilidad.</p></div></div>
<section class="panel mb-4"><h2><?= $edit?'Editar libro':'Registrar libro'; ?></h2><form method="post" class="row g-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>"><input type="hidden" name="action" value="<?= $edit?'edit':'create'; ?>"><?php if($edit): ?><input type="hidden" name="id_libro" value="<?= (int)$edit['id_libro']; ?>"><?php endif; ?><div class="col-md-4"><label class="form-label">Título</label><input class="form-control" name="titulo" maxlength="200" required value="<?= e($edit['titulo']??''); ?>"></div><div class="col-md-4"><label class="form-label">Autor</label><input class="form-control" name="autor" maxlength="150" required value="<?= e($edit['autor']??''); ?>"></div><div class="col-md-3"><label class="form-label">Categoría</label><input class="form-control" name="categoria" maxlength="100" required value="<?= e($edit['categoria']??''); ?>"></div><div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100">Guardar</button></div></form></section>
<section class="panel"><form method="get" class="row g-2 mb-3"><div class="col-md-6"><input class="form-control" name="q" placeholder="Buscar título, autor o categoría" value="<?= e($q); ?>"></div><div class="col-md-3"><select class="form-select" name="estado"><option value="">Todos los estados</option><option <?= $estado==='Disponible'?'selected':''; ?>>Disponible</option><option <?= $estado==='Prestado'?'selected':''; ?>>Prestado</option></select></div><div class="col-md-3"><button class="btn btn-outline-primary">Filtrar</button> <a class="btn btn-outline-secondary" href="libros.php">Limpiar</a></div></form><div class="table-responsive"><table class="table align-middle"><thead><tr><th>ID</th><th>Título</th><th>Autor</th><th>Categoría</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($libros as $l): ?><tr><td><?= (int)$l['id_libro']; ?></td><td><?= e($l['titulo']); ?></td><td><?= e($l['autor']); ?></td><td><?= e($l['categoria']); ?></td><td><span class="badge rounded-pill text-bg-<?= $l['estado']==='Disponible'?'success':'primary'; ?>"><?= e($l['estado']); ?></span></td><td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="libros.php?editar=<?= (int)$l['id_libro']; ?>">Editar</a><form method="post" onsubmit="return confirm('¿Eliminar este libro?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id_libro" value="<?= (int)$l['id_libro']; ?>"><button class="btn btn-sm btn-outline-danger">Eliminar</button></form></td></tr><?php endforeach; ?>
<?php if(!$libros): ?><tr><td colspan="6" class="text-center text-secondary py-4">No se encontraron libros.</td></tr><?php endif; ?></tbody></table></div></section>
<?php page_end(); ?>
