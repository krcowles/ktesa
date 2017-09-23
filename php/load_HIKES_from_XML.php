<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Load the XML Database</title>
    <meta charset="utf-8" />
    <meta name="description" content="Use MySql database" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
</head>
<body>
    <div>
        <p>Begin loading the HIKES table in the database from the database.xml file</p>
<?php
    $db = simplexml_load_file('../data/database.xml');
    if (!$db) {
        $errmsg = '<p style="color:red;font-size:18px;margin-left:16px">' .
            'Failed to load xml database.</p>';
        die($errmsg);
    }
    # Extract each row's variables and load into mysql HIKES table
    # NOTE: 'serialize' will have content even when the array is empty.
    $maxref = 0;
    $maxprop = 0;
    $maxact = 0;
    $maxtsv = 0;
    $maxtbl = 0;
    
    require "000mysql_connect.php";
    echo "<p>Opened</p>";

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
        $log = $row->logisitics;  # controlled - no special characters
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
        $ao1 = $row->aoimg1;  # filename - no special characters
        if (strlen($ao1) === 0 ) {
            $ao1 = '';
        }
        $ao2 = $row->aoimg2;  # filename - no special characters
        if (strlen($ao2) === 0 ) {
            $ao2 = '';
        }
        $url1 = $row->mpUrl;
        if (strlen($url1) === 0 ) {
            $url1 = '';
        } else {
            $url1 = mysqli_real_escape_string($link,$url1);
        }
        $url2 = $row->spUurl;
        if (strlen($url2) === 0 ) {
            $url2 = '';
        } else {
            $url2 = mysqli_real_escape_string($link,$url2);
        }
        $dirs = $row->dirs;
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
        $refarray = [];
        $refitem = [];
        foreach ($row->refs->ref as $ref) {
            $refitem[0] = $ref->rtype;
            $refitem[1] = $ref->rit1;
            $refitem[2] = $ref->rit2;
            $catref = implode("^",$refitem);
            array_push($refarray,$catref);
        }
        if (count($refarray) === 0) {
            $refs = '';
        } else {
            $refs = serialize($refarray);
            if (strlen($refs) > $maxref) {
                $maxref = strlen($refs);
            }
            $refs = mysqli_real_escape_string($link,$refs);
        }
        $proparray = [];
        $propitem = [];
        foreach ($row->dataProp->prop as $prop) {
            $propitem[0] = $prop->plbl;
            $propitem[1] = $prop->purl;
            $propitem[2] = $prop->pcot;
            $catprop = implode("^",$propitem);
            array_push($proparray,$catprop);
        }
        if (count($proparray) === 0) {
            $props = '';
        } else {
            $props = serialize($proparray);
            if (strlen($props) > $maxprop) {
                $maxprop = strlen($props);
            }
            $props = mysqli_real_escape_string($link,$props);
        }
        $actarray = [];
        $actitem = [];
        foreach ($row->dataAct->act as $act) {
            $actitem[0] = $act->albl;
            $actitem[1] = $act->aurl;
            $actitem[2] = $act->acot;
            $catact = implode("^",$actitem);
            array_push($actarray,$catact);
        }
        if (count($actarray) === 0) {
            $acts = '';
        } else {
            $acts = serialize($actarray);
            if (strlen($acts) > $maxact) {
                $maxact = strlen($acts);
            }
            $acts = mysqli_real_escape_string($link,$acts);
        }
        # there are xml tag differences between a hike page and an index page:
        if ($row->content->count() !== 0) {  # this is an index page
            $content = [];
            $tblitem = [];
            foreach ($row->content->tblRow as $trow) {
                $tblitem[0] = $trow->compl;
                $tblitem[1] = $trow->tdname;
                $tblitem[2] = $trow->tdpg;
                $tblitem[3] = $trow->tdmiles;
                $tblitem[4] = $trow->tdft;
                $tblitem[5] = $trow->tdexp;
                $tblitem[6] = $trow->tdalb;
                $cattbl = implode("^",$tblitem);
                array_push($content,$cattbl);
            }
            if (count($content) === 0) {
                $tsv = '';
            } else {
                $tsv = serialize($content);
                if (strlen($tsv) > $maxtbl) {
                    $maxtbl = strlen($tsv);
                }
                $tsv = mysqli_real_escape_string($link,$tsv);
            }
            
        } else {
            $tsvarray = [];
            $tsvitem = [];
            foreach ($row->tsv->picDat as $img) {
                $tsvitem[0] = $img->folder;
                $tsvitem[1] = $img->title;
                $tsvitem[2] = $img->hpg;
                $tsvitem[3] = $img->mpg;
                $tsvitem[4] = $img->desc;
                $tsvitem[5] = $img->lat;
                $tsvitem[6] = $img->lng;
                $tsvitem[7] = $img->thumb;
                $tsvitem[8] = $img->alblnk;
                $tsvitem[9] = $img->date;
                $tsvitem[10] = $img->mid;
                $tsvitem[11] = $img->symbol;
                $tsvitem[12] = $img->icon_size;
                $tsvitem[13] = $img->iclr;
                $tsvitem[14] = $img->imgHt;
                $tsvitem[15] = $img->imgWd;
                $tsvitem[16] = $img->org;
                $cattsv = implode("^",$tsvitem);
                array_push($tsvarray,$cattsv);
            }
            if (count($tsvarray) === 0) {
                $tsv = '';
            } else {
                $tsv = serialize($tsvarray);
                if (strlen($tsv) > $maxpic) {
                    $maxpic = strlen($tsv);
                }
                $tsv = mysqli_real_escape_string($link,$tsv);
            }
        }
echo "Start " . "D";
        $SQL_query = "INSERT INTO HIKES " .
            "( pgTitle,locale,marker," .
            "collection,cgroup,cname," .
            "logistics,miles,feet," .
            "diff,fac,wow," .
            "seasons,expo,gpx," .
            "trk,lat,lng," .
            "aoimg1,aoimg2,purl1," .
            "purl2,dirs,tips," .
            "info,refs,props ) " .
/*
            "acts,tsv,  ) " .
*/
            "VALUES ( '{$htitle}','{$hloc}','{$marker}'," .
            "'{$coll}','{$clus}','{$grpName}'," .
            "'{$log}','{$dist}','{$elev}'," .
            "'{$diff}','{$facil}','{$wow}'," .
            "'{$seasons}','{$exp}','{$gpx}'," .
            "'{$trk}','{$lat}','{$lng}'," .
            "'{$ao1}','{$ao2}','{$url1}'," .
            "'{$url2}','{$dirs}','{$tips}'," .
            "'{$desc}','{$refs}','{$props}' );";
/*
            "'{$acts}','{$tsv}' );";
*/
        $req = mysqli_query( $link,$SQL_query );
        if (!$req) {
            die("Failed to add data to HIKES: " . mysqli_error());
        } else {
            echo $row->indxNo . "..";
        }
    }
    
    echo "Maximum lengths for: <br />";
    echo "Refs: " . $maxref . "<br />";
    echo "Props: " . $maxprop . "<br />";
    echo "Acts: " . $maxact . "<br />";
    echo "Tables: " . $maxtbl . "<br />";
    echo "Pics: " . $maxpic . "<br />";
    
?>
        <p>Done!</p>
    </div>
</body>
</html>