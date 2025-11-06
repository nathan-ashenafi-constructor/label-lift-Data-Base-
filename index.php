<?php
// index.php - Main entry point for the application

// Load database configuration
require_once 'db_config.php';

// Include database functions
require_once 'db_functions.php';

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Handle both root and user directory paths
// Remove script directory from URI (handles /~mznaien/ or /)
$base_path = dirname($script_name);
$path = str_replace($base_path, '', $request_uri);
$path = trim($path, '/');

// Remove query string
$path = strtok($path, '?');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Route handling
if (empty($path) || $path === 'index.php') {
    // Home page - check if index.html exists, otherwise show directory listing
    if (file_exists(BASE_DIR . '/index.html')) {
        serve_html_file('index.html');
    } else {
        // Show available files if index.html doesn't exist
        echo "<h1>LabelLift - Available Pages</h1>";
        echo "<p style='color: red;'>Note: index.html not found in " . BASE_DIR . "</p>";
        echo "<h2>Available HTML Files:</h2><ul>";
        $files = glob(BASE_DIR . '/*.html');
        foreach ($files as $file) {
            $filename = basename($file);
            echo "<li><a href='/$filename'>$filename</a></li>";
        }
        echo "</ul>";
        echo "<h2>Available Routes:</h2><ul>";
        echo "<li><a href='/products/add'>Add Product</a></li>";
        echo "<li><a href='/brands/add'>Add Brand</a></li>";
        echo "<li><a href='/categories/add'>Add Category</a></li>";
        echo "<li><a href='/tags/add'>Add Tag</a></li>";
        echo "<li><a href='/nutrition/add'>Add Nutrition Facts</a></li>";
        echo "<li><a href='/scans/add'>Add Scan</a></li>";
        echo "<li><a href='/alternatives/add'>Add Alternative</a></li>";
        echo "<li><a href='/product-tags/add'>Add Product Tags</a></li>";
        echo "<li><a href='/user/add'>Add User (Select Type)</a></li>";
        echo "<li><a href='/~mznaien/scanner.html'>Scanner</a></li>";
        echo "<li><a href='/~mznaien/test.php'>Test PHP</a></li>";
        echo "</ul>";
    }
} else {
    // Check if it's an API endpoint
    handle_routes($path);
}

function handle_routes($path) {
    global $_SERVER;
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Route mapping
    $routes = [
        'products/add' => 'handle_products',
        'brands/add' => 'handle_brands',
        'categories/add' => 'handle_categories',
        'tags/add' => 'handle_tags',
        'nutrition/add' => 'handle_nutrition',
        'scans/add' => 'handle_scans',
        'alternatives/add' => 'handle_alternatives',
        'product-tags/add' => 'handle_product_tags',
        'user/add' => 'handle_user_select',
        'fitness-user/add' => 'handle_fitness_user',
        'casual-user/add' => 'handle_casual_user',
        'health-user/add' => 'handle_health_user',
        'get-lower-sodium' => 'handle_lower_sodium',
        'get-alternatives' => 'handle_get_alternatives',
        'login.php' => 'serve_login',
        'logout.php' => 'serve_logout',
        'maintenance.php' => 'serve_maintenance'
    ];
    
    // Check if route exists
    if (array_key_exists($path, $routes)) {
        call_user_func($routes[$path]);
    } else {
        // Try to serve as static file
        serve_html_file($path);
    }
}

function serve_html_file($filename) {
    $filepath = BASE_DIR . '/' . $filename;
    
    if (file_exists($filepath)) {
        // Get file extension
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        
        // Set content type
        $content_types = [
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif'
        ];
        
        $content_type = isset($content_types[$ext]) ? $content_types[$ext] : 'text/plain';
        header('Content-Type: ' . $content_type);
        
        readfile($filepath);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "File not found: " . htmlspecialchars($filename);
    }
}

// ==================== Products ====================
function handle_products() {
    // Require authentication for POST (adding data)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'auth_check.php';
        
        $name = $_POST['name'] ?? null;
        $barcode = $_POST['barcode'] ?? null;
        $size_of_serving = $_POST['size_of_serving'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $brand_id = $_POST['brand_id'] ?? null;
        
        if ($name && $barcode && $category_id && $brand_id) {
            execute_query(
                "INSERT INTO Products (name, barcode, size_of_serving, category_id, brand_id) VALUES (?, ?, ?, ?, ?)",
                [$name, $barcode, $size_of_serving, $category_id, $brand_id]
            );
            serve_html_file('feedback.html');
        } else {
            http_response_code(400);
            echo "Required fields missing";
        }
    } else {
        serve_html_file('products_input.html');
    }
}

