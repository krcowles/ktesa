<?php
require 'setenv.php';
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Populate HIKES</title>
    <meta charset="utf-8" />
    <meta name="description" content="Fill the HIKES table w/xml database" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body { background-color: #eaeaea; }
    </style>
</head>
<body>
    <div id="logo">
        <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
        <p id="logo_left">Hike New Mexico</p>
        <img id="tmap" src="../images/trail.png" alt="trail map icon" />
       <p id="logo_right">w/Tom &amp; Ken</p>
    </div>
    <p id="trail">Create A New Page</p>
    <div style="margin-left:16px;font-size:18px;">
        <p>Use database.xml to populate the HIKES table in the 'mysql' database...</p>

<?php
    echo "<p>mySql Connection Opened.</p>";
    $db = simplexml_load_file('../data/database.xml');
    if (!$db) {
        $errmsg = '<p style="color:red;font-size:18px;margin-left:16px">' .
            'Failed to load xml database.</p>';
        die($errmsg);
    } else {
        echo '<p>XML Database successfully opened.</p>';
    }
    # Extract each row's variables and load into mysql HIKES table
    # NOTE: 'serialize' will have content even when the array is empty.
    $maxref = 0;
    $maxprop = 0;
    $maxact = 0;
    $maxtsv = 0;
    $maxtbl = 0;
    foreach ($db->row as $row) {
        # $htitle is a non-NULL field, no test for existence here:
        $htitle = mysqli_real_escape_string($link,$row->pgTitle);
        # everything else can be NULL:
        $hloc = $row->locale;  # controlled - no special characters
        if (strlen($hloc) === 0) {
            $hloc = '';
        }
        $marker = $row->marker;  # controlled - no special characters
        if (strlen($marker) === 0) {
            $marker = '';
        }
        $coll = $row->clusterStr;  # controlled - no special characters
        if (strlen($coll) === 0) {
            $coll = '';
        }
        $clus = $row->clusGrp;  # controlled - no special characters
        if (strlen($clus) === 0) {
            $clus = '';
        }
        $grpName = $row->cgName;  # controlled - no special characters
        if (strlen($grpName) === 0) {
            $grpName = '';
        }
        $log = $row->logistics;  # controlled - no special characters
        if (strlen($log) === 0 ) {
            $log = '';
        }
        $dist = $row->miles;
        if (strlen($dist) === 0 ) {
            $dist = '';
        } else {
            $dist = floatval($dist);
        }
        $elev = $row->feet;
        if (strlen($elev) === 0 ) {
            $elev = '';
        } else {
            $elev = intval($elev);
        }
        $diff = $row->difficulty;  # controlled - no special characters
        if (strlen($diff) === 0 ) {
            $diff = '';
        }
        $facil = $row->facilities;
        if (strlen($facil) === 0 ) {
            $facil = '';
        } else {
            $facil = mysqli_real_escape_string($link,$facil);
        }
        $wow = $row->wow;
        if (strlen($wow) === 0 ) {
            $wow = '';
        } else {
            $wow = mysqli_real_escape_string($link,$wow);
        }
        $seasons = $row->seasons;
        if (strlen($seasons) === 0 ) {
            $seasons = '';
        } else {
            $seasons = mysqli_real_escape_string($link,$seasons);
        }
        $exp = $row->expo;  # controlled - no special characters
        if (strlen($exp) === 0 ) {
            $exp = '';
        }
        $gpx = $row->gpxfile;  # filename - no special characters
        if (strlen($gpx) === 0 ) {
            $gpx = '';
        }
        $trk = $row->trkfile;  # filename - no special characters
        if (strlen($trk) === 0 ) {
            $trk = '';
        }
        $lat = $row->lat;
        if (strlen($lat) === 0 ) {
            $lat = '';
        } else {
            $lat = floatval($lat);
        }
        $lng = $row->lng;
        if (strlen($lng) === 0 ) {
            $lng = '';
        } else {
            $lng = floatval($lng);
        }
        # ADD-ON IMAGES HAVE SUB-ELEMENTS: (won't work w/o __toString() !!)
        if($row->aoimg1->name->count() === 0) {
            $ao1 = '';
        } else {
            $addon1 = [];
            $addon1[0] = $row->aoimg1->name->__toString();
            $addon1[1] = $row->aoimg1->iht->__toString();
            $addon1[2] = $row->aoimg1->iwd->__toString();
            $ao1 = serialize($addon1);
        }
        if ($row->aoimg2->name->count() === 0 ) {
            $ao2 = '';
        } else {
            $addon2 = [];
            $addon2[0] = $row->aoimg2->name->__toString();
            $addon2[1] = $row->aoimg2->iht->__toString();
            $addon2[2] = $row->aoimg2->iwd->__toString();
            $ao2 = serialize($addon2);
        }
        /*
         * At some time, some of the url's may have been encoded for one reason or another...
         */
        $url1 = urldecode($row->mpUrl);
        if (strlen($url1) === 0 ) {
            $url1 = '';
        } else {
            $url1 = mysqli_real_escape_string($link,$url1);
        }
        $url2 = urldecode($row->spUurl);
        if (strlen($url2) === 0 ) {
            $url2 = '';
        } else {
            $url2 = mysqli_real_escape_string($link,$url2);
        }
        $dirs = urldecode($row->dirs);
        if (strlen($dirs) === 0 ) {
            $dirs = '';
        } else {
            $dirs = mysqli_real_escape_string($link,$dirs);
        }
        $tips = $row->tipsTxt;
        if (strlen($tips) === 0 ) {
            $tips = '';
        } else {
            $tips = mysqli_real_escape_string($link,$tips);
        }
        $desc = $row->hikeInfo;
        if (strlen($desc) === 0 ) {
            $desc = '';
        } else {
            $desc = mysqli_real_escape_string($link,$desc);
        }
        $SQL_query = "INSERT INTO HIKES " .
            "( pgTitle,usrid,locale,marker," .
            "collection,cgroup,cname," .
            "logistics,miles,feet," .
            "diff,fac,wow," .
            "seasons,expo,gpx," .
            "trk,lat,lng," .
            "aoimg1,aoimg2,purl1," .
            "purl2,dirs,tips," .
            "info  ) " .
            "VALUES ( '{$htitle}','mstr','{$hloc}','{$marker}'," .
            "'{$coll}','{$clus}','{$grpName}'," .
            "'{$log}','{$dist}','{$elev}'," .
            "'{$diff}','{$facil}','{$wow}'," .
            "'{$seasons}','{$exp}','{$gpx}'," .
            "'{$trk}','{$lat}','{$lng}'," .
            "'{$ao1}','{$ao2}','{$url1}'," .
            "'{$url2}','{$dirs}','{$tips}'," .
            "'{$desc}' );";
        $req = mysqli_query( $link,$SQL_query );
        if (!$req) {
            die("Failed to add data to HIKES: " . mysqli_error());
        } else {
            echo $row->indxNo . "..";
            if (intval($row->indxNo) % 10 === 0) {
                echo '<br />';
            }
        }
    }
    mysqli_close($link);
    echo "<p>Maximum lengths for: <br />";
    echo "Refs: " . $maxref . "<br />";
    echo "Props: " . $maxprop . "<br />";
    echo "Acts: " . $maxact . "<br />";
    echo "Tables: " . $maxtbl . "<br />";
    echo "Pics: " . $maxpic . "</p>";
    
?>
        <p>Done!</p>
    </div>
</body>
</html>
