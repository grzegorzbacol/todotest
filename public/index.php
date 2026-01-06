<?php

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

// Fix SCRIPT_NAME to prevent index.php from appearing in URLs
// This must be done before Laravel is loaded, as Laravel uses SCRIPT_NAME for URL generation
// Force SCRIPT_NAME to root directory, regardless of what Caddy sends
// Handle case where SCRIPT_NAME might be an array (shouldn't happen, but just in case)
if (is_array($_SERVER['SCRIPT_NAME'] ?? null)) {
    $_SERVER['SCRIPT_NAME'] = '/';
} else {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
    if (is_string($scriptName) && str_contains($scriptName, 'index.php')) {
        $_SERVER['SCRIPT_NAME'] = str_replace('/index.php', '', $scriptName);
        if (empty($_SERVER['SCRIPT_NAME'])) {
            $_SERVER['SCRIPT_NAME'] = '/';
        }
    } else {
        $_SERVER['SCRIPT_NAME'] = '/';
    }
}

// Always set SCRIPT_FILENAME to current file
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

// Also fix PHP_SELF
if (isset($_SERVER['PHP_SELF'])) {
    $phpSelf = $_SERVER['PHP_SELF'];
    if (is_string($phpSelf) && str_contains($phpSelf, 'index.php')) {
        $_SERVER['PHP_SELF'] = str_replace('/index.php', '', $phpSelf);
        if (empty($_SERVER['PHP_SELF'])) {
            $_SERVER['PHP_SELF'] = '/';
        }
    } elseif (!is_string($phpSelf)) {
        $_SERVER['PHP_SELF'] = '/';
    }
} else {
    $_SERVER['PHP_SELF'] = '/';
}

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

