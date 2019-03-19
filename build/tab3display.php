<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>
<p>Tips Text: </p>
<textarea id="ttxt" name="tips" rows="10" cols="130"
    placeholder="Add any special notes about travel, or the hike, here"><?php
    if (!empty($tips)) {
            echo $tips;
    }?></textarea><br />
<p>Hike Information:</p>
<textarea id="info" name="hinfo" rows="16" 
        cols="130"><?php echo $info;?></textarea>
<input type="hidden" name="dno" value="<?= $hikeNo;?>" />
<input type="hidden" name="did" value="<?= $usr;?>" />
