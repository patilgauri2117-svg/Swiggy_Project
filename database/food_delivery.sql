-- =====================================================
--  Swiggy - Food Delivery Website
--  Database: food_delivery
--  Import this file in phpMyAdmin (XAMPP) -> Import tab
-- =====================================================


-- -----------------------------------------------------
-- 1. users : customers and admin (role column decides)
-- -----------------------------------------------------
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    phone       VARCHAR(15)  NOT NULL,
    address     TEXT,
    password    VARCHAR(255) NOT NULL,          -- stores password_hash() output
    role        ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- 2. restaurants
-- -----------------------------------------------------
CREATE TABLE restaurants (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100) NOT NULL,
    image          VARCHAR(255) NOT NULL,
    cuisine        VARCHAR(150) NOT NULL,
    rating         DECIMAL(2,1) NOT NULL DEFAULT 4.0,
    delivery_time  INT NOT NULL DEFAULT 30,      -- in minutes
    location       VARCHAR(100) NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- 3. food_categories
-- -----------------------------------------------------
CREATE TABLE food_categories (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    name   VARCHAR(50)  NOT NULL,
    image  VARCHAR(255) NOT NULL
);

-- -----------------------------------------------------
-- 4. food_items
-- -----------------------------------------------------
CREATE TABLE food_items (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id  INT NOT NULL,
    category_id    INT NOT NULL,
    name           VARCHAR(100) NOT NULL,
    description    VARCHAR(255) NOT NULL,
    price          DECIMAL(10,2) NOT NULL,
    image          VARCHAR(255) NOT NULL,
    is_veg         TINYINT(1) NOT NULL DEFAULT 1,
    is_popular     TINYINT(1) NOT NULL DEFAULT 0,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id)   REFERENCES food_categories(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- 5. orders
-- -----------------------------------------------------
CREATE TABLE orders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    customer_name   VARCHAR(100) NOT NULL,
    phone           VARCHAR(15)  NOT NULL,
    address         TEXT NOT NULL,
    total_amount    DECIMAL(10,2) NOT NULL,
    payment_method  VARCHAR(30) NOT NULL DEFAULT 'Cash on Delivery',
    status          ENUM('Pending','Confirmed','Preparing','Out for Delivery','Delivered','Cancelled')
                    NOT NULL DEFAULT 'Pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- 6. order_items
-- (food_name and price are copied so old orders stay correct
--  even if the admin later edits or deletes a food item)
-- -----------------------------------------------------
CREATE TABLE order_items (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    order_id      INT NOT NULL,
    food_item_id  INT NULL,
    food_name     VARCHAR(100) NOT NULL,
    quantity      INT NOT NULL,
    price         DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)     REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE SET NULL
);

-- =====================================================
--  SAMPLE DATA
-- =====================================================

-- Admin login : admin@foodexpress.com / admin123
-- Customer    : rahul@example.com     / rahul123
INSERT INTO users (name, email, phone, address, password, role) VALUES
('Site Admin',   'admin@foodexpress.com', '9000000000', 'Swiggy Office, Pune', '$2y$10$0GjwBy4Ajl2sSrox.O0F0OVbCqUNCaU8NZi6ycABDeFSaTftNhx9a', 'admin'),
('Rahul Sharma', 'rahul@example.com',     '9876543210', 'Flat 12, Green Park Society, Baner, Pune - 411045', '$2y$10$YDT/lnOrN3Uj4.1s.3Ml..1JTwIxRCqSSsOhVmX0M7xlGWWhcu9tK', 'customer');

-- Category images are bundled in assets/images (they work offline)
INSERT INTO food_categories (id, name, image) VALUES
(1, 'Pizza',        'assets/images/pizza.svg'),
(2, 'Burger',       'assets/images/burger.svg'),
(3, 'Biryani',      'assets/images/biryani.svg'),
(4, 'Chinese',      'assets/images/chinese.svg'),
(5, 'South Indian', 'assets/images/south-indian.svg');

-- Restaurant and food photos load from Unsplash (internet needed).
-- If a photo cannot load, the website shows a bundled picture instead.
INSERT INTO restaurants (id, name, image, cuisine, rating, delivery_time, location) VALUES
(1, 'Pizza Palace',    'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600', 'Pizza, Italian, Fast Food',      4.3, 30, 'Koregaon Park'),
(2, 'Burger Barn',     'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=600', 'Burgers, American, Shakes',      4.1, 25, 'Viman Nagar'),
(3, 'Biryani House',   'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600', 'Biryani, Hyderabadi, Mughlai', 4.5, 35, 'Camp'),
(4, 'Dragon Wok',      'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600', 'Chinese, Asian, Noodles',     4.2, 30, 'Aundh'),
(5, 'Dosa Corner',     'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=600', 'South Indian, Dosa, Idli',       4.4, 20, 'Deccan'),
(6, 'Spice Junction',  'https://images.unsplash.com/photo-1424847651672-bf20a4b0982b?w=600', 'Biryani, North Indian, Chinese', 4.0, 40, 'Kothrud');

