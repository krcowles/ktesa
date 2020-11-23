<?php
/**
 * This simple script is ajaxed by any hike page to delete its temporary
 * map file. The map file is created for the purpose of invoking an iframe.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
verifyAccess('get');

$tmpFile = filter_input(INPUT_GET, 'file');
$file = $documentRoot . '/maps/tmp/' . $tmpFile . '.php';
if (file_exists($file)) {
    unlink($file);
}
