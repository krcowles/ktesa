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
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$ehikeNo      = filter_input(INPUT_GET, 'hno'); // The EHIKES indxNo
$clusterPage = isset($_GET['clus']) && $_GET['clus'] === 'y' ? true : false;
$msgout      = '';

$query = "SELECT * FROM `EHIKES` WHERE `indxNo` = :hikeNo;";
$ehk = $pdo->prepare($query);
$ehk->execute(["hikeNo" => $ehikeNo]);
$ehike = $ehk->fetch(PDO::FETCH_ASSOC);
if ($ehike === false) {
    $msgout .= "<p class='brown'>Hike {$ehikeNo} has no data!</p>";
}
$clusPgField = $ehike['pgTitle']; // used if publishing a new cluster page
$cname = $ehike['cname'];
// $pubNo will be used for the to-be-published HIKE 'indxNo'
$pubNo = 0; // over-ridden when published 'indxNo' is known

/**
 * Validate key data: some data must not be 'empty' in order to prevent
 * viewing problems; see comments below
 */
if ($clusterPage) {
    // Data omission here prevents displaying cluster on main map (mapJsData.php)
    $cpClustersReq = "SELECT `lat`,`lng` FROM `CLUSTERS` WHERE `group`=?;";
    $clusterData = $pdo->prepare($cpClustersReq);
    $clusterData->execute([$clusPgField]);
    $cdat = $clusterData->fetch(PDO::FETCH_ASSOC);
    if (is_null($cdat['lat']) || is_null($cdat['lng'])) {
        $msgout 
            .= "<p class='brown'>Missing lat or lng for Cluster {$clusPgField}</p>";
    }
} else {
    // Data omission here will cause issues in mapJsData.php on home page,
    // or other problems (including execution errors)
    if (!empty($cname)) {
        // for a group hike page, validate CLUSTERS data (whether or not published)
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
    if (empty($ehike['last_hiked'])) {
        $msgout .= '<p class="brown">Missing last_hiked data</p>';
    }
    if (empty($ehike['preview'])) {
        $msgout .= '<p class="brown">Missing preview/thumb data</p>';
    }
}
if (empty($ehike['dirs'])) {
    $msgout .= '<p class="brown">Missing directions link</p>';
}
$status = intval($ehike['stat']);

/**
 * Continue ONLY IF NO ERRORS...
 * NOTE: The simplified process for copying data from EHIKES to HIKES excludes
 * the 'gpxlist' field, which needs to be updated -after- the transferred META/GPX
 * data has been completed, as the old 'gpxlist' includes filenos from the edit
 * database and will be incorrect for the published database.
 */
if ($msgout == '') {  
    /**
     * Get column names for building query strings:
     * Don't load 'indxNo' (sequentially assigned), 'stat' (there is no stat in
     * HIKES), 'cname' (there is no cname in HIKES), or 'gpxlist' (don't know
     * filenos yet)
     */
    $columns = $pdo->query("SHOW COLUMNS FROM `EHIKES`;")->fetchAll(PDO::FETCH_BOTH);
    if ($status > 0) { // this is a published hike, UPDATE its record in HIKES
        $query = "UPDATE `HIKES`, `EHIKES` SET ";
        foreach ($columns as $column) {
            if ($column[0] !== "indxNo" && $column[0] !== "stat"
                && $column[0] !== "cname" && $column[0] !== "gpxlist"
            ) {
                $query .= "HIKES.{$column[0]} = EHIKES.{$column[0]}, ";
            }
        }
        $query = rtrim($query, ", "); // remove final comma and space
        $query .= " WHERE HIKES.indxNo = :status AND EHIKES.indxNo = :hikeNo;";
        $updte = $pdo->prepare($query);
        $updte->bindValue(":status", $status);
        $updte->bindValue(":hikeNo", $ehikeNo);
    } else { // this is a new hike, INSERT its record into HIKES
        $query = "INSERT INTO HIKES (";
        $fields = '';
        foreach ($columns as $column) {
            if ($column[0] !== "indxNo" && $column[0] !== "stat"
                && $column[0] !== "cname" && $column[0] !== "gpxlist"
            ) {
                $fields .= "{$column[0]}, ";
            }
        }
        $fields = rtrim($fields, ", "); // remove final comma and space
        $query .= $fields . ") SELECT " . $fields .
            " FROM EHIKES WHERE indxNo = :hikeNo;";
        $updte = $pdo->prepare($query);
        $updte->bindValue(":hikeNo", $ehikeNo);
    }
    $updte->execute();
    if ($status === 0) {
        // Retrieve just-inserted 'indxNo' & assign as $pubNo
        $getIndxNoReq = "SELECT `indxNo` FROM `HIKES` ORDER BY `indxNo` DESC " .
            "LIMIT 1;";
        $getIndxNo = $pdo->query($getIndxNoReq)->fetch(PDO::FETCH_NUM);
        $pubNo = $getIndxNo[0];
    } else {
        $pubNo = $status; // a previously published hike 'indxNo'
    }
    $pubPage = "../pages/hikePageTemplate.php?hikeIndx=" . $pubNo;
    if (!$clusterPage) {  
        /**
         * Transfer GPX data from EMETA/EGPX into META/GPX for all files associated
         * with the EHIKE's 'gpxlist' (this is always empty for Cluster Pages). The
         * filenos in META/GPX will form the new 'gpxlist' for the to-be-published 
         * hike [$pubNo]
         */
        $newlist = [];
        $gpxlist = $ehike['gpxlist'];
        $list = explode(",", $gpxlist); // array of sourcing filenos
        // get the next available fileno from META
        $getFileno = "SELECT `fileno` FROM `META` ORDER BY `fileno` DESC LIMIT 1;";
        $gpxfno = $gdb->query($getFileno)->fetch(PDO::FETCH_NUM);
        $targNo = $gpxfno[0] + 1; // target fileno
        foreach ($list as $efileno) {
            xfrGpxData('pub', $targNo, $efileno, $gdb);
            deleteGpxData('new', $gdb, $efileno);
            array_push($newlist, $targNo);
            $targNo++;
        }
        // The updated 'gpxlist' can now be placed into HIKES
        $pubgpxlist = implode(",", $newlist);
        $setlistReq = "UPDATE `HIKES` SET `gpxlist`=? WHERE `indxNo`=?;";
        $setlist = $pdo->prepare($setlistReq);
        $setlist->execute([$pubgpxlist, $pubNo]);
        /**
         * In the cases of EGPSDAT, EREFS, and ETSV, elements may have been
         * deleted during edit, therefore, remove ALL the old data if the
         * hike was type 'published'. In either case then insert new data 
         * (no UPDATEs, only INSERTs)
         */
        
        //  ---------------------  GPSDAT -------------------
        if ($status > 0) {  // ehike was previously published - delete GPSDAT
            // get the previously published filenos to also delete
            $odatReq = "SELECT `fileno` FROM `GPSDAT` WHERE `indxNo`=?;";
            $odat = $pdo->prepare($odatReq);
            $odat->execute([$pubNo]); // previously published indxNo
            $old_filenos = $odat->fetchAll(PDO::FETCH_ASSOC);
            // first, delete old GPSDAT data
            $query = "DELETE FROM `GPSDAT` WHERE `indxNo` = :pubNo;";
            $pubdat = $pdo->prepare($query);
            $pubdat->bindValue(":pubNo", $pubNo);
            $pubdat->execute();
            // then, eliminate associated META/GPX data
            foreach ($old_filenos as $ofile) {
                if (!empty($ofile)) {
                    deleteGpxData('pub', $gdb, $ofile);
                }
            }
        } 
        /**
         * Insert new GPS data - whether old or new hike;
         * If the GPS data is for a gpx file, add that data to META/GPX;
         * NOTE: fileno will be null if not a gpx file
         */
        $newgpxReq = "SELECT `datId`,`fileno` FROM `EGPSDAT` WHERE `indxNo`=?;";
        $newgpx = $pdo->prepare($newgpxReq);
        $newgpx->execute([$ehikeNo]);
        $xfrlist = $newgpx->fetchAll(PDO::FETCH_ASSOC);
        // get next available fileno in META
        $nxtGpxfno = "SELECT `fileno` FROM `META` ORDER BY `fileno` DESC LIMIT 1;";
        $nxtgpx = $gdb->query($nxtGpxfno)->fetch(PDO::FETCH_NUM);
        $pubgpxno = $nxtgpx[0] + 1;
        foreach ($xfrlist as $addgpx) {  // array of filenos from EGPSDAT
            if (!empty($addgpx['fileno'])) { // non-gpx items have a null fileno
                xfrGpxData('pub', $pubgpxno, $addgpx['fileno'], $gdb);
                deleteGpxData('new', $gdb, $addgpx['fileno']);
                $insquery = "INSERT INTO `GPSDAT` (`indxNo`,`fileno`,`label`," .
                    "`clickText`) SELECT ?,?,`label`,`clickText` FROM EGPSDAT " .
                    "WHERE `datId`=?;";
                $insgps = $pdo->prepare($insquery);
                $insgps->execute([$pubNo, $pubgpxno, $addgpx['datId']]);
                $pubgpxno++;
            } else {
                $insnullfno = "INSERT INTO `GPSDAT` (`indxno`,`label`,`clickText`) " .
                "SELECT ?,`label`,`clickText` FROM EGPSDAT WHERE `datId`=?";
                $insgpsdat = $pdo->prepare($insnullfno);
                $insgpsdat->execute([$pubNo, $addgpx['datId']]);
            }
        }
       
        // ------------------------  TSV ----------------------
        if ($status > 0) { // eliminate any existing data
            $query = "DELETE FROM TSV WHERE indxNo = :indxNo;";
            $deltsv = $pdo->prepare($query);
            $deltsv->bindValue(":indxNo", $pubNo);
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
        $instsv->bindValue(":indxNo", $pubNo); // the indxNo of the new/updated hike
        $instsv->bindValue(":ehikeNo", $ehikeNo); // the EHIKES indxNo
        $instsv->execute();
    }
    // Cluster pages also receive REFS updates
    // ---------------------  REFS -------------------
    if ($status > 0) { // eliminate any existing data
        $dquery = "DELETE FROM REFS WHERE indxNo = :indxNo;";
        $delref = $pdo->prepare($dquery);
        $delref->bindValue(":indxNo", $pubNo);
        $delref->execute();
    }
    // insert new data whether old or new hike
    $query
        = "INSERT INTO REFS (indxNo,rtype,rit1,rit2) 
        SELECT :indxNo,rtype,rit1,rit2
        FROM EREFS WHERE indxNo = :ehikeNo;";
    $insref = $pdo->prepare($query);
    $insref->bindValue(":indxNo", $pubNo); // the indxNo of the new/updated hike
    $insref->bindValue(":ehikeNo", $ehikeNo); // the EHIKES indxNo
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
        $clushike->execute([$pubNo, $ehikeNo]);
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
        $newPage->execute([$pubNo, $clusPgField]);
    }

    /**
     * Regardless of state, remove this hike from EHIKES et al:
     * Foreign Keys ensures deletion in remaining E-tables (EGPSDAT, etc.)
     */
    $query = "DELETE FROM `EHIKES` WHERE `indxNo` = :ehikeNo;";
    $dele = $pdo->prepare($query);
    $dele->bindValue(":ehikeNo", $ehikeNo);
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        .brown { color: brown }
    </style>
    <script src="../scripts/jquery.js"></script>
</head>
<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Release EHIKE No. <?=$ehikeNo;?></p>
<p id="active" style="display:none">Admin</p>


<div style="margin-left:16px;font-size:22px">
    <?php if ($msgout !== '') : ?>
        <?=$msgout;?>
    <?php else : ?>
        <p>E-Hike <?=$ehikeNo;?> Has Been Released to the Main Site and 
            may now be viewed from the main page as hike no <?=$pubNo;?>
            (<a href="<?=$pubPage;?>">New Hike</a>)</p>
        <p>Edited hike has been removed from the list of New/In-Edit Hikes</p>
    <?php endif; ?>
</div>

</body>
</html>
