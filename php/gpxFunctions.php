<?php
/**
 * This module contains functions used both during build and when creating
 * GPSV maps.
 * PHP Version 1.1
 * 
 * @package GPSV_Mapping
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No License to date
 */
/**
 * This function will output a file, identified with an extension of
 * "_DebugArray.csv", with the header row populated. The file is written
 * to the system tmp directory. The function is only invoked when the url
 * query string specifies "&makeGpsvDebug=true". A pointer to the file is returned.
 *
 * @param string $gpxPath relative path to the gpx file being processed
 *  
 * @return file  $fileArrayHandle file pointer to debug file w/headers
 */
function gpsvDebugFileArray($gpxPath)
{
    $tmpFilename = sys_get_temp_dir() . "/" . basename($gpxPath) 
        . "_DebugArray.csv";
    if (file_exists($tmpFilename)) {
        unlink($tmpFilename);
    }
    if (($fileArrayHandle = fopen("{$tmpFilename}", "w")) === false) {
        $dbfMsg = "Could not open {$gpxPath}_DebugArray.csv in file: " . 
        __File__ . " at line: " . __Line__;
        die($dbfMsg);
    }
    fputs(
        $fileArrayHandle, "trk,seg,n,Lat,Lon,EleM,gpxtimes," .
        "eleChg,timeChg,distance,grade,speed" . PHP_EOL
    );
    return $fileArrayHandle;
}
/**
 * This function will output a file, identified with an extension of 
 * "_DebugCompute.csv", with the header row populated. The file is written 
 * to the system tmp directory. The function is only invoked when the url 
 * query string specifies "&makeGpsvDebug=true". A pointer to the file is returned.
 * 
 * @param string $gpxPath relative path to the gpx file being processed
 * 
 * @return file  $debugComputeArray file pointer to debug file created w/headers
 */
function gpsvDebugComputeArray($gpxPath)
{
    $tmpFilename = sys_get_temp_dir() . "/" . basename($gpxPath) 
        . "_DebugCompute.csv";
    if (file_exists($tmpFilename)) {
        unlink($tmpFilename);
    }
    if (($debugComputeHandle = fopen("{$tmpFilename}", "w")) === false) {
        $dbfMsg = "Could not open {$gpxPath}_DebugCompute.csv in file: " . 
        __File__ . " at line: " . __Line__;
        die($dbfMsg);
    }
    fputs(
        $debugComputeHandle,
        "trk,trkpt,Lat,Lon,EleM,elevChg,dist,eFlg,dFlg,grade,hikeLgth" .
        ",hikeLgthMiles,pup,pdwn" . PHP_EOL
    );
    return $debugComputeHandle;
}
/**
 * This function will output a file, identified with an extension of
 * "_DebugCompute.csv" with the header row populated. The file is written
 * to the system tmp directory. The function is only invoked when the url
 * query string specifies "&makeGpsvDebug=true". A pointer to the file is returned.
 * 
 * @param string  $gpxPath relative path to the gpx file being processed
 * @param integer $window  size of moving average window
 * 
 * @return file  $debugFileMa file pointer to debug file created w/headers populated
 */
function gpsvDebugMaArray($gpxPath, $window) 
{
    $tmpFilename = sys_get_temp_dir() . "/" . basename($gpxPath)
        . "_DebugMa.csv";
    if (file_exists($tmpFilename)) {
        unlink($tmpFilename);
    }
    if (($debugFileMa = fopen("{$tmpFilename}", "w")) === false) {
        $dbfMsg = "Could not open {$gpxPath}_DebugMa.csv in file: " . 
        __File__ . " at line: " . __Line__;
        die($dbfMsg);
    }
    fputs($debugFileMa, "i,Ele,EleMa,window: {$window}" . PHP_EOL);
    return $debugFileMa;
}
/**
 * This function does the actual distance and elevation calculations using various
 * data filtering/smoothing methodologies. 
 * 
 * @param integer          $noOfTrks    no. of tracks to be processed
 * @param simpleXMLElement $xmldata     gpx data loaded into simpleXml data file
 * @param boolean          $debug       T/F use debug files
 * @param file             $dbugFile    file pointer to dbug file array
 * @param file             $dbugCompute file pointer to dbug compute array
 * @param integer          $dThresh     threshold for filtering distance
 * @param integer          $eThresh     threshold for filtering elevation
 * @param integer          $maWindow    window size of moving average
 * @param array            $lats        caller's array of latitudes
 * @param array            $lngs        caller's array of longitudes
 * @param string           $gpsvtdat    the current tracks $tdat for gpsv
 * 
 * @return float           $hikeLgthTot total distance traversed in all tracks
 */
