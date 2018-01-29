<?php
/**
 * This file contains function declarations designed to be used
 * by modules in the build directory.
 * 
 * @package Build_Functions
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
/**
 * This function validates the upload file and then moves the temporary
 * copy of the file stored in the server, to the site directory. In the
 * process, it calls the function uploadErr() if an error occurs during
 * upload, and dupFileName() if it finds that a file of the same name
 * already exists on the site. Other validation checks are performed.
 * NOTE: If the upload is a main site gpx file, makeTrackFile will be called.
 * 
 * @param string $name     <input type="file" name="$name" />
 * @param string $fileloc  The directory location for moving the upload
 * @param string $mimetype The mime type to validate on upload
 * 
 * @return array $filename, $msg
 */
function validateUpload($name, $fileloc, $mimetype)
{
    $msg = '';
    $filename = basename($_FILES[$name]['name']);
    if ($filename !== '') {
        $tmp_upload = $_FILES[$name]['tmp_name'];    
        $filetype = $_FILES[$name]['type'];
        $filestat = $_FILES[$name]['error'];
        if ($filestat !== UPLOAD_ERR_OK) {
            $badupld = "Failed to upload {$name}: " . uploadErr($filestat);
            die($badupld);
        }
        if (substr_count($filename, ".") !== 1) {
            $odd = "This file may be corrupted. Please correct the " .
                "file format and re-submit, or contact Site Master.";
            die($odd);
        }
        if ($mimetype !== 'nocheck') {
            if (preg_match($mimetype, $filetype) === 0) {
                $badmime = $filetype . ": should be '{$mimetype}'";
                die($badmime);
            }
        }
        $saveloc = $fileloc . $filename;
        if (file_exists($saveloc)) {
            $dupdata = dupFileName($filename);
            $filename = $dupdata[0];
            $saveloc = $fileloc . $filename;
            $msg .=  $dupdata[1] . "<br />";
        }
        if (!move_uploaded_file($tmp_upload, $saveloc)) {
            $nomove = "Could not save {$filename} to site: contact Site Master";
            die($nomove);
        } else {
            $msg .= "'{$filename}' Successfully uploaded to site";
        }
    } else {
        $filename = "No file specified";
    }
    return array($filename, $msg);
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
/**
 * When it has been determined that a file already exists on the
 * site in the destination directory, the string _DUP is attached
 * and the file is uploaded. The user will be notified when this
 * occurs.
 * 
 * @param string $oldname The filename which currently exists on site
 * 
 * @return array The new filename and msg to user that a dup was found
 */
function dupFileName($oldname)
{
    $extpos = strrpos($oldname, ".");
    $fbase = substr($oldname, 0, $extpos) . '_DUP.';
    $extpos++;
    $extlgth = strlen($oldname) - $extpos;
    $fext = substr($oldname, $extpos, $extlgth);
    $newname = $fbase . $fext;
    $fout = 'NOTE: ' . $oldname . ' has been previously saved on the '.
        'server; A new file name was created: ' . $newname;
    return array($newname, $fout);
}
/**
 * This function will create a .json track file from the specified gpx.
 * 
 * @param string $gpxfile The file to be converted to a .json file
 * @param string $gpxpath The path to the gpx file
 * 
 * @return array $trkfile (new .json file), $msg (output message if desired)
 */
function makeTrackFile($gpxfile, $gpxpath) 
{
    $ext = strrpos($gpxfile, ".");
    $baseName = substr($gpxfile, 0, $ext);
    $gpxLoc = $gpxpath . $gpxfile;
    // Now create the .json track file
    $gpxdat = simplexml_load_file($gpxLoc);
    if ($gpxdat === false) {
        die(
            "buildFunctions.php: Could not load gpx file as simplexml; " .
            "Please contact Site Master"
        );
    }
    $trkfile = $baseName . ".json";
    $trkLoc = '../json/' . $trkfile;
    $json = true;
    include "../php/extractGpx.php"; // creates track file $jdat
    $trk = fopen($trkLoc, "w");
    $dwnld = fwrite($trk, $jdat);
    if ($dwnld === false) {
        $trkfail =  "buildFunctions.php: Failed to write out {$trkfile} " .
            "[length: " . strlen($jdat) . "]; Please contact Site Master";
        die($trkfail);
    } else {
        $msg = '<p>Track file created from GPX and saved</p>';
    }
    fclose($trk);
    // Beginning pt = trailhead
    $latpos = strpos($jdat, '"lat":') + 6;
    $latend = strpos($jdat, ',', $latpos);
    $latlgth = $latend - $latpos;
    $lat = substr($jdat, $latpos, $latlgth);
    $lngpos = strpos($jdat, '"lng":') + 6;
    $lngend = strpos($jdat, '}', $lngpos);
    $lnglgth = $lngend - $lngpos;
    $lng = substr($jdat, $lngpos, $lnglgth);
    
    return array($trkfile, $msg, $lat, $lng);
}
/**
 * This function is used to check the file extension of an upload and determine
 * the location on the server for it to reside, based on extension type.
 * The whitelist for extensions is currently: .gpx (.GPX), .kml, and .html
 * 
 * @param string $fname The file name with or with path info
 * 
 * @return array $uplType (file mime type) and $floc (site path for storage)
 */
function fileTypeAndLoc($fname)
{
    $usable = array('gpx', 'kml', 'html');
    // get lower case representation of file extension
    $dot = strpos($fname, ".") + 1;
    $extlgth = strlen($fname) - $dot;
    $ext = substr($fname, $dot, $extlgth);
    $fext = strtolower($ext);
    $checks = count($usable);
    // see if the extension is "usable" and assign it an appropriate location
    $mimeType = '';
    $ftypeError = '';
    for ($i=0; $i<$checks; $i++) {
        if ($fext === $usable[$i]) {
            if ($fext === 'html') {
                $mimeType = "/html/";
                $floc = '../maps/';
            } elseif ($fext === 'kml') {
                $mimeType = '/vnd.google-earth.kml+xml/';
                $floc = '../gpx/';
            } else {
                $mimeType = "/octet-stream/";
                $floc = '../gpx/';
            }
        }
    }
    if ($mimeType === '') {
        $floc = 'NONE';
        $ftypeError = "Unacceptable file extension";
    }
    return array($floc, $mimeType, $fext, $ftypeError);
}
/**
 * This function extracts existing cluster info and Visitor Center info
 * from the HIKES table needed to display 'select' drop-down boxes
 * 
 * @return array The results of extracting clus & vc data from HIKES
 */
function dropdownData()
{
    $link = connectToDb(__FILE__, __LINE__);
    $vchikes = [];
    $vcnos = [];
    $clhikes = [];
    $cldat = [];
    $hquery = "SELECT indxNo,pgTitle,marker,`collection`,cgroup,cname "
            ."FROM HIKES;";
    $specdat = mysqli_query($link, $hquery) or die(
        'enterHike.php: Could not retrieve vc/cluster info: ' .
        mysqli_error($link)
    );
    while ($select = mysqli_fetch_assoc($specdat)) {
        $indx = $select['indxNo'];
        $title = $select['pgTitle'];
        $marker = $select['marker'];
        $coll = $select['collection'];
        $clusltr = $select['cgroup'];
        $clusnme = $select['cname'];
        if ($marker == 'Visitor Ctr') {
            array_push($vchikes, $title);
            array_push($vcnos, $indx);
        } elseif ($marker == 'Cluster') {
            $dup = false;
            for ($l=0; $l<count($clhikes); $l++) {
                if ($clhikes[$l] == $clusnme) {
                    $dup = true;
                }
            }
            if (!$dup) {
                array_push($clhikes, $clusnme);
                // Need to include both Cluster Name and Cluster Letter when posting
                $postCl = $clusltr . ":" . $clusnme;
                array_push($cldat, $postCl);
            }
        }
    }
    mysqli_free_result($specdat);
    return array($clhikes, $cldat, $vchikes, $vcnos);
}
/**
 * A simple function converts null into empty string after reading
 * a field from the database.
 * 
 * @param string $var Incoming database variable to be checked
 * 
 * @return string the prepped string
 */
function fetch($var)
{
    $clean = is_null($var) ? '' : $var;
    return trim($clean);
}
/**
 * For Flickr Albums ONLY: retrieve photo date from Flickr html based
 * on Flickr's photomodel javascript object.
 * 
 * @param string $photomodel extracted string of javascript object defni
 * @param string $size       letter representation of stored image size
 * 
 * @return string $url Exptracted url for corresponding letter size
 */
function getFlickrDat($photomodel, $size)
{
    $ltrSize = strlen($size);  // NOTE: at least 1 size is two letters
    $offset = 4 + $ltrSize;
    $modelLtr = '"' . $size . '":{';
    $sizePos = strpos($photomodel, $modelLtr) + $offset;
    $urlPos = strpos($photomodel, '"url":"', $sizePos) + 7;
    $urlEnd = strpos($photomodel, '"', $urlPos);
    $urlLgth = $urlEnd - $urlPos;
    $rawurl = substr($photomodel, $urlPos, $urlLgth);
    $url = 'https:' . preg_replace('/\\\\/', '', $rawurl);
    return $url;
}
/**
 * The latitude and longitude in exif data are given as numeric arrays.
 * This function forms the float value corresponding to the array.
 * 
 * @param array $degrees Exif data for lat or lng in native exif array
 * 
 * @return float $coords The array value given as a floating point no.
 */
function mantissa($degrees)
{
    $coords = 0;
    for ($z = 0; $z < 3; $z++) {
        $div = strpos($degrees[$z], '/');
        $body = substr($degrees[$z], 0, $div);
        $divisor = substr($degrees[$z], $div + 1);
        switch ($z) {
        case 0:
            $coords = $body / $divisor;
            break;
        case 1:
            $mins = $body / $divisor;
            break;
        case 2:
            $secs = $body / $divisor;
            break;
        }
    }
    $coords += ($mins + $secs / 60) / 60;
    return $coords;
}
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
