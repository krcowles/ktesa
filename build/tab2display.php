<hr />
<p style="color:brown;">Upload your photos (you will be directed to a new page)</p> 
<input type="button" name="upld" id="upld" value="Upload" /></h3>
<hr />
<p style="color:brown;"><em>Edit captions below each photo as needed
    and assign display options.</em></p>
<input type="hidden" name="pno" value="<?php echo $hikeNo;?>" />
<input type="hidden" name="pid" value="<?php echo $uid;?>" />
<div style="margin-left:8px;">
        <input type="submit" name="savePg" value="Apply" /></p>
</div>
<?php
    require "photoSelect.php";
?>
<?php if ($wayPointCount > 0) : ?>
    <hr />
    <p style="color:brown;">The following waypoints are available for edit</p>
    <?php for ($i=0; $i<$wayPointCount; $i++) : ?>
        <div id="wpts">
            <input type="hidden" name="wids[]" value="<?= $wids[$i];?>" />
            <p id="wicn<?= $i;?>" style="display:none;"><?= $wicon[$i];?></p>
            <input type="hidden" name="wicon[]" value="<?= $wicon[$i];?>" />
            Waypoint Description (will appear as popup on mouseover)
            <textarea class="tstyle2" name="wdes[]"><?= $wdes[$i];?></textarea>
            <br /><br />
            Waypoint icon:
            <select id="selicon<?= $i;?>" name="wsym[]">
                <option value="googlemini">[Default] Google</option>
                <option value="Flag, Red">Red Flag</option>
                <option value="Flag, Blue">Blue Flag</option>
                <option value="Flag, Green">Green Flag</option>
                <option value="Trail Head">Hiker</option>
                <option value="Triangle, Red">Red Triangle</option>
            </select>&nbsp;&nbsp;
            Waypoint Latitude:
            <textarea class="tstyle1 coords"
                name="wlat[]"><?= $waylat[$i];?></textarea>
            &nbsp;&nbsp;Longitude:
            <textarea class="tstyle1 coords"
                name="wlng[]"><?=$waylng[$i];?></textarea>
            <br /><br />
        </div>
    <?php endfor; ?>
<?php endif; ?>
