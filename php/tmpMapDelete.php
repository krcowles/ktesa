<?php
/**
 * This simple script is ajaxed by any hike page to delete its temporary
 * map file. The map file is created for the purpose of invoking an iframe.
 * PHP Version 7.1
 * 
 * @package Page_Display
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$tmpFile = filter_input(INPUT_GET, 'file');
if (file_exists($tmpFile)) {
    unlink($tmpFile);
}
