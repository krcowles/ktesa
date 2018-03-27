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
//$reversedFile = '../gpx/reversed.gpx';
/**
 *  Validate the upload
 */
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
$dom = new DOMDocument();
$dom->formatOutput = true;
$dom->load($editfile);
if (!$dom) {
    die("Could not retrieve uploaded gpx file and convert to DOM document");
}
// END FILE VALIDATION
$tracks = $dom->getElementsByTagName('trk'); // DONMNodeList object
$trkcnt = $tracks->length;
// process user input to determine tracks to be iteratively reversed
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
        if (strpos($member, "-") !== false) {
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
// Iterate through all selected tracks
foreach ($tracklist as $trkno) {
    reverseTrack($tracks, $trkno);
    unset($tracks);
    $tracks = $dom->getElementsByTagName('trk'); // DONMNodeList object
}
$downloadStr = $dom->saveXML();
// escape quotes for javascript string:
$jsvar = str_replace("'", "\'", $downloadStr);
$jsvar = str_replace('"', '\"', $jsvar);
