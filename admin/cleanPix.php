<?php
/**
 * This script extracts the picture filenames from both TSV and ETSV
 * and then compares them to the files currently residing in 'pictures'
 * for zsize photos. The subsequent html will list the 
 * candidates for deletion, and if the admin wishes to then remove 
 * those extraneous files, he may do so by checking the boxes provided,
 * and then selecting the 'Remove' button. In addition, those same tables
 * are compared to the filelist to see if any of the table entries do not
 * have matching files.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$published_query = "SELECT picIdx,thumb,mid FROM TSV;";
$in_edit_query   = "SELECT picIdx,thumb,mid FROM ETSV;";
$published_pix   = $pdo->query($published_query);
$in_edit_pix     = $pdo->query($in_edit_query);

// arrays for deletion candidates
$base_names     = [];  // used to look for filenames w/differing thumb values
$zsize_in_table = [];
$table_entry_id = [];

foreach ($published_pix as $photo) {
    if (!empty($photo['mid'])) {  // no waypoints please
        array_push($base_names, $photo['mid']);
        $zid = $photo['mid'] . "_" . $photo['thumb'] . "_z.jpg";
        array_push($zsize_in_table, $zid);
        array_push($table_entry_id, "TSV-" . $photo['picIdx']);
    }
}
foreach ($in_edit_pix as $photo) {
    if (!empty($photo['mid'])) {
        array_push($base_names, $photo['mid']);
        $zid = $photo['mid'] . "_" . $photo['thumb'] . "_z.jpg";
        array_push($zsize_in_table, $zid);
        array_push($table_entry_id, "ETSV-" . $photo['picIdx']);
    }
}

// find level at which pictures directory resides
$current = getcwd();
$startDir = $current;
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
$zsize_candidates = [];
$thumb_mismatch   = [];
$zsize_noshows    = [];

// repeat for zsize:
$photo_array      = scandir('pictures/zsize');
array_shift($photo_array);  // eliminate '.'
array_shift($photo_array);  // eliminate '..'
if ($photo_array[0] == '.DS_Store') {  // MacOS 
    array_shift($photo_array);
}
// are any table entries NOT represented in the zsize file list?
for ($z=0; $z<count($zsize_in_table); $z++) {
    if (!in_array($zsize_in_table[$z], $photo_array)) {
        $report = "[" . $table_entry_id[$z] . "] " . $zsize_in_table[$z];
        array_push($zsize_noshows, $report);
    }
}
// conversely, are any files not found in the table entries?
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
chdir($startDir);
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
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="cleanPix.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Photo Cleanup Utility</p>
<p id="page_id" style="display:none">Admin</p>

</div>
<div id="main">
<form id="form" action="photoCleanup.php" method="POST" />
    <input id="all" type="checkbox" name="all" />
    <label for="all">Select All Photos (Or unselect all if selected)
        </label><br /><br />
    <input type="submit" name="submit" value="Delete Selected Photos" />
        &nbsp;&nbsp;OR&nbsp;&nbsp;
    <input type="submit" name="submit" value="Create Shell to Delete" />&nbsp;&nbsp;
        Script will reside in 'pictures' directory and should be invoked there
    <input type="hidden" name="action" value="unlink" />
    <p class="head">The following directory items were found to have no matching
        entries in the database:</p>
    <span class="types">Z-size photos:</span><br />
    <ul>
<?php foreach($zsize_candidates as $zsize) : ?>
    <li><input id="chkbox<?= $i++;?>" type="checkbox" name="checkboxes[]"
        value="<?= $zsize;?>" />&nbsp;&nbsp;<?= $zsize;?></li>
<?php endforeach; ?>
    </ul>
    <span class="types">Photos having a matching base name only, but 'thumb'
        is incorrect:</span><br />
    <ul>
<?php foreach($thumb_mismatch as $wrong_key) : ?>
    <li><input id="chkbox<?= $i++;?>" type="checkbox" name="checkboxes[]"
        value="<?= $wrong_key;?>" />&nbsp;&nbsp;<?= $wrong_key;?></li>
<?php endforeach; ?>
    </ul>
    <input type="hidden" name="total" value="<?= $i;?>" />
</form>
<p class="head">A zsize filename is in the database, but no file exists:</p>
<?php if (count($zsize_noshows) > 0) : ?>
    <ul>
    <?php for ($j=0; $j<count($zsize_noshows); $j++) : ?>
    <li><?= $zsize_noshows[$j];?></li>
    <?php endfor; ?>
    </ul>
<?php endif; ?>
</div>
<script src="../scripts/menus.js"></script>
<script src="cleanPix.js"></script>

</body>
</html>
