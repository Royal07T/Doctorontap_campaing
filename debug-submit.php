<?php
/**
 * Temporary debug file to test form submission
 * Upload this to your production server root and access it directly
 * DELETE THIS FILE after debugging!
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

try {
    // Test 1: PHP is working
    $tests = [
        'php_version' => PHP_VERSION,
        'time' => date('Y-m-d H:i:s'),
        'post_data_received' => !empty($_POST),
        'post_data' => $_POST,
    ];
    
    // Test 2: Can we load Laravel?
    $laravelPath = __DIR__ . '/laravel/public/index.php';
    if (file_exists($laravelPath)) {
        $tests['laravel_file_exists'] = true;
    } else {
        $tests['laravel_file_exists'] = false;
        $tests['looking_for'] = $laravelPath;
    }
    
    // Test 3: Check .env file
    $envPath = __DIR__ . '/laravel/.env';
    if (file_exists($envPath)) {
        $tests['env_file_exists'] = true;
        $envContent = file_get_contents($envPath);
        $tests['app_debug'] = strpos($envContent, 'APP_DEBUG=true') !== false ? 'true' : 'false';
    } else {
        $tests['env_file_exists'] = false;
    }
    
    // Test 4: Database connection (basic check)
    if (file_exists($envPath)) {
        preg_match('/DB_CONNECTION=(.*)/', file_get_contents($envPath), $matches);
        $tests['db_driver'] = $matches[1] ?? 'not found';
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Debug tests completed',
        'tests' => $tests
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}

