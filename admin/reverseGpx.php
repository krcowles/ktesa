<?php
/**
 * This module will reverse the order of trackpoints within a given
 * track no (or all tracks) of the input file. If there are multiple segments
 * within a track, each segment within the track will have its trackpts 
 * reversed independently. Elevation data is not required here.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$revtype = filter_input(INPUT_POST, 'revtype');
$revlist = filter_input(INPUT_POST, 'revlist');

unset($_SESSION['alerts']); // start clean
if (!empty($_FILES['gpx2edit']['name'])) {
    $upload = uploadFile(prepareUpload('gpx2edit'));
    if ($upload === 'none') {
        header("Location: admintools.php", true); // display any alerts
        exit;
    }
} else {
    // nothing to do here
    header("Location: admintools.php");
    exit;
}

$dom = new DOMDocument();
$dom->formatOutput = true;
$dom->load($upload);
$tracks = $dom->getElementsByTagName('trk'); // DONMNodeList object
$trkcnt = $tracks->length;
unlink("gpx2edit.gpx");
// process user input to determine tracks to be iteratively reversed
$tracklist = [];
if ($revtype === 'gpxall') {
    for ($i=0; $i<$trkcnt; $i++) {
        $tracklist[$i] = $i;
    }
} elseif ($revtype === 'gpxsgl') {
    $noWhiteList = preg_replace('/\s+/', '', $revlist);
    $trkels = explode(",", $noWhiteList);
    foreach ($trkels as $member) {
        if (strpos($member, "-") !== false) {
            $range = explode("-", $member);
            $start = array_shift($range);
            $end = array_shift($range);
            if (!is_numeric($start) || !is_numeric($end)) {
                throw new Exception(
                    "Bad range, non-numeric element: " . $start . "-" . $end
                );
            }
            if ($start >= $end) {
                throw new Exception(
                    "Range limits are incorrect: " . $start . "-" . $end
                );
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
