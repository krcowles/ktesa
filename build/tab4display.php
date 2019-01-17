<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
    <input type="submit" name="savePg" value="Apply" /></p>
</div>	
<h3>Hike Reference Sources: (NOTE: Book type cannot be changed - if needed,
    delete and add a new one)</h3>
<input type="hidden" name="rno" value="<?= $hikeNo;?>" />
<input type="hidden" name="rid" value="<?= $uid;?>" />
<script type=text/javascript>
    var titles = <?= $titles;?>;
    var authors = <?= $authors;?>;
</script>
<!-- Pre-populated References -->
<p id="refcnt" style="display:none"><?= $noOfRefs;?></p>
<?php for ($k=0; $k<$noOfRefs; $k++) : ?>
<p id="rid<?= $k;?>" style="display:none"><?= $rtypes[$k];?></p>
<p id="r1<?= $k;?>" style="display:none"><?= $rit1s[$k];?></p>
<p id="r2<?= $k;?>" style="display:none"><?= $rit2s[$k];?></p>
<select id="ref<?= $k;?>" style="height:26px;width:150px;" name="drtype[]">
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
    <?php if ($rtypes[$k] === 'Book:' || $rtypes[$k] === 'Photo Essay:') : ?>
<select style="height:26px;width:360px;" id="rttl<?= $k;?>"
    name="drit1[]"><?= $bkopts;?>
</select>&nbsp;&nbsp;&nbsp; 
<input style="height:24px;width:282px;" type="text" name="drit2[]"
    id="rr2<?= $k;?>" class="upbox" />&nbsp;&nbsp;
<label>Delete: </label>
<input style="height:18px;width:18px;" type="checkbox" name="delref[]" 
    value="<?= $k;?>"><br />
<?php else : ?>
<input style="height:24px;width:352px;" class="upbox"
    name="drit1[]" value="<?= $rit1s[$k];?>" />&nbsp;&nbsp;&nbsp;
<input style="height:24px;width:280px;" class="upbox" id="tr<?= $k;?>"
    name="drit2[]" value="<?= $rit2s[$k];?>" />&nbsp;&nbsp;
<label>Delete: </label>
<input style="height:18px;width:18px;" type="checkbox" name="delref[]"
    value="<?= $k;?>" /><br />
<?php endif; ?>
<?php endfor; ?>
<!-- Unpopulated References -->
<p><em style="font-weight:bold;">Add</em> references here:</p>
<p>Select the type of reference and its accompanying data below:</p>
<?php for($j=0; $j<4; $j++) : ?>
<select id="href<?= $j;?>" style="height:26px;width:150px;" name="rtype[]">
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

<select style="height:26px;width:360px;" id="bkttl<?= $j;?>"
    name="brit1<?= $j;?>"><?= $bkopts;?>
</select>&nbsp;&nbsp;&nbsp;
<input style="height:24px;width:280px;" class="bkauths" type="text"
    id="bkauth<?= $j;?>" name="brit2<?= $j;?>" value="" /></span>
<!-- Invisible unless other than book type is selected: -->
<span style="display:none;" id="nbk<?= $j;?>">
<input id="notbk<?= $j;?>" type="hidden" name="notbks[]" value="no" />

<input style="height:24px;width:352px;" type="text" name="orit1<?= $j;?>"
    id="nr1<?= $j;?>" class="upbox" value="" />&nbsp;&nbsp;&nbsp;
<input style="height:24px;width:282px;" type="text" name="orit2<?= $j;?>"
    id="nr2<?= $j;?>" class="upbox" value="" /></span><br />
<?php endfor; ?>

<h3>GPS Data:</h3>
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
    - Note: An Editable Reference Will Automatically Be Added Below After
    'APPLY' Is Performed</em></span><br />
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
    Label: <textarea class="tstyle1" name="labl[]"><?= $pl[$n];?></textarea>
    &nbsp;&nbsp;Url: <textarea class="tstyle2" 
        name="lnk[]"><?= $pu[$n];?></textarea>
    &nbsp;&nbsp;Click-on text: <textarea class="tstyle3" 
        name="ctxt[]"><?= $pc[$n];?></textarea>&nbsp;&nbsp;
    <label>Delete: </label>
    <input style="height:18px;width:18px;" type="checkbox"
        name="delgps[]" value="<?= $n;?>$x"><br /><br />
<?php endfor; ?>
<!-- Unpopulated Data -->
<p><em style="color:brown;font-weight:bold;">Add</em> GPS Data:</p>
<label>Label: </label><input class="tstyle1" name="labl[]" size="30" />&nbsp;&nbsp;
<label>Url: </label><input class="tstyle2" name="lnk[]" size="55" />
<label style="text-indent:30px">Click-on text: </label><input
    class="tstyle3" name="ctxt[]" size="30" /><br />
<label>Label: </label><input class="tstyle1" name="labl[]" size="30" />&nbsp;&nbsp;
<label>Url: </label><input class="tstyle2" name="lnk[]" size="55" />
<label style="text-indent:30px">Click-on text: </label><input
    class="tstyle3" name="ctxt[]" size="30" />
