<?php
/* 
 * This routine extracts exif data by partially downloading the original 
 * photo - sufficient to contain the exif metadata - and writing the truncated
 * file to a temp directory. It then reads the exif data from that file, stores 
 * the required information in arrays for the calling routine, and then deletes
 * the file. This script does not care which album type has been selected.
 */
define ("CHUNK",8192);
/*
function convtTime($GPStime) {
    $hrs = explode("/",$GPStime[0]);
    $hr = intval($hrs[0]/$hrs[1]);
    $mins = explode("/",$GPStime[1]);
    $min = intval($mins[0]/$mins[1]);
    $secs = explode("/",$GPStime[2]);
    $sec = intval($secs[0]/$secs[1]);
    $tstring = $hr . ':' . $min . ":" . $sec;
    return $tstring;
}
 * NOT CURRENTLY USED
 */
# EXIF data arrays
$imgs = [];
$imgHt = [];
$imgWd = [];
$imgPh = [];  # not currently utilzed
$timeStamp = [];
$lats = [];
$lngs = [];
$elev = [];  # not currently utilized
$gpds = [];
$gpts = [];

# original photos assumed to be stored in the $o array
for ($k=0; $k<count($o); $k++) {
    # Read the original-sized photo w/metadata
    $orgPhoto = $o[$k];
    $photoHandle = fopen($orgPhoto,"r");
    if ($photoHandle === false) {
        $noread = $pstyle . "Failed to open photo" . $k . 
            ' url: ' . $orgPhoto . '</p>';
        die($noread);
    }
    $fileSize = CHUNK;
    $contents = '';
    while ($fileSize < 10 * CHUNK) {
        $contents .= fread($photoHandle, CHUNK);
        $fileSize += CHUNK;
    }
    fclose($photoHandle);
    # Write the truncated file to tmp/
    $truncFile = 'tmp/photo' . $k . '.jpg';
    $exifFile = fopen($truncFile,"w");
    if ($exifFile === false) {
        $nowrite = $pstyle . 'Could not open file to write photo' . $k . '</p>';
        die ($nowrite);
    }
    fwrite($exifFile,$contents);
    fclose($exifFile);
    $exifdata = exif_read_data($truncFile, ANY_TAG, EXIF);
    if ($exifdata === false) {
        echo $pstyle . 'WARNING: Could not read exif data for ' . $orgPhoto
            . '<br />Please verify that all album photos contain metadata. '
            . 'Note that the routine will continue without including '
            . 'latitude/longitude/date-stamp data. This implies that such '
            . 'photos will not appear on the hike map.</p>';
    } else {
        foreach ($exifdata as $key => $section) {
            foreach ($section as $name => $val) {
                switch ($key) {
                    case 'FILE':
                        if ($name === 'FileName') {
                            $ext = strrpos($val,".");
                            $imgName = substr($val,0,$ext);
                            $imgs[$k] = $imgName;
                        }
                        break;
                    case 'COMPUTED':
                        if ($name === 'Height') {
                            $imgHt[$k] = $val;
                        } elseif ($name === 'Width') {
                            $imgWd[$k] = $val;
                        }
                        break;
                    case 'IFD0':
                        if ($name === 'Model') {
                            $imgPh[$k] = $val; # currently not used
                        }
                        break;
                    case 'EXIF':
                        if ($name === 'DateTimeOriginal') {
                            $timeStamp[$k] = $val;
                            if ($val == '') {
                                echo "WARNING: No date/time data found " .
                                    'for ' . $orgPhoto . '</p>';
                            }
                        }
                    case 'GPS':
                        if ($name === 'GPSLatitude') {
                            $lats[$k] = mantissa($val);
                        } elseif ($name === 'GPSLongitude') {
                            $lngs[$k] = -1 * mantissa($val);
                        } elseif ($name === 'GPSAltitude') {
                            $elev[$k] = $val;
                        } elseif ($name === 'GPSDateStamp') {
                            $gpds[$k] = $val;
                        } elseif ($name === 'GPSTimeStamp') {
                            // array
                            $gpts[$k] = $val;
                        }
                        break;
                    default:
                        break;
                }  // end of switch statement
            }  // end of foreach $name loop
        }  // end of foreach $section loop
        if ($lats[$k] == 0 || $lngs[$k] == 0) {
            echo $pstyle . "WARNING: No latitude/longitude data obtained for " .
                $orgPhoto . '</p>';
        }
    }  # end of exifdata found
    if (!unlink($truncFile)) {
        $nodelete = $pstyle . 'Could not delete temporary file ' . $truncFile .
                '</p>';
    }
}  # end of for each original photo loop
?>