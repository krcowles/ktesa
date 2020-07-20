<?php
/**
 * This is the tab for users to add/delete photos with captions,
 * as well as to add/edit waypoints.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
?>
<span><strong>Manage Your Photos Below</strong><a class="like-button"
    href="#wloc">Manage Waypoints</a></span>
<hr />
<p id="ehno" style="display:none;"><?= $hikeNo;?></p>
<p id="eusr" style="display:none;"><?= $usr;?></p>

<form class="box" action="saveTab2.php" method="POST">
<span id="userupld">Add Photos using drag-and-drop onto the page, or select:</span>
<span class="box__input">
    <input type="file" name="files[]" id="file" class="inputfile"
        data-multiple-caption="&nbsp;&nbsp;{count} files selected" multiple />
    <label for="file">
        <span>&nbsp;&nbsp;Choose one or more photos&hellip;</span>
    </label>
</span><br />
<p id="ldg">Processing images&hellip;Please wait
</p><div id="preload"><img src="../images/loader-64x/Preloader_4.gif"
    alt="Loading image" /></div>
<p>
    <em>Edit captions below each photo as needed and assign display options.</em>
</p>
<input type="hidden" name="hikeNo" value="<?= $hikeNo;?>" />
<input type="hidden" name="usr" value="<?= $usr;?>" />

<div id="d2">
    <input id="ap2" type="submit" name="savePg" value="Apply" />
</div>
<?php if ($inclPix === 'YES') : ?>
<style type="text/css">
    .capLine {
    margin: 0px;
    font-weight: bold;
    background-color: #dadada; }
</style>
<h4>Please check the boxes corresponding to the pictures you wish to
        include on the hike page, and those you wish to include on the geomap.
    </h4><br />
<div style="position:relative;top:-14px;margin-left:16px;">
    <input id="all" type="checkbox" name="allPix" value="useAll" />&nbsp;
        Use All Photos on Hike Page<br />
    <input id="mall" type="checkbox" name="allMap" value="mapAll" />&nbsp;
        Use All Photos on Map
</div>
<div style="margin-left:16px;">
    <div class="flex-box">
        <?= $html;?>
    </div>
<?php else : ?>
    <div class="flex-box"></div>
    <p id="nophotos">There are no photos to edit<p>
<?php endif; ?>

<input type="hidden" name="track" value="<?= $curr_gpx;?>" />

<hr id="wloc" />
<?= $wptedits;?>

</form>

<script type="text/javascript">
    var phTitles = <?php echo $jsTitles;?>;
    var phDescs = <?php echo $jsDescs;?>;
    var phMaps = <?php echo $jsMaps;?>;
</script>
<script src="photoSelect.js" type="text/javascript"></script>
<script src="../scripts/picPops.js" type="text/javascript"></script>
<div class="popupCap"></div>
<input type="hidden" name="usepics" value="<?= $inclPix;?>" />
<input type="hidden" name="hikeno" value="<?= $hikeNo;?>" />
