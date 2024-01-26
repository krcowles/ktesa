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
 * If there are non-empty string elements in an array, return
 * them. 
 * 
 * @param array $array The array to be examined for empty values
 * 
 * @return array $set_array
 */
function checkForEmptyArray($array)
{
    return array_filter(
        $array, function ($element) { 
            return  $element !== '';
        }
    );
}
/**
 * For each file upload, an array is established to capture data pertinent to
 * the upload process. The array captures the temporary server data and stores
 * that data to the local 'edit' directory. Locally stored files are temporary
 * and will be deleted when processing is complete.
 * NOTE: When saving tab1, clicking 'Apply' will potentially upload 4 different
 * files; hence 'ureq' indicates whether or not a file upload was specified for
 * the given file type (e.g main, or 1 of 3 additional gpx files); When saving
 * tab4, two possible file uploads are possible - 'ureq' again specifies if a
 * file upload was specified.
 * 
 * @param string $input_file_name Input file name attribute
 * 
 * @return array $upload_data Data pertinent for upload validation and saving
 */
function prepareUpload($input_file_name)
{
    $user_file = $_FILES[$input_file_name]['name'];
    $requested = empty($user_file) ? false : true;
    $error_stat = $requested ? 
        $_FILES[$input_file_name]['error'] : UPLOAD_ERR_NO_FILE;
    $extension = $requested ?
        strToLower(pathinfo($user_file, PATHINFO_EXTENSION)) : '';
    $upload_data = array(
        'ureq' => $requested,
        'ifn'  => $input_file_name,
        'err'  => $error_stat,
        'ufn'  => $requested ? $user_file : '',
        'ext'  => $extension,
        'type' => '',
        'apos' => 0 // default index into user_alerts[]
    );
    if (strpos($input_file_name, "addgpx") !== false) {
        $upload_data['apos'] = intval(substr($input_file_name, -1));
    } elseif ($upload_data['ifn'] === 'newmap') {
            $upload_data['apos'] = 1;
    }
    if ($requested && $upload_data['err'] === UPLOAD_ERR_OK ) {
        $upload_data['type'] = $_FILES[$input_file_name]['type'];
        $tmploc = $input_file_name . "." . $upload_data['ext'];
        move_uploaded_file($_FILES[$input_file_name]['tmp_name'], $tmploc);
    }
    return $upload_data;
}
/**
 * Validate and potentially save an upload file. For gpx files, this means
 * json tracks will need to be created by the caller. No validation occurs
 * for .html or .kml files at this time, beyond verifying the file extension.
 * These are saved, other file types are not. 
 * 
 * @param array   $upload An array of the file upload characteristics
 *                        created by 'prepareUpload()'
 * @param boolean $elev   Test for elevation data if true
 * @param boolean $syms   Test for unsupported symbols if true
 * 
 * @return string $saveloc Where file is saved
 */