// ==================== Brands ====================
function handle_brands() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $brand_name = $_POST['brand_name'] ?? null;
        $manufacturer = $_POST['manufacturer'] ?? null;
        
        if ($brand_name && $manufacturer) {
            execute_query(
                "INSERT INTO Brands (brand_name, manufacturer) VALUES (?, ?)",
                [$brand_name, $manufacturer]
            );
            serve_html_file('feedback.html');
        } else {
            http_response_code(400);
            echo "Brand name and manufacturer required";
        }
    } else {
        serve_html_file('brands_input.html');
    }
}

// ==================== Categories ====================
function handle_categories() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $category_name = $_POST['category_name'] ?? null;
        $health_standing = $_POST['health_standing'] ?? null;
        
        if ($category_name && $health_standing) {
            execute_query(
                "INSERT INTO Categories (category_name, health_standing) VALUES (?, ?)",
                [$category_name, $health_standing]
            );
            serve_html_file('feedback.html');
        } else {
            http_response_code(400);
            echo "Category name and health standing required";
        }
    } else {
        serve_html_file('categories_input.html');
    }
}

// ==================== Tags ====================
function handle_tags() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tag_name = $_POST['tag_name'] ?? null;
        $tag_type = $_POST['tag_type'] ?? null;
        
        if ($tag_name && $tag_type) {
            execute_query(
                "INSERT INTO Tags (tag_name, tag_type) VALUES (?, ?)",
                [$tag_name, $tag_type]
            );
            serve_html_file('feedback.html');
        } else {
            http_response_code(400);
            echo "Tag name and type required";
        }
    } else {
        serve_html_file('tags_input.html');
    }
}

// ==================== Nutrition Facts ====================
function handle_nutrition() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_id = $_POST['product_id'] ?? null;
        $calorie = $_POST['calorie'] ?? null;
        $fats = $_POST['fats'] ?? null;
        $sodium = $_POST['sodium'] ?? null;
        $sugars = $_POST['sugars'] ?? null;
        $protein = $_POST['protein'] ?? null;
        $dietary_fiber = $_POST['dietary_fiber'] ?? null;
        $fitness_score = $_POST['fitness_score'] ?? null;
        $dietary_restriction = $_POST['dietary_restriction'] ?? null;
        
        if ($product_id && $calorie) {
            execute_query(
                "INSERT INTO NutritionFacts (product_id, calorie, fats, sodium, sugars, protein, dietary_fiber, fitness_score, dietary_restriction) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$product_id, $calorie, $fats, $sodium, $sugars, $protein, $dietary_fiber, $fitness_score, $dietary_restriction]
            );
            serve_html_file('feedback.html');
        } else {
            http_response_code(400);
            echo "Product ID and calorie required";
        }
    } else {
        serve_html_file('nutrition_input.html');
    }
}

// ==================== Scans ====================
function handle_scans() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['user_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        
        if ($user_id && $product_id) {
            $scan_date = date('Y-m-d H:i:s');
            execute_query(
                "INSERT INTO Scans (user_id, product_id, scan_date) VALUES (?, ?, ?)",
                [$user_id, $product_id, $scan_date]
            );
            serve_html_file('feedback.html');
        } else {
            http_response_code(400);
            echo "User ID and Product ID required";
        }
    } else {
        serve_html_file('scans_input.html');
    }
}

// ==================== Product Alternatives ====================
function handle_alternatives() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_id = $_POST['product_id'] ?? null;
        $alternative_product_id = $_POST['alternative_product_id'] ?? null;
        
        if ($product_id && $alternative_product_id && $product_id != $alternative_product_id) {
            execute_query(
                "INSERT INTO ProductAlternatives (product_id, alternative_product_id) VALUES (?, ?)",
                [$product_id, $alternative_product_id]
            );
            serve_html_file('feedback.html');
        } else {
            http_response_code(400);
            echo "Valid product IDs required";
        }
    } else {
        serve_html_file('alternatives_input.html');
    }
}

