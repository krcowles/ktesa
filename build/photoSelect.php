<?php
/**
 * This module produces the html for placing photos with selection boxes.
 * REQUIREMENTS:
 *      1. $hikeNo must be defined in caller's environment (EHIKES or HIKES)
 *      2. picPops.js is looking for a <p id="ptype"> on the caller's page
 *         identifying the page type: Edit (This may 
 *         be related to Flickr uploads; not currently required)
 * Place this code inside a <div> element.
 * PHP Version 7.1
 * 
 * @package Photos
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$h4txt = "Please check the boxes corresponding to the pictures you wish to " .
    "include on the hike page, and those you wish to include on the geomap.";
$picreq = "SELECT * FROM ETSV WHERE indxNo = :hikeno;";
$picq = $pdo->prepare($picreq);
$picq->execute(["hikeno" => $hikeNo]);
if ($picq->rowCount() === 0) {
    $inclPix = 'NO';
    $jsTitles = "''";
    $jsDescs = "''";
} else {
    $inclPix = 'YES';
}
?>
<?php if ($inclPix === 'YES') : ?>
<style type="text/css">
    .capLine { margin: 0px;
    font-weight: bold;
    background-color: #dadada; }
</style>
<h4 style="text-indent:16px"><?= $h4txt;?></h4>
    <div style="position:relative;top:-14px;margin-left:16px;">
        <input id="all" type="checkbox" name="allPix" value="useAll" />&nbsp;
            Use All Photos on Hike Page<br />
        <input id="mall" type="checkbox" name="allMap" value="mapAll" />&nbsp;
            Use All Photos on Map
    </div>
    <div style="margin-left:16px;">
<?php endif; ?>
<?php
if ($inclPix === 'YES') {
    /**
     * The location of the 'pictures' directory is needed in order to 
     * specify <img> src attribute. The issue is that the src attribute
     * can only have a relative path or absolute path. To provide the
     * correct relative path, the 'pictures' directory needs to be
     * located, which resides at "DOCUMENT_ROOT". Unfortunately, the 
     * $_SERVER[] for that var specifies the server's absolute path,
     * e.g. on the MacOS, the DOCUMENT_ROOT includes "/Users/... etc."
     * This would look like a relative path to the img tag, having a
     * location of "root"/Users/..., which doesn't exist. Therefore, it
     * is necessary to extract the correct relative path to the pictures
     * directory from wherever this code is invoked. 
     */
    $picpath = "";
    // iteratively look for the pictures directory from here, and form
    // the appropriate path:
    $current = getcwd();
    while (!in_array('pictures', scandir($current))) {
        $picpath .= "../";
        chdir('..');
        $current = getcwd();
    }
    $picpath .= "pictures/nsize/";
    $picno = 0;
    $phNames = []; // filename w/o extension
    $phDescs = []; // caption
    $hpg = [];
    $mpg = [];
    $phPics = []; // capture the link for the mid-size version of the photo
    $phWds = []; // width
    $rowHt = 220; // nominal choice for row height in div
    $maxOccupy = 940;
    while ($pics = $picq->fetch(PDO::FETCH_ASSOC)) {
        $phNames[$picno] = $pics['title'];
        $phDescs[$picno] = $pics['desc'];
        $hpg[$picno] = $pics['hpg'];
        $mpg[$picno] = $pics['mpg'];
        if ($pics['mid']) {  // Picture
            $phPics[$picno] = $pics['mid'] . "_" . $pics['thumb'];
            $pHeight = $pics['imgHt'];
            $aspect = $rowHt/$pHeight;
            $pWidth = $pics['imgWd'];
            $phWds[$picno] = floor($aspect * $pWidth);
        }
        else {  // Waypoint
            $phPics[$picno] = "waypoint";
            $pHeight = 220;
            $aspect = $rowHt/$pHeight;
            $pWidth = 220;
            $phWds[$picno] = floor($aspect * $pWidth);
        }
        $picno += 1;
    }
    for ($i=0; $i<$picno; $i++) {
        if ($phWds[$i] > $maxOccupy) {
            $aspect = $rowHt/$phWds[$i];
            $newht = floor($maxOccupy * $aspect);
            echo '<div style="width:' . $maxOccupy . 'px;margin-left:2px;'
                . 'margin-right:2px;display:inline-block;">';
        } else {
            echo '<div style="width:' . $phWds[$i] . 'px;margin-left:2px;'
                . 'margin-right:2px;display:inline-block;">';
        }
        $pgbox = '<input class="hpguse" type="checkbox" name="pix[]" value="'
            . $phNames[$i];
        if ($hpg[$i] === 'Y') {
            $pgbox .= '" checked />Page&nbsp;&nbsp;';
        } else {
            $pgbox .= '" />Page&nbsp;&nbsp;';
        }
        echo $pgbox;
        $mpbox = '<input class="mpguse" type="checkbox" name="mapit[]" value="'
            . $phNames[$i];
        if ($mpg[$i] === 'Y') {
            $mpbox .= '" checked />Map<br />' . PHP_EOL;
        } else {
            $mpbox .= '" />Map<br />' . PHP_EOL;
        }
        echo $mpbox;
        echo '<input class="delp" type="checkbox" name="rem[]" value="'
            . $phNames[$i] . '" />Delete<br />';
        if ($phWds[$i] > $maxOccupy) {
            echo '<img class="allPhotos" height="' . $newht . 'px" width="' 
            . $maxOccupy . 'px" src="' . $picpath . $phPics[$i] . "_n.jpg"
            . '" alt="' . $phNames[$i] . '" /><br />' . PHP_EOL;
        } else {
            echo '<img class="allPhotos" height="200px" width="' . $phWds[$i]
                . 'px" src="' . $picpath . $phPics[$i] . "_n.jpg"
                . '" alt="' . $phNames[$i]
                . '" /><br />' . PHP_EOL;
        }
        if ($phWds[$i] > $maxOccupy) {
            $tawd = $maxOccupy - 12; // textarea widths don't compute exactly
        } else {
            $tawd = $phWds[$i] - 12; 
        } 
        echo '<textarea style="width:' . $tawd . 'px" name="ecap[]">' .
            $phDescs[$i] . "</textarea>";
        echo "</div>" . PHP_EOL;
    }
    // create the js arrays to be passed to the accompanying script:
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
    echo '</div>';
}
?>
<script type="text/javascript">
    var phTitles = <?php echo $jsTitles;?>;
    var phDescs = <?php echo $jsDescs;?>;
</script>
<script src="photoSelect.js" type="text/javascript"></script>
<script src="../scripts/picPops.js" type="text/javascript"></script>
<div class="popupCap"></div>
<input type="hidden" name="usepics" value="<?= $inclPix;?>" />
<input type="hidden" name="hikeno" value="<?= $hikeNo;?>" />
