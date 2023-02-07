<?php

include_once '../../Functions/Transaction/all_receiveing_credit.php';
include_once '../../Functions/Connection/connection.php';

$Database = new Database();
$userDB = $Database->user_connection();
$walletDB = $Database->wallet_connection();

$arc = new ReceiveingCredit($userDB, $walletDB);

if (isset($_POST['id'])){ // or personal_user_id

    $personalId = $_POST['id'];
    $result = $rc->totalReceiveingCredit($personalId);
    $Row = $result->fetch(PDO::FETCH_ASSOC);
    if($result->rowCount() == 1) {

        $response['exist'] = 'ok';
        $response["total_receiveing_credit"] = $Row['total_receiveing_credit'];
        
        $js_response = json_encode($response, JSON_UNESCAPED_UNICODE);
        echo $js_response;
    
    } else {
        die("ERROR");
    }
} else {
    echo 'ERROR!';
}

?>