<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';

use ImageKit\ImageKit;
use Dotenv\Dotenv;

// Deteksi lingkungan (localhost atau hosting)
$isLocalhost = ($_SERVER['HTTP_HOST'] === 'localhost:8080' || $_SERVER['HTTP_HOST'] === '127.0.0.1');

// Tentukan path .env berdasarkan lingkungan
$envPath = $isLocalhost
    ? __DIR__  // Path untuk localhost
    : __DIR__ . '/../../../app'; // Path untuk hosting
// Load environment variables from .env file
$dotenv = Dotenv::createImmutable($envPath);
$dotenv->load();

// Initialize ImageKit object
$imageKit = new ImageKit(
    $_ENV['IMAGEKIT_PUBLIC_KEY'],   // Public Key
    $_ENV['IMAGEKIT_PRIVATE_KEY'],  // Private Key
    $_ENV['IMAGEKIT_URL']           // URL Endpoint
);