function getTotalDistAndElev(
    $noOfTrks, &$xmldata, $debug, $dbugFile, $dbugCompute, $dThresh, $eThresh,
    $maWindow, &$lats, &$lngs, &$gpsvtdat
) {
    // variables for accumulation calcs
    $pup = (float)0;
    $pdwn = (float)0;
    $pmax = (float)0;
    $pmin = (float)50000;
    $hikeLgthTot = (float)0;

    for ($k=0; $k<$noOfTrks; $k++) { // PROCESS EACH TRK
        $hikeLgth = (float)0;
        /**
         * Get gpx data into individual arrays and do first level
         * processing. Reset arrays for each track.
         */
        $gpxlats = [];
        $gpxlons = [];
        $gpxeles = [];
        $gpxtimes = [];
        $eleChg = [];
        $distance = [];
        $grade = [];
        $speed = [];
        // Read data for trk $k into arrays and do Level 1 calcs
        // Note: $dbugFile may be passed in as a null, so no effect including here
        getGpxL1(
            $xmldata, $k, $gpxlats, $gpxlons, $gpxeles, $gpxtimes,
            $eleChg, $distance, $grade, $speed, $dbugFile, $gpsvtdat
        );

        // Do moving average smoothing on elevation values
        $gpxeles = moveAvg($gpxeles, $maWindow, $gpxPath, $debug);
        // Start computing statistics for trk k
        // Process first trkpt in current trk
        $trkptStrtIdx = 0;
        if ($k == 0) { // Special setup for very first trkpt
            $trkptStrtIdx = 1;
            $prevLat = $gpxlats[0];
            $prevLon = $gpxlons[0];
            $prevEle = $gpxeles[0];

            // Do debug output
            if ($outputDebug) {
                fputs(
                    $dbugCompute, "0,0,{$gpxlats[0]},{$gpxlons[0]},{$gpxeles[0]}"
                    . PHP_EOL
                );
            }
        } // end if: Special setup for very first trkpt

        // Compute stats and create map data for remaining trkpts in trk $k
        for ($m=$trkptStrtIdx; $m<count($gpxlats); $m++) {
            //Do distance and elevation calcs for this trkpt
            if ($outputDebug) {
                $rotation = distElevCalc(
                    $k, $m, $gpxlats, $gpxlons, $gpxeles,
                    $dThresh, $eThresh,
                    $pmax, $pmin, $pup, $pdwn, $hikeLgth, $hikeLgthMiles,
                    $prevLat, $prevLon, $prevEle,
                    $tdat, $dbugCompute
                );
            } else { // no debug file output unless param is set
                $rotation = distElevCalc(
                    $k, $m, $gpxlats, $gpxlons, $gpxeles,
                    $distThresh, $elevThresh,
                    $pmax, $pmin, $pup, $pdwn, $hikeLgth, $hikeLgthMiles,
                    $prevLat, $prevLon, $prevEle,
                    $tdat
                );

            }
        }  // end for: Compute stats and create map data for remaining trkpts in trk k
        $hikeLgthTot += $hikeLgth;
    }  // end for: PROCESS EACH TRK
    return array($hikeLgthTot, $pmax, $pmin, $pup, $pdwn);
}
/**
 * Function to do first level import of one trk from a gpx file.
 * 
 * @param float $gpxlats array of latitude points
 * @param float $gpxlons array of latitude points
 * @param resource $debugFileArray   handle to debug file
 * 
 */
