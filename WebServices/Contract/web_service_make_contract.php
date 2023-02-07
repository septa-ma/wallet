<?php
include '../../Functions/Contract/make_and_impart_contracts.php';
include_once '../../Functions/Connection/connection.php';

$Database = new Database();
$userDB = $Database->user_connection();
$walletDB = $Database->wallet_connection();

$q = new MakeContractAndImpart($walletDB, $userDB);
// $x = 2700;
$objectRow = $q->readAllUserInfo();
$num = $objectRow->rowCount();
 
// $contName = $stDate = $endDate = $description = 
// $totalCr = $creditType = $contFile = $fee = "";
$contName_err = $stDate_err = $contFile_err = $description_err 
= $totalCr_err = $fee_err = $creditType_err = $endDate_err = 
$userList_err = $conditionsList_err = $totalCrCont_err ="";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate contract name
    $input_contName = trim($_POST["contract_name"]);
    if (empty($input_contName)) {
        $contName_err = "Please write a name for contract.";
        echo $contName_err;
    } else {
        $q->contract_name = $input_contName;
    }

    // Validate start date
    $input_stDate = $_POST["start_date"];
    if (empty($input_stDate)) {
        $stDate_err = "Please enter start Date.";
        echo $stDate_err;
    } else if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$input_stDate)) {
        $q->start_date = $input_stDate;
    }

    // Validate end date
    $input_endDate = $_POST["end_date"];
    if (empty($input_endDate)) {
        $endDate_err = "Please enter end Date.";
        echo $endDate_err;
    } else if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$input_endDate)) {
        $q->end_date = $input_endDate;
    }

    // Validate total credit
    $input_totalCr = $_POST["total_credit"];
    if (empty($input_totalCr)) {
        $totalCr_err = "Please enter the total credit amount.";
        echo $totalCr_err;
    } elseif (!ctype_digit($input_totalCr)) {
        $totalCr_err = 'Please enter a positive float value.';
        echo $totalCr_err;
    // } elseif(is_float($input_totalCr)) {
    //     $q->total_credit = $input_totalCr;
    }else{
        $q->total_credit = $input_totalCr;
        // $totalCr_err = 'Please enter a float value.';
        // echo $totalCr_err;
    }

     // Validate total credit contract
     $input_totalCrCont = $_POST["total_credit_contract"];
     if (empty($input_totalCrCont)) {
         $totalCrCont_err = "Please enter the total credit contract amount.";
         echo $totalCrCont_err;
     } elseif (!ctype_digit($input_totalCrCont)) {
         $totalCrCont_err = 'Please enter a positive float value.';
         echo $totalCrCont_err;
     // } elseif(is_float($input_totalCrCont)) {
     //     $q->total_credit_contract = $input_totalCrCont;
     }else{
         $q->total_credit_contract = $input_totalCrCont;
         // $totalCrCont_err = 'Please enter a float value.';
         // echo $totalCrCont_err;
     }

    // Validate credit type
    $input_creditType = $_POST["credit_type"];
    if (empty($input_creditType)) {
        $creditType_err = "Please choose a credit type.";
        echo $creditType_err;
    } else {
        $q->credit_type = $input_creditType;
    }

    // Validate fee
    $input_fee = $_POST["transaction_fee"];
    if (empty($input_fee)) {
        $fee_err = "Please enter the fee amount.";
        echo $fee_err;
    } elseif (!ctype_digit($input_fee)) {
        $fee_err = 'Please enter a positive integer value.';
        echo $fee_err;
    } else {
        $q->transaction_fee = $input_fee;
    }

    // Validate contract file
    $input_contFile = $_POST['contract_file'];
    if (empty($input_contFile)) {
        $contFile_err = "Please add the contract file.";
        echo $contFile_err;
    } elseif (isset($_FILES['contract_file']) && $_FILES['contract_file']['error'] === UPLOAD_ERR_OK) {
        $q->credit_file = $input_contFile;
    }

    // Validate description
    $input_description = trim($_POST["contract_detail"]);
    if (empty($input_description)) {
        $description_err = "Please enter contract description.";
        echo $description_err;
    } else {
        $q->contract_detail = $input_description;
    }

    // page 2
    // Validate wallet type
    // $input_walletType = $_POST["wallet_type"];
    // if (empty($input_walletType)) {
    //     $walletType_err = "Please choose a credit type.";
    //     echo $walletType_err;
    // } else {
    //     $q->wallet_type = $input_walletType;
    // }

    // give list of user's full name and other detail for donate
    if (isset($_POST['fullName']) && isset($_POST['wallet_type']) 
        && isset($_POST['active']) && isset($_POST['user_credit']) ) {

        $donateResultList = array();
        $userFullName = array();
        $userCredit = array();
        $userWalletType = array();
        $userWalletSt = array();

        // foreach ( $_POST['fullName'] && $_POST['active'] && $_POST['user_credit'] as $res ){
        //     if(!empty($res)){
        //         echo $res;
        //         array_push($donateResultList, $res);
        //     }
        // }
        foreach ( $_POST['fullName'] as $fn) {
            if(!empty($fn))
                // echo $fn;
                array_push($userFullName, $fn);
                // $userFullName['fullName'] = $fn;
        }
        foreach ( $_POST['wallet_type'] as $wt) {
            if(!empty($wt))
                // echo $wt;
                array_push($userWalletType, $wt);
                // $userWalletType['wallet_type'] = $wt;
        }
        foreach ( $_POST['active'] as $ws) {
            if(!empty($ws))
                // echo $ws;
                array_push($userWalletSt, $ws);
                // $userWalletSt['active'] = $ws;
        }
        foreach ( $_POST['user_credit'] as $uc) {
            if(!empty($uc))
                // echo $uc;
                // $userCredit['user_credit'] = $uc;
                array_push($userCredit, $uc);
        }
        $donateResultList = array('fullName'=>$userFullName, 'wallet_type'=>$userWalletType,
        'active'=>$userWalletSt, 'userCredit'=>$userCredit);
        // array_merge($userFullName, $userWalletSt, $userCredit);
        // print_r($donateResultList);
        
        // $input_userList['fullName'] = $_POST['fullName'];
        // $input_userList['user_credit'] =  $_POST['user_credit'];
        // $input_userList['active'] = $_POST['active'];
        // print_r($input_userList);

    } else {
        $userList_err = "Please choose atleast 1 user.";
        echo $userList_err;
    }

    // page 3
    // give conditions
    if (isset($_POST['conditions'])) {
        $conditionsList = array();
        foreach ($_POST['conditions'] as $selected) {
            array_push($conditionsList, $selected);
        }
    } else {
        $conditionsList_err = "Please choose atleast 1 condition.";
        echo $conditionsList_err;
    }

    if(empty($contName_err) && empty($endDate_err) && empty($stDate_err) && empty($fee_err)
    && empty($description_err)&& empty($contFile_err) && empty($totalCr_err) && empty($totalCrCont_err) && empty($creditType_err)
    && empty($userList_err) && empty($conditionsList_err)) {

        $q->makeContractAndImpartNewVersion($donateResultList, $conditionsList);
    
    }  else {
        echo 'something is wrong!';
    }

}

?>