function uploadFile($upload, $elev=false, $syms=false)
{
    if (!isset($_SESSION['alerts'])) {
        $_SESSION['alerts'] = ["", "", "", ""];
    }
    if ($upload['err'] !== UPLOAD_ERR_OK) {  // no $tmpfile has been created...
        $_SESSION['alerts'][$upload['apos']] = "Server Error: " .
            "Failed to upload {$upload['ufn']}: " . uploadErr($upload['err']) . "; ";
        return 'none';
    }
    $tmpfile = $upload['ifn'] . '.' . $upload['ext'];
    $allowed = ['gpx'];
    if ($upload['ifn'] === 'newmap') {
        $allowed = ['html', 'pdf'];
    } elseif ($upload['ifn'] === 'newgps') {
        $allowed = ['gpx', 'kml'];
    } 
    if (!in_array($upload['ext'], $allowed)) {
        // remove $tmpfile w/disallowed file extension
        unlink($tmpfile);
        $_SESSION['alerts'][$upload['apos']]
            = "Incorrect file extension specified: {$upload['ufn']}; ";
        return 'none';
    }
    // Only 'allowed' file extensions from here on...
    if ($upload['ext'] === 'gpx') {
        $file_type = 'gpx';
    } else {
        $file_type = validateType($upload['type']); // html, kml, or unknown
    }
    if ($file_type === 'unknown') {
        // remove unknonwn file type's $tmpfile (type not based on file extension)
        unlink($tmpfile);
        $_SESSION['alerts'][$upload['apos']] = "The file type for {$filename} " .
            "is not permitted; ";
        return 'none';
    } 
    $valid = validateUpload(
        $upload['ifn'], $upload['ufn'], $file_type, $upload['apos'], $elev, $syms
    );
    // if any alerts were registered, no upload;
    if ($_SESSION['alerts'][$upload['apos']] !== '') {
        unlink($tmpfile);
        return 'none';
    }
    // also, if a gpx file and symfault detected, no upload
    if ($syms && isset($_SESSION['symfault']) && $file_type === 'gpx'
        && strpos($_SESSION['symfault'], $upload['ifn']) !== false
    ) {
        // $tmpfile exists and will be renamed by resumeUploadGpx
        return 'none';
    }
    if ($file_type === 'html' || $file_type === 'pdf') {
        $saveloc = $valid; // path to saved html/pdf file; $tmpfile renamed
    } elseif ($file_type === 'gpx' || $file_type === 'kml') {
        // gpx could be from tab1 or tab4
        $file_ext = $file_type === 'gpx' ? '.gpx' : '.kml';
        if ($file_type === 'kml') {
            $barefile = pathinfo($upload['ufn'], PATHINFO_FILENAME);
            $user_ip = getIpAddress();
            $unique_file_name = $barefile . "-" . $user_ip . "-" .
                time() . $file_ext;
            $saveloc = "../kml/" . $unique_file_name;
            if (!rename($tmpfile, $saveloc)) {
                throw new Exception("Could not rename {$tmpfile}");
            } 
        } else {
            // gpx returns only $tmpfile, no path
            $saveloc = $tmpfile;
        }
    } 
    /**
     * Tab1 only uploads gpx files, 'uplmsg' is set
     * Tab4 allows gpx, html, or pdf files, 'gpsmsg' is set
     */
    if (isset($_SESSION['uplmsg'])) {
        $_SESSION['uplmsg'] .= "Your file [{$upload['ufn']}] was saved; ";
    } else if (isset($_SESSION['gpsmsg'])) {
        $_SESSION['gpsmsg'] .= "Your file [{$upload['ufn']}] was saved; ";
    }
    return $saveloc;
}
/**
 * Any completed gpx file upload will require 'processing', which means that
 * the corresponding json track files must be created and stored, and 
 * 
 * @param PDO    $pdo    Database connection object
 * @param array  $upload The array holding the pre-processed upload info for 
 *                       the <input> specified
 * @param array  $ifiles The list of input files preprocessed
 * @param array  $jfiles The list of corresponding $org_names file types
 * @param string $hikeNo The EHIKES indxNo
 * 
 * @return array $new_orgdat The associative array to replace current entry
 *                           in $org_names
 */
function processGpx($pdo, $upload, $ifiles, $jfiles, $hikeNo)
{
    $indx = array_search($upload['ifn'], $ifiles);
    $org_key = $jfiles[$indx];
    $tmpfile = $upload['ifn'] . ".gpx";
    $new_orgdat = makeTrackFiles(
        $pdo, $org_key, $upload['ufn'], $tmpfile, $hikeNo
    );
    if ($org_key === 'main') {
        $calcs = getGpxFileStats($tmpfile);
        $newstatsReq
            = "UPDATE `EHIKES` SET `miles`=?,`feet`=? WHERE " .
              "`indxNo`=?;";
        $newstats = $pdo->prepare($newstatsReq);
        $newstats->execute([$calcs[0], $calcs[1], $hikeNo]);
    }
    if (!unlink($tmpfile)) {
        new Exception("Failed to delete temporary gpx {$tmpfile}");
    }
    return [$org_key, $new_orgdat];
}
/**
 * Find the symfault(s) corresponding to the specified input file. This function
 * is only called when the symfault has been corrected and 'resumeUploadGpx' invoked.
 * 
 * @param string $input_file The name attribute for the corrected file
 * 
 * @return null
 */
