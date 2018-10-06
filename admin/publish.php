<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
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
    $lastHikeNo = getDbRowNum($link, 'HIKES', __FILE__, __LINE__);
    $query = "SELECT stat FROM EHIKES WHERE indxNo = {$hikeNo};";
    $result = mysqli_query($link, $query);
    if (!$result) {
        die("EHIKE could not be retrieved: " .
            mysqli_error($link) .
            "Query: {$query}" . 
            "  File: " . __FILE__ . "  Line:" . __LINE__
            );
    }
    if (mysqli_num_rows($result) === 0) {
        die("<p style=color:brown>Hike {$hikeNo} has no data!</p>" . 
            "  File: " . __FILE__ . "  Line:" . __LINE__
            );
    }
    
    // Proceed with copying record from EHIKES to HIKES
    $hike = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    $status = intval($hike['stat']);
    if ($status > $lastHikeNo || $status < 0) {
        die("publish.php: Status out-of-range: {$status}" . 
        "  File: " . __FILE__ . "  Line:" . __LINE__
        );
    }
    // Get column names for buillding query strings
    $query = "SHOW COLUMNS FROM EHIKES;";
    $result = mysqli_query($link, $query);
    if (!$result) {
        die(
            "publish.php: EHIKE columns could not be retrieved: " .
            mysqli_error($link) .
            "Query: {$query}" . 
            "  File: " . __FILE__ . "  Line:" . __LINE__
        );
    }
    if (mysqli_num_rows($result) === 0) {
        die("<p style=color:brown>SHOW COLUMNS has no data!</p>" . 
            "  File: " . __FILE__ . "  Line:" . __LINE__
        );
    }
    if ($status > 0) { // this is an existing hike, UPDATE its record in HIKES
        $query = "
            UPDATE HIKES, EHIKES
            SET ";
        while($column = mysqli_fetch_row($result)) {
            if (($column[0] !== "indxNo") &&
                ($column[0] !== "stat")) {
                $query .= "HIKES.{$column[0]} = EHIKES.{$column[0]}, ";
            }
        }
        $query = rtrim($query,", "); // remove final comma and space
        $query .= " WHERE HIKES.indxNo = {$status} AND EHIKES.indxNo = {$hikeNo};";
    }
    else { // this is a new hike, INSERT its record into HIKES
        $query = "INSERT INTO HIKES (";
        while($column = mysqli_fetch_row($result)) {
            if (($column[0] !== "indxNo") &&
                ($column[0] !== "stat")) {
                $query .= "{$column[0]}, ";
            }
        }
        $query = rtrim($query,", "); // remove final comma and space
        $query .= ") SELECT ";
        
        if (!mysqli_data_seek($result, 0)) {
            die("Failed seek </br>" . 
            "  File: " . __FILE__ . "  Line:" . __LINE__
            );
        }
        while($column = mysqli_fetch_row($result)) {
            if (($column[0] !== "indxNo") &&
                ($column[0] !== "stat")) {
                $query .= "{$column[0]}, ";
            }
        }
        $query = rtrim($query,", "); // remove final comma and space
        $query .= " FROM EHIKES WHERE indxNo = {$hikeNo};";
    }

    doQuery($link, $query, __FILE__, __LINE__);
    
    # Assign the hike number for the remaining tables based on status:
    if ($status === 0) { # this will be the newly added no.
        $indxNo = $lastHikeNo + 1;
        /* NOTE: If this newly submitted hike (not previously published) is
         * a hike that is of type 'At VC', then the index page table for that
         * Visitor Center needs to be updated with the newly added hike:
         * This is done via the IPTBLS table.
         */
        if (trim($hike['marker']) === 'At VC') {
            $updtReq = "INSERT INTO IPTBLS (indxNo,compl,tdname,tdpg," .
                "tdmiles,tdft,tdexp,tdalb) VALUES ('{$co}','Y'," .
                "'{$pg}','{$indxNo}','{$mi}','{$ft}','{$ex}','{$p1}');";
            $updt = mysqli_query($link, $updtReq);
            if (!$updt) {
                die("publish.php: Failed to insert new table entry for " .
                    "index page {$co}: " . mysqli_error($link));
            }
            # Also need to update the Index Page's collection field to
            # indicate the new hike (used by javascript for infoWindow)
            $getColReq = "SELECT collection FROM HIKES WHERE indxNo = {$co};";
            $getCol = mysqli_query($link, $getColReq);
            if (!$getCol) {
                die("publish.php: Failed to get 'collection' from Index " .
                    "Page {$co}: " . mysqli_error($link));
            }
            $prev = mysqli_fetch_row($getCol);
            $oldCol = $prev[0];
            mysqli_free_result($getCol);
            $newCol = $oldCol . "." . $indxNo;
            $colReq = "UPDATE HIKES SET collection = '{$newCol}' WHERE " .
                "indxNo = {$co};";
            $col = mysqli_query($link, $colReq);
            if (!$col) {
                die("publish.php: Failed to update the collection field for " .
                    "Index Page {$co}: " . mysqli_error($link));
            }
        }
    } else { # this will be the hike being modified, already on the site
        $indxNo = $status;
    }
    /*
     * In the cases of EGPSDAT, EREFS, and ETSV, elements may have been
     * deleted during edit, therefore, remove ALL the old data if the
     * hike was type 'pub'. Insert new data (no UPDATEs, only INSERTs)
     */

    # ---------------------  GPSDAT -------------------
    if ($status > 0) { # eliminate any existing data
        $query = "DELETE FROM GPSDAT WHERE indxNo = '{$status}';";
        doQuery($link, $query, __FILE__, __LINE__);
    }
    $query =
        "INSERT INTO GPSDAT
        (indxNo,datType,label,url,clickText) 
        SELECT
        {$indxNo},datType,label,url,clickText
        FROM EGPSDAT WHERE indxNo = {$hikeNo};";
    doQuery($link, $query, __FILE__, __LINE__);
    
    # ---------------------  REFS -------------------
    if ($status > 0) {
        $query = "DELETE FROM REFS WHERE indxNo = '{$status}';";
        doQuery($link, $query, __FILE__, __LINE__);
    }
    $query =
        "INSERT INTO REFS
        (indxNo,rtype,rit1,rit2) 
        SELECT
        {$indxNo},rtype,rit1,rit2
        FROM EREFS WHERE indxNo = {$hikeNo};";
    doQuery($link, $query, __FILE__, __LINE__);

    # ---------------------  TSV -------------------
    if ($status > 0) {
        $query = "DELETE FROM TSV WHERE indxNo = '{$status}';";
        doQuery($link, $query, __FILE__, __LINE__);
    }
    $query =
        "INSERT INTO TSV
        (indxNo,folder,title,hpg,mpg,`desc`,lat,lng,
        thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) 
        SELECT
        {$indxNo},folder,title,hpg,mpg,`desc`,lat,lng,
        thumb,alblnk,date,mid,imgHt,imgWd,iclr,org
        FROM ETSV WHERE indxNo = {$hikeNo};";
    doQuery($link, $query, __FILE__, __LINE__);

    /* Regardless of state, remove this hike from EHIKES et al:
     * Foreign Keys ensures deletion in remaining E-tables
     */
    $query = "DELETE FROM EHIKES WHERE indxNo = {$hikeNo};";
    doQuery($link, $query, __FILE__, __LINE__);
    echo "<p>Hike has been removed from the list of New/In-Edit Hikes</p>";
    ?>
    <p>E-Hike <?php echo $hikeNo;?> Has Been Released to the Main Site and 
        may now be viewed from the main page</p>
</div>
</body>
</html>
