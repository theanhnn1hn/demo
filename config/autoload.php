<?php
/**
 * Autoload helper functions and global variables
 */

// Load configuration
$config = require_once 'config/config.php';

// Set timezone
date_default_timezone_set($config['app']['timezone']);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error handling based on environment
if ($config['app']['debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Helper functions

/**
 * Get configuration value
 * 
 * @param string $key Dot notation key (e.g. 'app.name')
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value
 */
function config($key, $default = null) {
    global $config;
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        
        $value = $value[$k];
    }
    
    return $value;
}

/**
 * Get base URL
 * 
 * @param string $path Path to append to base URL
 * @return string Full URL
 */
function url($path = '') {
    $baseUrl = rtrim(config('app.url'), '/');
    $path = ltrim($path, '/');
    
    return $baseUrl . '/' . $path;
}

/**
 * Get asset URL
 * 
 * @param string $path Path to asset
 * @return string Full asset URL
 */
function asset($path) {
    return url('public/' . ltrim($path, '/'));
}

/**
 * Escape HTML for output
 * 
 * @param string $text Text to escape
 * @return string Escaped text
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date)) return '';
    
    $dt = new DateTime($date);
    return $dt->format($format);
}

/**
 * Format file size
 * 
 * @param int $bytes Size in bytes
 * @return string Formatted size
 */
function formatFileSize($bytes) {
    if ($bytes < 1024) {
        return $bytes . ' B';
    } elseif ($bytes < 1048576) {
        return round($bytes / 1024, 2) . ' KB';
    } elseif ($bytes < 1073741824) {
        return round($bytes / 1048576, 2) . ' MB';
    } else {
        return round($bytes / 1073741824, 2) . ' GB';
    }
}

/**
 * Format duration in seconds to HH:MM:SS
 * 
 * @param int $seconds Duration in seconds
 * @return string Formatted duration
 */
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds / 60) % 60);
    $seconds = $seconds % 60;
    
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
}

/**
 * Generate a random string
 * 
 * @param int $length Length of the string
 * @return string Random string
 */
function randomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

/**
 * Check if a string is valid JSON
 * 
 * @param string $string String to check
 * @return bool True if valid JSON
 */
function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * Get current user from session
 * 
 * @return array|null User data or null if not logged in
 */
function currentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    // Load user from database
    $db = \App\Core\Database::getInstance();
    $user = $db->single("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    
    return $user;
}

/**
 * Check if current user has a specific role
 * 
 * @param string|array $roles Role or roles to check
 * @return bool True if user has the role
 */
function hasRole($roles) {
    $user = currentUser();
    
    if (!$user) {
        return false;
    }
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    return in_array($user['role'], $roles);
}

/**
 * Create directory if it doesn't exist
 * 
 * @param string $path Directory path
 * @return bool True if directory exists or was created
 */
function ensureDirectoryExists($path) {
    if (!file_exists($path)) {
        return mkdir($path, 0755, true);
    }
    
    return true;
}
