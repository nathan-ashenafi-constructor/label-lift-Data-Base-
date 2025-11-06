<?php
// 1. Include our database connection script
include 'db_connect.php'; // $conn is now available

// 2. Get data from the form using the $_POST superglobal
$name = $_POST['name'];
$barcode = $_POST['barcode'];
$size_of_serving = $_POST['size_of_serving'];
$category_id = $_POST['category_id'];
$brand_id = $_POST['brand_id'];

// 3. Check if required fields are there
if (empty($name) || empty($barcode) || empty($category_id) || empty($brand_id)) {
    die("Error: Required fields missing.");
}

// 4. Prepare the SQL query (using prepared statements is safer!)
$stmt = $conn->prepare(
    "INSERT INTO Products (name, barcode, size_of_serving, category_id, brand_id) 
     VALUES (?, ?, ?, ?, ?)"
);

// "ssiii" means (String, String, String, Integer, Integer)
$stmt->bind_param("ssiii", 
    $name, 
    $barcode, 
    $size_of_serving, 
    $category_id, 
    $brand_id
);

// 5. Execute the query
if ($stmt->execute()) {
    // 6. Success! Redirect to the feedback page
    header("Location: feedback.html");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

// 7. Close connections
$stmt->close();
$conn->close();

?>