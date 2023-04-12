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

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
<?php
echo '<script type="text/javascript"> ';

echo 'var inputname = prompt("Please enter your new customer ID.", "");'; 

echo 'createCookie("cus_id", inputname, "1");';

echo 'function createCookie(name, value, days) {
    var expires;
      
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    else {
        expires = "";
    }
      
    document.cookie = escape(name) + "=" + 
        escape(value) + expires + "; path=/";
}';

echo '</script>';
echo "<script>window.location = 'add_customer.php';</script>" ;
?>
</head>
<body>

</body>
</html>