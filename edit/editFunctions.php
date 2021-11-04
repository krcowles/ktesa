<?php
/**
 * This file contains function declarations designed to be used
 * by modules performing page editing. At this time, there are also
 * some instances called by makeGpsv.php.
 * PHP Version 7.4
 * 
 * @package Ktesa
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
 * Multiple places require uploading a gpx, kml, or html file. Any other
 * type results in a user alert. Only one file at a time may be uploaded.
 * 
 * @param PDO     $pdo    The PDO class object for EHIKES, ETSV
 * @param PDO     $gdb    The PDO class object for EMETA, EGPX
 * @param string  $name   The 'name' of the file: <input id="name">
 * @param string  $hikeno The EHIKE indxNo
 * @param boolean $init   Reset alerts if true, Accumulate if false
 * @param boolean $elev   Test for elevation data if true, ignore otherwise
 * @param boolean $getwpt If waypoints are to be extracted and recorded in ETSV 
 * 
 * @return string array with new fileno and location of server data for file
 */
function uploadGpxKmlFile(
    $pdo, $gdb, $name, $hikeno, $init, $elev=false, $getwpt=true
) {
    $user_ip = getIpAddress();  // for forming a unique upload name
    $_SESSION['user_alert'] = $init ? '' : $_SESSION['user_alert'];
    // first, validate the file type (gpx, kml or html)
    $valid = validateUpload($name, $elev);
    if (empty($valid['file'])) {
        $_SESSION['user_alert'] .= "No file specified";
        return array('0', '', '', '');
    } elseif ($_SESSION['user_alert'] !== '') {
            return array('0', '', '', $valid['type']);
    }
    $tmp_loc = $valid['loc'];
    // No errors so far...
    $newfileno = '0';
    $barefile = pathinfo($valid['file'], PATHINFO_FILENAME);
    $unique_file_name = $barefile . "-" . $user_ip . "-" . time() .
        "." . $valid['type'];
    if ($valid['type'] === 'gpx') {
        // For gpx files, save to EMETA & EGPX, get fileno to return
        $newfileno = loadGPXdb(
            $hikeno, $unique_file_name, $valid['loc'], $pdo, $gdb, $getwpt
        );
    } elseif ($valid['type'] !== 'unknown') { // kml or html
        $dir = $valid['type'] === 'kml' ?  "../gpx/"  : "../maps/";
        $floc = $dir . $unique_file_name;
        move_uploaded_file($tmp_loc, $floc);
    } else {
        return array('0', '', '', 'unknown');
    }
    if (isset($_SESSION['uplmsg'])) {
        $_SESSION['uplmsg'] .= "Your file [{$valid['file']}] was saved as " .
            $unique_file_name . "; ";
    } 
    return array($newfileno, $tmp_loc, $unique_file_name, $valid['type']);
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
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $file_ext = strtolower($ext);
    $uploadType = 'none';
    if (!empty($filename)) {
        $tmp_upload = $_FILES[$name]['tmp_name'];    
        $filestat = $_FILES[$name]['error'];
        if ($filestat !== UPLOAD_ERR_OK) {
            $_SESSION['user_alert'] .= " Server error: " .
                "Failed to upload {$filename}: " . uploadErr($filestat);
        } else {
            if ($file_ext === 'gpx') {
                $uploadType = 'gpx';
                // make sure xml loads and passes gpx file schema
                validateGpx($tmp_upload, $filename, $elev);
            } else {
                $filetype = $_FILES[$name]['type'];
                $uploadType = validateType($filetype);  // kml, html ok
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
    case "application/vnd.google-earth.kml+xml": // add Google Earth - KML
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
 * Load the server file data into the EMETA & EGPX tables;
 * NOTE: Processes one ($file_name) file
 * 
 * @param string  $hikeno     The EHIKE's indxNo
 * @param string  $file_name  The uploaded unique file name
 * @param string  $server_loc The location of the server's temp data
 * @param PDO     $pdo        The class object for the EHIKES table
 * @param PDO     $gdb        The class object for the EMETA/EGPX tables
 * @param boolean $wpt        Whether or not to record waypts in ETSV
 * 
 * @return string the new fileno representing the loaded gpx data
 */
function loadGPXdb($hikeno, $file_name, $server_loc, $pdo, $gdb, $wpt)
{
    $gpxarray = file($server_loc);
    if ($gpxarray === false) {
        throw new Exception("Failed to load server contents for {$file_name}");
    }
    $gpxhead = '';
    foreach ($gpxarray as $line) {
        if (strpos($line, "<trk>") === false) {
            $gpxhead .= $line;
        } else {
            break;
        }
    }
    $gpxarray = null;
    $gpx = simplexml_load_file($server_loc);
    if ($gpx === false) {
        throw new Exception("Could not load {$file_name} as xml");
    }
    if ($wpt && $gpx->wpt->count() > 0) {
        extractWayPts($hikeno, $gpx, $pdo);
    }
    // get the current highest fileno
    $fileno = 1;
    $lastFilenoReq = "SELECT `fileno` FROM `EMETA` ORDER BY `fileno` " .
        "DESC LIMIT 1;";
    $lastFileno = $gdb->query($lastFilenoReq);
    $last = $lastFileno->fetch(PDO::FETCH_NUM); // no data => false
    if ($last !== false) {
        $fileno = $last[0] + 1;
    } 
    // Extract any track extensions
    $gpx_string = file_get_contents($server_loc);
    $trkcnt = substr_count($gpx_string, "<trk>");
    $offset = 0;
    for ($i=1; $i<=$trkcnt; $i++) {
        $pos = strpos($gpx_string, "<trk", $offset) + 5;
        // Note: <trkseg> is not required and may not be present...
        if (strpos($gpx_string, "<trkseg") === false) {
            $end = strpos($gpx_string, "<trkpt", $offset);
        } else {
            $end = strpos($gpx_string, "<trkseg", $offset);
        }
        $lgth = $end - $pos;
        $offset = strpos($gpx_string, "</trk>", $offset+50);
        // Without 'trim' below, field data cannot be retrieved!
        $trkext = trim(substr($gpx_string, $pos, $lgth));
        $name = $gpx->trk[$i-1]->name->__toString();
        // If $name looks like a timestamp:
        $dtime = explode(" ", $name);
        $dte   = explode("-", $dtime[0]);
        if (count($dte) === 3) {
            if (strlen($dte[0]) === 4 
                && strlen($dte[1]) === 2 && strlen($dte[2]) === 2
            ) {
                // $name is a date format
                $name = substr($file_name, 0, 7) . '_' . $i;
            }
        }
        // no length, etc. yet...
        $saveDataReq = "INSERT INTO `EMETA` (`fname`,`fileno`,`meta`," .
            "`trkno`,`trkext`,`trkname`) VALUES (?,?,?,?,?,?);";
        $saveData = $gdb->prepare($saveDataReq);
        $saveData->execute(
            [$file_name, $fileno, $gpxhead, $i, $trkext, $name]
        );
    }
    $gpx_string = null;
    /**
     * Load the track data for each <trk>'s <trkpt> into the GPX table
     * NOTE: trkpts with no <ele> are not written out, and message can
     * be printed at end showing affected files
     */
    $trkno = 1; 
    foreach ($gpx->trk as $track) {
        $noEles = 0;
        $segno = 1;
        // some files have no trkseg
        if ($track->trkseg->count() !== 0) {
            foreach ($track->trkseg as $seg) {
                foreach ($seg->trkpt as $row) {
                    if (!writeGPSData($fileno, $trkno, $segno, $row, $gdb)) {
                        $noEles++;
                    }
                }
                $segno++;
            }
        } else {
            foreach ($track->trkpt as $row) {
                if (!writeGPSData($fileno, $trkno, $segno, $row, $gdb)) {
                    $noEles++;
                }
            }
        }
        $trkno++;
    }
    if ($file_name !== 'filler.gpx') {
        getTrkStats($fileno, $gdb);
    }
    return $fileno;
}

/**
 * This function will add data to the ETSV table for the gpx file specified
 * First, the ETSV table is checked to see if waypoints already exist, and
 * if they do (and are not duplicates), then the gpx waypoints are appended.
 * Since names and symbols may be changed during edit, but probably not the
 * lats/lngs, the latter is checked for duplicate entries.
 * 
 * @param integer          $hikeno  The file # associated with the waypoints
 * @param simpleXMLElement $xmlfile The file loaded as simpleXMLElement
 * @param PDO              $pdo     The PDO class for the TSV table
 * 
 * @return null;
 */
function extractWayPts($hikeno, $xmlfile, $pdo)
{
    // retrieve any existing waypt data for this hikeno
    $etsvdatReq = "SELECT `lat`,`lng` FROM `ETSV` WHERE `indxNo`={$hikeno};";
    $etsvdat = $pdo->query($etsvdatReq)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($xmlfile->wpt as $waypt) {
        $wlat  = floor($waypt['lat'] * LOC_SCALE);
        $wlon  = floor($waypt['lon'] * LOC_SCALE);
        $wname = $waypt->name->__toString();
        $wsym  = $waypt->sym->__toString();
        $add = true;
        foreach ($etsvdat as $twpt) {
            if ($wlat == $twpt['lat'] && $wlon == $twpt['lng']) {
                $add = false;
                break;
            }
        }
        if ($add) {
            $wptSaveReq = "INSERT INTO `ETSV` (`indxNo`,`title`,`mpg`,`lat`,`lng`," .
                "`iclr`) VALUES (?,?,?,?,?,?);";
            $wptSave = $pdo->prepare($wptSaveReq);
            $wptSave->execute(
                [$hikeno, $wname, 'Y', $wlat, $wlon, $wsym]
            );
        }
    }
}
/**
 * A function to extract GPS data from simplexml element and write to GPX table
 * (Created to avoid code duplication)
 * 
 * @param number           $fileno The file no in the db for the subject gpx file
 * @param number           $trkno  The track number being processed 
 * @param number           $segno  The <trkseg> being processed
 * @param simpleXMLElement $row    The <trkpt> tag data
 * @param PDO              $gdb    The PDO class instantiated for the GPX table
 * 
 * @return boolean
 */
function writeGPSData($fileno, $trkno, $segno, $row, $gdb)
{
    $lat = $row['lat']->__toString();
    $lon = $row['lon']->__toString();
    $ele = null;
    $time = null;
    if ($row->count() > 0) {
        foreach ($row->children() as $child) {
            $name = $child->getName();
            if ($name === 'ele') {
                $ele = $child->__toString();
            }
            if ($name === 'time') {
                $time = $child->__toString();
                $tim  = str_replace('T', ' ', $time);
                $time = str_replace('Z', '', $tim);
            }
        }
    }
    if (empty($ele)) { // don't write a <trkpt> with no elevation data
        return false;
    } else {
        $addRowReq = "INSERT INTO `EGPX` (`fileno`,`trackno`," .
            "`segno`,`lat`,`lon`,`ele`,`time`) " .
            "VALUES (?,?,?,?,?,?,?);";
        $addRow = $gdb->prepare($addRowReq);
        $addRow->execute(
            [$fileno, $trkno, $segno, $lat, $lon, $ele, $time]
        );
        return true;
    }
}
/**
 * Create the track data for the specified fileno
 * 
 * @param string $fileno The fileno in the GPX database for the file
 * @param PCO    $gdb    The PDO class for EGPX/EMETA
 * 
 * @return null;
 */
function getTrkStats($fileno, $gdb)
{
    $getTracks = "SELECT `trkno` FROM `EMETA` WHERE `fileno`={$fileno} " .
        "ORDER BY `trkno` DESC LIMIT 1;";
    $noOfTracks = $gdb->query($getTracks)->fetch(PDO::FETCH_NUM);
    $trkcount = $noOfTracks[0];
    for ($k=1; $k<=$trkcount; $k++) {
        $getData = "SELECT `lat`,`lon`,`ele` FROM `EGPX` WHERE `fileno`=? " .
            "AND `trackno`=?;";
        $gpsdata = $gdb->prepare($getData);
        $gpsdata->execute([$fileno, $k]);
        $gps = $gpsdata->fetchAll(PDO::FETCH_ASSOC);
        // in case of a missing fileno
        if ($gps !== false) {
            $length = (float) 0;
            $maxele = (float) 0;
            $minele = (float) 100000;
            $asc = 0;
            $dsc = 0;
            for ($i=0; $i<count($gps)-1; $i++) {
                $calcs = distance(
                    floatval($gps[$i]['lat']), floatval($gps[$i]['lon']), 
                    floatval($gps[$i+1]['lat']), floatval($gps[$i+1]['lon'])
                );
                $length += $calcs[0];
                $maxele = floatval($gps[$i]['ele']) > $maxele ? 
                    floatval($gps[$i]['ele']) : $maxele;
                $minele = floatval($gps[$i]['ele']) < $minele ? 
                    floatval($gps[$i]['ele']) : $minele;
                $delta = round($gps[$i+1]['ele'], 2) - round($gps[$i]['ele'], 2);
                if ($delta < 0) {
                    $dsc -= $delta;
                } else {
                    $asc += $delta;
                }
            }
            // convert from meters to feet
            $min2max   = ($maxele - $minele) * 3.2808;
            $asc       = round($asc*3.2808);
            $dsc       = round($dsc*3.2808);
            $length    = ($length * 3.2808)/5280;
            $dbmin2max = round($min2max);
            $dblength  = round($length, 2);

            $add2dbReq = "UPDATE `EMETA` SET `length`=?,`min2max`=?,`asc`=?," .
                "`dsc`=? WHERE `fileno`=? AND `trkno`=?;";
            $add2db = $gdb->prepare($add2dbReq);
            $add2db->execute([$dblength, $dbmin2max, $asc, $dsc, $fileno, $k]);
        }
    }
}
/**
 * This function will create a JSON track file from the specified gpx.
 * 
 * @param string $tmpdata The filepath the server data
 * @param string $fname   The unique file name
 * 
 * @return array JSON file made from target gpx file, lat & lng of 
 * track starting point
 */
function makeTrackFile($tmpdata, $fname) 
{
    $ext = strrpos($fname, ".");
    $base = substr($fname, 0, $ext);
    $trkfile = $base . ".json";
    $trkloc = '../json/' . $trkfile;
    $gpxfile = simplexml_load_file($tmpdata);
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
 * the map. This will only happen in edit mode, so the data is stored in the
 * EGPX and EMETA tables. Once published, any associated pseudo files will be
 * removed.
 * 
 * @param string $hikeno The EHIKES indxNo
 * @param float  $clat   Map center latitude
 * @param float  $clng   Map center longitude
 * @param PDO    $pdo    The PDO Class for EHIKES
 * @param PDO    $gdb    The PDO Class for the EGPX/EMETA tables
 * 
 * @return string $pseudoFile The fileno in EGPX/EMETA where the file is located
 */
function createPseudoGpx($hikeno, $clat, $clng, $pdo, $gdb)
{
    // Prevent duplication:
    $emeta_name = "E{$hikeno}_filler.gpx";
    $pseudo_loc = '../gpx/filler.gpx';
    $filenum    = ''; // default
    $checkReq = "SELECT `fileno` FROM `EMETA` WHERE `fname`=?;";
    $dupPrep  = $gdb->prepare($checkReq);
    $dupPrep->execute([$emeta_name]);
    $dupcheck = $dupPrep->fetch(PDO::FETCH_NUM);
    if ($dupcheck === false) { // no dup
        $pseudo = simplexml_load_file("../edit/pseudo.gpx"); // default file
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
        $pseudo->asXML($pseudo_loc);  // tmp save in gpx directory
        $filenum = loadGPXdb($hikeno, $emeta_name, $pseudo_loc, $pdo, $gdb, false);
        unlink($pseudo_loc);
    } else {
        $filenum = $dupcheck[0];
    }
    return $filenum;
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
 * @param SimpleXMLElement $gpxdat       simplexml version of server's tmpdat    
 * @param string           $no_of_tracks Return data for number of tracks 
 *                                       specified (or all)
 * 
 * @return array $track_data
 */
function gpxLatLng($gpxdat, $no_of_tracks)
{
    $gpxlats = [];
    $gpxlons = [];
    $gpxelev = [];
    $plat = 0;
    $plng = 0;
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
/**
 * A simple comparison function for photo sequencing
 * 
 * @param string $a Org field within the array for a-th element
 * @param string $b Org field within the array for b-th element
 * 
 * @return integer
 */
function cmp($a, $b)
{
    $delta = intval($a["org"]) - intval($b["org"]);
    return  $delta;
}
/**
 * The following function will accept a $target fileno to use to store
 * data into the corresponding $action tables where that data is selected 
 * from the originating tables (using their corresponding fileno: $fromno).
 * 
 * @param string $action Specifies originating and receiving tables
 * @param string $target Fileno for the receiving tables
 * @param string $fromno Fileno for the originating tables
 * @param PDO    $gdb    The GPX database class
 * 
 * @return null
 */
function xfrGpxData($action, $target, $fromno, $gdb)
{
    $FromMetaTable = $action === 'xfr' ? 'META' : 'EMETA';
    $ToMetaTable   = $action === 'xfr' ? 'EMETA' : 'META';
    $FromGpxTable  = $action === 'xfr' ? 'GPX' : 'EGPX';
    $ToGpxTable    = $action === 'xfr' ? 'EGPX' : 'GPX';

    $meta = "INSERT INTO {$ToMetaTable} (`fname`,`fileno`,`meta`,`trkno`,`trkext`," .
        "`trkname`,`length`,`min2max`,`asc`,`dsc`) SELECT `fname`,?,`meta`," .
        "`trkno`,`trkext`,`trkname`,`length`,`min2max`,`asc`,`dsc` FROM " .
        "{$FromMetaTable} WHERE `fileno`=?;";
    $putMeta = $gdb->prepare($meta);
    $putMeta->execute([$target, $fromno]);
    $gpx  = "INSERT INTO {$ToGpxTable} (`fileno`,`trackno`,`segno`,`lat`,`lon`," .
        "`ele`,`time`) SELECT ?,`trackno`,`segno`,`lat`,`lon`,`ele`,`time` FROM ".
        "{$FromGpxTable} WHERE `fileno`=?;";
    $putGPX = $gdb->prepare($gpx);
    $putGPX->execute([$target, $fromno]);
    return;
}
/**
 * Delete all [E]META and [E]GPX data for the specified tables and fileno
 * and if any waypoints were included in [E]TSV, delete them for the
 * corresponding hikeno
 * 
 * @param string $type   Whether [E]DATA or DATA
 * @param PDO    $gdb    The PDO class of the GPX db
 * @param string $fileno Fileno of data to be deleted
 * 
 * @return null
 */
function deleteGpxData($type, $gdb, $fileno)
{
    $targetGPX  = $type === 'pub' ? 'GPX' : 'EGPX';
    $targetMETA = $type === 'pub' ? 'META' : 'EMETA';
    $deletionMReq = "DELETE FROM {$targetMETA} WHERE `fileno`=?;";
    $deleteMeta = $gdb->prepare($deletionMReq);
    $deleteMeta->execute([$fileno]);
    $deletionGReq = "DELETE FROM {$targetGPX} WHERE `fileno`=?;";
    $deleteGpx = $gdb->prepare($deletionGReq);
    $deleteGpx->execute([$fileno]);
    return;
}
