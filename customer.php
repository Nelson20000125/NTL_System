<?php 
// Initialize the session
session_start();

error_reporting(0);
 
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] != true) {
	header("location: index.php");
    exit;
}

if (isset($_GET['c']) && $_GET['c'] != null) {
    $customerID = $_GET['c'];
} else {
    echo '<script>window.location = "home.php";</script>';
}

// Include config file
require_once "includes/db/config.php";

$available = false;

$id = $_SESSION["id"];
$superadmin = $_SESSION['superadmin'];
$fa = 0;
$sql = 'SELECT a.loan_id FROM `'.$table_prefix.'loan_tepi_details` AS a LEFT JOIN `loan_details` AS b ON a.loan_id = b.loan_id WHERE a.paid = "FALSE" AND b.code = "'.$customerID.'";';
$res = mysqli_query($conn, $sql);
while ($myrow96 = mysqli_fetch_array($res)) {
	$fa++;
	$loan_id_2 = $myrow96['loan_id'];
}

if (isset($_GET['l']) && $_GET['l'] != null) {
    $loanID = $_GET['l'];
		$available = true;
} else {
	if ($fa != 0) {
		$loanID = $loan_id_2;
		$available = true;
	} else {
		$loanID = $customerID."-001";
	}
}

$xx = 0;

$sql = 'SELECT pay_day FROM '.$table_prefix.'employment_details WHERE customer_id = "'.$customerID.'";';
$res100 = mysqli_query($conn, $sql);
$myrow100 = mysqli_fetch_assoc($res100);
$pay_day = $myrow100['pay_day'];

$sql = 'SELECT * FROM `'.$table_prefix.'loan_details` where loan_id = "'.$loanID.'";';
$res99 = mysqli_query($conn, $sql);
$myrow99 = mysqli_fetch_assoc($res99);
$pokok99 = $myrow99['pokok'];
$bunga99 = $myrow99['bunga'];
$status99 = $myrow99['status'];

$sql = 'SELECT * FROM `'.$table_prefix.'loan_tepi_details` WHERE loan_id = "'.$loanID.'" AND paid = "FALSE";';
$res98 = mysqli_query($conn, $sql);
$te = 0;
while ($myrow98 = mysqli_fetch_array($res98)) {
	$te++;
}

$sql = "SELECT * FROM {$table_prefix}customer_details WHERE customer_code = ?;";
if ($stmt = mysqli_prepare($conn, $sql)) {
	mysqli_stmt_bind_param($stmt, "s", $param_customerID);
	$param_customerID = $customerID;
	if (mysqli_stmt_execute($stmt)) {
		mysqli_stmt_store_result($stmt);
		if (mysqli_stmt_num_rows($stmt) == 0){
			echo '<script>alert("找不到'.$customerID.'的记录。")</script>';
			// echo '<script>window.location = "home.php";</script>';
		}
	}
}

$sql = 'SELECT name, ic, passport, phone1, remark, status, CTOS FROM '.$table_prefix.'customer_details WHERE customer_code = "'.$customerID.'";';
$res = mysqli_query($conn, $sql);
$myrow = mysqli_fetch_assoc($res);

// counter for 隐藏结清贷款 (for hide and bighide)
$a = 0;

// counter for hide settled or payoff
$x = 0;

// count how many loan
$l = 0;

// count how many tepi
$o = 0;

