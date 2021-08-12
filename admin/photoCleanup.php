<?php
/**
 * This script is called when the admin selects (submits) either:
 * "Delete Selected Photo" or "Create Shell to Delete". Any items
 * whose checkbox is checked will be removed.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$action = filter_input(INPUT_POST, 'submit');  // remove files or create shell?

$picdir = getPicturesDirectory();
$basedir = str_replace("zsize/", "", $picdir);
$shell = false; // unless proven otherwise...
$shell_file = $basedir . "cleanpix.sh";

/**
 * If a shell script is to be created instead of outright deletion of files:
 */
if (strpos($action, "Create") !== false) {
    $shell = true;
    $handle = fopen($shell_file, "w"); // this writes over any existing
    $bash_start = "#!/bin/bash\n" . "# Version 1.0\n";
    fwrite($handle, $bash_start);
    fclose($handle);
    $script = fopen($shell_file, "a"); // now append to this new file
} // otherwise $shell remains false

// 'p' and 't' prefixes indicate 'preview' and 'thumb' respectively
$checkboxes  = isset($_POST['checkboxes']) ? $_POST['checkboxes'] : null;
$pcheckboxes = isset($_POST['pcheckboxes']) ? $_POST['pcheckboxes'] : null;
$tcheckboxes = isset($_POST['tcheckboxes']) ? $_POST['tcheckboxes'] : null;
$deleted = [];
$failures = [];
$msg = '';
if (isset($checkboxes)) {
    // remove z-size photos
    foreach ($checkboxes as $deletion) {
        $file = $picdir . $deletion;
        if ($shell) {
            // append the remove command
            $cmd = "rm zsize/" . $deletion . "\n";
            fwrite($script, $cmd);
            array_push($deleted, $file);
        } else {
            if (file_exists($file)) {
                array_push($deleted, $file);
                unlink($file);
            } else {
                array_push($failures, $file);
            }
        }
    }
    if ($shell) {
        fclose($script);
        chmod($shell_file, 0755);
    }
}
if (isset($pcheckboxes)) {
    // remove preview images
    foreach ($pcheckboxes as $deletion) {
        $file = $basedir . 'previews/' . $deletion;
        if ($shell) {
            // append the remove command
            $cmd = "rm previews/" . $deletion . "\n";
            fwrite($script, $cmd);
            array_push($deleted, $file);
        } else {
            if (file_exists($file)) {
                array_push($deleted, $file);
                unlink($file);
            } else {
                array_push($failures, $file);
            }
        }
    }
    if ($shell) {
        fclose($script);
        chmod($shell_file, 0755);
    }
}
if (isset($tcheckboxes)) {
    // remove thumb images
    foreach ($tcheckboxes as $deletion) {
        $file = $basedir . 'thumbs/' . $deletion;
        if ($shell) {
            // append the remove command
            $cmd = "rm thumbs/" . $deletion . "\n";
            fwrite($script, $cmd);
            array_push($deleted, $file);
        } else {
            if (file_exists($file)) {
                array_push($deleted, $file);
                unlink($file);
            } else {
                array_push($failures, $file);
            }
        }
    }
    if ($shell) {
        fclose($script);
        chmod($shell_file, 0755);
    }
}
if (!isset($checkboxes) && !isset($pcheckboxes) && !isset($zcheckboxes)) {
    $msg .= "No items were selected for deletion";
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Photos Deleted</title>
    <meta charset="utf-8" />
    <meta name="description" content="Check for extraneous photos" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <link href="cleanPix.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Photos Deleted</p>
<p id="active" style="display:none">Admin</p>

</div>
<div style="margin-left:24px;">
<?php if (strpos($msg, "No pictures")) : ?>
    <?= $msg;?>
<?php else : ?>
    <span style="color:brown;font-size:18px;">
        The following (<?= count($deleted);?>) photo(s) were 
        <?php if ($shell) : ?>
            entered into the executable shell script 'cleanpix.sh'
            in pictures/;</span>
        <?php else : ?>
            deleted from pictures/ (exceptions noted):</span>
        <?php endif; ?>
    <ul style="list-style-type:square;">
        <?php for ($j=0; $j<count($deleted); $j++) : ?>
            <li><?= $deleted[$j];?></li>
        <?php endfor; ?>
    </ul>
        
    <?php if (count($failures) > 0) : ?>
    <span style="color:brown;font-size=18px;">The following files were 
        unable to be deleted:
        <ul style="list-style-type:square">
        <?php for ($k=0; $k<count($failures); $k++) : ?>
            <li><?= $failures[$k];?></li>
        <?php endfor; ?>
        </ul>
    <?php endif; ?>
    <?php if (!empty($msg)) : ?>
    <br /><?= $msg;?>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>