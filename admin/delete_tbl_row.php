<?php
require 'setenv.php';
$tbl_type = filter_input(INPUT_GET,'tbl');
$rowno = filter_input(INPUT_GET,'indx');
if ($tbl_type === 'h') {
    $table = "HIKES";
} elseif ($tbl_type === 'e') {
    $table = "EHIKES";
} elseif ($tbl_type === 'u') {
    $table = "USERS";
} else {
    die("No Such Table Type: " . $tbl_type);
}
$lastid = "SELECT indxNo FROM " . $table . " ORDER BY indxNo DESC LIMIT 1";
$getid = mysqli_query($link,$lastid);
if (!$getid) {
    if (Ktesa_Dbug) {
        dbug_print('delete_tbl_row.php: Could not retrieve highest indxNo: ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,6,0);
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
    $remrow = mysqli_query($link,"DELETE FROM " . $table . " WHERE indxNo = " . $rowno . ";");
    if (!remrow) {
        $drop_fail = "<p>Could not delete the specified row: " . mysqli_error($link) . "</p>";
        die ($drop_fail);
    } else {
        $good =  "<p>Row " . $rowno . " successfully removed; </p>";
    }
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
    if($badrow) {
        echo $toobig;
    } else {
        echo $good;
    }
?>
</div>
</body>
</html>
