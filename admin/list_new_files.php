<?php
/**
 * This script is common to two basic admin functions: "List New Files"
 * and "New Pictures"/"Pictures Newer Than...". The associated buttons on
 * admintools.php invoke this script via admintools.js. The action taken by
 * this script is dependent on the query string used to invoke it. For the
 * 'New Files' button, the query string generated in admintools.js is
 * 'request=files'; for both 'Pictures' buttons, the query string becomes
 * 'request=pictures'. The recursive action taken is the same for all cases,
 * but the files compared differ.  
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require '../php/global_boot.php';
$adminDir ="{$thisSiteRoot}/admin/";
// default 'compare to' date/time:
$uploadDate = filemtime("{$adminDir}/dummy.txt") + 20;
//$inputDate = "02/20/2019 1:30:00";   // Use these lines to manually enter a date
//$uploadDate = strtotime($inputDate); // Use these lines to manually enter a date
// additional parameters may be specified in the query string [below]
$request = filter_input(INPUT_GET, 'request');
if ($request === 'files') {
    $directories = [];
    // Note that no test sites or pictures are included here...
    $core = [
        'accounts',
        'admin',
        'data',
        'edit',
        'images',
        'json',
        'kml',
        'maps',
        'mobile_sass',
        'pages',
        'php',
        'sass',
        'scripts',
        'styles',
        'test',
        'tools'
    ];
    foreach ($core as $dir) {
        array_push($directories, "{$thisSiteRoot}/{$dir}");
    }
} else { // request === 'pictures'
    $tmpPix = sys_get_temp_dir() . '/newPix.zip';
    if (file_exists($tmpPix)) {
        unlink($tmpPix);
    }
    $zip = new ZipArchive();
    $stat = $zip->open($tmpPix, ZipArchive::CREATE);
    if ($stat !== true) {
        throw new Exception("ZipArchive Error: " . $stat);
    }
    $current = getcwd();
    $restore = $current;
    while (!in_array('pictures', scandir($current))) {
        chdir('..');
        $current = getcwd();
    }
    $pic_dir = "{$current}/pictures";
    chdir($restore);
    $zsize_dir    = "{$pic_dir}/zsize";
    $previews_dir = "{$pic_dir}/previews";
    $thumbs_dir   = "{$pic_dir}/thumbs";
    $directories  = [$zsize_dir, $previews_dir, $thumbs_dir];
    // look for additional query string parameters
    $dtFile = isset($_GET['dtFile']) ?
        filter_input(INPUT_GET, 'dtFile') : false;
    $dtTime = isset($_GET['dtTime']) ?
        filter_input(INPUT_GET, 'dtTime') : false;
    // if either of the above are set, then 'Pictures Newer Than' has been invoked
    $newerThan = $dtFile || $dtTime ? true : false;
    if ($newerThan) {
        // Get date from either photo specified ($dtFile) or calendar data ($dtTime)
        if (isset($dtFile) && $dtFile !== '') {
            $uploadDate = filemtime("./{$dtFile}");
        } else {
            $uploadDate = strtotime($dtTime);
        }
    }
}
$udate = date(DATE_RFC2822, $uploadDate);
/**
 * Looking only for files - there should generally be no sub-directories within the
 * $directories array. Exceptions are explicitly specified.
 */
$qualified = [];
foreach ($directories as $dir) {
    $contents = scandir($dir);
    $clean_list = array_filter(
        $contents, function ($item) {
            if ($item === '.' || $item === '..' || $item === '.DS_Store') {
                return false;
            } else {
                return true;
            }
        }
    );
    foreach ($clean_list as $item) {
        $candidate = "{$dir}/{$item}";
        if (is_file($candidate)) {
            $file = new SplFileInfo($candidate);
            if ($file->getMTime() > $uploadDate) {
                if ($request === 'files') {
                    $candidate .= ": " . date(DATE_RFC2822, $file->getMTime());
                } 
                array_push($qualified, $candidate);
                
            }
        } else {
            // some sub-dirs also need to be searched
            if (strpos($item, "logos") !== false
                || strpos($item, "domdoc")
            ) {
                $subscan = scandir($candidate);
                foreach ($subscan as $sub) {
                    $sub_item = "{$candidate}/{$sub}";
                    $file = new SplFileInfo($sub_item);
                    if ($file->getMTime() > $uploadDate) {
                        $file .= ": " . date(DATE_RFC2822, $file->getMTime());
                        array_push($qualified, $sub_item);
                    }
                }
            }
        }
    }
}
if ($request === 'pictures') {
    /**
     * NOTE: download memory limit (20MB) may be exceeded resulting in
     * no download. 
     */
    $iter = 0;  // need to know if there are no pix
    foreach ($qualified as $newpic) {
        $zip->addFile($newpic);
        $iter++;
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
<p style="font-size:16px;color:brown;">Files changed since upload:</p>
<?php foreach ($qualified as $nfile) : ?>
    <?= $nfile;?><br />
<?php endforeach; ?>
<p style="font-size:18px;color:brown;">DONE</p>
</div>

</body>
</html>
