<?php
// Initialize the session
session_start();

// Include config file
require_once "includes/db/config.php";
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] != true){
    header("location: index.php");
    exit;
}

$date = date("Y-m-d");
$temp = explode("-", $date);
$currentyear = $temp[0];
$currentmonth = $temp[1];
$days = 31;

switch ($currentmonth) {
    case '01':
    case '03':
    case '05':
    case '07':
    case '08':
    case '10':
    case '12':
        $days = 31;
        break;
    case '04':
    case '06':
    case '09':
    case '11':
        $days = 30;
        break;
    case '02':
        if ($currentyear % 4 == 0) {
            $days = 29;
        } else {
            $days = 28;
        }
        break;
}

if (isset($_GET['u'])) {
    $user = $_GET['u'];
    $sql = "SELECT * FROM {$table_prefix}users WHERE id = '".$user."';";
    $temp_user = 1;
    $i = 0;
    $user_list = array();
    $id_list = array();
    do {
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($res);
        $temp_user = $row['superior'];
        $user_list[$i] = $row['user_id'];
        $id_list[$i] = $row['id'];
        $sql = "SELECT * FROM {$table_prefix}users WHERE id = '".$temp_user."';";
        $i++;
    } while ($temp_user != 1);
} else {
    $user = 1;
}

// print_r($user_list);
// echo "<br>";
// print_r($id_list);
// echo "<br>";
// echo $i;
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>NTL-System</title>
    <link rel="icon" href="../includes/img/focus37-logo.png" type="image/icon type">

    <!-- Custom fonts for this template-->
    <link href="includes/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="includes/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="includes/css/customer_detail.css" rel="stylesheet">
    <link href="includes/css/w3s.css" rel="stylesheet">

</head>

<body id="page-top" onload="time()">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

<?php
include('includes/function/topbar.php');
?>  

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
<?php
if (isset($_GET['u'])) {?>
                            <h6 class="m-0 font-weight-bold text-primary">用户列表<?php if ($_SESSION['superadmin'] == '1') {?><a class="btn btn-primary" href="add_user.php?u=<?php echo $_GET['u'];?>" style="float: right;">添加新用户</a><?php }?></h6>
<?php } else {?>
                            <h6 class="m-0 font-weight-bold text-primary">用户列表<?php if ($_SESSION['superadmin'] == '1') {?><a class="btn btn-primary" href="add_user.php?u=<?php echo $_GET['u'];?>" style="float: right;">添加新用户</a><?php }?></h6>
<?php }
if ($_SESSION['superadmin'] == '1') {?>
                                    <a href="user_list.php">admin</a>
<?php
for ($j = $i - 1; $j >= 0; $j--) {?>
                                     > <a href="user_list.php?u=<?php echo $id_list[$j];?>"><?php echo $user_list[$j];?></a>
<?php }
}?>
                        </div>
                        <div class="card-body" style="overflow-x: auto;">
                            <div class="table-responsive">
                                <div>
                                </div>
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" style="white-space: nowrap;">
                                    <thead>
                                        <tr style="text-align: right;">
                                            <th style="text-align: center;">功能</th>
                                            <th>貸款</th>
                                            <th>本金</th>
                                            <th>收集</th>

                                            <th>利息</th>
                                            <th>費用</th>
                                            <th>餘額</th>

                                            <th>利潤</th>
                                            <th>獎金</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php
if (isset($_GET['u'])) {
    if ($_SESSION['superadmin'] == '1') {
        $sql = "SELECT * FROM {$table_prefix}users WHERE superior = '".$user."';";
    } else {
        $sql = "SELECT * FROM {$table_prefix}users WHERE id = '".$_SESSION['id']."';";
    }
} else {
    if ($_SESSION['superadmin'] == '1') {
        $sql = "SELECT * FROM {$table_prefix}users WHERE superior = '1';";
    } else {
        $sql = "SELECT * FROM {$table_prefix}users WHERE id = '".$_SESSION['id']."';";
    }
}

