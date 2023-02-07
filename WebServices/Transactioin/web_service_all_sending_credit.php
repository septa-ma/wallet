<?php

include_once '../../Functions/Transaction/all_sending_credit.php';
include_once '../../Functions/Connection/connection.php';

$Database = new Database();
$userDB = $Database->user_connection();
$walletDB = $Database->wallet_connection();

$asc = new SendingCredit($userDB, $walletDB);

if (isset($_POST['id'])) { // or personal_user_id
 
   $personalId = $_POST['id'];
    $result = $asc->allSendingCredit($personalId);
    $Row= $result->fetchAll(PDO::FETCH_ASSOC);
    // print_r($Row);
    $arr = array(); 
    if($result->rowCount() > 0){
        for($i = 0; $i < $result->rowCount(); $i++) {
            
            // $response['exist'] = 'ok';
            $response[$i]["rName"] = $asc->getReceiverFullname($Row[$i]['wu_receiver_key']);
            $response[$i]["amount"] = $Row[$i]['transaction_credit'];
            $response[$i]["date"] = $Row[$i]['date_transaction'];
            $response[$i]["time"] = $Row[$i]['time_transaction'];
        }
        $js_response = json_encode($response, JSON_UNESCAPED_UNICODE);
        echo $js_response;

    } else {
        die("ERROR");
    }

} else {
    echo 'ERROR!';
}

?>