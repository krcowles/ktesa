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

<div id="main" style="padding:16px;">
<form action="addNewPhotos.php" method="POST">
<input type="hidden" name="xno" value="<?= $hikeNo;?>" />
<input type="hidden" name="xid" value="<?= $usr;?>" />
<div style="position:relative;top:-14px;">
    <input id="addall" type="checkbox" name="allPix" value="useAll" />&nbsp;
        Add all photos to Photo Editor<br />
</div>
<?php
require "uploadPhotos.php";
?>
<?php for ($i=0; $i<$picno; $i++) : ?>
<div style="width:<?= $phWds[$i];?>px;margin-left:2px;
    margin-right:2px;display:inline-block">
<input class="ckbox" type="checkbox" name="incl[]"
    value="<?= $phNames[$i];?>" />&nbsp;&nbsp;Add it
<img class="allPhotos" height="<?= $rowHt;?>px" 
    width="<?= $phWds[$i];?>px" src="<?= $phPics[$i];?>" 
    alt="<?= $phNames[$i];?>" /><br />
</div>
<?php endfor; ?>
<input style="font-size:18px;" type="submit" value="Add Photos" />
</form>
</div>
<div class="popupCap"></div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script type="text/javascript">
    var phTitles = <?= $jsTitles;?>;
    var phDescs = <?= $jsDescs;?>;
</script>
<script src="newPhotos.js" type="text/javascript"></script>
<script src="../scripts/picPops.js" type="text/javascript"></script>
</body>
</html>
