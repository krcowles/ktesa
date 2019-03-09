<?php
/**
 * This module generates summary statistics for all the gpx files in 
 * the gpx/test directory.  
 * Variables expected to be defined prior to invocation: 
 *    string  $gpxPath, relative url to the gpx file;
 *    integer $hikeNo, unique hike id
 * PHP Version 7.0
 * 
 * @category Not_Sure_What
 * @package  GPSV_Mapping
 * @author   Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license  None at this time
 * @link     ../php
 */
require "../build/buildFunctions.php";
// Error messaging
$intro = '<p style="color:red;left-margin:12px;font-size:18px;">';
$close = '</p>';
$gpxmsg = $intro . 'Mdl: makeGpsv.php - Could not parse XML in gpx file: ';

// Open debug files
$tmpFilename = sys_get_temp_dir() . "/DebugFile.csv";
if (file_exists($tmpFilename)) {
    unlink($tmpFilename);
}
if (($debugFile = fopen("{$tmpFilename}", "w")) === false) {
    $dbfMsg = "Could not open {$tmpFilename} in file: " . 
    __File__ . " at line: " . __Line__;
    throw new Exception($dbfMsg);
}
fputs($debugFile, "name,length,MaxEl,MinEl,ElChg,Asc,Dsc,maW,dTh,eTh" . PHP_EOL);

// Generate summary stats for each file in gpx/test directory 
$dir_iterator = new RecursiveDirectoryIterator(
    "../gpx/test/", RecursiveDirectoryIterator::SKIP_DOTS
);
$iterator = new RecursiveIteratorIterator(
    $dir_iterator, RecursiveIteratorIterator::SELF_FIRST
);
foreach ($iterator as $file) { // each gpx file
    if ($file->isFile()) {
        $gpxPath = $iterator->getPathName();
        echo $gpxPath . "</br>";
        flush();
        ob_flush();
    } else {
        echo "Ooops " . $iterator->getPathName() . "</br>";
        break;
    }
    $gpxdat = simplexml_load_file($gpxPath);
    if ($gpxdat === false) {
        if ($gpxPath == '') {
            $filemsg = "Empty GPX Path String encountered";
        } else {
            $filemsg = $gpxPath;
        }
        throw new Exception($gpxmsg . $filemsg . $close);
    }

    /**
     * In case the file is constructed using 'rtept' tags instead of
     * 'trkpt' tags, convert to trkpts:
     */
    if ($gpxdat->rte->count() > 0) {
        $gpxdat = convertRtePts($gpxdat);
    }

    /**
     * Process the data for each gpx file multiple times, each time using different
     * values for the various distance and elevation smoothing parameters
     */
    for ($maT=1; $maT<16; $maT+=2) { // Moving average window
        $maWindow = $maT;

        for ($dT=0; $dT<8; $dT++) { // distance threshold
            $distThresh = $dT; //5.0;

            for ($eT=0; $eT<5; $eT++) { // elevation threshold
                $elevThresh = $eT; //1.0;

                // variables for accumulation calcs
                $pup = (float)0;
                $pdwn = (float)0;
                $pmax = (float)0;
                $pmin = (float)50000;
                $hikeLgthTot = (float)0;
                for ($k=0; $k<$gpxdat->trk->count(); $k++) { // PROCESS EACH TRK
                    $hikeLgth = (float)0;

                    /**
                     * Get gpx data into individual arrays and do first level
                     * processing.
                     */
                    // Declare arrays - unset first
                    unset(
                        $gpxlats, $gpxlons, $gpxeles, $gpxtimes, $eleChg, $distance,
                        $grade, $speed
                    );
                    $gpxlats = [];
                    $gpxlons = [];
                    $gpxeles = [];
                    $gpxtimes = [];
                    $eleChg = [];
                    $distance = [];
                    $grade = [];
                    $speed = [];
                
                    // Read data for trk k into arrays and do Level 1 calcs
                    getGpxL1(
                        $gpxdat, $k, $gpxlats, $gpxlons, $gpxeles, $gpxtimes,
                        $eleChg, $distance, $grade, $speed
                    );
                        
                    // Do moving average smoothing on elevation values
                    $gpxeles = moveAvg($gpxeles, $maWindow, $gpxPath, false);

                    // Start computing statistics for trk k
                    // Process first trkpt in current trk
                    $trkptStrtIdx = 0;
                    if ($k == 0) { // Special setup for very first trkpt
                        $trkptStrtIdx = 1;
                        $prevLat = $gpxlats[0];
                        $prevLon = $gpxlons[0];
                        $prevEle = $gpxeles[0];
                    } // end if: Special setup for very first trkpt

                    // Compute stats for remaining trkpts in trk k

                    //Do distance and elevation calcs for this trkpt
                    for ($m=$trkptStrtIdx; $m<count($gpxlats); $m++) {
                        $rotation = distElevCalc(
                            $k, $m, $gpxlats, $gpxlons, $gpxeles,
                            $distThresh, $elevThresh,
                            $pmax, $pmin, $pup, $pdwn, $hikeLgth, $hikeLgthMiles,
                            $prevLat, $prevLon, $prevEle
                        );
                    }  // end for: Compute stats for remaining trkpts in trk k
                    $hikeLgthTot += $hikeLgth;
                } // end for: PROCESS EACH TRK

                // Compute summary statistics
                $pmaxFeet = round($pmax * 3.28084, 2);
                $pminFeet = round($pmin * 3.28084, 2);
                $pup = round(3.28084 * $pup, 0);
                $pdwn = round(3.28084 * $pdwn, 0);

                // Do debug output (summary stats for entire hike)
                fputs(
                    $debugFile,
                    "{$iterator->getFileName()}," .
                    sprintf("%.2f,", $hikeLgthTot / 1609) .
                    sprintf("%.0f,", $pmaxFeet) .
                    sprintf("%.0f,", $pminFeet) .
                    sprintf("%.0f,", $pmaxFeet - $pminFeet) .
                    "{$pup},{$pdwn}," .
                    "{$maWindow},{$distThresh},{$elevThresh}," . PHP_EOL
                );
            } // end for eT (Elevation Threshold)
        } // end for dT (Distance Threshold)
    } // end for maT (Moving Average Window)
} // end foreach: each gpx file
fclose($debugFile);