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
 * Check to see if a visitor is logged in & session has not expired;
 * Due to numerous emails from certain robot-searching sites trying 
 * to access members-only pages, this function has been modified to
 * suppress admin emails. The function is only invoked in the 
 * hikeEditor.php, startNewPg.php, and favTable.php scripts.
 * 
 * @param string $page_loc From which editor page the error occurred
 * 
 * @return string User's logged in userid
 */
function validSession($page_loc)
{
    $ip = getIpAddress();
    if (!isset($_SESSION['userid'])) {
        echo "Your session has expired, or you are not a registered user...";
        exit;
        /*
        throw new Exception(
            "Page {$page_loc}: No userid id - session expired" .
                " or illegal access: {$ip}"
        );
        */
    } else {
        return $_SESSION['userid'];
    }
    
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
    $upload_data = [
        'ureq' => $requested,
        'ifn'  => $input_file_name,
        'err'  => $error_stat,
        'ufn'  => $requested ? $user_file : '',
        'ext'  => $extension,
        'type' => '',
        'apos' => 0 // default index into user_alerts[]
    ];
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
        // eliminate any 'photo' waypoints added by apps....
        $tmp_file = $upload['ifn'] . '.gpx';
        checkForPhotoWpts($tmp_file);
    } else {
        $file_type = validateType($upload['type']); // html, kml, or unknown
    }
    if ($file_type === 'unknown') {
        // remove unknonwn file type's $tmpfile (type not based on file extension)
        unlink($tmpfile);
        $_SESSION['alerts'][$upload['apos']] = "The file type for {$tmpfile} " .
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
 * Some apps place waypoints where the user took a photo, as photos are not
 * permitted in gpx files - these are not useful for creating a track on a hike
 * page, as it overloads the track with waypoint markers. Two apps to date have
 * been identified, and they do it differently: One use the <name> tag of the 
 * waypoint with the word 'Photo' appearing in it (e.g. <name>My Photo...</name>);
 * Another places the word 'camera' in the waypoint <sym> tag instead 
 * (e.g. <sym>camera-24</sym>). There may be others discovered as more users
 * create or modify hike pages. It will be necessary to add checks in this function
 * for newly discovered issues.
 * 
 * @param string $gpxfile Gpx filename
 * 
 * @return null;
 */
function checkForPhotoWpts($gpxfile)
{
    $upld_gpx = simplexml_load_file($gpxfile);
    if ($upld_gpx->rte->count() > 0) {
        $upld_gpx = convertRtePts($upld_gpx);
    }
    $photo_wpts = [];
    $wcnt = $upld_gpx->wpt->count();
    if ($wcnt > 0) {
        for ($i=0; $i<$wcnt; $i++) {
            $fwpt = $upld_gpx->wpt[$i];
            if (empty($fwpt->name)) {
                $name = "No description";
            } else {
                $name = $fwpt->name->__toString();
            }
            $test_name = strtolower($name);
            $symbol = $fwpt->sym->__toSTring();
            // ignore photo waypoints in apps
            if (strpos($test_name, "photo") !== false
                || strpos($symbol, "camera") !== false
            ) {
                array_push($photo_wpts, $fwpt);
            }
        }
    }
    foreach ($photo_wpts as $item) {
        $dom = dom_import_simplexml($item);
        $dom->parentNode->removeChild($dom);
    }
    $upld_gpx->asXml($gpxfile);
    return;
}
/**
 * Any completed gpx file upload will require 'processing', which means that
 * the corresponding json track files must be created and stored. Lats
 * and lngs and eles are extracted and data is returned.
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
    $tmpfile = $upload['ifn'] . ".gpx"; // file is located in edit directory
    if ($upload['ifn'] === 'newgpx') { // this is the only input file utilized
        // to retrieve Ascent/Descent metadata (if present)
        $ascent_descent = gpxAscentDescent("./{$tmpfile}");
        $setMeta = "UPDATE `EHIKES` SET `meta`='{$ascent_descent}' WHERE " .
            "`indxNo`={$hikeNo};";
        $pdo->query($setMeta);
        // NOTE: Track nos begin at 1 [not 0] in the a/d coded data retrieved
    }
    $new_orgdat = makeTrackFiles(
        $pdo, $org_key, $upload['ufn'], $tmpfile, $hikeNo
    );
    if ($org_key === 'main') {
            $gpx_data = simplexml_load_file($tmpfile);
            $calcs = getGpxStats($gpx_data, 0);
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
 * @return string The client filename that was uploaded & server location
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
    return 'No matching error found';
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
 * @param string $ext     Extension to start with for json file, if requested
 * 
 * @return array $org_name The array used to store original file name w/tracks
 */
function makeTrackFiles($pdo, $type, $gpxfile, $tmploc, $hikeNo, $ext=false) 
{
    $ftype = ''; // will be the 1st 3 chars in json filename
    $fno   = $ext ?: 1;  // suffix indicating file no. (when multiple tracks)
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
        // ensure a unique suffix as multiple gps are possible
        $base_gps = "../json/egp" . $hikeNo . "_";
        while (file_exists($base_gps . $fno . ".json")) {
            $fno++;
        }
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
    /**
     * Waypoints: there is only one set of waypoints regardless of the
     * number of tracks present in the file (refer to GPX Schema). The
     * gpx waypoint data is simplified by stripping metadata and extensions
     * from the file and uses only fields relevant to this application.
     */
    if ($gpxdat->wpt->count() > 0) {
        foreach ($gpxdat->wpt as $waypt) {
            $sym  = empty($waypt->sym)  ? "googlemini" : $waypt->sym;
            $name = empty($waypt->name) ? "Noname" : $waypt->name;
            $wptlat = LOC_SCALE * $waypt['lat'];
            $wptlng = LOC_SCALE * $waypt['lon'];
            $gpswptReq = "INSERT INTO `EWAYPTS` (`indxNo`,`type`,`name`," .
            "`lat`,`lng`,`sym`) VALUES (?,'gpx',?,?,?,?);";
            $gpswpt = $pdo->prepare($gpswptReq);
            $gpswpt->execute([$hikeNo, $name, $wptlat, $wptlng, $sym]);
        }
    }
    $trk_array   = []; // json file names for all tracks of this gpx
    $track_names = []; // <name> for each track
    $org_name    = []; // name of gpx file & associated tracks
    $trkcnt = $gpxdat->trk->count();
    for ($j=0; $j<$trkcnt; $j++) {
        $trkname= $gpxdat->trk[$j]->name->__toString();
        $track_name = empty($trkname) ? "No Track Name" : $trkname;
        array_push($track_names, $track_name);
    }
    /**
     * Each track in $trkcnt has a corresponding array of lats, lngs, eles;
     * The last entry ($trkcnt) is the total hike bounds for the combined tracks
     * and is the last entry in $track_files, since returned tracks are 0->$trkcnt-1
     */
    $track_files = gpxLatLng($gpxdat, $trkcnt);
    for ($k=0; $k<$trkcnt; $k++) {
        $json_array = $track_files[$k]; // $k is the kth track
        $no_of_entries = count($json_array[0]); // lats, lngs, eles have same cnt
        $jdat = '{"name":"' . $track_names[$k] . '","trk":[';   // array of objects
        for ($n=0; $n<$no_of_entries; $n++) {
            $jdat .= '{"lat":' . $json_array[0][$n] . ',"lng":' .
                $json_array[1][$n] . ',"ele":' . $json_array[2][$n] . '},';
        }
        $jdat = rtrim($jdat, ","); 
        $jdat .= ']}';
        // now save the json file data for this track
        $basename = $ftype . $hikeNo . "_" . $fno++ . ".json";
        $jname = "../json/" . $basename;
        file_put_contents($jname, $jdat);
        array_push($trk_array, $basename);
 
        // for main gpx file only, record new lat/lng (1st track) & bounds
        if ($k === 0 && $ftype === 'emn') {
            $trk_loc = strpos($jdat, '"trk":[{');
            $latlng_str = substr($jdat, $trk_loc+14, 70);
            $latlng_arr = explode(",", $latlng_str);
            $trklat = (float) $latlng_arr[0];
            $lat = (int) ($trklat * LOC_SCALE);
            $trklng = (float) substr($latlng_arr[1], 6);
            $lng = (int) ($trklng * LOC_SCALE);
            $bounds = $track_files[$trkcnt];
            $newdatReq
                = "UPDATE `EHIKES` SET `lat`=?,`lng`=?,`bounds`=? WHERE `indxNo`=?;";
            $newdat = $pdo->prepare($newdatReq);
            $newdat->execute([$lat, $lng, $bounds, $hikeNo]);
        }
    }
    $org_name[$gpxfile] = $trk_array;
    return $org_name;
}
/**
 * If there is metadata in a gpx file relative to Ascent and Descent,
 * it will be used instead of calculating this info on the hike page.
 * The GPX Schema allows the following children of <trk>:
 *    [name, cmt, desc, src, link, number, type, extensions, trkseg]
 * It is assumed that any ascent/descent data is contained in the
 * extension tag. Note that 'extensions' can contain a variety of
 * different items, usually varying by creator: e.g. Garmin has
 * its own set, Gaia uses 'line', etc. If no extensions, it is
 * presumed there is no ascent/descent info. The attempts to use
 * simpleXML fail, as it can't seem to handle the Garmin extensions:
 * the children thereof appear blank and have no string content, (or
 * debugger can't show it) hence I have reverted to the cumbersome but
 * more reliable string search methods. Note that there may be more
 * than one track in a gpx file.
 * 
 * @param string $gpx The gpx file location
 * 
 * @return string ascent/descent data, if any
 */
function gpxAscentDescent($gpx)
{
    $ad_data = [];
    $xml = file_get_contents($gpx);
    $trk_strs = explode("<trk>", $xml);
    $noOfTracks = count($trk_strs);
    if ($noOfTracks < 1) { // sanity check
        return "";
    }
    for ($j=1; $j<$noOfTracks; $j++) { //  [0] has no track data
        // Must have both Ascent and Descent:
        if (str_contains($trk_strs[$j], "Ascent")
            && str_contains($trk_strs[$j], "Descent")
        ) {
            $current = $trk_strs[$j];
            $aloc = strpos($current, 'Ascent');
            $bloc = strpos($current, 'Descent');
            // Assuming Ascent/Descent are followed by tag ending ">"
            $atxt = substr($current, $aloc+7, 50);
            $dtxt = substr($current, $bloc+8, 50);
            // Data is between tag and closing tag
            $aclose = strpos($atxt, "<");
            $bclose = strpos($dtxt, "<");
            $asc  = substr($atxt, 0, $aclose);
            $des = substr($dtxt, 0, $bclose);
            // Convert meters to feet
            $ascent = round((float) $asc * 3.28084, 0);
            $descent = round((float) $des * 3.28084, 0);
            $data = "{$j}:{$ascent}/{$descent}";
        } else {
            $data = "{$j}:0/0";
        }
        array_push($ad_data, $data);
    }
    $return_data = implode(",", $ad_data);
    return $return_data;
    /*
    $track = $gpx_xml->trk;
    $data = [0, 0];
    if ($track->hasChildren()) {
        $trk_children = $track->getChildren();
        foreach ($trk_children as $child) {
            $trk_tag = $child->getName();
            // lookd for 'extensions' where presumed data resides
            if ($trk_tag === 'extensions') {
                $ext_items = $gpx_xml->trk->extensions;
                // if the tag exists, it is presumed there are children:
                if ($ext_items->hasChildren()) {
                    $child_count = $ext_items->count();
                    if ($child_count === 1) {
                        // Garmins various children don't show up here
                        $ext_els = $ext_items->getChildren();
                        $contents = $ext_els->__toString();
                    }
                    foreach ($ext_els as $sxml) {
                        $tag = $sxml->getName();
                        $name = strtolower($tag);
                        if (str_contains($name, 'ascent')) {
                            $data[0] = $sxml;
                        } else if (str_contains($name, 'descent')) {
                            $data[1] = $sxml;
                        }
                    }
                }
            }
        }
    }
    */
}
/**
 * An often required function is presented to simplify access to the
 * gpx field and convert the contents to a corresponding php array.
 * 
 * @param PDO    $pdo    Database connection
 * @param string $hikeno Unique hike indxNo
 * @param string $state  Determines which database table to use
 * 
 * @return array $gpx_array The converted php array
 */
function getGpxArray($pdo, $hikeno, $state) 
{
    $table = $state === 'pub' ? 'HIKES' : 'EHIKES';
    $getGpxFieldReq = "SELECT `gpx` FROM `{$table}` WHERE `indxNo`={$hikeno};";
    $gpxField = $pdo->query($getGpxFieldReq)->fetch(PDO::FETCH_ASSOC);
    if (!empty($gpxField['gpx'])) {
        $stdClassGpx = json_decode($gpxField['gpx'], true);
        // Convert stdClass array to php array: 
        $gpx_array = [];
        foreach ($stdClassGpx as $item => $value) {
            $gpx_array[$item] = $value;
        }
    } else {
        $gpx_array = ["main"=>[], "add1"=>[], "add2"=>[], "add3"=>[]];
    }
    return $gpx_array;
}
/**
 * When the [E]GPSDAT table is accessed, this function will convert the 
 * url field for labels GPX[] into a standard array
 * 
 * @param string $url The 'url' field of the row
 * 
 * @return array $gps_array
 */
function getGPSurlData($url)
{
    $stdClassGpx = json_decode($url, true);
    // Convert stdClass to array: [only 1 to convert]
    $gpx_array = [];
    foreach ($stdClassGpx as $item => $value) {
        $gpx_array[$item] = $value;
    }
    $gps_gpx  = array_keys($gpx_array)[0];   // this is the org gpx filename
    $gps_json = array_values($gpx_array)[0]; // an array of its json files
    return array($gps_gpx, $gps_json);
}
/**
 * It is often required to extract the information contained in the 
 * HIKES/EHIKES 'gpx' field and convert it to a usable array (or
 * comma-separated string) of any and all track files associated with
 * the hike. The stored json data, when decoded, results in an array
 * of stdClass type, and must be converted to standard php string arrays.
 * 
 * @param PDO    $pdo    The database connection
 * @param string $hikeNo The hike's unique indxNo in the HIKES/EHIKES table
 * @param string $state  Whether hike is in-edit or published 
 * 
 * @return array $converted An array of track data as both an array of
 *                          track files, or a comma-separated string;
 *                          includes name of the main gpx file 
 */
function getTrackFileNames($pdo, $hikeNo, $state)
{
    // Get a complete list of tracks [from 'gpx'] associated with this hike 
    $gpx_array = getGpxArray($pdo, $hikeNo, $state);
    if (!empty($gpx_array["main"])) {
        $main = array_values($gpx_array["main"])[0];
        $mainfile = array_keys($gpx_array["main"])[0];
        $add1 = empty($gpx_array["add1"]) ?
            [] : array_values($gpx_array["add1"])[0];
        $add2 = empty($gpx_array["add2"]) ?
            [] : array_values($gpx_array["add2"])[0];
        $add3 = empty($gpx_array["add3"]) ?
            [] : array_values($gpx_array["add3"])[0];
        $track_array = array_merge($main, $add1, $add2, $add3);
        $track_string = implode(",", $track_array);
    } else {
        $track_array  = [];
        $track_string = '';
        $mainfile = '';
    }
    return [$track_array, $track_string, $mainfile];
}

/**
 * From a file that contains json track data, extract only the track
 * name.
 * 
 * @param string $filename The file name holding the json track data
 * 
 * @return string The track name
 */
function getTrackNameFromFile($filename)
{
    $track = file_get_contents("../json/{$filename}");
    $json = json_decode($track, true);
    return $json['name'];
}

/**
 * Replacement for former gpxFunction: getTrackDistAndElev.
 * gpxFunctions.php no longer exists. This function will
 * calculate miles/feet from the original gpx file.
 * Note, for any gpx file w/multiple trkseg's in a trk, all
 * trkpt's will be assembled in one array ($track_pts);
 * 
 * @param SimpleXMLElement $xml_data Pre-loaded xml data from gpx file
 * @param int              $track_no Which track in the data to use
 * 
 * @return array $data Hike distance (miles) and elevation change (feet)
 */
function getGpxStats($xml_data, $track_no)
{
    if ($xml_data->rte->count() > 0) {
        $xml_data = convertRtePts($xml_data);
    }
    // Look for any ascent/descent metadata

    $track_pts = [];
    $noOfSegs = $xml_data->trk[$track_no]->trkseg->count();
    for ($i=0; $i<$noOfSegs; $i++) {
        $xml_elements = $xml_data->trk[$track_no]->trkseg[$i];
        foreach ($xml_elements as $trkpt) {
            array_push($track_pts, $trkpt);
        }
    }
    // variables for each track's calcs
    $hikeLgth = (float)0;
    $pmax = (float)0;
    $pmin = (float)50000;
    // 1st lat/lng, used when calling 'distance()'
    $prevLat  = (float)$track_pts[0]["lat"]->__toString(); 
    $prevLng  = (float)$track_pts[0]["lon"]->__toString();
    $prevEle  = (float)$track_pts[0]->ele->__toString();

    for ($k=0; $k<count($track_pts); $k++) {
        $curlat = (float)$track_pts[$k]["lat"]->__toString();
        $curlng = (float)$track_pts[$k]["lon"]->__toString();
        $curele = (float)$track_pts[$k]->ele->__toString();
        // 1st distance = 0
        $parms = distance($prevLat, $prevLng, $curlat, $curlng);
        $hikeLgth += $parms[0];
        $prevLat = $curlat;
        $prevLng = $curlng;
        if (!($curele === 0 || $prevEle === 0)) {
            if ($curele > $pmax) {
                $pmax = $curele;
            }
            if ($curele < $pmin) {
                $pmin = $curele;
            }
        }
        $prevEle = (float)$track_pts[$k]->ele->__toString();
    }
    $trackDistance = round($hikeLgth/1609, 2); // converted to miles
    $echg = 3.28084 * ($pmax - $pmin); // converted to feet
    $elevChange = round($echg); 

    return [$trackDistance, $elevChange];
}

/**
 * Using json data, calculate total track distance and elevation for the
 * track file submitted. Calculations are returned on a per-track basis.
 * NOTE: By default, a track combines all <trsegs> if there are multiple
 * This function fills the caller's $lats, $lngs, and $ticks arrays.
 * 
 * @param array  $json_data The json-decoded array of lat/lng/ele elements
 * @param string $trkname   The name associated with the track
 * @param array  $lats      The callers array of this track's lats
 * @param array  $lngs      The callers array of this track's lngs
 * @param array  $ticks     The callers array of gpsv tickmarks
 * @param int    $trackno   The trackno represented by the file (1-based)
 * 
 * @return array $track_dat 
 */
function trackStats($json_data, $trkname, &$lats, &$lngs, &$ticks, $trackno)
{
    $pup = (float)0;
    $pdwn = (float)0;
    $pmax = (float)0;
    $pmin = (float)50000;
    $tickMrk  = 0.3; // 0.3 miles as meters
    $hikeLgth = (float)0;
    $prevLat  = $json_data[0]["lat"]; // 1st lat, used when calling 'distance()'
    $prevLng  = $json_data[0]["lng"];
    $prevEle  = $json_data[0]["ele"];
    $gpsv_trackdat = '[ [';

    for ($k=0; $k<count($json_data); $k++) {
        array_push($lats, $json_data[$k]["lat"]);
        array_push($lngs, $json_data[$k]["lng"]);
        $parms = distance( // 1st distance is 0
            $prevLat, $prevLng, $json_data[$k]["lat"], $json_data[$k]["lng"]
        );
        $hikeLgth += $parms[0];
        $prevLat = $json_data[$k]["lat"];
        $prevLng = $json_data[$k]["lng"];
        if (!($json_data[$k]["ele"] === 0 || $prevEle === 0)) {
            if ($json_data[$k]["ele"] > $pmax) {
                $pmax = $json_data[$k]["ele"];
            }
            if ($json_data[$k]["ele"] < $pmin) {
                $pmin = $json_data[$k]["ele"];
            }
            $elevChg = $json_data[$k]["ele"] - $prevEle; // 1st chg is 0
            if ($elevChg >= 0) {
                $pup += $elevChg;
            } else {
                $pdwn -= $elevChg;
            }
        }
        $prevEle = $json_data[$k]["ele"];
        // Form GPSV javascript track and tickmark data for this trkpt
        $rotation = $parms[1];
        // $gpsv_trackdat => track "points" in trk[t].segments.push({ points:  })
        $gpsv_trackdat .= $json_data[$k]["lat"] . "," . 
            $json_data[$k]["lng"] . "],[";
        if ($hikeLgth > $tickMrk) {
            $tick
                = "GV_Draw_Marker({lat:" . $json_data[$k]["lat"] .
                    ",lon:" . $json_data[$k]["lng"] . ",alt:" . 
                    $json_data[$k]["ele"] . ",name:'" . $tickMrk . 
                    " mi',desc:trk[" . ($trackno) . "].info.name,color:trk["
                    . $trackno . "]"
                    . ".info.color,icon:'tickmark',type:'tickmark',folder:'"
                    . $trkname . " [tickmarks]',rotation:" . $rotation
                    . ",track_number:" . $trackno . ",dd:false});";
            array_push($ticks, $tick);
            $tickMrk += 0.30 * 1609.344; // increase interval (meters)
        }
    }
    // remove trailing characters forming next lat/lng array
    $gpsv_trackdat = substr($gpsv_trackdat, 0, strlen($gpsv_trackdat)-3);
    // complete the string
    $gpsv_trackdat .= '] ]';
    
    return [$hikeLgth, round($pmax), round($pmin), 
        round($pup), round($pdwn), $gpsv_trackdat];
}
/**
 * Whenever a routine requires 'multiMap.php', which subsequently requires
 * 'fillGpsvTemplate.php', those routines expect to have certain variables
 * pre-defined. This function establishes those variables derivable from
 * the json track files. The calculations performed also extract info for
 * the hike page side panel and that data is returned as a set of arrays.
 * This function is invoked (currently) in 'fullPgMapLink.php', twice in
 * 'hikePageData.php', and 'multiMap.php', where each script has access to
 * the hike's indxNo.
 * 
 * @param PDO    $pdo       Database connection
 * @param array  $tracks    An array of all track names (json files) to be mapped
 * @param array  $trk_nmes  An array in caller holding the names of the tracks
 * @param array  $gpsv_trk  An array in caller holding the gpsv-style track data
 * @param array  $trk_lats  An array in caller holding lats stored in the files
 * @param array  $trk_lngs  An array in caller holding lngs stored in the files
 * @param array  $gpsv_tick An array in caller holding 
 * @param int    $hikeNo    The hike page's indxNo
 * @param string $table     Which database table to use [HIKES or EHIKES]
 * 
 * @return array calc data for side panel; $gpsv_trk = [];
 */
function prepareMappingData(
    $pdo, $tracks, &$trk_nmes, &$gpsv_trk, &$trk_lats, &$trk_lngs, &$gpsv_tick,
    $hikeNo = 0, $table = '' // multiMap.php may not have $hikeNo or $table...
) {
    $trkno    = 1;
    $miles    = [];
    $maxmin   = [];
    $asc      = [];
    $dsc      = [];
    // assume less than 10 tracks per file...
    $meta_asc = [-1, -1, -1, -1, -1, -1, -1, -1, -1];
    $meta_dsc = [-1, -1, -1, -1, -1, -1, -1, -1, -1];
    // is ascent/descent already provided via the gpx file?
    if ($table === 'HIKES' || $table === 'EHIKES' && $hikeNo !== 0) {
        $gpx_metaReq = "SELECT `meta` FROM `{$table}` WHERE `indxNo`={$hikeNo};";
        $gpx_meta = $pdo->query($gpx_metaReq)->fetch(PDO::FETCH_ASSOC);
        if (!empty($gpx_meta['meta'])) {
            $ad = explode(",", $gpx_meta['meta']); // 1 dataset per track
            for ($k=0; $k<count($ad); $k++) {
                // strip off initial track no
                $arg1 = substr($ad[$k], 2);
                $data = explode("/", $arg1);
                //$masc = substr($data[0], 2);
                //$mdsc = substr($data[1], 2);
                if ((int) $data[0] !== 0 && (int) $data[1] !== 0) {
                    $meta_asc[$k] = (int) $data[0];
                    $meta_dsc[$k] = (int) $data[1];
                }
            }
        }
    }
    $ptr = 0;
    foreach ($tracks as $json_file) {
        $trk_file = "../json/" . $json_file;
        if (!file_exists($trk_file)) {
            die("The track file specified does not exist...");
        }
        $jdat = file_get_contents($trk_file);
        $stdClass = json_decode($jdat, true);
        // Convert stdClass to php array: 
        $trk_dat = [];
        foreach ($stdClass as $item => $value) {
            $trk_dat[$item] = $value;
        }
        array_push($trk_nmes, $trk_dat['name']);
        $track  = $trk_dat['trk'];
        $lats   = [];
        $lngs   = [];
        $ticks  = [];
        $calcs = trackStats( // for a single track [$track]...
            $track, $trk_dat['name'], $lats, $lngs, $ticks, $trkno++
        );
        // map data
        array_push($gpsv_trk, $calcs[5]);
        array_push($trk_lats, $lats);
        array_push($trk_lngs, $lngs);
        array_push($gpsv_tick, $ticks);
        // side panel data for hike pages

        array_push($miles, $calcs[0]);
        array_push($maxmin, $calcs[1] - $calcs[2]);
        if ($meta_asc[$ptr] !== -1) {
            array_push($asc, $meta_asc[$ptr]);
        } else {
            array_push($asc, $calcs[3]);
        }
        if ($meta_dsc[$ptr] !== -1) {
            array_push($dsc, $meta_dsc[$ptr]);
        } else {
            array_push($dsc, $calcs[4]);
        }
        $ptr++;
    }
    return [$miles, $maxmin, $asc, $dsc];
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
 * When there is no gpxfile, a pseudo-json file is created for display on
 * the map.
 * 
 * @param float $clat Map center latitude
 * @param float $clng Map center longitude
 * 
 * @return null
 */
function createPseudoJson($clat, $clng)
{
    $json_file = '{"name":"filler","trk":[';
    // The lat/lng of center may change, and therefore appear as variables
    $json_file .= '{"lat":' . $clat . ',"lng":' . $clng . ',"ele":500},';
    $json_file .= '{"lat":' . ($clat+.004507) . ',"lng":' . $clng . ',"ele":510},';
    $json_file .= '{"lat":' . ($clat-.004507) . ',"lng":' . $clng . ',"ele":510},';
    $json_file .= '{"lat":' . $clat . ',"lng":' . $clng . ',"ele":500},';
    $json_file .= '{"lat":' . $clat . ',"lng":' . ($clng-.005477) . ',"ele":500},';
    $json_file .= '{"lat":' . $clat . ',"lng":' . ($clng+.005466) . ',"ele":500}]}';
    file_put_contents('../json/filler.json', $json_file);
    return;
}
/**
 * This function extracts the lats, lngs, and elevs from a gpx file,
 * and returns them as arrays. Gpx files list elevations in meters.
 * NOTE: if there are multiple segments within a track, they are effectively
 * combined into one segment.
 * 
 * @param SimpleXMLElement $gpxdat       Pre-loaded simplexml for gpx file
 * @param int              $no_of_tracks Write one json file per track
 * 
 * @return array $track_data
 */
function gpxLatLng($gpxdat, $no_of_tracks)
{
    if ($gpxdat->rte->count() > 0) {
        $gpxdat = convertRtePts($gpxdat);
    }
    $track_data = [];
    $gpxlats = [];
    $gpxlons = [];
    $gpxelev = [];
    $plat = 0;
    $plng = 0;
    // for bounds determination...
    $maxlat = 0;
    $minlat = 100;
    $maxlng = -110;
    $minlng = -106;
    for ($i=0; $i<$no_of_tracks; $i++) {
        foreach ($gpxdat->trk[$i]->trkseg as $trackdat) {
            foreach ($trackdat->trkpt as $datum) {
                if (!( $datum['lat'] === $plat && $datum['lon'] === $plng )) {
                    $plat = $datum['lat'];
                    $plng = $datum['lon'];
                    array_push($gpxlats, (float)$plat);
                    array_push($gpxlons, (float)$plng);
                    // update bounds
                    $blat = round((float)$plat, 5);
                    $blng = round((float)$plng, 5);
                    if ($blat > $maxlat) {
                        $maxlat = $blat;
                    }
                    if ($blat < $minlat) {
                        $minlat = $blat;
                    }
                    if ($blng > $maxlng) {
                        $maxlng = $blng;
                    }
                    if ($blng < $minlng) {
                        $minlng = $blng;
                    }
                    $meters = $datum->ele;
                    $feet = round(3.28084 * (float)($meters[0]), 1);
                    array_push($gpxelev, $feet);
                }
            }
        }
        $track_array = array(
            $gpxlats, $gpxlons, $gpxelev
        );
        array_push($track_data, $track_array);
        $gpxlats = [];
        $gpxlons = [];
        $gpxelev = [];
    }
    $bounds = "{$maxlat},{$minlat},{$maxlng},{$minlng}"; // all tracks combined
    array_push($track_data, $bounds);
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
    return array ($dist, $rotation);
}
/**
 * This module will replace all occurances of <rtept> in a gpx
 * file with <trkpt> tags.
 * 
 * @param SimpleXMLElement $rtefile simpleXML file object from gpx file
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
    return $trkPtFile;
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
 * Find the location of the 'pictures' directory on the site (it will be situated
 * differently for a test site, for example)
 * 
 * @return string $picdir Relative path to the pictures directory
 */
function getPicturesDirectory()
{
    $picdir = "";
    $current = getcwd();
    $prev = $current; // return to $prev when done
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
        case ($loc < 199) :
            return "A";
        case ($loc === 199) :
            return "C";
        case ($loc < 204) :
            return "E";
        case ($loc < 208) :
            return "I";
        case ($loc === 208) :
            return "Bad";
        case ($loc === 209) :
            return "N";
        case ($loc < 215) :
            return "O";
        case ($loc === 215 || $loc === 216) :
            return "Bad";
        case ($loc < 221) :
            return "U";
        case ($loc < 224) :
            return "Bad";
        case ($loc < 231) :
            return "a";
        case ($loc === 231) :
            return "c";
        case ($loc < 236) :
            return "e";
        case ($loc < 240) :
            return "i";
        case ($loc === 240) :
            return "Bad";
        case ($loc === 241) :
            return "n";
        case ($loc < 247) :
            return "o";
        case ($loc === 247 || $loc === 248) :
            return "Bad";
        case ($loc < 253) :
            return "u";
        default :
            return "Bad";
        }
    }
}
