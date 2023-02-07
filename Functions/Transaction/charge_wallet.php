<?php

class ChargeWallet{
    
    private static $TABLE_TRANSACTION = "transaction";
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

    public function charge_wallet(){
        try {

            $this->conn->beginTransaction();
            $this->con->beginTransaction();

            // checking whos charging her|his wallet
            $findUser = $this->conn->prepare(" SELECT personal_info_id, input, 
            transaction_status, reference_number, contract_name FROM " 
            . self::$TABLE_RELATION 
            . " WHERE credit_status = :status LIMIT 1 ");

            $status = 23;
            $findUser->bindParam(":status", $status);
            $findUser->execute();
            $row = $findUser->fetch(PDO::FETCH_ASSOC);
            $num = count($row);

            if($num > 0) {

                for ( $i = 0; $i < $num; $i++ ) {

                    $userId = $row[$i]['personal_info_id'];
                    $chargeAmount = $row[$i]['input'];
                    $transactionStatus = $row[$i]['transaction_status'];
                    $referenceNum = $row[$i]['reference_number'];
                    $contractName = $row['contract_name'];

                    if ($transactionStatus == 100 && $referenceNum != 0) {
                        
                        // getting her|his old wallet credit
                        $getOwnerCredit = $this->con->prepare(" SELECT own_credit FROM " . self::$TABLE_WALLET 
                        . " WHERE personal_user_id = :user_id ");

                        $getOwnerCredit->bindParam(':user_id',$userId);
                        $getOwnerCredit->execute();
                        $creditData = $getOwnerCredit->fetch(PDO::FETCH_ASSOC);

                        if ( count($creditData) > 0 ) {

                            $oldCredit = $creditData['own_credit'];

                            // getting contract's credit
                            $getContCredit = $this->con->prepare(" SELECT contract_credit FROM ". self::$TABLE_CONTRACT 
                            . " WHERE contract_name = :contract_name LIMIT 1 ");

                            $getContCredit->bindParam(":contract_name", $contractName);
                            $getContCredit->execute();
                            $roW = $getContCredit->fetch(PDO::FETCH_ASSOC);

                            if ( count($roW) > 0 ) {

                                $oldContCredit = $roW['contract_credit'];

                                // increaseing contract's crdit 
                                $increaseingContCredit = $this->con->prepare(" UPDATE ". self::$TABLE_CONTRACT 
                                . " SET contract_credit = :contract_credit "
                                . " WHERE contract_name = :contract_name ");

                                $newContCredit = $oldContCredit + $chargeAmount;
                                $increaseingContCredit->bindParam(":contract_name", $contractName);
                                $increaseingContCredit->bindParam(":contract_credit", $newContCredit);
                                $increaseingContCredit->execute();
                                
                                // increaseing her|his wallet credit
                                $chargeWallet = $this->con->prepare(" UPDATE " . self::$TABLE_WALLET . "
                                SET own_credit = :own_credit 
                                WHERE personal_user_id = :personal_user_id ");

                                $newCredit = $oldCredit + $chargeAmount;
                                $chargeWallet->bindParam(":personal_user_id", $userId);
                                $chargeWallet->bindParam(":own_credit", $newCredit);
                                $chargeWallet->execute();

                                // changeing relation table status
                                $changeCreditStatus = $this->conn->prepare(" UPDATE " . self::$TABLE_RELATION . 
                                " SET 
                                credit_status = :credit_status, 
                                input = :input,
                                transaction_status = :transaction_status,
                                reference_number = :reference_number
                                WHERE personal_info_id = :PUId ");

                                $newStatus = 80;
                                $input = 0;
                                $transaction_status = 7000;
                                $reference_number = 0;
                                $changeCreditStatus->bindParam(":credit_status", $newStatus);
                                $changeCreditStatus->bindParam(":input", $input);
                                $changeCreditStatus->bindParam(":transaction_status", $transaction_status);
                                $changeCreditStatus->bindParam(":reference_number", $reference_number);
                                $changeCreditStatus->bindParam(":PUId", $userId);
                                $changeCreditStatus->execute();

                                // getting now time and date
                                $dNow = date("Y-m-d", strtotime("now"));
                                $tNow = date("H:i:s", strtotime("now"));

                                // saveing transaction info
                                $query4 = $this->con->prepare(" INSERT INTO " . self::$TABLE_TRANSACTION
                                . " ( wu_transend_key, wu_receiver_key, transaction_credit, transaction_status,
                                reference_number, date_transaction, time_transaction, delete_key )
                                VALUES ( :wu_transend_key, :wu_receiver_key, :transaction_credit, :transaction_status,
                                :reference_number, :date_transaction, :time_transaction, 80 ) ");
                                

                                $query4->bindParam(":wu_transend_key", $userId);
                                $query4->bindParam(":wu_receiver_key", $userId);
                                $query4->bindParam(":transaction_credit", $input);
                                $query4->bindParam(":transaction_status", $transactionStatus);
                                $query4->bindParam(":reference_number", $referenceNum);
                                $query4->bindParam(":date_transaction", $dNow);
                                $query4->bindParam(":time_transaction", $tNow);
                                $query4->execute();
                            }
                        }
                        echo 'successful';
                    }
                    else {
                        // get status and save it in table
                    }
                }
            }

            $this->con->commit();
            $this->conn->commit();
        }catch (Exception $ex){
            $this->con->rollBack();
            $this->conn->rollBack();
            die($ex->getMessage());
        }
    }

// API 
    public function chargeWallet($personalUId, $input, $transactionStatus, $referenceNum){
        // echo $personalUId, $input, $transactionStatus, $referenceNum;
        try {
            
            $this->con->beginTransaction();
            
            $walletContInfo = $this->con->prepare( " SELECT w.id AS wallet_id, w.own_credit, c.id, c.total_credit 
                , c.transaction_fee FROM " . self::$TABLE_WALLET . " AS w INNER JOIN " 
                . self::$TABLE_USER_CONTRACTS . " AS uc ON uc.personal_user_id = w.personal_user_id "
                . " INNER JOIN " . self::$TABLE_CONTRACT . " AS c ON uc.contract_id = c.id " 
                . " WHERE uc.personal_user_id = :personal_user_id LIMIT 1 ");

                // $walletContInfo->bindParam(":contrtact_name", $contractName);
                $walletContInfo->bindParam(":personal_user_id",$personalUId);
                $walletContInfo->execute();
                $r2 = $walletContInfo->fetch(PDO::FETCH_ASSOC);
                if (count($r2) == 0) {
                    return 0;
                } elseif (count($r2) >= 1) {
                    $walletId = $r2['wallet_id'];
                    $oldCredit = $r2['own_credit'];
                    $oldContCredit = $r2['total_credit'];
                    $contId = $r2['id'];
                    $trxFee = $r2['transaction_fee'];
                }
            if ($transactionStatus == 500 && $referenceNum != null ) { 
                
                // saveing transaction info
                $query4 = $this->con->prepare(" INSERT INTO " . self::$TABLE_TRANSACTION
                . " ( wu_transend_key, wu_receiver_key, transaction_credit, transaction_status,
                reference_number, date_transaction, time_transaction, delete_key )
                VALUES ( :wu_transend_key, :wu_receiver_key, :transaction_credit, :transaction_status,
                :reference_number, :date_transaction, :time_transaction, 80 ) ");
                // getting now time and date
                $dNow = date("Y-m-d", strtotime("now"));
                $tNow = date("H:i:s", strtotime("now"));

                $query4->bindParam(":wu_transend_key", $walletId);
                $query4->bindParam(":wu_receiver_key", $walletId);
                $query4->bindParam(":transaction_credit", $input);
                $query4->bindParam(":transaction_status", $transactionStatus);
                $query4->bindParam(":reference_number", $referenceNum);
                $query4->bindParam(":date_transaction", $dNow);
                $query4->bindParam(":time_transaction", $tNow);
                $query4->execute();
                
            } elseif ($transactionStatus == 100 && $referenceNum != null) {
                
                // increaseing contract's credit 
                $increaseingContCredit = $this->con->prepare(" UPDATE ". self::$TABLE_CONTRACT 
                . " SET total_credit = :total_credit "
                . " WHERE id = :id ");
        
                $newContCredit = $oldContCredit + $input;
                $increaseingContCredit->bindParam(":id", $contId);
                $increaseingContCredit->bindParam(":total_credit", $newContCredit);
                $increaseingContCredit->execute();
                
                // increaseing her|his wallet credit
                $chargeWallet = $this->con->prepare(" UPDATE " . self::$TABLE_WALLET . "
                SET own_credit = :own_credit 
                WHERE personal_user_id = :personal_user_id ");
        
                // ask for this calculaters...
                $newCredit = ($oldCredit + $input);
                $chargeWallet->bindParam(":personal_user_id", $personalUId);
                $chargeWallet->bindParam(":own_credit", $newCredit);
                $chargeWallet->execute();
                
                // saveing transaction info
                $query4 = $this->con->prepare(" INSERT INTO " . self::$TABLE_TRANSACTION
                . " ( wu_transend_key, wu_receiver_key, transaction_credit, transaction_status,
                reference_number, date_transaction, time_transaction, delete_key )
                VALUES ( :wu_transend_key, :wu_receiver_key, :transaction_credit, :transaction_status,
                :reference_number, :date_transaction, :time_transaction, 80 ) ");
                // getting now time and date
                $dNow = date("Y-m-d", strtotime("now"));
                $tNow = date("H:i:s", strtotime("now"));
        
                $query4->bindParam(":wu_transend_key", $walletId);
                $query4->bindParam(":wu_receiver_key", $walletId);
                $query4->bindParam(":transaction_credit", $input);
                $query4->bindParam(":transaction_status", $transactionStatus);
                $query4->bindParam(":reference_number", $referenceNum);
                $query4->bindParam(":date_transaction", $dNow);
                $query4->bindParam(":time_transaction", $tNow);
                $query4->execute();
            }
            $this->con->commit();
            return 7000;
        }catch (Exception $ex){
            $this->con->rollBack();
            die($ex->getMessage());
        }
    }

}

?>