<?php
// Initialize the session
session_start();

if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] != true){
    header("location: index.php");
    exit;
}

// Include config file
require_once "includes/db/config.php";

$total_debit = 0;
$total_credit = 0;
$total = 0;

$sql = "SELECT amount, type, EXTRACT(month FROM date) AS month, EXTRACT(year FROM date) AS year FROM `{$table_prefix}cash_book` GROUP BY YEAR(date), Month(date) ORDER BY EXTRACT(year FROM date) DESC, EXTRACT(month FROM date) DESC;";
$res = mysqli_query($conn, $sql);
while ($myrow = mysqli_fetch_array($res)) {
	if ($myrow['type'] == "debit") {
		$total_debit += $myrow['amount'];
	} elseif($myrow['type'] == "credit") {
		$total_credit += $myrow['amount'];
	}
}

$total = $total_debit - $total_credit;

$sql1 = "SELECT EXTRACT(month FROM date) AS month, EXTRACT(year FROM date) AS year FROM `{$table_prefix}cash_book` GROUP BY YEAR(date), Month(date) ORDER BY EXTRACT(year FROM date) DESC, EXTRACT(month FROM date) DESC LIMIT 1;";
$res1 = mysqli_query($conn, $sql1);
$myrow1 = mysqli_fetch_assoc($res1);

$year = $myrow1['year'];
$month = str_pad($myrow1['month'], 2, "0", STR_PAD_LEFT);

switch ($month) {
    case "01":
    case "03":
    case "05":
    case "07":
    case "08":
    case "10":
    case "12":
        $day = "31";
        break;
    case "04":
    case "06":
    case "09":
    case "11":
        $day = "30";
        break;
    case "02":
        if ($year % 4 == 0) {
            $day = "29";
        } else {
            $day = "28";
        }
        break;
}

$date = $year."-".$month."-".str_pad($day, 2, "0", STR_PAD_LEFT);
$month++;
if ($month > 12) {
	$month -= 12;
	$year ++;
}

$date2 = $year."-".str_pad($month, 2, "0", STR_PAD_LEFT)."-01";

if ($total < 0) {
	$sql = "INSERT INTO `{$table_prefix}cash_book` (`description`, `type`, `amount`, `date`, `user`) VALUES ('Closed balance', 'credit', '".($total * -1)."', '".$date."', '1');";
	$sql1 = "INSERT INTO `{$table_prefix}cash_book` (`description`, `type`, `amount`, `date`, `user`) VALUES ('Opening balance', 'credit', '".($total * -1)."', '".$date2."', '1');";
} else {
	$sql = "INSERT INTO `{$table_prefix}cash_book` (`description`, `type`, `amount`, `date`, `user`) VALUES ('Closed balance', 'debit', '".$total."', '".$date."', '1');";
	$sql1 = "INSERT INTO `{$table_prefix}cash_book` (`description`, `type`, `amount`, `date`, `user`) VALUES ('Opening balance', 'debit', '".$total."', '".$date2."', '1');";
}

mysqli_query($conn, $sql);
mysqli_query($conn, $sql1);
echo "<script>window.location = 'cash_book.php';</script>" ;
?>