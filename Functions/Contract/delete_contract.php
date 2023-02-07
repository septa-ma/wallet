<?php


class DeleteContract
{ 

    private static $SERVER_NAME = "localhost";
    private static $USER_NAME = "root";
    private static $PASSWORD = "";

    private static $DATABASE_NAME_WALLET = "Wallet";
    private static $TABLE_WALLET = "wallet";
    private static $TABLE_CONTRACT = "contract";
    private $con = null;

    public $id;

    public function __construct() { 
        try{
            $this->con = new PDO("mysql:host=" . self::$SERVER_NAME . ";dbname=" . self::$DATABASE_NAME_WALLET . ";", self::$USER_NAME, self::$PASSWORD);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->con->query("set names utf8");
        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }
        
    }

    public function deleteContract()
    {
        try {
            $query = $this->con->prepare(" UPDATE " . self::$TABLE_CONTRACT . " SET "
            . " delete_key = :delete_key "
            . " WHERE id = :id ");

            $delete_key = 1;
            $id = $this->id; 
            $query->bindParam(":delete_key", $delete_key);
            $query->bindParam(":id", $id);
            $query->execute();

        } catch (Exception $exception) {
            die($exception->getMessage());
        }
    }

}

$x = new DeleteContract();
$x->id = $_POST['id'];
$x->deleteContract();
