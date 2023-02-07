<?php

class ReceiveingCredit{

    private static $TABLE_TRANSACTION = "transaction";
    private static $TABLE_WALLET = "wallet";
    private static $TABLE_CONTRACT = "contract";
    private static $TABLE_USER_CONTRACTS = "user_contracts";
    private static $TABLE_USER_PERSONAL_INFO = "user_personal_info";

    private $conn = null;
    private $con = null;

    public function __construct($userDB, $walletDB) { 
        try {
            $this->conn = $userDB;
            $this->con = $walletDB;
        } catch(PDOException $ex) {
            echo "Connection error: " . die($ex->getMessage());
        }    
    }

    public function totalReceiveingCredit($receiveingCreditUserId){
        try{
            
            $allReceiveingCredit = $this->con->prepare(" SELECT sum(t.transaction_credit) AS 
            total_receiveing_credit FROM " . self::$TABLE_TRANSACTION 
            . " AS t INNER JOIN " . self::$TABLE_WALLET 
            . " AS w ON w.id = t.wu_receiver_key AND w.id != t.wu_transend_key"
            . " WHERE w.personal_user_id = :personal_user_id ");
            
            $allReceiveingCredit->bindParam(":personal_user_id", $receiveingCreditUserId);
            $allReceiveingCredit->execute();
            return $allReceiveingCredit;

        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }
    }

    public function allReceiveingCredit($receiveingCreditUserId){
        try{
            
            $allReceiveingCredit = $this->con->prepare(" SELECT t.wu_transend_key, t.transaction_credit 
            , t.date_transaction , t.time_transaction FROM " . self::$TABLE_TRANSACTION 
            . " AS t INNER JOIN " . self::$TABLE_WALLET . " AS w "
            . " WHERE w.id != t.wu_transend_key AND w.id = t.wu_receiver_key 
            AND w.personal_user_id = :personal_user_id ORDER BY t.date_transaction DESC ");
            
            $allReceiveingCredit->bindParam(":personal_user_id", $receiveingCreditUserId);
            $allReceiveingCredit->execute();
            return $allReceiveingCredit;

        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }
    }

    public function getSenderFullname($senderKey) {

        try {
            
            $this->con->beginTransaction();

            $allSendingCredit = $this->con->prepare(" SELECT w.personal_user_id 
            FROM " . self::$TABLE_TRANSACTION . " AS t INNER JOIN " . self::$TABLE_WALLET . " AS w "
            . " ON w.id = t.wu_transend_key WHERE t.wu_transend_key = :wu_transend_key ");
            
            $allSendingCredit->bindParam(":wu_transend_key", $senderKey);
            $allSendingCredit->execute();
            $data = $allSendingCredit->fetch(PDO::FETCH_ASSOC);

            if(count($data) > 0) {
                
                $senderId = $data['personal_user_id'];
                $userS = $this->conn->prepare(" SELECT CONCAT(fname, ' ', family) AS fullName FROM "
                . self::$TABLE_USER_PERSONAL_INFO . " WHERE id = :id ");

                $userS->bindParam(':id', $senderId);
                $userS->execute();
                $s = $userS->fetch(PDO::FETCH_ASSOC);  
            }

            $this->con->commit();
            return implode('',$s);
        } catch (Exception $exception) {
            die($exception->getMessage());
        }

    }

}

?>