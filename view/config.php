<?php
class Database {
    private $servername = "localhost";
    private $username = "root";  // Replace with your database username
    private $password = "";      // Replace with your database password
    private $dbname = "learnifydb";
    public $conn;

    // Constructor that automatically connects to database
    public function __construct() {
        $this->connect();
    }

    // Connection method
    private function connect() {
        try {
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            return $this->conn;
        } catch(Exception $e) {
            die("Connection error: " . $e->getMessage());
        }
    }

    // Method to get connection
    public function getConnection() {
        return $this->conn;
    }

    // Method to close connection
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // Method to clean input data
    public function cleanInput($data) {
        return $this->conn->real_escape_string($data);
    }
}
?>