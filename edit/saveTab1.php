<?php
/**
 * This script saves all user data present on tab1 of the hike page Editor,
 * which may include gpx file uploads. However, if issues are detected with
 * file uploads, then the files cannot be saved and must be corrected in order
 * to complete the upload process. In some cases, a file may have issues that
 * must be corrected by offline editing, and the user can then resubmit the
 * file. However, in the case where the file specifies a waypoint which has a
 * symbol not currently supported by this application, a page will be presented
 * to the user wherein the issue can be resolved, thus allowing the upload to 
 * complete automatically. In this latter case, the save process will have been
 * temporarily interrupted, but will resume after symbol correction. When
 * this occurs, this script will be re-invoked as outlined below.
 *  
 * When the 'Apply' on tab1 is hit, '$form_saved' will be false, and all user
 * data will be saved. When the script is being re-invoked after an 'interrupt'
 * to fix a symbol issue, '$form_saved' will be true.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require "../accounts/gmail.php";
$hikeNo    = filter_input(INPUT_POST, 'hikeNo');
$form_saved = filter_input(INPUT_POST, 'fsaved') === 'N' ? false : true;
// redirect locations
$interrupt = "correctableFault.php?hikeNo={$hikeNo}";
$tab1      = "editDB.php?tab=1&hikeNo={$hikeNo}";

if (!$form_saved) {
    // set alerts to empty every time tab1 is saved
    $_SESSION['alerts'] = ["", "", "", ""]; // [main, addgpx1, addgpx2, addgpx3]
    $_SESSION['symfault'] = '';

    /**
     * All non-gpxfile data is collected in $basic_data and then saved prior to
     * processing any outstanding file uploads. 
     */
    // pre-process certain data for the array:
    $dirs = filter_input(INPUT_POST, 'dirs');
    if (empty($dirs)) {
        $dirs = '';
    } 
    $clusterlat = isset($_POST['cluslat']) && !empty($_POST['cluslat']) ?
        filter_input(INPUT_POST, 'cluslat', FILTER_VALIDATE_FLOAT) : false;
    $clusterlng = isset($_POST['cluslng']) && !empty($_POST['cluslng']) ?
        filter_input(INPUT_POST, 'cluslng', FILTER_VALIDATE_FLOAT) : false;
    // all non-gpxfile data:
    $basic_data = array(
        'hikeNo'     => $hikeNo, // not user-assignable
        'pgTitle'    => filter_input(INPUT_POST, 'pgTitle'),
        'locale'     => filter_input(INPUT_POST, 'locale'), 
        // when adding a new location
        'addloc'     => isset($_POST['addaloc']) ? true : false, 
        'newloc'     => filter_input(INPUT_POST, 'userloc'),
        'region'     => filter_input(INPUT_POST, 'locregion'),
        'userlat'    => filter_input(INPUT_POST, 'newloclat'),
        'userlng'    => filter_input(INPUT_POST, 'newloclng'),
        //
        'logistics'  => filter_input(INPUT_POST, 'logistics'),
        'diff'       => filter_input(INPUT_POST, 'diff'),
        'fac'        => filter_input(INPUT_POST, 'fac'),
        'wow'        => filter_input(INPUT_POST, 'wow'),
        'seasons'    => filter_input(INPUT_POST, 'seasons'),
        'expo'       => filter_input(INPUT_POST, 'expo'),
        'dirs'       => $dirs,
        // 'clusters' posts 'null' when clusters drop-down is empty, otherwise it
        // contains the user selection - or db default, if previously saved and
        // unchanged:
        'cname'      => filter_input(INPUT_POST, 'clusters'),
        'remclus'    => isset($_POST['rmClus']) ?
                            filter_input(INPUT_POST, 'rmClus') : false,
        'clusterlat' => round($clusterlat, 7),
        'clusterlng' => round($clusterlng, 7),
    );
    /**
     * Has a new location/area been specified by the user?
     * If so, update the localeBox.html <select> element and the areas.json file:
     */
    if ($basic_data['addloc']) {
        $newloc = $basic_data['newloc'];
        $loc = ['        <option value="' . $newloc . '">' . $newloc .
            '</option>' . PHP_EOL];
        $areas = file('localeBox.html');
        $position = 0;
        for ($j=0; $j<count($areas); $j++) {
            if (strpos($areas[$j], $basic_data['region']) !== false) {
                $position = $j + 1;
                break;
            }
        }
        array_splice($areas, $position, 0, $loc);
        file_put_contents('localeBox.html', $areas);
        // add this locale to the db so that it will display on page refresh
        $basic_data['locale'] = $newloc;
        // Now update areas.json or notify admin to update
        if (!empty($basic_data['userlat']) && !empty($basic_data['userlng'])) {
            $jsonareas = file('../json/areas.json');
            $locobj = [
                '        {"loc": "' . $newloc . '", "lat": ' .
                $basic_data['userlat'] . ', "lng": ' . $basic_data['userlng'] .
                '},' . PHP_EOL
            ];
            array_splice($jsonareas, 2, 0, $locobj);
            file_put_contents('../json/areas.json', $jsonareas);
        } else {
            include "requestNewLoc.php";  // advise admin to update areas.json
        }
    }
    /**
     * CLUSTER ASSIGNMENT PROCESSING:
     */ 
    // Previous state of `cname` determines CLUSHIKES actions
    $clusAssignmentReq = "SELECT `cname` FROM `EHIKES` WHERE `indxNo`=?;";
    $clusAssignment = $pdo->prepare($clusAssignmentReq);
    $clusAssignment->execute([$hikeNo]);
    $assign = $clusAssignment->fetch(PDO::FETCH_ASSOC);
    $currentClus = empty($assign['cname']) ? false : $assign['cname']; // db value
    $delCHike = false;
    $addCHike = false;
    $cname = $basic_data['cname']; // current form's <select> choice
    if ($currentClus === false && !empty($cname)) {
        // Nothing in the db yet, but a selection was made
        $addCHike = true;
    }
    if ($currentClus && empty($cname)) {
        // Something in the db, but <select> is now empty
        $delCHike = true;
    }
    if (!empty($cname)) {  // user selected a cluster
        if ($currentClus && $currentClus !== $cname) { // assignmt changed
            $delCHike = true;
            $addCHike = true;
        }
        // Is this an unpublished cluster?
        $getStateReq = "SELECT `pub` FROM `CLUSTERS` WHERE `group`=?;";
        $getState = $pdo->prepare($getStateReq);
        $getState->execute([$cname]);
        $clusState = $getState->fetch(PDO::FETCH_ASSOC);
        if ($clusState['pub'] === 'N') {
            // minimum validation of lat/lng values for cluster:
            $vlat = $basic_data['clusterlat']; // entered by user (may be false)
            $vlng = $basic_data['clusterlng']; // entered by user (may be false)
            $valid_latlng = true;
            if ($vlat && $vlng) {
                $vlat = $vlat < 37 && $vlat > 31.3 ? $vlat * LOC_SCALE : 0;
                $vlng = $vlng < -103 && $vlat > -109.1 ? $vlng * LOC_SCALE : 0;
                if ($vlat === 0 || $vlng === 0) {
                    $_SESSION['clus_loc'] = " Cluster latitude or longitude " .
                        "appears to be out of bounds; ";
                    $valid_latlng = false;
                }
            }
            // either/both may be false:
            $clat = !$vlat ? null : $vlat;
            $clng = !$vlng ? null : $vlng;
            if (is_null($clat) || is_null($clng)) {
                $_SESSION['clus_loc'] = " Cluster latitude or longitude " . 
                    "is missing; ";
                $valid_latlng = false;
            } 
            if ($valid_latlng) {      
                $updte_req = "UPDATE `CLUSTERS` SET `lat`=:lat, `lng`=:lng WHERE " .
                    "`group` = :group;";
                $updte = $pdo->prepare($updte_req);
                $updte->execute(["lat" => $clat, "lng" => $clng, "group" => $cname]);
                /**
                 * If there is a Cluster Page in-edit for this new group, update it's
                 * lat/lng values. 
                 * Note: if a Cluster Page for this new group was published, it must
                 * already have had lat/lng specified (it won't publish otherwise).
                 * Therefore, the only scenario to update is if the Cluster Page for
                 * the new group is in-edit.
                 */
                $checkForCPReq = "SELECT `pgTitle` FROM `EHIKES` WHERE `pgTitle`=?;";
                $checkForCP = $pdo->prepare($checkForCPReq);
                $checkForCP->execute([$cname]);
                $CP_InEdit = $checkForCP->fetch(PDO::FETCH_ASSOC);
                if ($CP_InEdit !== false) {
                    $newLatLngReq = "UPDATE `EHIKES` SET `lat`=?,`lng`=? WHERE " .
                        "`pgTitle`=?;";
                    $newLatLng = $pdo->prepare($newLatLngReq);
                    $newLatLng->execute([$clat, $clng, $cname]);
                }
            }
        }
    } else {
        $basic_data['cname'] = '';
    }
    // update CLUSHIKES as appropriate
    if ($delCHike) {
        $deleteCHikeReq = "DELETE FROM `CLUSHIKES` WHERE `indxNo`=?;";
        $deleteCHike = $pdo->prepare($deleteCHikeReq);
        $deleteCHike->execute([$hikeNo]);
    }
    if ($addCHike) {
        // get clusterid for $cname
        $cnameIdReq = "SELECT `clusid` FROM `CLUSTERS` WHERE `group`=:grp;";
        $cnameId = $pdo->prepare($cnameIdReq);
        $cnameId->execute(["grp" => $cname]);
        $cnId = $cnameId->fetch(PDO::FETCH_ASSOC);
        $id = $cnId['clusid'];
        $addClusHikeReq = "INSERT INTO `CLUSHIKES` (`indxNo`,`pub`,`cluster`) " .
            "VALUES(?,'N',?);";
        $addClusHike = $pdo->prepare($addClusHikeReq);
        $addClusHike->execute([$hikeNo, $id]);
    }
    /**
     * Save the basic user data prior to processing any upload file requests:
     */
    $svreq = "UPDATE EHIKES SET " .
        "pgTitle = :pgTitle, locale = :locale, cname = :cname, " .
        "logistics = :logistics, diff = :diff, fac = :fac, wow = :wow, " .
        "seasons = :seasons, expo = :expo, dirs = :dirs " .
        "WHERE indxNo = :hikeNo";
    $basic = $pdo->prepare($svreq);
    $basic->execute(
        [$basic_data['pgTitle'], $basic_data['locale'], $basic_data['cname'],
        $basic_data['logistics'], $basic_data['diff'], $basic_data['fac'],
        $basic_data['wow'], $basic_data['seasons'], $basic_data['expo'],
        $basic_data['dirs'], $hikeNo]
    );

    /**
     * All current database gpx file and new upload file data is assembled in the
     * $tab1_file_data array. Gpx file tracks, whether originally contained in a
     * single gpx file or in separate files, will each be assigned a separate json
     * file. The gpx's track files will be indicated consecutively by the numbers
     * 1, 2, 3 appearing at the end of the file's base name to indicate track no
     * within the original gpx file (note that it quite infrequent that a gpx file
     * has multiple tracks). A user may specify up to 3 additional gpx files to
     * appear on the hike page along with the main track. The orginal uploaded gpx
     * file names are saved in the 'gpx' field as a JSON string. When decoded it
     * is an associative array whose keys are always: 'main', 'add1', 'add2', and
     * 'add3'. Each key is subsequently an associative array whose key is the
     * original gpx filename and whose value is the corresponding json track files.
     * Filenames are constructed as follow: 1) 'e' or 'p' for in-edit or production;
     * 2) 2-char for input filename - 'mn'-> main, 'a1'->add1gpx, 'a2'->add2gpx,
     * 'a3'->add3gpx; 3) hike indexNo; 4) '_1', ... '_n' track file number
     * e.g.
     *  [NO gpx files specified]:
     *      ['main'=>[], 'add1'=>[], 'add2'=>[], 'add3'=>[]]
     * 
     *  [2 gpx files uploaded]: main gpx had 2 tracks, add2 had 1 track (hikeNo 3)
     *      ['main'=>'abrigo.gpx'=>['emn3_1.json', 'emn3_2.json'], 'add1'=>'',
     *          'add2'=>'proposed.gpx'=>['ea23_1.json'], 'add3'=>'']
     * 
     * Filenames are guaranted unique by virtue of the unique hike index no
     * plus assigned suffix
     */
    $empty_org = ['main'=>[], 'add1'=>[], 'add2'=>[],'add3'=>[]];
    $getGpxReq = "SELECT `gpx` FROM `EHIKES` WHERE `indxNo` = ?;";
    $getGpx    = $pdo->prepare($getGpxReq);
    $getGpx->execute([$hikeNo]);
    $gpx_data   = $getGpx->fetch(PDO::FETCH_ASSOC);
    if (empty($gpx_data['gpx'])) {
        $org_names  = $empty_org;
    } else {
        $stdClassGpx = json_decode($gpx_data['gpx'], true);
        // Convert stdClass to array: 
        $org_names = [];
        foreach ($stdClassGpx as $item => $value) {
            $org_names[$item] = $value;
        }
    }
    // associative arrays, else empty array              
    $main = $org_names['main'];
    $add1 = $org_names['add1'];
    $add2 = $org_names['add2'];
    $add3 = $org_names['add3'];
    // some files may have been specified for removal:
    $delgpx     = isset($_POST['dgpx']) ? $_POST['dgpx'] : false;
    $noincludes = isset($_POST['deladd']) ? $_POST['deladd'] : false;
    $uploads = [];
    $upload_files = ['newgpx', 'addgpx1', 'addgpx2', 'addgpx3'];
    /**
     * Relevant upload data for each input file on tab1 consists of:
     *  ureq => boolean: is an input file specified by user?
     *  ifn  => string: input file name attribute
     *  err  => int: server code for upload result
     *  ufn  => string: user's file name
     *  type => string: server evaluation of file type
     *  apos => int: $_SESSION['alerts'] index: each file has its own
     * 
     * NOTE: Since the script may be exited to service correctable errors,
     * server tmp files are stored locally, as $_FILES data will be lost;
     */
    foreach ($upload_files as $upld) {
        array_push($uploads, prepareUpload($upld));
    }
    $tab1_file_data = array(
        'org_gpx'  => $org_names, // original gpx filenames, each w/track files
        'ifiles'   => $upload_files,
        'jtype'    => ['main', 'add1', 'add2', 'add3'],
        'del_main' => $delgpx,
        'del_adds' => $noincludes,
        'uploads'  => $uploads
    );
    $_SESSION['uplmsg'] = ''; // returns upload/delete status to user on tab1

    // --------------------- HANDLE ANY FILE DELETIONS ------------------

    /**
     * This section handles the main gpx file delete. Lat/lngs and miles/feet are
     * always updated when the existing main file is deleted. NOTE: If there are
     * additional files associated with the main gpx file, they will also be deleted.
     * Deletion occurs once and not during re-invocation of this script (it is
     * possible that a main gpx file is being deleted while a new one is being
     * uploaded). It is assumed that when deleting a main track file, the basic tab1
     * data, the photos, any description or trail tips, references, and gps data 
     * may still be valid and are not deleted. The user can alter as he/she sees fit.
     * However, if this file had database waypoints, they will be deleted, as they
     * are generally associated with a track. If a new upload is being requested and
     * a main file already existed, the previous main will be deleted ($replace_main).
     */
    $mainkey = empty($main) ? '' : array_keys($main)[0];
    $add1key = empty($add1) ? '' : array_keys($add1)[0];
    $add2key = empty($add2) ? '' : array_keys($add2)[0];
    $add3key = empty($add3) ? '' : array_keys($add3)[0];
    $replace_main = $tab1_file_data['uploads'][0]['ureq'] && !empty($mainkey) ?
        true : false;
    if ($tab1_file_data['del_main'] || $replace_main) {
        $tracks  = $main[$mainkey];
        foreach ($tracks as $track) {
            $deltrk = '../json/' . $track;
            if (!unlink($deltrk)) {
                throw new Exception("Could not remove {$deltrk} from site");
            }
        }
        $_SESSION['uplmsg']
            .= "Deleted file {$mainkey} and it's associated track(s) from site; ";
        $org_names['main'] = [];
        // Also delete additional files for this hike if no new main
        if (!$tab1_file_data['uploads'][0]['ureq']) {
            $tab1_file_data['del_adds'] = false;
            $additional_deletes = [];
            if (!empty($add1)) {
                $jdels = $add1[$add1key];
                foreach ($jdels as $del) {
                    array_push($additional_deletes, $del);
                }
                $_SESSION['uplmsg'] .= " Deleted additional file {$add1key}; ";
                $org_names['add1'] = [];
            }
            if (!empty($add2)) {
                $jdels = $add2[$add2key];
                foreach ($jdels as $del) {
                    array_push($additional_deletes, $del);
                }
                $_SESSION['uplmsg'] .= " Deleted additional file {$add2key}; ";
                $org_names['add2'] = [];
            }
            if (!empty($add3)) {
                $jdels = $add3[$add3key];
                foreach ($jdels as $del) {
                    array_push($additional_deletes, $del);
                }
                $_SESSION['uplmsg'] .= " Deleted additional file {$add3key}; ";
                $org_names['add3'] = [];
            }
            foreach ($additional_deletes as $json) {
                $delfile = "../json/" . $json;
                if (!unlink($delfile)) {
                    throw new Exception("Could not remove {$delfile} from site");
                }
            }
            // delete any database waypoints for this hike
            $waypointsReq = "DELETE FROM `EWAYPTS` WHERE `indxNo`=?;";
            $rmWaypts = $pdo->prepare($waypointsReq);
            $rmWaypts->execute([$hikeNo]);
            // update 'gpx' field in EHIKES db
            $new_org = json_encode($org_names);
            $udgpxreq = "UPDATE EHIKES SET gpx=?,lat=NULL,lng=NULL,
                miles=NULL,feet=NULL WHERE indxNo=?;";
            $udgpx = $pdo->prepare($udgpxreq);
            $udgpx->execute([$new_org, $hikeNo]);
        }
        $tab1_file_data['del_main'] = false;
        $tab1_file_data['org_gpx']  = $org_names;
    }
    /**
     * This section handles the deletion of any additional files specified.
     */
    $add_deletes = $tab1_file_data['del_adds'];
    if ($add_deletes) {
        foreach ($add_deletes as $del) {
            $addfiles = [];
            if ($add1key === $del) {
                $jdels = $add1[$add1key];
                foreach ($jdels as $del) {
                    array_push($addfiles, $del);
                }
                $_SESSION['uplmsg']
                        .= " Deleted additional file {$add1key}; ";
                $org_names['add1'] = [];
            } elseif ($add2key === $del) {
                $jdels = $add2[$add2key];
                foreach ($jdels as $del) {
                    array_push($addfiles, $del);
                }
                $_SESSION['uplmsg']
                        .= " Deleted additional file {$add2key}; ";
                $org_names['add2'] = [];
            } elseif ($add3key === $del) {
                $jdels = $add3[$add3key];
                foreach ($jdels as $del) {
                    array_push($addfiles, $del);
                }
                $_SESSION['uplmsg']
                        .= " Deleted additional file {$add3key}; ";
                $org_names['add3'] = [];
            }
            foreach ($addfiles as $json) {
                $delfile = "../json/" . $json;
                if (!unlink($delfile)) {
                    throw new Exception("Could not remove {$json} from site");
                }
            }
        }
        // update 'gpx' field in EHIKES db
        $new_org = json_encode($org_names);
        $udgpxreq = "UPDATE EHIKES SET gpx=? WHERE indxNo=?;";
        $udgpx = $pdo->prepare($udgpxreq);
        $udgpx->execute([$new_org, $hikeNo]);
        $tab1_file_data['org_gpx'] = $org_names;
        $tab1_file_data['del_adds'] = false; // don't re-process
    }
} else {
    // END OF TAB1 'SAVE SANS UPLOADS', RE-INVOKED SCRIPT CONTINUES HERE:
    $saved_data = file_get_contents('tab1FileData.json');
    // json_decode creates a stdClass object, not the original array
    $stdClass_data = json_decode($saved_data, true);
    $tab1_file_data = [];
    foreach ($stdClass_data as $item => $value) {
        $tab1_file_data[$item] = $value;
    }
}