function getGpxL1(
    &$gpxdat, $trkIdx, &$gpxlats, &$gpxlons, &$gpxeles, &$gpxtimes, 
    &$eleChg, &$distance, &$grade, &$speed, &$debugFileArray=null
) {
    $timeChg = [];
    // Get all trkpts for current trk (all trksegs) into array
    $noOfSegs = $gpxdat->trk[$trkIdx]->trkseg->count();
    for ($trkSegIdx=0; $trkSegIdx<$noOfSegs; $trkSegIdx++) {  // All trksegs
        foreach ($gpxdat->trk[$trkIdx]->trkseg[$trkSegIdx]->trkpt as $datum) { // All trkpts
            if (isset($datum->ele)) { // skip trkpts with no ele element
                array_push($gpxlats, (float)$datum['lat']);
                array_push($gpxlons, (float)$datum['lon']);
                array_push($gpxeles, (float)$datum->ele);
                array_push($gpxtimes, strtotime($datum->time));
                $n = count($gpxeles) - 1;
                // Could add grade and speed here too
                if ($n == 0) {
                    $eleChg[$n] = null;
                    $timeChg[$n] = null;
                    $distance[$n] = null;
                    $grade[$n] = null;
                    $speed[$n] = null;
                }
                if ($n >= 1) {
                    $eleChg[$n] = $gpxeles[$n] - $gpxeles[$n-1];
                    $timeChg[$n] = $gpxtimes[$n] - $gpxtimes[$n-1];
                    $parms = distance(
                        $gpxlats[$n-1], $gpxlons[$n-1], $gpxlats[$n], $gpxlons[$n]
                    );
                    $distance[$n] = $parms[0] * 1609; // distance in meters;
                    $grade[$n] = $distance[$n] == 0 ?
                        (float)0 : $eleChg[$n] / $distance[$n];
                    $speed[$n] = $timeChg[$n] == 0 ?
                        (float)0 : $distance[$n] / $timeChg[$n];
                }
                if (!is_null($debugFileArray)) {
                    fputs(
                        $debugFileArray,
                        "{$trkIdx},{$trkSegIdx},{$n},{$gpxlats[$n]}," .
                        "{$gpxlons[$n]},{$gpxeles[$n]},{$gpxtimes[$n]}," .
                        "{$eleChg[$n]},{$timeChg[$n]},{$distance[$n]}," .
                        "{$grade[$n]},{$speed[$n]}" . PHP_EOL
                    );
                }
            }
        }  // end foreach: All trkpts
    } // end for: All trksegs in trk
}
/**
 * Function to do dist/elev calcs for one trkpt.
 * 
 * @param int   $k       trk number
 * @param int   $m       trkpt number
 * @param float $gpxlats array of latitude points
 * @param float $gpxlons array of latitude points
 * 
 * @return array $parms  rotation and distance in miles
 */
