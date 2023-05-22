<?php
/**
 * This module opens the PDF document specified in the query string.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$doc = filter_input(INPUT_GET, 'doc');
$doc = urldecode($doc);

$privacy = "PrivacyPolicy.pdf";
// for new documents, add a var def and and test to the following 'if'
if ($doc === $privacy) {
    $doc = '../accounts/' . $privacy;
}
$file = $doc;
$filename = $doc;
header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($file));
header('Accept-Ranges: bytes');
readfile($file);
