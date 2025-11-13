<?php

$pages = [
    '/index.php' => 80,
    '/products_input.html' => 60,
    '/scanner.html' => 55,
    '/login.php' => 50,
    '/api_endpoints.php' => 45,
    '/add_product.php' => 40,
    '/nutrition_input.html' => 35,
    '/brands_input.html' => 30,
    '/categories_input.html' => 25,
    '/lookup_product.php' => 25,
    '/logout.php' => 20,
    '/user_type_select.html' => 20,
    '/users_input.html' => 15,
    '/tags_input.html' => 15,
    '/products_tags_input.html' => 10,
    '/scans_input.html' => 10,
    '/alternatives_input.html' => 10,
    '/casual_user_input.html' => 8,
    '/fitness_user_input.html' => 8,
    '/health_user_input.html' => 8,
    '/feedback.html' => 5,
    '/imprint.html' => 3,
    '/maintenance.php' => 2,
    '/style.css' => 100,
    '/nutrition_extractor.js' => 50,
    '/img/logo.png' => 30,
    '/img/product1.jpg' => 20,
    '/favicon.ico' => 40
];

$ips = [
    '192.168.1.100', '192.168.1.101', '192.168.1.102', '192.168.1.103',
    '10.0.0.5', '10.0.0.6', '10.0.0.7', '10.0.0.8',
    '172.16.0.10', '172.16.0.11', '172.16.0.12',
    '127.0.0.1', '::1',
    '203.0.113.0', '198.51.100.0', '192.0.2.1',
    '93.184.216.34', '151.101.1.140', '104.244.42.65',
    '134.94.1.10', '134.94.1.11', '134.94.1.12'
];

$userAgents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
    'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
    'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
    'Mozilla/5.0 (Android 13; Mobile; rv:109.0) Gecko/117.0 Firefox/119.0',
    'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)'
];

$methods = ['GET', 'GET', 'GET', 'GET', 'POST', 'GET', 'GET', 'HEAD'];

$statusCodes = [
    200, 200, 200, 200, 200, 200, 200, 200, 200, 200,
    200, 200, 200, 200, 200, 200, 200, 200, 200, 200,
    304, 304, 304, 304, 304,
    404, 404, 404,
    301, 302,
    500,
    403,
];

$referrers = [
    '-', '-', '-', '-',
    'http://localhost:8080/', 'http://localhost:8080/index.php',
    'http://localhost:8080/products_input.html',
    'https://www.google.com/', 'https://www.bing.com/',
    'http://localhost:8080/scanner.html'
];

$accessLog = fopen('test_access.log', 'w');

$baseTime = time() - (48 * 3600);

for ($i = 0; $i < 500; $i++) {
    $hour = date('H', $baseTime + ($i * 350));
    $trafficMultiplier = 1;
    if ($hour >= 9 && $hour <= 17) {
        $trafficMultiplier = 3;
    } elseif ($hour >= 18 && $hour <= 22) {
        $trafficMultiplier = 2;
    }
    
    if (rand(1, 10) > $trafficMultiplier * 3) {
        continue;
    }
    
    $timestamp = $baseTime + ($i * 350) + rand(-300, 300);
    $dateStr = date('d/M/Y:H:i:s O', $timestamp);
    
    $pageChoice = rand(1, 100);
    $selectedPage = '';
    $cumulative = 0;
    foreach ($pages as $page => $weight) {
        $cumulative += $weight / 10;
        if ($pageChoice <= $cumulative) {
            $selectedPage = $page;
            break;
        }
    }
    if (empty($selectedPage)) {
        $selectedPage = array_keys($pages)[0];
    }
    
    if (strpos($selectedPage, '.php') !== false && rand(1, 5) == 1) {
        $method = 'POST';
    } else {
        $method = 'GET';
    }
    
    $ip = $ips[array_rand($ips)];
    $status = $statusCodes[array_rand($statusCodes)];
    $size = ($status == 200) ? rand(1000, 50000) : (($status == 304) ? 0 : rand(100, 500));
    $userAgent = $userAgents[array_rand($userAgents)];
    $referrer = $referrers[array_rand($referrers)];
    
    $logLine = sprintf(
        '%s - - [%s] "%s %s HTTP/1.1" %d %d "%s" "%s"',
        $ip,
        $dateStr,
        $method,
        $selectedPage,
        $status,
        $size,
        $referrer,
        $userAgent
    );
    
    fwrite($accessLog, $logLine . "\n");
}

