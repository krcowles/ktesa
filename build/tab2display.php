<h3>You may wish to upload more photos to add to your page. The currently
    saved album links are displayed below. You may re-select a currently
    saved link in order to update your photo list, and/or you may add up to two
    more links.</h3>
    <input type="hidden" name="nno" value="<?php echo $hikeNo;?>" />
    <input type="hidden" name="nid" value="<?php echo $uid;?>" />
<?php
if ($hikeUrl1 !== '') {
    echo '<input type="checkbox" name="ps[]" value="1" />&nbsp;';
    echo "Include in upload:&nbsp;&nbsp;";
    echo '<input style="border-color:black;color:blue;font-weight:bold;" ' .
        'class="phurl" type="text" name="lnk1" value="' . $hikeUrl1 .
        '" />&nbsp;&nbsp;';
    echo 'Type:&nbsp;&nbsp;<select class="albs" id="alb1" name="alb1">' .
        '<option value="flckr">Flickr</option>' .
        '<option value="apple">Apple iCloud</option>' .
        '<option value="googl">Google</option></select><br />';
}
if ($hikeUrl2 !== '') {
    echo '<input type="checkbox" name="ps[]" value="2" />&nbsp;';
    echo "Include in upload:&nbsp;&nbsp;";
    echo '<input style="border-color:black;color:blue;font-weight:bold;" ' .
        'class="phurl" type="text" name="lnk2" value="' . $hikeUrl2 .
        '" />&nbsp;&nbsp;';
    echo 'Type:&nbsp;&nbsp;<select class="albs" id="alb2" name="alb2">' .
        '<option value="flckr">Flickr</option>' .
        '<option value="apple">Apple iCloud</option>' .
        '<option value="googl">Google</option></select><br />';
}
?>
<input type="checkbox" name="ps[]" value="3" />&nbsp;Include new album: 
<input class="phurl" type="text" name="lnk3" value="" size="75" />&nbsp;&nbsp;
Type:&nbsp;
<select class="albs" id="alb3" name="alb3">
    <option value="flckr">Flickr</option>
    <option value="apple">Apple iCloud</option>
    <option value="googl">Google</option>
</select><br />
<input type="checkbox" name="ps[]" value="4" />&nbsp;Include new album:
<input class="phurl" type="text" name="lnk4" value="" size="75" />&nbsp;&nbsp;
Type:&nbsp;
<select class="albs" id="alb4" name="alb4">
    <option value="flckr">Flickr</option>
    <option value="apple">Apple iCloud</option>
    <option value="googl">Google</option>
</select><br /><br />
<input style="font-size:16px;width:165px;" type="submit" value="Upload Album(s)" />
    &nbsp;&nbsp;Review these album photos for possible
    inclusion on the edit page...
</form>
<p style="color:brown;"><em>Edit captions below each photo as needed. Images with no
        captions (e.g. maps, imported jpgs, etc.) are not shown.</em></p>
<form action="saveTab2.php" method="POST">
    <input type="hidden" name="pno" value="<?php echo $hikeNo;?>" />
    <input type="hidden" name="pid" value="<?php echo $uid;?>" />
<?php
    $pgType = 'Edit';
    require "photoSelect.php";
?>
<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>