<?php
// Initialize the session
session_start();

// error_reporting(0);
 
// Include config file
require_once "includes/db/config.php";

if (isset($_GET['c']) && $_GET['c'] != null) {
    $customerID = $_GET['c'];
} else {
    echo '<script>window.location = "home.php";</script>';
}
$total = 0;
$sql = 'SELECT * FROM `'.$table_prefix.'loan_details` WHERE code = "'.$customerID.'" AND (status = "New" OR status = "InProgress");';
$res = mysqli_query($conn, $sql);
while ($myrow = mysqli_fetch_array($res)) {
	$total += $myrow['remaining_amount'];
	$sql2 = 'SELECT * FROM `'.$table_prefix.'loan_tepi_details` WHERE loan_id = "'.$myrow['loan_id'].'" AND paid = "FALSE"';
	$res2 = mysqli_query($conn, $sql2);
	while ($myrow2 = mysqli_fetch_array($res2)){
		$total += $myrow2['tepi_amount'];
	}
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$sql = 'UPDATE `'.$table_prefix.'customer_details` SET status = "Defaulted" WHERE customer_code = "'.$customerID.'";';
	$res = mysqli_query($conn, $sql);
	$sql = 'SELECT * FROM `'.$table_prefix.'loan_details` WHERE code = "'.$customerID.'" AND (status = "New" OR status = "InProgress");';
	$res = mysqli_query($conn, $sql);
	while ($myrow = mysqli_fetch_array($res)) {
		$sql1 = 'UPDATE `'.$table_prefix.'loan_details` SET status = "Default", default_capital = "'.$myrow['remaining_amount'].'" WHERE loan_id = "'.$myrow['loan_id'].'";';
		$res1 = mysqli_query($conn, $sql1);
		$sql1 = 'SELECT * FROM `'.$table_prefix.'loan_payment_details` WHERE loan_id = "'.$myrow['loan_id'].'"; ORDR BY id DESC LIMIT 1;';
		$res1 = mysqli_query($conn, $sql1);
		$myrow1 = mysqli_fetch_assoc($res1);
		$temp = explode("-", $myrow1['payment_id']);
		if ($temp[0] == "") {
			$newID = $myrow['loan_id']."-1";
		} else {
			$temp[2] += 1;
			$newID = $temp[0]."-".$temp[1]."-".$temp[2];
		}

		$sql1 = 'INSERT INTO `'.$table_prefix.'loan_payment_details` (loan_id, payment_id, date, type, pokok, bunga, remaining_amount) VALUES ("'.$myrow['loan_id'].'", "'.$newID.'", "'.$_POST['date'].'", "Capital Loss", "'.$myrow['remaining_amount'].'", "0", "0");';
		$res1 = mysqli_query($conn, $sql1);
	}
	echo "<script>window.location = 'customer.php?c=".$customerID."';</script>" ;
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
	<link rel="stylesheet" type="text/css" href="includes/css/style6.css">
	<!--load all Font Awesome styles -->
	<link href="includes/fontawesome/css/all.css" rel="stylesheet">
	<script src="includes/js/script.js"></script>
	<title>违约客户<?php echo $customerID;?></title>
</head>
<body>
	<form method="post">
	<div style="background-color: white; padding: 5px;">
		<div class="grid-container" style="text-align: center; grid-template-columns: 50% 50%;">
			<div class="grid-item" style="text-align: center;">
				<div style="margin-bottom: 15px;">
					<label>违约日期：</label><br>
				</div>
				<div>
					<label>总损失 (RM)：</label>
				</div>
			</div>
			<div class="grid-item">
				<input type="date" name="date" value="<?php echo date("Y-m-d");?>" style="margin-bottom: 15px;"><br>
				<input type="text" disabled value="<?php echo $total;?>" placeholder="<?php echo $total;?>">
			</div>
		</div>
		<div style="margin: 10px;">
			<label style="color: red;">违约贷款单</label>
			<table style="margin-top: 5px;">
				<tr>
					<th>贷款ID</th>
					<th>最后付款日期</th>
					<th>预期 (days)</th>
					<th>贷损</th>
					<th>TEPI损</th>
					<th>总损</th>
				</tr>
<?php
$sql = 'SELECT * FROM `'.$table_prefix.'loan_details` WHERE code = "'.$customerID.'" AND (status = "New" OR status = "InProgress");';
$res = mysqli_query($conn, $sql);
while ($myrow = mysqli_fetch_array($res)) {?>
				<tr>
					<td style="text-align: right;"><?php echo $myrow['loan_id'];?></td>
<?php
$sql1 = 'SELECT * FROM `'.$table_prefix.'loan_payment_details` WHERE loan_id = "'.$myrow['loan_id'].'" ORDER BY id DESC LIMIT 1;';
$res1 = mysqli_query($conn, $sql);
$myrow1 = mysqli_fetch_assoc($res1);
$now = time(); // or your date as well
if (isset($myrow1['date'])) {?>
					<td><?php echo $myrow1['date'];?></td>
<?php
$your_date = strtotime($myrow1['date']);
} else {?>
	<td><?php echo $myrow['start_date'];?></td>
<?php
$your_date = strtotime($myrow['start_date']);
}
$datediff = $now - $your_date;
?>
					<td style="text-align: center;"><?php echo round($datediff / (60 * 60 * 24));?></td>
					<td style="text-align: right;">RM<?php echo $myrow['remaining_amount'];?></td>
<?php
$sql2 = 'SELECT * FROM `'.$table_prefix.'loan_tepi_details` WHERE loan_id = "'.$myrow['loan_id'].'" AND paid = "FALSE"';
$res2 = mysqli_query($conn, $sql2);
$tepi = 0;
$total = 0;
while ($myrow2 = mysqli_fetch_array($res2)) {
	$tepi += $myrow2['tepi_amount'];
}
$total += ($myrow['remaining_amount'] + $tepi);
?>
					<td style="text-align: right;">RM<?php echo $tepi;?></td>
					<td style="text-align: right;">RM<?php echo $total;?></td>
				</tr>
<?php }?>
			</table>
		</div>
		<div style="text-align: right;">
			<button class="button" style="margin-right: 10px; background-color: blue; color: white;">确定</button>
		</div>
	</div>
	</form>
</body>
</html>