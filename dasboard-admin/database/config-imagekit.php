<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';

use ImageKit\ImageKit;
use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize ImageKit object
$imageKit = new ImageKit(
    $_ENV['IMAGEKIT_PUBLIC_KEY'],   // Public Key
    $_ENV['IMAGEKIT_PRIVATE_KEY'],  // Private Key
    $_ENV['IMAGEKIT_URL']           // URL Endpoint
);
