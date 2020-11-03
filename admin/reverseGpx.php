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
$filetype = $_FILES['gpx2edit']['type'];
$filestat = $_FILES['gpx2edit']['error'];
$gpxfile = basename($_FILES['gpx2edit']['name']);

/**
 * Some of the following tests could be done by the client. These include the
 * tests for file name and size. That would mean that all failures here
 * could be reported as exceptions that display the generic user error page.
*/
if ($gpxfile === '') {
    $_SESSION['usr_alert'] = "Error: No file selected for upload.";
    header("Location: ../admin/admintools.php");
    exit;
} elseif ($filestat === UPLOAD_ERR_INI_SIZE || $filestat === UPLOAD_ERR_FORM_SIZE) {
    $_SESSION['usr_alert'] = "Error: The file is too large to upload.";
    header("Location: ../admin/admintools.php");
    exit;
} elseif ($filestat !== UPLOAD_ERR_OK) {
    $badupld = "Failed to upload {$gpxfile}: " . uploadErr($filestat);
    throw new Exception($badupld);
} elseif (substr_count($gpxfile, ".") !== 1) {
    $_SESSION['usr_alert'] = "Error: The file name must contain exactly " .
        "one period(.).";
    header("Location: ../admin/admintools.php");
    exit;
} else {
    $dot = strrpos($gpxfile, ".") + 1;
    $ext = strtolower(substr($gpxfile, $dot, 3));
    if ($ext !== 'gpx') {
        $_SESSION['usr_alert'] = "Error: The file name must end in .gpx or .GPX.";
        header("Location: ../admin/admintools.php");
        exit;
    }
}

libxml_use_internal_errors(true);
$dom = new DOMDocument;
if ($dom->load($_FILES['gpx2edit']['tmp_name'])) { // load GPX into DOM
    if ($dom->schemaValidate(                   // validate GPX against schema
        "http://www.topografix.com/GPX/1/1/gpx.xsd", LIBXML_SCHEMA_CREATE
        )
    ) {
        unset($_SESSION['usr_alert']);
        $dom->formatOutput = true;
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
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . "reversed.gpx" . "\"");
        echo $dom->saveXML();
        exit;
    } else {
        $_SESSION['usr_alert'] = "The uploaded file: {$gpxfile} " .
            "does not conform to the XML schema at:\n" .
            "http://www.topografix.com/GPX/1/1/gpx.xsd.\n" .
            "The first error is:\n" .
            displayXmlError(libxml_get_errors()[0]);
        header("Location: ../admin/admintools.php");
        exit;
    }
} else {
    $_SESSION['usr_alert'] = "The uploaded file: {$gpxfile} " .
        "is not a properly formatted XML file. The first error is:\n" .
        displayXmlError(libxml_get_errors()[0]);
    header("Location: ../admin/admintools.php");
    exit;
}
