<?php 

class BalanceData{

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

    public function testEvent(){
        try{
            $stm = $this->con->prepare(" CREATE EVENT checkLoginUser
            ON SCHEDULE EVERY 1 MINUTE 
            STARTS CURRENT_TIMESTAMP + INTERVAL 1 DAY
            ENDS CURRENT_TIMESTAMP + INTERVAL 1 YEAR
            COMMENT 'Check relation table for knowing who logined each minute.'
            DO 
            BEGIN
            SELECT user_wallet_key FROM ".  self::$TABLE_RELATION ." WHERE active = :active
            END " ); 

        }catch(Exeption $ex){
            die($ex->getMessage());
        }
    }

    public function allDataRestful($uId){
        try{

            $walletContInfo = $this->con->prepare( " SELECT 
            w.own_credit , uc.conrtact_user_credit , c.contract_name , c.end_date 
            , c.transaction_fee , c.contract_detail , c.start_time , c.start_date 
            FROM " . self::$TABLE_WALLET . " AS w INNER JOIN " 
            . self::$TABLE_USER_CONTRACTS . " AS uc ON uc.personal_user_id = w.personal_user_id
            INNER JOIN " . self::$TABLE_CONTRACT . " AS c ON uc.contract_id = c.id " 
            . " WHERE w.personal_user_id = :personal_user_id LIMIT 1");

            $walletContInfo->bindParam(':personal_user_id',$uId );
            $walletContInfo->execute();
            return $walletContInfo;

        }catch(Exeption $ex){
            die($ex->getMessage());
        }

    }

    public function balanceCreditRestful($uId){
        try{

            $walletCreditInfo = $this->con->prepare( " SELECT own_credit FROM " 
            . self::$TABLE_WALLET . " WHERE personal_user_id = :personal_user_id LIMIT 1");

            $walletCreditInfo->bindParam(':personal_user_id',$uId );
            $walletCreditInfo->execute();
            return $walletCreditInfo;

        }catch(Exeption $ex){
            die($ex->getMessage());
        }

    }

    public function allUserContracts($uId){
        try{

            $walletContInfo = $this->con->prepare( " SELECT 
            uc.conrtact_user_credit , c.contract_name , c.start_date , c.end_date , c.transaction_fee 
            FROM " . self::$TABLE_WALLET . " AS w INNER JOIN " 
            . self::$TABLE_USER_CONTRACTS . " AS uc ON uc.personal_user_id = w.personal_user_id
            INNER JOIN " . self::$TABLE_CONTRACT . " AS c ON uc.contract_id = c.id " 
            . " WHERE w.personal_user_id = :personal_user_id ");

            $walletContInfo->bindParam(':personal_user_id',$uId);
            $walletContInfo->execute();
            return $walletContInfo;

        }catch(Exeption $ex){
            die($ex->getMessage());
        }

    }

    public function balanceDate(){
        try{

            $this->con->beginTransaction();
            $checkRel = $this->conn->prepare(" SELECT user_wallet_key FROM "
            . self::$TABLE_RELATION ." WHERE active = :act ");

            $act = 1;
            $checkRel->bindParam(':act', $act);
            $checkRel->execute();
            $row = $checkRel->fetchAll(PDO::FETCH_ASSOC);
            $num = count($row);

            if($num > 0) {
                for ( $i = 0; $i < $num; $i++ ) {

                    $walletInfo = $this->con->prepare( " SELECT w.user_public_key ,
                    w.own_credit , w.receive_credit , c.contract_name , c.dead_line_date ,
                    c.dead_line_time , c.contract_detail FROM " . self::$TABLE_WALLET .
                    " AS w INNER JOIN " . self::$TABLE_CONTRACT . " AS c ON w.contract_id = c.id
                    WHERE w.user_public_key = :user_public_key ");

                    $walletInfo->bindParam(':user_public_key',$row[$i]['user_wallet_key'] );
                    $walletInfo->execute();
                    $data = $walletInfo->fetch(PDO::FETCH_ASSOC);

                    if(count($data) > 0){

                        $user_public_key = $data['user_public_key'];
                        $own_credit = $data['own_credit'];
                        $receive_credit = $data['receive_credit'];
                        $contract_name = $data['contract_name'];
                        $dead_line_date = $data['dead_line_date'];
                        $dead_line_time = $data['dead_line_time'];
                        $contract_detail = $data['contract_detail'];

                        $mainRel = $this->conn->prepare(" UPDATE " . self::$TABLE_RELATION ." SET 
                        own_credit = :own_credit ,
                        receive_credit = :receive_credit , 
                        contract_name = :contract_name , 
                        dead_line_date = :dead_line_date , 
                        dead_line_time = :dead_line_time ,
                        contract_detail = :contract_detail 
                        WHERE 
                        user_wallet_key = :user_wallet_key ");

                        $mainRel->bindParam(":own_credit", $own_credit);
                        $mainRel->bindParam(":receive_credit", $receive_credit);
                        $mainRel->bindParam(":contract_name", $contract_name);
                        $mainRel->bindParam(":dead_line_date", $dead_line_date);
                        $mainRel->bindParam(":dead_line_time", $dead_line_time);
                        $mainRel->bindParam(":contract_detail", $contract_detail);
                        $mainRel->bindParam(":user_wallet_key", $row[$i]['user_wallet_key']);

                        $mainRel->execute();

                    } else {
                        echo 'error_1';
                    }
                }
            } else {
                echo 'error_2';
            }
            $this->con->commit();

        }catch (Exception $ex){
            die($ex->getMessage());
        }

    }

}

?>

