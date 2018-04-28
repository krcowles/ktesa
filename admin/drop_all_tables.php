<?php
/**
 * This module drops all tables listed in the $tables array. That array
 * is established using the current list of tables in the db, but placing
 * EHIKES last as it is the parent for multiple foreign keys.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$tables = array();
$tbl_list = mysqli_query($link, "SHOW TABLES;") or die(
    __FILE__ . " Line " . __LINE__ . "Failed to get list of tables: "
    . mysqli_error($link)
);
while ($row = mysqli_fetch_row($tbl_list)) {
    if ($row[0] !== 'EHIKES') {
        array_push($tables, $row[0]);
    }
}
array_push($tables, 'EHIKES');
$tblcnt = count($tables); // total number of database tables
if (isset($_REQUEST['no'])) {
    $action = 'Drop All Tables';
} else {
    $action = "Reload Database";
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?= $action;?></title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Drop (and Load if reqested) the specified Tables" />
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
for ($i=0; $i<$tblcnt; $i++) {
    echo "Dropping {$tables[$i]}: ... ";
    $remtbl = mysqli_query($link, "DROP TABLE {$tables[$i]};");
    if (!$remtbl) {
        echo"<p>drop_all_tables.php: Failed to drop {$tables[$i]}: " .
            mysqli_error($link) . "</p>";
    } else {
        echo "Table Removed<br />";
    }
}
mysqli_close($link);
?>
<span style="color:brown;">DONE</span>
<?php if ($action == 'Reload Database') : ?>
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
