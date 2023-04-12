<?php
// Initialize the session
session_start();

// Include config file
require_once "includes/db/config.php";

if (isset($_GET['c']) && $_GET['c'] != "") {
	$id = $_GET['c'];

	$sql = "SELECT * FROM {$table_prefix}customer_details WHERE id = '".$id."';";
	$res = mysqli_query($conn, $sql);
	$row = mysqli_fetch_assoc($res);
	$customer_id = $row['customer_code'];

	$score = 0;
	$sql = "SELECT * FROM {$table_prefix}loan_details WHERE code = '".$customer_id."';";
	$res = mysqli_query($conn, $sql);
	while ($myrow = mysqli_fetch_array($res)) {
		$score = $myrow['remaining_amount'] - $myrow['gst'];

		$sql1 = "SELECT * FROM {$table_prefix}users WHERE id = '".$myrow['people1']."';";
		$res1 = mysqli_query($conn, $sql1);
		$myrow1 = mysqli_fetch_assoc($res1);
		$agent_score = $myrow1['score'];
		$new_score = $agent_score + $score;

		$sql2 = "UPDATE users SET score = '".$new_score."' WHERE id = '".$myrow['people1']."';";
		mysqli_query($conn, $sql2);
	}

	$sql = "DELETE FROM {$table_prefix}customer_details WHERE id = '".$id."';";
	// echo $sql."<br>";
	$res = mysqli_query($conn, $sql);

	$sql = "DELETE FROM {$table_prefix}bank_details WHERE customer_id = '".$customer_id."';";
	// echo $sql."<br>";
	$res = mysqli_query($conn, $sql);

	$sql = "DELETE FROM {$table_prefix}employment_details WHERE customer_id = '".$customer_id."';";
	// echo $sql."<br>";
	$res = mysqli_query($conn, $sql);

	$sql = "DELETE FROM {$table_prefix}loan_details WHERE code = '".$customer_id."';";
	// echo $sql."<br>";
	$res = mysqli_query($conn, $sql);

	$sql = "DELETE FROM {$table_prefix}loan_payment_details WHERE loan_id LIKE '%".$customer_id."-%';";
	// echo $sql."<br>";
	$res = mysqli_query($conn, $sql);

	echo "<script>alert('客户刪除成功。')</script>";
	echo "<script>window.location = 'customer_list.php';</script>" ;
} else {
	echo "<script>window.location = 'customer_list.php';</script>" ;
}

?>