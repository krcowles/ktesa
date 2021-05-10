<?php
/**
 * This file contains function declarations designed to be used
 * by modules performing page editing. At this time, there are also
 * some instances called by makeGpsv.php.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
libxml_use_internal_errors(true);

/**
 * The following function gets the visitor's machine IP even when going through
 * a proxy server. Copied from:
 * https://www.w3adda.com/blog/how-to-get-user-ip-address-in-php
 * 
 * @return string $ip user's machine IP address
 */
function getIpAddress()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
/**
 * Multiple places require uploading a gpx (or possibly a kml file). 
 * Only one file at a time may be uploaded.
 * 
 * @param string  $name <input type="file" name="$name" />
 * @param boolean $init Reset alerts if true, Accumulate if false
 * @param boolean $elev Test for elevation data if true, ignore otherwise 
 * 
 * @return string Location of file save, otherwise all msgs are in SESSION
 */
function uploadGpxKmlFile($name, $init, $elev=false)
{
    $user_ip = getIpAddress();
    $_SESSION['user_alert'] = $init ? '' : $_SESSION['user_alert'];
    // first, validate the file as as <gpx> or <kml>
    $valid = validateUpload($name, $elev);
    if (empty($valid['file'])) {
        $_SESSION['user_alert'] .= "No file specified";
        return 'none';
    } else {
        if ($_SESSION['user_alert'] !== '' && $init) {
            return;
        }
    }
    if ($valid['type'] === 'gpx' || $valid['type'] === 'kml') {
        $file_ext = $valid['type'] === 'gpx' ? '.gpx' : '.kml';
        $barefile = pathinfo($valid['file'], PATHINFO_FILENAME);
        $unique_file_name = $barefile . "-" . $user_ip . "-" . time() . $file_ext;
        $saveloc = "../gpx/" . $unique_file_name;
        if (!move_uploaded_file($valid['loc'], $saveloc)) {
            $nomove = "Could not save {$valid['file']} to site: contact Site Master";
            throw new Exception($nomove);
        } else {
            $_SESSION['uplmsg'].= "Your file [{$valid['file']}] was saved as " .
                $unique_file_name . "; ";
        }
    } else {
        $saveloc = '';
        $_SESSION['user_alert'] .= " Incorrect file type: not gpx or kml; ";
    }
    return $saveloc;
}
/**
 * This function validates the uploaded file against currently allowed types.
 * Errors encountered are communicated via session variable 'user_alert'.
 * 
 * @param string  $name <input type="file" name="$name" />
 * @param boolean $elev Test for elevation data if true, ignore otherwise
 * 
 * @return array The client filename that was uploaded & server location
 */
