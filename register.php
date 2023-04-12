<?php
// Initialize the session
session_start();

setcookie ("year","");
setcookie ("month","");
 
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] != true){
	header("location: index.php");
    exit();
}

if (!isset($_SESSION['superadmin']) && $_SESSION['superadmin'] != "1"){
	header("location: home.php");
	exit();
}

// Include config file
require_once "includes/db/config.php";

$id = $_SESSION["id"];
$superadmin = $_SESSION['superadmin'];

if ($_SERVER["REQUEST_METHOD"] == "POST"){
	$r = 0;

	$x = 0;
	$sql = "SELECT * FROM {$table_prefix}users WHERE user_id = '".$_POST['username']."';";
	$res = mysqli_query($conn, $sql);
	while ($myrow = mysqli_fetch_array($res)) {
		$x++;
	}

	if ($x != 0) {
		echo '<script>alert("That username has been taken.")</script>';
		$r++;
	}

	if ($r != 0) {
	} else {
		$user_id = $_POST['username'];
		$password = password_hash($_POST['psw'], PASSWORD_DEFAULT);
		$superadmin = $_POST['superadmin'];
		$name = $_POST['name'];
		$ic = $_POST['ic'];
		$phone_number = $_POST['phone'];
		if ($superadmin != 0)
			$superior = 0;
		else
			$superior = $_POST['superior'];
		$sql = "INSERT INTO {$table_prefix}users (user_id, password, superadmin, name, ic, phone_number, superior) VALUES ('".$user_id."', '".$password."', '".$superadmin."', '".$name."', '".$ic."', '".$phone_number."', '".$superior."');";
		$res = mysqli_query($conn, $sql);
		echo '<script>alert("User account create successful.")</script>';
		echo "<script>window.location = 'home.php'</script>";
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
	<title>Create User Account</title>
	<style type="text/css">
		* {
			box-sizing: border-box
		}

		/* Add padding to containers */
		.container {
			padding: 16px;
		}

		/* Full-width input fields */
		input[type=text], input[type=password], select {
			width: 100%;
			padding: 15px;
			margin: 5px 0 22px 0;
			display: inline-block;
			border: none;
			background: #f1f1f1;
		}

		input[type=text]:focus, input[type=password]:focus {
			background-color: #ddd;
			outline: none;
		}

		/* Overwrite default styles of hr */
		hr {
			border: 1px solid #f1f1f1;
			margin-bottom: 25px;
		}

		/* Set a style for the submit/register button */
		.registerbtn {
			background-color: #04AA6D;
			color: white;
			padding: 16px 20px;
			margin: 8px 0;
			border: none;
			cursor: pointer;
			width: 100%;
			opacity: 0.9;
		}

		.registerbtn:hover {
			opacity: 1;
		}

		/* Add a blue text color to links */
		a {
			color: dodgerblue;
		}

		/* Set a grey background color and center the text of the "sign in" section */
		.signin {
			background-color: #f1f1f1;
			text-align: center;
		}
	</style>
</head>
<body>
	<form method="post">
		<div class="container">
			<h1>用户创建</h1>
			<p>请填写此表格以创建用户帐户。</p>
			<hr>

			<label for="username"><b>用户名</b></label>
			<input type="text" placeholder="输入用户名" name="username" id="username" required>

			<label for="psw"><b>密码</b></label>
			<input type="password" placeholder="输入密码" name="psw" id="psw" required>

			<label>权限</label>
			<select name="superadmin" id="selection" onchange="change()">
				<option value="1">Admin</option>
				<option value="0" selected>Agent</option>
			</select>

			<div id="id01">
				<label>上线</label>
				<select name="superior">
<?php
$sql = "SELECT id, name FROM {$table_prefix}users;";
$res = mysqli_query($conn, $sql);
while ($myrow = mysqli_fetch_array($res)) {?>
					<option value="<?php echo $myrow['id'];?>"><?php echo $myrow['name'];?></option>
<?php }?>
				</select>
			</div>

			<label for="name"><b>姓名</b></label>
			<input type="text" placeholder="输入姓名" name="name" id="name" required>

			<label for="ic"><b>身份证号码</b></label>
			<input type="text" placeholder="输入身份证号码" name="ic" id="ic" required>

			<label for="phone"><b>手机号码</b></label>
			<input type="text" placeholder="输入手机号码" name="phone" id="phone" required>
			<hr>

			<button type="submit" class="registerbtn">创建账户</button>

		</div>
	</form>
	<script type="text/javascript">
        function change() {
            if (document.getElementById("selection").value == 1) {
                document.getElementById("id01").style.display = "none";
            } else {
                document.getElementById("id01").style.display = "block";
            }
        }
	</script>
</body>
</html>