<hr />
<p style="color:brown;">Upload your photos (you will be directed to a new page)</p> 
<input type="button" name="upld" id="upld" value="Upload" /></p>
<hr />
<p style="color:brown;"><em>Edit captions below each photo as needed
    and assign display options.</em></p>
<input type="hidden" name="hikeNo" value="<?= $hikeNo;?>" />
<input type="hidden" name="usr" value="<?= $usr;?>" />
<div style="margin-left:8px;">
        <input type="submit" name="savePg" value="Apply" /></p>
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
    <?= $html;?>
</div>
<?php endif; ?>

<hr />
<h3>Waypoint Editor</h3>
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