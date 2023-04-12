<?php
// Initialize the session
session_start();

error_reporting(0);
 
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] != true) {
	header("location: index.php");
    exit;
}

if (isset($_GET['i']) && $_GET['i'] != null) {
    $id = $_GET['i'];
} else {
    echo '<script>window.location = "home.php";</script>';
}

// Include config file
require_once "includes/db/config.php";

$sql = "SELECT * FROM {$table_prefix}loan_payment_details WHERE id = '".$id."';";
$res1 = mysqli_query($conn, $sql);
$myrow1 = mysqli_fetch_assoc($res1);

$temp = explode("-", $myrow1['loan_id']);
if ($temp[1] == "RA") {
	$sql = "SELECT * FROM {$table_prefix}loan_delinquentloan_detail WHERE loan_id = '".$myrow1['loan_id']."';";
	$res = mysqli_query($conn, $sql);
	$myrow = mysqli_fetch_assoc($res);
	$amount = $myrow['total_loan_balance'] + $myrow1['pokok'];

	$sql = "UPDATE loan_delinquentloan_detail SET total_loan_balance = '".$amount."' WHERE loan_id = '".$myrow1['loan_id']."';";
	$res = mysqli_query($conn, $sql);
} else {
	$sql = "SELECT * FROM {$table_prefix}loan_details WHERE loan_id = '".$myrow1['loan_id']."';";
	$res = mysqli_query($conn, $sql);
	$myrow = mysqli_fetch_assoc($res);
	$amount = $myrow['remaining_amount'] + $myrow1['pokok'];

	$sql = "UPDATE loan_details SET remaining_amount = '".$amount."' WHERE loan_id = '".$myrow1['loan_id']."';";
	$res = mysqli_query($conn, $sql);
}

$sql = "DELETE from {$table_prefix}loan_payment_details WHERE id = '".$id."';";
$res = mysqli_query($conn, $sql);
echo '<script type="text/javascript">'
			   , 'history.go(-1);'
			   , '</script>';
?>