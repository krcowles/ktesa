<?php
/**
 * This file will read the existing HIKES table to gather data
 * pertaining to Visitor Centers and Clusters, and populate the CLUSTERS 
 * and CLUSHIKES tables. The Visitor Center-to-Cluster transformation gets
 * somewhat complex owing to the fact that some previous clusters will no
 * longer exist, and those hikes will be transferred to the new 'VC' type
 * clusters.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

/**
 * Address current Visitor Centers as new clusters:
 * NOTE: There will be no VC's in EHIKES
 */
$vc_req = "SELECT `indxNo`,`pgTitle`,`lat`,`lng` FROM HIKES " .
    "WHERE `marker` = 'Visitor Ctr';";
$vcs = $pdo->query($vc_req)->fetchAll(PDO::FETCH_ASSOC);
foreach ($vcs as $vc) {
    $clus = "INSERT INTO `CLUSTERS` (`group`,`lat`,`lng`,`page`) VALUES " .
        "(?,?,?,?);";
    $clusStmnt = $pdo->prepare($clus);
    $clusStmnt->execute(
        [$vc['pgTitle'], $vc['lat'], $vc['lng'], $vc['indxNo']]
    );
}

/**
 * Now get ALL currently assigned clusters in db, but do not add the
 * cluster for either 'Boca Negra Group' or for 'Yellow House Group' as
 * these are now obsolete. The hikes in those groups are being moved to
 * the new 'VC' clusters.
 */
$cl_req = "SELECT `cname`,`lat`,`lng` FROM HIKES WHERE " .
    "`cname` <> 'NULL' AND `cname` <> '';";
$cls = $pdo->query($cl_req)->fetchAll(PDO::FETCH_ASSOC);
$names = [];
$groups = [];
foreach ($cls as $cl) {
    if (!in_array($cl['cname'], $names) && $cl['cname'] !== 'Boca Negra Group'
        && $cl['cname'] !== 'Yellow House Group'
    ) {
        array_push($names, $cl['cname']);
        $cluster = array(
            "group" => $cl['cname'],
            "lat" => $cl['lat'],
            "lng" => $cl['lng']);
        array_push($groups, $cluster);
    }
}
foreach ($groups as $cluster) {
    $clus = "INSERT INTO `CLUSTERS` (`group`,`lat`,`lng`,`page`) VALUES " .
        "(?,?,?,?);";
    $clusStmnt = $pdo->prepare($clus);
    $clusStmnt->execute(
        [$cluster['group'], $cluster['lat'], $cluster['lng'], '0']
    );
}

/**
 * All 'At VC' hikes will be added to their respective Visitor Centers, and
 * items not previously marked as 'At VC' but belonging to the VC are added
 * manually. Note that there are no Clusters = 98, or 99: those items will
 * now be pointed to their new index in CLUSTERS: 5, 6.
 */
$atvc_req = "SELECT `indxNo`,`collection` FROM `HIKES` WHERE `marker` = 'At VC';";
$atvcs = $pdo->query($atvc_req)->fetchAll(PDO::FETCH_ASSOC);
foreach ($atvcs as &$oldvc) {
    if ($oldvc['collection'] == 98) {
        $oldvc['collection'] = 5;
    }
    if ($oldvc['collection'] == 99) {
        $oldvc['collection'] = 6;
    }
}
insert($atvcs, $pdo);
// manual entries
$chaco = [];
array_push($chaco, array("indxNo" => '83', "collection" => '2')); // Normal
array_push($chaco, array("indxNo" => '84', "collection" => '2')); // Normal
insert($chaco, $pdo);
$elmalp = [];
array_push($elmalp, array("indxNo" => '79',  "collection" => '3')); // Normal
array_push($elmalp, array("indxNo" => '80',  "collection" => '3')); // Normal
array_push($elmalp, array("indxNo" => '81',  "collection" => '3')); // Normal
array_push($elmalp, array("indxNo" => '202', "collection" => '3')); // Normal
array_push($elmalp, array("indxNo" => '204', "collection" => '3')); // Normal
insert($elmalp, $pdo);
$petro = [];
array_push($petro, array("indxNo" => '76', "collection" => '4')); // Normal
array_push($petro, array("indxNo" => '77', "collection" => '4')); // Normal
insert($petro, $pdo);

/**
 * Fill the remaining CLUSHIKES with hikes actually currently assigned
 * to a cluster group (but not Boca Negra or Yellow House). EHIKES will
 * not be addressed since no hikes in EHIKES have assigned indxNo for
 * the HIKES table until published.
 */
$hclus_req = "SELECT `indxNo`,`cname` FROM `HIKES` WHERE " .
    "`cname` <>'NULL' AND `cname` <> '';";
$hclus = $pdo->query($hclus_req)->fetchAll(PDO::FETCH_ASSOC);
$clusname_req = "SELECT `clusid`,`group` FROM `CLUSTERS`;";
$clusArray = $pdo->query($clusname_req)->fetchAll(PDO::FETCH_ASSOC);
//split into two arrays of items: clusid & group name
$clusids = [];
$groups  = [];
foreach ($clusArray as $item) {
    if ($item['group'] !== 'Boca Negra Group'
        && $item['group'] !== 'Yellow House Group'
    ) {
        array_push($clusids, $item['clusid']);
        array_push($groups, $item['group']);
    }
}
// Two clusters no longer exist: Yellow House Group and Boca Negra Group
foreach ($hclus as $cluster) {
    if ($cluster['cname'] == 'Yellow House Group') {
        $cluster['cname'] = "Chaco Index";
    }
    if ($cluster['cname'] == 'Boca Negra Group') {
        $cluster['cname'] = "Petroglyphs Index";
    }
    $cluskey = array_search($cluster['cname'], $groups);
    $clusid = $clusids[$cluskey];
    $newchike_req = "INSERT INTO `CLUSHIKES` (`indxNo`,`cluster`) VALUES " .
        "(:hikeno,:cluster);";
    $entry = $pdo->prepare($newchike_req);
    $entry->execute(["hikeno" => $cluster['indxNo'], "cluster" => $clusid]);
}

/**
 * Place hike lists into the CLUSHIKES table
 * 
 * @param array $hikes The associative array of hikes => vc indx
 * @param PDO   $pdo   The PDO object
 * 
 * @return null
 */
function insert($hikes, $pdo)
{
    foreach ($hikes as $hike) {
        // special cases: indxNo <> clusid
        if ($hike['indxNo'] == '47') {
            $hike['collection'] = 5;
        }
        if ($hike['indxNo'] == '96' || $hike['indxNo'] == '150') {
            $hike['collection'] = 6;
        } 
        $ch_req = "INSERT INTO `CLUSHIKES` (`indxNo`,`cluster`) VALUES (?,?);";
        $ch = $pdo->prepare($ch_req);
        $ch->execute([$hike['indxNo'],$hike['collection']]);
    }
    return;
}

header("Location: admintools.php");
