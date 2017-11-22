<?php
/* This module produces the html for placing photos with selection boxes.
 * REQUIREMENTS:
 *      1. $hikeNo must be defined in caller's environment (EHIKES or HIKES)
 *      2. $ptable must also be defined in caller's environment (ETSV or TSV)
 *      3. $pgType must be defined by caller to determine whether or not
 *         to display captions for editing
 *      4. picPops.js is looking for a <p id="ptype"> on the caller's page
 *         identifying the page type: Validation, Finish or Edit
 * Place this code inside a <div> element.
 */
require_once "../mysql/setenv.php";
?>
<style type="text/css">
    .capLine { margin: 0px;
    font-weight: bold;
    background-color: #eaeaea; }
</style>
<h4 style="text-indent:16px">Please check the boxes corresponding to
    the pictures you wish to include on the hike page, and those you wish to
    include on the geomap.
</h4>
<div style="position:relative;top:-14px;margin-left:16px;">
    <input id="all" type="checkbox" name="allPix" value="useAll" />&nbsp;
        Use All Photos on Hike Page<br />
    <input id="mall" type="checkbox" name="allMap" value="mapAll" />&nbsp;
        Use All Photos on Map
</div>
<div style="margin-left:16px;">
<?php
$picreq = "SELECT * FROM {$ptable} WHERE indxNo = {$hikeNo};";
$pix = mysqli_query($link,$picreq);
if (!$pix) {
    die("photoSelect.php: Failed to get picdat from ETSV for hike {$hikeNo}: " .
        mysqli_error($link));
}
if (mysqli_num_rows($pix) === 0) {
    $inclPix = 'NO';
} else {
    # Display photos for selection
    $inclPix = 'YES';
    $picno = 0;
    $phNames = []; # filename w/o extension
    $phDescs = []; # caption
    $phPics = []; # capture the link for the mid-size version of the photo
    $phWds = []; # width
    $rowHt = 220; # nominal choice for row height in div
    while ($pics = mysqli_fetch_assoc($pix)) {
        $phNames[$picno] = $pics['title'];
        $phDescs[$picno] = $pics['desc'];
        $phPics[$picno] = $pics['mid'];
        $pHeight = $pics['imgHt'];
        $aspect = $rowHt/$pHeight;
        $pWidth = $pics['imgWd'];
        $phWds[$picno] = floor($aspect * $pWidth);
        $picno += 1;
    }
    for ($i=0; $i<$picno; $i++) {
        echo '<div class="selPic" style="width:' . $phWds[$i] . 'px;float:left;'
            . 'margin-left:2px;margin-right:2px;">';
        echo '<input class="hpguse" type="checkbox" name="pix[]" value="'
            . $phNames[$i] . '" />Display&nbsp;&nbsp;';
        echo '<input class="mpguse" type="checkbox" name="mapit[]" value="' 
            . $phNames[$i] . '" />Map<br />' . PHP_EOL;
        echo '<img class="allPhotos" height="200px" width="' . $phWds[$i]
                . 'px" src="' . $phPics[$i] . '" alt="' . $phNames[$i] 
                . '" /><br />' . PHP_EOL;
        if ($pgType === 'Edit') {
            $tawd = $phWds[$i] - 12;  # textarea widths don't compute exactly
            echo '<textarea style="width:' . $tawd . 'px" name="ecap[]">' . 
                $phDescs[$i] . "</textarea>";
        }
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
    if ($pgType == 'Validate' || $pgType == 'Finish') {
        echo '<div style="width:200px;position:relative;top:90px;left:20px;float:left;">' .
        '<input type="submit" value="Create Page w/This Data" /><br /><br />' . "\n";
    }
}
?>
</div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script type="text/javascript">
    var phTitles = <?php echo $jsTitles;?>;
    var phDescs = <?php echo $jsDescs;?>;
</script>
<script src="photoSelect.js" type="text/javascript"></script>
<script src="../scripts/picPops.js" type="text/javascript"></script>
<div class="popupCap"></div>
<?php 
    echo '<input type="hidden" name="usepics" value="' . $inclPix . '" />' . "\n";
    echo '<input type="hidden" name="hikeno" value="' . $hikeNo . '" />' . "\n";