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

# original photos assumed to be stored in the $o array
# start processing based on album:
$kstart = $pcnt - $albOcnt;
for ($k=$kstart; $k<$pcnt; $k++) {
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
    $exifdata = exif_read_data($truncFile);
#	echo $pstyle . "GPSLongitudeRef: " . $exifdata["GPSLongitudeRef"]; # TJS
    if ($exifdata === false) {
        echo $pstyle . 'WARNING: Could not read exif data for ' . $orgPhoto
            . '<br />Please verify that all album photos contain metadata. '
            . 'Note that the routine will continue without including '
            . 'latitude/longitude/date-stamp data. This implies that such '
            . 'photos will not appear on the hike map.</p>';
    } else {
#        foreach ($exifdata as $key => $section) {
#            foreach ($section as $name => $val) {
#                switch ($key) {
#                    case 'FILE':
#                        if ($name === 'FileName') {
                            $ext = strrpos($exifdata["FileName"],".");
                            $imgName = substr($exifdata["FileName"],0,$ext);
                            $imgs[$k] = $imgName;
#                        }
#                        break;
#                    case 'COMPUTED':
#                        if ($name === 'Height') {
                            $imgHt[$k] = $exifdata["Height"];
#                        } elseif ($name === 'Width') {
                            $imgWd[$k] = $exifdata["Width"];
#                        }
#                        break;
#                    case 'IFD0':
#                        if ($name === 'Model') {
#                            $imgPh[$k] = $val; # currently not used
#                        }
#                        break;
#                    case 'EXIF':
#                        if ($name === '0') {
                            $timeStamp[$k] = $exifdata["DateTimeOriginal"];
                            if ($timeStamp[$k] == '') {
                                echo "WARNING: No date/time data found " .
                                    'for ' . $orgPhoto . '</p>';
                            }
#                        }
#                    case 'GPS':
#                        if ($name === 'GPSLatitude') {
					if ($exifdata["GPSLatitudeRef"] == 'N') {
                            $lats[$k] = mantissa($exifdata["GPSLatitude"]); # TJS
					} else {
                            $lats[$k] = -1 * mantissa($exifdata["GPSLatitude"]); # TJS
					}
#                        } elseif ($name === 'GPSLongitude') {
					if ($exifdata["GPSLongitudeRef"] == 'E') {
                            $lngs[$k] = mantissa($exifdata["GPSLongitude"]); # TJS
					} else {
                            $lngs[$k] = -1 * mantissa($exifdata["GPSLongitude"]); # TJS
					}
#                        } elseif ($name === 'GPSAltitude') {
                            $elev[$k] = $exifdata["GPSAltitude"];
#                        } elseif ($name === 'GPSDateStamp') {
                            $gpds[$k] = $exifdata["GPSDateStamp"];
#                        } elseif ($name === 'GPSTimeStamp') {
                            // array
                            $gpts[$k] = $exifdata["GPSTimeStamp"];
#                        }
#                        break;
#                    default:
#                        break;
#                }  // end of switch statement
#            }  // end of foreach $name loop
#        }  // end of foreach $section loop
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
