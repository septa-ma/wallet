<?php
include_once '../../Functions/Transaction/balance_data.php';
include_once '../../Functions/Connection/connection.php';

$Database = new Database();
$walletDB = $Database->wallet_connection();
$bd = new BalanceData($walletDB);

if (isset($_POST['id'])){ // or personal_user_id

    $personalId = $_POST['id'];
    $res = $bd->balanceCreditRestful($personalId);
    $row = $res->fetch(PDO::FETCH_ASSOC);
    // print_r($row);
    if($res->rowCount() == 1){
        $response['exist'] = 'ok';
        // $response["personal_user_id"] = $row['personal_user_id'];
        $response["own_credit"] = $row['own_credit'];
        // $response["conrtact_user_credit"] = $row['conrtact_user_credit'];
        // $response["contract_name"] = $row['contract_name'];
        // $response["end_date"] = $row['end_date'];
        // $response["transaction_fee"] = $row['transaction_fee'];
        // $response["contract_detail"] = $row['contract_detail'];
        // $response["start_time"] = $row['start_time'];
        // $response["start_date"] = $row['start_date'];
        $j_response = json_encode($response, JSON_UNESCAPED_UNICODE);
        echo $j_response;
    } else {
        die("ERROR");
    }

} else {
    die("Does not get id.");
}


?>