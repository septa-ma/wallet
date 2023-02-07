<?php

class AllUserInFinantialContract {

    private static $TABLE_USER_CONTRACTS = "user_contracts";
    private static $TABLE_CONTRACT = "contract";
    private static $TABLE_WALLET = "wallet";

    private $con = null;

    public function __construct($walletDB) { 
        try{
            $this->con = $walletDB;
        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }
    }

    public function allInFinantialCont($contName) {

        try {
            
            $this->con->beginTransaction();

            $fetchContId = $this->con->prepare(" SELECT id FROM " . self::$TABLE_CONTRACT 
                . " WHERE contract_name = :contract_name AND delete_key = 0 ");

            $fetchContId->bindParam(":contract_name", $contName);
            $fetchContId->execute();
            $result = $fetchContId->fetch(PDO::FETCH_ASSOC);

            if(count($result) > 0) {
                $contId = $result['id'];
            }

            // get all users id
            $getUserId = $this->con->prepare(" SELECT personal_user_id FROM "
            . self::$TABLE_WALLET );

            $getUserId->execute();
            $row = $getUserId->fetchAll(PDO::FETCH_ASSOC);
            
            if(count($row) > 0) {
                for($i = 0; $i < count($row); $i++) {
                    $userId = $row[$i]['personal_user_id'];
                    // add into user contract table
                    $finantialTrx = $this->con->prepare(" INSERT INTO ". self::$TABLE_USER_CONTRACTS
                    . " ( contract_id, personal_user_id, conrtact_user_credit, wallet_type, active, delete_key )
                    VALUES ( :contract_id, :personal_user_id, :conrtact_user_credit, :wallet_type, :active, :delete_key ) ");

                    $creditAmount = 0;
                    $walletType = 8;
                    $active = 11;
                    $deleteKey = 90;

                    $finantialTrx->bindParam(":contract_id", $contId);
                    $finantialTrx->bindParam(":personal_user_id", $userId);
                    $finantialTrx->bindParam(":conrtact_user_credit", $creditAmount);
                    $finantialTrx->bindParam(":wallet_type", $walletType);
                    $finantialTrx->bindParam(":active", $active);
                    $finantialTrx->bindParam(":delete_key", $deleteKey);
                    $finantialTrx->execute();
                }
            }

            $this->con->commit();
        } catch(PDOException $ex) {
            die($ex->getMessage());
        }

    }
    
}

$a = new AllUserInFinantialContract();
$name = 'name';
$a->allInFinantialCont($name);

?>