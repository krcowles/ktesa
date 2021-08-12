<?php
/**
 * This script extracts the picture filenames from both TSV and ETSV
 * and then compares them to the files currently residing in 'pictures/'
 * for zsize photos, previews and thumbs. If files exist in the directories
 * that are not found in the tables, they are identified as candidates for
 * deletion (NOTE: for convenience, if the images match the base file name
 * but differ in 'thumb' field value, they are tagged). In addition, those
 * same tables are compared to the file list to see if there are images that
 * do not reside in the file system. The subsequent html will list the candidates
 * for deletion, and if the admin wishes to then remove those extraneous files,
 * he/she may do so by checking the boxes provided, and then selecting the 
 * 'Remove' button. Alternately, he/she may have a shell script created to
 * do the removal so that it can be reviewed prior to invoking. Any items that
 * appear to be missing must be supplied by the admin.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

// find level at which pictures directory resides
$zsize_loc = getPicturesDirectory();

/**
 * Find the items that SHOULD be in the file system
 */
// First, retrieve the list of 'expected' photos in the database
$published_query = "SELECT picIdx,thumb,mid FROM TSV;";
$in_edit_query   = "SELECT picIdx,thumb,mid FROM ETSV;";
$published_pix   = $pdo->query($published_query)->fetchAll(PDO::FETCH_ASSOC);
$in_edit_pix     = $pdo->query($in_edit_query)->fetchAll(PDO::FETCH_ASSOC);
$allpix          = array_merge($published_pix, $in_edit_pix);
// Now retrieve the list of all expected 'previews' and 'thumbs (same image name)
$ehikesPrevReq   = "SELECT `preview` FROM `EHIKES`;";
$hikesPrevReq    = "SELECT `preview` FROM `HIKES`;";
$ehikesPrev      = $pdo->query($ehikesPrevReq)->fetchAll(PDO::FETCH_COLUMN);
$hikesPrev       = $pdo->query($hikesPrevReq)->fetchAll(PDO::FETCH_COLUMN);
$allPrevVals     = array_merge($hikesPrev, $ehikesPrev);
// NOTE: cluster pages have no previews, and will return a null value, so:
$allPrevs        = array_filter($allPrevVals, 'strlen');

/**
 * By comparing the file system to the 'expected' files listed in the database
 * the items that are in the file system but are not listed in the database can
 * be identified for potential deletion ('_candidates'). Conversely, items that
 * should be in the file system as listed in the database but do not appear in
 * the files system ('_noshows') can also be identified for appropriate action.
 */
// photos
$base_names     = [];
$zsize_in_table = [];
$table_entry_id = [];
$zsize_candidates = [];
$thumb_mismatch   = [];
$zsize_noshows    = [];
// previews
$prev_candidates = [];
$prev_noshows = [];
// thumbs
$th_candidates = [];
$th_noshows = [];

/**
 * Fill the $base_names array with the 'mid' field lists in the [E]TSV tables
 * Create the actual photo name (.jpg) and save in array $zsize_in_table array.
 * The routine goes 'a step further' by separately identifying images which have
 * the same base name but a different 'thumb' field value.
 */
foreach ($allpix as $photo) {
    if (!empty($photo['mid'])) {  // no waypoints are checked
        array_push($base_names, $photo['mid']);
        $zid = $photo['mid'] . "_" . $photo['thumb'] . "_z.jpg";
        array_push($zsize_in_table, $zid);
        array_push($table_entry_id, "[E]TSV-" . $photo['picIdx']);
    }
}
// collect the zsize images actually appearing in the pictures/zsize directory
$photo_array = scandir($zsize_loc);
array_shift($photo_array);  // eliminate '.'
array_shift($photo_array);  // eliminate '..'
if ($photo_array[0] == '.DS_Store') {  // MacOS 
    array_shift($photo_array);
}
// are any table entries NOT represented in the zsize file list? ('_noshows'_)
for ($z=0; $z<count($zsize_in_table); $z++) {
    if (!in_array($zsize_in_table[$z], $photo_array)) {
        $report = "[" . $table_entry_id[$z] . "] " . $zsize_in_table[$z];
        array_push($zsize_noshows, $report);
    }
}
// conversely, are any files not found with no matching table entry?
foreach ($photo_array as $filename) {
    if (!in_array($filename, $zsize_in_table)) {
        $mismatch = false;
        // does this file base_name appear with a different thumb?
        foreach ($base_names as $mid) {
            if (strpos($filename, $mid) !== false) {
                $mismatch = true;
                array_push($thumb_mismatch, $filename);
            }
        }
        if (!$mismatch) {
            array_push($zsize_candidates, $filename);
        }
    }
}
$i = 0; // index for checkboxes
/**
 * Now check for the presence/absence of previews and thumbs
 */
