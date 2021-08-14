<?php
/**
 * This is the html for tab1 in the editor 
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
?>
<!-- Hidden inputs required by saveTab1.php, & non-displayed <p>'s' for editDB.js -->
<input type="hidden" name="hikeNo" value="<?=$hikeNo;?>" />
<input type="hidden" name="mtrk" value="<?=$curr_trk;?>" />
<p id="mgpx" style="display:none;"><?=$curr_gpx;?></p>
<p id="group" style="display:none;"><?=$cname;?></p>
<input type="hidden" name="cname" value="<?=$cname;?>" />
<p id="ctype" style="display:none;"><?=$logistics;?></p>
<p id="ptype" style="display:none;">Edit</p>
<p id="ua1" class="user_alert" style="display:none;"><?=$user_alert;?></p>

<!-- File upload for all gpx files to be displayed on hike page map -->
<h4 class="up">File Upload for Hike Page Map and Track: (.gpx file)</h4>
<p><span class="brown" style="font-weight:bold;"><em
    style="text-decoration:underline;">Warning:</em></span>
    If you delete the main gpx file, published hikes may be affected; you may
    simply specify a new main file to override the current settings for this hike.
</p>
<?php if (isset($_SESSION['uplmsg']) && $_SESSION['uplmsg'] !== '') : ?>
<p style="font-size:18px;color:darkblue;">The following action has resulted
    from your latest "APPLY": <?= $_SESSION['uplmsg'];?></p>
    <?php $_SESSION['uplmsg'] = ''; ?>
<?php endif; ?>
<span class="brown">Current Main Hike Track File: </span>
<?php if (empty($curr_gpx)) : ?>
    <em>None Specified</em><br />
<?php  else : ?>
    <em><?= $curr_gpx;?></em>&nbsp;&nbsp;&nbsp;&nbsp;
    <span class="brown">Check to Delete&nbsp;&nbsp;</span>
    <input type="checkbox" name="dgpx" /><br />
        <?php if (count($additional_files) > 0) : ?>
            The following files will also appear on the main hike page:
            <?=$adders;?>
        <?php endif; ?>
<?php endif; ?>
<ul>
    <li><span class="brown">Upload main/new gpx file:&nbsp;</span>
        <input id="gpxfile1" type="file" name="newgpx" /></li>
        <span>- Note: you can add up to <span id="addno">3</span>
        additional gpx file[s] to be displayed on the hike page map simultaneously
        </span>
    <li id="li1"><span class="brown">Additional track for main chart</span>
        <input id="gpxfile2" type="file" name="addgpx1" /></li>
    <li id="li2"><span class="brown">Additional track for main chart</span>
        <input id="gpxfile3" type="file" name="addgpx2" /></li>
    <li id="li3"><span class="brown">Additional track for main chart</span>
        <input id="gpxfile4" type="file" name="addgpx3" /></li>
</ul>

<!-- Begin basic data presentation -->
<h4>Data Related to This Hike:</h4>

<label for="hike">Hike Name: <span class="brown">[30 Characters Max]</span></label>
<textarea id="hike" class="ctrshift" name="pgTitle"
        maxlength="30"><?=$pgTitle;?></textarea>&nbsp;&nbsp;

<p style="display:none;" id="locality"><?= $locale;?></p>
<?php require "localeBox.html"; ?>&nbsp;&nbsp;
[ Add a location&nbsp;&nbsp;<input id="addaloc" name="addaloc"
    type="checkbox" />&nbsp;&nbsp;]<br />
<div id="newloc"><br />General Area:&nbsp;&nbsp;
    <select id="locregion" name="locregion">
        <option value="North/Northeast">North/Northeast</option>
        <option value="Northwest">Northwest</option>
        <option value="Central NM">Cetnral NM</option>
        <option value="West">West</option>
        <option value="South Central">South Central</option>
        <option value="Southwest">Southwest</option>
    </select>&nbsp;&nbsp;New Location: <input id="userloc" type="text"
        name="userloc" />
    <hr />
</div>
<br />

<label for="type">Hike Type: </label>
<select id="type" name="logistics">
    <option value="Loop">Loop</option>
    <option value="Two-Cars">Two-Cars</option>
    <option value="Out-and-back">Out-and-back</option>
</select>&nbsp;&nbsp;&nbsp;&nbsp;

<p id="dif" style="display:none"><?= $diff;?></p>
<label for="diff">Level of difficulty: </label>
<select id="diff" name="diff">
    <option value="Easy">Easy</option>
    <option value="Easy-Moderate">Easy-Moderate</option>
    <option value="Moderate">Moderate</option>
    <option value="Med-Difficult">Medium-Difficult</option>
    <option value="Difficult">Difficult</option>
</select><br /><br />

<input id="mft" type="checkbox" name="mft" />&nbsp;&nbsp;
    Calculate Miles/Feet From GPX,&nbsp;&nbsp;or Specify/Change below:<br />
<label for="miles">Round-trip length in miles:
    <span class="brown">[Number less than 50, and a max of two
        decimal places]&nbsp;</span>
</label>
<textarea id="miles" class="ctrshift" name="miles"><?=$miles;?></textarea><br />
<input type="hidden" name="usrmiles" value="NO" />

<label for="elev">Elevation change in feet:
    <span class="brown">[Integer value up to four digits]&nbsp;</span>
</label>
<textarea id="elev" class="ctrshift" name="feet" 
    maxlength="30"><?=$feet;?></textarea><br /><br />
<input type="hidden" name="usrfeet" value="NO" />

<label for="fac">Facilities at the trailhead:
    <span class="brown">[30 Characters Max]</span>
</label>
<textarea id="fac" class="ctrshift" name="fac"
    maxlength="30"><?=$fac;?></textarea><br /><br />

<label for="wow">"Wow" Appeal:
    <span class="brown">[50 Characters Max]</span>
</label>
<textarea id="wow" class="ctrshift" name="wow"
    maxlength="50"><?=$wow;?></textarea><br /><br />

<label for="seas">Best Hiking Seasons:
    <span class="brown">[12 Characters Max]</span>
</label>
<textarea id="seas" class="ctrshift" name="seasons"
    maxlength="12"><?=$seasons;?></textarea>

&nbsp;&nbsp;&nbsp;&nbsp;<p id="expo" style="display:none"><?=$expo;?></p>
<label for="sun">Exposure: </label>
<select id="sun" name="expo">
    <option value="Full sun">Full sun</option>
    <option value="Mixed sun/shade">Mixed sun/shade</option>
    <option value="Good shade">Good shade</option>
</select><br /><br />

<p>Trailhead Latitude/Longitude is set by the uploaded GPX file.
    If you wish to manually enter/edit these, click here: (again to hide) 
    <input id="showll" type="checkbox" name="latlng" value="nosend" /></p>
<p id="lldisp" style="display:none">
<label for="lat">Trailhead: Latitude </label>
<textarea id="lat" name="lat"><?=$lat;?></textarea>&nbsp;&nbsp;
<label for="lon">Longitude </label>
<textarea id="lon" name="lng"><?=$lng;?></textarea></p>

<label id="dirlbl" for="murl">Map Directions Link (Url):
    <span class="brown">[1024 Characters Max]</span>
</label>
<textarea id="murl" name="dirs" maxlength="1024"><?=$dirs;?></textarea>

<hr />
<h4 style="margin-bottom:12px;">Cluster Hike Assignments:
    (Hikes with overlapping trailheads or in close proximity)</h4>
<label for="clusters">Current Cluster:&nbsp;&nbsp;</label><?=$clusters;?>&nbsp;&nbsp;
<p id="showdel" style="display:none;">Remove the cluster
    assignment by checking here:&nbsp;
    <input id="deassign" type="checkbox" name="rmClus" value="NO" /></p>
<span id="notclus" style="display:none;">There is no currently
        assigned cluster for this hike.</span>

<div id="newcoords">
    This cluster group is not yet published; Please enter/verify the following:
    <br />
    <div id="cluscoords">
        Cluster's (NOT Hike's) latitude:
        <textarea id="cluslat" class="tstyle4 ctrshift" name="cluslat"></textarea>
        &nbsp;&nbsp;Longitude:
        <textarea id="cluslng" class="tstyle4 ctrshift" name="cluslng"></textarea>
    </div>
</div><br />

<script type="text/javascript">
    var newgrps = <?=$newgrps;?>;
</script>
