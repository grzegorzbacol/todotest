<?php

// Enable error reporting only if APP_DEBUG is true
if (env('APP_DEBUG', false)) {
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
    if (env('APP_DEBUG', false)) {
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

