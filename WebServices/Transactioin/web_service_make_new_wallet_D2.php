<?php

include_once '../../Functions/Transaction/new_user_wallet_D2.php';
include_once '../../Functions/Connection/connection.php';

$Database = new Database();
$walletDB = $Database->wallet_connection();
$uw = new NewUserWalletD2($walletDB);
// echo 0;
if ( isset($_REQUEST['walletKey']) && isset($_REQUEST['projectName']) ) { 

    // validation datas!!!
    $walletKey = $_REQUEST['walletKey'];
    $projectName = $_REQUEST['projectName'];
    
    $response = $uw->newUserWalletViaAPID2($walletKey, $projectName); 
    $j_response = json_encode($response, JSON_UNESCAPED_UNICODE);
    echo $j_response;

} else {
    die("Does not get data.");
}

?>