-- =============================================
-- Sistema de Facturación / POS Database Schema
-- =============================================

DROP DATABASE IF EXISTS pos_system;
CREATE DATABASE pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pos_system;

-- Tabla de usuarios
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'vendedor') DEFAULT 'vendedor',
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de categorías
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de productos
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    min_stock INT DEFAULT 5,
    category_id INT,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tabla de clientes
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    ci_nit VARCHAR(50),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de ventas
CREATE TABLE sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    client_id INT,
    user_id INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    tax DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    payment_method ENUM('efectivo', 'qr', 'transferencia') NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'completed',
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de detalles de venta
CREATE TABLE sale_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    product_code VARCHAR(50) NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tabla de movimientos de stock
CREATE TABLE stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    movement_type ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    quantity INT NOT NULL,
    previous_stock INT NOT NULL,
    new_stock INT NOT NULL,
    reference_type ENUM('sale', 'adjustment', 'purchase') NOT NULL,
    reference_id INT,
    user_id INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insertar datos de ejemplo
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin@pos.com', 'admin'),
('vendedor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', 'vendedor@pos.com', 'vendedor');

INSERT INTO categories (name, description) VALUES
('Bebidas', 'Refrescos, jugos y aguas'),
('Snacks', 'Galletas, papas fritas y dulces'),
('Lácteos', 'Leche, queso y yogurt'),
('Panadería', 'Pan y productos de panadería'),
('Limpieza', 'Productos de aseo y limpieza');

INSERT INTO clients (code, name, ci_nit, phone, email, address) VALUES
('CLI001', 'Cliente General', '0', '00000000', 'general@cliente.com', 'Sin dirección'),
('CLI002', 'María González', '12345678', '70123456', 'maria@email.com', 'Av. Siempre Viva 123'),
('CLI003', 'Carlos Mendoza', '87654321', '75987654', 'carlos@email.com', 'Calle Principal 456');

INSERT INTO products (code, name, description, price, stock, category_id) VALUES
('PRD001', 'Coca Cola 500ml', 'Refresco de cola 500ml', 3.50, 100, 1),
('PRD002', 'Agua Vital 600ml', 'Agua purificada 600ml', 2.00, 150, 1),
('PRD003', 'Papas Lays', 'Papas fritas sabor natural', 4.50, 80, 2),
('PRD004', 'Oreo Original', 'Galletas chocolate', 5.00, 60, 2),
('PRD005', 'Leche PIL 1L', 'Leche entera 1 litro', 7.50, 40, 3),
('PRD006', 'Pan Tostado', 'Pan integral tostado', 12.00, 25, 4),
('PRD007', 'Detergente Ace', 'Detergente en polvo 1kg', 15.00, 30, 5);

-- Crear índices para mejorar performance
CREATE INDEX idx_products_code ON products(code);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_clients_code ON clients(code);
CREATE INDEX idx_sales_date ON sales(sale_date);
CREATE INDEX idx_sales_user ON sales(user_id);
CREATE INDEX idx_sale_details_sale ON sale_details(sale_id);
CREATE INDEX idx_stock_movements_product ON stock_movements(product_id);