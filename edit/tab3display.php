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
<p><strong>Tips Text:</strong></p>
<textarea id="ttxt" class="wysiwyg" name="tips" rows="14" cols="130" maxlength="4096"
    placeholder="Add any special notes about travel, or the hike, here"><?php
    if (!empty($tips)) {
            echo $tips;
    }?></textarea><br />
<p><strong>Hike Information:</strong></p>
<textarea id="info" class="wysiwyg" name="hinfo" rows="44"  maxlength="4096" 
        cols="130"
        placeholder="Enter hike description here"><?= $info;?></textarea>
<input type="hidden" name="dno" value="<?= $hikeNo;?>" />
<br /><br />