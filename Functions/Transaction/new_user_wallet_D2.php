<?php

class NewUserWalletD2 {

    private static $TABLE_WALLET = "wallet";
    private static $TABLE_CONTRACT = "contract";
    private static $TABLE_USER_CONTRACTS = "user_contracts";
  
    private $con = null;

    public function __construct($walletDB) { 
        try{
            $this->con = $walletDB;
        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }
    }

    public function newUserWalletViaAPID2($walletKey, $contName){
        try{
            $this->con->beginTransaction();
            // 1- make new wallet
            $makeWallet = $this->con->prepare(" INSERT INTO ". self::$TABLE_WALLET 
            . " ( user_private_key, delete_key )
                VALUES ( :user_private_key, :delete_key ) ");

            $delete_key_w = 10;
            $makeWallet->bindParam(":user_private_key",$walletKey);
            $makeWallet->bindParam(":delete_key",$delete_key_w);
            $makeWallet->execute();
            $lastWalletId = $this->con->lastInsertId();

            // 2- find the contract
            $fetchContId = $this->con->prepare(" SELECT id FROM " . self::$TABLE_CONTRACT 
            . " WHERE contract_name = :contract_name AND delete_key = 0 ");

            $fetchContId->bindParam(":contract_name", $contName);
            $fetchContId->execute();
            $result = $fetchContId->fetch(PDO::FETCH_ASSOC);

            if(count($result) == 0) {
                echo 'error';
            }
            elseif(count($result) > 0) {
                $contId = $result['id'];

                // 3- add access to user to do finantial transaction
                $finantialTrx = $this->con->prepare(" INSERT INTO ". self::$TABLE_USER_CONTRACTS
                . " ( contract_id, wallet_id, conrtact_user_credit, wallet_type, active, delete_key )
                VALUES ( :contract_id, :wallet_id, :conrtact_user_credit, :wallet_type, :active, :delete_key ) ");

                $creditAmount = 0;
                $walletType = 8;
                $active = 11;
                $deleteKey = 90;

                $finantialTrx->bindParam(":contract_id", $contId);
                $finantialTrx->bindParam(":wallet_id", $lastWalletId);
                $finantialTrx->bindParam(":conrtact_user_credit", $creditAmount);
                $finantialTrx->bindParam(":wallet_type", $walletType);
                $finantialTrx->bindParam(":active", $active);
                $finantialTrx->bindParam(":delete_key", $deleteKey);
                $finantialTrx->execute();
            }
            $this->con->commit();
            return 7000;

        } catch (Exception $ex) {
            die($ex->getMessage());
        }

    }

}

?>