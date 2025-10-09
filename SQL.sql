DROP TABLE IF EXISTS ProductAlternatives;
DROP TABLE IF EXISTS ProductTags;
DROP TABLE IF EXISTS Scans;
DROP TABLE IF EXISTS NutritionFacts;
DROP TABLE IF EXISTS Fresh;
DROP TABLE IF EXISTS Packaged;
DROP TABLE IF EXISTS Products;
DROP TABLE IF EXISTS ConventionalBrands;
DROP TABLE IF EXISTS OrganicBrands;
DROP TABLE IF EXISTS Brands;
DROP TABLE IF EXISTS Tags;
DROP TABLE IF EXISTS Food;
DROP TABLE IF EXISTS Drink;
DROP TABLE IF EXISTS Categories;
DROP TABLE IF EXISTS HealthConsciousUser;
DROP TABLE IF EXISTS CasualUser;
DROP TABLE IF EXISTS FitnessUser;
DROP TABLE IF EXISTS Users;

CREATE TABLE Users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    joining_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    CONSTRAINT chk_email CHECK (email LIKE '%@%.%')
);

CREATE TABLE FitnessUser (
    user_id INT PRIMARY KEY,
    protein_goal DECIMAL(5,2) NOT NULL,
    CONSTRAINT fk_fitness_user FOREIGN KEY (user_id) 
        REFERENCES Users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_protein_goal CHECK (protein_goal > 0)
);

CREATE TABLE CasualUser (
    user_id INT PRIMARY KEY,
    usage_frequency ENUM('daily', 'weekly', 'monthly', 'rarely') NOT NULL,
    CONSTRAINT fk_casual_user FOREIGN KEY (user_id) 
        REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE HealthConsciousUser (
    user_id INT PRIMARY KEY,
    health_focus VARCHAR(100) NOT NULL,
    CONSTRAINT fk_health_user FOREIGN KEY (user_id) 
        REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE Categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) UNIQUE NOT NULL,
    health_standing ENUM('healthy', 'moderate', 'unhealthy') DEFAULT 'moderate'
);

CREATE TABLE Drink (
    category_id INT PRIMARY KEY,
    drink_type VARCHAR(50) NOT NULL,
    CONSTRAINT fk_drink_category FOREIGN KEY (category_id) 
        REFERENCES Categories(category_id) ON DELETE CASCADE
);

CREATE TABLE Food (
    category_id INT PRIMARY KEY,
    food_type VARCHAR(50) NOT NULL,
    CONSTRAINT fk_food_category FOREIGN KEY (category_id) 
        REFERENCES Categories(category_id) ON DELETE CASCADE
);

CREATE TABLE Brands (
    brand_id INT PRIMARY KEY AUTO_INCREMENT,
    brand_name VARCHAR(100) NOT NULL,
    manufacturer VARCHAR(100)
);

CREATE TABLE OrganicBrands (
    brand_id INT PRIMARY KEY,
    certification_date DATE,
    CONSTRAINT fk_organic_brand FOREIGN KEY (brand_id) 
        REFERENCES Brands(brand_id) ON DELETE CASCADE
);

CREATE TABLE ConventionalBrands (
    brand_id INT PRIMARY KEY,
    preservatives BOOLEAN DEFAULT FALSE,
    CONSTRAINT chk_preservatives CHECK (preservatives IN (0, 1)),
    CONSTRAINT fk_conventional_brand FOREIGN KEY (brand_id) 
        REFERENCES Brands(brand_id) ON DELETE CASCADE
);

CREATE TABLE Tags (
    tag_id INT PRIMARY KEY AUTO_INCREMENT,
    tag_name VARCHAR(50) UNIQUE NOT NULL,
    tag_type VARCHAR(50) NOT NULL
);

CREATE TABLE Products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    barcode VARCHAR(50) UNIQUE,
    size_of_serving VARCHAR(50),
    category_id INT NOT NULL,
    brand_id INT NOT NULL,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) 
        REFERENCES Categories(category_id) ON DELETE RESTRICT,
    CONSTRAINT fk_product_brand FOREIGN KEY (brand_id) 
        REFERENCES Brands(brand_id) ON DELETE RESTRICT
);

