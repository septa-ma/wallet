<?php
class EditContract
{ 
    private static $SERVER_NAME = "localhost";
    private static $USER_NAME = "root";
    private static $PASSWORD = "";

    private static $DATABASE_NAME_WALLET = "Wallet";
    private static $TABLE_CONTRACT = "contract";
    private $con = null;

    public $id;
    public $contract_name;
    public $start_date;
    public $end_time;
    public $total_credit;
    public $total_credit_contract;
    public $transaction_fee;
    public $contract_detail;

    public function __construct() { 

        try{
            $this->con = new PDO("mysql:host=" . self::$SERVER_NAME . ";dbname=" . self::$DATABASE_NAME_WALLET . ";", self::$USER_NAME, self::$PASSWORD);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->con->query("set names utf8");
        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }        
    }

    public function edit_contract(){
        try {
            $query = $this->con->prepare(" UPDATE " . self::$TABLE_CONTRACT . " SET "
            . " contract_name = :contract_name , "
            . " start_date = :start_date , "
            . " end_date = :end_date , "
            . " total_credit = :total_credit , "
            . " total_credit_contract = :total_credit_contract , "
            . " transaction_fee = :transaction_fee , "
            . " contract_detail = :contract_detail "
            . " WHERE id = :id ");

            $id = $this->id;
            $contract_name = $this->sanitizeString($this->contract_name);
            $start_date = $this->sanitizeString($this->start_date);
            $end_date = $this->sanitizeString($this->end_date);
            $total_credit = $this->sanitizeString($this->total_credit);
            $total_credit_contract = $this->sanitizeString($this->total_credit_contract);
            $transaction_fee = $this->sanitizeString($this->transaction_fee);
            $contract_detail = $this->sanitizeString($this->contract_detail);
            
            $query->bindParam(":id", $id);
            $query->bindParam(":contract_name", $contract_name);
            $query->bindParam(":start_date", $start_date);
            $query->bindParam(":end_date", $end_date);
            $query->bindParam(":total_credit", $total_credit);
            $query->bindParam(":total_credit_contract", $total_credit_contract);
            $query->bindParam(":transaction_fee", $transaction_fee);
            $query->bindParam(":contract_detail", $contract_detail);

            $query->execute();

        } catch (Exception $ex) {
            die($ex->getMessage());
        }
    }

    public function sanitizeString($var){

        $var = stripslashes($var);
        $var = htmlentities($var);
        $var = strip_tags($var);
        return $var;

    }

}
