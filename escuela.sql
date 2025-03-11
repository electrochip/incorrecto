-- Control de Cambios
-- Hash: m9n0o1p2q3r4s5t6u7v8w9x0y1z2 (MD5 del contenido sin este comentario)
-- Versión: v1.4
CREATE DATABASE IF NOT EXISTS if0_38403974_cantonal;
USE if0_38403974_cantonal;

CREATE TABLE ciclos_escolares (
    nombre VARCHAR(9) PRIMARY KEY,
    es_actual BOOLEAN DEFAULT FALSE,
    es_siguiente BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE docentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    primer_apellido VARCHAR(100) NOT NULL,
    segundo_apellido VARCHAR(100),
    direccion TEXT,
    telefono VARCHAR(15)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE grupos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grado INT NOT NULL,
    grupo CHAR(1) NOT NULL,
    id_docente INT,
    ciclo VARCHAR(9),
    capacidad_maxima INT DEFAULT 32,
    FOREIGN KEY (id_docente) REFERENCES docentes(id),
    FOREIGN KEY (ciclo) REFERENCES ciclos_escolares(nombre)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE alumnos (
    curp CHAR(18) PRIMARY KEY,
    primer_apellido VARCHAR(100) NOT NULL,
    segundo_apellido VARCHAR(100),
    nombres VARCHAR(100) NOT NULL,
    telefono VARCHAR(15),
    tutor VARCHAR(200),
    niev CHAR(5) UNIQUE NOT NULL,
    bloqueado BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curp_alumno CHAR(18),
    id_grupo INT,
    ciclo VARCHAR(9),
    folio VARCHAR(20) UNIQUE NOT NULL,
    fecha_inscripcion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (curp_alumno) REFERENCES alumnos(curp),
    FOREIGN KEY (id_grupo) REFERENCES grupos(id),
    FOREIGN KEY (ciclo) REFERENCES ciclos_escolares(nombre)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE historico (
    curp_alumno CHAR(18),
    ciclo VARCHAR(9),
    grado INT NOT NULL,
    grupo CHAR(1) NOT NULL,
    promedio FLOAT DEFAULT 0.0,
    estatus ENUM('Aprobado', 'Reprobado') DEFAULT 'Aprobado',
    PRIMARY KEY (curp_alumno, ciclo),
    FOREIGN KEY (curp_alumno) REFERENCES alumnos(curp),
    FOREIGN KEY (ciclo) REFERENCES ciclos_escolares(nombre)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    es_admin BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE operaciones_pendientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    tipo_operacion ENUM('Alta', 'Baja', 'Modificacion') NOT NULL,
    tabla_afectada VARCHAR(50) NOT NULL,
    datos_json JSON NOT NULL,
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    aprobada BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE periodos_inscripcion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ciclo VARCHAR(9),
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    FOREIGN KEY (ciclo) REFERENCES ciclos_escolares(nombre)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Datos de ejemplo
INSERT INTO ciclos_escolares (nombre, es_actual) VALUES ('2024-2025', TRUE);
INSERT INTO ciclos_escolares (nombre, es_siguiente) VALUES ('2025-2026', TRUE);
INSERT INTO ciclos_escolares (nombre) VALUES ('2023-2024');

INSERT INTO usuarios (usuario, contrasena, es_admin) 
VALUES ('admin', '$2y$10$Q8g7z7zZ7zZ7z7zZ7zZ7zO7zZ7zZ7zZ7z7zZ7zZ7zZ7zZ7zZ7zZ7z', 1); -- admin123

INSERT INTO docentes (nombre, primer_apellido, segundo_apellido, direccion, telefono) 
VALUES ('María', 'García', 'López', 'Calle 123, Ciudad', '555-1234');

INSERT INTO grupos (grado, grupo, id_docente, ciclo, capacidad_maxima) 
VALUES (1, 'A', 1, '2024-2025', 32),
       (1, 'B', 1, '2024-2025', 32),
       (2, 'A', 1, '2025-2026', 32);

INSERT INTO alumnos (curp, primer_apellido, segundo_apellido, nombres, telefono, tutor, niev) 
VALUES ('CURP12345678901234', 'Hernández', 'Gómez', 'Luis', '555-9012', 'Ana Gómez', 'ABC12');

INSERT INTO inscripciones (curp_alumno, id_grupo, ciclo, folio) 
VALUES ('CURP12345678901234', 3, '2025-2026', 'INS202502241234567');

INSERT INTO historico (curp_alumno, ciclo, grado, grupo, promedio, estatus) 
VALUES ('CURP12345678901234', '2023-2024', 1, 'A', 8.5, 'Aprobado'),
       ('CURP12345678901234', '2024-2025', 2, 'A', 0.0, 'Aprobado');

INSERT INTO periodos_inscripcion (ciclo, fecha_inicio, fecha_fin, hora_inicio, hora_fin)
VALUES ('2025-2026', '2025-02-25', '2025-02-27', '08:00:00', '20:00:00');