CREATE TABLE Packaged (
    product_id INT PRIMARY KEY,
    shelf_life INT NOT NULL,
    CONSTRAINT fk_packaged_product FOREIGN KEY (product_id) 
        REFERENCES Products(product_id) ON DELETE CASCADE,
    CONSTRAINT chk_shelf_life CHECK (shelf_life > 0)
);

CREATE TABLE Fresh (
    product_id INT PRIMARY KEY,
    expiry_date DATE NOT NULL,
    CONSTRAINT fk_fresh_product FOREIGN KEY (product_id) 
        REFERENCES Products(product_id) ON DELETE CASCADE
);

CREATE TABLE NutritionFacts (
    product_id INT PRIMARY KEY,
    calorie INT NOT NULL,
    fats DECIMAL(5,2),
    sodium DECIMAL(5,2),
    sugars DECIMAL(5,2),
    protein DECIMAL(5,2),
    dietary_fiber DECIMAL(5,2),
    fitness_score INT, 
    weightlifter_score INT,
    regular_score INT,
    avrg_score INT,
    dietary_restriction VARCHAR(255),
    CONSTRAINT fk_nutrition_product FOREIGN KEY (product_id) 
        REFERENCES Products(product_id) ON DELETE CASCADE,
    CONSTRAINT chk_calorie CHECK (calorie >= 0),
    CONSTRAINT chk_fitness_score CHECK (fitness_score BETWEEN 0 AND 100),
    CONSTRAINT chk_weightlifter_score CHECK (weightlifter_score BETWEEN 0 AND 100),
    CONSTRAINT chk_regular_score CHECK (regular_score BETWEEN 0 AND 100)
);

CREATE TABLE Scans (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    scan_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, product_id, scan_date),
    CONSTRAINT fk_scan_user FOREIGN KEY (user_id) 
        REFERENCES Users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_scan_product FOREIGN KEY (product_id) 
        REFERENCES Products(product_id) ON DELETE CASCADE
);

CREATE TABLE ProductTags (
    product_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (product_id, tag_id),
    CONSTRAINT fk_producttag_product FOREIGN KEY (product_id) 
        REFERENCES Products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_producttag_tag FOREIGN KEY (tag_id) 
        REFERENCES Tags(tag_id) ON DELETE CASCADE
);

CREATE TABLE ProductAlternatives (
    product_id INT NOT NULL,
    alternative_product_id INT NOT NULL,
    PRIMARY KEY (product_id, alternative_product_id),
    CONSTRAINT fk_alt_product1 FOREIGN KEY (product_id) 
        REFERENCES Products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_alt_product2 FOREIGN KEY (alternative_product_id) 
        REFERENCES Products(product_id) ON DELETE CASCADE,
    CONSTRAINT chk_different_products CHECK (product_id != alternative_product_id)
);

INSERT INTO Users (username, email) VALUES ('alice', 'alice@example.com');

INSERT INTO FitnessUser (user_id, protein_goal) VALUES (1, 120.5);

INSERT INTO Categories (category_name, health_standing) VALUES ('Snacks', 'healthy');

INSERT INTO Brands (brand_name, manufacturer) VALUES ('GoodFoods', 'Good Inc.');

INSERT INTO Products (name, barcode, size_of_serving, category_id, brand_id) 
VALUES ('Granola Bar', '123456', '50g', 1, 1);

INSERT INTO NutritionFacts (product_id, calorie, protein) VALUES (1, 200, 5.5);

DELETE FROM Scans;
DELETE FROM FitnessUser;
DELETE FROM CasualUser;
DELETE FROM HealthConsciousUser;
DELETE FROM Users;

DELETE FROM ProductAlternatives;
DELETE FROM ProductTags;
DELETE FROM Scans;
DELETE FROM NutritionFacts;
DELETE FROM Fresh;
DELETE FROM Packaged;
DELETE FROM Products;
DELETE FROM ConventionalBrands;
DELETE FROM OrganicBrands;
DELETE FROM Brands;
DELETE FROM Tags;
DELETE FROM Food;
DELETE FROM Drink;
DELETE FROM Categories;
DELETE FROM HealthConsciousUser;
DELETE FROM CasualUser;
DELETE FROM FitnessUser;
DELETE FROM Users;

