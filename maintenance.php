<?php
session_start();
$isLoggedIn = isset($_SESSION['user']);
$username = $isLoggedIn ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Maintenance - LabelLift</title>
    <link rel="stylesheet" type="text/css" href="/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #2ecc71;
            margin: 0;
        }
        
        .auth-status {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-badge {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-login {
            background: #2ecc71;
            color: white;
        }
        
        .btn-login:hover {
            background: #27ae60;
        }
        
        .btn-logout {
            background: #e74c3c;
            color: white;
        }
        
        .btn-logout:hover {
            background: #c0392b;
        }
        
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card h2 {
            color: #2ecc71;
            margin-bottom: 15px;
        }
        
        .card p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .card-link {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        
        .card-link:hover {
            background: #5568d3;
        }
        
        .login-warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px 20px;
            border-radius: 10px;
            border-left: 4px solid #ffc107;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗄️ Database Maintenance</h1>
            <div class="auth-status">
                <?php if ($isLoggedIn): ?>
                    <span class="user-badge">👤 <?php echo htmlspecialchars($username); ?></span>
                    <a href="/~mznaien/logout.php" class="btn btn-logout">Logout</a>
                <?php else: ?>
                    <a href="/~mznaien/login.php" class="btn btn-login">Login</a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!$isLoggedIn): ?>
            <div class="login-warning">
                <strong>⚠️ Notice:</strong> You must be logged in to add or modify data. 
                <a href="/~mznaien/login.php">Click here to login</a>.
            </div>
        <?php endif; ?>
        
        <div class="card-grid">
            <div class="card">
                <h2>📦 Products</h2>
                <p>Add new products to the database with barcode and serving information.</p>
                <a href="/products/add" class="card-link">Add Product</a>
            </div>
            
            <div class="card">
                <h2>🏷️ Brands</h2>
                <p>Manage product brands and manufacturers.</p>
                <a href="/brands/add" class="card-link">Add Brand</a>
            </div>
            
            <div class="card">
                <h2>📂 Categories</h2>
                <p>Organize products into categories with health standings.</p>
                <a href="/categories/add" class="card-link">Add Category</a>
            </div>
            
            <div class="card">
                <h2>🏷️ Tags</h2>
                <p>Create tags to label and filter products.</p>
                <a href="/tags/add" class="card-link">Add Tag</a>
            </div>
            
            <div class="card">
                <h2>📊 Nutrition Facts</h2>
                <p>Add detailed nutrition information for products.</p>
                <a href="/nutrition/add" class="card-link">Add Nutrition</a>
            </div>
            
            <div class="card">
                <h2>📸 Scans</h2>
                <p>Record product scans by users.</p>
                <a href="/scans/add" class="card-link">Add Scan</a>
            </div>
            
            <div class="card">
                <h2>🔄 Alternatives</h2>
                <p>Link products with healthier alternatives.</p>
                <a href="/alternatives/add" class="card-link">Add Alternative</a>
            </div>
            
            <div class="card">
                <h2>🔗 Product Tags</h2>
                <p>Associate tags with specific products.</p>
                <a href="/product-tags/add" class="card-link">Add Product Tag</a>
            </div>
            
            <div class="card">
                <h2>👥 Users</h2>
                <p>Add and manage different types of users.</p>
                <a href="user/add" class="card-link">Add User</a>
            </div>
            
            <div class="card">
                <h2>📸 Scanner</h2>
                <p>Scan nutrition labels with your camera.</p>
                <a href="/~mznaien/scanner.html" class="card-link">Open Scanner</a>
            </div>
            
            <div class="card">
                <h2>🏠 Home</h2>
                <p>Return to the main page.</p>
                <a href="/~mznaien/index.html" class="card-link">Go Home</a>
            </div>
            
            <div class="card">
                <h2>🔧 Test System</h2>
                <p>Test PHP and database connectivity.</p>
                <a href="/test.php" class="card-link">Run Tests</a>
            </div>
        </div>
    </div>
</body>
</html>