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
<h4 class="up">Hike Reference Sources: (NOTE: Book type cannot be 
    changed - if needed, delete and add a new one)</h4>
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

<h4>GPS Data:</h4>
<p id="ua4" class="user_alert" style="display:none;"><?=$user_alert;?></p>
<h4>File Upload for 'Related Hike Information' (types .gpx, .kml, .html):</h4>
<p>Note: These files are generally useful for proposed hike track data
and/or maps</p>
<?php if (isset($_SESSION['gpsmsg']) && $_SESSION['gpsmsg'] !== '') : ?>
    <p style="font-size:18px;color:darkblue;">The following action has resulted 
        from your latest "APPLY": <?= $_SESSION['gpsmsg'];?></p>
    <?php $_SESSION['gpsmsg'] = ''; ?>
<?php endif; ?>
<span style="font-weight:bold;margin-bottom:0px;color:black;">
    Upload New Data File:</span><br />
<table>
    <tbody>
        <tr>
            <td class="italic">GPS Track Uploads:</td>
        </tr>
        <tr>
            <td><label style="color:brown;padding-right:6px;">Upload New File&nbsp;
                (Accepted file types: gpx, kml)</label></td>
            <td><input type="file" name="newgps" /></td>
            <td><textarea id="ctgpx" name="glnktxt" placeholder=
                "Enter text to use for the link to this file"></textarea></td>
        </tr>
        <tr>
            <td class="italic">HTML Map Uploads:</td>
        </tr>
        <tr>
            <td><label style="color:brown;padding-right:6px;">Upload New MAP
                File&nbsp;(Accepted file type: html)</label></td>
            <td><input type="file" name="newmap" /></td>
            <td><textarea id="cthtm" name="hlnktxt" placeholder=
                "Enter text to use for the link to this file"></textarea></td>
        </tr>
    </tbody>
</table>


<!-- Pre-populated GPS Data -->
<?php if ($displayGps) : ?>
    <strong>The following items have already been uploaded and created:</strong>
    <br />[You may modify the Link text below]<br />
    <?php foreach ($disp_data as $fname => $data) : ?>
        Link text: <textarea class="tstyle2 ctrshift"
            name="clickText[]"><?=$data['clickText'];?></textarea>&nbsp;&nbsp;
        For uploaded file:&nbsp;&nbsp;<em><?=$fname;?></em>&nbsp;&nbsp;
        <label>Delete This Reference ? </label>&nbsp;&nbsp;
        <input style="height:18px;width:18px;" type="checkbox"
            name="delgps[]" value="<?=$data['datId'];?>" /><br />
    <?php endforeach; ?>
<?php endif; ?>
