<?php


session_start();


if (!isset($_SESSION['user'])) {

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0;
            }
            .error-box {
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                text-align: center;
                max-width: 400px;
            }
            .error-box h1 {
                color: #e74c3c;
                margin-bottom: 20px;
            }
            .error-box p {
                color: #666;
                margin-bottom: 30px;
            }
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background: #2ecc71;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
            }
            .btn:hover {
                background: #27ae60;
            }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>🔒 Access Denied</h1>
            <p>You must be logged in to access this page.</p>
            <a href="/login.php" class="btn">Go to Login</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

?>