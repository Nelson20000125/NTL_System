<?php
// Initialize the session
session_start();
 
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] != true){
	header("location: index.php");
    exit;
}

// Include config file
require_once "includes/db/config.php";

$superadmin = $_SESSION['superadmin'];

$username = $password = "";

$sql = "SELECT id, code FROM {$table_prefix}branch ORDER BY code;";
$res = mysqli_query($conn, $sql);

if ($_SERVER["REQUEST_METHOD"] == "POST"){
	if (($_POST['action'] == "changepassword")) {
		$oldpassword = trim($_POST["oldpassword"]);
		$newpassword = trim($_POST["newpassword"]);
		if ($oldpassword == $newpassword) {
			echo '<script>alert("你的旧密码与新密码一样！无需更换密码。")</script>';
		} else {
			$sql = "SELECT password FROM {$table_prefix}users WHERE user_id = ?;";
			if($stmt = mysqli_prepare($conn, $sql)) {
				mysqli_stmt_bind_param($stmt, "s", $param_username);
	    		$param_username = $_SESSION["username"];
	    		if(mysqli_stmt_execute($stmt)) {
	    			mysqli_stmt_store_result($stmt);
	    			if(mysqli_stmt_num_rows($stmt) == 1) {
	    				mysqli_stmt_bind_result($stmt, $hashed_password);
	    				if(mysqli_stmt_fetch($stmt)) {
	    					if(password_verify($oldpassword, $hashed_password)) {
	    						$sql = 'UPDATE '.$table_prefix.'users SET password = "'.password_hash($newpassword, PASSWORD_DEFAULT).'" WHERE user_id = "'.$_SESSION["username"].'";';
	    						if (mysqli_query($conn, $sql)){
	    							echo '<script>alert("你已成功更换密码！请用新的密码再次登录系统。")</script>';
	    							echo "<script>window.location = 'includes/function/logout.php'</script>";
	    						} else {
	    							echo '<script>alert("Something error. Please try again later.")</script>';
	    						}
	    					} else {
	    						echo '<script>alert("你的旧密码不正确。请重新输入。")</script>';
	    					}
	    				}
	    			} 
	    		}
			}
		}
	} else if (($_POST['action'] == "search")) {
		$input = $_POST['searchCustomer'];
		if ($_POST['searchType'] == "ic") {
			$sql1 = 'SELECT * FROM '.$table_prefix.'customer_details WHERE ic LIKE "%'.$input.'%" OR passport LIKE "%'.$input.'%";';
		} elseif ($_POST['searchType'] == "name") {
			$sql1 = 'SELECT * FROM '.$table_prefix.'customer_details WHERE name LIKE "%'.$input.'%";';
		} elseif ($_POST['searchType'] == "phone") {
			$sql1 = 'SELECT * FROM '.$table_prefix.'customer_details WHERE phone1 LIKE "%'.$input.'%";';
		}
		$res1 = mysqli_query($conn, $sql1);
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="includes/css/style.css">
	<!--load all Font Awesome styles -->
	<link href="includes/fontawesome/css/all.css" rel="stylesheet">
	<script src="includes/js/script.js"></script>
	<link href="includes/css/home.css" rel="stylesheet">
	<title>NTL System</title>
    <style type="text/css">
        .ul {
            display: block;
            border: none;
            text-align: left;
        }

        .li {
            display: block;
            border: none;
            padding: 0;
        }
    </style>
</head>
<body>
	<div class="main">
		<div id="id01" class="modal">
			<form class="modal-content animate" method="post">
				<input type="hidden" name="action" value="changepassword">
				<div class="container">
					<label><b>旧密码：</b></label>
					<input type="password" name="oldpassword" required class="loginInput"><br>

					<label><b>新密码：</b></label>
					<input type="password" name="newpassword" required class="loginInput"><br>
					<button class="loginBtn">确认</button>
				</div>
			</form>
		</div>
		<section>
			<div class="grid-container">
				<div class="tab grid-item" style="margin: 0;">
					<a href="home.php"><button class="tablinks">客户</button></a>
					<button class="tablinks active">搜索</button>
                    <a href="downline.php"><button class="tablinks">下线</button></a>
                    <a href="expense.php"><button class="tablinks">开销</button></a>
<?php
if ($superadmin == "1") {?>
                    <a href="report.php"><button class="tablinks">报告</button></a>
<?php }?>
				</div>
				<div style="text-align: -webkit-right;">
					<label class="user" style="margin-right: 40px;"><?php echo $_SESSION["username"];?></label>
						<label class="menu test">
							<i class="fa-solid fa-gear" style="text-align-last: center;border: solid 1px; padding: 1px;"></i>
							<ul class="nav-menu">
<?php
if ($superadmin == "1") {?>
								<li><a href="register.php" style="width: 100%; text-decoration-line: none; color: black;">Create User</a></li>	
<?php }?>
								<li><a style="width: 100%; text-decoration-line: none; color: black; cursor: pointer;" onclick="document.getElementById('id01').style.display='block'">更换密码</a></li>
								<li><a href="includes/function/logout.php" style="width: 100%; text-decoration-line: none; color: black;">登出</a></li>
							</ul>
						</label>
				</div>
			</div>
			<div id="search" class="tabcontent" style="display: block;">
				<form method="post">
					<input type="hidden" name="action" value="search">
					<div class="grid-container">
						<div class="grid-item" style="margin-left: 0;">
							<fieldset style="padding: 0">
								<legend style="font-size: 15px;">搜索依据</legend>
								
								<label style="font-size: 15px;"><input type="radio" name="searchType" value="ic" checked>按IC/Passport号码</label><br>
								
								<label style="font-size: 15px;"><input type="radio" name="searchType" value="name">按名字</label><br>
								
								<label style="font-size: 15px;"><input type="radio" name="searchType" value="phone">按手机号码</label><br>
							</fieldset>
						</div>
						<div class="grid-item" style="margin-left: 0; margin-right: 0; text-align: right; margin-top: 28px;">
							<input type="text" name="searchCustomer" style="width: -webkit-fill-available; margin-bottom: 5px;"><br>
							<!-- <a href="#" style="border: solid 1px black; padding-left: 5px; padding-right: 5px;"><i class="fa-solid fa-magnifying-glass"></i></a> -->
							<button class="button"><i class="fa-solid fa-magnifying-glass"></i></button>
						</div>
					</div>
				</form>
				<div style="text-align: left">
					<label>搜索结果</label>
					<table>
						<tr>
							<th style="text-align: center;">客户ID</th>
							<th style="text-align: center;">名字</th>
							<th style="text-align: center;">IC/Passport号码</th>
							<th style="text-align: center;">电话号码</th>
							<th style="text-align: center;">欠款(RM)</th>
							<th style="text-align: center;">盈利(RM)</th>
						</tr>
<?php
while ($myrow1 = mysqli_fetch_assoc($res1)) {?>
						<tr>
							<td style="text-align: center;"><a href="customer.php?c=<?php echo $myrow1['customer_code'];?>"><?php echo $myrow1['customer_code'];?></a></td>
							<td style="text-align: center;"><?php echo $myrow1['name'];?></td>
							<td style="text-align: center;"><?php echo $myrow1['ic'].$myrow1['passport'];?></td>
							<td style="text-align: center;"><?php echo $myrow1['phone1'];?></td>
<?php
$loan_amount2 = 0;
$remaining2 = 0;
$intrest = 0;
$gst = 0;
$profit2 = 0;
$sql2 = "SELECT * FROM {$table_prefix}loan_details WHERE code = '".$myrow1['customer_code']."';";
$res2 = mysqli_query($conn, $sql2);
while ($myrow2 = mysqli_fetch_array($res2)) {
	$remaining2 += $myrow2['remaining_amount'];
	$loan_amount2 += $myrow2['loan_amount'];
	$intrest += $myrow2['intrest'];
	$gst += $myrow2['gst'];
}
$loan_amount2 -= ($intrest + $gst);
$sql2 = "SELECT SUM(pokok) AS pokok, SUM(bunga) AS bunga FROM {$table_prefix}loan_payment_details WHERE loan_id LIKE '%".$myrow1['customer_code']."%';";
$res2 = mysqli_query($conn, $sql2);
while ($myrow2 = mysqli_fetch_array($res2)) {
	$profit2 += $myrow2['pokok'] + $myrow2['bunga'];
}
$profit2 -= $loan_amount2;
?>
							<td style="text-align: center;"><?php echo sprintf("%.2f", $remaining2);?></td>
							<td style="text-align: center; color: <?php if ($profit2 > 0) echo "green"; elseif ($profit2 < 0) echo "red";?>;"><?php echo sprintf("%.2f", $profit2);?></td>
						</tr>
<?php }?>
					</table>
				</div>
			</div>
		</section>
	</div>
	<script type="text/javascript">
		// Get the modal
		var modal = document.getElementById('id01');

		// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}
	</script>
</body>
</html>