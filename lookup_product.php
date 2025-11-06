<?php
// File: lookup_product.php
// This file only speaks JSON. It receives JSON and returns JSON.
// It does NOT return HTML.

include 'db_connect.php';

// 1. Get the JSON data sent from the JavaScript 'fetch'
$data = json_decode(file_get_contents('php://input'), true);
$barcode = $data['barcode'];

if (empty($barcode)) {
    echo json_encode(['error' => 'No barcode provided.']);
    exit();
}

// 2. Prepare and run the SQL query
// We join with Brands to get the brand_name
$stmt = $conn->prepare("
    SELECT p.name, p.size_of_serving, b.brand_name 
    FROM Products p
    JOIN Brands b ON p.brand_id = b.brand_id
    WHERE p.barcode = ?
");
$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();

// 3. Tell the browser we are sending back JSON
header('Content-Type: application/json');

if ($row = $result->fetch_assoc()) {
    // 4. Success! Send the product data back as JSON
    echo json_encode($row);
} else {
    // 5. No product found. Send a JSON error.
    echo json_encode(['error' => 'Product not found in database.']);
}

$stmt->close();
$conn->close();
?>