<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb($file, $line);
$rowno = filter_input(INPUT_GET, 'drow');
$lastid = "SELECT indxNo FROM EHIKES ORDER BY indxNo DESC LIMIT 1";
$getid = mysqli_query($link, $lastid);
if (!$getid) {
    if (Ktesa_Dbug) {
        dbug_print('delete_EHIKE.php: Could not retrieve highest indxNo: ' .
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr, 6, 0);
    }
}
if (mysqli_num_rows($getid) === 0) {
    die("There are no hikes currently listed in EHIKES");
}
$lastindx = mysqli_fetch_row($getid);
$hikeCnt = $lastindx[0];
mysqli_free_result($getid);
if ($rowno > $hikeCnt) {
    $badrow = true;
    $toobig = '<p>The specified hike row is larger than the number of hikes ' .
        "currently found in EHIKES; Please return to admin tools and " .
        "specify a valid hike number";
} else {
    $badrow = false;
    /*
     * DELETE HIKE FROM EHIKES....
     */
    $remehq = "DELETE FROM EHIKES WHERE indxNo = {$rowno};";
    $remeh = mysqli_query($link, $remehq);
    if (!$remeh) {
        die("delete_EHIKE.php: Failed to delete EHIKES for hike {$rowno}: " .
            mysqli_error($link));
    }
    mysqli_free_result($remeh);
    $good = "<p>Deleted hike {$rowno} from EHIKES</p>";
    /*
     * DELETE REFS FROM EREFS....
     */
    $remerq = "DELETE FROM EREFS WHERE indxNo = '{$rowno}';";
    $remer = mysqli_query($link, $remerq);
    if (!$remer) {
        die("delete_EHIKE.php: Failed to remove EREFS for hike {$rowno}: " .
            mysqli_error($link));
    }
    mysqli_free_result($remer);
    $good .= "<p>Deleted refs for hike {$rowno} from EREFS</p>";
    /*
     * DELETE FROM EGPSDAT...
     */
    $remegq = "DELETE FROM EGPSDAT WHERE indxNo = '{$rowno}';";
    $remeg = mysqli_query($link, $remegq);
    if (!$remeg) {
        die("delete_EHIKE.php: Failed to remove EGPSDAT for hike {$rowno}: " .
            mysqli_error($link));
    }
    mysqli_free_result($remeg);
    $good .= "<p>Deleted gps proposed and actual data for hike {$rowno} from EGPSDAT</p>";
    /*
     * DELETE FROM ETSV...
     */
    $remetq = "DELETE FROM ETSV WHERE indxNo = '{$rowno}';";
    $remet = mysqli_query($link, $remetq);
    if (!$remet) {
        die("delete_EHIKE.php: Failed to remove ETSV for hike {$rowno}: " .
            mysqli_error($link));
    }
    mysqli_free_result($remet);
    $good .= "<p>Deleted photo data for hike {$rowno} from ETSV</p>";
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