// ==================== Product Tags ====================
function handle_product_tags() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_id = $_POST['product_id'] ?? null;
        $tag_id = $_POST['tag_id'] ?? null;
        
        if ($product_id && $tag_id) {
            execute_query(
                "INSERT INTO ProductTags (product_id, tag_id) VALUES (?, ?)",
                [$product_id, $tag_id]
            );
            serve_html_file('feedback.html');
        } else {
            http_response_code(400);
            echo "Product ID and Tag ID required";
        }
    } else {
        serve_html_file('product_tags_input.html');
    }
}

// ==================== User Type Selection ====================
function handle_user_select() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_type = $_POST['user_type'] ?? null;
        
        if ($user_type === 'fitness') {
            header('Location: fitness-user/add');
        } elseif ($user_type === 'casual') {
            header('Location: casual-user/add');
        } elseif ($user_type === 'health') {
            header('Location: health-user/add');
        } else {
            http_response_code(400);
            echo "Invalid user type selected";
        }
        exit;
    } else {
        serve_html_file('user_type_select.html');
    }
}

// ==================== Fitness User ====================
function handle_fitness_user() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['user_id'] ?? null;
        $protein_goal = $_POST['protein_goal'] ?? null;
        
        if ($user_id) {
            $user_check = execute_query("SELECT user_id FROM Users WHERE user_id = ?", [$user_id], true);
            
            if (empty($user_check)) {
                execute_query("INSERT INTO Users (user_id) VALUES (?)", [$user_id]);
            }
            
            $fitness_check = execute_query("SELECT user_id FROM FitnessUser WHERE user_id = ?", [$user_id], true);
            if (!empty($fitness_check)) {
                http_response_code(400);
                echo "User already exists as a Fitness User";
                return;
            }
            
            execute_query("INSERT INTO FitnessUser (user_id, protein_goal) VALUES (?, ?)", [$user_id, $protein_goal]);
            serve_html_file('feedback.html');
        }
    } else {
        serve_html_file('fitness_user_input.html');
    }
}

// ==================== Casual User ====================
function handle_casual_user() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['user_id'] ?? null;
        $usage_frequency = $_POST['usage_frequency'] ?? null;
        
        if ($user_id) {
            $user_check = execute_query("SELECT user_id FROM Users WHERE user_id = ?", [$user_id], true);
            
            if (empty($user_check)) {
                execute_query("INSERT INTO Users (user_id) VALUES (?)", [$user_id]);
            }
            
            $casual_check = execute_query("SELECT user_id FROM CasualUser WHERE user_id = ?", [$user_id], true);
            if (!empty($casual_check)) {
                http_response_code(400);
                echo "User already exists as a Casual User";
                return;
            }
            
            execute_query("INSERT INTO CasualUser (user_id, usage_frequency) VALUES (?, ?)", [$user_id, $usage_frequency]);
            serve_html_file('feedback.html');
        }
    } else {
        serve_html_file('casual_user_input.html');
    }
}

// ==================== Health Conscious User ====================
function handle_health_user() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['user_id'] ?? null;
        $health_focus = $_POST['health_focus'] ?? null;
        
        if ($user_id) {
            $user_check = execute_query("SELECT user_id FROM Users WHERE user_id = ?", [$user_id], true);
            
            if (empty($user_check)) {
                execute_query("INSERT INTO Users (user_id) VALUES (?)", [$user_id]);
            }
            
            $health_check = execute_query("SELECT user_id FROM HealthConsciousUser WHERE user_id = ?", [$user_id], true);
            if (!empty($health_check)) {
                http_response_code(400);
                echo "User already exists as a Health Conscious User";
                return;
            }
            
            execute_query("INSERT INTO HealthConsciousUser (user_id, health_focus) VALUES (?, ?)", [$user_id, $health_focus]);
            serve_html_file('feedback.html');
        }
    } else {
        serve_html_file('health_user_input.html');
    }
}

// ==================== Get Lower Sodium Alternatives ====================
function handle_lower_sodium() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        
        // Get text from POST data
        $text = isset($_POST['text']) ? trim($_POST['text']) : '';
        
        if (empty($text)) {
            echo json_encode(['message' => 'No text provided']);
            return;
        }
        
        try {
            // Parse nutrition from text
            $nutrition = parse_nutrition_from_text($text);
            
            if (!isset($nutrition['sodium']) || $nutrition['sodium'] <= 0) {
                echo json_encode(['message' => 'No sodium data found in this product.']);
                return;
            }
            
            // Get lower sodium alternatives
            $alternatives = get_lower_sodium_alternatives($nutrition['sodium']);
            
            if (empty($alternatives)) {
                echo json_encode(['message' => 'No lower-sodium products found.']);
            } else {
                echo json_encode(['alternatives' => $alternatives]);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error processing request: ' . $e->getMessage()]);
        }
    }
}

