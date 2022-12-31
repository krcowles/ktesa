<?php
/**
 * This file creates the javascript objects required to load the home page map,
 * create markers and tracks, and set up side tables. Note: JSON objects
 * will have no white space. 'Clusters' are not hikes, just markers, so those
 * objects will have a different composition than 'Normal' (non-cluster) hikes.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */

/**
 * 'Side tables' needs to know which hike object type to use for a given hike
 * no., hence for every hike no. encountered ($allHikeIndices) there is a
 * corresponding $locater indicating object type, and object no. within that type.
 *       ---------------------------  NOTE: -------------------------
 * This version checks for the existence of HTML special entity characters in
 * hike and cluster names. HTML special entity characters are those listed in
 * ISO 8859-1 as 'Characters' (not 'Symbols'). These entities will be rendered by
 * HTML, on the page, as special characters, i.e. letters with diacritical marks.
 * This script will convert any entity names to their equivalent entity number,
 * since the javascript searchbar algorithm (sideTables.ts) retrieves only the
 * entity number, and not the name.
 */
$allHikeIndices = [];
$locaters = [];
// These hikes were previously listed as 'Normal' and are now clustered
$moved = array(76, 77, 79, 80, 81, 83, 84, 202, 204);
// The above is for reference only, and is otherwise unused.

/**
 * Retrieve cluster & hike data from db
 */
$clusdata = $pdo->query("SELECT * FROM `CLUSTERS` WHERE `pub`='Y';");
$clusters = $clusdata->fetchAll(PDO::FETCH_ASSOC);
$clushike_req = "SELECT `indxNo`,`cluster` FROM `CLUSHIKES` WHERE `pub`='Y';";
$clushikes 
    = $pdo->query($clushike_req)->fetchAll(PDO::FETCH_ASSOC);
$hike_req = "SELECT `pgTitle`,`indxNo`,`miles`,`feet`,`diff`,`lat`,`lng`," .
    "`preview`,`dirs` FROM `HIKES` ORDER BY `pgTitle`;";
$hikes = $pdo->query($hike_req)->fetchAll(PDO::FETCH_ASSOC);

/**
 * Retrieve the highest indxNo in HIKES for sizing the $hikePairings array:
 */ 
$indxNo_req = "SELECT `indxNo` FROM `HIKES` ORDER BY 1 DESC LIMIT 1;";
$last = $pdo->query($indxNo_req);
$lastno = $last->fetch(PDO::FETCH_ASSOC);
$pairingLim = $lastno['indxNo'] + 1;
$hikePairings = []; // initialize: each key has an associated array
for ($i=0; $i<$pairingLim; $i++) {
    $hikePairings[$i] = [];
}

$normalObjs  = [];
$clusterObjs = [];

/**
 * A typical cluster object makeup:
 * { seqno:0,
 *   group:"Bandelier Index",
 *   loc:{lat:35.779039,lng:-106.270788},
 *   page:1,
 *   hikes:[
 *      (see hike objects)
 *   ]
 *  }
 * NOTE: A sequence number has been added to accommodate cases where a cluster
 * is deleted, and the clusid's are no longer purely sequential
 */
$pages     = [];
$pageNames = [];
$clus2seq  = []; // associates a clusid to a sequence no in the $clusObjs
for ($j=0; $j<count($clusters); $j++) {
    $pgtitle = $clusters[$j]['group'];
    $cid = $clusters[$j]['clusid'];
    $clus2seq[$cid] = $j;
    $partial = '{seqno:' . $j . ',group:"' . $pgtitle . '",loc:{lat:' .
        $clusters[$j]['lat']/LOC_SCALE  . ',lng:' . $clusters[$j]['lng']/LOC_SCALE .
        '},page:' . $clusters[$j]['page'] . ',hikes:[';
    array_push($clusterObjs, $partial);
    if (!empty($clusters[$j]['page'])) {
        array_push($pages, $clusters[$j]['page']); // 'page' is a HIKES indxNo
        array_push($pageNames, '"' . $clusters[$j]['group'] . '"');
    }
}
 /**
 * There is now a sequentially indexed array of all clusters [$clusObjs],
 * soon to be CL objects in the javascript used by sideTables.ts/js
 * once each cluster's list of hikes is appended
 * 
 * Some indxNo's may have > 1 clusid's: assemble array associating indxNo
 * with one..many clusters (an array itself)
 */
foreach ($clushikes as $entry) {
    array_push($hikePairings[$entry['indxNo']], $entry['cluster']);
}

/**
 * Walk through the all the hikes and assign cluster objects;
 * Some hikes may be in more than one cluster, so find frequency 
 * per hike indxNo
 */
$nmindx = 0;
foreach ($hikes as $hike) {
    if (!in_array($hike['indxNo'], $pages)) { // no 'Cluster Pages'
        array_push($allHikeIndices, $hike['indxNo']);
        // PROPOSED HIKES may not have all the data:
        if (strpos($hike['pgTitle'], '[Proposed]') !== false) {
            $hike['miles'] = empty($hike['miles']) ? 0 : $hike['miles'];
            $hike['feet']  = empty($hike['feet'])  ? 0 : $hike['feet'];
            $hike['diff']  = empty($hike['diff'])  ? "Unrated" : $hike['diff'];
        }
        $pairing = $hikePairings[$hike['indxNo']];  // this is an array
        if (count($pairing) > 0) { 
            // ---- this is a 'Cluster' hike ----
            $repeats = 0; // there can be more than one cluster per hike
            foreach ($pairing as $clusterid) {
                $clusGroupNo = $clus2seq[$clusterid];
                // only one locater can be used per hike
                if ($repeats === 0) { 
                    $locater = '{type:"cl",group:' . $clusGroupNo . '}';
                    array_push($locaters, $locater);
                    $repeats++;
                }
                // form the cluster's hike object
                $chikeObj = '{name:"' . $hike['pgTitle']. '",indx:' .
                    $hike['indxNo'] . ',lgth:' . $hike['miles'] . ',elev:' .
                    $hike['feet'] . ',diff:"' . $hike['diff'] . '",loc:{lat:' .
                    $hike['lat']/LOC_SCALE . ',lng:' . $hike['lng']/LOC_SCALE .
                    '},' . 'prev:"' . $hike['preview'] . '"}';
                if (substr($clusterObjs[$clusGroupNo], -1) == '}') {
                    $clusterObjs[$clusGroupNo] .= ',' . $chikeObj;
                } else {
                    $clusterObjs[$clusGroupNo] .= $chikeObj;
                }
            }      
        } else {
            $locater = '{type:"nm",group:' . $nmindx++ . '}';
            array_push($locaters, $locater);
            $hikeObj = '{name:"' . $hike['pgTitle'] . '",indx:' .
                $hike['indxNo'] . ',loc:{lat:' . $hike['lat']/LOC_SCALE .
                ',lng:' . $hike['lng']/LOC_SCALE . '},lgth:' .
                $hike['miles'] . ',elev:' . $hike['feet'] . ',diff:"' .
                $hike['diff'] . '",prev:"' . $hike['preview'] . '",dirs:"' .
                $hike['dirs'] . '"}';
            array_push($normalObjs, $hikeObj);
        }
    }
}

// properly terminate each cluster object
foreach ($clusterObjs as &$cobj) {
    $cobj .= ']}';
}
// form single js variable;
$jsClusters = '[' . implode(",", $clusterObjs) . ']';
$jsHikes    = '[' . implode(",", $normalObjs) . ']';
$jsPages    = '[' . implode(",", $pages) . ']';
$jsPageNames  = '[' . implode(",", $pageNames) . ']';

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
