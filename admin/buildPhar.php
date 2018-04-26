<?php
$tmpFilename = sys_get_temp_dir() . '/archive.tar';
if (file_exists($tmpFilename)) {
    unlink($tmpFilename);
}
if (file_exists($tmpFilename . '.gz')) {
    unlink($tmpFilename . '.gz');
}
$phar = new PharData($tmpFilename);
// add all files in the project and then compress it
$phar->buildFromDirectory('../');
$phar->compress(Phar::GZ);
// Download the zip file
header("Content-Type: application/x-gtar");
header("Content-Disposition: attachment; filename=".basename($tmpFilename . '.gz'));
header("Content-Length: " . filesize($tmpFilename . '.gz'));    
header("Content-Transfer-Encoding: binary");
readfile($tmpFilename . '.gz');
// clean up
unlink($tmpFilename);
unlink($tmpFilename . '.gz');
?>
