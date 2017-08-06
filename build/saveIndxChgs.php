<!DOCTYPE html>
<html lang="en-us">
    
<head>
    <title>Save Changes to Database</title>
    <meta charset="utf-8" />
    <meta name="description" content="Edit a given hike" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="../styles/hikes.css" type="text/css" rel="stylesheet" />
</head>

<body>

<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>

    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
    
<?php
$database = '../data/database.xml';
$db = simplexml_load_file($database);
if ($db === false) {
    $emsg = '<p style="color:red;font-size:20px;margin-left:16px;>' .
            'Could not open xml database: contact site master</p>';
    die($emsg);
}
$hikeNo = filter_input(INPUT_POST,'hno');
$indxName = filter_input(INPUT_POST,'hname');
$indxLocale = filter_input(INPUT_POST,'locale');
$indxLat = filter_input(INPUT_POST,'hlat');
$indxLng = filter_input(INPUT_POST,'hlon');
$indxDirs = filter_input(INPUT_POST,'gdirs');
$indxInfo = filter_input(INPUT_POST,'info');
include "refEdits.php";
?>
<p id="trail"><?php echo $indxName;?></p>
<?php
$user = true;
if (filter_input(INPUT_POST,'savePg') === 'Site Master') {
    $passwd = filter_input(INPUT_POST,'mpass');
    if ($passwd !== '000ktesa') {
        die('<p style="color:brown;">Incorrect Password - save not executed</p>');
    }
    $user = false;
    /* WRITE OUT THE NEW INDEX PAGE */
    $msgout = '<p>The index page changes for ' . $info[1] . ' (if any)' .
        'have been made to the site</p>';
    
} else if (filter_input(INPUT_POST,'savePg') === 'Submit for Review') {
    $userchgs = '../data/reviewdat.csv';
    $dbhandle = fopen($userchgs,"a");
    fputcsv($dbhandle,$info);
    $msgout = '<p>Your changes for ' . $info[1] . 
            ' have been submitted for review by the site master.</p>';
} else {
    die('<p style="color:brown;">Contact Site Master: Submission not recognized');
} 

fclose($dbhandle);
?>
<div style="margin-left:16px;">
    <?php echo $msgout;?>
</div>
<?php
if (!user) {
    echo '<div data-ptype="index" data-indxno="' . $hikeNo . '" style="padding:16px;" id="more">';
    echo '<button style="font-size:16px;color:DarkBlue;" id="same">Re-edit this Index Page</button><br />';
    echo '<button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different Index Page</button><br />';
    echo '<button style="font-size:16px;color:DarkBlue;" id="view">View Changed Page</button>';
    echo '</div>';
}
?>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="postEdit.js"></script>


</body>

</html>