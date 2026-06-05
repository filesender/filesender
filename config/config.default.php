<?php
/**
 * Default configuration for FileSender Docker image
 * Override this by creating config/config.php
 */

$config = [
    // Database configuration (default untuk docker-compose)
    'db_type' => 'mysql',
    'db_host' => 'db',                    // Service name dalam docker-compose
    'db_database' => 'filesender',
    'db_username' => 'filesender',
    'db_password' => 'filesender123',
    
    // Site configuration
    'site_url' => 'http://localhost:8080',
    
    // Admin
    'admin_email' => 'admin@localhost.local',
    
    // Storage
    'storage_type' => 'filesystem',
    'storage_filesystem_path' => '/var/www/html/data/files',
    
    // Language
    'language' => 'en',
    
    // Logging
    'log_file' => '/var/www/html/data/file_sender.log',
    
    // Terasender
    'terasender_enabled' => true,
    'terasender_worker_count' => 5,
    'teraserver_enabled' => false,
    
    // Authentication (internal login guna database)
    'auth_sp_type' => 'internal',
    
    // Session
    'session_httponly' => true,
    'session_secure' => false,
    'session_name' => 'FileSender',
    
    // File upload limits (default 2GB)
    'upload_max_size' => 2147483648,
    
    // Cryptography
    'encryption_key' => 'change-me-in-production',
];