// user defaulted
$default = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$blank = 0;
	if ($_POST['action'] == "delete_loan_detail") {
		$id = $_POST['detail_id'];
		$amount = $_POST['amount'];

		$sql = "DELETE FROM `{$table_prefix}loan_payment_details` WHERE id = '".$id."';";
		mysqli_query($conn, $sql);
		// echo $sql."<br>";

		$sql = "SELECT * FROM `{$table_prefix}loan_details` WHERE loan_id = '".$loanID."';";
		$res = mysqli_query($conn, $sql);
		$myrow = mysqli_fetch_assoc($res);
		// echo $sql."<br>";

		$amount += $myrow['remaining_amount'];

		$sql = "UPDATE `{$table_prefix}loan_details` SET status = '0', remaining_amount = '".$amount."' WHERE loan_id = '".$loanID."';";
		// echo $sql."<br>";
		mysqli_query($conn, $sql);

		echo "<script>window.location = 'customer.php?c=".$customerID."&l=".$loanID."';</script>";
	} else if ($_POST['action'] == "addloan") {
		if ($_POST['loan_amount'] <= 0) {
			// code...
			$blank++;
		}
		if ($_POST['deposit'] < 0) {
			// code...
			$blank++;
		}
		if ($_POST['intrest'] <= 0) {
			// code...
			$blank++;
		}
		if ($_POST['gst'] <= 0) {
			// code...
			$blank++;
		}
		if ($_POST['tenure'] < 0) {
			// code...
			$blank++;
		}
		if ($_POST['day'] <= 0) {
			// code...
			$blank++;
		}
		if ($blank != 0) {
			echo "<script>alert('资料输入错误，请稍后再试。')</script>";
			echo "<script>window.location = 'customer.php?c=".$customerID."&l=".$loanID."';</script>";
		} else {
			$code = $customerID;
			$loan_id = $_POST['newloanID'];
			$start_date = $_POST['start_date'];
			$loan_amount = $_POST['loan_amount'];
			$intrest = $_POST['intrest'];
			$deposit = $_POST['deposit'];
			$gst = $_POST['gst'];
			$tenure = $_POST['tenure'];
			$payday = $_POST['day'];
			$type = $_POST['type'];
			$phone = $_POST['phone'];
			$people1 = $_POST['people1'];
			$people2 = $_POST['people2'];
			$status = $_POST['status'];
			$remaining_amount = $loan_amount;
			$date = date('Y-m-d', strtotime($start_date. ' + '.$payday.' days'));
			// $temp = explode("-", $start_date);
			// $temp[2] = str_pad(($temp[2] + $payday), 2, "0", STR_PAD_LEFT);
			// switch ($temp[1]) {
			// 	case '01':
			// 	case '03': 
			// 	case '05': 
			// 	case '07': 
			// 	case '08': 
			// 	case '10': 
			// 	case '12': 
			// 		if ($temp[2] > 31) {
			// 			$temp[2] -= 31;
			// 			$temp[2] = str_pad($temp[2], 2, "0", STR_PAD_LEFT);
			// 			$temp[1] ++;
			// 			if ($temp[1] > 12) {
			// 				$temp[1] -= 12;
			// 				$temp[1] = str_pad($temp[1], 2, "0", STR_PAD_LEFT);
			// 				$temp[0] ++;
			// 			}
			// 		}
			// 		break; 
			// 	case '04': 
			// 	case '06': 
			// 	case '09': 
			// 	case '11': 
			// 		if ($temp[2] > 30) {
			// 			$temp[2] -= 30;
			// 			$temp[2] = str_pad($temp[2], 2, "0", STR_PAD_LEFT);
			// 			$temp[1] ++;
			// 			if ($temp[1] > 12) {
			// 				$temp[1] -= 12;
			// 				$temp[1] = str_pad($temp[1], 2, "0", STR_PAD_LEFT);
			// 				$temp[0] ++;
			// 			}
			// 		}
			// 		break; 
			// 	case '02': 
			// 		$temp1 = $temp[0] % 4;
			// 		switch ($temp1) {
			// 			case '0':
			// 				if ($temp[2] > 29) {
			// 					$temp[2] -= 29;
			// 					$temp[2] = str_pad($temp[2], 2, "0", STR_PAD_LEFT);
			// 					$temp[1] ++;
			// 					if ($temp[1] > 12) {
			// 						$temp[1] -= 12;
			// 						$temp[1] = str_pad($temp[1], 2, "0", STR_PAD_LEFT);
			// 						$temp[0] ++;
			// 					}
			// 				}
			// 				break;
						
			// 			default:
			// 				if ($temp[2] > 28) {
			// 					$temp[2] -= 28;
			// 					$temp[2] = str_pad($temp[2], 2, "0", STR_PAD_LEFT);
			// 					$temp[1] ++;
			// 					if ($temp[1] > 12) {
			// 						$temp[1] -= 12;
			// 						$temp[1] = str_pad($temp[1], 2, "0", STR_PAD_LEFT);
			// 						$temp[0] ++;
			// 					}
			// 				}
			// 				break;
			// 		}
			// 		break;
			// }
			// $date = $temp[0]."-".$temp[1]."-".$temp[2];
			$sql = "INSERT INTO `{$table_prefix}loan_details`(code, loan_id, start_date, loan_amount, intrest, deposit, gst, tenure, payday, type, phone, people1, people2, status, remaining_amount, date, created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			if ($stmt = mysqli_prepare($conn, $sql)) {
				mysqli_stmt_bind_param($stmt, "sssddddiisssssdsi", $param_code, $param_loan_id, $param_start_date, $param_loan_amount, $param_intrest, $param_deposit, $param_gst, $param_tenure, $param_payday, $param_type, $param_phone, $param_people1, $param_people2, $param_status, $param_remaining_amount, $param_date, $param_created);
				$param_code = $code;
				$param_loan_id = $loan_id;
				$param_start_date = $start_date;
				$param_loan_amount = $loan_amount;
				$param_intrest = $intrest;
				$param_deposit = $deposit;
				$param_gst = $gst;
				$param_tenure = $tenure;
				$param_payday = $payday;
				$param_type = $type;
				$param_phone = $phone;
				$param_people1 = $people1;
				$param_people2 = $people2;
				$param_status = $status;
				$param_remaining_amount = $remaining_amount;
				$param_date = $date;
				$param_created = $id;
				if(mysqli_stmt_execute($stmt)) {
					echo "<script>alert('你已成功创建".$loan_id."新贷款。')</script>";
					echo "<script>window.location = 'customer.php?c=".$customerID."&l=".$loan_id."';</script>";
				}
			}
		}

	} else if ($_POST['action'] == "settle") {
		$sql = 'SELECT payment_id FROM `'.$table_prefix.'loan_payment_details` WHERE loan_id = "'.$loanID.'";';
		if($stmt = mysqli_prepare($conn, $sql)) {
    		if(mysqli_stmt_execute($stmt)) {
    			mysqli_stmt_store_result($stmt);
    			if(mysqli_stmt_num_rows($stmt) == 1) {
    				mysqli_stmt_bind_result($stmt, $payment_id);
    				mysqli_stmt_fetch($stmt);
    				$temp3 = explode("-", $payment_id);
    				$temp3[2] = $temp3[2] + 1;
    				$current_payment_id = $temp3[0]."-".$temp3[1]."-".$temp3[2];
    			} else {
    				$current_payment_id = $loanID."-1";
    			}
    		}
		}
		$sql = 'INSERT INTO `'.$table_prefix.'loan_payment_details` (loan_id, payment_id, date, type, pokok, bunga, remaining_amount) VALUES ("'.$loanID.'", "'.$current_payment_id.'", "'.$_POST['POdate'].'", "Close", "'.$_POST['POamount'].'", "'.$_POST['PObunga'].'", 0);';
		mysqli_query($conn, $sql);
		$sql = 'UPDATE `'.$table_prefix.'loan_details` SET status = "Settled", remaining_amount = "0" WHERE loan_id = "'.$loanID.'";';
		mysqli_query($conn, $sql);
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
			$url = "https://";
		else
			$url = "http://";
		// Append the host(domain name, ip) to the URL.
		$url.= $_SERVER['HTTP_HOST'];
		// Append the requested resource location to the URL
		$url.= $_SERVER['REQUEST_URI'];
		header("location: ".$url);
	} else if ($_POST['action'] == "payloan") {
		$current_payment_id = $loanID."-1";
		$sql = 'SELECT payment_id FROM `'.$table_prefix.'loan_payment_details` WHERE loan_id = ? ORDER BY payment_id DESC LIMIT 1;';
		if($stmt = mysqli_prepare($conn, $sql)) {
			mysqli_stmt_bind_param($stmt, "s", $param_loan_id);
    		$param_loan_id = $loanID;
    		if(mysqli_stmt_execute($stmt)) {
    			mysqli_stmt_store_result($stmt);
    			if(mysqli_stmt_num_rows($stmt) == 1) {
    				mysqli_stmt_bind_result($stmt, $payment_id);
    				mysqli_stmt_fetch($stmt);
    				$current_payment_id = $payment_id;
    				$temp2 = explode("-", $current_payment_id);
    				$temp2[2] = $temp2[2] + 1;
    				$current_payment_id = $temp2[0]."-".$temp2[1]."-".$temp2[2];
    			}
    		}
		}

		$loan_id = $loanID;
		$date = $_POST['datevalue'];
		// if ($_POST['category'] == "1") {
		// 	$type = "full_payment";
		// 	$pokok = $_POST['pokokvalue'];
		// 	$bunga = $_POST['bungavalue'];
		// } elseif ($_POST['category'] == "2") {
		// 	$type = "Pokok";
		// 	$pokok = $_POST['pokokvalue'];
		// 	$bunga = 0;
		// } elseif ($_POST['category'] == "3") {
		// 	$type = "interest_only";
		// 	$pokok = 0;
		// 	$bunga = $_POST['bungavalue'];
		// } elseif ($_POST['category'] == "4") {
		// 	$type = "custom";
		// 	$pokok = $_POST['valuepokok'];
		// 	$bunga = $_POST['valuebunga'];
		// }

		// if (isset($_POST['late'])) {
		// 	$late_date = $_POST['late_date'];
		// } else {
		// 	$late_date = $date;
		// }

		$pokok = $_POST['pokok'];
		$bunga = $_POST['bunga'];

		$date = date("Y-m-d");

		$remaining_amount = $_POST['currentremaining'] - $pokok;
		$sql = 'INSERT INTO `'.$table_prefix.'loan_payment_details` (loan_id, payment_id, date, pokok, bunga, remaining_amount) VALUES ("'.$loan_id.'", "'.$current_payment_id.'", "'.$date.'", "'.$pokok.'", "'.$bunga.'", "'.$remaining_amount.'");';
		$res = mysqli_query($conn, $sql);
		// if ($remaining_amount == 0) {
			// $sql = 'UPDATE `'.$table_prefix.'loan_details` SET remaining_amount = "'.$remaining_amount.'", status = "1" WHERE loan_id = "'.$loanID.'";';
		// } else {
			$sql = 'UPDATE `'.$table_prefix.'loan_details` SET remaining_amount = "'.$remaining_amount.'" WHERE loan_id = "'.$loanID.'";';
		// }
		$res = mysqli_query($conn, $sql);
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
			$url = "https://";
		else
			$url = "http://";
		// Append the host(domain name, ip) to the URL.
		$url.= $_SERVER['HTTP_HOST'];
		// Append the requested resource location to the URL
		$url.= $_SERVER['REQUEST_URI'];
		header("location: ".$url);
	} else if ($_POST['action'] == "tepi") {
		if (isset($_POST['add'])) {
			$sql = 'SELECT tepi_id FROM `'.$table_prefix.'loan_tepi_details` WHERE loan_id = "'.$loanID.'" ORDER BY tepi_id DESC LIMIT 1;';
			if($stmt = mysqli_prepare($conn, $sql)) {
	    		if(mysqli_stmt_execute($stmt)) {
	    			mysqli_stmt_store_result($stmt);
	    			if(mysqli_stmt_num_rows($stmt) == 1) {
	    				mysqli_stmt_bind_result($stmt, $tepi_id);
	    				mysqli_stmt_fetch($stmt);
	    				$tepi_id;
	    				$temp3 = explode("-", $tepi_id);
	    				$temp3[2] = $temp3[2] + 1;
	    				$current_tepi_id = $temp3[0]."-".$temp3[1]."-".$temp3[2];
	    			} else {
	    				$current_tepi_id = $loanID."-1";
	    			}
	    		}
			}
			$loan_id = $loanID;
			$tepi_id = $current_tepi_id;
			$borrow_date = $_POST['borrow_date'];
			$tepi_amount = $_POST['tepi_amount'];
			$received_bunga = $_POST['received_bunga'];
			$sql = 'INSERT INTO `'.$table_prefix.'loan_tepi_details` (loan_id, tepi_id, borrow_date, tepi_amount, received_bunga, paid, paid_by_refinance, is_default, tepi_status, loan_type) VALUES ("'.$loan_id.'", "'.$tepi_id.'", "'.$borrow_date.'", "'.$tepi_amount.'", "'.$received_bunga.'", "FALSE", "FALSE", "FALSE", "0", "0");';
			$res = mysqli_query($conn, $sql);
			if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
				$url = "https://";
			else
				$url = "http://";
			// Append the host(domain name, ip) to the URL.
			$url.= $_SERVER['HTTP_HOST'];
			// Append the requested resource location to the URL
			$url.= $_SERVER['REQUEST_URI'];
			header("location: ".$url);
		} else if (isset($_POST['pay'])) {
			if (isset($_POST['checktepi'])) {
				$checktepi = $_POST['checktepi'];
				foreach ($checktepi as $tepi => $tepivalue) {
					$sql = 'UPDATE `'.$table_prefix.'loan_tepi_details` SET paid = "TRUE", paid_date = "'.date("Y-m-d").'" WHERE id = "'.$tepivalue.'";';
					$res = mysqli_query($conn, $sql);
				}
				if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
					$url = "https://";
				else
					$url = "http://";
				// Append the host(domain name, ip) to the URL.
				$url.= $_SERVER['HTTP_HOST'];
				// Append the requested resource location to the URL
				$url.= $_SERVER['REQUEST_URI'];
				header("location: ".$url);
			}
		}
	} elseif ($_POST['action'] == "pay") {
		$remaining = $_POST['remainingamount'];
		$date = $_POST['paydate'];
		$pokok = $_POST['payamount'];
		if ($pokok <= 0) {
			echo "<script>alert('请填写正确的数值。')</script>";
			echo "<script>window.location = 'customer.php?c=".$customerID."&l=".$loanID."';</script>" ;
			exit();
		}

		$current_payment_id = $loanID."-1";
		$sql = 'SELECT payment_id FROM `'.$table_prefix.'loan_payment_details` WHERE loan_id = ? ORDER BY payment_id DESC LIMIT 1;';
		if($stmt = mysqli_prepare($conn, $sql)) {
			mysqli_stmt_bind_param($stmt, "s", $param_loan_id);
    		$param_loan_id = $loanID;
    		if(mysqli_stmt_execute($stmt)) {
    			mysqli_stmt_store_result($stmt);
    			if(mysqli_stmt_num_rows($stmt) == 1) {
    				mysqli_stmt_bind_result($stmt, $payment_id);
    				mysqli_stmt_fetch($stmt);
    				$current_payment_id = $payment_id;
    				$temp2 = explode("-", $current_payment_id);
    				$temp2[1] = $temp2[1] + 1;
    				$current_payment_id = $temp2[0]."-".$temp2[1];
    			}
    		}
		}

		$sql = 'INSERT INTO `'.$table_prefix.'loan_payment_details` (loan_id, payment_id, date, pokok, bunga, remaining_amount) VALUES ("'.$loanID.'", "'.$current_payment_id.'", "'.$date.'", "'.$pokok.'", "0", "'.$remaining.'");';
		$res = mysqli_query($conn, $sql);
		$sql = "SELECT id, total_loan_balance FROM {$table_prefix}loan_delinquentloan_detail WHERE customer_code = '".$customerID."' ORDER BY id DESC LIMIT 1;";
		$res = mysqli_query($conn, $sql);
		$myrow = mysqli_fetch_assoc($res);

		$temp55 = $myrow['total_loan_balance'] - $pokok;

		$sql = "UPDATE loan_delinquentloan_detail SET total_loan_balance = '".$temp55."' WHERE id = '".$myrow['id']."';";
		mysqli_query($conn, $sql);

		echo "<script>window.location = 'customer.php?c=".$customerID."&l=".$loanID."';</script>" ;
	}
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
	<link rel="stylesheet" type="text/css" href="includes/css/customer.css">
	<title><?php echo $customerID;?></title>
