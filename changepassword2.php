<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if(!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] != true){
    header("location: index.php");
    exit;
}

// Include config file
require_once "includes/db/config.php";

$newpassword = password_hash($_COOKIE["gfg"], PASSWORD_DEFAULT);

if ($_COOKIE['gfg'] == "" || $_COOKIE['gfg'] == "null") {
		echo '<script>alert("Your password can\'t empty. Please try again.")</script>';
		echo "<script>window.location = 'changepassword.php';</script>" ;
} else {
	$sql1 = "UPDATE {$table_prefix}users SET password = '".$newpassword."' WHERE id = '".$_SESSION['id']."';";
	if (mysqli_query($conn, $sql1)) {
		// setcookie("gfg", "");
		echo '<script>alert("Your password change successful. Please login with new password.")</script>';
		echo "<script>window.location = 'includes/function/logout.php';</script>" ;
	}
}

?>