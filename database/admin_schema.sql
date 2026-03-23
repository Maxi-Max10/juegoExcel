-- Tabla de administradores (login separado del de usuarios)
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(40) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Insertar admin por defecto: usuario "admin", contraseña "admin123"
-- Cambiar la contraseña después del primer inicio de sesión.
INSERT IGNORE INTO admins (username, password_hash) VALUES
('admin', '$2y$10$kH8Ql8xKxYe5RzUQnJ4Kj.3YQ0H7vHSzKJ4XmxhPdFjT06Y6VIbXC');
