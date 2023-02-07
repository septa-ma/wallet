<?php

class MakeContractAndImpart
{ 
    private static $TABLE_CONTRACT = "contract";
    private static $TABLE_WALLET = "wallet";
    private static $TABLE_USER_CONTRACTS = "user_contracts";
    private static $TABLE_USER_PERSONAL_INFO = "user_personal_info";
    private static $TABLE_ADDRESSES = "addresses";
    private static $TABLE_USER_ADDRESS = "user_address";

    private $conn = null;
    private $con = null;

    public $id;
    public $contract_name;
    public $start_date;
    public $end_date;
    public $total_credit;
    public $total_credit_contract;
    public $credit_type;
    public $transaction_fee;
    public $contract_file;
    public $contract_detail;
    public $wallet_type;
 
    public function __construct($walletDB, $userDB) { 
        try{
            $this->conn = $userDB;
            $this->con = $walletDB;
        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }
        
    }

    // make pay contract and imparting 
    public function makeContractAndImpart($fullNameList)
    {
        try {

            $this->con->beginTransaction();
            // defining new contract
            $makeContract = $this->con->prepare(" INSERT INTO " . self::$TABLE_CONTRACT
            . " ( contract_name, end_date, total_credit, transaction_fee, 
            contract_detail, credit_type, start_time, start_date, conditions, delete_key )
            VALUES ( :contract_name, :dead_line_date, :total_credit, :transaction_fee, 
            :contract_detail, :credit_type, :start_time, :start_date, :conditions, :delete_key ) ");

            $contract_name = 'Rial';//$this->sanitizeString($this->contract_name);
            $dead_line_date = date("Y-m-d", strtotime("2222-01-01"));//$this->sanitizeString($this->dead_line_date);
            //$dead_line_time = date("H:i:s", strtotime("now"));//$this->sanitizeString($this->dead_line_time);
            $total_credit = 0.0;//$this->sanitizeString($this->total_credit);
            $transaction_fee = 0.0;//$this->sanitizeString($this->transaction_fee);
            $contract_detail = 'قرارداد پرداخت برای همه اعضا سامانه و تا ابد قابل اجراست.';//$this->sanitizeString($this->contract_detail);
            // rial=1 or dollar=2 or ...
            $credit_type = 1;//$this->sanitizeString($this->credit_type);
            $start_time = date("H:i:s", strtotime("now"));//$this->sanitizeString($this->start_time);
            $start_date = date("Y-m-d", strtotime("now"));//$this->sanitizeString($this->start_date);
            // conditions number each number means a condition
            $conditions = 100; // there isn't any condition in this contract
            $delete_key = 0;

            $makeContract->bindParam(":contract_name", $contract_name);
            $makeContract->bindParam(":dead_line_date", $dead_line_date);
            // $makeContract->bindParam(":dead_line_time", $dead_line_time);
            $makeContract->bindParam(":total_credit", $total_credit);
            $makeContract->bindParam(":transaction_fee", $transaction_fee);
            $makeContract->bindParam(":contract_detail", $contract_detail);
            $makeContract->bindParam(":credit_type", $credit_type);
            $makeContract->bindParam(":start_time", $start_time);
            $makeContract->bindParam(":start_date", $start_date);
            $makeContract->bindParam(":conditions", $conditions);
            $makeContract->bindParam(":delete_key", $delete_key);

            $makeContract->execute();
            $lastContId = $this->con->lastInsertId();
            // echo $lastContId;
            
            // get selected organ id and save its id with contract
            // in a contract-organ table code must insert here.
            /************...HERE...************/
            
            for($i = 0; $i < count($fullNameList); $i++) {
                // $fullName  = ;
                // echo $fullName;
                // echo count($fullNameList);
                // $this->conn->beginTransaction();
                // get receiver full name in member table based on id in wallet
                $getSelectedUserId = $this->conn->prepare(" SELECT id FROM " . self::$TABLE_USER_PERSONAL_INFO 
                . " WHERE CONCAT(fname, ' ', family) LIKE :fullName ");
                // :fullName LIMIT 1 ");
                echo $fullNameList[$i];

                $getSelectedUserId->bindParam(":fullName", $fullNameList[$i]);
                $getSelectedUserId->execute();
                $data1 = $getSelectedUserId->fetch(PDO::FETCH_ASSOC);
                // print_r($data1);
                // if ($data1 == 0) {
                //     echo 'error';
                // }
                // echo count($data1);
                if ($data1 >= 1) {

                    $userId = $data1['id'];
                    // echo $userId;
                    $saveUserContractId = $this->con->prepare(" INSERT INTO " . self::$TABLE_USER_CONTRACTS
                    . " ( contract_id, personal_user_id, conrtact_user_credit, wallet_type, active, delete_key ) 
                    VALUES ( :contract_id, :personal_user_id, :conrtact_user_credit, :wallet_type, :active, :delete_key ) ");

                    // a wallet is block or not | is active or not
                    
                    $creditAmount = 0;
                    $walletType = 73;
                    $active = 1;
                    $deleteKey = 90;
                    $saveUserContractId->bindParam(":contract_id", $lastContId);
                    $saveUserContractId->bindParam(":personal_user_id", $userId);
                    $saveUserContractId->bindParam(":conrtact_user_credit", $creditAmount);
                    $saveUserContractId->bindParam(":wallet_type", $walletType);
                    $saveUserContractId->bindParam(":active", $active);
                    $saveUserContractId->bindParam(":delete_key", $deleteKey);

                    $saveUserContractId->execute();
                    echo 'ok';
                }
                echo 'succesful'.'</br>';
            }
            echo 'rock it babe!';
            // $this->conn->commit();
            $this->con->commit();
        } catch (Exception $exception) {
            // $this->conn->rollBack();
            $this->con->rollBack();
            die($exception->getMessage());
        }
    }

    // make pay contract and imparting new version
    public function makeContractAndImpartNewVersion($donateResultList, $conditionsList) {
        // print_r($donateResultList);
        try {
            $this->con->beginTransaction();
/************************************page 1**************************************/
            // defining new contract
            $makeContract = $this->con->prepare(" INSERT INTO " . self::$TABLE_CONTRACT
            . " ( contract_name, start_date, end_date, total_credit, total_credit_contract, credit_type, 
            transaction_fee, contract_file, contract_detail, delete_key )
            VALUES ( :contract_name, :start_date, :end_date, :total_credit, :total_credit_contract, :credit_type, 
            :transaction_fee, :contract_file, :contract_detail, :delete_key ) ");
 
            $contract_name = $this->sanitizeString($this->contract_name);
            $start_date = $this->sanitizeString($this->start_date);
            $end_date = $this->sanitizeString($this->end_date);//date("Y-m-d", strtotime("2222-01-01"));
            //$dead_line_time = date("H:i:s", strtotime("now"));//$this->sanitizeString($this->dead_line_time);
            $total_credit = $this->sanitizeString($this->total_credit);
            $total_credit_contract = $this->sanitizeString($this->total_credit_contract);
            $credit_type = $this->sanitizeString($this->credit_type);
            $transaction_fee = $this->sanitizeString($this->transaction_fee);
            $contract_file = $this->sanitizeString($this->contract_file);
            $contract_detail = $this->sanitizeString($this->contract_detail);
            $delete_key = 0;

            $makeContract->bindParam(":contract_name", $contract_name);
            $makeContract->bindParam(":start_date", $start_date);
            $makeContract->bindParam(":end_date", $end_date);
            $makeContract->bindParam(":total_credit", $total_credit);
            $makeContract->bindParam(":total_credit_contract", $total_credit_contract);
            $makeContract->bindParam(":credit_type", $credit_type);
            $makeContract->bindParam(":transaction_fee", $transaction_fee);
            $makeContract->bindParam(":contract_file", $contract_file);
            $makeContract->bindParam(":contract_detail", $contract_detail);
            $makeContract->bindParam(":delete_key", $delete_key);

            $makeContract->execute();
            $lastContId = $this->con->lastInsertId();
            // echo 'succesful p1'.'</br>';
            // echo $lastContId;
            
            // get selected organ id and save its id with contract
            // in a contract-organ table code must insert here.
            /************...HERE...************/
/************************************page 2**************************************/
            for($i = 0; $i < count($donateResultList); $i++) {
                // $fullName  = ;
                // echo $fullName;
                // echo count($fullNameList);
                // $this->conn->beginTransaction();
                // get receiver full name in member table based on id in wallet
                $getSelectedUserId = $this->conn->prepare(" SELECT id FROM " . self::$TABLE_USER_PERSONAL_INFO 
                . " WHERE CONCAT(fname, ' ', family) LIKE :fullName ");
                // :fullName LIMIT 1 ");
                // echo $fullNameList[$i];

                $getSelectedUserId->bindParam(":fullName", $donateResultList['fullName'][$i]);
                $getSelectedUserId->execute();
                $data1 = $getSelectedUserId->fetch(PDO::FETCH_ASSOC);
                // print_r($data1);
                // if ($data1 == 0) {
                //     echo 'error';
                // }
                // echo count($data1);
                if ($data1 >= 1) {

                    $userId = $data1['id'];
                    
                    $saveUserContractId = $this->con->prepare(" INSERT INTO " . self::$TABLE_USER_CONTRACTS
                    . " ( contract_id, personal_user_id, conrtact_user_credit, wallet_type, active, delete_key ) 
                    VALUES ( :contract_id, :personal_user_id, :conrtact_user_credit, :wallet_type, :active, :delete_key ) ");

                    // a wallet is block or not | is active or not
                    // echo $donateResultList[$i][1];
                    // $creditAmount = $donateResultList['user_credit'][$i];
                    $walletType = $this->sanitizeString($this->wallet_type);
                    if ($donateResultList['wallet_type'][$i] == 'مبدا'){
                        $walletType = 85;
                    }elseif($donateResultList['wallet_type'][$i] == 'مقصد'){
                        $walletType = 84;
                    }
                    // }elseif($walletType == 'مقصد-مبدا'){
                    //     $walletType = 3;
                    // }else{
                    //     $walletType = 0;
                    // echo $donateResultList['active'][$i];
                    if ( $donateResultList['active'][$i] == 'بلاک شده' ) {
                        $active = 7;
                    } elseif ( $donateResultList['active'][$i] == 'قابل استفاده' ) {
                        $active = 11;
                    }
                    $creditAmount = $donateResultList['userCredit'][$i];
                    $deleteKey = 90;
                    $saveUserContractId->bindParam(":contract_id", $lastContId);
                    $saveUserContractId->bindParam(":personal_user_id", $userId);
                    $saveUserContractId->bindParam(":conrtact_user_credit", $creditAmount);
                    $saveUserContractId->bindParam(":wallet_type", $walletType);
                    $saveUserContractId->bindParam(":active", $active);
                    $saveUserContractId->bindParam(":delete_key", $deleteKey);

                    $saveUserContractId->execute();
                    // echo 'ok';
                }
                // echo 'succesful p2'.'</br>';
            }
/************************************page 3**************************************/
            for ($j = 0; $j < count($conditionsList); $j++) {
                $conditionNum = $conditionsList[$j].'111';
                // echo $conditionNum;
            }
            $addContConditions = $this->con->prepare(" UPDATE " . self::$TABLE_CONTRACT . " SET "
            . " conditions = :conditions "
            . " WHERE id = :id ");

            $addContConditions->bindParam(":conditions",$conditionNum);
            $addContConditions->bindParam(":id",$lastContId);

            $addContConditions->execute();

            // echo 'succwssful p3';
/************************************end**************************************/            
            $this->con->commit();
        } catch (Exception $exception) {
            $this->con->rollBack();
            die($exception->getMessage());
        }
    }

    public function readAllUserInfo() {
        try { 

            $readAll = $this->conn->prepare(" SELECT 
            CONCAT(U.fname, ' ', U.family) AS fullName ,
            U.birth_date , U.gender , A.city , A.state FROM "
            . self::$TABLE_USER_PERSONAL_INFO . " AS U INNER JOIN "
            . self::$TABLE_ADDRESSES . " AS A INNER JOIN "
            . self::$TABLE_USER_ADDRESS . " AS UA "
            . " ON "
            . " UA.personal_info_id = U.id "
            . " AND " 
            // . " ON "
            . " UA.address_id = A.id "
            // . " LIMIT $x "
            );

            $readAll->execute();
            // print_r($readAll->fetchAll(PDO::FETCH_ASSOC));
            // echo $readAll->rowCount();
            return $readAll;

        } catch (Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function sanitizeString($var){

        $var = stripslashes($var);
        $var = htmlentities($var);
        $var = strip_tags($var);
        return $var;

    }
}

?>
