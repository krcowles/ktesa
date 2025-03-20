<?php
/**
 * This code module performs the saving of edited and new waypoints in
 * either the database, or the gpx file, depending on where the waypoints
 * are found. If there are no waypoints yet, the new ones will be entered 
 * into the database. NOTE: Due to the rendering process of waypoints in 
 * GPSV maps, a description text cannot contain a single quote (') which
 * is not escaped.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */

// Retrieve waypoint format:
$wpt_format = filter_input(INPUT_POST, 'wpt_format');
if (!empty($wpt_format)) {
    // Note: for now, it may update the value already there...
    $user = $_SESSION['userid'];
    $entryExists = $pdo->query("SELECT `userid` FROM `MEMBER_PREFS`;")
        ->fetchAll(PDO::FETCH_COLUMN);
    if (in_array($user, $entryExists)) {
        $prefDB = "UPDATE `MEMBER_PREFS` SET `wpt_format`=:wpt WHERE `userid`=:uid;";
    } else {
        $prefDB = "INSERT INTO `MEMBER_PREFS` (`userid`,`wpt_format`) VALUES " .
            "(:uid,:wpt);";
    }
    $fixFormat = $pdo->prepare($prefDB);
    $fixFormat->execute(["uid"=>$user, "wpt"=>$wpt_format]);
}

/**
 * Case 1. The following code stores NEW database waypoint entries.
 * [Also applies to new db pts in Case 3.]
 */
if (!empty($_POST['ndes'])) {
    $newdesc = $_POST['ndes'];
    $newicon = $_POST['nsym'];
    $newlat  = $_POST['nlat'];
    $newlng  = $_POST['nlng'];
    // arrays used to hold db updates
    $dbdesc = [];
    $dbicon = [];
    $dblat  = [];
    $dblng  = [];
    $dbcnt = 0;
    // PDO insert any entered data
    for ($i=0; $i<count($newdesc); $i++) {
        if (!empty($newdesc[$i]) || !empty($newlat[$i])
            || !empty($newlng[$i]) 
        ) {
            // add this waypoint to the database array
            $dbdesc[$dbcnt]   = str_replace("'", "\'", $newdesc[$i]);
            $dbicon[$dbcnt]   = $newicon[$i];
            $dblat[$dbcnt]    = (int) ((float) $newlat[$i] * LOC_SCALE);
            $dblng[$dbcnt++]  = (int) ((float) $newlng[$i] * LOC_SCALE);
        }
    }
    $query = "INSERT INTO `EWAYPTS` (`indxNo`,`type`,`name`,`lat`,`lng`,`sym`) " .
        "VALUES (?,'db',?,?,?,? );";
    for ($j=0; $j<$dbcnt; $j++) {
        $newentry = $pdo->prepare($query);
        $newentry->execute(
            [$hikeNo, $dbdesc[$j], $dblat[$j], $dblng[$j], $dbicon[$j]]
        );
    }
}

/**
 * Case 2. If the current gpx file has embedded waypoints, the entries
 * may have been modified or deleted, and new entries may have been made;
 */
if (!empty($_POST['gdes'])) {
    $curgdesc = $_POST['gdes'];
    $curgicon = $_POST['gsym'];
    $curglat  = $_POST['glat'];
    $curglng  = $_POST['glng'];
    $curgidx  = $_POST['gidx'];
    $curgrem  = isset($_POST['gdel']) ? $_POST['gdel'] : [];
    $addgdesc = $_POST['ngdes'];
    $addgicon = $_POST['ngsym'];
    $addglat  = $_POST['nglat'];
    $addglng  = $_POST['nglng'];
    // Update existing points, including deletions if specified
    for ($j=0; $j<count($curgdesc); $j++) {
        if (in_array($curgidx[$j], $curgrem)) {
            $delquery = "DELETE FROM `EWAYPTS` WHERE `wptId` = ?;";
            $delpt = $pdo->prepare($delquery);
            $delpt->execute([$curgidx[$j]]);
        } else {
            $curtxt = str_replace("'", "\'", $curgdesc[$j]);
            $lat = (int) ((float)$curglat[$j] * LOC_SCALE);
            $lng = (int) ((float)$curglng[$j] * LOC_SCALE);
            $updateGpxReq
                = "UPDATE `EWAYPTS` SET `name`=?,`lat`=?,`lng`=?,`sym`=? " .
                    "WHERE `wptId`=?";
            $updateGpx = $pdo->prepare($updateGpxReq);
            $updateGpx->execute(
                [$curtxt, $lat, $lng, $curgicon[$j], $curgidx[$j]]
            );
        }
    }
    // Add new gpx points
    for ($k=0; $k<count($addgdesc); $k++) {
        if (!empty($addgdesc[$k])) {
            $addtxt = str_replace("'", "\'", $addgdesc[$k]);
            $lat = (int) ((float)$addglat[$k] * LOC_SCALE);
            $lng = (int) ((float)$addglng[$k] * LOC_SCALE);
            $newGpxReq
                = "INSERT INTO `EWAYPTS` (`indxNo`,`type`,`name`,`lat`,`lng`," .
                    "`sym`) VALUES (?,'gpx',?,?,?,?);";
            $newGpx = $pdo->prepare($newGpxReq);
            $newGpx->execute([$hikeNo, $addtxt, $lat, $lng, $addgicon[$k]]);
        }
    }
}

/**
 * Case 3. If there are any waypoints in the database for this hike
 * update them here (including any deletions)
 */
if (!empty($_POST['ddes'])) {
    $chgdesc = $_POST['ddes'];
    $chgicon = $_POST['dsym'];
    $chglat  = $_POST['dlat'];
    $chglng  = $_POST['dlng'];
    $chgidx  = $_POST['didx'];
    $remwpt  = isset($_POST['ddel']) ? $_POST['ddel'] : [];
   
    for ($n=0; $n<count($chgdesc); $n++) {
        if (in_array($chgidx[$n], $remwpt)) {
            $delquery = "DELETE FROM `EWAYPTS` WHERE `wptId` = ?;";
            $delpt = $pdo->prepare($delquery);
            $delpt->execute([$chgidx[$n]]);
        } else {
            $chgtxt = str_replace("'", "\'", $chgdesc[$n]);
            $lat = (int) ((float)$chglat[$n] * LOC_SCALE);
            $lng = (int) ((float)$chglng[$n] * LOC_SCALE);
            $updtequery = "UPDATE `EWAYPTS` SET `name`=?,`lat`=?,`lng`=?," .
            "`sym`=? WHERE `wptId`=?;";
            $updte = $pdo->prepare($updtequery);
            $updte->execute(
                [$chgtxt, $lat, $lng, $chgicon[$n], $chgidx[$n]]
            );
        }
    }
}
