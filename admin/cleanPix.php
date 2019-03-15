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

$published_query = "SELECT title,thumb,mid FROM TSV;";
$in_edit_query   = "SELECT title,thumb,mid FROM ETSV;";
$published_pix   = $pdo->query($published_query);
$in_edit_pix     = $pdo->query($in_edit_query);

// arrays for deletion candidates
$base_names     = [];  // used to look for filenames w/differing thumb values
$nsize_in_table = [];
$zsize_in_table = [];

foreach ($published_pix as $photo) {
    array_push($base_names, $photo['mid']);
    array_push(
        $nsize_in_table, $photo['mid'] . "_" . $photo['thumb'] . "_n.jpg"
    );
    array_push(
        $zsize_in_table, $photo['mid'] . "_" . $photo['thumb'] . "_z.jpg"
    );
}
foreach ($in_edit_pix as $photo) {
    array_push($base_names, $photo['mid']);
    array_push(
        $nsize_in_table, $photo['mid'] . "_" . $photo['thumb'] . "_n.jpg"
    );
    array_push(
        $zsize_in_table, $photo['mid'] . "_" . $photo['thumb'] . "_z.jpg"
    );
}

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

// validate file existence and capture no-shows in tables
$nsize_candidates = [];
$zsize_candidates = [];
$thumb_mismatch   = [];
$photo_array      = scandir('pictures/nsize');
array_shift($photo_array);  // eliminate '.'
array_shift($photo_array);  // eliminate '..'
if ($photo_array[0] == '.DS_Store') {  // MacOS 
    array_shift($photo_array);
}
foreach ($photo_array as $filename) {
    if (!in_array($filename, $nsize_in_table)) {
        array_push($nsize_candidates, $filename);
        // does this file base_name appear with a different thumb?
        foreach ($base_names as $mid) {
            if (strpos($filename, $mid) !== false) {
                // it must have a different thumb value...
                array_push($thumb_mismatch, $filename);
            }
        }
    }
}
$photo_array      = scandir('pictures/zsize');
array_shift($photo_array);  // eliminate '.'
array_shift($photo_array);  // eliminate '..'
if ($photo_array[0] == '.DS_Store') {  // MacOS 
    array_shift($photo_array);
}
foreach ($photo_array as $filename) {
    if (!in_array($filename, $zsize_in_table)) {
        array_push($zsize_candidates, $filename);
        // does this file base_name appear with a different thumb?
        foreach ($base_names as $mid) {
            if (strpos($filename, $mid) !== false) {
                array_push($thumb_mismatch, $filename);
            }
        }
    }
}
$i = 0; // index for checkboxes
// list them out and provide a means for deletion via html form
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Find Extraneous Pix</title>
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
<p id="trail">Photo Cleanup Utility</p>
</div>
<div id="main">
<form action="photoCleanup.php" method="POST" />
    <input id="all" type="checkbox" name="all" />
    <label for="all">Select All Photos (Or unselect all if selected)
        </label><br /><br />
    <button id="submit">Delete Selected Photos</button>
    <p id="head">The following items were found to have no matching entries
        in the database</p>
    <span class="types">N-size photos:</span><br />
    <ul>
<?php foreach($nsize_candidates as $nsize) : ?>
    <li><input id="chkbox<?= $i++;?>" type="checkbox" name="checkboxes[]" 
        value="<?= $nsize;?>" /><?= $nsize;?></li>
<?php endforeach; ?>
    </ul>
    <span class="types">Z-size photos:</span><br />
    <ul>
<?php foreach($zsize_candidates as $zsize) : ?>
    <li><input id="chkbox<?= $i++;?>" type="checkbox" name="checkboxes[]"
        value="<?= $zsize;?>" /><?= $zsize;?></li>
<?php endforeach; ?>
    </ul>
    <span class="types">Photos having a matching base name but different
        key value:</span><br />
    <ul>
<?php foreach($thumb_mismatch as $wrong_key) : ?>
    <li><input id="chkbox<?= $i++;?>" type="checkbox" name="checkboxes[]"
        value="<?= $wrong_key;?>" /><?= $wrong_key;?>"</li>
<?php endforeach; ?>
    </ul>
    <input type="hidden" name="total" value="<?= $i;?>" />
</form>
</div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="cleanPix.js"></script>

</body>
</html>
