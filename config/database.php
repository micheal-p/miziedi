<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

class Database {
    private static $instance = null;
    private $client;
    private $database;

    private function __construct() {
        // Load .env variables
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $uri = $_ENV['MONGODB_URI'];
        $dbName = $_ENV['MONGODB_DB'];

        try {
            $this->client = new Client($uri);
            $this->database = $this->client->selectDatabase($dbName);
        } catch (Exception $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getDb() {
        return $this->database;
    }
}