</head>
<body>
	<div class="main">
		<section>
			<div style="text-align: left; padding-bottom: 12px;">
				<a class="button" style="margin: 10px 0; cursor: pointer; padding: 4.5px 31px 4.5px 31px;" onclick="goHome()">返回首頁</a>
			</div>
			<div style="text-align: left;">
				<div class="grid-container" style="text-align: left;">
					<div class="grid-item">
						<div style="margin-bottom: 10px;">
							<label>名字：</label>
						</div>
						<div>
							<label>IC/Passport号码：</label>
						</div>
					</div>
					<div class="grid-item">
						<div style="margin-bottom: 13px;">
							<label style="color: blue;"><?php echo $myrow['name'];?></label>
<?php 
if ($myrow['CTOS'] == 1) {?>
							<label style="color: red;">(还完不做)</label>
<?php } elseif ($myrow['CTOS'] == 2) {?>
							<label style="color: red;">(CTOS Blacklist)</label>
<?php } elseif ($myrow['CTOS'] == 3) {?><label style="color: red;">(还完不做，CTOS Blacklist)</label>
<?php }?>
						</div>
						<div>
<?php 
if (substr($myrow['ic'], 1) == "") {?>
							<label style="color: blue;"><?php echo $myrow['passport'];?></label>
<?php }
if (substr($myrow['passport'], 1) == "") {?>
							<label style="color: blue;"><?php echo substr_replace(substr_replace($myrow['ic'], "-" ,8 ,0), "-", 6, 0);?></label>
<?php }?>
						</div>
					</div>
					<div class="grid-item">
						<div style="margin-bottom: 10px;">
							<label class="beizhu">备注：</label>
							<a href="customer_detail.php?c=<?php echo $customerID;?>" style="color: blue;">客户资料</a>
						</div>
						<textarea style="height: 50px; width: 250px;" disabled><?php echo $myrow['remark'];?></textarea>
					</div>
