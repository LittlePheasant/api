<?php
    class Operations
    {
    
        private $db_host = 'localhost';
        private $db_name = 'id20554492_oeswebsite_db';
        private $db_username = 'id20554492_root';
        private $db_password = '000WEBhost@keanna';
    
    
        public function dbConnection()
        {
    
            try {
                $conn = new PDO('mysql:host=' . $this->db_host . ';dbname=' . $this->db_name, $this->db_username, $this->db_password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $conn;
            } catch (PDOException $e) {
                echo "Connection error " . $e->getMessage();
                exit;
            }
        }
    }
?>