/**
 * ----------------------------------------------------------------------------------
 * |            BOTH INITIAL & RE-INVOKED SCRIPT EXECUTION CONTINUE HERE            |
 * ----------------------------------------------------------------------------------
 *
 * Now that basic data has been saved to the database, file uploads (if any) may
 * proceed. For each file upload, one of three things can happen:
 *  1. An internal issue with the file is discovered, thereby generating an 'alert'
 *     for that file. It will not be uploaded. Alerts are concatenated and presented
 *     to the user when the routine redirects to tab1.
 *  2. A file contains one or more unsupported symbols in the waypoint section. In
 *     that case, $_SESSION['symfault'] holds the relevant data until the initial
 *     uploading process is complete. At that point the script will redirect to a
 *     page where the user can correct the issue(s), after which uploading will
 *     resume.
 *  3. No issues are encountered, and the file is uploaded
 * All upload files will attempt to be uploaded when $form_saved is false.
 */
$file_uploads = $tab1_file_data['uploads'];
foreach ($file_uploads as &$upload) {
    if ($upload['ureq']) {
        if (!$form_saved) {
            $file_path = uploadFile($upload, true, true);
            $base_file = pathinfo($file_path, PATHINFO_BASENAME);
            $tmpfile = $upload['ifn'] . '.gpx'; // potentially same as $file_path
            if (!empty($_SESSION['alerts'][$upload['apos']])) {
                $upload['ureq'] = false;
                if (file_exists($tmpfile)) {
                    if (!unlink($tmpfile)) {
                        throw new Exception("Could not remove tmp file {$badfile}");
                    }
                }
            } elseif (!(isset($_SESSION['symfault']) && $upload['ext'] === 'gpx'
                && strpos($_SESSION['symfault'], $upload['ifn']) !== false)
            ) {
                if ($upload['ext'] === 'gpx' && $upload['ifn'] !== 'file2edit'
                    && $upload['ifn'] !== 'gpx2edit'
                ) {
                    $new_data = processGpx(
                        $pdo, $upload, $tab1_file_data['ifiles'],
                        $tab1_file_data['jtype'], $hikeNo
                    );
                    $org_key = $new_data[0];
                    $org_names[$org_key] = $new_data[1];
                    $tab1_file_data['org_gpx'] = $org_names;
                } 
            }
            if (isset($_SESSION['symfault']) 
                && strpos($_SESSION['symfault'], $upload['ifn']) !== false
            ) {
                $upload['ureq'] = true;
            } else {
                $upload['ureq'] = false;
            }      
        } else {
            /**
             * $upload now points to the interrupted (but corrected) file load
             */
            resetSymfault($upload['ifn']);
            $new_data = processGpx(
                $pdo, $upload, $tab1_file_data['ifiles'],
                $tab1_file_data['jtype'], $hikeNo
            );
            $org_key = $new_data[0];
            $tab1_file_data['org_gpx'][$org_key] = $new_data[1];
            $_SESSION['uplmsg'] .= "Your file [" . $upload['ufn'] . "] was saved; ";
            $upload['ureq'] = false;
        }
    }
}
$tab1_file_data['uploads'] = $file_uploads;   
if (isset($_SESSION['symfault']) && $_SESSION['symfault'] !== '') {
    $saved_data = json_encode($tab1_file_data);
    file_put_contents('tab1FileData.json', $saved_data);
    header("Location: " . $interrupt);
    exit;
} 

// write out the updated gpx file and track info:
$newgpx = json_encode($tab1_file_data['org_gpx']);
$newlistReq = "UPDATE `EHIKES` SET `gpx`=? WHERE `indxNo` =?;";
$newlist = $pdo->prepare($newlistReq);
$newlist->execute([$newgpx, $hikeNo]);

if (file_exists('tab1FileData.json')) {
    unlink('tab1FileData.json');
}
header("Location: {$tab1}");