<?php 
if ($myrow['status'] == "Active") {?>
					<div class="grid-item">
<?php
if ($superadmin == 1) {?>
						<a style="margin-right: 5px;" class="tooltip"><i class="fa-solid fa-circle-plus fa-xl" style="color: green; cursor: pointer;" onclick="document.getElementById('id01').style.display='block'"></i><span class="tooltiptext">加新贷款</span></a>
						<a onclick="confirmDelete()" style="margin-right: 5px; cursor: pointer;" class="tooltip"><i class="fa-solid fa-trash fa-xl" style="color: red;"></i><span class="tooltiptext">删除贷款</span></a>
						<a onclick="confirmBlack()" style="margin-right: 5px; cursor: pointer;" class="tooltip"><i class="fa-solid fa-arrow-trend-down fa-xl" style="color: red;"></i><span class="tooltiptext">违约</span></a>
						<a class="tooltip"><i class="fa-regular fa-file-lines fa-xl" style="color: grey;"></i><span class="tooltiptext">拖欠贷款回收新协议</span></a>
<?php } else {?>
						<a style="margin-right: 5px;" class="tooltip"><i class="fa-solid fa-circle-plus fa-xl" style="color: grey; cursor: auto;"></i><span class="tooltiptext">加新贷款</span></a>
						<a style="margin-right: 5px; cursor: auto;" class="tooltip"><i class="fa-solid fa-trash fa-xl" style="color: grey;"></i><span class="tooltiptext">删除贷款</span></a>
						<a style="margin-right: 5px; cursor: auto;" class="tooltip"><i class="fa-solid fa-arrow-trend-down fa-xl" style="color: grey;"></i><span class="tooltiptext">违约</span></a>
						<a class="tooltip"><i class="fa-regular fa-file-lines fa-xl" style="color: grey;"></i><span class="tooltiptext">拖欠贷款回收新协议</span></a>
<?php }?>
					</div>
<?php } else {
	$sql81 = "SELECT * FROM {$table_prefix}loan_delinquentloan_detail WHERE customer_code = '".$customerID."' ORDER BY id DESC LIMIT 1;";
	$res81 = mysqli_query($conn, $sql81);
	$myrow81 = mysqli_fetch_assoc($res81);
	$default = true;
?>
					<div class="grid-item">
<?php 
	if ($myrow81['total_loan_balance'] == 0) {?>
						<a style="margin-right: 5px;" class="tooltip" href="restore_customer.php?c=<?php echo $customerID;?>" onclick="return confirm('您确定复活这位客户吗？')"><i class="fa-solid fa-registered fa-xl"style="color: green; cursor: pointer;"></i><span class="tooltiptext">复活客户</span></a>
<?php 	}?>
						<a style="margin-right: 5px;" class="tooltip"><i class="fa-solid fa-circle-plus fa-xl" style="color: lightgrey; cursor: auto;"></i><span class="tooltiptext">加新贷款</span></a>
						<a style="margin-right: 5px; cursor: auto;" class="tooltip"><i class="fa-solid fa-trash fa-xl" style="color: lightgrey;"></i><span class="tooltiptext">删除贷款</span></a>
<!-- 						<a style="margin-right: 5px; cursor: auto;" class="tooltip"><i class="fa-solid fa-comments-dollar fa-xl" style="color: lightgrey;"></i><span class="tooltiptext">融资</span></a> -->
						<a style="margin-right: 5px; cursor: auto;" class="tooltip"><i class="fa-solid fa-arrow-trend-down fa-xl" style="color: lightgrey;"></i><span class="tooltiptext">违约</span></a>
						<a class="tooltip" href="restore.php?c=<?php echo $customerID;?>"><i class="fa-regular fa-file-lines fa-xl" style="color: grey;"></i><span class="tooltiptext">拖欠贷款回收新协议</span></a>
					</div>
<?php }?>
				</div>
			</div>
			<div class="daikuanziliao">
				<div class="tab" style="margin: 0;">
					<button class="tablinks active" style="cursor: auto;">贷款资料</button>
				</div>
			</div>
			<div id="customer" class="tabcontent">

