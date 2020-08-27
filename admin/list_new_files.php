<?php
/**
 * This module serves several purposes:
 *  1. List all the files that are new since the last upload (based on 
 *     timestamp), and present that list on a page;
 *  2. Download all pictures that have been added since the last upload
 *     (based on timestamp);
 *  3. Download all pictures that are newer than:
 *      a. A picture file selected *locally* from the 'pictures' directory
 *      b. A user-selected calendar date
 * In all cases, the code will recursively scan the project directory and
 * identify the desired items.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require '../php/global_boot.php';
$request = filter_input(INPUT_GET, 'request');  // either 'files' or 'pictures'
$dtFile = filter_input(INPUT_GET, 'dtFile');  // filepath for comparison picture
$dtTime = filter_input(INPUT_GET, 'dtTime');  // time for locating new pictures
$newerThan = (isset($dtFile) || isset($dtTime)) ? true : false;
$tmpPix = sys_get_temp_dir() . '/newPix.zip';
if (file_exists($tmpPix)) {
    unlink($tmpPix);
}

$dir_iterator = new RecursiveDirectoryIterator(
    "../", RecursiveDirectoryIterator::SKIP_DOTS
);
$iterator = new RecursiveIteratorIterator(
    $dir_iterator, RecursiveIteratorIterator::SELF_FIRST
);
//$inputDate = "02/20/2019 1:30:00"; // Use these lines to manually enter a date
//$uploadDate = strtotime($inputDate); // Use these lines to manually enter a date

// save current directory location, prior to changing to find 'pictures'
$current = getcwd();
$adminDir = $current;
// get location of pictures directory
$ups = 0;
// find level at which pictures directory resides
while (!in_array('pictures', scandir($current))) {
    chdir('..');
    $current = getcwd();
    $ups++;
    if ($ups > 10) { 
        throw new Exception("Can't find pictures directory!");
    }
}
if (!$newerThan) {
    // Upload time plus 20 seconds for unzip
    $uploadDate = filemtime("{$adminDir}/dummy.txt") + 20;
} else {
    // Use the date/time from the query string parameters
    if (isset($dtFile) && $dtFile !== '') {
        $uploadDate = filemtime("./{$dtFile}");
    } else {
        $uploadDate = strtotime($dtTime);
    }
}
$udate = date(DATE_RFC2822, $uploadDate);

$dir_iterator = new RecursiveDirectoryIterator(
    ".", RecursiveDirectoryIterator::SKIP_DOTS
);
$iterator = new RecursiveIteratorIterator(
    $dir_iterator, RecursiveIteratorIterator::SELF_FIRST
);
// could use CHILD_FIRST if you so wish
$items = [];
//echo "Upload date: " . date(DATE_RFC2822, $uploadDate) . "<br /><br />";
//echo "Files changed since upload:<br />";
foreach ($iterator as $file) {
    if ($file->isFile()) {
        if ($file->getMTime() > $uploadDate) {
            $leaf = $iterator->getSubPathName();
            if (substr($leaf, 0, 4) !== '.git' && $leaf !== '.DS_Store') {
                if ($request === 'files') {
                    $leaf .= ": " . date(DATE_RFC2822, $file->getMTime());
                } 
                array_push($items, $leaf);
            }
        }
    }
}
if ($request === 'pictures') {
    $zip = new ZipArchive();
    $stat = $zip->open($tmpPix, ZipArchive::CREATE);
    if ($stat !== true) {
        throw new Exception("ZipArchive Error: " . $stat);
    }
    $iter = 0;  // need to know if there are no pix
    foreach ($items as $newpic) {
        if (strpos($newpic, 'pictures/zsize') !== false 
            && strpos($newpic, 'DS_Store') === false
        ) {
            $zip->addFile($newpic);
            $iter++;
        }
    }
    $zip->close();
    if ($iter === 0) {
        $_SESSION['nopix'] = "No new pictures to download";
        header("Location: admintools.php");
    } else {
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=newPix.zip");
        readfile($tmpPix);
        exit();
    }
}
chdir($adminDir);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Site Admin Tools</title>
    <meta charset="utf-8" />
    <meta name="description" content="List new <?= $request;?> since last upload" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>
<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">List New Files Since Last Upload</p>
<p id="page_id" style="display:none">Admin</p>

<div style="margin-left:24px;">
<p style="font-size:16px;">Upload date: <?= $udate;?></p>
<p style="font-size:16px;color:brown;">Files changed since upload:</p>
<?php foreach ($items as $nfile) : ?>
    <?= $nfile;?><br />
<?php endforeach; ?>
<p style="font-size:18px;color:brown;">DONE</p>
</div>
<script src="../scripts/menus.js"></script>

</body>
</html>