INSERT INTO food_items (restaurant_id, category_id, name, description, price, image, is_veg, is_popular) VALUES
-- Pizza Palace
(1, 1, 'Margherita Pizza',        'Classic cheese pizza with fresh tomato sauce and basil.',          199.00, 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=600', 1, 1),
(1, 1, 'Farmhouse Pizza',         'Loaded with capsicum, onion, tomato, mushroom and corn.',          299.00, 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600', 1, 0),
(1, 1, 'Pepperoni Pizza',         'Spicy chicken pepperoni with mozzarella cheese.',                  349.00, 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600', 0, 1),
(1, 1, 'Paneer Tikka Pizza',      'Tandoori paneer cubes, onion and green chillies.',                 329.00, 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600', 1, 0),
-- Burger Barn
(2, 2, 'Classic Veg Burger',      'Crispy veg patty with lettuce, tomato and mayo.',                  99.00,  'https://images.unsplash.com/photo-1550547660-d9450f859349?w=600', 1, 1),
(2, 2, 'Chicken Zinger Burger',   'Crunchy fried chicken fillet with spicy sauce.',                   169.00, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600', 0, 1),
(2, 2, 'Cheese Lava Burger',      'Double cheese burger with melting cheese inside.',                 149.00, 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=600', 1, 0),
(2, 2, 'Double Patty Burger',     'Two juicy chicken patties with caramelised onions.',               219.00, 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=600', 0, 0),
-- Biryani House
(3, 3, 'Hyderabadi Chicken Biryani', 'Dum-cooked basmati rice with tender chicken and spices.',       279.00, 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=600', 0, 1),
(3, 3, 'Mutton Biryani',          'Slow-cooked mutton pieces layered with fragrant rice.',            349.00, 'https://images.unsplash.com/photo-1589302168068-964664d93dc0?w=600', 0, 0),
(3, 3, 'Veg Dum Biryani',         'Mixed vegetables and saffron rice cooked on dum.',                 219.00, 'https://images.unsplash.com/photo-1631515243349-e0cb75fb8d3a?w=600', 1, 1),
(3, 3, 'Egg Biryani',             'Flavourful biryani rice with boiled masala eggs.',                 229.00, 'https://images.unsplash.com/photo-1642821373181-696a54913e93?w=600', 0, 0),
-- Dragon Wok
(4, 4, 'Veg Hakka Noodles',       'Stir-fried noodles with crunchy vegetables.',                      159.00, 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=600', 1, 1),
(4, 4, 'Chicken Fried Rice',      'Wok-tossed rice with chicken, egg and spring onions.',             189.00, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=600', 0, 0),
(4, 4, 'Veg Manchurian',          'Vegetable balls in a tangy soya-garlic gravy.',                    169.00, 'https://images.unsplash.com/photo-1645696301019-35adcc18fc21?w=600', 1, 0),
(4, 4, 'Chicken Momos',           'Steamed dumplings served with spicy red chutney.',                 139.00, 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=600', 0, 1),
-- Dosa Corner
(5, 5, 'Masala Dosa',             'Crispy dosa filled with potato masala, chutney and sambar.',       99.00,  'https://images.unsplash.com/photo-1630383249896-424e482df921?w=600', 1, 1),
(5, 5, 'Idli Sambar',             'Soft steamed idlis (3 pcs) with sambar and coconut chutney.',      69.00,  'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?w=600', 1, 0),
(5, 5, 'Medu Vada',               'Crispy lentil doughnuts (2 pcs) with sambar.',                     79.00,  'https://images.unsplash.com/photo-1626074353765-517a681e40be?w=600', 1, 0),
(5, 5, 'Onion Uttapam',           'Thick rice pancake topped with onions and chillies.',              109.00, 'https://images.unsplash.com/photo-1668236543090-82eba5ee5976?w=600', 1, 0),
-- Spice Junction
(6, 3, 'Chicken 65 Biryani',      'Biryani rice topped with spicy Chicken 65.',                       299.00, 'https://images.unsplash.com/photo-1701579231305-d84d8af9a3fd?w=600', 0, 0),
(6, 4, 'Schezwan Noodles',        'Hot and spicy noodles tossed in schezwan sauce.',                  179.00, 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=600', 1, 0),
(6, 2, 'Aloo Tikki Burger',       'Desi potato tikki burger with mint mayo.',                         89.00,  'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=600', 1, 0),
(6, 1, 'Corn & Cheese Pizza',     'Sweet corn and extra cheese on a thin crust.',                     249.00, 'https://images.unsplash.com/photo-1594007654729-407eedc4be65?w=600', 1, 0);

-- One sample order so the admin panel is not empty
-- (279 + 2 x 99 = 477 items + 40 delivery fee = 517)
INSERT INTO orders (user_id, customer_name, phone, address, total_amount, payment_method, status) VALUES
(2, 'Rahul Sharma', '9876543210', 'Flat 12, Green Park Society, Baner, Pune - 411045', 517.00, 'Cash on Delivery', 'Delivered');

INSERT INTO order_items (order_id, food_item_id, food_name, quantity, price) VALUES
(1, 9, 'Hyderabadi Chicken Biryani', 1, 279.00),
(1, 17, 'Masala Dosa', 2, 99.00);
