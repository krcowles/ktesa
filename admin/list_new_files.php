<?php
/**
 * List all the files that are new since the last upload (based on 
 * timestamp), and present that list on a page; Allow script to ignore
 * 'test sites', as hundreds of those files would otherwise be displayed.
 * In all cases, the code will recursively scan the project directory and
 * identify the desired items. This same script is used to list only new
 * pictures - by date or by comparison to selected photo.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require '../php/global_boot.php';
require 'filterClass.php';

$request = filter_input(INPUT_GET, 'request');  // either 'files' or 'pictures'
$testSites = isset($_GET['tsites']) ? filter_input(INPUT_GET, 'tsites') : false;
// parms when looking for pictures 'newer than...' (both parms must exist)
$dtFile = isset($_GET['dtFile']) ? filter_input(INPUT_GET, 'dtFile') : false;
$dtTime = isset($_GET['dtTime']) ? filter_input(INPUT_GET, 'dtTime') : false;
$newerThan = (isset($dtFile) || isset($dtTime)) ? true : false;

$ignores = [];
if ($testSites) {
    $ignores = explode(",", $testSites);
}
$tmpPix = sys_get_temp_dir() . '/newPix.zip';
if (file_exists($tmpPix)) {
    unlink($tmpPix);
}

// save current directory location, prior to changing it to find 'pictures'
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
    $thisSiteRoot, RecursiveDirectoryIterator::SKIP_DOTS
);
$filtered = new DirFilter($dir_iterator, $ignores); 
$iterator = new RecursiveIteratorIterator(
    $filtered, RecursiveIteratorIterator::SELF_FIRST
);
//$inputDate = "02/20/2019 1:30:00";   // Use these lines to manually enter a date
//$uploadDate = strtotime($inputDate); // Use these lines to manually enter a date

$toobig = false;
$items = [];
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
    /**
     * NOTE: download memory limit (20MB) may be exceeded resulting in
     * no download. 
     */
    $zip = new ZipArchive();
    $stat = $zip->open($tmpPix, ZipArchive::CREATE);
    if ($stat !== true) {
        throw new Exception("ZipArchive Error: " . $stat);
    }
    $iter = 0;  // need to know if there are no pix
    foreach ($items as $newpic) {
        if (strpos($newpic, 'DS_Store') === false
            && (strpos($newpic, 'pictures/previews') !== false 
            || strpos($newpic, 'pictures/thumbs') !== false
            || strpos($newpic, 'pictures/zsize') !== false )
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
        if (filesize($tmpPix) > 19500000) {
            $toobig = true;

        } else {
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=newPix.zip");
            readfile($tmpPix);
            exit();
        }
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/admintools.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>
<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">List New Files Since Last Upload</p>
<p id="active" style="display:none">Admin</p>

<div style="margin-left:24px;">
<p style="font-size:16px;">Upload date: <?= $udate;?></p>
<?php if ($toobig) : ?>
    <p style="font-size:18px;color:brown;">File size exceeded 20MB
        and was not downloaded</p>
<?php else : ?>
    <p style="font-size:16px;color:brown;">Files changed since upload:</p>
    <?php foreach ($items as $nfile) : ?>
        <?= $nfile;?><br />
    <?php endforeach; ?>
<?php endif; ?>
<p style="font-size:18px;color:brown;">DONE</p>
</div>

</body>
</html>