<!-- area 1 -->
				<div style="overflow-x: auto;">
					<table style="white-space: nowrap;">
						<tr>
							<th>盈利状况</th>
							<th>号码</th>
							<th>代理人</th>
							<th>顾客</th>
							<th>贷款日期</th>
							<th>贷款数额</th>
							<th>印花税</th>
							<th>押金</th>
							<th>GST</th>
							<th>共收集</th>
							<th>未结余额</th>
							<th>滚动次数</th>
							<th>付款次数</th>
							<th>付款时间长短</th>
							<th>逾期天数</th>
							<th>付款日</th>
							<th>类型</th>
							<th>利润</th>
							<th>利润率（%）</th>
							<th>完（%）</th>
							<th>分期付款余额数</th>
							<th>电话#1</th>
							<th>创建由</th>
							<th>状态</th>
							<th>功能</th>
						</tr>
<?php
$sql = "SELECT * FROM {$table_prefix}loan_details WHERE code = '".$customerID."' GROUP BY loan_id;";
$res = mysqli_query($conn, $sql);
while ($myrow1 = mysqli_fetch_array($res)) {
	$available = true;
	if ($loanID == $myrow1['loan_id']) {?>
						<tr onclick="window.location='customer.php?c=<?php echo $customerID;?>&l=<?php echo $myrow1['loan_id'];?>';" style="cursor: pointer; text-align: center; background-color: lightblue; color: black;">
<?php 	} else {?>
						<tr onclick="window.location='customer.php?c=<?php echo $customerID;?>&l=<?php echo $myrow1['loan_id'];?>';" style="cursor: pointer; text-align: center;">
<?php 	}?>
						
							<td></td>
							<td><?php echo $myrow1['loan_id'];?></td>
<?php
$sql7 = "SELECT * FROM {$table_prefix}users WHERE id = '".$myrow1['people1']."';";
$res7 = mysqli_query($conn, $sql7);
$myrow7 = mysqli_fetch_assoc($res7);
if ($myrow1['people2'] != 0) {
	$sql8 = "SELECT * FROM {$table_prefix}users WHERE id = '".$myrow1['people2']."';";
	$res8 = mysqli_query($conn, $sql8);
	$myrow8 = mysqli_fetch_assoc($res8);
	$str = $myrow7['name'].",".$myrow8['name'];
} else {
	$str = $myrow7['name'];
}
?>
							<td><?php echo $str;?></td>
							<td><?php echo $myrow['name'];?></td>
							<td><?php echo $myrow1['start_date'];?></td>
							<td>RM<?php echo sprintf("%.2f", $myrow1['loan_amount']);?></td>
							<td>RM<?php echo sprintf("%.2f", $myrow1['intrest']);?></td>
							<td>RM<?php echo sprintf("%.2f", $myrow1['deposit']);?></td>
<?php
$totalcollect = $myrow1['loan_amount'] - $myrow1['remaining_amount'];
// $now = time();
// $your_date = strtotime($myrow1['date']);
// $datediff = $now - $your_date;
// $later = round($datediff / (60 * 60 * 24));
$date1 = date_create($myrow1['date']);
$date2 = date_create(date("Y-m-d"));
$later = date_diff($date1, $date2);
?>
							<td>RM<?php echo sprintf("%.2f", $myrow1['gst']);?></td>
							<td>RM<?php echo sprintf("%.2f", $totalcollect);?></td>
							<td>RM<?php echo sprintf("%.2f", $myrow1['remaining_amount']);?></td>
							<td> - </td>
							<td><?php echo $myrow1['tenure'];?></td>
							<td><?php echo $myrow1['payday'];?></td>
<?php 
if ($myrow1['status'] == "1") {?>
							<td style="color: green;"> - </td>
							<td style="color: green;"> - </td>
<?php } else {
	if ($later -> format("%R%a") > 0) {?>
							<td style="color: red;"><?php echo $later -> format("%R%a");?></td>
							<td style="color: red;"><?php echo $myrow1['date'];?></td>
<?php 	} else {?>
							<td style="color: green;"><?php echo $later -> format("%R%a");?></td>
							<td style="color: green;"><?php echo $myrow1['date'];?></td>
<?php }}?>
							<td><?php echo $myrow1['type'];?></td>
							<td>RM<?php echo sprintf("%0.2f", $myrow1['intrest'] + $myrow1['gst']);?></td>
							<td><?php echo sprintf("%0.2f", (($myrow1['intrest'] + $myrow1['gst']) / $myrow1['loan_amount'] / ($myrow1['tenure'] * $myrow1['payday']) * 100));?></td>
							<td><?php echo sprintf("%0.1f", ($myrow1['intrest'] + $myrow1['gst']) / $myrow1['loan_amount'] * 100);?></td>
<?php
$sql5 = "SELECT * FROM {$table_prefix}loan_payment_details WHERE loan_id = '".$myrow1['loan_id']."';";
$res5 = mysqli_query($conn, $sql5);
$i = 0;
while ($myrow5 = mysqli_fetch_array($res5)) {
	$i++;
}
?>
							<td><?php echo $myrow1['tenure'] - $i;?></td>
							<td><?php echo $myrow1['phone'];?></td>
<?php
$sql5 = "SELECT * FROM {$table_prefix}users WHERE id = '".$myrow1['created']."';";
$res5 = mysqli_query($conn, $sql5);
$myrow5 = mysqli_fetch_assoc($res5);
?>
							<td><?php echo $myrow5['name'];?></td>
							<td><?php if ($myrow1['status'] == 0) echo "未付清"; else echo "付清";?></td>
							<td><a href="edit_loan.php?l=<?php echo $myrow1['id'];?>">修改记录</a></td>
						</tr>
	
<?php }?>
					</table>
				</div>
