-- ============================================================
-- FASHION LIGHT SYSTEM
-- Base de datos para gestión de inventario y ventas
-- Proyecto académico SENA 2026
-- ============================================================

CREATE DATABASE IF NOT EXISTS fashion_light_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE fashion_light_system;

-- ============================================================
-- TABLA: productos
-- ============================================================

CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    marca VARCHAR(100) NOT NULL,
    talla VARCHAR(20) NOT NULL,
    color VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    imagen VARCHAR(255) NULL
);

-- Si la tabla ya existia antes de agregar imagen, ejecutar tambien:
-- ALTER TABLE productos ADD COLUMN imagen VARCHAR(255) NULL AFTER stock;

-- ============================================================
-- TABLA: usuarios
-- ============================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL
);

-- ============================================================
-- TABLA: sesiones_caja
-- ============================================================

CREATE TABLE IF NOT EXISTS sesiones_caja (
    id_sesion INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha_apertura DATETIME NOT NULL,
    fecha_cierre DATETIME NULL,
    monto_inicial DECIMAL(10,2) NOT NULL,
    monto_final DECIMAL(10,2) NULL,
    estado VARCHAR(30) NOT NULL,
    CONSTRAINT fk_sesion_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id_usuario)
);

-- ============================================================
-- TABLA: ventas
-- ============================================================

CREATE TABLE IF NOT EXISTS ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    sesion_caja_id INT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_venta_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_venta_sesion
        FOREIGN KEY (sesion_caja_id)
        REFERENCES sesiones_caja(id_sesion)
);

-- ============================================================
-- TABLA: detalle_venta
-- ============================================================

CREATE TABLE IF NOT EXISTS detalle_venta (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_venta
        FOREIGN KEY (venta_id)
        REFERENCES ventas(id_venta),
    CONSTRAINT fk_detalle_producto
        FOREIGN KEY (producto_id)
        REFERENCES productos(id_producto)
);

-- ============================================================
-- DATOS DE PRUEBA: PRODUCTOS
-- ============================================================

INSERT INTO productos
(nombre, marca, talla, color, precio, stock)
VALUES
('Camiseta básica', 'Fashion Light', 'S', 'Blanco', 45000.00, 20),
('Camiseta estampada', 'Urban Style', 'M', 'Negro', 55000.00, 15),
('Jean clásico', 'Denim Pro', '32', 'Azul', 120000.00, 10),
('Pantalón casual', 'Fashion Light', 'M', 'Beige', 95000.00, 12),
('Chaqueta deportiva', 'Sport Line', 'L', 'Azul', 150000.00, 8),
('Vestido casual', 'Elegance', 'M', 'Rojo', 110000.00, 7),
('Blusa femenina', 'Fashion Light', 'S', 'Rosado', 75000.00, 14),
('Sudadera deportiva', 'Sport Line', 'L', 'Gris', 105000.00, 9);

-- ============================================================
-- DATOS DE PRUEBA: USUARIO ADMINISTRADOR
-- ============================================================

INSERT INTO usuarios
(nombres, apellidos, cedula, fecha_nacimiento, correo, password, rol)
VALUES
(
    'Administrador',
    'Fashion Light',
    '1000000000',
    '1990-01-01',
    'admin@fashionlight.com',
    'admin123',
    'administrador'
);