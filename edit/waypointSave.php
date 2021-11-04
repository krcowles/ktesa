<?php
/**
 * This code module performs the saving of edited and new waypoints in
 * the database. Note that all waypoints found in gpx files when uploaded
 * are stored in the database.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */


// A. No waypoints previously existed in the database
if ($wptCount === 0) {
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
            $dbdesc[$dbcnt]   = $newdesc[$i];
            $dbicon[$dbcnt]   = $newicon[$i];
            $dblat[$dbcnt]    = (int) ((float) $newlat[$i] * LOC_SCALE);
            $dblng[$dbcnt++]  = (int) ((float) $newlng[$i] * LOC_SCALE);
        }
    }
    $query = "INSERT INTO ETSV (indxNo,title,mpg,lat,lng,iclr) "
        . "VALUES (?,?,'Y',?,?,? );";
    for ($j=0; $j<$dbcnt; $j++) {
        $newentry = $pdo->prepare($query);
        $newentry->execute(
            [$hikeNo, $dbdesc[$j], $dblat[$j], $dblng[$j], $dbicon[$j]]
        );
    }
} else {
    $chgdesc = $_POST['ddes'];
    $chgicon = $_POST['dsym'];
    $chglat  = $_POST['dlat'];
    $chglng  = $_POST['dlng'];
    $chgidx  = $_POST['didx'];
    $remwpt  = isset($_POST['ddel']) ? $_POST['ddel'] : false;
    $newdbdesc = $_POST['nddes'];
    $newdbicon = $_POST['ndsym'];
    $newdblat  = $_POST['ndlat'];
    $newdblng  = $_POST['ndlng'];
    // current
    $des = [];
    $icn = [];
    $lat = [];
    $lng = [];
    $idx = [];
    // new adds
    $ndes = [];
    $nicn = [];
    $nlat = [];
    $nlng = [];
    // current database points:
    for ($n=0; $n<count($chgdesc); $n++) {
        $tag = 'd' . $n;
        if ($remwpt && in_array($tag, $remwpt)) {
            $delquery = "DELETE FROM ETSV WHERE picIdx = ?;";
            $delpt = $pdo->prepare($delquery);
            $delpt->execute([$chgidx[$n]]);
        } else {
            array_push($des, $chgdesc[$n]);
            array_push($icn, $chgicon[$n]);
            array_push($lat, (int) ((float) $chglat[$n] * LOC_SCALE));
            array_push($lng, (int) ((float) $chglng[$n] * LOC_SCALE));
            array_push($idx, $chgidx[$n]);
        }
    }
    // update current point data
    for ($t=0; $t<count($des); $t++) {
        $updtequery = "UPDATE ETSV SET title = ?, lat = ?, lng = ?,  "
            . "iclr = ? WHERE picIdx = ?;";
        $updte = $pdo->prepare($updtequery);
        $updte->execute([$des[$t], $lat[$t], $lng[$t], $icn[$t], $idx[$t]]);
    }
    // newly added database points:
    for ($p=0; $p<count($newdbdesc); $p++) {
        if (!empty($newdbdesc[$p]) || !empty($newdblat[$p]) 
            || !empty($newdblng[$p])
        ) {
            array_push($ndes, $newdbdesc[$p]);
            array_push($nicn, $newdbicon[$p]);
            array_push($nlat, (int) ((float) $newdblat[$p] * LOC_SCALE));
            array_push($nlng, (int) ((float) $newdblng[$p] * LOC_SCALE));
        }
    }
    $dbquery = "INSERT INTO ETSV (indxNo,title,mpg,lat,lng,iclr) "
        . "VALUES (?,?,'Y',?,?,? );";
    for ($q=0; $q<count($ndes); $q++) {
        $db = $pdo->prepare($dbquery);
        $db->execute(
            [$hikeNo, $ndes[$q], $nlat[$q], $nlng[$q], $nicn[$q]]
        );
    }
}