INSERT INTO Categories (category_id, category_name, health_standing) VALUES
(1, 'Beverages', 'moderate'),
(2, 'Snacks', 'unhealthy'),
(3, 'Dairy', 'healthy');

INSERT INTO Brands (brand_id, brand_name, manufacturer) VALUES
(1, 'HealthyChoice', 'Healthy Foods Inc'),
(2, 'NutriLife', 'NutriLife Corp'),
(3, 'OrganicValley', 'Organic Valley Co'),
(4, 'SnackTime', 'Snack Foods Ltd');

INSERT INTO Products (product_id, name, barcode, size_of_serving, category_id, brand_id) VALUES
(1, 'Orange Juice Regular', '111111', '250ml', 1, 1),
(2, 'Orange Juice Premium', '111112', '250ml', 1, 2),
(3, 'Apple Juice', '111113', '250ml', 1, 3),
(4, 'Grape Juice', '111114', '250ml', 1, 1),
(5, 'Potato Chips Regular', '222221', '100g', 2, 4),
(6, 'Baked Chips', '222222', '100g', 2, 1),
(7, 'Veggie Chips', '222223', '100g', 2, 2),
(8, 'Corn Chips', '222224', '100g', 2, 4),
(9, 'Whole Milk', '333331', '200ml', 3, 1),
(10, 'Low-Fat Milk', '333332', '200ml', 3, 2),
(11, 'Skim Milk', '333333', '200ml', 3, 3),
(12, 'Almond Milk', '333334', '200ml', 3, 2);

INSERT INTO NutritionFacts (product_id, calorie, fats, sodium, sugars, protein, dietary_fiber, fitness_score, weightlifter_score, regular_score ,dietary_restriction) VALUES
(1, 110, 0, 10, 22, 2, 0, 65,70,60, NULL),
(2, 100, 0, 5, 20, 2, 1, 75,70,60, NULL),
(3, 95, 0, 8, 18, 1, 2, 80,45,60, 'Vegan'),
(4, 120, 0, 15, 28, 1, 0, 60,50,65 ,NULL),
(5, 540, 35, 170, 1, 7, 3, 30,40,35, NULL),
(6, 450, 20, 140, 2, 6, 4, 55,60,50 ,'Gluten-Free'),
(7, 480, 22, 120, 3, 5, 5, 60,50,55, 'Vegan'),
(8, 520, 30, 200, 2, 6, 2, 35,40,30, NULL),
(9, 150, 8, 125, 12, 8, 0, 70,65,60, NULL),
(10, 100, 2.5, 125, 12, 8, 0, 80,75,70, NULL),
(11, 80, 0, 130, 12, 8, 0, 85,80,75, NULL),
(12, 60, 2.5, 180, 7, 1, 1, 75,80,70, 'Vegan');

INSERT INTO Users (username, email) VALUES ('john_fit', 'john@email.com');
INSERT INTO FitnessUser (user_id, protein_goal) VALUES (LAST_INSERT_ID(), 150.00);

INSERT INTO Users (username, email) VALUES ('jane_fit', 'jane@email.com');
INSERT INTO FitnessUser (user_id, protein_goal) VALUES (LAST_INSERT_ID(), 145.00);

INSERT INTO Users (username, email) VALUES ('bob_fit', 'bob@email.com');
INSERT INTO FitnessUser (user_id, protein_goal) VALUES (LAST_INSERT_ID(), 155.00);

INSERT INTO Users (username, email) VALUES ('alice_fit', 'alice@email.com');
INSERT INTO FitnessUser (user_id, protein_goal) VALUES (LAST_INSERT_ID(), 170.00);

INSERT INTO Users (username, email) VALUES ('charlie_fit', 'charlie@email.com');
INSERT INTO FitnessUser (user_id, protein_goal) VALUES (LAST_INSERT_ID(), 148.00);

SELECT * FROM Users;
SELECT * FROM Products;
SELECT * FROM NutritionFacts;
-- FIND PRODUCTS ALTERNATIVES FOR REGULAR USERS 
SELECT 
    p1.product_id AS original_product,
    p1.name AS original_name,
    p2.product_id AS alternative_product,
    p2.name AS alternative_name,
    nf1.regular_score AS original_regular_score,
    nf2.regular_score AS alternative_regular_score,
    (nf2.regular_score - nf1.regular_score) AS health_improvement
