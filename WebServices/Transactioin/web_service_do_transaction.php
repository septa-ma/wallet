<?php
include_once '../../Functions/Transaction/spend_credit.php';
include_once '../../Functions/Connection/connection.php';

$Database = new Database();
$walletDB = $Database->wallet_connection();
$sc = new SpendCredit($walletDB);

if ( isset($_POST['senderId']) && isset($_POST['receiverId']) && isset($_POST['input']) ) {// && isset($_POST['trxSt']) && isset($_POST['refNum']) ) { 

    // validation datas! 
    
    $personalSId = $_POST['senderId'];
    $personalRId = $_POST['receiverId'];
    $input = $_POST['input'];
    // $trxSt = $_POST['trxSt'];
    // $refNum = $_POST['refNum'];
    $response = $sc->spendCredit($personalSId, $personalRId, $input); //, $trxSt, $refNum);
    // print($response);
    $j_response = json_encode($response, JSON_UNESCAPED_UNICODE);
    // echo 'ok';
    echo $j_response;

} else {
    die("Does not get data.");
}