<hr />
<h3>Upload your photos directly! 
<input type="button" name="upld" id="upld" value="Go to Upload Pg" /></h3>
<hr />
<p style="color:brown;"><em>Edit captions below each photo as needed
    and assign display options.</em></p>
<input type="hidden" name="pno" value="<?php echo $hikeNo;?>" />
<input type="hidden" name="pid" value="<?php echo $uid;?>" />
<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the
        Photo Assignments Below&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>
<?php
    require "photoSelect.php";
?>
