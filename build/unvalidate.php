<?php
session_start();
require_once '../mysql/setenv.php';
/* Capture and remove any uploaded files and/or photos:
 * If the files had upload errors, were the wrong file type, or coudn't be 
 * moved from the server's tmp directory to the proper site directory, 
 * the flag state will still be 'false' and nothing will be undone in that case
 */
$undos = 0;
$errs = 0;
$err = '';
$hikeNo = $_SESSION['hno'];
$usr = $_SESSION['usr'];
$photos = '';
if ($_SESSION['tsv']) {
    $undos++;
    # photos need to be deleted from ETSV table for this hike
    $delpixreq = "DELETE FROM ETSV WHERE indxNo = {$hikeNo};";
    $delpix = mysqli_query($link,$delpixreq);
    if (!$delpix) {
        die("unvalidate.php: Failed to delete photo data from ETSV: " . 
            mysqli_error($link));
    }
    $photos = '<p>Photo data has been removed</p>';
    mysqli_free_result($delpix);
}
if ($_SESSION['havgpx']) {
    $undos++;  
    if (unlink($_SESSION['gpx']) === false) {
        $err .= "<p>Could not remove {$_SESSION['gpx']}</p>";
        $errs++;
    }
    if (unlink($_SESSION['trk']) === false) {
        $err .= "<p>Could not remove {$_SESSION['trk']}</p>";
        $errs++;
    }
}
if ($_SESSION['if1']) {
    $undos++; 
    if (unlink($_SESSION['i1loc']) === false) {
        $err .= "<p>Could not remove {$_SESSION['i1loc']}</p>";
        $errs++;
    }
}
if ($_SESSION['if2']) {
    $undos++;
    if (unlink($_SESSION['i2loc']) === false) {
        $err .= "<p>Could not remove {$_SESSION['i2loc']}</p>";
        $errs++;
    }
}
if ($_SESSION['pf1']) {
    $undos++;
    if (unlink($_SESSION['p1loc']) === false) {
        $err .= "<p>Could not remove Proposed Data File 1</p>";
        $errs++;
    }
}
if ($_SESSION['pf2']) {
    $undos++;
    if (unlink($_SESSION['p2loc']) === false) {
        $err .= "<p>Could not remove Proposed Data File 2</p>";
        $errs++;
    }
}
if ($_SESSION['af1']) {
    $undos++;
    if (unlink($_SESSION['a1loc']) === false) {
        $err .= "<p>Could not remove Actual Data File 1</p>";
        $errs++;
    }
}
if ($_SESSION['af2']) {
    $undos++;
    if (unlink($_SESSION['a2loc']) === false) {
        $err .= "<p>Could not remove Actual Data File 2</p>";
        $errs++;
    }
}
if ($undos > 0 && $errs === 0) {
    $updtreq = "UPDATE EHIKES SET stat = 'new' WHERE indxNo = {$hikeNo};";
    $updt = mysqli_query($link,$updtreq);
    if(!$updt) {
        die("unvalidate.php: Could not update stat field in EHIKES: " . 
                mysqli_error($link));
    }
    mysqli_free_result($updt);
    header("Location: enterHike.php?hno={$hikeNo}&usr={$usr}");
} else {
    if ($errs !== 0) {
        $msg = "<p>The attempt to unvalidate was unsuccessful. Please resolve the"
        . " issues prior to proceeding: Contact Site Master (Upload Status"
        . "Not Changed)</p>" . $err;
    } else {
        $msg =  "<p>No files were uploaded: You may go back and"
        . "Validate again</p>";
    }
}
if ($photos !== '') {
    $msg = $photos . $msg;
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Un-Validate</title>
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="validateHike.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Undo Validation</p>
<p style="margin-left:16px;font-size:18px;"><?php echo $msg;?></p>
</body>
</html>