function resetSymfault($input_file)
{
    $allFaults = $_SESSION['symfault'];
    $fault_list = explode("|", $allFaults);
    $delete_indices = [];
    for ($k=0; $k<count($fault_list); $k++) {
        if (strpos($fault_list[$k], $input_file) !== false) {
            array_push($delete_indices, $k);
        }
    }
    for ($j=count($delete_indices)-1; $j >= 0; $j--) {
        unset($fault_list[$delete_indices[$j]]);
    }
    $resetFaults = implode("|", $fault_list);
    $_SESSION['symfault'] = $resetFaults;
    return;
}
/**
 * This function validates the uploaded file against currently allowed types.
 * Errors encountered are communicated via session variable 'alerts'. For
 * gpx and kml files, this function is called from uploadKtesaFile(), and
 * for html files, it is called directly from saveTab4.php.
 * 
 * @param string  $ifn       Input file name attribute
 * @param string  $filename  User's uploaded file name
 * @param string  $type      Server identified file type 
 * @param string  $alert_pos Index into $_SESSION['alerts'] for alert msg
 * @param boolean $elev      Test for elevation data if true, ignore otherwise
 * @param boolean $symbols   Test for supported waypoint symbols if true
 * 
 * @return array The client filename that was uploaded & server location
 */
function validateUpload($ifn, $filename, $type, $alert_pos, $elev, $symbols)
{
    $tmp_upload = '';
    if ($type === 'gpx') {
        validateGpx(
            $ifn, $alert_pos, $filename, $elev, $symbols
        );
    } elseif ($type === 'html' || $type === 'pdf') { 
        // $type = html || pdf || kml
        $basefilename = pathinfo($filename, PATHINFO_BASENAME);
        $tmpfile = $type === 'html' ? 'newmap.html' : 'newmap.pdf';
        $tmp_upload = uploadHTML($type, $basefilename, $tmpfile);
    }
    return  $tmp_upload; 
}
/**
 * When the user uploads a map on tab4, it will be saved in the 'maps' directory
 * with a unique name. When this function is called, it has already cleared the
 * 'validateUpload' functionality.
 *
 * @param string $filetype   HTML or PDF
 * @param string $basefname  The file name without .html extension 
 * @param string $server_loc The temporary location where the html is stored.
 * 
 * @return string $unique_file_name
 */
function uploadHTML($filetype, $basefname, $server_loc)
{
    $user_ip = getIpAddress();
    $basename = pathinfo($basefname, PATHINFO_FILENAME); // strip extension
    $unique_file_name = $basename . "-" . $user_ip . "-" . time();
    $unique_file_name .= $filetype === 'html' ? '.html' : '.pdf';
    $saveloc = "../maps/" . $unique_file_name;
    if (!rename($server_loc, $saveloc)) {
        $nomove = "Could not save {$basefname} to site: contact Site Master";
        throw new Exception($nomove);
    }
    return $saveloc;
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
    case "application/octet-stream"; // apparently possible for a kml file...
    case "application/vnd.google-earth.kml+xml":
        $usertype = 'kml';
        break;
    case "application/pdf":
        $usertype = 'pdf';
        break;
    default;
        $usertype = 'unknown';
    }
    return $usertype;
}
/**
 * This function will validate the basic file formatting for a (gpx) file.
 * If an error occurred, $_SESSION['alerts'] will retain error.
 *
 * @param string  $input     Input file 'name' attribute 
 * @param string  $alert_pos Index into $_SESSION['alerts'] for msg
 * @param string  $filename  The name of the gpxfile
 * @param boolean $etest     Test for elevation data if true
 * @param boolean $symtest   Test for supported waypoint symbols
 * 
 * @return null; // may set alerts or symfault but returns nothing
 */
