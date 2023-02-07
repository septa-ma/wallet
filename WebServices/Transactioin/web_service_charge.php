<?php
include_once '../../Functions/Transaction/charge_wallet.php';
include_once '../../Functions/Connection/connection.php';

$Database = new Database();
$walletDB = $Database->wallet_connection();
$cw = new ChargeWallet($walletDB);

if ( isset($_POST['senderId']) && isset($_POST['input']) && isset($_POST['trxSt']) && isset($_POST['refNum']) ) { 

    // validation datas!
    
    $personalSId = $_POST['senderId'];
    // $personalRId = $_POST['receiverId'];
    $input = $_POST['input'];
    $trxSt = $_POST['trxSt'];
    $refNum = $_POST['refNum'];
    $response = $cw->chargeWallet($personalSId, /* $personalRId, */ $input, $trxSt, $refNum);
    // print($response);
    $j_response = json_encode($response, JSON_UNESCAPED_UNICODE);
    echo $j_response;
    // return $j_response;

} else { 
    die("Does not get data.");
}