$prev_loc = str_replace("zsize", "previews", $zsize_loc);
$prevPix    = scandir($prev_loc);
array_shift($prevPix);  // eliminate '.'
array_shift($prevPix);  // eliminate '..'
if ($prevPix[0] == '.DS_Store') {  // MacOS 
    array_shift($prevPix);
}
// any preview pix not assigned in the HIKES/EHIKES tables?
foreach ($prevPix as $pImage) {
    if (!in_array($pImage, $allPrevs)) {
        array_push($prev_candidates, $pImage);
    }
}
// does every table entry have a corresponding previews pic?
foreach ($allPrevs as $tableItem) {
    if (!in_array($tableItem, $prevPix)) {
        array_push($prev_noshows, $tableItem);
    }
}

$thumb_loc = str_replace("zsize", "thumbs", $zsize_loc);
$thPix = scandir($thumb_loc);
array_shift($thPix);  // eliminate '.'
array_shift($thPix);  // eliminate '..'
if ($thPix[0] == '.DS_Store') {  // MacOS 
    array_shift($thPix);
}
// any thumbs pix not assigned in the HIKES/EHIKES tables?
foreach ($thPix as $tImage) {
    if (!in_array($tImage, $allPrevs)) {
        array_push($th_candidates, $tImage);
    }
}
// does every table entry have a corresponding thumbs pic?
foreach ($allPrevs as $tableItem) {
    if (!in_array($tableItem, $thPix)) {
        array_push($th_noshows, $tableItem);
    }
}
// list the findings and provide a means for deletion via html 'form'
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Find Extraneous Pix</title>
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
<p id="trail">Photo Cleanup Utility</p>
<p id="active" style="display:none">Admin</p>

<div id="main">
<form id="form" action="photoCleanup.php" method="POST" />
    <input id="all" type="checkbox" name="all" />&nbsp;
    <label for="all">Select All Photos (Or unselect all if selected)
        </label><br /><br />
    <input class="btn btn-danger" type="submit" type="submit"
        name="submit" value="Delete Selected Photos" />&nbsp;&nbsp;OR&nbsp;&nbsp;
    <input class="btn btn-secondary" type="submit" name="submit" 
        value="Create Shell to Delete" />&nbsp;&nbsp;
        Script will reside in 'pictures' directory and should be invoked there
    <p class="head">The following directory items were found to have no matching
        entries in the database:</p>
    <span class="types">Z-size photos that exist that do not appear in the
        database:</span><br />
    <ul>
<?php foreach($zsize_candidates as $zsize) : ?>
    <li><input id="chkbox<?= $i++;?>" type="checkbox" name="checkboxes[]"
        value="<?= $zsize;?>" />&nbsp;&nbsp;<?= $zsize;?></li>
<?php endforeach; ?>
    </ul>
    <span class="types">Z-size photos having a matching base name only, but 'thumb'
        value is incorrect:</span><br />
    <ul>
<?php foreach($thumb_mismatch as $wrong_key) : ?>
    <li><input id="chkbox<?= $i++;?>" type="checkbox" name="checkboxes[]"
        value="<?= $wrong_key;?>" />&nbsp;&nbsp;<?= $wrong_key;?></li>
<?php endforeach; ?>
    </ul>
    <span class="types">Preview images that exist that do not appear in the
        database:</span><br />
    <ul>
<?php if (count($prev_candidates) > 0) : ?>
    <?php for ($k=0; $k<count($prev_candidates); $k++) : ?>
    <li><input id="chkboxp<?=$k;?>" type="checkbox" name="pcheckboxes[]"
        value="<?=$prev_candidates[$k];?>" />&nbsp;&nbsp;<?=$prev_candidates[$k];?>
    </li>
    <?php endfor; ?>
<?php endif; ?>
    </ul>
    <span class="types">Thumbs images that exist that do not appear in the 
        database:</span>
    <ul>
<?php if (count($th_candidates) > 0) : ?>
    <?php for ($n=0; $n<count($th_candidates); $n++) : ?>
        <li><input id="chkboxt<?=$n;?>" type="checkbox" name="tcheckboxes[]"
        value="<?=$th_candidates[$n];?>" />&nbsp;&nbsp;<?=$th_candidates[$n];?>
    </li>
    <?php endfor; ?>
<?php endif; ?>
    </ul>
</form>

<p class="head">The database lists the following z-size photos which 
    cannot be located:</p>
<?php if (count($zsize_noshows) > 0) : ?>
    <ul>
    <?php for ($j=0; $j<count($zsize_noshows); $j++) : ?>
    <li><?= $zsize_noshows[$j];?></li>
    <?php endfor; ?>
    </ul>
<?php endif; ?>
<p class="head">The database lists the following preview images which
    cannot be located:</p>
<?php if (count($prev_noshows) > 0) : ?>
    <ul>
    <?php for ($k=0; $k<count($prev_noshows); $k++) : ?>
    <li><?= $prev_noshows[$k];?></li>
    <?php endfor; ?>
    </ul>
<?php endif; ?>
<p class="head">The database lists the following thumbs images which
        cannot be located:</p>
<?php if (count($th_noshows) > 0) : ?>
    <ul>
    <?php for ($k=0; $k<count($th_noshows); $k++) : ?>
    <li><?= $th_noshows[$k];?></li>
    <?php endfor; ?>
    </ul>
<?php endif; ?>
</div>

<script src="cleanPix.js"></script>

</body>
</html>