function distElevCalc(
    $k, $m, &$gpxlats, &$gpxlons, &$gpxeles,
    $distThresh, $elevThresh,
    &$pmax, &$pmin, &$pup, &$pdwn, &$hikeLgth, &$hikeLgthMiles,
    &$prevLat, &$prevLon, &$prevEle,
    &$tdat=null, &$debugFileCompute=null
) {
    if (!is_null($debugFileCompute)) {
        fputs(
            $debugFileCompute, "{$k},{$m},{$gpxlats[$m]},{$gpxlons[$m]}," .
            "{$gpxeles[$m]}"
        );
    }

    //Do distance calcs
    $parms = distance(
        $prevLat, $prevLon, $gpxlats[$m], $gpxlons[$m]
    );
    $dist = $parms[0] * 1609; // distance in meters                
    if ($dist < $distThresh) {  // Skip small distance changes
        $distThreshMet = false;
    } else {
        $distThreshMet = true;
        $hikeLgth += $dist;
        $hikeLgthMiles = $hikeLgth / 1609;
        $prevLat = $gpxlats[$m];  // update previous element only when used
        $prevLon = $gpxlons[$m];
    }

    //Do elevation calcs
    // Update max and min elevation and Calculate Ascent and Descent.
    if ($gpxeles[$m] > $pmax) {
        $pmax = $gpxeles[$m];
    }
    if ($gpxeles[$m] < $pmin) {
        $pmin = $gpxeles[$m];
    }
    $elevChg = $gpxeles[$m] - $prevEle;
    if (abs($elevChg) < $elevThresh) {  // Skip small elevation changes
        $elevThreshMet = false;
    } else { // calculate Up and Dn
        $elevThreshMet = true;
        if ($elevChg > 0) {
            $pup += $elevChg;
        } else {
            $pdwn -= $elevChg;
        }
        $prevEle = $gpxeles[$m];
    }

    // Do debug output
    if (!is_null($debugFileCompute)) {
        fputs($debugFileCompute, sprintf(",%.2f,%.2f", $elevChg, $dist));
        if (!$distThreshMet && !$elevThreshMet ) {
            fputs($debugFileCompute, ",SBT,SDT");
        } elseif (!$distThreshMet && $elevThreshMet ) {
            fputs($debugFileCompute, ",,SDT");
        } elseif ($distThreshMet && !$elevThreshMet ) {
            fputs($debugFileCompute, ",SBT,");
        } else { 
            fputs($debugFileCompute, ",,");
        }
        $grade = $dist == 0 ? $grade = (float)0: $elevChg / $dist;
        fputs(
            $debugFileCompute,
            sprintf(",%.2f", $grade) .
            sprintf(",%.2f", $hikeLgth) .
            sprintf(",%.2f", $hikeLgthMiles) .
            sprintf(",%.2f", $pup) .
            sprintf(",%.2f", $pdwn) .
            PHP_EOL
        );
    }
    return ($parms[1]);
}
/**
 * Function to do compute elevation moving average for on trkpt 
 * 
 * @param float    $data    array of elevation values
 * @param int      $window  moving average window size must be odd
 * @param string   $gpxPath path to gpx file
 * @param resource $debug   handle to debug file
 * 
 * @return array $averages  array of moving average results
 */
function moveAvg($data, $window, $gpxPath, $debug)
{
    if ($window <= 1) { // just copy if window too small
        for ($i=0; $i< count($data); $i++) {
            $averages[$i] = $data[$i];
        }
    }
    if (count($data)<=$window) {
        $dbMsg = "Not enough data to process in file: " . 
        __File__ . " at line: " . __Line__;
        die($dbMsg);
    }
    if ($window%2 <> 1) {
        $dbMsg = "Window value  {$window} not odd" . 
        __File__ . " at line: " . __Line__;
        die($dbMsg);
    }
    if ($debug) {
        // Open debug file with headers
        $maHandle = gpsvDebugMaArray($gpxPath, $window);
    }

    $averages = [];
    $windowHalf = ($window - 1) / 2;
    $last_i = count($data);
    // Return first ($window-1)/2 elements unchanged
    for ($i=0; $i<$windowHalf; $i++) {
        $averages[$i] = $data[$i];
        if ($debug) {
            fputs($maHandle, "{$i},{$data[$i]},{$averages[$i]}" . PHP_EOL);
        }
    }
    // Create moving average elements
    for ($i=$windowHalf; $i<($last_i-$windowHalf); $i++) {
        $sum = 0;
        for ($j=($i-$windowHalf); $j<($i+$windowHalf+1); $j++) {
            $sum = $sum + $data[$j];
        }
            $averages[$i] = $sum / $window;
        //$sum = $sum - $data[$i-$window] + $data[$i];
        if ($debug) {
            fputs($$maHandle, "{$i},{$data[$i]},{$averages[$i]}" . PHP_EOL);
        }
    }
    // Return last ($window-1)/2 elements unchanged
    for ($i=($last_i-$windowHalf); $i<$last_i; $i++) {
        $averages[$i] = $data[$i];
        if ($debug) {
            fputs($maHandle, "{$i},{$data[$i]},{$averages[$i]}" . PHP_EOL);
        }
    }
    if ($debug) {
        fclose($maHandle);
    }
    return $averages;
}
