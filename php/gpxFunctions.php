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
 * 
 * @return float           $hikeLgthTot total distance traversed in all tracks
 */
function getTotalDistAndElev(
    $noOfTrks, $xmldata, $debug, $dbugFile, $dbugCompute, $dThresh, $eThresh, $maWindow
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
        // Read data for trk k into arrays and do Level 1 calcs
        // Note: $dbugFile may be passed in as a null, so no effect including here
        getGpxL1(
            $xmldata, $k, $gpxlats, $gpxlons, $gpxeles, $gpxtimes,
            $eleChg, $distance, $grade, $speed, $dbugFile
        );

        // Do moving average smoothing on elevation values
        if ($outputDebug) {
            $gpxeles = moveAvg($gpxeles, $maWindow, $gpxPath, true);
        } else { // no debug file output unless param is set
            $gpxeles = moveAvg($gpxeles, $maWindow, $gpxPath, false);
        }
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
    return $hikeLgthTot;
}
