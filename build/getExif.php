<?php
/**
 * This routine extracts exif data by partially downloading the original 
 * photo - sufficient to contain the exif metadata - and writing the truncated
 * file to a temp directory. It then reads the exif data from that file, stores 
 * the required information in arrays for the calling routine, and then deletes
 * the file. This script does not care which album type has been selected.
 * 
 * @package Create
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
define("CHUNK", 8192);
// original photos assumed to be stored in the $o array in getPicDat.php
// start processing based on album: $pcnt is total, $albOcnt is album only
$kstart = $pcnt - $albOcnt;
for ($k=$kstart; $k<$pcnt; $k++) {
    // Read the original-sized photo w/metadata
    $orgPhoto = $o[$k];
    $photoHandle = fopen($orgPhoto, "r");
    if ($photoHandle === false) {
        $noread = $pstyle . "Failed to open photo" . $k .
            ' url: ' . $orgPhoto . '</p>';
        die($noread);
    }
    $truncFile = 'tmp/photo' . $k . '.jpg';
    $fileSize = 0;
    $contents = '';
    while (!feof($photoHandle)) {
        $contents .= fread($photoHandle, CHUNK);
        $fileSize += CHUNK;
        // Write the truncated file to tmp/
        $exifFile = fopen($truncFile, "w");
        if ($exifFile === false) {
            $nowrite = $pstyle . 'Could not open file to write photo' . $k . '</p>';
            die($nowrite);
        }
        // Write the truncated file to tmp/
        fwrite($exifFile, $contents);
        fclose($exifFile);
        $exifdata = exif_read_data($truncFile);
        if ($exifdata === false) {
            continue;   // no exif data yet - go back and read some more
        } else {
            break;      // exif data found - exit while
        }
    }
    fclose($photoHandle);
    if ($exifdata === false) {
        echo $pstyle . 'WARNING: Could not read exif data for ' . $orgPhoto
            . '<br />Please verify that all album photos contain metadata. '
            . 'Note that the routine will continue without including '
            . 'latitude/longitude/date-stamp data. This implies that such '
            . 'photos will not appear on the hike map.</p>';
    } else {
        $ext = strrpos($exifdata["FileName"], ".");
        $imgName = substr($exifdata["FileName"], 0, $ext);
        $imgs[$k] = $imgName;
        // NOTE: orientations of 3, and 8 are not addressed here
        $imgHt[$k] = $exifdata["COMPUTED"]['Height'];
        $imgWd[$k] = $exifdata["COMPUTED"]['Width'];
        $orient = $exifdata["Orientation"];
        if ($orient == '6') {
            $tmpval = $imgHt[$k];
            $imgHt[$k] = $imgWd[$k];
            $imgWd[$k] = $tmpval;
        }
        $timeStamp[$k] = $exifdata["DateTimeOriginal"];
        if ($timeStamp[$k] == '') {
            echo "WARNING: No date/time data found " . 'for ' .
                $orgPhoto . '</p>';
        }
        if (!isset($exifdata["GPSLatitudeRef"])
            || !isset($exifdata["GPSLatitude"])
        ) {
            $lats[$k] = 0;
            $lngs[$k] = 0;
        } else {
            if ($exifdata["GPSLatitudeRef"] == 'N') {
                    $lats[$k] = mantissa($exifdata["GPSLatitude"]);
            } else {
                    $lats[$k] = -1 * mantissa($exifdata["GPSLatitude"]);
            }
            if ($exifdata["GPSLongitudeRef"] == 'E') {
                    $lngs[$k] = mantissa($exifdata["GPSLongitude"]);
            } else {
                    $lngs[$k] = -1 * mantissa($exifdata["GPSLongitude"]);
            }
        }
        $elev[$k] = $exifdata["GPSAltitude"];
        $gpds[$k] = $exifdata["GPSDateStamp"];
        // array
        $gpts[$k] = $exifdata["GPSTimeStamp"];
        if ($lats[$k] == 0 || $lngs[$k] == 0) {
            echo $pstyle . "WARNING: No latitude/longitude data obtained for " .
                $orgPhoto . '</p>';
        }
    }  // end of exifdata found
    if (!unlink($truncFile)) {
        $nodelete = $pstyle . 'Could not delete temporary file ' . $truncFile .
                '</p>';
    }
}  // end of for each original photo loop