function validateGpx($input, $alert_pos, $filename, $etest, $symtest)
{
    $tmpfile = $input . '.gpx';
    $dom = new DOMDocument;
    if (!$dom->load($tmpfile)) {
        displayGpxUserAlert($filename, $alert_pos);
        return;
    }
    if (!$dom->schemaValidate(
        "http://www.topografix.com/GPX/1/1/gpx.xsd", LIBXML_SCHEMA_CREATE
    )
    ) {
        displayGpxUserAlert($filename, $alert_pos);
        return;
    }
    if ($etest) {
        $elevs = $dom->getElementsByTagName('ele');
        if ($elevs->length === 0) {
            $_SESSION['alerts'][$alert_pos] .= " {$filename} cannot be used " .
                "without elevation data; ";
            return;
        }
    }
    if ($symtest) {
        if (!isset($_SESSION['symfault'])) {
            $_SESSION['symfault'] = '';
        }
        // look for unsupported waypoint symbols in the file
        include "gpxWaypointSymbols.php"; // holds $supported_syms
        $gpxsyms = array_keys($supported_syms);

        $wpts = $dom->getElementsByTagName('wpt');
        $sym_string = '';
        foreach ($wpts as $item) {
            $children = $item->childNodes;
            $wptname = 'Undefined';
            // according to gpx schema, <name> occurs before <sym>
            foreach ($children as $node) {
                if ($node->nodeName === 'name') {
                    $wptname = $node->nodeValue;
                } elseif ($node->nodeName === 'sym') {
                    $gpxsymbol = $node->nodeValue;
                    if (!in_array($gpxsymbol, $gpxsyms)) {
                        /**
                         * This is an unsupported <sym>; 
                         * There may be multiple appearances of the
                         * same symbol in different waypoints, hence
                         * include the waypoint's name (if it exists)
                         */
                        $sym_string .= $tmpfile . "^" . $filename . "^" .
                            $gpxsymbol . "^" . $wptname . "^" . "|";
                    }
                }
            }
        }
        $_SESSION['symfault'] .= $sym_string;
    }
    return;
}
/**
 * Display a message for the user about the gpx file failure encountered
 * 
 * @param string $filename The gpx file containing the error
 * @param string $position Index into $_SESSION['alerts'] for msg
 * 
 * @return null
 */
