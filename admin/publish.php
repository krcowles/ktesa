<?php
/**
 * This script will  'publish' an in-edit hike or cluster page by transferring
 * the data from EHIKES and E-Tables to the HIKES and Hike Tables. The hike will
 * no longer appear in the 'in-edit' list. If the EHIKE has a cluster assignment
 * in 'cname', then update CLUSHIKES. For new Cluster pages, update the CLUSTERS
 * 'page' field with the new HIKES indxNo: already published cluster pages need
 * no adjustment in this field.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$hikeNo      = filter_input(INPUT_GET, 'hno');
$clusterPage = isset($_GET['clus']) && $_GET['clus'] === 'y' ? true : false;
$msgout      = '';

// next hike no if published as brand new hike
$last = "SELECT * FROM HIKES ORDER BY 1 DESC LIMIT 1;";
$lasthike = $pdo->query($last);
$item = $lasthike->fetch(PDO::FETCH_NUM);
$lastHikeNo = intval($item[0]);

$query = "SELECT * FROM EHIKES WHERE indxNo = :hikeNo;";
$ehk = $pdo->prepare($query);
$ehk->execute(["hikeNo" => $hikeNo]);
$ehike = $ehk->fetch(PDO::FETCH_ASSOC);
if ($ehike === false) {
    $msgout .= "<p class='brown'>Hike {$hikeNo} has no data!</p>";
}
$clusPgField = $ehike['pgTitle']; // used if publishing a new cluster page
$cname = $ehike['cname'];

/**
 * Validate key data: some data must not be 'empty' in order to prevent
 * viewing problems; see comments below
 */
if ($clusterPage) {
    // Data omission here prevents displaying cluster on main map (mapJsData.php)
    $cpClustersReq = "SELECT * FROM `CLUSTERS` WHERE `group`=?;";
    $clusterData = $pdo->prepare($cpClustersReq);
    $clusterData->execute([$clusPgField]);
    $cdat = $clusterData->fetch(PDO::FETCH_ASSOC);
    if (is_null($cdat['lat']) || is_null($cdat['lng'])) {
        $msgout 
            .= "<p class='brown'>Missing lat or lng for Cluster {$clusPgField}</p>";
    }
} else {
    // Data omission here will cause issues in mapJsData.php on home page; no display
    if (!empty($cname)) {
        // if a group hike page, validate data in CLUSTERS (whether or not published)
        $clusterDataReq = "SELECT * FROM `CLUSTERS` WHERE `group`=?;";
        $clusterData = $pdo->prepare($clusterDataReq);
        $clusterData->execute([$cname]);
        $clusdat = $clusterData->fetch(PDO::FETCH_ASSOC);
        if ($clusdat === false) {
            throw new Exception("Could not find {$cname} in CLUSTERS");
        }
        if (empty($clusdat['lat']) || empty($clusdat['lng'])) {
            $msgout .= '<p class="brown">Missing lat or lng data in CLUSTERS</p>';
        }
    }
    if (empty($ehike['miles']) || empty($ehike['feet'])|| empty($ehike['diff'])) {
        $msgout .= '<p class="brown">Missing miles, feet, or difficulty data</p>';
    }
    if (empty($ehike['lat']) || empty($ehike['lng'])) {
        $msgout .= '<p class="brown">Missing lat or lng data</p>';
    }
}
if (empty($ehike['dirs'])) {
    $msgout .= '<p class="brown">Missing directions link</p>';
}
$status = intval($ehike['stat']);
if ($status > $lastHikeNo || $status < 0) {
    $msgout .="<p class='brown'>Status out-of-range: {$status}</p>";
}

/**
 * Continue ONLY IF NO ERRORS...
 */
