<?php
/**
 * This file creates the javascript objects required to load the home page map,
 * create markers and tracks, and set up side tables. Note: JSON objects
 * will have no white space.
 * PHP Version 7.1
 * 
 * @package Map
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "global_boot.php";
/**
 * 'Side tables' needs to know which hike object to use for a given hike no,
 * hence for every hike index encountered ($allHikeIndices) there is a 
 * corresponding $locater indicating group and object within the group. 
 */
$allHikeIndices = [];
$locaters = [];

/**
 * Form array of Visitor Center objects
 */
$vcrequest = "SELECT `indxNo`,`pgTitle`,`marker`,`collection`,`miles`,`feet`," .
    "`diff`,`lat`,`lng`,`dirs` FROM `HIKES` WHERE marker='Visitor Ctr' " .
    "OR marker='At VC';";
$vcinfo = $pdo->query($vcrequest);
$vcdata = $vcinfo->fetchAll(PDO::FETCH_ASSOC);
$vcs = [];
$vchikes = [];
foreach ($vcdata as $item) {
    if ($item['marker'] == 'Visitor Ctr') {
        array_push($vcs, $item);
    } else {
        array_push($vchikes, $item);
    }
}
$vcobj = [];
$hikeobj = [];
$vcno = 0;
foreach ($vcs as $vc) {
    $atvcs = explode(".", $vc['collection']);
    $leader = '{name:"' . $vc['pgTitle'] . '",indx:' . $vc['indxNo'] .
        ',loc:{lat:' . $vc['lat']/LOC_SCALE . ',lng:' . $vc['lng']/LOC_SCALE . '},hikes:[';
    foreach ($vchikes as $atvc) {
        if (in_array($atvc['indxNo'], $atvcs)) {
            // add this hike objext to current $leader
            $hobj = '{name:"' . $atvc['pgTitle'] . '",indx:' . $atvc['indxNo'] .
                ',lgth:' . $atvc['miles'] . ',elev:' . $atvc['feet'] .
                ',diff:"' . $atvc['diff'] . '",lat:' .
                $atvc['lat']/LOC_SCALE . ',lng:' .
                $atvc['lng']/LOC_SCALE . '}';
            array_push($hikeobj, $hobj);
            array_push($allHikeIndices, $atvc['indxNo']);
            $loc = '{type:"vc",group:' . $vcno . '}';
            array_push($locaters, $loc);
        }
    }
    $leader .= implode(",", $hikeobj) . ']}';
    $hikeobj = [];
    array_push($vcobj, $leader);
    $vcno++;
}
$jsVCs = '[' . implode(",", $vcobj) . ']';

/**
 * Form array of Cluster objects
 */
$clusterdat = "SELECT `indxNo`,`pgTitle`,`cgroup`,`cname`,`miles`,`feet`,`diff`," .
    "`lat`,`lng` FROM `HIKES` WHERE marker='Cluster';";
$clusterRequest = $pdo->query($clusterdat);
$clusters = $clusterRequest->fetchAll(PDO::FETCH_ASSOC);
$enrolled = [];
$leaders  = [];
$hikeobj  = [];
// form main objects for clusters (w/o hikes)
for ($j=0; $j<count($clusters); $j++) {
    if (!in_array($clusters[$j]['cgroup'], $enrolled)) {
        array_push($enrolled, $clusters[$j]['cgroup']); // $id-th item
        $lead = '{group:"' . $clusters[$j]['cname'] . '",loc:{lat:' .
            $clusters[$j]['lat']/LOC_SCALE .
            ',lng:' . $clusters[$j]['lng']/LOC_SCALE . '},hikes:[';
        array_push($leaders, $lead);
    }
}
// form hike objects belonging to each cluster object
for ($k=0; $k<count($clusters); $k++) {
    $hobj = '{name:"' . $clusters[$k]['pgTitle'] . '",indx:' .
        $clusters[$k]['indxNo'] . ',lgth:' . $clusters[$k]['miles'] .',elev:' .
        $clusters[$k]['feet'] . ',diff:"' . $clusters[$k]['diff'] . 
        '",lat:' . $clusters[$k]['lat']/LOC_SCALE . ',lng:' .
        $clusters[$k]['lng']/LOC_SCALE . '}';
    $clus = $clusters[$k]['cgroup'];
    $prefix = array_search($clus, $enrolled);
    $hobj = $prefix . ":" . $hobj;
    array_push($hikeobj, $hobj);
    array_push($allHikeIndices, $clusters[$k]['indxNo']);
}
// glue the hike objects into the cluster objects for single array of objects
$firsts = [];
for ($i=0; $i<count($hikeobj); $i++) {
    $idpos = strpos($hikeobj[$i], ":");
    $idno = substr($hikeobj[$i], 0, $idpos);
    if (!in_array($idno, $firsts)) {
        array_push($firsts, $idno);
    } else {
        $leaders[$idno] .= ",";
    }
    $loc = '{type:"cl",group:' . $idno . '}';
    array_push($locaters, $loc);
    $leaders[$idno] .= substr($hikeobj[$i], $idpos+1);
}
// properly terminate each cluster object
foreach ($leaders as &$cobj) {
    $cobj .= ']}';
}
// form single js variable;
$jsClusters = '[' . implode(",", $leaders) . ']';

/**
 * Form individual ('Normal') hike array
 */
$hikeobj = [];
$hikedata = "SELECT `pgTitle`,`indxNo`,`miles`,`feet`,`diff`,`lat`,`lng`,`dirs` " .
    "FROM `HIKES` WHERE marker='Normal';";
$hikesreq = $pdo->query($hikedata);
$hikes = $hikesreq->fetchAll(PDO::FETCH_ASSOC);
$nmindx = 0;
foreach ($hikes as $hike) {
    array_push($allHikeIndices, $hike['indxNo']);
    $loc = '{type:"nm",group:' . $nmindx++ . '}';
    array_push($locaters, $loc);
    $hike = '{name:"' . $hike['pgTitle'] . '",indx:' . $hike['indxNo'] .
        ',loc:{lat:' . $hike['lat']/LOC_SCALE . ',lng:' .
        $hike['lng']/LOC_SCALE . '},lgth:' .
        $hike['miles'] . ',elev:' . $hike['feet'] . ',diff:"' . $hike['diff'] .
        '",dir:"' . $hike['dirs'] . '"}';
    array_push($hikeobj, $hike);
}
$jsHikes = '[' . implode(",", $hikeobj) . ']';

/**
 * Form array of json file names
 */
$trackArray = [];
$inos = $pdo->query('SELECT MAX(indxNo) FROM `HIKES`;')->fetch(PDO::FETCH_NUM);
for ($t=0; $t<$inos[0]; $t++) {
    $trackArray[$t] = "''";
}
$trackdat = $pdo->query('SELECT `indxNo`,`trk` FROM `HIKES`');
$tracks = $trackdat->fetchAll(PDO::FETCH_ASSOC);
foreach ($tracks as $track) {
    if (!empty($track['trk'])) {
        $track['trk'] = "'" . $track['trk'] . "'";
        $trackArray[$track['indxNo']] = $track['trk'];
    }
}
$jsTracks = '[' . implode(",", $trackArray) . ']';

// All legitmate hike indices: not including deleted hikes or index pages
$jsIndx = '[' . implode(",", $allHikeIndices) . ']';
// Object location for each index
$jsLocs = '[' . implode(",", $locaters) . ']';
