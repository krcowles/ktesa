<?php
/**
 * This page allows the admin to select a time range for which to 
 * display visitor data.
 * PHP Version 7.8
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$avail_yrs = '<option value="0">Select Year</option>';
$firstyr = 2021;
$current = intval(date("Y"));
// Selectable months for years past
while (($current - $firstyr) >= 0) {
    $avail_yrs .= '<option value="' . $current . '">' . $current .
        '</option>';
    $current--;
}
$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July',
    'August', 'September', 'October', 'November', 'December');
$longmos = '<select id="yr1">';
for ($i=0; $i<12; $i++) {
    if ($months[$i] === 'January') {
        $longmos .= '<option value="1" selected>January</option>';
    } else {
        $longmos .= '<option value="' . ($i+1) . '">' . $months[$i] . '</option>';
    }
}
$longmos .= '</select>';
// Selectable months for current year
$currmo = intval(date('m'));
$shortmos = [];
for ($i=1; $i<13; $i++) {
    if ($currmo >= $i) {
        array_push($shortmos, $months[$i-1]);
    }
}
$currmos = '<select id="yr1">';
for ($j=0; $j<count($shortmos); $j++) {
    if ($shortmos[$j] === 'January') {
        $currmos .= '<option value="1" selected>January</option>';
    } else {
        $currmos .= '<option value="' . ($j+1) . '">' . $shortmos[$j] . '</option>';
    }
}
$currmos .= '</select>';
// Minimum days in a month 
$modays = '<select id="sgldays">';
for ($k=1; $k<=28; $k++) {
    $modays .= '<option value="' . $k . '">' . $k . '</option>';
}
$modays .= '</select>';
$curryr = date('Y');
$onemo = '&nbsp;&nbsp;<button id="onemo" type="button" class="btn ' .
    'btn-secondary btn-sm">Display Month</button>';
// day extensions
$addone = '<option value="29">29</option>';
$addtwo = $addone . '<option value="30">30</option>';
$addmax = $addtwo . '<option value="31">31</option>';
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
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <link href="../styles/visitor_data.css" rel="stylesheet" />
    <link href="../styles/jquery-ui.css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Display Selected Visitor Data</p>
<p id="active" style="display:none">Admin</p>

<div id="main">
    <p id="curr_yr" style="display:none"><?=$curryr;?></p>
    <h5>Select a day or month to display visitor data</h5>
    <div>
        <select id="strt_yr">
            <?=$avail_yrs;?>
        </select><br />
        <div id="opts">
            <h5>For a single month in the selected year:</h5>
            <p id="sglmo"></p>
            <h5>For a single day in the month selected above:</h5>
            <p id="sglday"><?=$modays;?>&nbsp;&nbsp;
                <button id="oneday" class="btn btn-secondary btn-sm">
                    Display Day</button>
            </p>
        </div><br />
        <div>
            <h5>Or, If you wish to specify a date range to display, do it here:</h5>
            <div>
                <span id="rg">Start:&nbsp;&nbsp;<input id="begin" type="text" 
                placeholder="Click to select" />
                &nbsp;&nbsp;End:&nbsp;&nbsp;<input id="end" type="text" 
                placeholder="Click to select" /></span>
                &nbsp;&nbsp;<button id="range" type="button"
                    class="btn btn-secondary btn-sm">Display Range</button>
            </div>
        </div>
    </div>
</div><br /><br/>

<script type="text/javascript">
    var longmos  = '<?=$longmos;?>';
    var shortmos = '<?=$currmos;?>';
    var onemnth  = '<?=$onemo;?>';
    var mindays  = '<?=$modays;?>';
    var addone   = '<?=$addone;?>';
    var addtwo   = '<?=$addtwo;?>';
    var addmax   = '<?=$addmax;?>';
</script>
<script src="visitor_data.js"></script>
</body>
</html>