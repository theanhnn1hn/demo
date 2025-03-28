<?php
/**
 * Application entry point
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Change to project root directory
chdir(BASE_PATH);

// Load composer autoloader
require 'vendor/autoload.php';

// Load environment variables
if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

// Initialize application
$app = new App\Core\App();
