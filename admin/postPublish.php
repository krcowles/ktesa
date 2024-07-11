<?php
/**
 * This script is designed to simplify identification of JSON
 * files that need to be downloaded and/or eliminated from the
 * (new) local download branch after a hike has been published.
 * It uses the files created by the publish.php script (deleted.txt
 * and changed.txt) to create a list to be applied to the branch.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$changed   = file_get_contents("changed.txt");
$deleted   = file_get_contents("deleted.txt");
$prevJson  = file_get_contents("pub_xfrs.txt");
$changes   = explode(",", $changed);
$deletions = explode(",", $deleted);
$previous  = explode(",", $prevJson); // published hikes in-edit

if (count($changes) === 0 && count($deletions) === 0) {
    echo '<h4 style="margin-left:3rem;">There are no changes to process</h4>';
    exit;
}
$downloads = '';
foreach ($changes as $download) {
    $downloads .= "<li>" . $download . "</li>" . PHP_EOL;
}
$removals = '';
foreach ($deletions as $remove) {
        $removals .= "<li>" . $remove . "</li>" . PHP_EOL;
}
// Any files published back ($changes) can be removed from $previous
$eliminate = '';
foreach ($previous as $pjson) {
    if (!in_array($pjson, $changes)) {
        $eliminate .= "<li>" . $pjson . "</li>" . PHP_EOL;
    } else {
        $key = array_search($pjson, $previous);
        unset($previous[$key]);
    }
}
$current_json = scandir("../json");
$ejson = '';
foreach ($current_json as $json) {
    if (substr($json, 0, 1) === 'e') {
        $ejson .= "<li>" . $json . "</li>" . PHP_EOL;
    }
}
if (count($previous) === 0) {
    unlink("pub_xfrs.txt");
} else {
    $updated_xfrs = implode(",", $previous);
    file_put_contents("pub_xfrs.txt", $updated_xfrs);
}
unlink("changed.txt");
unlink("deleted.txt");
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
        <?php if (!empty($removals)) : ?>
        <h4>
            The following files should no longer appear in the json/ directory:
        </h4>
        <ul id="removals">
            <?=$removals;?>
        </ul>
        <?php else : ?>
        <h4>There are no JSON files to remove</h4>
        <?php endif; ?> 
        <br />
        <?php if (!empty($eliminate)) : ?>
        <h4>
            The following production json files should no longer exist:
        </h4>
        <ul id="eliminations">
            <?=$eliminate;?>
        </ul>
        <?php else : ?>
        <h4>There are no production json files to delete</h4><br />
        <?php endif; ?>
        <?php if (!empty($downloads)) : ?>
        <h4>
            The following files should be downloaded to localhost
        </h4>
        <ul id="downloads">
            <?=$downloads;?>
        </ul>
        <?php else : ?>
        <h4>No production json files have changed</h4>
        <?php endif; ?>
        <?php if (!empty($ejson)) : ?>
        <h4>The following in-edit json files should reside in the json directory:
        </h4>
        <ul id="remaining">
            <?=$ejson;?>
        </ul>
        <?php else : ?>
        <h4>There should be no in-edit json files in the json directory</h5>
        <?php endif; ?>
    </div>
</body>
</html>
