<?php
/**
 * This module will reverse the order of trackpoints within a given
 * track no of the input file. If there are multiple segments within
 * a track, each segment within the track will have its trackpts 
 * reversed independently.
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
require "adminFunctions.php";
// validate file info:
$name = "gpx2edit";
$gpxfile = basename($_FILES[$name]['name']);
if ($gpxfile !== '') {
    $filetype = $_FILES[$name]['type'];
    $filestat = $_FILES[$name]['error'];
    if ($filestat !== UPLOAD_ERR_OK) {
        $badupld = "Failed to upload {$gpxfile}: " . uploadErr($filestat);
        die($badupld);
    }
    if (substr_count($gpxfile, ".") !== 1) {
        $odd = "This file may be corrupted. Please correct the " .
            "file format and re-submit, or contact Site Master.";
        die($odd);
    }
    $dot = strrpos($gpxfile, ".") + 1;
    $ext = strtolower(substr($gpxfile, $dot, 3));
    if ($ext !== 'gpx') {
        $badext = "This file appears to have an incompatible extension type, " .
            "{$ext}; No edits made";
        die($badext);
    }
    $mimetype = "/octet-stream/";
    if (preg_match($mimetype, $filetype) === 0) {
        $badmime = $gpxfile . "has file type: " . $filetype . 
            ": should be {$mimetype}";
        die($badmime);
    }
} else {
    die("No file specified");
}
$editfile = $_FILES[$name]['tmp_name'];
$gpx = file($editfile, FILE_SKIP_EMPTY_LINES);
if (!$gpx) {
    die("Could not retrieve uploaded gpx file");
}
$trkcnt = 0;
foreach ($gpx as $line) {
    if (strpos($line, "<trk>") !== FALSE) {
        $trkcnt++;
    }
}
echo "<br />No of tracks is " . $trkcnt . "<br />";
// form array of tracknos
$tracklist = [];
if (isset($_POST['gpxall'])) {
    for ($i=0; $i<$trkcnt; $i++) {
        $tracklist[$i] = $i;
    }
} elseif (isset($_POST['gpxlst'])) {
    $revlist = filter_input(INPUT_POST, 'revlst');
    $noWhiteList = preg_replace('/\s+/', '', $revlist);
    $trkels = explode(",", $noWhiteList);
    foreach ($trkels as $member) {
        if (strpos($member, "-") !== FALSE) {
            $range = explode("-", $member);
            $start = array_shift($range);
            $end = array_shift($range);
            if (!is_numeric($start) || !is_numeric($end)) {
                die("Bad range, non-numeric element: " . $start . "-" . $end);
            }
            if ($start >= $end) {
                die("Range limits are incorrect: " . $start . "-" . $end);
            }
            for ($j=$start; $j<$end; $j++) {
                if ($j > $trkcnt) {
                    die(
                        "Range exceeded number of tracks in file: " .
                        $start . "-" . $end . " > " . $trkcnt
                    );
                }
                array_push($tracklist, ($j-1));
            }
        } else {
            if (!is_numeric($member)) {
                die("Found non-number item in range: " . $member);
            }
            if ($member > $trkcnt) {
                die(
                    "Track number exceeded number of tracks in file: " .
                    $member . " > " . $trkcnt
                );
            }
            array_push($tracklist, $member-1);
        }
    }
}
$newfile = "../gpx/reversed.gpx";
foreach ($tracklist as $track) {
    $edited = reverseTrack($gpx, $track);
    file_put_contents($newfile, $edited);
    $gpx = file($newfile);
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Site Admin Tools</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Site Administration Tools</p>
<div style="margin-left:24px;font-size:18px;">
    <p>DONE!</p>
    <p>File with reversed track(s) is stored on site as ../gpx/reversed.gpx</p>
</div>
</body>
</html>
