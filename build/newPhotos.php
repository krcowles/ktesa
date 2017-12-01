<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Edit Database</title>
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
<?php
$hikeNo = filter_input(INPUT_POST,'nno');
$usr = filter_input(INPUT_POST,'nid');
?>
<form action="addNewPhotos.php" method="POST">
<input type="hidden" name="xno" value="<?php echo $hikeNo;?>" />
<input type="hidden" name="xid" value="<?php echo $usr;?>" />
<div style="position:relative;top:-14px;">
    <input id="addall" type="checkbox" name="allPix" value="useAll" />&nbsp;
        Add all photos to Photo Editor<br />
</div>
<?php
require_once '../mysql/setenv.php';
$incl = $_POST['ps'];
$curlids = [];
$albums = [];
$j = 0;
/* This routine differs slightly from validateHIke in that the photo album
 * links must be checked (checkboxes) in order to upload (user may not want
 * to re-upload original album, for example).
 */
foreach ($incl as $newalb) {
    $alnk = 'lnk' . $newalb;
    $atype = 'alb' . $newalb;
    $curlids[$j] = filter_input(INPUT_POST,$alnk);
    $albums[$j] = filter_input(INPUT_POST,$atype);
    $j++;
}
$caller = "newPhotos";
include 'getPicDat.php';
# all photos are now in picdat[], time sorted
$picno = 0;
$phNames = []; # filename w/o extension
$phDescs = []; # caption
$hpg = [];
$mpg = [];
$phPics = []; # capture the link for the mid-size version of the photo
$phWds = []; # width
$rowHt = 220; # nominal choice for row height in div
foreach ($picdat as $pics) {
    $phNames[$picno] = $pics['pic'];
    $phDescs[$picno] = $pics['desc'];
    $hpg[$picno] = 'N';
    $mpg[$picno] = 'N';
    $phPics[$picno] = $pics['nsize'];
    $pHeight = $pics['pHt'];
    $aspect = $rowHt/$pHeight;
    $pWidth = $pics['pWd'];
    $phWds[$picno] = floor($aspect * $pWidth);
    $picno += 1;
}
for ($i=0; $i<$picno; $i++) {
    echo '<div style="width:' . $phWds[$i] . 'px;margin-left:2px;'
        . 'margin-right:2px;display:inline-block">';
    echo '<input class="ckbox" type="checkbox" name="incl[]" value="' . $phNames[$i] .'" />';
    echo "&nbsp;&nbsp;Add it";
    echo '<img class="allPhotos" height="' . $rowHt . 'px" width="' . 
        $phWds[$i] . 'px" src="' . $phPics[$i] . '" alt="' . $phNames[$i] . 
        '" /><br />' . PHP_EOL;
    echo "</div>" . PHP_EOL;
}
# create the js arrays to be passed to the accompanying script:
$jsTitles = '[';
for ($n=0; $n<count($phNames); $n++) {
    if ($n === 0) {
        $jsTitles .= '"' . $phNames[0] . '"';
    } else {
        $jsTitles .= ',"' . $phNames[$n] . '"';
    }
}
$jsTitles .= ']';
$jsDescs = '[';
for ($m=0; $m<count($phDescs); $m++) {
    if ($m === 0) {
        $jsDescs .= '"' . $phDescs[0] . '"';
    } else {
        $jsDescs .= ',"' . $phDescs[$m] . '"';
    }
}
$jsDescs .= ']';
?>
<input style="font-size:18px;" type="submit" value="Add Photos" />
</form>
</div>
<div class="popupCap"></div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script type="text/javascript">
    var phTitles = <?php echo $jsTitles;?>;
    var phDescs = <?php echo $jsDescs;?>;
</script>
<script src="newPhotos.js" type="text/javascript"></script>
<script src="../scripts/picPops.js" type="text/javascript"></script>
</body>
</html>