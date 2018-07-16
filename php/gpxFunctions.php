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
 * @return resource $fileArrayHandle file pointer to debug file w/headers
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
 * @return resource $debugComputeArray file pointer to debug file created w/headers
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
 * @return resource $debugFileMa file ptr to debug file created w/headers populated
 */
function gpsvDebugMaArray($gpxPath, $window) 
{
    $tmpFilename = sys_get_temp_dir() . "/" . basename($gpxPath)
        . "_DebugMa.csv";
    if (file_exists($tmpFilename)) {
        unlink($tmpFilename);
    }
    if (($debugFileMa = fopen("{$tmpFilename}", "a")) === false) {
        $dbfMsg = "Could not open {$gpxPath}_DebugMa.csv in file: " . 
        __File__ . " at line: " . __Line__;
        die($dbfMsg);
    }
    fputs($debugFileMa, "i,Ele,EleMa,window: {$window}" . PHP_EOL);
    return $debugFileMa;
}
/**
 * This function does the actual distance and elevation calculations using various
 * data filtering/smoothing methodologies. Called once for each track.
 * 
 * @param integer  $trkNo       track no. being 
 * @param string   $trkname     name of current track
 * @param string   $gpxPath     path to gpxfile
 * @param object   $xmldata     gpx file loaded into simpleXml object
 * @param boolean  $debug       T/F use debug files
 * @param resource $dbugFile    file pointer to dbug file array
 * @param resource $dbugCompute file pointer to dbug compute array
 * @param integer  $dThresh     threshold for filtering distance
 * @param integer  $eThresh     threshold for filtering elevation
 * @param integer  $maWin       window size of moving average
 * @param string   $tdat        GPSV track data js string
 * @param array    $ticks       GPSV array of ticks
 * 
 * @return float           $hikeLgthTot total distance traversed in all tracks
 */
function getTrackDistAndElev(
    $trkNo, $trkname, $gpxPath, &$xmldata, $debug, $dbugFile, $dbugCompute,
    $dThresh, $eThresh, $maWin, &$tdat, &$ticks
) {
    // variables for each track's calcs
    $hikeLgth = (float)0;
    $tickMrk = 0.30 * 1609; // tickmark interval in miles converted to meters
    $tno = $trkNo + 1;  // GPSV map starts with track 1, not 0
    // the following variables are passed back to the caller for accumulation
    $pup = (float)0;
    $pdwn = (float)0;
    $pmax = (float)0;
    $pmin = (float)50000;
    $hikeLgthTot = (float)0;
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
    // Read data for trk $trkNo into arrays and do Level 1 calcs
    // Note: $dbugFile may be passed in as a null, so no effect including here
    getGpxL1(
        $xmldata, $trkNo, $gpxlats, $gpxlons, $gpxeles, $gpxtimes,
        $eleChg, $distance, $grade, $speed, $dbugFile
    );

    // Do moving average smoothing on elevation values
    $gpxeles = moveAvg($gpxeles, $maWin, $gpxPath, $debug);
    
    // Start computing statistics for trk $trkNo
    // Process first trkpt in current trk &  establish 'prev' values
    $prevLat = $gpxlats[0];
    $prevLon = $gpxlons[0];
    $prevEle = $gpxeles[0];

    // For makeGpsv.php file:
    if (isset($tdat)) {
        $tdat .= $gpxlats[0] . "," . $gpxlons[0] . "],[";
    }
    // Conditional debug output
    if (isset($dbugCompute)) {
        fputs(
            $dbugCompute, "0,0,{$gpxlats[0]},{$gpxlons[0]},{$gpxeles[0]}"
            . PHP_EOL
        );
    }

    // Compute stats and create map data for remaining trkpts in $trkNo track
    for ($m=1; $m<count($gpxlats); $m++) {
        //Do distance and elevation calcs for this trkpt ($dbugCompute may be null)
        $rotation = distElevCalc(
            $trkNo, $m, $gpxlats, $gpxlons, $gpxeles,
            $dThresh, $eThresh,
            $pmax, $pmin, $pup, $pdwn, $hikeLgth, $hikeLgthMiles,
            $prevLat, $prevLon, $prevEle, $dbugCompute
        );
        // For makeGpsv.php:
        if (isset($tdat)) {
            // Form GPSV javascript track and tickmark data for this trkpt
            $tdat .= $gpxlats[$m] . "," . $gpxlons[$m] . "],[";
            if ($hikeLgth > $tickMrk) {
                $tick
                    = "GV_Draw_Marker({lat:" . $gpxlats[$m] .
                    ",lon:" . $gpxlons[$m] . ",name:'" . $tickMrk/1609 .
                    " mi',desc:'',color:trk[" . $tno .
                    "].info.color,icon:'tickmark',type:'tickmark',folder:'" .
                    $trkname . " [tickmarks]',rotation:" . $rotation .
                    ",track_number:" . $tno . ",dd:false});";
                array_push($ticks, $tick);
                $tickMrk += 0.30 * 1609; // interval in miles converted to meters
            }
        }
    }  // end for: Compute stats and create map data for remaining trkpts in trk k
    $hikeLgthTot += $hikeLgth;
    return array($hikeLgthTot, $pmax, $pmin, $pup, $pdwn, $gpxlats, $gpxlons);
}
/**
 * Function to do first level import of one trk from a gpx file.
 * 
 * @param object   $gpxdat         xml data from gpx file
 * @param integer  $trkIdx         track id
 * @param array    $gpxlats        caller's array of latitude points
 * @param array    $gpxlons        caller's array of longitude points
 * @param array    $gpxeles        caller's array of 
 * @param array    $gpxtimes       array of caller's timestamps
 * @param array    $eleChg         array of caller's elev changes
 * @param array    $distance       array of caller's distances
 * @param array    $grade          array of caller's grades
 * @param array    $speed          array of caller's speeds
 * @param resource $debugFileArray handle to debug file
 * 
 * @return null
 */
