<?php

include_once '../../Functions/Transaction/new_user_wallet.php';
include_once '../../Functions/Connection/connection.php';

$Database = new Database();
$userDB = $Database->user_connection();
$walletDB = $Database->wallet_connection();

$uw = new NewUserWallet($userDB, $walletDB);
// echo 0;
if ( isset($_REQUEST['userId']) && isset($_REQUEST['projectName']) ) { 

    // validation datas!!!
    $personalSId = $_REQUEST['userId'];
    $projectName = $_REQUEST['projectName'];
    
    $response = $uw->newUserWalletViaAPI($personalSId, $projectName); 
    $j_response = json_encode($response, JSON_UNESCAPED_UNICODE);
    echo $j_response;

} else {
    die("Does not get data.");
}

?>