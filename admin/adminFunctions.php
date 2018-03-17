<?php
/**
 * This module contains a function (to be moved later) that
 * will reverse the designated track in a gpx file and output
 * a new file. Track numbers begin at 0.
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
 * @param string  $gpxfile file array of all lines in gpx file
 * @param integer $trkno   identifies the track number (from 0) to reverse
 * 
 * @return string $newgpx file with track reversed.
 */
function reverseTrack($gpxdata, $trkno)
{
    $curr = 0;
    $lines = count($gpxdata);
    $ptcoll = [];
    // Main loop through the gpxdata file looking for designated track
    $newfile = [];
    for ($i=0; $i<$lines; $i++) { // "A" : scan entire file
        if (strpos($gpxdata[$i], "<trk>")) { // "B" : starting a track?
            if ($trkno === $curr) { // "C" : designated track?
                // this is the track specified
                array_push($newfile, $gpxdata[$i]); // save '<trk>'
                $trkStrt = $i + 1;
                // search for <trkseg>
                for ($j=$trkStrt; $j<$lines; $j++) { // "D" : look for trkseg...
                    // is this the end of the track?
                    if (strpos($gpxdata[$j], "</trk>")) { //"E" : end of track?
                        $curr++;
                        break;
                    } else {
                        // may be numerous tags between <trk> and <trkseg>
                        array_push($newfile, $gpxdata[$j]);
                        if (strpos($gpxdata[$j], "<trkseg>")) { //  "F" : find trkseg
                            // ASSUMPTION: no intervening tags between <trkseg> and <trkpt>s
                            // trkseg found, process pts to end of track:
                            $ptStrt = $j + 1;
                            $normal = false; // if exits properly, will be true
                            for ($k=$ptStrt; $k<$lines; $k++) { // "G" : collect trkpts    
                                // gather trkpt "sets"
                                $ptset = [];
                                if (strpos($gpxdata[$k], "/>")) { // "H" : single or multiple?
                                    // single-line, self-closing
                                    array_push($ptset, $gpxdata[$k]);
                                } else {
                                    // multi-line trkpt:
                                    $setStrt = $k;
                                    $endpt = false;
                                    for ($set=$setStrt; $set<$lines; $set++) { // "I" :  collect
                                        array_push($ptset, $gpxdata[$set]);
                                        if (strpos($gpxdata[$set], "</trkpt>")) { // "J" : end?
                                            $endpt = true;
                                            break;
                                        } // "J" : end if /trkpt
                                    } // "I" : end for multi-line capture
                                    if (!$endpt) { // "K" : proper exit?
                                        $msg = htmlspecialchars(
                                            "No </trkpt> found;  line " . $set
                                        );
                                        die($msg);
                                    } // "K" : end if /trkpt found properly
                                    $k = $set;
                                } // "H" : end if-else capture trkpt data
                                array_push($ptcoll, $ptset); // look for more
                                if (strpos($gpxdata[$k+1], "</trkseg>")) { // "L"
                                    // Do reverse/write here
                                    $reverse = array_reverse($ptcoll);
                                    for ($r=0; $r<count($reverse); $r++) {
                                        foreach ($reverse[$r] as $trkset) {
                                            array_push($newfile, $trkset);
                                        }
                                    }
                                    $ptcoll = [];
                                    $normal = true;
                                    array_push($newfile, $gpxdata[$k+1]); // </trkseg>
                                    $endseg = $k + 1;
                                    break;
                                } // "L" : end if a trkseg closing
                            } // "G" : end of for $k, trkpt gathering
                            if (!$normal) { // "M" 
                                $msg = "Did not find end-of-trkseg; line " . ($k);
                                die($msg);
                            } // "M" : end if normal exit from k-loop
                            $j = $endseg;
                        } // "F" : end of if looking for trkseg
                    } // "E" : end if-else looking for /trk
                } //  "D" : end of $j loop looking for trkseg's
                $i = $j;
            } else { // end of designated track found
                $curr++; // not this particular track
            } // "C" : end if-else designated track
        } // "B" : end of if found trk
        array_push($newfile, $gpxdata[$i]);
    } // "A" : end of main loop stepping thru gpxdata
    return $newfile;
}
/**
 * This function supplies a message appropriate to the type of upload
 * error encountered.
 * 
 * @param integer $errdat The flag supplied by the upload error check
 * 
 * @return string 
 */
function uploadErr($errdat)
{
    if ($errdat === UPLOAD_ERR_INI_SIZE || $errdat === UPLOAD_ERR_FORM_SIZE) {
        return 'File is too large for upload';
    }
    if ($errdat === UPLOAD_ERR_PARTIAL) {
        return 'The file was only partially uploaded (no further information';
    }
    if ($errdat === UPLOAD_ERR_NO_FILE) {
        return 'The file was not uploaded (no further information';
    }
    if ($errdat === UPLOAD_ERR_CANT_WRITE) {
        return 'Failed to write file to disk';
    }
    if ($errdat === UPLOAD_ERR_EXTENSION) {
        return 'A PHP extension stopped the upload';
    }
}