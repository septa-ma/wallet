<?php

class Database{ 
  
    private static $SERVER_NAME = "localhost";
    
    private static $USER_NAME_WALLET = "root";
    private static $PASSWORD_WALLET = "";
    
    private static $USER_NAME_MAIN = "root";
    private static $PASSWORD_MAIN = "";
    
    private static $DATABASE_NAME_MAIN = "User";
    private static $DATABASE_NAME_WALLET = "Wallet";

    private $conn = null;
    private $con = null;

    // get the user database connection
    public function user_connection(){
        $this->conn = null;
        try{
            $this->conn = new PDO("mysql:host=" . self::$SERVER_NAME . ";dbname=" . self::$DATABASE_NAME_MAIN . ";", self::$USER_NAME_MAIN, self::$PASSWORD_MAIN);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->query("set names utf8");
        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }
        return $this->conn;
    }

    // get the wallet database connection
    public function wallet_connection(){
        $this->con = null;
        try{
            $this->con = new PDO("mysql:host=" . self::$SERVER_NAME . ";dbname=" . self::$DATABASE_NAME_WALLET . ";", self::$USER_NAME_WALLET, self::$PASSWORD_WALLET);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->con->query("set names utf8");
        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }
        return $this->con;
    }
    
}

?>