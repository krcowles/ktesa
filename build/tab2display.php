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
