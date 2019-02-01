<?php
/**
 * This script will  'publish' a hike in-edit by transferring
 * the data from EHIKES and ETables to the HIKES and Hike Tables.
 * The hike will no longer appear in the 'in-edit' list.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$hikeNo = filter_input(INPUT_GET, 'hno');
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Release to Main Site</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Release EHIKE No. <?php echo $hikeNo;?></p>
<div style="margin-left:16px;font-size:22px">
    <?php
    $last = "SELECT * FROM HIKES ORDER BY 1 DESC LIMIT 1;";
    try {
        $lasthike = $pdo->query($last);
    }
    catch (PDOException $e) {
        pdoErr("SELECT ... ORDER BY 1", $e);
    }
    $item = $lasthike->fetch(PDO::FETCH_BOTH);
    $lastHikeNo = $item[0];
    // In preparation for the case where the hike is 'At VC', get all fields
    $query = "SELECT * FROM EHIKES WHERE indxNo = :hikeNo;";
    $ehk = $pdo->prepare($query);
    $ehk->bindValue(":hikeNo", $hikeNo);
    try {
        $ehk->execute();
    }
    catch (PDOException $e) {
        pdoErr("SELECT * FROM EHIKES", $e);
    }
    $ehike = $ehk->fetch(PDO::FETCH_ASSOC);
    if ($ehike === null) {
        die(
            "<p style=color:brown>Hike {$hikeNo} has no data!</p>" . 
            "  File: " . __FILE__ . "  Line:" . __LINE__
        );
    }
    // 'At VC' data:
    $tdnme = $ehike['pgTitle'];
    $tdcol = $ehike['collection'];
    $tdmis = $ehike['miles'];
    $tdft = $ehike['feet'];
    $tdx = $ehike['expo'];
    $tdalb = $ehike['purl1'];
    // Proceed with copying record from EHIKES to HIKES
    $status = intval($ehike['stat']);
    if ($status > $lastHikeNo || $status < 0) {
        die(
            "publish.php: Status out-of-range: {$status}" . 
            "  File: " . __FILE__ . "  Line:" . __LINE__
        );
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
    try {
        $updte->execute();
    }
    catch (PDOException $e) {
        pdoErr($cmd, $e);
    }
    // Assign the hike number for the remaining tables based on status:
    if ($status === 0) { // this will be the newly added no.
        $indxNo = $lastHikeNo + 1;
        /* NOTE: If this newly submitted hike (not previously published) is
         * a hike that is of type 'At VC', then the index page table for that
         * Visitor Center needs to be updated with the newly added hike:
         * This is done in conjunctions with the update of IPTBLS.
         */
        if (trim($ehike['marker']) === 'At VC') {
            $updtReq = "INSERT INTO IPTBLS (indxNo,compl,tdname,tdpg," .
                "tdmiles,tdft,tdexp,tdalb) VALUES (:refVC,'Y'," .
                ":tdname,:tdpg,:tdmiles,:tdft,:tdexp,:tdalb);";
            $iptbl = $pdo->prepare($updtReq);
            $iptbl->bindValue(":refVC", $tdcol);
            $iptbl->bindValue(":tdname", $tdnme);
            $iptbl->bindValue(":tdpg", $indxNo);
            $iptbl->bindValue(":tdmiles", $tdmis);
            $iptbl->bindValue(":tdft", $tdft);
            $iptbl->bindValue(":tdexp", $tdx);
            $iptbl->bindValue(":tdalb", $tdalb);
            try {
                $iptbl->execute();
            }
            catch (PDOException $e) {
                pdoErr("INSERT INTO IPTBLS", $e);
            }
            // Now update the Index Page's 'collection' field (in HIKES)
            // ti add the new hike to it's current collection
            $getColReq = "SELECT `collection` FROM HIKES WHERE indxNo = :indxNo;";
            $indxPtr = $pdo->prepare($getColReq);
            $indxPtr->bindValue(":indxNo", $tdcol);
            try {
                $indxPtr->execute();
            }
            catch (PDOException $e) {
                pdoErr("SELECT `collection` FROM HIKES", $e);
            }
            $ipg = $indxPtr->fetch(PDO::FETCH_BOTH);
            $oldCol = fetch($ipg[0]);
            if ($oldCol == '') {
                $newCol = $indxNo;
            } else {
                $newCol = $oldCol . "." . $indxNo;
            }
            $colReq = "UPDATE HIKES SET `collection` = :coll WHERE " .
                "indxNo = :indxNo";
            $updtcol = $pdo->prepare($colReq);
            $updtcol->bindValue(":coll", $newCol);
            $updtcol->bindValue(":indxNo", $tdcol);
            try {
                $updtcol->execute();
            }
            catch (PDOException $e) {
                pdoErr("UPDATE HIKES SET `collection`", $e);
            }
        }
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
        try {
            $pubdat->execute();
        }
        catch (PDOException $e) {
            pdoErr("DELETE FROM GPSDAT", $e);
        }
    }
    // insert new data whether old or new hike
    $insquery
        = "INSERT INTO GPSDAT (indxNo,datType,label,url,clickText) 
        SELECT :indxNo,datType,label,url,clickText
        FROM EGPSDAT WHERE indxNo = :ehikeNo;";
    $insgps = $pdo->prepare($insquery);
    $insgps->bindValue(":indxNo", $indxNo); // the indxNo of the new/updated hike
    $insgps->bindValue(":ehikeNo", $hikeNo); // the EHIKES indxNo
    try {
        $insgps->execute();
    }
    catch (PDOException $e) {
        pdoErr("INSERT INTO GPSDAT", $e);
    }
    // ---------------------  REFS -------------------
    if ($status > 0) { // eliminate any existing data
        $dquery = "DELETE FROM REFS WHERE indxNo = :indxNo;";
        $delref = $pdo->prepare($dquery);
        $delref->bindValue(":indxNo", $indxNo);
    }
    // insert new data whether old or new hike
    $query
        = "INSERT INTO REFS (indxNo,rtype,rit1,rit2) 
        SELECT :indxNo,rtype,rit1,rit2
        FROM EREFS WHERE indxNo = :ehikeNo;";
    $insref = $pdo->prepare($query);
    $insref->bindValue(":indxNo", $indxNo); // the indxNo of the new/updated hike
    $insref->bindValue(":ehikeNo", $hikeNo); // the EHIKES indxNo
    try {
        $insref->execute();
    }
    catch (PDOException $e) {
        pdoErr("INSERT INTO REFS", $e);
    }
    // ---------------------  TSV -------------------
    if ($status > 0) { // eliminate any existing data
        $query = "DELETE FROM TSV WHERE indxNo = :indxNo;";
        $deltsv = $pdo->prepare($query);
        $deltsv->bindValue(":indxNo", $indxNo);
        try {
            $deltsv->execute();
        }
        catch (PDOException $e) {
            pdoErr("DELETE FROM TSV", $e);
        }
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
    try {
        $instsv->execute();
    }
    catch (PDOException $e) {
        pdoErr("INSERT INTO TSV", $e);
    }
    /* Regardless of state, remove this hike from EHIKES et al:
     * Foreign Keys ensures deletion in remaining E-tables
     */
    $query = "DELETE FROM EHIKES WHERE indxNo = :ehikeNo;";
    $dele = $pdo->prepare($query);
    $dele->bindValue(":ehikeNo", $hikeNo);
    try {
        $dele->execute();
    }
    catch (PDOException $e) {
        pdoErr("DELETE FROM EHIKES", $e);
    }
    echo "<p>Hike has been removed from the list of New/In-Edit Hikes</p>";
    ?>
    <p>E-Hike <?= $hikeNo;?> Has Been Released to the Main Site and 
        may now be viewed from the main page</p>
</div>
</body>
</html>