FROM Products p1
JOIN Products p2 ON p1.category_id = p2.category_id AND p1.product_id != p2.product_id
JOIN NutritionFacts nf1 ON p1.product_id = nf1.product_id
JOIN NutritionFacts nf2 ON p2.product_id = nf2.product_id
WHERE nf2.regular_score > nf1.regular_score
ORDER BY p1.product_id, nf2.regular_score DESC;
-- FIND PRODUCT ALTERNATIVES FOR FITNESS USERS
SELECT 
    p1.product_id AS original_product,
    p1.name AS original_name,
    p2.product_id AS alternative_product,
    p2.name AS alternative_name,
    nf1.fitness_score AS original_fitness_score,
    nf2.fitness_score AS alternative_fitness_score,
    (nf2.fitness_score - nf1.fitness_score) AS health_improvement
FROM Products p1
JOIN Products p2 ON p1.category_id = p2.category_id AND p1.product_id != p2.product_id
JOIN NutritionFacts nf1 ON p1.product_id = nf1.product_id
JOIN NutritionFacts nf2 ON p2.product_id = nf2.product_id
WHERE nf2.fitness_score > nf1.fitness_score
ORDER BY p1.product_id, nf2.fitness_score DESC;
--FIND PRODUCT ALTERNATIVES FOR WEIGHTLIFTERS 
SELECT 
    p1.product_id AS original_product,
    p1.name AS original_name,
    p2.product_id AS alternative_product,
    p2.name AS alternative_name,
    nf1.weightlifter_score AS original_weightlifter_score,
    nf2.weightlifter_score AS alternative_weightlifter_score,
    (nf2.weightlifter_score - nf1.weightlifter_score) AS health_improvement
FROM Products p1
JOIN Products p2 ON p1.category_id = p2.category_id AND p1.product_id != p2.product_id
JOIN NutritionFacts nf1 ON p1.product_id = nf1.product_id
JOIN NutritionFacts nf2 ON p2.product_id = nf2.product_id
WHERE nf2.weightlifter_score > nf1.weightlifter_score
ORDER BY p1.product_id, nf2.weightlifter_score DESC;
--FIND USERS WITH SIMILAR PROTEIN GOALS 
SELECT 
    u1.user_id AS user1_id,
    u1.username AS user1_name,
    fu1.protein_goal AS user1_protein,
    u2.user_id AS user2_id,
    u2.username AS user2_name,
    fu2.protein_goal AS user2_protein,
    ABS(fu1.protein_goal - fu2.protein_goal) AS difference
FROM Users u1
JOIN FitnessUser fu1 ON u1.user_id = fu1.user_id
JOIN Users u2 ON u1.user_id < u2.user_id
JOIN FitnessUser fu2 ON u2.user_id = fu2.user_id
WHERE ABS(fu1.protein_goal - fu2.protein_goal) <= 10
ORDER BY difference ASC;

DELETE FROM Scans;

INSERT INTO Scans (user_id, product_id, scan_date) VALUES
(2, 3, CURRENT_TIMESTAMP),
(2, 11, CURRENT_TIMESTAMP),
(2,6, CURRENT_TIMESTAMP),
(3, 4, CURRENT_TIMESTAMP),
(3, 12, CURRENT_TIMESTAMP),
(2, 5, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)),
(2, 7, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY));

UPDATE NutritionFacts SET protein = 8.50 WHERE product_id = 3;
UPDATE NutritionFacts SET protein = 6.00 WHERE product_id = 4;
UPDATE NutritionFacts SET protein = 3.00 WHERE product_id = 5;
UPDATE NutritionFacts SET protein = 4.50 WHERE product_id = 11;
UPDATE NutritionFacts SET protein = 5.00 WHERE product_id = 12;
UPDATE NutritionFacts SET protein = 4.00 WHERE product_id = 6;
UPDATE NutritionFacts SET protein = 8.00 WHERE product_id = 7;
--FIND TOTAL PROTEIN CONSUMED PER USER
SELECT 
    u.user_id,
    u.username,
    DATE(s.scan_date) AS scan_date,
    SUM(nf.protein) AS total_protein,
    fu.protein_goal,
    COUNT(DISTINCT p.product_id) AS products_scanned
