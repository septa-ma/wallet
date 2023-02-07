<?php
include_once '../../Functions/Transaction/check_credit_for_spend.php';
include_once '../../Functions/Connection/connection.php';

$Database = new Database();
$walletDB = $Database->wallet_connection();
$e = new CheckCreditForSpend($walletDB);

if (isset($_POST['contName']) && isset($_POST['senderId'])) { 

    // validation datas!
    $contName = $_POST['contName'];
    $personalSId = $_POST['senderId'];
    $response = $e->checkCreditForCharge($contName, $personalSId);
    $j_response = json_encode($response, JSON_UNESCAPED_UNICODE);
    echo $j_response;

} else {
    die("Does not get data.");
} 