<?php
/**
 * This script extracts the picture filenames from both TSV and ETSV
 * and then compares them to the files currently residing in 'pictures'
 * for both nsize and zsize photos. The subsequent html will list the 
 * candidates for deletion, and if the admin wishes to then remove 
 * those extraneous files, he may do so by checking the boxes provided,
 * and then selecting the 'Remove' button
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

// find level at which pictures directory resides
$current = getcwd();
$ups = 0;
while (!in_array('pictures', scandir($current))) {
    chdir('..');
    $current = getcwd();
    $ups++;
    if ($ups > 10) { 
        throw new Exception("Can't find pictures directory!");
    }
}
$checkboxes = isset($_POST['checkboxes']) ? $_POST['checkboxes'] : null;
$deleted = [];
$failures = [];
$msg = '';
if (isset($checkboxes)) {
    foreach ($checkboxes as $deletion) {
        if (strpos($deletion, "_n") !== false) {
            $file = 'pictures/nsize/' . $deletion;
        } else if (strpos($deletion) !== false) {
            $file = 'pictures/zsize/' . $deletion;
        } else {
            $msg .= "Unrecognized file type: " . $deletion . "<br />";
        }
        if (empty($msg)) {
            if (unlink($file)) {
                array_push($deleted, $file);
            } else {
                array_push($failures, $file);
            }
        }
    }
} else {
    $msg = "No Pictures were selected for deletion";
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
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="cleanPix.css" type="text/css" rel="stylesheet" />
<body>
<div id="logo"><img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
<p id="trail">Photos Deleted</p>
</div>
<div style="margin-left:24px;">
<?php if (strpos($msg, "No pictures" !== false)) : ?>
    <?= $msg;?>
<?php else : ?>
    <span style="color:brown;font-size:18px;">
        The following (<?= count($deleted);?>) photo(s) were deleted from 
        pictures (exceptions noted):</span>
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
<?php endif; ?>
</body>
</html>