<?php
/**
 * This file contains function declarations designed to be used
 * by modules in the build directory. At this time, there are also
 * some instances called by makeGpsv.php.
 * PHP Version 7.1
 * 
 * @package Build_Functions
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
/**
 * This function validates the upload file and then moves the temporary
 * copy of the file stored in the server, to the site directory. In the
 * process, it calls the function uploadErr() if an error occurs during
 * upload, and dupFileName() if it finds that a file of the same name
 * already exists on the site. Other validation checks are performed.
 * NOTE: If the upload is a main site gpx file, makeTrackFile will be called.
 * 
 * @param string $name    <input type="file" name="$name" />
 * @param string $fileloc The directory location for moving the upload
 * 
 * @return array $filename, $msg
 */
function validateUpload($name, $fileloc)
{
    libxml_use_internal_errors(true);
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
        // Validate against schema, if gpx (XML):
        $ext = strpos($filename, ".") + 1;
        $file_ext = substr($filename, $ext, 3);
        if (strtoLower($file_ext) === 'gpx') {
            $xml = new DOMDocument;
            if (!$xml->load($tmp_upload)) {
                die(
                    "{$filename} could not be loaded as a DOMDocument in "
                    . "validateUpload of buildFunctions.php line " . __LINE__
                );
            }
            if (!$xml->schemaValidate(
                "http://www.topografix.com/GPX/1/1/gpx.xsd", LIBXML_SCHEMA_CREATE
            )
            ) {
                $error_vals = libxml_get_errors();
                $err_list = "<ul>";
                foreach ($error_vals as $err) {
                    $err_list .= "<li>" . displayXmlError($err, $filename) .
                    "</li>";
                }
                $err_list .= "</ul>";
                die(
                    "{$filename} could not be validated against the XML gpx " 
                    . "schema in validateUpload() " . __FILE__ . " line "
                    . __LINE__ . "<br />" . $err_list
                );

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
 * The libxml errors have their own error processing requiring a handler,
 * specified in this function routine.
 * 
 * @param object $error   libxml object when error occurs
 * @param string $gpxfile name of affected file
 * 
 * @return string $return error string to return
 */
function displayXmlError($error, $gpxfile) 
{
    switch ($error->level) {
    case LIBXML_ERR_WARNING:
        $return .= "Warning $error->code: ";
        break;
    case LIBXML_ERR_ERROR:
        $return .= "Error $error->code: ";
        break;
    case LIBXML_ERR_FATAL:
        $return .= "Fatal Error $error->code: ";
        break;
    default:
        $return = "Error level not recognized";
    }
    $return .= trim($error->message) . "<br />" .
        "\n  Line: $error->line" . "\n  Column: $error->column";
    if ($error->file) {
        $return .= "\n  File: {$gpxfile}";
    }
    return $return;
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
    $trkfile = $baseName . ".json";
    $trkLoc = '../json/' . $trkfile;
    $gpxLoc = $gpxpath . $gpxfile;
    $gpxdat = gpxLatLng($gpxLoc, "1");
    $thlat = $gpxdat[0][0];
    $thlng = $gpxdat[1][0];
    $trk = fopen($trkLoc, "w");
    $dwnld = fwrite($trk, $gpxdat[3]);
    if ($dwnld === false) {
        $trkfail =  "buildFunctions.php: Failed to write out {$trkfile} " .
            "[length: " . strlen($jdat) . "]; Please contact Site Master";
        die($trkfail);
    } else {
        $msg = '<p>Track file created from GPX and saved</p>';
    }
    fclose($trk);
    
    return array($trkfile, $msg, $thlat, $thlng);
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
 * from the HIKES table needed to display 'select' drop-down boxes. Note:
 * Due to the fact that sorting will place group "AA" after "A" and not 
 * after "Z", the routine utilizes two sorted cluster arrays then merges them.
 * 
 * @param PDO    $pdo     PDO object for db access
 * @param string $boxtype data to be returned: 
 *                        vistor centers ('vcs') or clusters ('cls')
 * 
 * @return array depending on $boxType, vc data or cluster data
 */
function dropdownData($pdo, $boxtype)
{
    $hquery = "SELECT indxNo,pgTitle,marker,`collection`,cgroup,cname FROM HIKES;";
    $hdat = $pdo->query($hquery);
    $equery = "SELECT marker,cgroup,cname FROM EHIKES;";
    $edat = $pdo->query($equery);
    // return data based on $boxType:
    if ($boxtype === 'vcs') {
        $vchikes = [];
        $vcnos = [];
        $colls = [];
        while ($vcdata = $hdat->fetch(PDO::FETCH_ASSOC)) {
            $hmarker = $vcdata['marker'];
            if ($hmarker == 'Visitor Ctr') {
                $indx = $vcdata['indxNo'];
                $title = $vcdata['pgTitle'];
                $coll = $vcdata['collection'];
                array_push($vcnos, $indx);
                array_push($vchikes, $title);
                array_push($colls, $coll);
            }
        }
        return array($vchikes, $vcnos, $colls);
    } else {
        $singles = [];
        $doubles = [];
        while ($hclus = $hdat->fetch(PDO::FETCH_ASSOC)) {
            $hmarker = $hclus['marker'];
            if ($hmarker === 'Cluster') {  
                $clusltr = $hclus['cgroup'];
                $clusnme = $hclus['cname'];
                if (strlen($clusltr) === 1) {
                    if (!memberPresent($clusnme, $singles)) {
                        $singles[$clusltr] = $clusnme;
                    }
                } elseif (strlen($clusltr) === 2) {
                    if (!memberPresent($clusnme, $doubles)) {
                        $doubles[$clusltr] = $clusnme;
                    }
                } else {
                    die(
                        "Clusters of length " . strlen($clusltr)
                        . " not supported at this time"
                    );
                }
            }
        }
        while ($eclus = $edat->fetch(PDO::FETCH_ASSOC)) {
            $emarker = $eclus['marker'];
            // Note: creating new pg MAY result in 'Cluster' with no group...
            if ($emarker === 'Cluster' && fetch($eclus['cgroup']) !== '') {  
                $clusltr = $eclus['cgroup'];
                $clusnme = $eclus['cname'];
                if (strlen($clusltr) === 1) {
                    if (!memberPresent($clusnme, $singles)) {
                        $singles[$clusltr] = $clusnme;
                    }
                } elseif (strlen($clusltr) === 2) {
                    if (!memberPresent($clusnme, $doubles)) {
                        $doubles[$clusltr] = $clusnme;
                    }
                } else {
                    die(
                        "Clusters of length " . strlen($clusltr)
                        . "not supported at this time"
                    );
                }
            }
        }
    }
    /**
     * For debugging, it's easier to understand if keys are sorted;
     * unfortunately, even with various flags, double letters get sorted by their
     * first letter, and so must be separated then combined after sorting.
     */
    ksort($singles);
    ksort($doubles);
    $clusters = array_merge($singles, $doubles);
    return $clusters;
}
/**
 * This function is used in conjunction with dropdownData() to determine
 * whether or not a db item is already accounted for in the group of 
 * uniquely asigned cluster items - an associative array.
 * 
 * @param string $test_item  The item to look for in the specified array
 * @param array  $test_array The array in which to look for the item
 * 
 * @return boolean  true if present, false if not
 */
function memberPresent($test_item, $test_array)
{
    reset($test_array);
    while ($item = current($test_array)) {
        if ($item == $test_item) {
            return true;
        }
        next($test_array);
    }
    return false;
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
/**
 * This function extracts the lats, lngs, and elevs from a gpx file,
 * and returns them as arrays. It also creates a json file for use in javascript,
 * if and only if a single track is requested from the caller.
 * NOTE: if there are multiple segments within a track, they are essentially
 * combined into one seqment.
 * 
 * @param string $gpxfile      The (full or relative) path to the gpx file
 * @param string $no_of_tracks Return data for number of tracks specified (or all)
 * 
 * @return array $stuff
 */
function gpxLatLng($gpxfile, $no_of_tracks)
{
    $gpxlats = [];
    $gpxlons = [];
    $gpxelev = [];
    $plat = 0;
    $plng = 0;
    // get file as simple xml
    $gpxdat = simplexml_load_file($gpxfile);
    if ($gpxdat === false) {
        die(
            __FILE__ . "Line " . __LINE__ . "Could not load gpx file as " .
            "simplexml; Please contact Site Master"
        );
    }
    // If file happens to contain routes instead of tracks, convert:
    if ($gpxdat->rte->count() > 0) {
        $gpxdat = convertRtePts($gpxdat);
    }
    if ($no_of_tracks === 'all') {
        $trkcnt = $gpxdat->trk->count();
    } else {
        $trkcnt = intval($no_of_tracks);
    }
    for ($i=0; $i<$trkcnt; $i++) {
        foreach ($gpxdat->trk[$i]->trkseg as $trackdat) {
            foreach ($trackdat->trkpt as $datum) {
                if (!( $datum['lat'] === $plat && $datum['lon'] === $plng )) {
                    $plat = $datum['lat'];
                    $plng = $datum['lon'];
                    array_push($gpxlats, (float)$plat);
                    array_push($gpxlons, (float)$plng);
                    $meters = $datum->ele;
                    $feet = round(3.28084 * $meters, 1);
                    array_push($gpxelev, $feet);
                }
            }
        }
        if ($trkcnt === 1) {
            $jdat = '[';   // array of objects
            for ($n=0; $n<count($gpxlats); $n++) {
                $jdat .= '{"lat":' . $gpxlats[$n] . ',"lng":' . $gpxlons[$n] . '},';
            }
            $jdat = substr($jdat, 0, strlen($jdat)-1);
            $jdat .= ']';
            return array($gpxlats, $gpxlons, $gpxelev, $jdat);
        }
    }
    return array($gpxlats, $gpxlons, $gpxelev);
}
/**
 * Function to calculate the distance between two lat/lng coordinates.
 * In addition, the 'rotation' angle is calculated which provides the correct
 * oritentation on the page for key elements, like tick marks on the track.
 * 
 * @param float $lat1 starting latitude
 * @param float $lon1 starting longitude
 * @param float $lat2 ending latitude
 * @param float $lon2 ending longitude
 * 
 * @return array
 */
function distance($lat1, $lon1, $lat2, $lon2)
{
    if ($lat1 === $lat2 && $lon1 === $lon2) {
        return array (0,0);
    }
    $radlat1 = deg2rad($lat1);
    $radlat2 = deg2rad($lat2);
    $theta = $lon1 - $lon2;
    $dist = sin($radlat1) * sin($radlat2) +  cos($radlat1) *
        cos($radlat2) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $dist = $dist * 40075000 / 360; // circumference in meters / 360 degrees
    if (is_nan($dist)) {
        $err = $lat1 . ',' . $lon1 . '; ' . $lat2 . ',' . $lon2;
        echo $GLOBALS['intro'] .
            "Mdl: makeGpsv.php/function distance() - Not a number: " . $err . "</p>";
    }
    // angles using planar coords: ASSUME a minute/seconds in lat/lng spec
    $dely = $lat2 - $lat1;
    $delx = $lon2 - $lon1;
    $radang = atan2($dely, $delx);
    $angle = rad2deg($radang);
    // Convert Euclid Angle to GPSV Rotation
    if ($dely >= 0) {
        if ($delx >= 0) {
            $rotation = 90.0 - $angle;  // Northeast
        } else {
            $rotation = 450.0 - $angle; // Northwest
        }
    } else {
        $rotation = 90.0 + -$angle;     // South
    }
    $rotation = round($rotation);
    return array ($dist,$rotation);
}
/**
 * This module will replace all occurances of <rtept> in a gpx
 * file with <trkpt> tags.
 * 
 * @param string $rtefile simpleXML file object from gpx file
 * 
 * @return string $newfile with trkpt tags instead of rtepts
 */
function convertRtePts($rtefile)
{
    $oldroute = $rtefile->asXML();
    $nxt = 0;
    // for all <rte> tags:
    while ($rteLoc = strpos($oldroute, "<rte>", $nxt) !== false) {
        // insert <trackseg>; NOTE: intervening lines between <rte> & <rtept>
        $firstRtePt = strpos($oldroute, "<rtept", $rteLoc);
        $remlgth = strlen($oldroute) - $firstRtePt;
        $remndr = substr($oldroute, $firstRtePt, $remlgth);
        $new = substr($oldroute, 0, $firstRtePt);
        $new .= "<trkseg>\n\t";
        $oldroute = $new . $remndr;
        $nxt = strpos($oldroute, "<trkseg>");
    }
    $step1 = str_replace("<rte>", "<trk>", $oldroute);
    $step2 = str_replace("rtept", "trkpt", $step1);
    $step3 = str_replace("</rte>", "</trkseg>\n</trk>", $step2);
    $trkPtFile = simplexml_load_string($step3);
    return($trkPtFile);
}
/**
 * This generator extracts and yields elevation, latitude and longitude info 
 * from the target gpxfile, represented as a simpleXMLElement. 
 * 
 * @param simpleXMLElement $gpxobj The name of the object representing the gpx file
 * @param integer          $trkno  The number of the currently parsing track in gpx
 * 
 * @return array $latLngDat
 */
function genLatLng($gpxobj, $trkno)
{
    $segs =  $gpxobj->trk[$trkno]->trkseg->count();
    for ($i=0; $i<$segs; $i++) {
        $seg = $gpxobj->trk[$trkno]->trkseg[$i];
        foreach ($seg->trkpt as $geodat) {
            $meters = floatval($geodat->ele);
            $geo[0] = floatval($geodat['lat']);
            $geo[1] = floatval($geodat['lon']);
            $geo[2] = 3.28084 * $meters; // feet
            if ($meters > 0.5) {
                 yield $geo;
            }
        }
    }
}
/**
 * This function will resize an image and store it in the target_dir
 * as specified by the code and incoming file name.
 * 
 * @param string  $targ_fname     Target file name
 * @param string  $org_file       File contents of original image
 * @param integer $new_img_width  Resize width of image
 * @param integer $new_img_height Resize height of image
 * @param boolean $rotated        Is image rotated? T/F
 * 
 * @return string $target_file New resized filepath
 */
function storeUploadedImage($targ_fname, $org_file, $new_img_width,
    $new_img_height, $rotated
) {
    $target_dir = "../tmp/";
    $target_file = $target_dir . $targ_fname;
    $image = new \claviska\SimpleImage();
    $image->fromFile($org_file);
    $image->autoOrient();
    /*
     * rotation is already done in caller...
    if ($rotated) {
        $tmp = $new_img_height;
        $new_img_height = $new_img_width;
        $new_img_width = $tmp;
    }
    */
    $image->resize($new_img_width, $new_img_height);
    $image->toFile($target_file);
    // return name of saved file in case you want to store it 
    // in your database or show confirmation message to user
    return $target_file;
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
