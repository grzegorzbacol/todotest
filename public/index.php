<?php

// Fix SCRIPT_NAME, SCRIPT_FILENAME and PHP_SELF IMMEDIATELY
// This must be done before ANY other code, as Laravel uses these for URL generation
// Caddy may send these as arrays or with index.php, so we force them to correct values
if (isset($_SERVER['SCRIPT_NAME']) && (is_array($_SERVER['SCRIPT_NAME']) || str_contains((string)$_SERVER['SCRIPT_NAME'], 'index.php'))) {
    $_SERVER['SCRIPT_NAME'] = '/';
}
if (!isset($_SERVER['SCRIPT_NAME']) || empty($_SERVER['SCRIPT_NAME'])) {
    $_SERVER['SCRIPT_NAME'] = '/';
}
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
if (isset($_SERVER['PHP_SELF']) && (is_array($_SERVER['PHP_SELF']) || str_contains((string)$_SERVER['PHP_SELF'], 'index.php'))) {
    $_SERVER['PHP_SELF'] = '/';
}
if (!isset($_SERVER['PHP_SELF']) || empty($_SERVER['PHP_SELF'])) {
    $_SERVER['PHP_SELF'] = '/';
}

// Enable error reporting only if APP_DEBUG is true
// Use $_ENV or getenv() instead of env() because env() is not available before Laravel is loaded
$appDebug = $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? 'false';
if (filter_var($appDebug, FILTER_VALIDATE_BOOLEAN)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    
    // Set error handler to catch all errors
    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
}

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

try {
    // Determine if the application is in maintenance mode...
    if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
        require $maintenance;
    }

    // Register the Composer autoloader...
    if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
        throw new Exception('Composer autoloader not found. Run composer install.');
    }
    require __DIR__.'/../vendor/autoload.php';

    // Bootstrap Laravel and handle the request...
    if (!file_exists(__DIR__.'/../bootstrap/app.php')) {
        throw new Exception('Laravel bootstrap file not found.');
    }
    
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->handleRequest(Request::capture());
    
} catch (\Throwable $e) {
    // Log error
    error_log('ERROR: ' . $e->getMessage());
    error_log('FILE: ' . $e->getFile() . ':' . $e->getLine());
    error_log('TRACE: ' . $e->getTraceAsString());
    
    // Show error in response only if APP_DEBUG is true
    // Use $_ENV or getenv() instead of env() because env() is not available before Laravel is loaded
    $appDebug = $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? 'false';
    if (filter_var($appDebug, FILTER_VALIDATE_BOOLEAN)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "ERROR: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "\n\n";
        echo "FILE: " . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . ":" . $e->getLine() . "\n\n";
        echo "TRACE:\n" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');
    } else {
        http_response_code(500);
        echo 'Internal Server Error';
    }
}

