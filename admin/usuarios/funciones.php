<?php
require_once __DIR__ . '/../implements/conexion.php';

function obtenerUsuarios(string $busqueda = ''): array
{
    global $pdo;
    $termino = '%' . $busqueda . '%';
    $consulta = $pdo->prepare('SELECT id_usuario,nombre,documento,correo,rol,creado_en FROM usuarios WHERE nombre LIKE ? OR documento LIKE ? OR correo LIKE ? ORDER BY id_usuario DESC');
    $consulta->execute([$termino, $termino, $termino]);
    return $consulta->fetchAll();
}

function obtenerUsuarioPorId(int $id): ?array
{
    global $pdo;
    $consulta = $pdo->prepare('SELECT id_usuario,nombre,documento,correo,rol FROM usuarios WHERE id_usuario = ?');
    $consulta->execute([$id]);
    return $consulta->fetch() ?: null;
}

function crearUsuario(string $nombre, string $documento, string $correo, string $clave, string $rol = 'Usuario'): bool
{
    global $pdo;
    $consulta = $pdo->prepare('INSERT INTO usuarios (nombre,documento,correo,password_hash,rol) VALUES (?,?,?,?,?)');
    return $consulta->execute([$nombre, $documento, $correo, password_hash($clave, PASSWORD_DEFAULT), $rol]);
}

function actualizarUsuario(int $id, string $nombre, string $documento, string $correo, string $rol, ?string $clave = null): bool
{
    global $pdo;
    if ($clave !== null && $clave !== '') {
        $consulta = $pdo->prepare('UPDATE usuarios SET nombre=?,documento=?,correo=?,rol=?,password_hash=? WHERE id_usuario=?');
        return $consulta->execute([$nombre, $documento, $correo, $rol, password_hash($clave, PASSWORD_DEFAULT), $id]);
    }
    $consulta = $pdo->prepare('UPDATE usuarios SET nombre=?,documento=?,correo=?,rol=? WHERE id_usuario=?');
    return $consulta->execute([$nombre, $documento, $correo, $rol, $id]);
}

function eliminarUsuario(int $id): bool
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        $consulta = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE id_usuario = ? AND estado_prestamo = 'Activo'");
        $consulta->execute([$id]);
        if ((int)$consulta->fetchColumn() > 0) {
            $pdo->rollBack();
            return false;
        }

        // Si ya no hay préstamos activos, elimina los registros devueltos para respetar la clave foránea.
        $consulta = $pdo->prepare("DELETE FROM prestamos WHERE id_usuario = ? AND estado_prestamo = 'Devuelto'");
        $consulta->execute([$id]);

        $consulta = $pdo->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
        $consulta->execute([$id]);
        $eliminado = $consulta->rowCount() === 1;

        $pdo->commit();
        return $eliminado;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}
