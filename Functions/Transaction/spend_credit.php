<?php

class SpendCredit
{
    private static $TABLE_WALLET = "wallet";
    private static $TABLE_TRANSACTION = "transaction";
    private static $TABLE_USER_CONTRACTS = "user_contracts";
    private static $TABLE_CONTRACT = "contract";

    private $con = null;

    public function __construct($walletDB) { 
        try{
            $this->con = $walletDB;
        }catch(PDOException $ex){
            echo "Connection error: " . die($ex->getMessage());
        }
        
    }

    public function spendCredit($senderUserId, $receiverUserId, $input) { //, $transactionStatus, $referenceNum){
    
        try{

            $this->con->beginTransaction();

            // if ($transactionStatus == 100 && $referenceNum != 0) {

                $walletContInfo = $this->con->prepare(" SELECT uc.conrtact_user_credit, c.transaction_fee 
                FROM " . self::$TABLE_USER_CONTRACTS . " AS uc INNER JOIN " 
                . self::$TABLE_CONTRACT . " AS c ON uc.contract_id = c.id " 
                . " WHERE uc.personal_user_id = :personal_user_id AND uc.wallet_type LIKE '8%' 
                AND uc.active = :active AND uc.delete_key = :deleteKey LIMIT 1 ");

                $active = 11;
                $deleteKey = 90;
                $walletContInfo->bindParam(":active", $active);
                $walletContInfo->bindParam(":deleteKey", $deleteKey);
                $walletContInfo->bindParam(":personal_user_id",$senderUserId);
                $walletContInfo->execute();
                $r2 = $walletContInfo->fetch(PDO::FETCH_ASSOC);
// print_r($r2);echo $senderUserId;
                // $contCreditS = $r2['conrtact_user_credit'];echo $contCreditS.'</br>';
                // $trxFee = $r2['transaction_fee'];echo $trxFee.'</br>';
                if ($walletContInfo->rowCount() == 0) {
                    return 0;
                } elseif ($walletContInfo->rowCount() >= 1) {
                    $contCreditS = $r2['conrtact_user_credit'];//echo $contCreditS;
                    $trxFee = $r2['transaction_fee'];//echo $trxFee;
                }

                // get sender user own credit
                $getUserOwnCredit = $this->con->prepare(" SELECT id, own_credit FROM "
                . self::$TABLE_WALLET . " WHERE personal_user_id = :personal_user_id LIMIT 1 ");

                $getUserOwnCredit->bindParam(":personal_user_id",$senderUserId);
                $getUserOwnCredit->execute();
                $r5 = $getUserOwnCredit->fetch(PDO::FETCH_ASSOC);
                $senderWalletId = $r5['id'];//echo $senderWalletId.'</br>';
                $ownCreditS = $r5['own_credit'];//echo $ownCreditS.'</br>';

                // get receiver user own credit 
                $getReceiverOwnCredit = $this->con->prepare(" SELECT id, own_credit FROM "
                . self::$TABLE_WALLET . " WHERE personal_user_id = :personal_user_id LIMIT 1 ");

                $getReceiverOwnCredit->bindParam(":personal_user_id",$receiverUserId);
                $getReceiverOwnCredit->execute();
                $r6 = $getReceiverOwnCredit->fetch(PDO::FETCH_ASSOC);
                $receiverWalletId = $r6['id'];//echo $receiverWalletId.'</br>';
                $ownCreditR = $r6['own_credit'];//echo $ownCreditR.'</br>';

                // calculate user's total credit
                $totalCredit = $contCreditS + $ownCreditS; //+ (100 * $trxFee);
                // echo $totalCredit.'</br>';
                if ($input <= $totalCredit && $receiverWalletId != $senderWalletId) {
// this is just for mali contracts
                    if ( /* $contCreditS == 0 && */ $ownCreditS != 0 && $ownCreditS >= $input ) {
                        
                        $newInput = abs($ownCreditS - $input);
                        // adding new transaction
                        $query4 = $this->con->prepare(" INSERT INTO " . self::$TABLE_TRANSACTION
                        . " ( wu_transend_key, wu_receiver_key, transaction_credit, transaction_status,
                        reference_number, date_transaction, time_transaction, delete_key )
                        VALUES ( :wu_transend_key, :wu_receiver_key, :transaction_credit, :transaction_status,
                        :reference_number, :date_transaction, :time_transaction, 80 ) ");

                        // $transactionStatus = 0;
                        // getting now time and date
                        $dNow = date("Y-m-d", strtotime("now"));
                        $tNow = date("H:i:s", strtotime("now"));
                        // echo $input.'</br>';
                        // echo $transactionStatus.'</br>';
                        // echo $referenceNum.'</br>';
                        $transactionStatus = 100;
                        $referenceNum = $this->generateCode(10);
                        
                        $query4->bindParam(":wu_transend_key", $senderWalletId);
                        $query4->bindParam(":wu_receiver_key", $receiverWalletId);
                        $query4->bindParam(":transaction_credit", $input);
                        $query4->bindParam(":transaction_status", $transactionStatus);
                        $query4->bindParam(":reference_number", $referenceNum);
                        $query4->bindParam(":date_transaction", $dNow);
                        $query4->bindParam(":time_transaction", $tNow);
                        $query4->execute();

                        // increase receiver credit
                        $newCreditR = abs($ownCreditR + $input);
                        // echo $newCreditR;
                        $query5 = $this->con->prepare(" UPDATE "
                        . self::$TABLE_WALLET . " SET "
                        . " own_credit = :own_credit 
                        WHERE id = :id AND personal_user_id = :wspkey ");

                        $query5->bindParam(":id", $receiverWalletId);
                        $query5->bindParam(":wspkey", $receiverUserId);
                        $query5->bindParam(":own_credit", $newCreditR);
                        $query5->execute();

                        // decrease sender credit
                        $newCreditS = abs($ownCreditS - $input);
                        // echo $newCreditS;
                        $query6 = $this->con->prepare(" UPDATE "
                        . self::$TABLE_WALLET . " SET "
                        . " own_credit = :own_credit 
                        WHERE id = :id AND personal_user_id = :wupkey ");

                        $query6->bindParam(":id", $senderWalletId);
                        $query6->bindParam(":wupkey", $senderUserId);
                        $query6->bindParam(":own_credit", $newCreditS);
                        $query6->execute();

                        // echo 'successful'.'</br>';
                        // return 7000;
                    } 
                }
            //}

            $this->con->commit();
            return 7000;
        } catch (Exception $ex) {
            $this->con->rollBack();
            die($ex->getMessage());
        }
    }
    
    public function generateCode($limit){
        $code = '';
        for($i = 0; $i < $limit; $i++) { $code .= mt_rand(0, 9); }
        return $code;
    }
    
}

?> 