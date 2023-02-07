<?php

class SendingCredit{
 
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

    public function totalSendingCredit($sendingCreditUserId){
        try{
            
            $allSendingCredit = $this->con->prepare(" SELECT sum(t.transaction_credit) AS 
            total_sending_credit FROM " . self::$TABLE_TRANSACTION 
            . " AS t INNER JOIN " . self::$TABLE_WALLET 
            . " AS w ON w.id = t.wu_transend_key AND w.id != t.wu_receiver_key "
            . " WHERE w.personal_user_id = :personal_user_id ");
            
            $allSendingCredit->bindParam(":personal_user_id", $sendingCreditUserId);
            $allSendingCredit->execute();
            return $allSendingCredit;

        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }
    }

    public function allSendingCredit($sendingCreditUserId) {
        try {            
            $allSendingCredit = $this->con->prepare(" SELECT t.wu_receiver_key , t.transaction_credit 
            , t.date_transaction , t.time_transaction FROM " . self::$TABLE_TRANSACTION 
            . " AS t INNER JOIN " . self::$TABLE_WALLET . " AS w "
            . " WHERE w.id = t.wu_transend_key AND w.id != t.wu_receiver_key 
            AND w.personal_user_id = :personal_user_id ORDER BY t.date_transaction DESC ");
            
            $allSendingCredit->bindParam(":personal_user_id", $sendingCreditUserId);
            $allSendingCredit->execute();
            return $allSendingCredit;
 
        } catch(PDOException $ex) {
            echo "Connection error: " . die($ex->getMessage());
        }
    }

    public function getReceiverFullname($receiverKey) {

        try {

            $this->con->beginTransaction();

            $allReceiveingCredit = $this->con->prepare(" SELECT w.personal_user_id 
            FROM " . self::$TABLE_TRANSACTION . " AS t INNER JOIN " . self::$TABLE_WALLET . " AS w "
            . " ON w.id = t.wu_receiver_key WHERE t.wu_receiver_key = :wu_receiver_key ");
            
            $allReceiveingCredit->bindParam(":wu_receiver_key", $receiverKey);
            $allReceiveingCredit->execute();
            $data = $allReceiveingCredit->fetch(PDO::FETCH_ASSOC);

            if(count($data) > 0) {

                $receiverId = $data['personal_user_id'];
                $userS = $this->conn->prepare(" SELECT CONCAT(fname, ' ', family) AS fullName FROM "
                . self::$TABLE_USER_PERSONAL_INFO . " WHERE id = :id ");

                $userS->bindParam(':id', $receiverId);
                $userS->execute();
                $r = $userS->fetch(PDO::FETCH_ASSOC); 
            }

            $this->con->commit();
            return implode('',$r);
        } catch (Exception $exception) {
            die($exception->getMessage());
        }
        
    }


}

?>