if ($msgout == '') {    
    // Get column names for buillding query strings
    $result = $pdo->query("SHOW COLUMNS FROM EHIKES;");
    $columns = $result->fetchAll(PDO::FETCH_BOTH);
    if ($status > 0) { // this is an existing hike, UPDATE its record in HIKES
        $query = "UPDATE HIKES, EHIKES SET ";
        foreach ($columns as $column) {
            if (($column[0] !== "indxNo") && ($column[0] !== "stat")
                && $column[0] !== "cname"
            ) {
                $query .= "HIKES.{$column[0]} = EHIKES.{$column[0]}, ";
            }
        }
        $query = rtrim($query, ", "); // remove final comma and space
        $query .= " WHERE HIKES.indxNo = :status AND EHIKES.indxNo = :hikeNo;";
        $updte = $pdo->prepare($query);
        $updte->bindValue(":status", $status);
        $updte->bindValue(":hikeNo", $hikeNo);
    } else { // this is a new hike, INSERT its record into HIKES
        $query = "INSERT INTO HIKES (";
        $fields = '';
        foreach ($columns as $column) {
            if (($column[0] !== "indxNo") && ($column[0] !== "stat")
                && ($column[0] !== "cname")
            ) {
                $fields .= "{$column[0]}, ";
            }
        }
        $fields = rtrim($fields, ", "); // remove final comma and space
        $query .= $fields . ") SELECT " . $fields .
            " FROM EHIKES WHERE indxNo = :hikeNo;";
        $updte = $pdo->prepare($query);
        $updte->bindValue(":hikeNo", $hikeNo);
    }
    $updte->execute();

    // Assign the hike number for the remaining tables based on status:
    if ($status === 0) { // this will be the newly added hikeno.
        $indxNo = $lastHikeNo + 1;
    } else { // this will be the already published hikeno
        $indxNo = $status;
    }
    /**
     * In the cases of EGPSDAT, EREFS, and ETSV, elements may have been
     * deleted during edit, therefore, remove ALL the old data if the
     * hike was type 'published'. Insert new data (no UPDATEs, only INSERTs)
     */
    //  ---------------------  GPSDAT -------------------
    if (!$clusterPage) {
        if ($status > 0) { // eliminate any existing data
            $query = "DELETE FROM GPSDAT WHERE indxNo = :pubNo;";
            $pubdat = $pdo->prepare($query);
            $pubdat->bindValue(":pubNo", $indxNo);
            $pubdat->execute();
        }
        // insert new data whether old or new hike
        $insquery
            = "INSERT INTO GPSDAT (indxNo,datType,label,url,clickText) 
            SELECT :indxNo,datType,label,url,clickText
            FROM EGPSDAT WHERE indxNo = :ehikeNo;";
        $insgps = $pdo->prepare($insquery);
        $insgps->bindValue(":indxNo", $indxNo); // the indxNo of the new/updated hike
        $insgps->bindValue(":ehikeNo", $hikeNo); // the EHIKES indxNo
        $insgps->execute();
        // ---------------------  TSV -------------------
        if ($status > 0) { // eliminate any existing data
            $query = "DELETE FROM TSV WHERE indxNo = :indxNo;";
            $deltsv = $pdo->prepare($query);
            $deltsv->bindValue(":indxNo", $indxNo);
            $deltsv->execute();
        }
        // insert new data whether old or new hike
        $query
            = "INSERT INTO TSV 
            (indxNo,folder,title,hpg,mpg,`desc`,lat,lng,
            thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) 
            SELECT
            :indxNo,folder,title,hpg,mpg,`desc`,lat,lng,
            thumb,alblnk,date,mid,imgHt,imgWd,iclr,org
            FROM ETSV WHERE indxNo = :ehikeNo;";
        $instsv = $pdo->prepare($query);
        $instsv->bindValue(":indxNo", $indxNo); // the indxNo of the new/updated hike
        $instsv->bindValue(":ehikeNo", $hikeNo); // the EHIKES indxNo
        $instsv->execute();
    }
    // Cluster pages also receive REFS updates
    // ---------------------  REFS -------------------
    if ($status > 0) { // eliminate any existing data
        $dquery = "DELETE FROM REFS WHERE indxNo = :indxNo;";
        $delref = $pdo->prepare($dquery);
        $delref->bindValue(":indxNo", $indxNo);
        $delref->execute();
    }
    // insert new data whether old or new hike
    $query
        = "INSERT INTO REFS (indxNo,rtype,rit1,rit2) 
        SELECT :indxNo,rtype,rit1,rit2
        FROM EREFS WHERE indxNo = :ehikeNo;";
    $insref = $pdo->prepare($query);
    $insref->bindValue(":indxNo", $indxNo); // the indxNo of the new/updated hike
    $insref->bindValue(":ehikeNo", $hikeNo); // the EHIKES indxNo
    $insref->execute();

    /**
     * If a 'cname' field was populated in EHIKES, then one or more CLUSHIKE
     * entries also exist for this page. 
     * If corresponding 'group' in CLUSTERS has pub=N, then the group needs to be
     * published along with the group hike page.
     * NOTE: 'cname' will not be populated for cluster pgs or 'normal' hikes
     * ALSO: pub=N => indxNo is EHIKES indxNo; pub=Y => is HIKES indxNo
     * ALSO: data has already been validated above for these cases.
     */
    if (!empty($cname)) {
        $clushike_req = "UPDATE `CLUSHIKES` SET `pub`='Y', `indxNo`=? WHERE  " .
             "`indxNo`=? AND `pub`='N';";
        $clushike = $pdo->prepare($clushike_req);
        $clushike->execute([$indxNo, $hikeNo]);
        if ($clusdat['pub'] == 'N') {
            $updateClusReq = "UPDATE `CLUSTERS` SET `pub`='Y' WHERE `clusid`=?;";
            $updateClus = $pdo->prepare($updateClusReq);
            $updateClus->execute([$clusdat['clusid']]);
        } 
    }

    /**
     * If this hike was for a NEW cluster page, update the CLUSTERS group to point
     * to the new indxNo in HIKES; Already published cluster pages require no change.
     */
    if ($clusterPage && $status == 0) {
        $newPageReq = "UPDATE `CLUSTERS` SET `page`=?,`pub`='Y' WHERE `group`=?;";
        $newPage = $pdo->prepare($newPageReq);
        $newPage->execute([$indxNo, $clusPgField]);
    }

    /**
     * Regardless of state, remove this hike from EHIKES et al:
     * Foreign Keys ensures deletion in remaining E-tables (EGPSDAT, etc.)
     */
    $query = "DELETE FROM `EHIKES` WHERE `indxNo` = :ehikeNo;";
    $dele = $pdo->prepare($query);
    $dele->bindValue(":ehikeNo", $hikeNo);
    $dele->execute();
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Release to Main Site</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        .brown { color: brown }
    </style>
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>
<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Release EHIKE No. <?=$hikeNo;?></p>
<p id="page_id" style="display:none">Admin</p>

<div style="margin-left:16px;font-size:22px">
    <?php if ($msgout !== '') : ?>
        <?=$msgout;?>
    <?php else : ?>
        <p>E-Hike <?=$hikeNo;?> Has Been Released to the Main Site and 
            may now be viewed from the main page</p>
        <p>Hike has been removed from the list of New/In-Edit Hikes</p>
    <?php endif; ?>
</div>
<script src="../scripts/menus.js"></script>

</body>
</html>
