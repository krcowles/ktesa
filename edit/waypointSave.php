<?php
/**
 * This code module performs the saving of edited and new waypoints in
 * either the database, or the gpx file, depending on where the waypoints
 * are found. If there are no waypoints yet, the new ones will be entered 
 * into the database.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

// waypoint data, if present in database
$gpxfile    = filter_input(INPUT_POST, 'track');

// Retrieve waypoint format:
$wpt_format = filter_input(INPUT_POST, 'wpt_format');
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

/**
 * The following code retrieves NEW database waypoint entries. This can happen
 * when either: (1) No waypoints previously existed in either the gpx file or
 * in the database or (3) Waypoints previously existed in the database, and
 * new ones can be added. Case 1 and Case 3 can never happen simultaneously.
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
}

// Case 2. If the current gpx file has embedded waypoints
if (!empty($_POST['gdes'])) {
    $newgdesc = $_POST['gdes'];
    $newgicon = $_POST['gsym'];
    $newglat  = $_POST['glat'];
    $newglng  = $_POST['glng'];
    $newgrem  = isset($_POST['gdel']) ? $_POST['gdel'] : false;
    $addgdesc = $_POST['ngdes'];
    $addgicon = $_POST['ngsym'];
    $addglat  = $_POST['nglat'];
    $addglng  = $_POST['nglng'];
    // arrays used to hold updates to gpx file
    $allpts = [];
    $gpxloc = '../gpx/' . $gpxfile;
    $xml = simplexml_load_file($gpxloc);
    if ($xml === false) {
        // NOTE: file already validated during upload
        throw new Exception("Failed to load {$gpxfile}");
    }
    // rather than check each node and all its children to look for any
    // changes, the nodes are simply deleted then re-added from POSTed data
    $xml->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');
    $waypts = $xml->xpath('//gpx:wpt');
    foreach ($waypts as $node) {
        unset($node[0]);  // each node is an array, with [0] as the self-reference
    }
    // Form new waypoint nodes: 1) From current:
    for ($i=0; $i<count($newgdesc); $i++) {
        $newpt = '';
        $tag = "g" . $i;
        if (!$newgrem || ($newgrem && !in_array($tag, $newgrem))) {
            $newpt .= '<wpt lat="' . $newglat[$i] . '" lon="' . $newglng[$i]
                . '"><name>' . $newgdesc[$i] . '</name><sym>' . $newgicon[$i]
                . '</sym></wpt>';
            array_push($allpts, $newpt);
        }
    }
    // 2) From newly added:
    for ($j=0; $j<count($addgdesc); $j++) {
        $addpt = '';
        if (!empty($addgdesc[$j]) || !empty($addglat[$j]) || !empty($addglng[$j])) {
            $addpt .= '<wpt lat="' . $addglat[$j] . '" lon="' . $addglng[$j]
            . '"><name>' . $addgdesc[$j] . '</name><sym>' . $addgicon[$j]
            . '</sym></wpt>';
            array_push($allpts, $addpt);
        }
    }
    // find first <trk/> node
    $target;
    foreach ($xml->children() as $child) {
        $nodeName = $child->getName();
        if ($nodeName == 'trk') {
            $target = $child;
            break;
        }
    }
    foreach ($allpts as $savept) {
        $newwaypt = new SimpleXMLElement($savept);
        simplexmlInsertAfter($newwaypt, $target);
    }
    /**
     * SimpleXML formatOutput does NOT work, but DOES on Dom docs:
     */
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    $dom->save($gpxloc);
}

// Case 3. If there are any waypoints in the database for this hike
if (!empty($_POST['ddes'])) {
    $chgdesc = $_POST['ddes'];
    $chgicon = $_POST['dsym'];
    $chglat  = $_POST['dlat'];
    $chglng  = $_POST['dlng'];
    $chgidx  = $_POST['didx'];
    $remwpt  = isset($_POST['ddel']) ? $_POST['ddel'] : false;
    $des = [];
    $icn = [];
    $lat = [];
    $lng = [];
    $idx = [];
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
    for ($t=0; $t<count($des); $t++) {
        $updtequery = "UPDATE ETSV SET title = ?, lat = ?, lng = ?,  "
            . "iclr = ? WHERE picIdx = ?;";
        $updte = $pdo->prepare($updtequery);
        $updte->execute([$des[$t], $lat[$t], $lng[$t], $icn[$t], $idx[$t]]);
    }
}