fclose($accessLog);

$errorLog = fopen('test_error.log', 'w');

$errorTypes = [
    '[php:error]' => [
        'PHP Fatal error: Uncaught Error: Call to undefined function',
        'PHP Fatal error: Cannot access private property',
        'PHP Fatal error: Class not found',
        'PHP Fatal error: Maximum execution time exceeded'
    ],
    '[php:warning]' => [
        'PHP Warning: Invalid argument supplied for foreach()',
        'PHP Warning: mysqli_connect(): Connection refused',
        'PHP Warning: Undefined variable',
        'PHP Warning: Division by zero'
    ],
    '[php:notice]' => [
        'PHP Notice: Undefined index',
        'PHP Notice: Trying to access array offset on value of type null'
    ],
    '[core:error]' => [
        'AH00124: Request exceeded the limit of 10 internal redirects',
        'AH00126: Invalid URI in request'
    ],
    '[ssl:warn]' => [
        'AH01909: server certificate does NOT include an ID which matches the server name'
    ],
    '[authz_core:error]' => [
        'AH01630: client denied by server configuration'
    ]
];

for ($i = 0; $i < 50; $i++) {
    $timestamp = $baseTime + ($i * 3600) + rand(-1800, 1800);
    $dateStr = date('D M d H:i:s.u Y', $timestamp);
    
    $errorType = array_rand($errorTypes);
    $errorMsg = $errorTypes[$errorType][array_rand($errorTypes[$errorType])];
    
    $ip = $ips[array_rand($ips)];
    $pid = rand(1000, 9999);
    
    $logLine = sprintf(
        '[%s] %s [pid %d] [client %s:%d] %s',
        $dateStr,
        $errorType,
        $pid,
        $ip,
        rand(10000, 65535),
        $errorMsg
    );
    
    if (strpos($errorType, 'php') !== false) {
        $files = array_keys($pages);
        $file = $files[array_rand($files)];
        if (strpos($file, '.php') !== false || strpos($file, '.html') !== false) {
            $logLine .= ' in /var/www/html/src' . $file . ' on line ' . rand(1, 200);
        }
    }
    
    fwrite($errorLog, $logLine . "\n");
}

fclose($errorLog);

$accessLog = fopen('test_access.log', 'a');

$attackTime = time() - 3600;
for ($i = 0; $i < 20; $i++) {
    $timestamp = $attackTime + ($i * 2);
    $dateStr = date('d/M/Y:H:i:s O', $timestamp);
    $logLine = sprintf(
        '%s - - [%s] "GET /admin/config.php HTTP/1.1" 404 289 "-" "Bot/Scanner"',
        '203.0.113.99',
        $dateStr
    );
    fwrite($accessLog, $logLine . "\n");
}

$sessionTime = time() - 7200;
$sessionIP = '192.168.1.105';
$sessionPages = [
    '/login.php' => 200,
    '/index.php' => 200,
    '/products_input.html' => 200,
    '/add_product.php' => 200,
    '/logout.php' => 200
];

foreach ($sessionPages as $page => $status) {
    $dateStr = date('d/M/Y:H:i:s O', $sessionTime);
    $method = ($page == '/login.php' || $page == '/add_product.php') ? 'POST' : 'GET';
    $logLine = sprintf(
        '%s - - [%s] "%s %s HTTP/1.1" %d %d "http://localhost:8080/" "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/119.0"',
        $sessionIP,
        $dateStr,
        $method,
        $page,
        $status,
        rand(1000, 5000)
    );
    fwrite($accessLog, $logLine . "\n");
    $sessionTime += rand(30, 300);
}

fclose($accessLog);
