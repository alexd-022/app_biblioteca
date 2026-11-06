ALTER TABLE usuarios
    ADD COLUMN password_hash VARCHAR(255) NULL AFTER correo,
    ADD COLUMN rol ENUM('Usuario','Admin') NOT NULL DEFAULT 'Usuario' AFTER password_hash,
    ADD COLUMN creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE usuarios u
JOIN administradores a ON LOWER(a.correo) = LOWER(u.correo)
SET u.password_hash = a.password_hash, u.rol = 'Admin';

INSERT INTO usuarios (nombre, documento, correo, password_hash, rol)
SELECT a.nombre, CONCAT('ADMIN-', a.id_admin), a.correo, a.password_hash, 'Admin'
FROM administradores a
LEFT JOIN usuarios u ON LOWER(u.correo) = LOWER(a.correo)
WHERE u.id_usuario IS NULL;

ALTER TABLE usuarios ADD UNIQUE KEY uq_usuarios_correo (correo);
DROP TABLE administradores;