<!-- end area 1 -->

				<div class="grid">
<!-- area 2 -->
<?php
if ($available == true) {
	$sql20 = "SELECT * FROM {$table_prefix}loan_details WHERE loan_id = '".$loanID."';";
	$res20 = mysqli_query($conn, $sql20);
	$myrow20 = mysqli_fetch_assoc($res20);
	if ($myrow20['status'] == "0") {?>
					<div style="overflow-x: auto; text-align: left;">
						<fieldset style="max-width: 200px;">
							<legend>还款</legend>
							<form method="post">
								<input type="hidden" name="action" value="payloan">
								<label>本金: </label>
								<input type="text" name="pokok" required>
								<br>
								<label>利息: </label>
								<input type="text" name="bunga" value="0" required>
								<br>
								<input type="hidden" name="currentremaining" value="<?php echo $myrow20['remaining_amount'];?>">
								<button style="float: right; margin-top: 10px; margin-right: 12px;">确定还款</button>
							</form>
						</fieldset>
					</div>
<?php	}?>
<!-- end erea 2 -->

<!-- area 3 -->
					<div style="overflow-x: auto; text-align: left;">
						<fieldset>
							<legend>还款记录</legend>
							<table>
								<thead>
									<tr>
										<th>No.</th>
										<th>日期</th>
										<th>本金</th>
										<th>利息</th>
										<th>功能</th>
									</tr>
								</thead>
								<tbody>
<?php 
$h = 1;
$detail_id = 0;
$sql21 = "SELECT * FROM `{$table_prefix}loan_payment_details` WHERE loan_id = '".$loanID."';";
$res21 = mysqli_query($conn, $sql21);
while ($myrow21 = mysqli_fetch_array($res21)) {?>
									<tr>
										<td><?php echo $h++;?></td>
										<td><?php echo $myrow21['date'];?></td>
										<td><?php echo "RM".sprintf("%.2f", $myrow21['pokok']);?></td>
										<td><?php echo "RM".sprintf("%.2f", $myrow21['bunga']);?></td>
										<td style="white-space: nowrap;"><a href="edit_payment.php?l=<?php echo $myrow21['id'];?>">修改记录</a></td>
									</tr>
<?php
$detail_id = $myrow21['id'];
$amount = $myrow21['pokok'] + $myrow21['bunga'];
}?>
								</tbody>
							</table>
							<form method="post">
								<input type="hidden" name="action" value="delete_loan_detail">
								<input type="hidden" name="detail_id" value="<?php echo $detail_id;?>">
								<input type="hidden" name="amount" value="<?php echo $amount;?>">
								<button style="float: right; margin-top: 10px; margin-right: 12px;"onclick="return confirm('您确定删除最新的还款记录吗？')" <?php if ($detail_id == 0) {echo "disabled";}?>>删除最新纪录</button>
							</form>
						</fieldset>
					</div>
<?php }?>
<!-- end erea 3 -->
				</div>
					

			</div>
		</section>
<?php 
$sql = 'SELECT * FROM `'.$table_prefix.'loan_details` WHERE code = "'.$customerID.'" ORDER BY id DESC LIMIT 1;';
$res = mysqli_query($conn, $sql);
$myrow = mysqli_fetch_assoc($res);
$temp = explode("-", $myrow['loan_id']);
if ($temp[0] == "") {
	$newloanID = $customerID."-001";
} else {
	$newloanID = $temp[0]."-".str_pad(($temp[1] + 1), 3, "0", STR_PAD_LEFT);
}
?>
		<div id="id01" class="modal1" style="position: absolute;">
			<form class="modal-content animate" method="post" style="margin-top: 2%;">
				<input type="hidden" name="action" value="addloan">
				<input type="hidden" name="newloanID" value="<?php echo $newloanID;?>">
				<div style="margin-top: 10px;"><label><b>新贷款<?php echo $newloanID;?></b></label></div>
				
				<div  class="grid-container" style="grid-template-columns: 50% 50%;">
					<div class="grid-item">
						<div style="margin-bottom: 10px;"><label>贷款日期</label></div>
						<div style="margin-bottom: 10px;"><label>贷款数额：</label></div>
						<div style="margin-bottom: 10px;"><label>印花税：</label></div>
						<div style="margin-bottom: 10px;"><label>押金：</label></div>
						<div style="margin-bottom: 10px;"><label>GST：</label></div>
						<div style="margin-bottom: 10px;"><label>付款次数：</label></div>
						<div style="margin-bottom: 10px;"><label>付款时间长短：</label></div>
						<div style="margin-bottom: 10px;"><label>类型：</label></div>
						<div style="margin-bottom: 10px;"><label>电话：</label></div>
						<div style="margin-bottom: 10px;"><label>创建由：</label></div>
					</div>
					<div class="grid-item">
						<input type="date" name="start_date" style="margin-bottom: 10px; width: 80%;" value="<?php echo date("Y-m-d");?>">
						<input type="text" name="loan_amount" style="margin-bottom: 10px; width: 80%;">
						<input type="text" name="intrest" style="margin-bottom: 10px; width: 80%;">
						<input type="text" name="deposit" style="margin-bottom: 10px; width: 80%;">
						<input type="text" name="gst" style="margin-bottom: 10px; width: 80%;">
						<input type="text" name="tenure" style="margin-bottom: 10px; width: 80%;">
						<input type="text" name="day" style="margin-bottom: 10px; width: 80%;">
						<select name="type" style="margin-bottom: 10px; width: 80%;">
							<option value="天数">天数</option>
							<option value="周数">周数</option>
						</select>
						<input type="text" name="phone" style="margin-bottom: 10px; width: 80%;">
						<select name="people1" style="margin-bottom: 10px; width: 80%;">
