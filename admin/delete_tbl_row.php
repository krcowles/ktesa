<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$tbl_type = filter_input(INPUT_GET, 'tbl');
$rowno = filter_input(INPUT_GET, 'indx');
if ($tbl_type === 'u') {
    $table = "USERS";
    $idfield = "userid";
} elseif ($tbl_type === 'eh') {
    $table = "EHIKES";
    $idfield = "indxNo";
} elseif ($tbl_type === 'h') {
    $table = "HIKES";
    $idfield = "indxNo";
} elseif ($tbl_type === 'et') {
    $table = "ETSV";
    $idfield = "picIdx";
} elseif ($tbl_type === 't') {
    $table = "TSV";
    $idfield = "picIdx";
} elseif ($tbl_type === 'er') {
    $table = "EREFS";
    $idfield = "refId";
} elseif ($tbl_type === 'r') {
    $table = "REFS";
    $idfield = "refId";
} elseif ($tbl_type === 'eg') {
    $table = "EGPSDAT";
    $idfield = "datId";
} elseif ($tbl_type === 'g') {
    $table = "GPSDAT";
    $idfield = "datId";
} else {
    die("Unrecognized table type in GET");
}
$lastid = "SELECT {$idfield} FROM {$table} ORDER BY {$idfield} DESC LIMIT 1";
$getid = mysqli_query($link, $lastid);
if (!$getid) {
    if (Ktesa_Dbug) {
        dbug_print('delete_tbl_row.php: Could not retrieve highest index: ' .
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr, 6, 0);
    }
}
$lastindx = mysqli_fetch_row($getid);
$tblcnt = $lastindx[0];
mysqli_free_result($getid);
if ($rowno > $tblcnt) {
    $badrow = true;
    $toobig = '<p>The specified row is larger than last row of the table; Please ' .
        'return to admin tools and specify a valid row number';
} else {
    $badrow = false;
    $remrow = mysqli_query($link, "DELETE FROM {$table} WHERE {$idfield} = " . $rowno . ";");
    if (!$remrow) {
        $drop_fail = "<p>Could not delete the specified row: " . mysqli_error($link) . "</p>";
        die($drop_fail);
    } else {
        $good =  "<p>Row " . $rowno . " successfully removed; </p>";
    }
    mysqli_free_result($remrow);
    mysqli_close($link);
}
?>
 <!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Delete a Row</title>
    <meta charset="utf-8" />
    <meta name="description" content="Drop the HIKES Table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body {background-color: #eaeaea;}
    </style>
</head>

<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Delete Row From HIKES Table</p>
<div style="margin-left:16px;font-size:18px;"> 
<?php
if ($badrow) {
    echo $toobig;
} else {
    echo $good;
}
?>
</div>
</body>
</html>
