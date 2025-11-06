<?php
// login.php - Login page
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Load database config FIRST
require_once dirname(__FILE__) . '/db_config.php';
require_once dirname(__FILE__) . '/db_functions.php';

// If already logged in, redirect to maintenance
if (isset($_SESSION['user'])) {
    header('Location: maintenance.php');
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Query database for user
        $conn = get_db_connection();
        
        if (!$conn) {
            $error = 'Cannot connect to database';
        } else {
            $stmt = $conn->prepare("SELECT password_hash FROM Users WHERE username = ?");
            
            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->bind_result($password_hash);
                $stmt->fetch();
                $stmt->close();
                
                // Verify password
                if ($password_hash && hash('sha256', $password) === $password_hash) {
                    // Login successful
                    $_SESSION['user'] = $username;
                    $_SESSION['login_time'] = time();
                    
                    // Redirect to maintenance page
                    header('Location: maintenance.php');
                    exit();
                } else {
                    $error = 'Invalid username or password.';
                }
            } else {
                $error = 'Database error. Please try again.';
            }
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    $success = 'You have been logged out successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LabelLift</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #2ecc71;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2ecc71;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-login:hover {
            background: #27ae60;
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }
        
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2ecc71;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .default-creds {
            background: #fff9c4;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.9rem;
            border-left: 4px solid #fbc02d;
        }
        
        .default-creds strong {
            color: #f57c00;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔒 Login</h1>
            <p>LabelLift Maintenance Access</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                <strong>✓ Success:</strong> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">👤 Username:</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">🔑 Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login" class="btn-login">
                Login
            </button>
        </form>
        
        <div class="default-creds">
            <strong>Default Credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>mypassword123</code>
        </div>
        
        <div class="back-link">
            <a href="index.html">← Back to Home</a>
        </div>
    </div>
</body>
</html>