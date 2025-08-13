#!/bin/bash

# Product Database Setup Script for Inception Project
# This script creates a products table and inserts sample data

echo "🛍️ Setting up products database..."

# Wait for MariaDB to be ready
sleep 15

# Database connection details from environment
DB_NAME=${DATABASE_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASSWORD}
DB_HOST="mariadb"

# Create products table and insert sample data
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME << 'EOF'

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    stock INT DEFAULT 0,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample products (matching your screenshot)
INSERT INTO products (name, price, description, category, stock, image_url) VALUES
('Tablette iPad', 449.99, 'Tablette tactile iPad©le pour la productivité et le divertissement', 'Informatique', 1, 'https://via.placeholder.com/300x200/007bff/ffffff?text=iPad'),
('Montre Connectée Apple', 399.99, 'Montre intelligente avec suivi de la santé et connectivité', 'Informatique', 2, 'https://via.placeholder.com/300x200/28a745/ffffff?text=Apple+Watch'),
('Casque Audio Sony', 299.99, 'Casque audio sans fil avec réduction de bruit', 'Informatique', 2, 'https://via.placeholder.com/300x200/dc3545/ffffff?text=Sony+Headphones'),
('Smartphone Premium', 899.99, 'Smartphone haut de gamme avec caméra professionnelle', 'Informatique', 5, 'https://via.placeholder.com/300x200/6f42c1/ffffff?text=Smartphone'),
('Ordinateur Portable', 1299.99, 'Ordinateur portable haute performance pour professionnels', 'Informatique', 3, 'https://via.placeholder.com/300x200/fd7e14/ffffff?text=Laptop'),
('Écouteurs Bluetooth', 149.99, 'Écouteurs sans fil avec excellente qualité audio', 'Informatique', 10, 'https://via.placeholder.com/300x200/20c997/ffffff?text=Earbuds'),
('Clavier Mécanique', 179.99, 'Clavier mécanique RGB pour gaming et productivité', 'Informatique', 7, 'https://via.placeholder.com/300x200/e83e8c/ffffff?text=Keyboard'),
('Souris Gaming', 89.99, 'Souris haute précision pour les joueurs professionnels', 'Informatique', 12, 'https://via.placeholder.com/300x200/17a2b8/ffffff?text=Gaming+Mouse');

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert categories
INSERT INTO categories (name, description) VALUES
('Informatique', 'Produits informatiques et technologiques'),
('Électronique', 'Appareils électroniques grand public'),
('Gaming', 'Équipements pour jeux vidéo'),
('Accessoires', 'Accessoires et périphériques');

-- Create orders table for future use
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255),
    customer_email VARCHAR(255),
    total_amount DECIMAL(10,2),
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

EOF

echo "✅ Products database setup completed!"
echo "📊 Added 8 sample products"
echo "🏷️ Created 4 categories"
echo "💾 Database tables created successfully"
