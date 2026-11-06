CREATE DATABASE IF NOT EXISTS biblioteca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE biblioteca;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS prestamos;
DROP TABLE IF EXISTS libros;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    documento VARCHAR(30) NOT NULL UNIQUE,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    rol ENUM('Usuario','Admin') NOT NULL DEFAULT 'Usuario',
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE libros (
    id_libro INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(150) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    estado ENUM('Disponible','Prestado') NOT NULL DEFAULT 'Disponible'
) ENGINE=InnoDB;

CREATE TABLE prestamos (
    id_prestamo INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_libro INT NOT NULL,
    fecha_prestamo DATE NOT NULL,
    fecha_devolucion DATE NOT NULL,
    estado_prestamo ENUM('Activo','Devuelto') NOT NULL DEFAULT 'Activo',
    CONSTRAINT fk_prestamo_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_prestamo_libro FOREIGN KEY (id_libro) REFERENCES libros(id_libro) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_prestamos_estado_fecha (estado_prestamo, fecha_devolucion)
) ENGINE=InnoDB;

INSERT INTO usuarios (nombre, documento, correo, rol) VALUES
('Ana Gómez', '1001', 'ana@example.com', 'Usuario'),
('Carlos Ruiz', '1002', 'carlos@example.com', 'Usuario'),
('Lucía Pérez', '1003', 'lucia@example.com', 'Usuario'),
('Mateo López', '1004', 'mateo@example.com', 'Usuario');

INSERT INTO libros (titulo, autor, categoria, estado) VALUES
('Clean Code', 'Robert C. Martin', 'Programación', 'Prestado'),
('El Principito', 'Antoine de Saint-Exupéry', 'Literatura', 'Disponible'),
('La Metamorfosis', 'Franz Kafka', 'Literatura', 'Disponible'),
('JavaScript Moderno', 'Luis García', 'Programación', 'Disponible'),
('Historia de la Matemática', 'María Torres', 'Ciencias', 'Prestado'),
('Algoritmos y Estructuras de Datos', 'John Klein', 'Tecnología', 'Disponible');

INSERT INTO prestamos (id_usuario, id_libro, fecha_prestamo, fecha_devolucion, estado_prestamo) VALUES
(1, 3, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'Devuelto'),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Activo'),
(3, 5, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Activo');

-- El primer administrador se crea al registrarse. Los siguientes registros serán usuarios.
