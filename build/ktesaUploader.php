<?php
/**
 * This page allows for uploading of images to the site (with subsequent
 * storage of image data in the ETSV table). Files can be selected via the
 * upload button, or by dragging and dropping them into the main div.
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$EHikeNo = filter_input(INPUT_GET, 'indx');
$Euser = filter_input(INPUT_GET, 'usr');
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Uploader</title>
    <meta charset="utf-8" />
    <meta name="description" content="Upload user's photos" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="ktesaUploader.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery-1.12.1.js"></script>
</head>

<body>   
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="ehno" style="display:none;"><?= $EHikeNo;?></p>
<p id="eusr" style="display:none;"><?= $Euser;?></p>
<p id="trail">Upload Your Photos!</p>
<form class="box" method="post" action="usrPhotos.php" enctype="multipart/form-data">
    <div class="box__input">
        <input type="file" name="files[]" id="file" class="inputfile"
            data-multiple-caption="&nbsp;&nbsp;{count} files selected" multiple />
        <label for="file"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg>
            <span>&nbsp;&nbsp;Choose one or more photos&hellip;</span></label>
    </div>
    <div class="box__dnd">
        <span class="box__dragndrop"> OR, you may drag and drop your photos here:
            [Will not appear in original size]
        </span><br />
        <div id="acts">
            <button class="box__button" type="submit">Upload</button>
            <input id="clrimgs" type="button" value="Clear images" />
            <button id="ret">Back To Editor</button>
            <span id="ldg" class="blink">&nbsp;&nbsp;Processing images&hellip;
                Please wait</span>
            <span class="box__uploading">Upload in progress&hellip;
                Please wait</span>
            </span>
            <pre>
            </pre>
        </div>
    </div>
</form>
<script src="ktesaUploader.js"></script>
<script src="meterReader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/exif-js"></script>
</body>
</html>