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
foreach ($db->row as $hikeLine) {
    if ($hikeLine->indxNo == $hikeNo) {
        $indxName = filter_input(INPUT_POST,'hname');
        $hikeLine->pgTitle = $indxName;
        $hikeLine->locale = filter_input(INPUT_POST,'locale');
        $hikeLine->lat = filter_input(INPUT_POST,'hlat');
        $hikeLine->lng = filter_input(INPUT_POST,'hlon');
        $hikeLine->dirs = filter_input(INPUT_POST,'gdirs');
        $hikeLine->hikeInfo = filter_input(INPUT_POST,'info');
        include "refEdits.php";
        break;
    }
}
?>
<p id="trail"><?php echo $indxName;?></p>
<?php
$user = true;
/* WRITE OUT THE NEW INDEX PAGE */
$lead = '<p style="color:brown;font-size:18px;">' .
        'The index page changes for ' . $indxName . ' (if any) ';
$msgsave = 'have been made to the site</p>';
$tmpsave = 'are saved temporarily and can be re-edited at any time; ' .
        'To save permanently, re-edit, complete the changes, and submit</p>';
$submitted = filter_input(INPUT_POST,'savePg');
if ($submitted === 'Site Master') {
    $passwd = filter_input(INPUT_POST,'mpass');
    if ($passwd !== '000ktesa') {
        die('<p style="color:brown;">Incorrect Password - save not executed</p>');
    }
    $user = false;
    $hikeLine->rlock = '';
    $db->asXML($database);
    $msgout = $lead . $msgsave;
} elseif ($submitted === 'Submit for Review') {
    $msgout = '<p>Your changes for ' . $indxName . 
            ' have been submitted for review by the site master.</p>';
} elseif ($submitted === 'Save for Re-edit') {
    $hikeLine->rlock = 'Edit';
    $db->asXML($database);
    $msgout = $lead . $tmpsave;
} else {
    $emsg = '<p style="color:red;font-size:20px;margin-left:16px;">' .
           'SUBMISSION NOT RECOGNIZED: Contact Site Master</p>';
    die ($emsg);
}
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