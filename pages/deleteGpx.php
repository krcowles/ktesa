<?php
/**
 * Delete a gpx file created by the server to download
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$gpx2delete = filter_input(INPUT_POST, 'gpx');
if (unlink("../pages/" . $gpx2delete) === false) {
    throw new Exception("Did not remove {$gpx2delete}");
}
