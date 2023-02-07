<?php
include_once '../../Functions/Contract/edit_contract.php';

$edit = new EditContract();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    $edit->id = $_POST["id"];
    $edit->contract_name = trim($_POST["contract_name"]);
    $edit->dead_line_date = $_POST["dead_line_date"];
    $edit->dead_line_time = $_POST["dead_line_time"];
    $edit->total_credit = $_POST["total_credit"];
    $edit->total_credit_contract = $_POST["total_credit_contract"];
    $edit->transaction_fee = $_POST["transaction_fee"];
    $edit->contract_detail = trim($_POST["contract_detail"]);
    
    $edit->edit_contract();

}
