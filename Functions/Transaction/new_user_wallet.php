<?php

class NewUserWallet {

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

    public function newUserWalletViaAPI($id, $contName){

        try{
            $this->conn->beginTransaction();

            $newUser = $this->conn->prepare(" SELECT created_at FROM " 
            . self::$TABLE_USER_PERSONAL_INFO . " WHERE qrcode IS NULL LIMIT 1");
            $newUser->execute();
            $row = $newUser->fetch(PDO::FETCH_ASSOC);

            if ( count($row) == 0 ) {
                return -1;
            } else if ( count($row) >= 1 ) {
                $created_at = $row['created_at'];

                // make qr for new user
                $stmt2 = $this->conn->prepare(" UPDATE " . self::$TABLE_USER_PERSONAL_INFO .
                " SET qrcode = :qr WHERE id = :id ");
                
                $strqr = "the user's QR is :";
                $var = str_shuffle($strqr);
                $QR = hash('md5',$var);

                $stmt2->bindParam(":id", $id);
                $stmt2->bindParam(":qr", $QR);
                $stmt2->execute();
                $this->con->beginTransaction();

                // make new wallet
                $makeWallet = $this->con->prepare(" INSERT INTO ". self::$TABLE_WALLET 
                . " ( personal_user_id, user_private_key, delete_key )
                    VALUES ( :personal_user_id, :user_private_key, :delete_key ) ");
                $delete_key_w = 10;
                $str = $this->shCharacter();
                $numR = $this->nonRepeat( 685, 5681, 1);
                $walletKey = hash('sha256',$numR.$str.$created_at);
                
                $makeWallet->bindParam(":personal_user_id",$id);
                $makeWallet->bindParam(":user_private_key",$walletKey);
                $makeWallet->bindParam(":delete_key",$delete_key_w);
                $makeWallet->execute();
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

                    // add access to user to do finantial transaction
                    $finantialTrx = $this->con->prepare(" INSERT INTO ". self::$TABLE_USER_CONTRACTS
                    . " ( contract_id, personal_user_id, conrtact_user_credit, wallet_type, active, delete_key )
                    VALUES ( :contract_id, :personal_user_id, :conrtact_user_credit, :wallet_type, :active, :delete_key ) ");

                    $creditAmount = 0;
                    $walletType = 8;
                    $active = 11;
                    $deleteKey = 90;

                    $finantialTrx->bindParam(":contract_id", $contId);
                    $finantialTrx->bindParam(":personal_user_id", $id);
                    $finantialTrx->bindParam(":conrtact_user_credit", $creditAmount);
                    $finantialTrx->bindParam(":wallet_type", $walletType);
                    $finantialTrx->bindParam(":active", $active);
                    $finantialTrx->bindParam(":delete_key", $deleteKey);
                    $finantialTrx->execute();
                }
                $this->con->commit();
            }
            $this->conn->commit();
            return 7000;

        } catch (Exception $ex) {
            die($ex->getMessage());
        }

    }


    public function nonRepeat($min,$max,$count) {

        if($max - $min < $count) {
            return false;
        }

        $nonrepeatarray = array();
        for($i = 0; $i < $count; $i++) {
            $rand = $this->random_float($min,$max);

            while(in_array($rand,$nonrepeatarray)) {
                $rand = $this->random_float($min,$max);
            }

            $nonrepeatarray[$i] = $rand;
        }
        return implode(',',$nonrepeatarray);
    }

    public function random_float($min,$max){
        return ($min + lcg_value()*(abs($max - $min)));
    }

    public function shCharacter(){
        $str = "NewUserWallet#20!";
        return str_shuffle($str);
    }

}

?>