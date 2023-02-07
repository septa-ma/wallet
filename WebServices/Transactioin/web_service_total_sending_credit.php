<?php

include_once '../../Functions/Transaction/all_sending_credit.php';
include_once '../../Functions/Connection/connection.php';

$Database = new Database();
$userDB = $Database->user_connection();
$walletDB = $Database->wallet_connection();

$asc = new SendingCredit($userDB, $walletDB);

if (isset($_POST['id'])){ // or personal_user_id

    $personalId = $_POST['id'];
    $result = $sc->totalSendingCredit($personalId);
    $Row = $result->fetch(PDO::FETCH_ASSOC);
    if($result->rowCount() == 1) {

        $response['exist'] = 'ok';
        $response["total_sending_credit"] = $Row['total_sending_credit'];
        
        $js_response = json_encode($response, JSON_UNESCAPED_UNICODE);
        echo $js_response;
    
    } else {
        die("ERROR");
    }
} else {
    echo 'ERROR!';
}

?>