$res = mysqli_query($conn, $sql);
while ($myrow = mysqli_fetch_array($res)) {?>
                                        <tr style="text-align: right;">
                                            <td style="text-align: center;">
                                                <?php echo $currentyear." ".$currentmonth."月";?>
                                                <br>
                                                <a href="users.php?u=<?php echo $myrow['id'];?>"><?php echo $myrow['name'];?></a>
<?php
if ($_SESSION['superadmin'] == '1') {?>
                                                (<a href="user_list.php?u=<?php echo $myrow['id'];?>">下線</a>)
                                                <br>
                                                <a href="add_bonus.php?u=<?php echo $myrow['id'];?>">添加獎金</a>
<?php }?>
                                                </td>
<?php 
    $loan_amount = 0;
    $collect = 0;
    $ben = 0;
    $intrest = 0;
    $expenses = 0;
    $profit = 0;
    $bonus = 0;

    $sql4 = "SELECT * FROM {$table_prefix}bonus WHERE agent_id = '".$myrow['id']."' AND month = '".$currentmonth."' AND year = '".$currentyear."';";
    $res4 = mysqli_query($conn, $sql4);
    while ($myrow4 = mysqli_fetch_array($res4)) {
        $bonus += $myrow4['bonus'];
    }

    for ($i = 1; $i <= $days; $i++) {
        $date = $currentyear."-".str_pad($currentmonth, 2, "0", STR_PAD_LEFT)."-".str_pad($i, 2, "0", STR_PAD_LEFT);

        $sql1 = "SELECT * FROM `{$table_prefix}loan_details` WHERE start_date = '".$date."' AND (people1 = '".$myrow['id']."' OR people2 = '".$myrow['id']."');";
        $res1 = mysqli_query($conn, $sql1);

        while ($myrow1 = mysqli_fetch_array($res1)) {
            $loan_amount += $myrow1['loan_amount'];
            $profit += $myrow1['gst'];
            // $ben += ($myrow1['loan_amount'] - $myrow1['gst']);
        }
        $ben = $loan_amount - $profit;

        $sql2 = "SELECT a.* FROM `{$table_prefix}loan_payment_details` AS a LEFT JOIN loan_details AS b ON a.loan_id = b.loan_id WHERE a.date = '".$date."' AND (people1 = '".$myrow['id']."' OR people2 = '".$myrow['id']."');";
        $res2 = mysqli_query($conn, $sql2);
        while ($myrow2 = mysqli_fetch_array($res2)) {
            $collect += $myrow2['pokok'];
            $intrest += $myrow2['bunga'];
        }

        $sql3 = "SELECT * FROM {$table_prefix}operating_expenses WHERE date LIKE '%".$date."%' AND user = '".$myrow['id']."';";
        $res3 = mysqli_query($conn, $sql3);
        while ($myrow3 = mysqli_fetch_array($res3)) {
            $expenses += $myrow3['cost'];
        }


    }
    // $profit = ($collect + $intrest - $ben - $expenses);
    $sql4 = "SELECT * FROM {$table_prefix}credit_log WHERE month = '".$currentmonth."' AND year = '".$currentyear."' AND user = '".$myrow['id']."';";
?>
                                            <td>RM<?php printf("%0.2f", $loan_amount);?></td>
                                            <td>RM<?php printf("%0.2f", $ben);?></td>
                                            <td>RM<?php printf("%0.2f", $collect);?></td>
                                            <td>RM<?php printf("%0.2f", $intrest);?></td>
                                            <td>RM<?php printf("%0.2f", $expenses);?></td>
<?php 
if ($myrow['score'] < 0) {?>
                                            <td>-RM<?php printf("%0.2f", $myrow['score'] * -1);?></td>
<?php } else {?>
                                            <td>RM<?php printf("%0.2f", $myrow['score']);?></td>
<?php }
if ($profit < 0) {?>
                                            <td>-RM<?php printf("%0.2f", $profit * -1);?></td>
<?php } else {?>
                                            <td>RM<?php printf("%0.2f", $profit);?></td>
<?php }?>
                                            <td>RM<?php printf("%0.2f", $bonus);?></td>
                                        </tr>
<?php }
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                            
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->


        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

<?php
include('includes/function/logout_modal.php');
?> 

    <!-- Bootstrap core JavaScript-->
    <script src="includes/vendor/jquery/jquery.min.js"></script>
    <script src="includes/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="includes/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="includes/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="includes/vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="includes/js/demo/chart-area-demo.js"></script>
    <script src="includes/js/demo/chart-pie-demo.js"></script>
    <script src="includes/js/script2.js"></script>
    <script>
        function time() {
            t_div = document.getElementById('showtime');
            var now = new Date()
            t_div.innerHTML = now.getFullYear() + "/" + (now.getMonth() + 1) + "/" + now.getDate() + " " + now.getHours() + ":" + now.getMinutes() + ":" + now.getSeconds() + "";
            setTimeout(time, 1000);
        }
    </script>

</body>

</html>