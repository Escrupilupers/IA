-- Script para crear la tabla usuarios y primer admin
-- Base de datos: restaurante

-- Crear tabla
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  usuario VARCHAR(255) UNIQUE NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  rol VARCHAR(50) DEFAULT 'user',
  activo TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Insertar primer usuario admin
-- Credenciales: usuario=admin, contraseña=admin123
-- Hash generado con: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO usuarios (nombre, usuario, contrasena, rol, activo) 
VALUES ('Administrador', 'admin', '$2y$10$n8lVVcTTg7hIc/CEHVRmIuG.OBJz2X7WYChAPv0I5VHjkJEzL0Euu', 'admin', 1)
ON DUPLICATE KEY UPDATE contrasena = VALUES(contrasena);

-- Opcional: Crear más usuarios de prueba
-- INSERT INTO usuarios (nombre, usuario, contrasena, rol, activo) 
-- VALUES ('Usuario Prueba', 'usuario.prueba', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36DH..', 'user', 1);

-- Verificar usuarios creados
SELECT id, nombre, usuario, rol, activo, created_at FROM usuarios;