function getGpxL1(
    &$gpxdat, $trkIdx, &$gpxlats, &$gpxlons, &$gpxeles, &$gpxtimes, 
    &$eleChg, &$distance, &$grade, &$speed, $debugFileArray=null
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
 * @param int      $k                trk number
 * @param int      $m                trkpt number
 * @param array    $gpxlats          array of latitude points in caller
 * @param array    $gpxlons          array of latitude points in caller
 * @param array    $gpxeles          array of elevations in caller
 * @param float    $distThresh       value to be used for distance filter
 * @param float    $elevThresh       value to be used for elevation fileter
 * @param float    $pmax             maximum elev so far
 * @param float    $pmin             minimum elev so far
 * @param float    $pup              accumulated ascent
 * @param float    $pdwn             accumulated descent
 * @param float    $hikeLgth         track length in meters
 * @param float    $hikeLgthMiles    length in miles
 * @param float    $prevLat          previous trkpt latitude
 * @param float    $prevLon          previous trkpt longitude
 * @param float    $prevEle          previous trkpt elevation
 * @param resource $debugFileCompute handle to debug file
 * 
 * @return array $parms  rotation and distance in miles
 */
function distElevCalc(
    $k, $m, &$gpxlats, &$gpxlons, &$gpxeles,
    $distThresh, $elevThresh,
    &$pmax, &$pmin, &$pup, &$pdwn, &$hikeLgth, &$hikeLgthMiles,
    &$prevLat, &$prevLon, &$prevEle, $debugFileCompute
) {
    if (!is_null($debugFileCompute)) {
        fputs(
            $debugFileCompute, 
            "{$k},{$m},{$gpxlats[$m]},{$gpxlons[$m]},{$gpxeles[$m]}"
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
            fputs($maHandle, "{$i},{$data[$i]},{$averages[$i]}" . PHP_EOL);
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