function validateUpload($name, $elev=false)
{
    $filename = basename($_FILES[$name]['name']);
    $uploadType = 'none';
    if (!empty($filename)) {
        $tmp_upload = $_FILES[$name]['tmp_name'];    
        $filestat = $_FILES[$name]['error'];
        if ($filestat !== UPLOAD_ERR_OK) {
            $_SESSION['user_alert'] .= " Server error: " .
                "Failed to upload {$filename}: " . uploadErr($filestat);
        } else {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (strtolower($ext) === 'gpx') {
                $uploadType = 'gpx';
                validateGpx($tmp_upload, $filename, $elev);
            } else { 
                $filetype = $_FILES[$name]['type'];
                $uploadType = validateType($filetype);
                if ($uploadType === 'unknown') {
                    $_SESSION['user_alert'] .= " Incorrect file type for upload; ";
                }
            }
        }
    } else {
        $tmp_upload = '';
    }
    return array("file" => $filename, "type" => $uploadType, "loc" => $tmp_upload);
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
 * This function attempts to qualify the mime type against the whitelist:
 * The whitelist for extensions is currently: .gpx (.GPX), .kml, and .html
 * Unfortunately, octet-stream applies to many file types, but non-gpx will
 * fail later on when gpx validation occurs.
 * 
 * @param string $filetype $_FILES['type'] value from upload
 * 
 * @return string $uplType (file mime type) and $floc (site path for storage)
 */
function validateType($filetype)
{
    switch ($filetype) {
    case "text/html":
        $usertype = 'html';
        break;
    case "application/vnd.google-earth.kml+xml": // add Google Earth - KML  ??
        $usertype = 'kml';
        break;
    default;
        $usertype = 'unknown';
    }
    return $usertype;
}
/**
 * This function will validate the basic file formatting for a (gpx) file.
 * If an error occurred, $_SESSION['user_alert'] will retain error.
 * 
 * @param string  $file     The path to the file to be validated
 * @param string  $filename The name of the file
 * @param boolean $etest    Test for elevation data if true
 * 
 * @return null;
 */
function validateGpx($file, $filename, $etest)
{
    $dom = new DOMDocument;
    if (!$dom->load($file)) {
        displayGpxUserAlert($filename);
        return;
    }
    if (!$dom->schemaValidate(
        "http://www.topografix.com/GPX/1/1/gpx.xsd", LIBXML_SCHEMA_CREATE
    )
    ) {
        displayGpxUserAlert($filename);
        return;
    }
    if ($etest) {
        $elevs = $dom->getElementsByTagName('ele');
        if ($elevs->length === 0) {
            $_SESSION['user_alert'] .= " {$filename} cannot be used " .
                "without elevation data";
        }
    }
    return;
}
/**
 * Display a message for the user about the gpx file failure encountered
 * 
 * @param string $filename The gpx file containing the error
 * 
 * @return null
 */
function displayGpxUserAlert($filename)
{
    $err_array = libxml_get_errors();
    $usr_msg = "There is an error in {$filename}:\n" .
        displayXmlError($err_array[0]);
    $_SESSION['user_alert'] .= $usr_msg;
    return;
}
/**
 * The libxml errors have their own error processing requiring a handler,
 * specified in this function routine.
 * 
 * @param object $error libxml object when error occurs
 * 
 * @return string $return error string to return
 */
function displayXmlError($error) 
{
    $return = '';
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
    $return .= trim($error->message) .  " Line: $error->line" .
        ", Column: $error->column";
    return $return;
}

/**
 * This function will create a JSON track file from the specified gpx.
 * 
 * @param string $gpxfile The filepath for the file to be converted.
 * 
 * @return array JSON file made from target gpx file, lat & lng of 
 * track starting point
 */
function makeTrackFile($gpxfile) 
{
    $basename = basename($gpxfile);
    $ext = strrpos($basename, ".");
    $base = substr($basename, 0, $ext);
    $trkfile = $base . ".json";
    $trkloc = '../json/' . $trkfile;
    $gpxdat = gpxLatLng($gpxfile, "1");
    $trklat = $gpxdat[0][0];
    $trklng = $gpxdat[1][0];
    $trk = fopen($trkloc, "w");
    $dwnld = fwrite($trk, $gpxdat[3]);
    if ($dwnld === false) {
        $trkfail =  "editFunctions.php: Failed to write out {$trkfile} " .
            "[length: " . strlen($jdat) . "]; Please contact Site Master";
        throw new Exception($trkfail);
    } 
    fclose($trk);
    return array($trkfile, $trklat, $trklng);
}
/**
 * This function extracts existing cluster info from the CLUSTERS table
 * needed to display the 'select' drop-down boxes for the editor.
 * 
 * @param PDO $pdo PDO object for db access
 *
 * @return $select html text for a select box containing clusters
 */
function getClusters($pdo)
{
    $select = '<select id="clusters" name="clusters">' . PHP_EOL;
    $clus_req = "SELECT `group` FROM `CLUSTERS`;";
    $clusters = $pdo->query($clus_req)->fetchAll(PDO::FETCH_COLUMN);
    foreach ($clusters as $cluster) {
        $select .= '<option value="' . $cluster . '">' . $cluster .
            '</option>' . PHP_EOL;
    }
    $select .= '</select>' . PHP_EOL;
    return $select;
}
/**
 * When there is no gpxfile, a pseudo-gpx file is created for display on
 * the map.
 * 
 * @param float  $clat    Map center latitude
 * @param float  $clng    Map center longitude
 * @param string $gpxfile Gpx file name
 * @param array  $files   All gpx files associated
 * 
 * @return null
 */
function createPseudoGpx($clat, $clng, &$gpxfile, &$files)
{
    $pseudo = simplexml_load_file("../edit/pseudo.gpx");
    if ($pseudo === false) {
        throw new Exception("Couldn't load pseudo.gpx");
    }
    $y = $pseudo->trk->trkseg[0];
    $y->trkpt[0]['lat'] = $clat;
    $y->trkpt[0]['lon'] = $clng;
    $y->trkpt[1]['lat'] = $clat + .004507;
    $y->trkpt[1]['lon'] = $clng;
    $y->trkpt[2]['lat'] = $clat - .004507;
    $y->trkpt[2]['lon'] = $clng;
    $y->trkpt[3]['lat'] = $clat;
    $y->trkpt[3]['lon'] = $clng;
    $y->trkpt[4]['lat'] = $clat;
    $y->trkpt[4]['lon'] = $clng - .005477;
    $y->trkpt[5]['lat'] = $clat;
    $y->trkpt[5]['lon'] = $clng + .005477;
    $pseudo->asXML("../gpx/filler.gpx");
    $gpxfile = "filler.gpx";
    $files = [$gpxfile];
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
 * @return array $track_data
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
        throw new Exception(
            __FILE__ . "Line " . __LINE__ . "Could not load {$gpxfile} as " .
            "simplexml"
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

    // avoid rounding error causing acos to return nan. See:
    // https://stackoverflow.com/questions/37184259/acos1-returns-nan-in-some-conditions
    $dist = acos(min(max($dist, -1.0), 1.0));

    $dist = rad2deg($dist);
    $dist = $dist * 40075000 / 360; // circumference in meters / 360 degrees
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
 * This function will insert a new simpleXMLElement after a specified node.
 * See https://stackoverflow.com/questions/3361036/php-simplexml-insert-node-at-certain-position
 * 
 * @param simpleXMLElement $insert node which is to be inserted
 * @param simpleXMLElement $target node after which $insert will be placed
 * 
 * @return DOMNode original parent node
 */
function simplexmlInsertAfter(SimpleXMLElement $insert, SimpleXMLElement $target)
{
    $domTarg = dom_import_simplexml($target);
    if ($domTarg === false) {
        throw new Exception(
            "Function simplexmlInsertAfter failed with attempt to load as dom"
        );
    }
    $domIns = $domTarg->ownerDocument->importNode(
        dom_import_simplexml($insert), true
    );
    return $domTarg->parentNode->insertBefore($domIns, $domTarg);
    /*
    if ($domTarg->nextSibling) {
        return $domTarg->parentNode->insertBefore($domIns, $domTarg);
    } else {
        return $domTarg->parentNode->appendChild($domIns);
    }
    */
}
/**
 * This function will resize an image and store it in the target_dir
 * as specified by the code and incoming file name.
 * 
 * @param string  $targ_fname     Target file name
 * @param string  $org_file       File contents of original image
 * @param integer $new_img_width  Resize width of image
 * @param integer $new_img_height Resize height of image
 * 
 * @return string $target_file New resized filepath
 */
function storeUploadedImage($targ_fname, $org_file, $new_img_width, $new_img_height)
{
    // find the location of the 'pictures' dir in server:
    $picpath = "";
    $current = getcwd();
    $startdir = $current;
    while (!in_array('pictures', scandir($current))) {
        $picpath .= "../";
        chdir('..');
        $current = getcwd();
    }
    $picpath .= "pictures/";
    // return to starting point:
    chdir($startdir);
    $target_dir = $picpath . "zsize/";
    $target_file = $target_dir . $targ_fname;
    $image = new \claviska\SimpleImage();
    $image->fromFile($org_file);
    $image->autoOrient();
    $image->resize($new_img_width, $new_img_height);
    $image->toFile($target_file);
    // return name of saved file in case you want to store it 
    // in your database or show confirmation message to user
    return $target_file;
}
/**
 * Find the location of the 'pictures' directory on the site (it will be situated
 * differently for a test site, for example)
 * 
 * @return string $picdir Relative path to the pictures directory
 */
function getPicturesDirectory()
{
    $picdir = "";
    $current = getcwd();
    $prev = $current;
    while (!in_array('pictures', scandir($current))) {
        $picdir .= "../";
        chdir('..');
        $current = getcwd();
    }
    chdir($prev);
    $picdir .= 'pictures/zsize/';
    return $picdir;
}
