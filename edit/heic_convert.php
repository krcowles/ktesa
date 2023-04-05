<?php
/**
 * This page allows a user to upload one or more .heic files and convert
 * them to .jpg. Since conversion loses metadata, first the metadata is
 * extracted from the .heic file. The heic is then converted to .jpg and
 * placed in the upload package. The relevant metadata is added to the 
 * upload package, which is then ajax'ed to store the jpg to the pictures
 * directory and update the ETSV table. This is accomplished via this
 * module's various scripts.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
session_start();
$ehike = filter_input(INPUT_GET, 'ehike');
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>HEIC Converter</title>
    <meta charset="utf-8" />
    <meta name="description" content="Convert .heic to .jpg and get metadata" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/editDB.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body> 
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">HEIC Converter</p>
<p id="active" style="display:none;">Edit</p>
<a id="anchor" style="display:none;"></a>
<p id="ehike"  style="display:none;"><?=$ehike;?></p>

<span id="selbtn">
    <input type="file" name="files[]" id="file" class="inputfile"
        data-multiple-caption="&nbsp;&nbsp;{count} files selected" multiple />
    <label for="file">
        <span>&nbsp;&nbsp;Select HEIC photos&hellip;</span>
    </label>
</span>
<div id="heic_done">
    <button id="tab2" type="button" class="btn btn-secondary">
        Return to Editor</button>
</div>
<hr />
 <div id="heic_upld">
    <span class="userupld">OR ... Drag HEIC Photos here:</span>
    <div id="preload">
        <p id="ldg">Processing images&hellip;Please wait</p>
        <img id="convgif" src="../images/loader-64x/Preloader_4.gif"
            alt="Loading image" />
    </div>
    <br /><br /><br />
</div>
<hr />
<!-- Preview the converted images -->
<div id="previews">
    <div id="phdrs">
        <p>Preview area: Click link  to delete an upload</p>
    </div>
</div>

<script
    src="https://cdn.jsdelivr.net/gh/exif-heic-js/exif-heic-js/exif-heic.min.js">
</script>
<script src="./heic2any.min.js"></script>
<script src="./heic_converter.js"></script>
</body>

</html>