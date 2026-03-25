CREATE DATABASE IF NOT EXISTS melodymasters;
USE melodymasters;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Customer', 'Staff', 'Admin') DEFAULT 'Customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INT DEFAULT NULL,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    brand VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    shipping_cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    is_digital BOOLEAN DEFAULT FALSE,
    image VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS digital_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    file_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Processing', 'Completed', 'Cancelled') DEFAULT 'Pending',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    order_number VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE
);

-- Insert Default Admin and Staff
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@melodymasters.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin'), -- pass: password
('Staff', 'staff@melodymasters.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff');

-- Insert Categories
INSERT INTO categories (id, name, parent_id) VALUES 
(1, 'Guitars', NULL),
(2, 'Keyboards', NULL),
(3, 'Drums & Percussion', NULL),
(4, 'Wind Instruments', NULL),
(5, 'String Instruments', NULL),
(6, 'Accessories', NULL),
(7, 'Digital Sheet Music', NULL);

-- Insert sample products mapping to user images
INSERT INTO products (category_id, name, description, brand, price, stock, is_digital, image) VALUES 
(1, 'Acoustic Guitar Pro', 'High quality acoustic guitar with beautiful finish.', 'Yamaha', 199.99, 10, FALSE, 'guitar.jpeg'),
(1, 'Electric Guitar X', 'Solid body electric guitar for rock and metal.', 'Fender', 499.99, 5, FALSE, 'electric.jpeg'),
(1, 'Bass Guitar Classic', 'Smooth sounding bass guitar.', 'Ibanez', 299.99, 8, FALSE, 'bass guitars.jpeg'),
(2, 'Digital Piano 88', 'Full 88-key digital piano with weighted keys.', 'Roland', 799.99, 4, FALSE, 'digital pianos.jpeg'),
(2, 'MIDI Controller Mini', 'Compact 25-key MIDI controller.', 'Akai', 99.99, 20, FALSE, 'MIDI controllers.jpeg'),
(2, 'Pro Synthesizer', 'Analog modeling synthesizer.', 'Korg', 599.99, 3, FALSE, 'synthesizers.jpeg'),
(2, 'Keyboard Arranger', 'Feature-rich keyboard with backing tracks.', 'Casio', 249.99, 12, FALSE, 'keyboards.jpeg'),
(3, 'Acoustic Drum Kit Set', 'Complete 5-piece acoustic drum kit.', 'Pearl', 699.99, 2, FALSE, 'acoustic drum kits.jpeg'),
(3, 'Drums and Percussion Master', 'Professional snare drum.', 'Ludwig', 199.99, 7, FALSE, 'drums and  percussion.jpeg'),
(3, 'Handheld Percussion Pack', 'Tambourines, shakers, and more.', 'Meinl', 49.99, 30, FALSE, 'handheld percussion.jpeg'),
(4, 'Flute Student Model', 'Perfect flute for beginners.', 'Yamaha', 149.99, 15, FALSE, 'flute.jpeg'),
(4, 'Alto Saxophone', 'Beautiful alto sax with case.', 'Selmer', 899.99, 3, FALSE, 'saxophone.jpeg'),
(4, 'Trumpet Brass', 'Standard Bb trumpet.', 'Bach', 349.99, 6, FALSE, 'trumpet.jpeg'),
(4, 'Wind Instruments Collection', 'Miscellaneous wind instruments.', 'Generic', 129.99, 10, FALSE, 'wind instruments.jpeg'),
(5, 'Violin 4/4 Student', 'Full size violin complete outfit.', 'Cremona', 199.99, 10, FALSE, 'violin.jpeg'),
(5, 'Cello Standard', 'Student cello with bow and bag.', 'Stentor', 499.99, 4, FALSE, 'cello.jpeg'),
(5, 'Classic Harp', 'Small Celtic harp.', 'Harpsicle', 699.99, 2, FALSE, 'harp.jpeg'),
(5, 'String Instruments Pack', 'Set of spare strings and rosin.', 'DAddario', 29.99, 50, FALSE, 'string instruments,.jpeg'),
(6, 'Guitar Accessories Pack', 'Picks, strap, and tuner.', 'Ernie Ball', 19.99, 100, FALSE, 'accessories.jpeg'),
(7, 'Classical Mastery Sheet', 'Beethoven Symphony No.5 PDF', 'PublisherX', 5.99, 9999, TRUE, 'digital sheet music.jpeg'),
(7, 'Jazz Standards Book', 'Digital download PDF format.', 'Hal Leonard', 14.99, 9999, TRUE, 'download.jpeg');

-- Insert digital products mapping
INSERT INTO digital_products (product_id, file_path) VALUES 
(20, 'assets/digital/beethoven_sym5.pdf'),
(21, 'assets/digital/jazz_standards.pdf');
