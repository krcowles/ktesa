<?php
/**
 * This page displays site visitor data for the incoming date range.
 * All visitor data is stored in the VISITORS table by the utility
 * botScreen.php which is included in global_boot.php. All public-facing
 * pages will invoke the script (other than admins).
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

date_default_timezone_set('America/Denver');
$curr_yr   = date("Y");
$today  = date("Y-m-d");
$lastwk = date("Y-m-d", strtotime("-7 days"));
$begin_time = ' 00:00:00';
$end_time   = ' 23:59:59';

$span = filter_input(INPUT_GET, 'time');
switch ($span) {
case 'today' :
    $start = $today . $begin_time;
    $end   = $today . $end_time;
    break;
case 'week' :
    $start = $lastwk . $begin_time;
    $end   = $today  . $end_time;
    break;
case 'month' :
    $month  = filter_input(INPUT_GET, 'mo');  // string with leading 0's as needed
    $daycnt = cal_days_in_month(CAL_GREGORIAN, intval($month), intval($curr_yr));
    $start  = date($curr_yr . "-" . $month . "-01") . $begin_time;
    $end    = date($curr_yr . "-" . $month . "-" . $daycnt) . $end_time;
    break;
case 'range' :
    $range = filter_input(INPUT_GET, 'rg');
    $dates = explode(":", $range);
    $start = date($dates[0]) . $begin_time;
    $end   = date($dates[1]) . $end_time;
}
$dataReq = "SELECT * FROM `VISITORS` WHERE `vdatetime` BETWEEN '{$start}' AND " .
    "'{$end}';";
$visitor_data = $pdo->query($dataReq)->fetchAll(PDO::FETCH_ASSOC);
if (count($visitor_data) === 0) {
    echo '<span id="nodat">There is no visitation data to display</span>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Display Visitor Data</title>
    <meta charset="utf-8" />
    <meta name="description" content="Create the USERS Table" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php require "../pages/favicon.html";?>
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/visitor_data.css" rel="stylesheet" />
    <link href="../styles/jquery-ui.min.css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.min.js"></script>
</head>

<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Display Selected Visitor Data</p>
<p id="active" style="display:none">Admin</p>
 
<table id="vdat" style="margin-top:24px;">
    <thead>
        <tr>
            <th>User IP</th>
            <th>Browser</th>
            <th>Platform</th>
            <th>Time</th>
            <th>Page</th>
            <th>City</th>
            <th>Region</th>
            <th>Code</th>
        </tr>
    </thead>
    <tbody>
    <?php for ($k=0; $k<count($visitor_data); $k++) : ?>
        <tr>
                <td><?=$visitor_data[$k]['vip'];?></td>
                <td><?=$visitor_data[$k]['vbrowser'];?></td>
                <td><?=$visitor_data[$k]['vplatform'];?></td>
                <td><?=$visitor_data[$k]['vdatetime'];?></td>
                <td><?=$visitor_data[$k]['vpage'];?></td>
                <td><?=$visitor_data[$k]['vcity'];?></td>
                <td><?=$visitor_data[$k]['vregion'];?></td>
                <td><?=$visitor_data[$k]['vloc']?></td>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>
<div id="loading" style="display:none;text-align:center;">
    <img src="../images/loader-64x/Preloader_4.gif"
        alt="Waiting for server to complete" />
</div>

</body>
</html>