<?php
/**
 * This module drops all tables listed in the $table array. Note that there
 * is a sensitivity to order for E-tables, since those have foreign keys.
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$table = array('USERS', 'HIKES', 'TSV', 'REFS', 'GPSDAT', 'IPTBLS', 'BOOKS',
    'ETSV', 'EREFS', 'EGPSDAT', 'EHIKES'); // NOTE: E-tables are order-sensitive
$tblcnt = count($table); // total number of database tables
if (isset($_REQUEST['no'])) {
    $qty = filter_input(INPUT_GET, 'no');
    if ($qty === 'all') {
        $action = 'Drop All Tables';
        $strt = 0;
    } else {
        $action = 'Drop All E-Tables';
        for ($j=0; $j<$tblcnt; $j++) {
            if (substr($table[$j], 0, 1) == 'E') {
                $strt = $j;
                break;
            }
        }
    }
} else {
    $action = 'Reload Database';
    $strt = 0;
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?= $action;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Drop (and Load if reqested) the specified Tables" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body {background-color: #eaeaea;}
        #progress { width: 420px; height: 36px; background-color: #ace600; }
        #bar { width: 0px; height: 36px; background-color: #aa0033; }
    </style>
    <script src="../scripts/jquery-1.12.1.js"></script>
</head>
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail"><?= $action;?></p>
<div style="margin-left:16px;font-size:18px;">
<?php
// Error messages:
$query_fail = "<p>Query did not succeed: SHOW TABLES</p>";
// Execute the DROP TABLE command for chosen tables:
for ($i=$strt; $i<$tblcnt; $i++) {
    echo "Dropping {$table[$i]}: ... ";
    $remtbl = mysqli_query($link, "DROP TABLE {$table[$i]};");
    if (!$remtbl) {
        echo"<p>drop_all_tables.php: Failed to drop {$table[$i]}: " .
            mysqli_error($link) . "</p>";
    } else {
        echo "Table Removed<br />";
    }
}
mysqli_close($link);
?>
<span style="color:brown;">DONE</span>
<?php if ($action == 'Reload Database') : ?>
<p id="trail">Loading Database</p>
<div style="margin-left:16px;">
<p>Please wait until the 'DONE' message appears below</p>
<div id="progress">
    <div id="bar"></div>
</div>
<script src="load_progress.js"></script>
<?php
    include 'loader.php';
?>
<p>DONE: Tables imported successfully</p>
</div>
<?php endif; ?>
</div>
</body>
</html>
