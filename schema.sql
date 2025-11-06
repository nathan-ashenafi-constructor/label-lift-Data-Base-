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

CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    joining_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO Users (username, password_hash)
VALUES ('admin', SHA2('mypassword123', 256));






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

-- Regular user (balanced heuristic)
DROP VIEW IF EXISTS vw_regular_alternatives;
CREATE VIEW vw_regular_alternatives AS
SELECT
  p1.product_id AS original_product,
  p1.name       AS original_name,
  p2.product_id AS alternative_product,
  p2.name       AS alternative_name,
  (
    -0.004*COALESCE(nf1.calories,0)
    -0.050*COALESCE(nf1.sugars,0)
    -0.040*COALESCE(nf1.fat,0)
    -0.001*COALESCE(nf1.sodium,0)
    +0.030*COALESCE(nf1.protein,0)
  ) AS original_regular_score,
  (
    -0.004*COALESCE(nf2.calories,0)
    -0.050*COALESCE(nf2.sugars,0)
    -0.040*COALESCE(nf2.fat,0)
    -0.001*COALESCE(nf2.sodium,0)
    +0.030*COALESCE(nf2.protein,0)
  ) AS alternative_regular_score,
  (
    (
      -0.004*COALESCE(nf2.calories,0)
      -0.050*COALESCE(nf2.sugars,0)
      -0.040*COALESCE(nf2.fat,0)
      -0.001*COALESCE(nf2.sodium,0)
      +0.030*COALESCE(nf2.protein,0)
    )
    -
    (
      -0.004*COALESCE(nf1.calories,0)
      -0.050*COALESCE(nf1.sugars,0)
      -0.040*COALESCE(nf1.fat,0)
      -0.001*COALESCE(nf1.sodium,0)
      +0.030*COALESCE(nf1.protein,0)
    )
  ) AS health_improvement
FROM Products p1
JOIN Products p2
  ON p1.category_id = p2.category_id AND p1.product_id <> p2.product_id
JOIN NutritionFacts nf1 ON nf1.product_id = p1.product_id
JOIN NutritionFacts nf2 ON nf2.product_id = p2.product_id
WHERE
  (
    -0.004*COALESCE(nf2.calories,0)
    -0.050*COALESCE(nf2.sugars,0)
    -0.040*COALESCE(nf2.fat,0)
    -0.001*COALESCE(nf2.sodium,0)
    +0.030*COALESCE(nf2.protein,0)
  )
  >
  (
    -0.004*COALESCE(nf1.calories,0)
    -0.050*COALESCE(nf1.sugars,0)
    -0.040*COALESCE(nf1.fat,0)
    -0.001*COALESCE(nf1.sodium,0)
    +0.030*COALESCE(nf1.protein,0)
  );

-- Fitness user
DROP VIEW IF EXISTS vw_fitness_alternatives;
CREATE VIEW vw_fitness_alternatives AS
SELECT 
  p1.product_id AS original_product,
  p1.name       AS original_name,
  p2.product_id AS alternative_product,
  p2.name       AS alternative_name,
  nf1.fitness_score  AS original_fitness_score,
  nf2.fitness_score  AS alternative_fitness_score,
  (nf2.fitness_score - nf1.fitness_score) AS health_improvement
FROM Products p1
JOIN Products p2
  ON p1.category_id = p2.category_id AND p1.product_id <> p2.product_id
JOIN NutritionFacts nf1 ON nf1.product_id = p1.product_id
JOIN NutritionFacts nf2 ON nf2.product_id = p2.product_id
WHERE nf2.fitness_score > nf1.fitness_score;

-- Weightlifter
DROP VIEW IF EXISTS vw_weightlifter_alternatives;
CREATE VIEW vw_weightlifter_alternatives AS
SELECT 
  p1.product_id AS original_product,
  p1.name       AS original_name,
  p2.product_id AS alternative_product,
  p2.name       AS alternative_name,
  nf1.weightlifter_score AS original_weightlifter_score,
  nf2.weightlifter_score AS alternative_weightlifter_score,
  (nf2.weightlifter_score - nf1.weightlifter_score) AS health_improvement
FROM Products p1
JOIN Products p2
  ON p1.category_id = p2.category_id AND p1.product_id <> p2.product_id
JOIN NutritionFacts nf1 ON nf1.product_id = p1.product_id
JOIN NutritionFacts nf2 ON nf2.product_id = p2.product_id
WHERE nf2.weightlifter_score > nf1.weightlifter_score;