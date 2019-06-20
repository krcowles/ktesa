<hr />
<p style="color:brown;">Upload your photos (you will be directed to a new page)</p> 
<input type="button" name="upld" id="upld" value="Upload" />

<hr />
<p style="color:brown;"><em>Edit captions below each photo as needed
    and assign display options.</em>&nbsp;&nbsp;&nbsp;
    <a id="wlnk" href="#wloc">Go To</a>&nbsp;Waypoint Editor
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
<h4 style="text-indent:16px"><?= $h4txt;?></h4>
    <div style="position:relative;top:-14px;margin-left:16px;">
        <input id="all" type="checkbox" name="allPix" value="useAll" />&nbsp;
            Use All Photos on Hike Page<br />
        <input id="mall" type="checkbox" name="allMap" value="mapAll" />&nbsp;
            Use All Photos on Map
    </div>
<div style="margin-left:16px;">
    <!-- </div> contained in $html -->
    <?= $html;?>
<?php endif; ?>

<input type="hidden" name="track" value="<?= $curr_gpx;?>" />
<hr />
<div id="wloc"></div>
<?= $wptedits;?>

<script type="text/javascript">
    var phTitles = <?php echo $jsTitles;?>;
    var phDescs = <?php echo $jsDescs;?>;
</script>
<script src="photoSelect.js" type="text/javascript"></script>
<script src="../scripts/picPops.js" type="text/javascript"></script>
<div class="popupCap"></div>
<input type="hidden" name="usepics" value="<?= $inclPix;?>" />
<input type="hidden" name="hikeno" value="<?= $hikeNo;?>" />