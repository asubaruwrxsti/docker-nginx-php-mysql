<?php

namespace App\Acme;

class Database
{
    private static $instance = null;
    private $connection;
    private $host = 'mysql';
    private $username = 'dev';
    private $password = 'dev';
    private $database = 'test';

    private function __construct()
    {
        $this->connection = new \mysqli($this->host, $this->username, $this->password, $this->database);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    private function __clone() {}

    public function __wakeup() {}
}
