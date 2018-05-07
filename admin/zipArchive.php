<?php
/**
 * This script iterates through the project directory, looking
 * for files that have changed since the last time stamp (when
 * dummy.txt was uploaded). Those files are then added to a zip
 * archive and downloaded.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($dev) {
    $mysqlUserName = USERNAME_LOC;
    $mysqlPassword = PASSWORD_LOC;
    $mysqlHostName = HOSTNAME_LOC;
    $DbName = DATABASE_LOC;
} else {
    $mysqlUserName = USERNAME_000;
    $mysqlPassword = PASSWORD_000;
    $mysqlHostName = HOSTNAME_000;
    $DbName = DATABASE_000;
}
$db = sys_get_temp_dir() . '/' . $DbName . '.sql';
$tmpFilename = sys_get_temp_dir() . '/changes.zip';
if (file_exists($tmpFilename)) {
    unlink($tmpFilename);
}
$zip = new ZipArchive();
$ziparch = $zip->open($tmpFilename, ZipArchive::CREATE);
// get new file list:
$dir_iterator = new RecursiveDirectoryIterator(
    "../", RecursiveDirectoryIterator::SKIP_DOTS
);
$iterator = new RecursiveIteratorIterator(
    $dir_iterator, RecursiveIteratorIterator::SELF_FIRST
);
// Upload time plus 20 seconds for unzip
$uploadDate = filemtime("./dummy.txt") + 20;
$udate = date(DATE_RFC2822, $uploadDate);
$chgList = array();
foreach ($iterator as $file) {
    if ($file->isFile()) {
        if ($file->getMTime() > $uploadDate) {
            if (substr($iterator->getSubPathName(), 0, 4) !== '.git') {
                $zip->addFile($file);
                array_push($chgList, $file);
            }
        }
    }
}
$zip->addFile($db, '../data/' . $DbName . '.sql');
$zip->close();
// Download the zip file
header("Content-Type: application/x-gzip");
header("Content-Disposition: attachment; filename=".basename($tmpFilename));
header("Content-Length: " . filesize($tmpFilename));    
header("Content-Transfer-Encoding: binary");
readfile($tmpFilename);
// clean up
unlink($tmpFilename);;
unlink($db);
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
<p id="trail">Create Zip Archive</p>
<div style="margin-left:24px;">
List of files that have changes and have been zipped:
<ol>
<?php foreach($chgList as $fname) : ?>
<li><?= $fname;?></li>
<?php endforeach; ?>
</ol>
DONE!
</div>
</body>
</html>
