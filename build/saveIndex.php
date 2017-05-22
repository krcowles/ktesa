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
    for ($j=0; $j<41; $j++) {
        $indx[$j] = '';
    }
    # NOTE: when retrieving an array, all the values/checks/etc are listed first, out-of-order
    $import = $_POST['indx'];
    $indx[0] = $import[0];
    $indx[1] = $import[1];
    $indx[2] = $import[2];
    $indx[3] = $import[3];
    $indx[10] = $import[4];
    $indx[11] = $import[5];
    $indx[19] = $import[6];
    $indx[20] = $import[7];
    $indx[21] = $import[8];
    $mapName = $import[9];
    $indx[25] = $import[10];
    $indx[38] = $import[11];
    $indx[39] = $import[12];
    ksort($indx, SORT_NUMERIC);
    echo '<p style="margin:16px;">Name for this center is: ' . $indx[1] . '</p>';
    $user = true;
    $dupFile = filter_input(INPUT_POST,'pmapdup');
    $owFile = filter_input(INPUT_POST,'pmapow');
    # Estabish directory locations
    $cwd = getcwd();
    $basedirlgth = strpos($cwd,"/build");
    $basedir = substr($cwd,0,$basedirlgth);
    $uploads = '/tmp/';
    $oldLoc = $cwd . $uploads . 'images/' . $mapName;
    if (filter_input(INPUT_POST,'savePg') === 'Site Master') {
        $passwd = filter_input(INPUT_POST,'mpass');
        if ($passwd !== '000ktesa') {
            die('<p style="color:brown;">Incorrect Password - save not executed</p>');
        }
        $user = false;
        $delmsg1 = '<p style="color:brown;">';
        $delmsg2 = " is being deleted because it was detected as a duplicate " .
            "file, and was not designated to overwrite the existing file.</p>";
        if ($dupFile === 'NO' || ($dupFile === 'YES' && $owFile === 'YES')) {
            $newLoc = $basedir . 'images/' . $mapName;
            if (!rename($oldLoc,$newLoc)) {
                die('<p style="color:brown;">COULD NOT MOVE PARK MAP FILE</p>');
            } else {
                echo '<p style="color:brown;font-size:18px;">Successfully moved park map file to site</p>';
            }
        } elseif ($dupFile === 'YES' && $owFile === 'NO') {
            echo $delmag1 . $mapName . $delmsg2;
            unlink($oldLoc);
        }
        $database = '../data/database.csv';   
    } else if (filter_input(INPUT_POST,'savePg') === 'Submit for Review') {
        # NOT UPDATED FOR VISITOR CENTER HIKES - need process
        $database = '../data/reviewdat.csv';
    } else {
        die('<p style="color:brown;">Contact Site Master: Submission not recognized');
    } 
    # PLEASE CHECK THIS LATER!!! IS LAST LINE EOF !== newline?????
    if ( ($handle = fopen($database,"a")) !== false ) {
        fputs($handle,"\n");
        fputcsv($handle,$indx);
        fclose($handle);
    } else {
        die ("COULD NOT OPEN DATABASE - Contact site master");
    }
    if ($user) {
        echo '<h2>Index Page Data saved for review - you will be notified when ' .  
            'the data has been accepted and posted.</h2>';
    } else {
        echo '<div data-ptype="index" data-indxno="' . $indx[0] . '" style="padding:16px;" id="more">';
	echo '<button style="font-size:16px;color:DarkBlue;" id="same">Edit this Index Page</button><br />';
	echo '<button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different Index Page</button><br />';
	echo '<button style="font-size:16px;color:DarkBlue;" id="view">View this completed page</button>';
        echo '</div>';
    }  
?>
<p id="trail"><?php echo $indx[1];?></p>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="postEdit.js"></script>

</body>

</html>
	