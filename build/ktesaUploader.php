<?php
/**
 * This page allows for uploading of images to the site (with subsequent
 * storage of image data in the ETSV table). Files can be selected via the
 * upload button, or by dragging and dropping them into the main div.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$EHikeNo = filter_input(INPUT_GET, 'indx');
$Euser = filter_input(INPUT_GET, 'usr');
$svg = "M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 " .
    "2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2." .
    "6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 " .
    "1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Uploader</title>
    <meta charset="utf-8" />
    <meta name="description" content="Upload user's photos" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="ktesaUploader.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>   
<?php require "../pages/ktesaPanel.php"; ?>
<p id="ehno" style="display:none;"><?= $EHikeNo;?></p>
<p id="eusr" style="display:none;"><?= $Euser;?></p>
<p id="trail">Upload Your Photos!</p>
<p id="page_id" style="display:none">Build</p>

<form class="box" method="post" action="#">
    <!-- FORM input selection div -->
    <div class="box__input">
        <input type="file" name="files[]" id="file" class="inputfile"
            data-multiple-caption="&nbsp;&nbsp;{count} files selected" multiple />
        <label for="file"><svg xmlns="http://www.w3.org/2000/svg"
            width="20" height="17" viewBox="0 0 20 17">
            <path d="<?=$svg;?>"/></svg>
            <span>&nbsp;&nbsp;Choose one or more photos&hellip;</span></label>
    </div>
    <div id="box__dragndrop"> &nbsp;&nbsp;OR, you may drag and drop your
        photos on the page</div>

    <div class="box__dnd">
        <button id="save" class="box__button" type="submit">Upload & Return</button>
        
        </span><br />
        <div id="acts">
            <span id="ldg">&nbsp;&nbsp;<strong>Processing 
                images&hellip;Please wait&nbsp;&nbsp;</strong>
            </span>
            <span id="loadbar"></span><br /><br />
            <br /><br />
        </div>
        <!-- Image rows will be inserted here -->
        <div id="image-row"></div>
    </div>
</form>

<script src="../scripts/menus.js"></script>
<script src="ktesaUploader.js"></script>
<script src="exifReader.js"></script>

</body>
</html>