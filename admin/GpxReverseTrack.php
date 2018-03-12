<?php
/**
 * This module contains a function (to be moved later) that
 * will reverse the argument's track in a gpx file and output
 * a new file.
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
/**
 * This function accepts a path to a gpx file and a track number 
 * within that file to reverse. If there are multiple segments within
 * the subject track, the segments will remain in order, but the data
 * in each segment will be reversed.
 * 
 * @param string  $gpxfile Path to the subject gpx file
 * @param integer $trkno   Identifies the track number (from 0) to reverse
 * 
 * @return string new file with track reversed.
 */
function reverseTrack($gpxfile, $trkno)
{
    $currTrk = 0;
    $rawdata = file($gpxfile);
    $lines = count($rawdata);
    if (!$rawdata) {
        die(__FILE__ . " Line " . __LINE__ . "Unable to load gpxfile");
    }
    // form newfile same as gpxfile until desired track is encountered
    $leadin = [];
    for ($i=0; $i<$lines; $i++) {
        if (strpos($rawdata[$i], "<trk>")) {
            if ($trkno === $currTrk) {
                break;
            } else {
                $currTrk++;
                $trkstart = $i + 1;
            }
        } else {
            array_push($leadin, $rawdata[$i]);
        }
    }
    // get to the trkpt data, in case of extensions, name, color, etc.
    for ($j=$trkstart; $j<$lines; $j++) {
        if (strpos($rawdata[$j], "trkseg")) {
            $segStrt = $j + 1;
            break;
        } else {
            array_push($leadin, $rawdata[$j]);
        }
    }
    // form array of trackpts to be reversed:
    $pts = [];
    for ($k=$segStrt; $k<$lines; $k++) {
        if (strpos($rawdata[$k], "/trkseg")) {
            break;
        } else {
            // keep internal data unsorted
            if (strpos($rawdata[$k], "trk")) {
                
            }
        }
    }
    
    echo $newfile;
    // add in the track info:
    /*
    $sxi = new simpleXMLIterator($gpxfile, null, true);
    $sxi->rewind();
    for ($sxi->rewind(); $sxi->valid(); $sxi->next()) {
        if ($sxi->key() === 'trk') {
            foreach ($sxi->getChildren() as $trkobj) {
                if ($trkobj->count() > 0) { // metadata et al not needed
                    foreach($trkobj as $tpt) {
                        
                    }
                }
            }
        }
    }
    */
}
reverseTrack('../gpx/BigTubes.GPX', 0);
