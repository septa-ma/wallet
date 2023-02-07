<?php


class ReadContract
{ 
    private static $SERVER_NAME = "localhost";
    private static $USER_NAME = "root";
    private static $PASSWORD = "";
    private static $DATABASE_NAME_WALLET = "Wallet";
    private static $TABLE_WALLET = "wallet";
    private static $TABLE_CONTRACT = "contract";
    private $con = null;

    public function __construct() { 
        try{
            $this->con = new PDO("mysql:host=" . self::$SERVER_NAME . ";dbname=" . self::$DATABASE_NAME_WALLET . ";", self::$USER_NAME, self::$PASSWORD);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->con->query("set names utf8");
        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }
        
    }

    public function readAllContracts()
    {
        try {
            $readAll = $this->con->prepare(" SELECT id, contract_name, start_date, end_date
            , transaction_fee, total_credit, total_credit_contract, contract_detail FROM " . self::$TABLE_CONTRACT . " WHERE delete_key = 0 ");

            $readAll->execute();
            $result = $readAll->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);

        } catch (Exception $exception) {
            die($exception->getMessage());
        }
    }

}

$x = new ReadContract();
$x->readAllContracts();
