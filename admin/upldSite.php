<?php
/**
 * This script uploads the selected file to the system's temp dir.
 * PHP Version 7.0
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../build/buildFunctions.php";
$usite = sys_get_temp_dir() . '/';
$upldFile = validateUpload('ufile', $usite);
$msg = $upldFile[1] . "<br />Directory location: " . $usite . "<br />";
// if no upload dir at project level, create one
if (file_exists('../upload') === false ) {
    mkdir('../upload', 0775);
}
// extract files to upload dir
$zfile = $usite . $upldFile[0];
$zip = new ZipArchive();
if ($zip->open($zfile) === true) {
    $zip->extractTo('../upload/');
    $zip->close();
    $msg .= "Successfully extracted files to upload directory";
} else {
    $msg .= "Failed to extract files";
}
unlink($usite . $upldFile[0]);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Upload File</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Upload File to Site</p>
<p style="margin-left:24px;"><?= $msg;?></p>
</body>
</html>