function displayGpxUserAlert($filename, $position)
{
    $err_array = libxml_get_errors();
    $usr_msg = "There is an error in {$filename}:\n" .
        displayXmlError($err_array[0]);
    $_SESSION['alerts'][$position] .= $usr_msg;
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
 * This function will create one or more JSON track file(s) from the specified gpx
 * for each track in the gpx file.
 * 
 * @param PDO    $pdo     Database PDO
 * @param string $type    Determines file name [<input> identifier]
 * @param string $gpxfile The filepath for the file to be converted.
 * @param string $tmploc  Location of the uploaded tmpfile in 'edit' dir
 * @param string $hikeNo  The index to the hike in the database
 * 
 * @return array $org_name The array used to store original file name w/tracks
 */
function makeTrackFiles($pdo, $type, $gpxfile, $tmploc, $hikeNo) 
{
    $ftype = ''; // will be the 1st 3 chars in name
    $fno   = 1;  // suffix indicating file no.
    switch ($type) {
    case 'main':
        $ftype = 'emn';
        break;
    case 'add1':
        $ftype = 'ea1';
        break;
    case 'add2':
        $ftype = 'ea2';
        break;
    case 'add3':
        $ftype = 'ea3';
        break;
    case 'gps':
        $ftype = 'egp';
    }
    // get file as simple xml
    $gpxdat = simplexml_load_file($tmploc);
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
    // Save any gpxfile waypoints in json file
    if ($gpxdat->wpt->count() > 0) {
        $waypoints = [];
        foreach ($gpxdat->wpt as $waypt) {
            if (!empty($waypt->sym)) {
                $name = empty($waypt->name) ? "" : $waypt->name;
                $wpts = '{"lat":' . $waypt['lat'] . ',"lng":' . $waypt['lon'] .
                    ',"name":"' . $name . '","sym":"' . $waypt->sym . '"}';
                array_push($waypoints, $wpts);
            }
        }
        $allpts = '[' . implode(",", $waypoints) . '],';
    } else {
        $allpts = '[],';
    }
    $json_data = '{"wpts":' . $allpts; 
    $trk_array = []; // file names for all tracks of this gpx
    $org_name  = []; // name of gpx file & associated tracks
    $org_dat   = []; // <input> type and its associated gpx data
    $trkcnt = $gpxdat->trk->count();
    $track_files = gpxLatLng($gpxdat, $trkcnt); // returns array of arrays
    for ($k=0; $k<$trkcnt; $k++) {
        $json_array = $track_files[$k];
        $no_of_entries = count($json_array[0]); // lats, lngs, eles have same cnt
        $jdat = '"trk":[';   // array of objects
        for ($n=0; $n<$no_of_entries; $n++) {
            $jdat .= '{"lat":' . $json_array[0][$n] . ',"lng":' .
                $json_array[1][$n] . ',"ele":' . $json_array[2][$n] . '},';
        }
        $jdat = rtrim($jdat, ","); 
        $jdat .= ']}';
        $json_data .= $jdat;
        // now save the json file data for this track
        $basename = $ftype . $hikeNo . "_" . $fno++ . ".json";
        $jname = "../json/" . $basename;
        file_put_contents($jname, $json_data);
        array_push($trk_array, $basename);

        // for main gpx file only, record new lat/lng (only 1st track is used)
        if ($k === 0 && $ftype === 'emn') {
            $trk_loc = strpos($json_data, '"trk":[{');
            $latlng_str = substr($json_data, $trk_loc+14, 70);
            $latlng_arr = explode(",", $latlng_str);
            $trklat = (float) $latlng_arr[0];
            $lat = (int) ($trklat * LOC_SCALE);
            $trklng = (float) substr($latlng_arr[1], 6);
            $lng = (int) ($trklng * LOC_SCALE);
            $newdatReq = "UPDATE EHIKES SET lat = ?, lng = ? WHERE indxNo = ?;";
            $newdat = $pdo->prepare($newdatReq);
            $newdat->execute([$lat, $lng, $hikeNo]);
        }
    }
    $org_name[$gpxfile] = $trk_array;
    return $org_name;
}
/**
 * This function calculates the gpx statistics for the main gpx file
 * of a hike.
 * 
 * @param string $gpxPath The hike's main gpx file uploaded via 'newgpx'
 * 
 * @return array miles and elevation change in feet
 */
function getGpxFileStats($gpxPath)
{
    // Now calculate the new gpx file's statistics (miles, feet, ...)
    $gpxdat = simplexml_load_file($gpxPath);
    if ($gpxdat === false) {
        throw new Exception("Failed to open {$gpxPath}");
    }
    if ($gpxdat->rte->count() > 0) {
        $gpxdat = convertRtePts($gpxdat);
    }
    $noOfTrks = $gpxdat->trk->count();
    // threshold in meters to filter out elevation and distance value variation
    // set by default if command line parameter(s) is not given
    $elevThresh = 1.0;
    $distThresh = 5.0;
    $maWindow = 3;
    
    // calculate stats for all tracks:
    $pup = (float)0;
    $pdwn = (float)0;
    $pmax = (float)0;
    $pmin = (float)50000;
    $hikeLgthTot = (float)0;
    for ($k=0; $k<$noOfTrks; $k++) {
        $calcs = getTrackDistAndElev(
            0, $k, "", $gpxPath, $gpxdat, false, null,
            null, $distThresh, $elevThresh, $maWindow
        );
        $hikeLgthTot += $calcs[0];
        if ($calcs[1] > $pmax) {
            $pmax = $calcs[1];
        }
        if ($calcs[2] < $pmin) {
            $pmin = $calcs[2];
        }
        $pup  += $calcs[3];
        $pdwn += $calcs[4];
    } // end for: PROCESS EACH TRK
    
    $totalDist = $hikeLgthTot / 1609;
    $miles = round($totalDist, 1, PHP_ROUND_HALF_DOWN);
    $elev = ($pmax - $pmin) * 3.28084;
    if ($elev < 100) { // round to nearest 10
        $adj = round($elev/10, 0, PHP_ROUND_HALF_UP);
        $feet = 10 * $adj;
    } elseif ($elev < 1000) { // 100-999: round to nearest 50
        $adj = $elev/100;
        $lead = substr($adj, 0, 1);
        $n5 = $lead + 0.50;
        $n2 = $lead + 0.25;
        if ($adj > $n5) {
            $adj = $lead + 1;
        } elseif ($adj >$n2) {
            $adj = $lead + 0.5;
        } else {
            $adj = $lead;
        }
        $feet = 100 * $adj;
    } else { // 1000+: round to nearest 100
        $adj = round($elev/100, 0, PHP_ROUND_HALF_UP);
        $feet = 100 * $adj;
    }
    return [$miles, $feet];
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
    sort($clusters);
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
 * @param SimpleXML $gpxdat       Pre-loaded simplexml for gpx file
 * @param int       $no_of_tracks Write one json file per track
 * 
 * @return array $track_data
 */
function gpxLatLng($gpxdat, $no_of_tracks)
{
    $track_data = [];
    $gpxlats = [];
    $gpxlons = [];
    $gpxelev = [];
    $plat = 0;
    $plng = 0;
    for ($i=0; $i<$no_of_tracks; $i++) {
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
        $track_array = array(
            $gpxlats, $gpxlons, $gpxelev
        );
        array_push($track_data, $track_array);
    }
    return $track_data;
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
 * This function will take a multi-byte UTF-8 character (argument) and
 * locate the 'equivalent' English character with no diacritical mark.
 * A test is used in case the argument is actually standard ASCII.
 * See codes at https://en.wikipedia.org/wiki/List_of_Unicode_characters
 * Added 12/23/2022.
 * 
 * @param string $mb_utf8 The UTF-8 multibyte character
 * 
 * @return string English alphabet equivalent
 */
function mapChar($mb_utf8)
{
    $loc = mb_ord($mb_utf8);
    if ($loc <= 127) { // Max ASCII value
        return ($mb_utf8);
    } else {
        switch ($loc) {
        case ($loc < 192) :
            return "Bad";
            break;
        case ($loc < 199) :
            return "A";
            break;
        case ($loc === 199) :
            return "C";
            break;
        case ($loc < 204) :
            return "E";
            break;
        case ($loc < 208) :
            return "I";
            break;
        case ($loc === 208) :
            return "Bad";
            break;
        case ($loc === 209) :
            return "N";
            break;
        case ($loc < 215) :
            return "O";
            break;
        case ($loc === 215 || $loc === 216) :
            return "Bad";
            break;
        case ($loc < 221) :
            return "U";
            break;
        case ($loc < 224) :
            return "Bad";
            break;
        case ($loc < 231) :
            return "a";
            break;
        case ($loc === 231) :
            return "c";
            break;
        case ($loc < 236) :
            return "e";
            break;
        case ($loc < 240) :
            return "i";
            break;
        case ($loc === 240) :
            return "Bad";
            break;
        case ($loc === 241) :
            return "n";
            break;
        case ($loc < 247) :
            return "o";
            break;
        case ($loc === 247 || $loc === 248) :
            return "Bad";
            break;
        case ($loc < 253) :
            return "u";
            break;
        default :
            return "Bad";
        }
    }
}
