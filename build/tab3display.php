<?php
/**
 * This is the html for tab3 in the editor 
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No licenxse to date
 */
?>
<p class="up" style="color:darkblue;font-size:16px;">
        All inputs limited to 4096 Characters</p>
<p>Tips Text: </p>
<textarea id="ttxt" name="tips" rows="10" cols="130" maxlength="4096"
    placeholder="Add any special notes about travel, or the hike, here"><?php
    if (!empty($tips)) {
            echo $tips;
    }?></textarea><br />
<p>Hike Information:</p>
<textarea id="info" name="hinfo" rows="16"  maxlength="4096" 
        cols="130"><?= $info;?></textarea>
<input type="hidden" name="dno" value="<?= $hikeNo;?>" />