<?php
$sql6 = "SELECT * FROM {$table_prefix}users;";
$res6 = mysqli_query($conn, $sql6);
while ($myrow6 = mysqli_fetch_array($res6)) {?>
							<option value="<?php echo $myrow6['id'];?>"><?php echo $myrow6['name'];?></option>
<?php }?>
						</select>
						<select name="people2" style="margin-bottom: 10px; width: 80%;">
							<option value="0" hidden>(Option)</option>
<?php
$sql6 = "SELECT * FROM {$table_prefix}users";
$res6 = mysqli_query($conn, $sql6);
while ($myrow6 = mysqli_fetch_array($res6)) {?>
							<option value="<?php echo $myrow6['id'];?>"><?php echo $myrow6['name'];?></option>
<?php }?>
						</select>
					</div>
					<div class="grid-item">
						<fieldset>
							<legend>状态</legend>
							<label><input id="flexid" type="radio" name="status" checked style="margin-bottom: 10px;" value="0" onclick="intrest2()">
							未付清</label><br>
							<label><input id="fixed" type="radio" name="status" style="margin-bottom: 10px;" value="1" onclick="intrest2()">
							付清</label>
						</fieldset>
					</div>
				</div>
				<div id="payday2field" style="margin-left: 20px; margin-right: 20px; display: none;">
					<fieldset style="text-align: left;">
						<legend>还款选项</legend>
						<div class="grid-container2" style="grid-template-columns: 50% 50%">
							<div class="grid-item" style="margin-top: 0;">
								<div style="margin-bottom: 5px;"><label>还款1</label></div>
								<div style="margin-bottom: 5px;"><label>日期：</label></div>
								<div style="margin-bottom: 5px;"><label>Pokok：</label></div>
								<div style="margin-bottom: 5px;"><label>Bunga：</label></div>
							</div>
							<div class="grid-item" style="margin-top: 0;">
								<br>
								<select style="margin-bottom: 5px;" name="payday">
									<option>1</option>
									<option>2</option>
									<option>3</option>
									<option>4</option>
									<option>5</option>
									<option>6</option>
									<option>7</option>
									<option>8</option>
									<option>9</option>
									<option>10</option>
									<option>11</option>
									<option>12</option>
									<option>13</option>
									<option>14</option>
									<option>15</option>
									<option>16</option>
									<option>17</option>
									<option>18</option>
									<option>19</option>
									<option>20</option>
									<option>21</option>
									<option>22</option>
									<option>23</option>
									<option>24</option>
									<option>25</option>
									<option>26</option>
									<option>27</option>
									<option>28</option>
								</select><br>
								<input type="text" name="paypokok" style="margin-bottom: 5px; width: 50%;"><br>
								<input type="text" name="paybunga" style="margin-bottom: 5px; width: 50%;"><br>
							</div>
							<div class="grid-item" style="margin-top: 0;">
								<div style="margin-bottom: 5px;"><label>还款2</label></div>
								<div style="margin-bottom: 5px;"><label>日期：</label></div>
								<div style="margin-bottom: 5px;"><label>Pokok：</label></div>
								<div style="margin-bottom: 5px;"><label>Bunga：</label></div>
							</div>
							<div class="grid-item" style="margin-top: 0;">
								<br>
								<select style="margin-bottom: 5px;" name="paydaytwo">
									<option>1</option>
									<option>2</option>
									<option>3</option>
									<option>4</option>
									<option>5</option>
									<option>6</option>
									<option>7</option>
									<option>8</option>
									<option>9</option>
									<option>10</option>
									<option>11</option>
									<option>12</option>
									<option>13</option>
									<option>14</option>
									<option>15</option>
									<option>16</option>
									<option>17</option>
									<option>18</option>
									<option>19</option>
									<option>20</option>
									<option>21</option>
									<option>22</option>
									<option>23</option>
									<option>24</option>
									<option>25</option>
									<option>26</option>
									<option>27</option>
									<option>28</option>
								</select><br>
								<input type="text" name="paypokok2" style="margin-bottom: 5px; width: 50%;"><br>
								<input type="text" name="paybunga2" style="margin-bottom: 5px; width: 50%;"><br>
							</div>
						</div>
					</fieldset>
				</div>
				<div style="text-align: right; margin: 0; margin-right: 20px;">
					<button class="loginBtn" style="background-color: #2090FE">增加</button>
				</div>
			</form>
		</div>
		<div id="id02" class="modal2">
			<form class="modal-content animate" method="post" style="width: 90%;">
				<input type="hidden" name="action" value="refinance">
				<div class="grid-container" style="grid-template-columns: 60% 40%;">
					<div class="grid-item" style="margin: 10px;">
						<label style="color: red;">请选择您要融资的贷款ID</label>
						<table>
							<tr>
								<th></th>
								<th>贷款ID</th>
								<th>贷款剩</th>
								<th>TEPI欠</th>
								<th>迁移</th>
								<th>罚款</th>
							</tr>
