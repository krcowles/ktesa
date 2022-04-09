<?php
/**
 * This file sends the file contents of a gpx file to a web page. This was
 * developed because previous techniques resulted in different - or no - displays
 * as browsers changed and evolved and standard techniques no longer worked.
 * Currently, attempting to display raw file contnents via a link causes a download
 * to occur.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$gpxfile = filter_input(INPUT_GET, 'gpx');
$file    = '../gpx/' . $gpxfile;
$content = file_get_contents($file);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>GPX File Contents: <?=$gpxfile;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Display GPX File" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <script src="../scripts/jquery.js"></script>
    <script type="text/javascript">
        $('textarea').autoResize();
    </script>
    <style type="text/css">
        textarea {
            width: 90%;
            resize: vertical;
            border: none;
            min-height: 1100px;
        }
     </style>
</head>
     
<body> 
<div style="margin-left: 18px;">
   <textarea><?=$content;?></textarea>
</div>

</body>
</html>
