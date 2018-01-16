<p>Tips Text: </p>
<textarea id="ttxt" name="tips" rows="10" cols="130"
    placeholder="Add any special notes about travel, or the hike here"><?php
if ($hikeTips !== '') {
    echo $hikeTips;
}?></textarea><br />
<p>Hike Information:</p>
<textarea id="info" name="hinfo" rows="16" 
        cols="130"><?php echo $hikeDetails;?></textarea>
<input type="hidden" name="dno" value="<?php echo $hikeNo;?>" />
<input type="hidden" name="did" value="<?php echo $uid;?>" />
<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>