FROM Users u
JOIN FitnessUser fu ON u.user_id = fu.user_id
JOIN Scans s ON u.user_id = s.user_id
JOIN Products p ON s.product_id = p.product_id
JOIN NutritionFacts nf ON p.product_id = nf.product_id
GROUP BY u.user_id, u.username, DATE(s.scan_date), fu.protein_goal
HAVING SUM(nf.protein) > 0
ORDER BY scan_date DESC, total_protein DESC;

DELETE FROM NutritionFacts;
DELETE FROM Packaged;
DELETE FROM Products;
DELETE FROM Food;
DELETE FROM Brands;
DELETE FROM  Categories;

INSERT INTO Categories (category_id, category_name, health_standing) VALUES
(1, 'Snacks', 'unhealthy'),
(2, 'Cereals', 'moderate'),
(3, 'Canned Goods', 'moderate');

INSERT INTO Food (category_id, food_type) VALUES
(1, 'Chips'),
(2, 'Breakfast Cereal'),
(3, 'Canned Vegetables');

INSERT INTO Brands (brand_id, brand_name, manufacturer) VALUES
(1, 'GoodFoods', 'Good Inc.'),
(2, 'NutriLife', 'NutriLife Corp'),
(3, 'OrganicValley', 'Organic Valley Co');

INSERT INTO Products (product_id, name, barcode, size_of_serving, category_id, brand_id) VALUES
(1, 'Potato Chips', '101', '30g', 1, 1),
(2, 'Corn Chips', '102', '30g', 1, 1),
(3, 'Pretzels', '103', '30g', 1, 1),
(4, 'Corn Flakes', '201', '40g', 2, 2),
(5, 'Oat Rings', '202', '40g', 2, 2),
(6, 'Wheat Flakes', '203', '40g', 2, 2),
(7, 'Canned Corn', '301', '100g', 3, 3),
(8, 'Canned Peas', '302', '100g', 3, 3),
(9, 'Canned Beans', '303', '100g', 3, 3);

INSERT INTO Packaged (product_id, shelf_life) VALUES
(1, 180), (2, 180), (3, 180),
(4, 365), (5, 365), (6, 365),
(7, 730), (8, 730), (9, 730);

INSERT INTO NutritionFacts (product_id, calorie, sodium, protein, fitness_score, weightlifter_score, regular_score) VALUES
(1, 540, 280.00, 3.00, 30, 40, 35),
(2, 520, 320.00, 4.00, 35, 40, 30),
(3, 480, 350.00, 5.00, 60, 50, 55),
(4, 380, 180.00, 6.00, 60, 55, 65),
(5, 370, 150.00, 7.00, 65, 60, 70),
(6, 360, 140.00, 8.00, 70, 65, 75),
(7, 80, 120.00, 2.00, 75, 70, 75),
(8, 70, 100.00, 3.00, 80, 75, 80),
(9, 90, 110.00, 4.00, 70, 65, 75);
--FIND PRODUCTS WITH LEAST SODIUM
SELECT 
    c.category_name,
    f.food_type,
    COUNT(p.product_id) AS product_count,
    AVG(nf.sodium) AS avg_sodium
FROM Products p
JOIN Categories c ON p.category_id = c.category_id
JOIN Food f ON c.category_id = f.category_id
JOIN Packaged pk ON p.product_id = pk.product_id
JOIN NutritionFacts nf ON p.product_id = nf.product_id
GROUP BY c.category_id, c.category_name, f.food_type
ORDER BY AVG(nf.sodium) ASC;

DELETE FROM Scans;

INSERT INTO Scans (user_id, product_id, scan_date) VALUES
(2, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)),
(2, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 DAY)),
(3, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 DAY)),
(4, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 4 DAY)),
(4, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY)),
(4, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 6 DAY)),
(2, 2, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)),
(2, 2, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 DAY)),
(3, 2, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 DAY));
--SELECT PRODUCTS WITH MOST SCANS
SELECT 
    p.product_id,
    p.name,
    b.brand_name,
    COUNT(s.user_id) AS scan_count,
    COUNT(DISTINCT s.user_id) AS unique_users
