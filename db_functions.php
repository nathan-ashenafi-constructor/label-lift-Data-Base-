<?php
// db_functions.php - Database connection and query functions

/**
 * Create and return a database connection
 * @return mysqli|null
 */
function get_db_connection() {
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($connection->connect_error) {
        error_log("Database connection failed: " . $connection->connect_error);
        return null;
    }
    
    return $connection;
}
/**
 * Execute a query and optionally fetch results
 * @param string $query SQL query
 * @param array|null $params Parameters for prepared statement
 * @param bool $fetch Whether to fetch results
 * @return array|int|null Returns fetched results, insert ID, or null on error
 */
function execute_query($query, $params = null, $fetch = false) {
    $connection = get_db_connection();
    
    if (!$connection) {
        return null;
    }
    
    try {
        $stmt = $connection->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $connection->error);
            $connection->close();
            return null;
        }
        
        // Bind parameters if provided
        if ($params && count($params) > 0) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        
        if ($fetch) {
            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $stmt->close();
            $connection->close();
            return $data;
        } else {
            $insert_id = $stmt->insert_id;
            $stmt->close();
            $connection->close();
            return $insert_id;
        }
    } catch (Exception $e) {
        error_log("Query error: " . $e->getMessage());
        if (isset($connection)) {
            $connection->close();
        }
        return null;
    }
}

/**
 * Determine product category from text
 * @param string $text Scanned text
 * @return int Category ID
 */
function determine_product_category($text) {
    $text_lower = strtolower($text);
    
    $keywords = [
        2 => ['juice', 'drink', 'beverage'],
        1 => ['cereal', 'grain', 'breakfast'],
        3 => ['yogurt', 'cheese', 'milk', 'dairy'],
        4 => ['candy', 'chocolate', 'sweet'],
        5 => ['snack', 'chip', 'cracker']
    ];
    
    foreach ($keywords as $category_id => $words) {
        foreach ($words as $word) {
            if (strpos($text_lower, $word) !== false) {
                return $category_id;
            }
        }
    }
    
    return 1; // Default category
}

/**
 * Calculate improvement between current and alternative product
 * @param array $current_nutrition Current product nutrition
 * @param array $alternative Alternative product nutrition
 * @return string Improvement description
 */
function calculate_improvement($current_nutrition, $alternative) {
    $improvements = [];
    
    // Calories
    if (isset($current_nutrition['calories']) && isset($alternative['calorie'])) {
        $cal_diff = $current_nutrition['calories'] - $alternative['calorie'];
        if ($cal_diff > 50) {
            $improvements[] = intval($cal_diff) . " fewer calories";
        }
    }
    
    // Sugars
    if (isset($current_nutrition['sugars']) && isset($alternative['sugars'])) {
        $sugar_diff = $current_nutrition['sugars'] - $alternative['sugars'];
        if ($sugar_diff > 2) {
            $improvements[] = number_format($sugar_diff, 1) . "g less sugar";
        }
    }
    
    // Sodium
    if (isset($current_nutrition['sodium']) && isset($alternative['sodium'])) {
        $sodium_diff = $current_nutrition['sodium'] - $alternative['sodium'];
        if ($sodium_diff > 100) {
            $improvements[] = intval($sodium_diff) . "mg less sodium";
        }
    }
    
    if (count($improvements) > 0) {
        return "✓ " . implode(" • ", $improvements);
    } else {
        return "✓ Healthier choice";
    }
}

/**
 * Get alternatives based on user type
 * @param int $category_id Product category
 * @param string $user_type Type of user (fitness, casual, weightlifter)
 * @param array $nutrition Current product nutrition
 * @return array List of alternative products
 */
function get_product_alternatives($category_id, $user_type, $nutrition) {
    $queries = [
        'fitness' => "
            SELECT p.product_id, p.name, p.barcode, c.category_id,
                   n.calorie, n.protein, n.sugars, n.sodium, n.fitness_score
            FROM Products p
            JOIN Categories c ON p.category_id = c.category_id
            JOIN NutritionFacts n ON p.product_id = n.product_id
            WHERE c.category_id = ? AND n.fitness_score > ?
            ORDER BY n.fitness_score DESC
            LIMIT 5
        ",
        'casual' => "
            SELECT p.product_id, p.name, p.barcode, c.category_id,
                   n.calorie, n.protein, n.sugars, n.sodium, n.fitness_score
            FROM Products p
            JOIN Categories c ON p.category_id = c.category_id
            JOIN NutritionFacts n ON p.product_id = n.product_id
            WHERE c.category_id = ? AND n.calorie < ?
            ORDER BY n.calorie ASC
            LIMIT 5
        ",
        'weightlifter' => "
            SELECT p.product_id, p.name, p.barcode, c.category_id,
                   n.calorie, n.protein, n.sugars, n.sodium, n.fitness_score
            FROM Products p
            JOIN Categories c ON p.category_id = c.category_id
            JOIN NutritionFacts n ON p.product_id = n.product_id
            WHERE c.category_id = ? AND n.protein > ?
            ORDER BY n.protein DESC
            LIMIT 5
        "
    ];
    
    $query = isset($queries[$user_type]) ? $queries[$user_type] : $queries['casual'];
    
    // Determine threshold
    $threshold = 0;
    if ($user_type === 'fitness') {
        $threshold = $nutrition['fitness_score'] ?? 0;
    } elseif ($user_type === 'casual') {
        $threshold = $nutrition['calories'] ?? 1000;
    } elseif ($user_type === 'weightlifter') {
        $threshold = $nutrition['protein'] ?? 0;
    }
    
    $results = execute_query($query, [$category_id, $threshold], true);
    
    if ($results) {
        $alternatives = [];
        foreach ($results as $result) {
            $alternatives[] = [
                'name' => $result['name'],
                'barcode' => $result['barcode'],
                'product_id' => $result['product_id'],
                'category_id' => $result['category_id'],
                'calories' => $result['calorie'],
                'protein' => $result['protein'],
                'sugars' => $result['sugars'],
                'sodium' => $result['sodium'],
                'improvement' => calculate_improvement($nutrition, $result)
            ];
        }
        return $alternatives;
    }
    
    return [];
}

/**
 * Get lower sodium alternatives
 * @param float $current_sodium Current product sodium
 * @return array List of lower sodium products
 */
function get_lower_sodium_alternatives($current_sodium) {
    $query = "
        SELECT p.product_id, p.name, n.calorie, n.protein, n.sugars, n.sodium
        FROM Products p
        JOIN NutritionFacts n ON p.product_id = n.product_id
        WHERE n.sodium < ?
        ORDER BY n.sodium ASC
        LIMIT 5
    ";
    
    $results = execute_query($query, [$current_sodium], true);
    
    if ($results) {
        $alternatives = [];
        foreach ($results as $p) {
            $improvement = round((1 - ($p['sodium'] / $current_sodium)) * 100, 1) . "% less sodium";
            $alternatives[] = [
                'name' => $p['name'],
                'calories' => $p['calorie'],
                'protein' => $p['protein'],
                'sugars' => $p['sugars'],
                'sodium' => $p['sodium'],
                'improvement' => $improvement
            ];
        }
        return $alternatives;
    }
    
    return [];
}
?>