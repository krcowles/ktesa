<!-- Hidden inputs and data required by saveTab1.php & edit.js routines -->
<input type="hidden" name="hno" value="<?= $hikeNo;?>" />
<input type="hidden" name="usr" value="<?= $uid;?>" />
<input type="hidden" name="col" value="<?= $hikeColl;?>" />
<input type="hidden" name="mgpx" value="<?= $curr_gpx;?>" />
<input type="hidden" name="mtrk" value="<?= $curr_trk;?>" />
<p id="mrkr" style="display:none"><?= $hikeMarker;?></p>
<p id="greq" style="display:none"><?= $grpReq;?></p>
<input type="hidden" name="pmrkr" value="<?=$hikeMarker;?>" />
<input type="hidden" name="pclus" value="<?= $hikeClusGrp;?>" />
<p id="group" style="display:none"><?= $hikeGrpTip;?></p>
<input type="hidden" name="pcnme" value="<?= $hikeGrpTip;?>" />
<p id="ctype" style="display:none"><?= $hikeStyle;?></p>
<p id="ptype" style="display:none">Edit</p>
<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>
<!-- File upload area for main gpx file -->
<h3>File Upload for Hike Page Map and Track: (.gpx file)</h3>
<p><em style="text-decoration:underline;">Warning:</em> If you delete an
    existing gpx file, published hikes may be affected; you may simply
    specify a new file to override the current settings for this hike.</p>
<?php if (isset($_SESSION['uplmsg']) && $_SESSION['uplmsg'] !== '') : ?>
<p style="font-size:18px;color:darkblue;">The following action has resulted
    from your latest "APPLY": <?= $_SESSION['uplmsg'];?></p>
    <?php $_SESSION['uplmsg'] = ''; ?>
<?php endif; ?>
<span style="color:brown;">Current Main Hike Track File: </span>
<?php if ($curr_gpx == '') : ?>
<em>None Specified</em><br />
<?php  else : ?>
<em><?= $curr_gpx;?></em>
&nbsp;&nbsp;&nbsp;&nbsp;
<span style="color:brown;">Check to Delete&nbsp;&nbsp;</span>
<input type="checkbox" name="dgpx" /><br />
<?php endif; ?>
<ul>
    <li><span style="color:brown;">Upload new gpx file:&nbsp;</span>
        <input type="file" name="newgpx" /></li>
</ul>
<!-- Begin basic data presentation -->
<h3>Data Related to This Hike:</h3>
<label for="hike">Hike Name: </label>
<textarea id="hike" name="hname"><?= $hikeTitle;?></textarea>
&nbsp;&nbsp;<p style="display:none;" id="locality"><?= $hikeLocale;?></p>
<label for="area">Locale (City/POI): </label>
<select id="area" name="locale">
    <optgroup label="North/Northeast">
        <option value="Jemez Springs">Jemez Springs</option>
        <option value="Valles Caldera">Valles Caldera</option>
        <option value="Los Alamos">Los Alamos</option>
        <option value="White Rock">White Rock</option>
        <option value="Santa Fe">Santa Fe</option>
        <option value="Ojo Caliente">Ojo Caliente</option>
        <option value="Abiquiu">Abiquiu</option>
        <option value="Pecos">Pecos</option>
        <option value="Villanueva">Villanueva</option>
        <option value="Taos">Taos</option>
        <option value="Pilar">Pilar</option>
    <optgroup label="Northwest">
        <option value="Farmington">Farmington</option>
        <option value="San Ysidro">San Ysidro</option>
        <option value="San Luis">San Luis</option>
        <option value="Cuba">Cuba</option>
        <option value="Lybrook">Lybrook</option>
    <optgroup label="Central NM">
        <option value="Cerrillos">Cerrillos</option>
        <option value="Golden">Golden</option>
        <option value="Albuquerque">Albuquerque</option>
        <option value="Placitas">Placitas</option>
        <option value="Corrales">Corrales</option>
        <option value="Tijeras">Tijeras</option>
        <option value="Tajique">Tajique</option>
    <optgroup label="West">
        <option value="Grants">Grants</option>
        <option value="Ramah">Ramah</option>
        <option value="Gallup">Gallup</option>
    <optgroup label="South Central">
        <option value="San Acacia">San Acacia</option>
        <option value="San Antonio">San Antonio</option>
        <option value="Tularosa">Tularosa</option>
    <optgroup label="Southwest">
        <option value="Silver City">Silver City</option>
        <option value="Pinos Altos">Pinos Altos</option>
        <option value="Glenwood">Glenwood</option>