FROM Products p
JOIN Scans s ON p.product_id = s.product_id
JOIN Brands b ON p.brand_id = b.brand_id
GROUP BY p.product_id, p.name, b.brand_name
ORDER BY scan_count DESC
LIMIT 10;

UPDATE NutritionFacts SET dietary_restriction = 'Vegan' WHERE product_id IN (1, 2, 17, 18, 19);
UPDATE NutritionFacts SET dietary_restriction = 'Gluten-Free' WHERE product_id IN (3, 4, 7);
UPDATE NutritionFacts SET dietary_restriction = 'Lactose-Free' WHERE product_id IN (5, 6);
UPDATE NutritionFacts SET dietary_restriction = 'Vegan, Gluten-Free' WHERE product_id IN (14, 15, 16);
--FIND GOOD FOOD FOR REGULAR USERS WITH DIETARY RESTRICTIONS
SELECT 
    c.category_name,
    nf.dietary_restriction,
    COUNT(p.product_id) AS product_count,
    AVG(nf.regular_score) AS avg_regular_score
FROM Products p
JOIN Categories c ON p.category_id = c.category_id
JOIN NutritionFacts nf ON p.product_id = nf.product_id
WHERE nf.dietary_restriction IS NOT NULL
GROUP BY c.category_id, c.category_name, nf.dietary_restriction
HAVING AVG(nf.regular_score) > 60
ORDER BY avg_regular_score DESC;
--FIND GOOD FOOD FOR WEIGHTLIFTING USERS WITH DIETARY RESTRICTIONS
SELECT 
    c.category_name,
    nf.dietary_restriction,
    COUNT(p.product_id) AS product_count,
    AVG(nf.weightlifter_score) AS avg_weightlifting_score
FROM Products p
JOIN Categories c ON p.category_id = c.category_id
JOIN NutritionFacts nf ON p.product_id = nf.product_id
WHERE nf.dietary_restriction IS NOT NULL
GROUP BY c.category_id, c.category_name, nf.dietary_restriction
HAVING AVG(nf.weightlifter_score) > 60
ORDER BY avg_weightlifting_score DESC;

--FIND GOOD PRODUCTS FOR FITNESS USERS WITH DIETARY RESTRICTIONS
SELECT 
    c.category_name,
    nf.dietary_restriction,
    COUNT(p.product_id) AS product_count,
    AVG(nf.fitness_score) AS avg_fitness_score
FROM Products p
JOIN Categories c ON p.category_id = c.category_id
JOIN NutritionFacts nf ON p.product_id = nf.product_id
WHERE nf.dietary_restriction IS NOT NULL
GROUP BY c.category_id, c.category_name, nf.dietary_restriction
HAVING AVG(nf.fitness_score) > 60
ORDER BY avg_fitness_score DESC;

DELETE FROM OrganicBrands;
DELETE FROM ConventionalBrands;

INSERT INTO OrganicBrands (brand_id, certification_date) VALUES
(1, '2024-01-15'),
(3, '2024-03-20');

INSERT INTO ConventionalBrands (brand_id, preservatives) VALUES
(2, TRUE);

--find organic food in a category 

SELECT 
    p.product_id,
    p.name,
    c.category_name,
    b.brand_name,
    b.manufacturer,
    ob.certification_date,
    nf.avrg_score
FROM Products p
JOIN Brands b ON p.brand_id = b.brand_id
JOIN OrganicBrands ob ON b.brand_id = ob.brand_id
JOIN Categories c ON p.category_id = c.category_id
JOIN NutritionFacts nf ON p.product_id = nf.product_id

ORDER BY nf.avrg_score DESC;

UPDATE Products SET barcode = '1234567890123' WHERE product_id = 1;

--find scanned product

SELECT 
    p.product_id,
    p.name,
    p.barcode,
    p.size_of_serving,
    b.brand_name,
    b.manufacturer,
    c.category_name,
    nf.calorie,
    nf.protein,
    nf.fats,
    nf.sodium,
    nf.avrg_score
FROM Products p
JOIN Brands b ON p.brand_id = b.brand_id
JOIN Categories c ON p.category_id = c.category_id
LEFT JOIN NutritionFacts nf ON p.product_id = nf.product_id
WHERE p.barcode = '1234567890123';