// ==================== Get Alternatives ====================
function handle_get_alternatives() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        
        // Get data from POST
        $text = isset($_POST['text']) ? trim($_POST['text']) : '';
        $user_type = isset($_POST['user_type']) ? trim($_POST['user_type']) : 'casual';
        
        if (empty($text)) {
            echo json_encode(['message' => 'No text provided']);
            return;
        }
        
        try {
            // Parse nutrition from text
            $nutrition = parse_nutrition_from_text($text);
            
            if (empty($nutrition)) {
                echo json_encode(['message' => 'Could not extract nutrition information from the scanned text.']);
                return;
            }
            
            // Determine category from text
            $category_id = determine_product_category($text);
            
            // Get alternatives
            $alternatives = get_product_alternatives($category_id, $user_type, $nutrition);
            
            if (empty($alternatives)) {
                echo json_encode(['message' => 'No alternatives found in our database yet. Try adding more products!']);
            } else {
                echo json_encode([
                    'alternatives' => $alternatives,
                    'message' => 'Found ' . count($alternatives) . ' alternatives for ' . $user_type . ' users!'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error processing request: ' . $e->getMessage()]);
        }
    }
}

/**
 * Parse nutrition from text (PHP implementation)
 */
function parse_nutrition_from_text($text) {
    $text = strtolower($text);
    $nutrition = [];
    
    // Extract calories
    if (preg_match('/calories\s*[:\s]*(\d+(?:\.\d+)?)/', $text, $matches)) {
        $nutrition['calories'] = floatval($matches[1]);
    }
    
    // Extract sodium
    if (preg_match('/sodium\s*[:\s]*(\d+(?:\.\d+)?)\s*mg/', $text, $matches)) {
        $nutrition['sodium'] = floatval($matches[1]);
    }
    
    // Extract protein
    if (preg_match('/protein\s*[:\s]*(\d+(?:\.\d+)?)\s*g/', $text, $matches)) {
        $nutrition['protein'] = floatval($matches[1]);
    }
    
    // Extract sugars
    if (preg_match('/sugars?\s*[:\s]*(\d+(?:\.\d+)?)\s*g/', $text, $matches)) {
        $nutrition['sugars'] = floatval($matches[1]);
    }
    
    // Extract total fat
    if (preg_match('/total\s+fat\s*[:\s]*(\d+(?:\.\d+)?)\s*g/', $text, $matches)) {
        $nutrition['total_fat'] = floatval($matches[1]);
    }
    
    // Extract carbohydrates
    if (preg_match('/total\s+carbohydrate\s*[:\s]*(\d+(?:\.\d+)?)\s*g/', $text, $matches)) {
        $nutrition['total_carbohydrate'] = floatval($matches[1]);
    }
    
    // Extract fiber
    if (preg_match('/dietary\s+fiber\s*[:\s]*(\d+(?:\.\d+)?)\s*g/', $text, $matches)) {
        $nutrition['dietary_fiber'] = floatval($matches[1]);
    }
    
    // Calculate fitness score
    if (!empty($nutrition)) {
        $nutrition['fitness_score'] = calculate_fitness_score_php($nutrition);
    }
    
    return $nutrition;
}

/**
 * Calculate fitness score in PHP
 */
function calculate_fitness_score_php($nutrition) {
    $score = 100;
    
    if (isset($nutrition['calories']) && $nutrition['calories'] > 400) {
        $score -= ($nutrition['calories'] - 400) / 10;
    }
    
    if (isset($nutrition['sugars']) && $nutrition['sugars'] > 10) {
        $score -= ($nutrition['sugars'] - 10) * 2;
    }
    
    if (isset($nutrition['sodium']) && $nutrition['sodium'] > 500) {
        $score -= ($nutrition['sodium'] - 500) / 100;
    }
    
    if (isset($nutrition['protein']) && $nutrition['protein'] > 5) {
        $score += min($nutrition['protein'] * 2, 20);
    }
    
    if (isset($nutrition['dietary_fiber']) && $nutrition['dietary_fiber'] > 3) {
        $score += $nutrition['dietary_fiber'] * 3;
    }
    
    return max(0, min(100, $score));
}
?>