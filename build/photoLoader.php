<?php
/**
 * This is the script that will display photos which may be selected
 * for inclusion in the editor. Once in the editor, the user can
 * decide where to include the photo (e.g. hike page, hike map).
 * 
 * @package Create
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
$hikeNo = filter_input(INPUT_POST, 'nno', FILTER_VALIDATE_INT);
$usr = filter_input(INPUT_POST, 'nid');
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Upload New Photos</title>
    <meta charset="utf-8" />
    <meta name="description" content="Select new photos for editor" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="editDB.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>   
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Select New Photos For Editor</p>
<h3 style="margin-left:16px;">Please check the boxes for the photos you would
    like to include on the photo editor tab. When done, select "Add Photos"</h3>
<p style="display:none;" id="ptype">EditNew</p>
<!-- The next div is displayed only until photo loading has completed  -->
<div id="loader">
<p style="text-align:center;font-size:18px;color:darkblue">
    Please wait while images load...</p>
<img id="ldgif" src="../images/loader-64x/Preloader_6.gif" alt="Loading Image" />
</div>
<!-- This div (main) is displayed when photos are ready -->
<div id="main" style="display:none;padding:16px;">
<div style="position:relative;top:-14px;">
    <span style="color:brown;font-size:18px">When desired photos have been
        selected,</span>
    <button id="load">Add Photos</button><br /><br />
    <input id="addall" type="checkbox" name="allPix" value="useAll" />&nbsp;
    Add all photos to Photo Editor;
<?php
require 'getLinks.php';
?>
</div>
</div><br /> <!-- main -->
<div class="popupCap"></div>
<script type="text/javascript">
    var cnt = <?= $supplied;?>;
    var pgLinks = <?= $alburls;?>;
    var albTypes = <?= $albtypes;?>;
    var phTitles = [];
    var picdata;
    var hikeno = "<?= $hikeNo;?>";
    var usrid = "<?= $usr;?>";
</script>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="photoLoader.js"></script>
</body>
</html>