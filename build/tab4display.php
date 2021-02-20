<?php
/**
 * This is the html for tab4 in the editor 
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
?>
<div id="d4">
    <input id="ap4" type="submit" name="savePg" value="Apply" />
</div>
<h3 class="up">Hike Reference Sources: (NOTE: Book type cannot be 
    changed - if needed, delete and add a new one)</h3>
<input type="hidden" name="hikeNo" value="<?= $hikeNo;?>" />
<?php 
    $hikeIndexNo = $hikeNo;
    require "getRefs.php";
?>
<script type=text/javascript>
    var titles = <?=$jsonBooks;?>;
    var authors = <?=$jsonAuths;?>;
</script>
<script src="refs.js"></script>

<h3>GPS Data:</h3>
<p id="ua4" class="user_alert" style="display:none;"><?=$user_alert;?></p>
<h3>File Upload for 'Related Hike Information' (types .gpx, .kml, .html):</h3>
<p>Note: These files are generally useful for proposed hike track data
and/or maps</p>
<?php if (isset($_SESSION['gpsmsg']) && $_SESSION['gpsmsg'] !== '') : ?>
    <p style="font-size:18px;color:darkblue;">The following action has resulted 
        from your latest "APPLY": <?= $_SESSION['gpsmsg'];?></p>
    <?php $_SESSION['gpsmsg'] = ''; ?>
<?php endif; ?>
<span style="font-weight:bold;margin-bottom:0px;color:black;">
    Upload New Data File:<br />
<em style="font-weight:normal;">
    - Note: You will be able to specify the click-text after the 'Apply'
    Is Performed</em></span><br />
<ul style="margin-top:0px;" id="relgpx">
    <li>Track Data Uploads:<br />
        <label style="color:brown;">Upload New File&nbsp;(Accepted file types:
        gpx, kml)</label>&nbsp;<input type="file" name="newgps" /></li>
    <li>Map Uploads:<br />
        <label style="color:brown;">Upload New File&nbsp;(Accepted file type:
        html)</label>&nbsp;<input type="file" name="newmap" /></li>
</ul>
<!-- Pre-populated GPS Data -->
<?php for ($n=0; $n<$gpsDbCnt; $n++) : ?>
    Specify click-text here: <textarea class="tstyle2"
        name="clickText[]"><?= $clickText[$n];?></textarea>
    <input type="hidden" name="datId[]" value="<?= $datId[$n];?>" />
    &nbsp;&nbsp;
    <label>Delete Reference ? </label>
    <input style="height:18px;width:18px;" type="checkbox"
        name="delgps[]" value="<?= $datId[$n];?>" />
    &nbsp;&nbsp;For File: <span 
        style="color:brown;"><?= $fname[$n];?></span><br /><br />
<?php endfor; ?>
