<?php
/**
 * This routine saves any changes made (or current data) on tab2
 * ('Photo Selection') and updates the ETSV table.
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$hikeNo = filter_input(INPUT_POST, 'hikeNo');
$usr = filter_input(INPUT_POST, 'usr');
// waypoint data, if present:
$gpxfile = filter_input(INPUT_POST, 'track');
$wids = isset($_POST['wids']) ? $_POST['wids'] : null;  // picIdx for waypoint
$wdes = isset($_POST['wsym']) ? $_POST['wdes'] : null;
$wsym = isset($_POST['wsym']) ? $_POST['wsym'] : null;
$wlat = isset($_POST['wlat']) ? $_POST['wlat'] : null;
$wlng = isset($_POST['wlng']) ? $_POST['wlng'] : null;
/* It is possible that no pictures are present, also that no
 * checkboxes are checked. Therefore, the script tests for these things
 * to prevent undefined vars
 */
// # of captions corresponds to # pictures present
if (isset($_POST['ecap'])) {
    $ecapts = $_POST['ecap'];
    $noOfPix = count($ecapts);
} else {
    $ecapts = [];
    $noOfPix = 0;
}
// 'pix' are the checkboxes indicating a photo is spec'd for the hike page
if (isset($_POST['pix'])) {
    $displayPg = $_POST['pix'];
} else {
    $displayPg = [];
}
// 'mapit' are the checkboxes indicating a photo is spec'd for the map
if (isset($_POST['mapit'])) {
    $displayMap = $_POST['mapit'];
} else {
    $displayMap = [];
}
// 'rem' are the checkboxes marking photos to be deleted
if (isset($_POST['rem'])) {
    $rems = $_POST['rem'];
    $noOfRems = count($rems);
} else {
    $rems = [];
    $noOfRems = 0;
}
$photoReq = "SELECT * FROM ETSV WHERE indxNo = ?;";
$photoq = $pdo->prepare($photoReq);
$photoq->execute([$hikeNo]);
$p = 0;
while ($photo = $photoq->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($photo['imgHt'])) {
        $thisid = $photo['picIdx'];
        $thispic = $photo['title'];
        $newcap = $ecapts[$p];
        // look for a matching checkbox then set for display (or map)
        $disph = 'N';
        for ($i=0; $i<count($displayPg); $i++) {
            if ($thispic == $displayPg[$i]) {
                $disph = 'Y';
                break;
            }
        }
        $dispm = 'N';
        for ($j=0; $j<count($displayMap); $j++) {
            if ($thispic == $displayMap[$j]) {
                $dispm = 'Y';
                break;
            }
        }
        $deletePic = false;
        for ($k=0; $k<$noOfRems; $k++) {
            if ($rems[$k] === $thispic) {
                $deletePic = true;
                break;
            }
        }
        if ($deletePic) {
            $delreq = "DELETE FROM ETSV WHERE title = ?;";
            $del = $pdo->prepare($delreq);
            $del->execute([$thispic]);
        } else {
            $updtreq = "UPDATE ETSV SET hpg = ?, mpg = ?, `desc` = ? "
                . "WHERE picIdx = ?;";
            $update = $pdo->prepare($updtreq);
            $update->execute([$disph, $dispm, $newcap, $thisid]);
        }
        $p++;
    }
}
/**
 * WAYPOINT Processing Section:
 */
// 1. No waypoints previously existed in either the gpx file or database
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
            $dblat[$dbcnt]    = $newlat[$i];
            $dblng[$dbcnt++]  = $newlng[$i];
        }
    }
    $query = "INSERT INTO ETSV (title,lat,lng,iclr) VALUES (?,?,?,? );";
    for ($j=0; $j<count($dbdesc); $j++) {
        $newentry = $pdo->prepare($query);
        $newentry->execute([$dbdesc[$j], $dblat[$j], $dblng[$j], $dbicon[$j]]);
    }
}
// 2. If the current gpx file has embedded waypoints
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
        throw new Exception("Failed to load {$xml}");
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
     * SimpleXML foramtOutput does NOT work, but DOES on Dom docs:
     */
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    $dom->save('dom.gpx');
}
// 3. If there are any waypoints in the database for this hike
if (!empty($_POST['ddes'])) {
    $chgdesc = $_POST['ddes'];
    $chgicon = $_POST['dsym'];
    $chglat  = $_POST['dlat'];
    $chglng  = $_POST['dlng'];
    $remwpt  = isset($_POST['ddel']) ? $_POST['ddel'] : false;
    $newdbdesc = $_POST['nddes'];
    $newdbicon = $_POST['ndsym'];
    $newdblat  = $_POST['ndlat'];
    $newdblng  = $_POST['ndlng'];
    $des = [];
    $icn = [];
    $lat = [];
    $lng = [];
    // current database points:
    for ($n=0; $n<count($chgdesc); $n++) {
        $tag = 'd' . $n;
        if (!$remwpt || $remwpt & !in_array($tag, $remwpt)) {
            array_push($des, $chgdesc[$n]);
            array_push($icn, $chgicon[$n]);
            array_push($lat, $chglat[$n]);
            array_push($lng, $chglng[$n]);
        }
    }
    // newly added database points:
    for ($p=0; $p<count($newdbdesc); $p++) {
        if (!empty($newdbdesc[$p]) || !empty($newdblat[$p]) 
            || !empty($newdblng[$p])
        ) {
            array_push($des, $newdbdesc[$p]);
            array_push($icn, $newdbicon[$p]);
            array_push($lat, $newdblat[$p]);
            array_push($lng, $newdblng[$p]);
        }
    }
    $dbquery = "INSERT INTO ETSV (title,lat,lng,iclr) VALUES (?,?,?,? );";
    for ($q=0; $q<count($des); $q++) {
        $db = $pdo->prepare($dbquery);
        $db->execute([$des[$q], $lat[$q], $lng[$q], $icn[$q]]);
    }
}
$redirect = "editDB.php?hikeNo={$hikeNo}&usr={$usr}&tab=2";
header("Location: {$redirect}");
