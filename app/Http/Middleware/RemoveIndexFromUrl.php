<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoveIndexFromUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Remove index.php from SCRIPT_NAME to prevent it from appearing in URLs
        if (isset($_SERVER['SCRIPT_NAME']) && str_contains($_SERVER['SCRIPT_NAME'], 'index.php')) {
            $_SERVER['SCRIPT_NAME'] = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
            if (empty($_SERVER['SCRIPT_NAME'])) {
                $_SERVER['SCRIPT_NAME'] = '/';
            }
        }
        
        // Also fix SCRIPT_FILENAME if needed
        if (isset($_SERVER['SCRIPT_FILENAME']) && str_contains($_SERVER['SCRIPT_FILENAME'], 'index.php')) {
            $_SERVER['SCRIPT_FILENAME'] = str_replace('/index.php', '', $_SERVER['SCRIPT_FILENAME']);
        }
        
        // Fix PHP_SELF if it contains index.php
        if (isset($_SERVER['PHP_SELF']) && str_contains($_SERVER['PHP_SELF'], 'index.php')) {
            $_SERVER['PHP_SELF'] = str_replace('/index.php', '', $_SERVER['PHP_SELF']);
            if (empty($_SERVER['PHP_SELF'])) {
                $_SERVER['PHP_SELF'] = '/';
            }
        }
        
        // Force correct URL generation by setting SCRIPT_NAME to root
        $_SERVER['SCRIPT_NAME'] = '/';
        
        return $next($request);
    }
}

