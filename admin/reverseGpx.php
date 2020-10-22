<?php
/**
 * This module will reverse the order of trackpoints within a given
 * track no of the input file. If there are multiple segments within
 * a track, each segment within the track will have its trackpts 
 * reversed independently.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$adminpg = '../admin/admintools.php';
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
        throw new Exception($badupld);
    }
    if (substr_count($gpxfile, ".") !== 1) {
        $odd = "This file may be corrupted. Please correct the " .
            "file format and re-submit, or contact Site Master.";
        throw new Exception($odd);
    }
    $dot = strrpos($gpxfile, ".") + 1;
    $ext = strtolower(substr($gpxfile, $dot, 3));
    if ($ext !== 'gpx') {
        $badext = "This file appears to have an incompatible extension type, " .
            "{$ext}; No edits made";
        throw new Exception($badext);
    }
} else {
    throw new Exception("No file specified");
}
$editfile = $_FILES[$name]['tmp_name'];
libxml_use_internal_errors(true);
$adminpg = "../admin/admintools.php";
validateGpxFile($editfile, $gpxfile);
if (isset($_SESSION['usr_alert'])) {
    header("Location: " . $adminpg);
    exit;
}
validateGpxSchema($editfile, $gpxfile);
if (isset($_SESSION['usr_alert'])) {
    header("Location: " . $adminpg);
    exit;
}
$dom = new DOMDocument();
$dom->formatOutput = true;

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
                throw new Exception("Bad range, non-numeric element: " . $start . "-" . $end);
            }
            if ($start >= $end) {
                throw new Exception("Range limits are incorrect: " . $start . "-" . $end);
            }
            for ($j=$start; $j<$end; $j++) {
                if ($j > $trkcnt) {
                    throw new Exception(
                        "Range exceeded number of tracks in file: " .
                        $start . "-" . $end . " > " . $trkcnt
                    );
                }
                array_push($tracklist, ($j-1));
            }
        } else {
            if (!is_numeric($member)) {
                throw new Exception("Found non-number item in range: " . $member);
            }
            if ($member > $trkcnt) {
                throw new Exception(
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
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . "reversed.gpx" . "\"");
echo $downloadStr;
exit;
