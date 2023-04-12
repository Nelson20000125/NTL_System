<?php
// Initialize the session
session_start();
 
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] != true){
	header("location: index.php");
    exit();
}

if (isset($_GET['l']) && $_GET['l'] != null) {
    $loan_payment_id = $_GET['l'];
} else {
    echo '<script>window.location = "customer_list.php";</script>';
}

// Include config file
require_once "includes/db/config.php";

$sql = "SELECT * FROM {$table_prefix}loan_payment_details WHERE id = '".$loan_payment_id."';";
$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);
$loan_id = $row['loan_id'];
$payment = $row['pokok'];
$remaining_amount = $row['remaining_amount'];
$qwe = explode("-", $loan_id);
$c = $qwe[0];

$sql = "SELECT * FROM {$table_prefix}loan_payment_details WHERE loan_id = '".$loan_id."' AND id > '".$loan_payment_id."';";
$res = mysqli_query($conn, $sql);
$i = 0;
while ($row = mysqli_fetch_array($res)) {
    $i++;
}

$sql5 = "SELECT * FROM {$table_prefix}loan_details WHERE loan_id = '".$loan_id."';";
$res5 = mysqli_query($conn, $sql5);
$myrow5 = mysqli_fetch_assoc($res5);
$pay_day = $myrow5['payday'];
$date = $myrow5['date'];

$new_date = date("Y-m-d", strtotime($date) - ($pay_day * 24 * 60 * 60));


if ($i > 0) {
    for ($j = 0; $j < $i; $j++) { 
        $sql = "SELECT * FROM {$table_prefix}loan_payment_details WHERE loan_id = '".$loan_id."' AND id > '".$loan_payment_id."' LIMIT 1;";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($res);
        $remaining_amount = $row['remaining_amount'];
        $loan_payment_id = $row['id'];
        $new_amount = $remaining_amount + $payment;

        $sql1 = "UPDATE loan_payment_details SET remaining_amount = '".$new_amount."' WHERE id = '".$loan_payment_id."';";
        mysqli_query($conn, $sql1);
        // echo $sql1."<br>";
    }
    $sql = "DELETE FROM {$table_prefix}loan_payment_details WHERE id = '".$_GET['l']."';";
    if (mysqli_query($conn, $sql)) {
        $sql = "UPDATE loan_details SET remaining_amount = '".$new_amount."', status = '0', date = '".$new_date."' WHERE loan_id = '".$loan_id."';";
        if ($res = mysqli_query($conn, $sql)) {
            echo "<script>window.location = 'customer_loan.php?c=".$c."&l=".$loan_id."';</script>";
        }
    }
} else {
    $sql = "DELETE FROM {$table_prefix}loan_payment_details WHERE id = '".$loan_payment_id."';";
    if (mysqli_query($conn, $sql)) {
        $new_amount = $myrow5['remaining_amount'] + $payment;
        $sql = "UPDATE loan_details SET remaining_amount = '".$new_amount."', status = '0', date = '".$new_date."' WHERE loan_id = '".$loan_id."';";
        if ($res = mysqli_query($conn, $sql)) {
            echo "<script>window.location = 'customer_loan.php?c=".$c."&l=".$loan_id."';</script>";
        }
    }
}
?>