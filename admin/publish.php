<?php
/**
 * This script will  'publish' an in-edit hike by transferring
 * the data from EHIKES and ETables to the HIKES and Hike Tables.
 * The hike will no longer appear in the 'in-edit' list. If the
 * EHIKE has a cluster assignment in 'cname', then update CLUSHIKES
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$hikeNo = filter_input(INPUT_GET, 'hno');
$msgout = '';

// next hike no if published as brand new hike
$last = "SELECT * FROM HIKES ORDER BY 1 DESC LIMIT 1;";
$lasthike = $pdo->query($last);
$item = $lasthike->fetch(PDO::FETCH_NUM);
$lastHikeNo = $item[0];

$query = "SELECT * FROM EHIKES WHERE indxNo = :hikeNo;";
$ehk = $pdo->prepare($query);
$ehk->bindValue(":hikeNo", $hikeNo);
$ehk->execute();
$ehike = $ehk->fetch(PDO::FETCH_ASSOC);
if ($ehike === null) {
    $msgout .= "<p style=color:brown>Hike {$hikeNo} has no data!<br />" . 
        "  File: " . __FILE__ . "  Line:" . __LINE__ . "/p>";
}

// Proceed with copying record from EHIKES to HIKES
$status = intval($ehike['stat']);
if ($status > $lastHikeNo || $status < 0) {
    $msgout .="<p>publish.php: Status out-of-range: {$status}" . 
        "  File: " . __FILE__ . "  Line:" . __LINE__ . "</p>";
}
// Get column names for buillding query strings
$result = $pdo->query("SHOW COLUMNS FROM EHIKES;");
$columns = $result->fetchAll(PDO::FETCH_BOTH);
if ($status > 0) { // this is an existing hike, UPDATE its record in HIKES
    $cmd = "UPDATE HIKES, EHIKES";
    $query = "
        UPDATE HIKES, EHIKES
        SET ";
    foreach ($columns as $column) {
        if (($column[0] !== "indxNo") && ($column[0] !== "stat")) {
            $query .= "HIKES.{$column[0]} = EHIKES.{$column[0]}, ";
        }
    }
    $query = rtrim($query, ", "); // remove final comma and space
    $query .= " WHERE HIKES.indxNo = :status AND EHIKES.indxNo = :hikeNo;";
    $updte = $pdo->prepare($query);
    $updte->bindValue(":status", $status);
    $updte->bindValue(":hikeNo", $hikeNo);
} else { // this is a new hike, INSERT its record into HIKES
    $cmd = "INSERT INTO HIKES";
    $query = "INSERT INTO HIKES (";
    $fields = '';
    foreach ($columns as $column) {
        if (($column[0] !== "indxNo") && ($column[0] !== "stat")) {
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
if ($status === 0) { // this will be the newly added no.
    $indxNo = $lastHikeNo + 1;
} else { // this will be the hike being modified, already on the site
    $indxNo = $status;
}
/*
    * In the cases of EGPSDAT, EREFS, and ETSV, elements may have been
    * deleted during edit, therefore, remove ALL the old data if the
    * hike was type 'pub'. Insert new data (no UPDATEs, only INSERTs)
    */
//  ---------------------  GPSDAT -------------------
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

/**
 * If a 'cname' field was populated in EHIKES, update CLUSHIKES
 */
if (!empty($ehike['cname'])) {
    $cluster_req = "SELECT `clusid`,`group` FROM `CLUSTERS`;";
    $clusters = $pdo->query($cluster_req)->fetchAll(PDO::FETCH_KEY_PAIR);
    if (($clusid = array_search($ehike['cname'], $clusters)) === false) {
        throw new Exception("Could not find {$ehike['cname']} in CLUSTERS");
    }
    // if previously published, update, else insert
    if ($status == 0) {
        $clushike_req = "INSERT INTO `CLUSHIKES` (`indxNo`,`cluster`) VALUES " .
            "(:indxNo, :cluster);";
        
    } else {
        $clushike_req = "UPDATE `CLUSHIKES` SET `cluster` = :cluster WHERE  " .
            "`indxNo` = :indxNo;";
    }
    $clushike = $pdo->prepare($clushike_req);
    $clushike->execute(["indxNo" => $indxNo, "cluster" => $clusid]); 
}

/* Regardless of state, remove this hike from EHIKES et al:
    * Foreign Keys ensures deletion in remaining E-tables
    */
$query = "DELETE FROM EHIKES WHERE indxNo = :ehikeNo;";
$dele = $pdo->prepare($query);
$dele->bindValue(":ehikeNo", $hikeNo);
    $dele->execute();
$msgout .= "<p>Hike has been removed from the list of New/In-Edit Hikes</p>";
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
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>
<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Release EHIKE No. <?php echo $hikeNo;?></p>
<p id="page_id" style="display:none">Admin</p>

<div style="margin-left:16px;font-size:22px">
    <?= $msgout;?>
    <p>E-Hike <?= $hikeNo;?> Has Been Released to the Main Site and 
        may now be viewed from the main page</p>
</div>
<script src="../scripts/menus.js"></script>

</body>
</html>
