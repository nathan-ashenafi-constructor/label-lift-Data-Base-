<?php
// api_endpoints.php - Separate API endpoints for AJAX requests

header('Content-Type: application/json');
require_once 'db_functions.php';

// Get the request path
$request_uri = $_SERVER['REQUEST_URI'];
$path = trim(parse_url($request_uri, PHP_URL_PATH), '/');

// Route to appropriate handler
switch ($path) {
    case 'get-alternatives':
        handleGetAlternatives();
        break;
    case 'get-lower-sodium':
        handleGetLowerSodium();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

function handleGetAlternatives() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $text = $_POST['text'] ?? '';
    $user_type = $_POST['user_type'] ?? 'casual';
    
    if (empty($text)) {
        echo json_encode(['message' => 'No text provided']);
        return;
    }
    
    // Parse nutrition from text (basic implementation)
    $nutrition = parseNutritionFromText($text);
    
    if (empty($nutrition)) {
        echo json_encode(['message' => 'Could not extract nutrition information']);
        return;
    }
    
    // Determine category from text
    $category_id = determine_product_category($text);
    
    // Get alternatives
    $alternatives = get_product_alternatives($category_id, $user_type, $nutrition);
    
    if (empty($alternatives)) {
        echo json_encode(['message' => 'No alternatives found in our database yet.']);
    } else {
        echo json_encode([
            'alternatives' => $alternatives,
            'message' => 'Found ' . count($alternatives) . ' alternatives for ' . $user_type . ' users!'
        ]);
    }
}

function handleGetLowerSodium() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $text = $_POST['text'] ?? '';
    
    if (empty($text)) {
        echo json_encode(['message' => 'No text provided']);
        return;
    }
    
    // Parse nutrition from text
    $nutrition = parseNutritionFromText($text);
    
    if (!isset($nutrition['sodium'])) {
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
}

/**
 * Parse nutrition from text (basic PHP implementation)
 * This mimics the JavaScript parseNutritionFromText function
 */
function parseNutritionFromText($text) {
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
        $nutrition['fitness_score'] = calculateFitnessScore($nutrition);
    }
    
    return $nutrition;
}

/**
 * Calculate fitness score in PHP
 */
function calculateFitnessScore($nutrition) {
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