</select><br /><br />
<label for="type">Hike Type: </label>
<select id="type" name="htype">
    <option value="Loop">Loop</option>
    <option value="Two-Cars">Two-Cars</option>
    <option value="Out-and-back">Out-and-back</option>
</select>&nbsp;&nbsp;
<p id="dif" style="display:none"><?= $hikeDiff;?></p>
<label for="diff">Level of difficulty: </label>
<select id="diff" name="hdiff">
    <option value="Easy">Easy</option>
    <option value="Easy-Moderate">Easy-Moderate</option>
    <option value="Moderate">Moderate</option>
    <option value="Med-Difficult">Medium-Difficult</option>
    <option value="Difficult">Difficult</option>
</select><br /><br />
<input id="mft" type="checkbox" name="mft" />&nbsp;&nbsp;
    Calculate Miles/Feet From GPX [<em style="color:brown">**NOTE: GPX file
        must already be uploaded</em>],&nbsp;&nbsp;or Specify/Change below<br />
<label for="miles">Round-trip length in miles: </label>
<textarea id="miles" name="hlgth"><?= $hikeMiles;?></textarea>
<label for="elev">Elevation change in feet: </label>
<textarea id="elev" name="helev"><?= $hikeFeet;?></textarea><br /><br />
<label for="pup">Ascent: </label>
<textarea id="pup" name="hpup"><?= $pup;?></textarea><br /><br />
<label for="fac">Facilities at the trailhead: </label>
<textarea id="fac" name="hfac"><?= $hikeFac;?></textarea>&nbsp;&nbsp;
<label for="wow">"Wow" Appeal: </label>
<textarea id="wow" name="hwow"><?= $hikeWow;?></textarea><br /><br />
<label for="seas">Best Hiking Seasons: </label>
<textarea id="seas" name="hsea"><?= $hikeSeasons;?></textarea>&nbsp;&nbsp;
<p id="expo" style="display:none"><?= $hikeExpos;?></p>
<label for="sun">Exposure: </label>
<select id="sun" name="hexp">
    <option value="Full sun">Full sun</option>
    <option value="Mixed sun/shade">Mixed sun/shade</option>
    <option value="Good shade">Good shade</option>
</select>&nbsp;&nbsp;
<p>Trailhead Latitude/Longitude is set by the uploaded GPX file.
    If you wish to edit these regardless, click here: (again to hide) 
    <input id="showll" type="checkbox" name="latlng" value="nosend" /></p>
<p id="lldisp" style="display:none">
<label for="lat">Trailhead: Latitude </label>
<textarea id="lat" name="hlat"><?= $hikeLat;?></textarea>&nbsp;&nbsp;
<label for="lon">Longitude </label>
<textarea id="lon" name="hlon"><?= $hikeLng;?></textarea></p>
<br /><label for="murl">Map Directions Link (Url): </label>
<textarea id="murl" name="gdirs"><?= $hikeDirs;?></textarea>
<h3 style="margin-bottom:12px;">Cluster Hike Assignments:
    (Hikes with overlapping trailheads or in close proximity)</h3>
<span style="font-size:18px;color:Brown;">Reset Assignments:&nbsp;&nbsp;
<input id="ignore" type="checkbox" name="nocare" /></span><br /><br />
<label for="ctip">Cluster: </label>
<select id="ctip" name="htool">
<?php for ($i=0; $i<count($cnames); $i++) : ?>
    <option value="<?= $cnames[$i]?>"><?= $cnames[$i];?></option>
<?php endfor; ?>
</select><span id="showdel" style="display:none;">You may remove the cluster
    assignment by checking here:&nbsp;<input id="deassign"
    type="checkbox" name="rmclus" value="NO" /></span>
<span id="notclus" style="display:none;">There is no currently
    assigned cluster for this hike.</span>
<input id="grpchg" type="hidden" name="chgd" value="NO" />
<p>If you are establishing a new group, select the checkbox: 
    <input id="newg" type="checkbox" name="nxtg" value="NO" />
</p>
<p style="margin-top:-10px;margin-left:40px;">and enter the name for the 
    new group here: <input id="newt" type="text" name="newgname" size="50" />
</p>