<?php 
$sql = 'SELECT * FROM `'.$table_prefix.'loan_details` AS a LEFT JOIN (SELECT loan_id AS loan, SUM(tepi_amount) AS tepi_amount, SUM(received_bunga) AS received_bunga FROM `'.$table_prefix.'loan_tepi_details` WHERE paid = "FALSE" GROUP BY loan_id) as b ON a.loan_id = b.loan WHERE remaining_amount != 0 AND code = "'.$customerID.'";';
$res = mysqli_query($conn, $sql);
while ($myrow = mysqli_fetch_array($res)) {?>
							<tr>
								<td style="text-align: center;"><input type="checkbox" name="" value="<?php echo $myrow['id'];?>"></td>
								<td style="text-align: right;"><?php echo $myrow['loan_id'];?></td>
								<td style="text-align: right;">RM<?php echo $myrow['remaining_amount'];?></td>
								<td style="text-align: right;">RM<?php echo $myrow['tepi_amount'] + $myrow['received_bunga'];?></td>
								<td style="text-align: center;"><input type="checkbox" name="" disabled></td>
								<td style="text-align: right;">RM<?php echo $myrow['bunga'];?></td>
							</tr>
<?php }?>
						</table>
					</div>
					<div class="grid-item" style="margin: 20px;">
						<fieldset style="padding: 0; width: 80%;">
							<legend>新的贷款</legend>
							<div class="grid-container" style="grid-template-columns: 50% 50%;">
								<div class="grid-item" style="margin: 0;">
									<div style="margin-bottom: 10px;">
									<label>贷款ID:</label><br></div>
									<div style="margin-bottom: 10px;">
									<label>新贷款日期:</label><br></div>
									<div style="margin-bottom: 10px;">
									<label>新贷款金额(RM):</label><br></div>
									<div style="margin-bottom: 10px;">
									<label>融资资本(RM):</label><br></div>
									<div style="margin-bottom: 10px;">
									<label>额外添加资本(RM):</label><br></div>
									<div style="margin-bottom: 10px;">
									<label>利率 (%):</label><br></div>
									<div style="margin-bottom: 10px;">
									<label>每月POKOK (RM):</label><br></div>
								</div>
								<div class="grid-item" style="margin: 0;">
									<div style="margin-bottom: 10px;">
									<label><?php echo $newloanID;?></label></div>
									<input type="date" name="start_date" style="margin-bottom: 10px; width: 80%;" value="<?php echo date("Y-m-d");?>">
									<input type="text" name="" style="margin-bottom: 10px; width: 80%;">
									<input type="text" name="" style="margin-bottom: 10px; width: 80%;">
									<input type="text" name="" style="margin-bottom: 10px; width: 80%;">
									<input type="text" name="" style="margin-bottom: 10px; width: 80%;">
									<input type="text" name="" style="margin-bottom: 10px; width: 80%;">
								</div>
								<div class="grid-item" style="margin: 0;">
									<fieldset style="padding: 0; width: 80%;">
										<legend>利息方案</legend>
										<label><input type="radio" name="">Fixed</label><br>
										<label><input type="radio" name="">Flexi</label><input type="text" name="" style="width: 20px; margin-left: 10px;">
									</fieldset>
								</div>
								<div class="grid-item" style="margin: 0;">
									<fieldset style="padding: 0; width: 80%;">
										<legend>选项</legend>
										<label>
										<input type="checkbox" name="">整数利息</label>
									</fieldset>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div id="id03" class="modal3">
			<form class="modal-content animate" method="post">
				<input type="hidden" name="action" value="settle">
				<div style="margin-top: 10px;"><label><b>抵消贷款<?php echo $loanID;?></b></label></div>
				
				<div  class="grid-container" style="grid-template-columns: 50% 50%;">
					<div class="grid-item">
						<div style="margin-bottom: 10px;"><label>贷款日期</label></div>
						<div style="margin-bottom: 10px;"><label>贷款欠 (RM)：</label></div>
						<div style="margin-bottom: 10px;"><label>罚款 (RM)：</label></div>
					</div>
					<div class="grid-item">
						<input type="date" name="POdate" style="margin-bottom: 10px; width: 80%;" value="<?php echo date("Y-m-d");?>">
						<input type="text" name="" style="margin-bottom: 10px; width: 80%;" value="<?php echo $currentremaining;?>" disabled>
						<input type="hidden" name="POamount" value="<?php echo $currentremaining;?>">
						<input type="text" name="PObunga" style="margin-bottom: 10px; width: 80%;" value="<?php echo $currentintrest * $currentloan * 3 / 100;?>">
					</div>
				</div>
				<div style="text-align: right; margin: 0; margin-right: 20px;">
					<button class="button" style="width: 80px; height: 40px; margin-left: 10px; color: white; background: #E18B00; border: solid 1px black;">增加</button>
				</div>
			</form>
		</div>
	<script>
		// Get the modal
		var modal1 = document.getElementById('id01');
		var modal2 = document.getElementById('id02');
		var modal3 = document.getElementById('id03');

		// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
			if (event.target == modal1) {
				modal1.style.display = "none";
			}
			if (event.target == modal2) {
				modal2.style.display = "none";
			}
			if (event.target == modal3) {
				modal3.style.display = "none";
			}
		}
		function confirmDelete() {
			var cf = confirm("您确定要删除<?php echo $loanID;?>贷款吗？");
			if (cf) {
				window.location = 'includes/function/deleteloan.php?c=<?php echo $customerID;?>&l=<?php echo $loanID?>';
			}
		}
		function confirmBlack() {
			var cf = confirm("您确定转换此客户去违约客户吗？");
			if (cf) {
				window.location = 'black.php?c=<?php echo $customerID;?>';
			}
		}
		// function return() {
		// 	var o = 0;
		// 	for (var i = 0; i < <?php echo $o;?>; i++) {
		// 		if (document.getElementById("payreturn" + i).checked == true) {
		// 			$o++;
		// 		}
		// 	}
		// 	if (o > 0) {
		// 		document.getElementById("field").style.color = "black";
		// 		document.getElementById("date").disabled = false;
		// 		document.getElementById("button").disabled = false;
		// 		document.getElementById("button").style.cursor = "pointer";
		// 	}
		// }
	</script>
</body>
</html>