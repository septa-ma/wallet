<?php

class CheckCreditForSpend
{
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

     public function checkCreditForSpend($contName, $senderUserId, $input){
        try {

            $walletContInfo = $this->con->prepare( " SELECT w.own_credit, uc.conrtact_user_credit, 
            c.id, c.contract_name, c.end_date, c.transaction_fee 
            FROM " . self::$TABLE_WALLET . " AS w INNER JOIN " 
            . self::$TABLE_USER_CONTRACTS . " AS uc ON uc.personal_user_id = w.personal_user_id "
            . " INNER JOIN " . self::$TABLE_CONTRACT . " AS c ON uc.contract_id = c.id " 
            . " WHERE uc.personal_user_id = :personal_user_id 
            AND
            c.contract_name = :contract_name LIMIT 1 ");
            
            $walletContInfo->bindParam(":contract_name", $contName);
            $walletContInfo->bindParam(":personal_user_id",$senderUserId);
            $walletContInfo->execute();
            $r2 = $walletContInfo->fetch(PDO::FETCH_ASSOC);
            if (count($r2) == 0) {
                return 0;
            } elseif (count($r2) >= 1) {
                $ownCreditS = $r2['own_credit'];
                $contCreditS = $r2['conrtact_user_credit'];
                $contId = $r2['id'];
                $contNameS = $r2['contract_name'];
                $dlDate = $r2['end_date'];
                $trxFee = $r2['transaction_fee'];
            }

            // getting now time and date
            $dNow = date("Y-m-d", strtotime("now"));
            $tNow = date("H:i:s", strtotime("now"));

            if ($contNameS == $contName) {

                if($dlDate > $dNow ) { 

                    // calculate user's total credit
                    $totalCredit = $contCreditS + $ownCreditS ;

                    // check user's creidt option
                    if ( $input > $totalCredit || $totalCredit == 0 ) {
                        $remaining = abs($input - $totalCredit);
                       // echo 'you can not do transaction, you need '. $remaining .' more credit.';
                        return 5025; 

                    } elseif ($input <= $totalCredit) {
                        // echo 'you can do transaction'.'</br>';
                        return 5020;
                    }

                // date and time expired.
                } else { 
                    // echo "contract's date and time is expired".'</br>';
                    return 5015;
                } 
            // diffrent contract
            } else {
                // echo 'user is not in this contract.'.'</br>';
                return 5010;
            }
            
        } catch (Exception $ex) {
            die($ex->getMessage());
        }
    }
    
    public function checkCreditForCharge($contName, $userId){
        try {
 
            $walletContInfo = $this->con->prepare( " SELECT c.contract_name, c.end_date FROM " 
            . self::$TABLE_CONTRACT . " AS c INNER JOIN " . self::$TABLE_USER_CONTRACTS  
            . " AS c ON uc.contract_id = c.id " 
            . " WHERE uc.personal_user_id = :personal_user_id LIMIT 1 ");

            $walletContInfo->bindParam(":personal_user_id",$userId);
            $walletContInfo->execute();
            $r2 = $walletContInfo->fetch(PDO::FETCH_ASSOC);

            if (count($r2) == 0) {
                return 0;
            } elseif (count($r2) >= 1) {
                $contNameS = $r2['contract_name'];
                $dlDate = $r2['end_date'];
            }

            // getting now time and date
            $dNow = date("Y-m-d", strtotime("now"));
            $tNow = date("H:i:s", strtotime("now"));

            if ($contNameS == $contName) {
                if($dlDate > $dNow ) {

                    // echo 'you can do transaction'.'</br>';
                    return 5020;

                // date and time expired.
                } else { 
                    // echo "contract's date and time is expired".'</br>';
                    return 5015;
                } 
            // diffrent contract
            } else {
                // echo 'user is not in this contract.'.'</br>';
                return 5010;
            }
            
        } catch (Exception $ex) {
            die($ex->getMessage());
        }
    }

}

?>