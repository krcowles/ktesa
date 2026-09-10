<?php
/**
 * This script is used to force a document download for pdf files.
 * Browsers can behave differently using a simple HTML <a download>
 * link, and this script overcomes many of those differences by assigning 
 * appropriate headers. Some browsers will nonetheless insist on also
 * displaying the document. PDF's must reside in the data directory.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
// strip any path, avoid directory traversal:
$requested = basename($_GET['file'] ?? '');
$path = "../pdf/{$requested}";

if ($requested === '' || !is_file($path)
    || pathinfo($path, PATHINFO_EXTENSION) !== 'pdf'
) {
    http_response_code(404);
    exit('File not found.');
}
$downloadName = basename($_GET['name'] ?? $requested);
if ($downloadName === '' || strtolower(pathinfo($downloadName, PATHINFO_EXTENSION)) !== 'pdf') {
    $downloadName = pathinfo($downloadName, PATHINFO_FILENAME) . '.pdf';
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');

readfile($path);
exit;
