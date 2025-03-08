<?php
/**
 * This script is designed to simplify identification of JSON files that need to
 * be downloaded and/or eliminated from the (new) local download branch after one
 * or more hikes have been published. It uses the files created by the publish.php
 * script (deleted.txt and changed.txt) and xfrPub.php (pub_xfrs.txt) to create a
 * list to be applied to the download branch.
 * NOTE: The $changes array may hold a list of files that were actually restored
 * after having been initially deleted in order to properly update the database.
 * The restored files may have the same name as the deleted ones, but may have
 * different contents, so should be tagged for download.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$actions   = file_exists("actions.txt") ?
    file("actions.txt", FILE_IGNORE_NEW_LINES) : [];
if (empty($actions)) {
    echo "No publishing actions found";
    exit;
} else {
    $hikeNos = [];
    foreach ($actions as $line) { // May be more than 1 hike published since last git
        $indxLoc = strpos($line, "EHIKE no");
        if ($indxLoc !== false) {
            $terminus = strpos($line, ";");
            $lgth = $terminus - ($indxLoc + 9); 
            $indxNo = substr($line, $indxLoc+9, $lgth);
            array_push($hikeNos, $indxNo);
        }
    }
}
$changed   = file_exists("changed.txt") ? file_get_contents("changed.txt") : '';
$deleted   = file_exists("deleted.txt") ? file_get_contents("deleted.txt") : '';
$xfrdJson  = file_exists("pub_xfrs.txt") ? file_get_contents("pub_xfrs.txt") : '';
$changes   = empty($changed) ? [] : explode(",", $changed);
$deletions = empty($deleted) ? [] : explode(",", $deleted);
$xfrs      = empty($xfrdJson) ? [] :explode(",", $xfrdJson);

if (count($changes) === 0 && count($deletions) === 0) {
    echo '<h4 style="margin-left:3rem;">There are no changes to process</h4>';
    exit;
}
$downloads = [];
foreach ($changes as $download) {
    $newPjson = substr($download, 2);
    $item = "<li>" . $newPjson . "</li>" . PHP_EOL;
    array_push($downloads, $item);
}
$old_deletes = [];
$new_deletes = [];
foreach ($deletions as $remove) {
    $dtype = substr($remove, 0, 1);
    $file  = substr($remove, 2);
    $ditem = "<li>" . $file . "</li>" . PHP_EOL;
    if ($dtype === "E") {    
        array_push($new_deletes, $ditem);
    } elseif ($dtype === "P") {
        array_push($old_deletes, $ditem);
    }
}
$from_pub = [];
foreach ($xfrs as $xfr) {
    $semi_pos = strpos($xfr, ";");
    $plgth = $semi_pos - 2;
    $pHikeNo = substr($xfr, 2, $plgth);
    if (in_array($pHikeNo, $hikeNos)) {
        $last_colon_pos = strrpos($xfr, ":");
        $json_file = substr($xfr, $last_colon_pos+1);
        $e_pos = $semi_pos + 3;
        $elgth = $last_colon_pos - $e_pos;
        $eHikeNo = substr($xfr, $e_pos, $elgth);
        $item = "<li>Published hike {$pHikeNo} transferred to E-Hike {$eHikeNo}; " .
            "json file: {$json_file}</li>";
        array_push($from_pub, $item);
    }
}
// check all json files for 'e' json
$current_json = scandir("../json");
$ejson = [];
foreach ($current_json as $json) {
    if (substr($json, 0, 1) === 'e') {
        $item = "<li>" . $json . "</li>" . PHP_EOL;
        array_push($ejson, $item);
    }
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>JSON File Changes</title>
    <meta charset="utf-8" />
    <meta name="description" content="Download/change advice after publish" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <style type="text/css">
        #contents { margin-left: 3rem;}
    </style>
</head>
<body>
    <div id="contents">
        <?php if (!empty($new_deletes)) : ?>
        <h4>
            The following files should no longer appear in the json directory:
        </h4>
        <ul id="removals">
            <?php foreach ($new_deletes as $djson) : ?>
                <?=$djson;?>
            <?php endforeach; ?>
        </ul>
        <?php else : ?>
        <h4>There are no JSON files to remove</h4>
        <?php endif; ?> 
        <br />

        <?php if (!empty($old_deletes)) : ?>
        <h4>
            The following production json files were removed, and some or all
            may have been reloaded:
        </h4>
        <ul id="eliminations">
            <?php foreach ($old_deletes as $pjson) : ?>
                <?=$pjson;?>
            <?php endforeach; ?>
        </ul>
        <?php else : ?>
        <h4>No production json files were deleted</h4><br />
        <?php endif; ?>
        <?php if (!empty($downloads)) : ?>
        <h4>
            The following files changed and should be downloaded to localhost
        </h4>
        <ul id="downloads">
            <?php foreach ($downloads as $newj) : ?>
                <?=$newj;?>
            <?php endforeach; ?>
        </ul>
        <?php else : ?>
        <h4>No production json files were transferred</h4>
        <?php endif; ?>
        <?php if (!empty($ejson)) : ?>
        <h4>The following in-edit json files should reside in the json directory:
        </h4>
        <ul id="remaining">
            <?php foreach ($ejson as $left) : ?>
                <?=$left;?>
            <?php endforeach; ?>
        </ul>
        <?php else : ?>
        <h4>There should be no in-edit json files in the json directory</h5>
        <?php endif; ?>
    </div>
</body>
</html>
