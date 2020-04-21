<?php
/**
 * This script uploads the selected file to the system's temp dir.
 * PHP Version 7.0
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$msg = '';
if ($_FILES['ufile']['name'] == '') {
    $msg .= "No file was selected for upload";
} else {
    $usite = sys_get_temp_dir() . '/';
    $upldFile = validateUpload('ufile', $usite);
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
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Upload File</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Upload File to Site</p>
<p id="page_id" style="display:none">Admin</p>

<p style="margin-left:24px;"><?= $msg;?></p>
<script src="../scripts/menus.js"></script>

</body>
</html>
