<?php
/**
 * Create a compressed archive for automatically downloading to 
 * the browser. 
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$tmpFilename = sys_get_temp_dir() . '/archive.phar';
if (file_exists($tmpFilename)) {
    unlink($tmpFilename);
}
if (file_exists($tmpFilename . '.gz')) {
    unlink($tmpFilename . '.gz');
}
$phar = new PharData($tmpFilename);
// add all files in the project and then compress it
$phar->buildFromDirectory('../', '/^((?!vendor|\.git|maps\/tmp).)*$/');
$phar->compress(Phar::GZ);
// Download the compressed phar file
header("Content-Type: application/x-gtar");
header("Content-Disposition: attachment; filename=".basename($tmpFilename . '.gz'));
header("Content-Length: " . filesize($tmpFilename . '.gz'));    
header("Content-Transfer-Encoding: binary");
readfile($tmpFilename . '.gz');
// clean up
unlink($tmpFilename);
unlink($tmpFilename . '.gz');
