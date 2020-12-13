<div id="d4">
    <input id="ap4" type="submit" name="savePg" value="Apply" />
</div>
<h3 class="up">Hike Reference Sources: (NOTE: Book type cannot be 
    changed - if needed, delete and add a new one)</h3>
<input type="hidden" name="hikeNo" value="<?= $hikeNo;?>" />
<script type=text/javascript>
    var titles = <?= $titles;?>;
    var authors = <?= $authors;?>;
</script>
<!-- Pre-populated References; SESSION is for 'bad url' during save -->
<p id="refcnt" style="display:none"><?= $noOfRefs;?></p>
<?php if (isset($_SESSION['riturl']) && $_SESSION['riturl'] !== '') {
    echo '<p style="color:brown;">' . $_SESSION['riturl'] . '</p>';
    $_SESSION['riturl'] = '';
} ?>
<?php for ($k=0; $k<$noOfRefs; $k++) : ?>
<p id="rtype<?= $k;?>" style="display:none"><?= $rtypes[$k];?></p>
<p id="rit1<?= $k;?>" style="display:none"><?= $rit1s[$k];?></p>
<p id="rit2<?= $k;?>" style="display:none"><?= $rit2s[$k];?></p>
<select id="sel<?= $k;?>" style="height:30px;width:150px;" name="drtype[]">
    <option value="Book:" >Book</option>
    <option value="Photo Essay:">Photo Essay</option>
    <option value="Website:">Website</option>
    <option value="App:">App</option>
    <option value="Downloadable Doc:">Downloadable Doc</option>
    <option value="Blog:">Blog</option>
    <option value="On-line Map:">On-line Map</option>
    <option value="Magazine:">Magazine</option>
    <option value="News Article:">News Article</option>
    <option value="Meetup Group:">Meetup Group</option>
    <option value="Related Link:">Related Link</option>
    <option value="Text:">Text Only - No Link</option>
</select>&nbsp;&nbsp;&nbsp;
    <?php if (trim($rtypes[$k]) === 'Book:' 
    || trim($rtypes[$k]) === 'Photo Essay:'
    ) : ?>
        <select id="bkname<?= $k;?>"
            style="height:32px;width:362px;top:-7px;"
            name="drit1[]"><?= $bkopts;?>
        </select>&nbsp;&nbsp;&nbsp; 
        <input  id="auth<?= $k;?>"
            style="height:24px;width:282px;font-size:14px;margin-left:2px;"
            type="text" name="drit2[]" class="upbox" />&nbsp;&nbsp;
        <label>Delete: </label>
        <input style="height:18px;width:18px;" type="checkbox" name="delref[]" 
            value="<?= $k;?>"><br />
    <?php else : ?>
        <input id="url<?= $k;?>" style="height:24px;width:352px;font-size:14px;"
            class="upbox urlbox" name="drit1[]" value="<?= $rit1s[$k];?>" />
            &nbsp;&nbsp;&nbsp;
        <input id="txt<?= $k;?>" style="height:24px;width:280px;font-size:14px;"
            class="upbox" name="drit2[]" value="<?= $rit2s[$k];?>" />&nbsp;&nbsp;
        <label>Delete: </label>
        <input style="height:18px;width:18px;" type="checkbox" name="delref[]"
            value="<?= $k;?>" /><br />
    <?php endif; ?>
<?php endfor; ?>
<!-- Unpopulated References -->
<p><em style="font-weight:bold;">Add</em> references here:</p>
<p>Select the type of reference and its accompanying data below:</p>
<?php for($j=0; $j<4; $j++) : ?>
<select id="href<?= $j;?>" style="height:30px;width:150px;" name="rtype[]">
    <option value="Book:" selected="selected">Book</option>
    <option value="Photo Essay:">Photo Essay</option>
    <option value="Website:">Website</option>
    <option value="App:">App</option>
    <option value="Downloadable Doc:">Downloadable Doc</option>
    <option value="Blog:">Blog</option>
    <option value="On-line Map:">On-line Map</option>
    <option value="Magazine:">Magazine</option>
    <option value="News Article:">News Article</option>
    <option value="Meetup Group:">Meetup Group</option>
    <option value="Related Link:">Related Link</option>
    <option value="Text:">Text Only - No Link</option>
</select>&nbsp;&nbsp;&nbsp;

<span id="bk<?= $j;?>">
<input id="usebk<?= $j;?>" type="hidden" name="usebks[]" value="yes" />

<select style="height:32px;width:360px;" id="bkttl<?= $j;?>"
    name="brit1<?= $j;?>"><?= $bkopts;?>
</select>&nbsp;&nbsp;&nbsp;
<input style="height:26px;width:280px;font-size:14px;" class="bkauths"
    type="text"
    id="bkauth<?= $j;?>" name="brit2<?= $j;?>" value="" />
</span>
<!-- Invisible unless other than book type is selected: -->
<span style="display:none;" id="nbk<?= $j;?>">
<input id="notbk<?= $j;?>" type="hidden" name="notbks[]" value="no" />

<input style="height:28px;width:352px;font-size:14px;" type="text" name="orit1<?= $j;?>"
    id="nr1<?= $j;?>" class="upbox" value="" />&nbsp;&nbsp;&nbsp;
<input style="height:28px;width:282px;font-size:14px;" type="text" name="orit2<?= $j;?>"
    id="nr2<?= $j;?>" class="upbox" value="" /></span><br />
<?php endfor; ?>

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
