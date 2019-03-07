<?php
/**
 * This module will recursively scan the project directory and identify any
 * files, based on timestamp, that are newer than the last upload.
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
require '../php/global_boot.php';
$dir_iterator = new RecursiveDirectoryIterator("../", RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
// could use CHILD_FIRST if you so wish
$uploadDate = filemtime("./dummy.txt") + 20; // Upload time plus 20 seconds for unzip
//$inputDate = "02/06/2018 1:30:00"; // Use these lines to manually enter a date
//$uploadDate = strtotime($inputDate); // Use these lines to manually enter a date
$udate = date(DATE_RFC2822, $uploadDate);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Site Admin Tools</title>
    <meta charset="utf-8" />
    <meta name="description" content="List new files since last upload" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">List New Files Since Last Upload</p>
<div style="margin-left:24px;">
<p style="font-size:16px;">Upload date: <?= $udate;?></p>
<p style="font-size:16px;color:brown;">Files changed since upload:</p>
<?php
//echo "Upload date: " . date(DATE_RFC2822, $uploadDate) . "<br /><br />";
//echo "Files changed since upload:<br />";
foreach ($iterator as $file) {
    if ($file->isFile()) {
        if ($file->getMTime() > $uploadDate) {
            //$leaf = $iterator->getSubPathName();
            if (substr($iterator->getSubPathName(), 0, 4) !== '.git') {
                echo $iterator->getSubPathName() . ": " . date(DATE_RFC2822, $file->getMTime()) . "<br>";
            }
        }
    }
}
?>
<p style="font-size:18px;color:brown;">DONE</p>
</div>
</body>
</html>