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
<?php
$hikeNo = filter_input(INPUT_POST, 'nno', FILTER_VALIDATE_INT);
$usr = filter_input(INPUT_POST, 'nid');
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
$lnk1 = '';
$lnk2 = '';
$j = 0;
foreach ($incl as $newalb) {
    $alnk = 'lnk' . $newalb;
    $atype = 'alb' . $newalb;
    $curlids[$j] = filter_input(INPUT_POST, $alnk);
    if ((strlen($lnk1) + strlen($curlids[$j])) > 1023) {
        $lnk2 .= "^" . $curlids[$j];
    } else {
        $lnk1 .= "^" . $curlids[$j];
    }
    if (strlen($lnk2) > 1023) {
        echo "Exceeded field limit for compounded link...";
    }
    $albums[$j] = filter_input(INPUT_POST, $atype);
    $j++;
}
$caller = "newPhotos";
include 'getPicDat.php';
# all photos are now in picdat[], time sorted
$picno = 0;
$folders = [];
$phNames = []; # filename w/o extension, aka 'title'
$hpg = [];
$mpg = [];
$phDescs = []; # caption
$lats = [];
$lngs = [];
$thumbs = [];
$alblinks = [];
$dates = [];
$phPics = []; # capture the link for the mid-size version of the photo
$phHts = [];
$phWds = []; # width, but adjusted for row size, so table uses:
$pWds = [];
$icolors = [];
$orgs = [];
$rowHt = 220; # nominal choice for row height in div
foreach ($picdat as $pics) {
    $folders[$picno] = $pics['folder'];
    $phNames[$picno] = $pics['pic'];
    $hpg[$picno] = 'N';
    $mpg[$picno] = 'N';
    $phDescs[$picno] = $pics['desc'];
    $lats[$picno] = $pics['lat'];
    $lngs[$picno] = $pics['lng'];
    $thumbs[$picno] = $pics['thumb'];
    $alblinks[$picno] = $pics['alb'];
    $dates[$picno] = $pics['taken'];
    $phPics[$picno] = $pics['nsize'];
    $phHts[$picno] = $pics['pHt'];
    $pHeight = $pics['pHt'];
    $aspect = $rowHt/$pHeight;
    $pWds[$picno] = $pics['pWd'];
    $pWidth = $pics['pWd'];
    $phWds[$picno] = floor($aspect * $pWidth);
    $icolors[$picno] = 'red';
    $orgs[$picno] = $pics['org'];
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
/* The technique here will be to create a temporary table to store all
 * uploaded pix and then xfr those selected into ETSV on the submitted page
 */
$nodup = mysqli_query($link, "DROP TABLE IF EXISTS tmpPix");
if (!$nodup) {
    die("newPhotos.php: DROP TABLE IF EXISTS failed: " . mysqli_error($link));
}
mysqli_free_result($nodup);
$tmpReq = "CREATE TABLE tmpPix LIKE TSV;";
$tmp = mysqli_query($link, $tmpReq);
if (!$tmp) {
    die("newPhotos.php: Failed to create tmp table for photos uploaded: " .
        mysqli_error($link));
}
mysqli_free_result($tmp);
for ($j=0; $j<$picno; $j++) {
    $fl = mysqli_real_escape_string($link, $folders[$j]);
    $ti = mysqli_real_escape_string($link, $phNames[$j]);
    $ds = mysqli_real_escape_string($link, $phDescs[$j]);
    $lt = mysqli_real_escape_string($link, floatval($lats[$j]));
    $ln = mysqli_real_escape_string($link, floatval($lngs[$j]));
    $th = mysqli_real_escape_string($link, $thumbs[$j]);
    $al = mysqli_real_escape_string($link, $alblinks[$j]);
    $dt = mysqli_real_escape_string($link, $dates[$j]);
    $md = mysqli_real_escape_string($link, $phPics[$j]);
    $ih = mysqli_real_escape_string($link, intval($phHts[$j]));
    $iw = mysqli_real_escape_string($link, intval($pWds[$j]));
    $ic = mysqli_real_escape_string($link, $icolors[$j]);
    $og = mysqli_real_escape_string($link, $orgs[$j]);
    $addReq = "INSERT INTO tmpPix (indxNo,folder,title,hpg,mpg,`desc`,lat,lng," .
        "thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) VALUES ({$hikeNo}," .
        "'{$fl}','{$ti}','N','N','{$ds}',{$lt},{$ln},'{$th}','{$al}'," .
        "'{$dt}','{$md}',{$ih},{$iw},'{$ic}','{$og}');";
    $addem = mysqli_query($link, $addReq);
    if (!$addem) {
        echo "YAPPP" . mysqli_error($link);
        die("newPhotos.php: Failed to add photos to tmpPix table: " .
            msyqli_error($link));
    }
    mysqli_free_result($addem);
}
/* UPDATE THE LINKS USED;
 * Note that if there is a "^" in the entry, all links after that were appended
 * as a result of an upload. NOT IMPLEMENTED YET...
 */
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
