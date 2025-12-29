<?php

namespace PilihKredit;

use PDO;
use PDOException;

/**
 * Database Configuration Utility Class
 */
class DatabaseConfig
{
    private static $dbConfig = null;

    /**
     * Get database connection
     * 
     * @return PDO Database connection instance
     * @throws \Exception
     */
    private static function getConnection()
    {
        if (self::$dbConfig === null) {
            // Load database configuration from config file
            $configPath = __DIR__ . '/../config/database_config.php';
            if (!file_exists($configPath)) {
                throw new \Exception("Database configuration file not found: " . $configPath);
            }
            
            $dbConfig = require $configPath;
            
            $host = $dbConfig['host'];
            $database = $dbConfig['database'];
            $username = $dbConfig['username'];
            $password = $dbConfig['password'];
            $charset = $dbConfig['charset'] ?? 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$database;charset=$charset";
            try {
                self::$dbConfig = new PDO($dsn, $username, $password);
                self::$dbConfig->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$dbConfig->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new \Exception("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$dbConfig;
    }

    /**
     * Get OSS configuration from database
     * 
     * @return array OSS configuration array
     * @throws \Exception
     */
    public static function getOssConfig()
    {
        try {
            $conn = self::getConnection();
            
            // Query app_config table for oss_config
            $stmt = $conn->prepare("SELECT value FROM app_config WHERE `key` = :key LIMIT 1");
            $stmt->bindValue(':key', 'oss_config', PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || empty($result['value'])) {
                throw new \Exception("OSS configuration not found in database (key: oss_config)");
            }
            
            // Decode JSON configuration
            $jsonValue = $result['value'];
            $config = json_decode($jsonValue, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errorMsg = json_last_error_msg();
                $errorCode = json_last_error();
                
                // Log the raw JSON value for debugging (truncate if too long)
                $jsonPreview = strlen($jsonValue) > 200 ? substr($jsonValue, 0, 200) . '...' : $jsonValue;
                error_log("Invalid JSON in database. Error: {$errorMsg} (Code: {$errorCode}). JSON preview: {$jsonPreview}");
                
                throw new \Exception("Invalid JSON format in OSS configuration: {$errorMsg} (Error Code: {$errorCode}). Please check the JSON syntax in database.");
            }
            
            return $config;
        } catch (PDOException $e) {
            throw new \Exception("Failed to read OSS configuration from database: " . $e->getMessage());
        }
    }
}

