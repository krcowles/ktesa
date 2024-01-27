<?php
/**
 * This page displays site visitor data for the incoming date range.
 * Do to limitations imposed by the ipinfo site, this script now uses
 * the MaxMind geolocation db and software library to access key data.
 * The download and scripting information is provided by CodexWorld:
 * https://www.codexworld.com/get-geolocation-from-ip-address-using-php/
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
use GeoIp2\Database\Reader;

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
// find locations of IP addresses (Not done in getLogin.php to improve performance)
$vloc = [];
$vreg = [];
$vcnt = [];
foreach ($visitor_data as $row) {
    try {
        $cityDbReader = new Reader('../GeoLite2-City.mmdb'); 
        $record = $cityDbReader->city($row['vip']); 
    } catch(Exception $e) {
        $api_error = $e->getMessage(); 
    }
    // Get geolocation data 
    if (empty($api_error)) { 
        $country_code = !empty($record->country->isoCode) ?
            $record->country->isoCode : '';
        array_push($vcnt, $country_code);
        $state_name
            = !empty($record->mostSpecificSubdivision->name) ?
                $record->mostSpecificSubdivision->name : ''; 
        array_push($vreg, $state_name);
        $city_name = !empty($record->city->name)?$record->city->name : '';
        array_push($vloc, $city_name);
    } else { 
        echo $api_error; 
    }
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
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/visitor_data.css" rel="stylesheet" />
    <link href="../styles/jquery-ui.css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
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
            <th>User Browser</th>
            <th>Platform</th>
            <th>Time of Visit</th>
            <th>Page Visited</th>
            <th>IP Location</th>
            <th>Region</th>
            <th>Country</th>
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
                <td><?=$vloc[$k];?></td>
                <td><?=$vreg[$k];?></td>
                <td><?=$vcnt[$k];?></td>
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