<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Save Index Page</title>
    <meta charset="utf-8" />
    <meta name="description" content="Write hike data to TblDB.csv" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/hikes.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>

<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>

<div style="margin-left:12px;padding:8px;">
<?php
    $indxPgTitle = filter_input(INPUT_POST, 'ptitle');
    echo '<p style="color:brown;font-size:20px;">Name for this center is: ' . $indxPgTitle . '</p>';
    $newPgNo = filter_input(INPUT_POST, 'newno');
    $user = true;
    $dupFile = filter_input(INPUT_POST, 'pmapdup');
    $owFile = filter_input(INPUT_POST, 'pmapow');
    $mapName = filter_input(INPUT_POST, 'pkmap');
    # Estabish directory locations
    $cwd = getcwd();
    $basedirlgth = strpos($cwd, "/build");
    $basedir = substr($cwd, 0, $basedirlgth);
    $uploads = '/tmp/';
    $oldLoc = $cwd . $uploads . 'images/' . $mapName;
if (filter_input(INPUT_POST, 'savePg') === 'Site Master') {
    $passwd = filter_input(INPUT_POST, 'mpass');
    if ($passwd !== '000ktesa') {
        die('<p style="color:brown;">Incorrect Password - save not executed</p>');
    }
    $user = false;
    $delmsg1 = '<p style="color:brown;">';
    $delmsg2 = " is being deleted because it was detected as a duplicate " .
        "file, and was not designated to overwrite the existing file.</p>";
    if ($dupFile === 'NO' || ($dupFile === 'YES' && $owFile === 'YES')) {
        $newLoc = $basedir . 'images/' . $mapName;
        if (!rename($oldLoc, $newLoc)) {
            die('<p style="color:brown;">COULD NOT MOVE PARK MAP FILE</p>');
        } else {
            echo '<p style="color:brown;font-size:18px;">Successfully moved park map file to site</p>';
        }
    } elseif ($dupFile === 'YES' && $owFile === 'NO') {
        echo $delmsg1 . $mapName . $delmsg2;
        unlink($oldLoc);
    }
    # Now unlock the new page:
    $xmlDB = simplexml_load_file('../data/database.xml');
    if ($xmlDB === false) {
        $emsg = '<p style="color:brown;font-size:18px;>Could not open xml '
                .'database to unlock new pg ' . $indxPgTitle . '</p>';
        die($emsg);
    }
    foreach ($xmlDB->row as $row) {
        if ($row->indxNo == $newPgNo) {
            $row->pgTitle = $indxPgTitle;
            $row->rlock = '';
            break;
        }
    }
    echo '<p style="color:brown;font-size:18px;">This hike is now ' .
    '"site-ready" and will appear on the map and table of hikes</p>';
} elseif (filter_input(INPUT_POST, 'savePg') === 'Submit for Review') {
    # NOT UPDATED FOR VISITOR CENTER HIKES - need process
} else {
    die('<p style="color:brown;">Contact Site Master: Submission not recognized');
}
if ($user) {
    echo '<h2>Index Page Data saved for review - you will be notified when ' .
        'the data has been accepted and posted.</h2>';
} else {
    echo '<div data-ptype="index" data-indxno="' . $newPgNo . '" style="padding:16px;" id="more">';
    echo '<button style="font-size:16px;color:DarkBlue;" id="same">Edit this Index Page</button><br />';
    echo '<button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different Index Page</button><br />';
    echo '<button style="font-size:16px;color:DarkBlue;" id="view">View this completed page</button>';
    echo '</div>';
}
?>
<p id="trail"><?php echo $indxPgTitle;?></p>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="postEdit.js"></script>

</body>

</html>
	
