<input type="hidden" name="hno" value="<?= $hikeNo;?>" />
<input type="hidden" name="usr" value="<?= $uid;?>" />
<input type="hidden" name="col" value="<?= $hikeColl;?>" />
<!-- gpx is required esp when extracting a published hike -->
<input type="hidden" name="gpx" value="<?= $hikeGpx;?>" />
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
</select>&nbsp;&nbsp;
<p id="mrkr" style="display:none"><?= $hikeMarker;?></p>
<p id="greq" style="display:none"><?= $grpReq;?></p>
<input type="hidden" name="pmrkr" value="<?=$hikeMarker;?>" />
<input type="hidden" name="pclus" value="<?= $hikeClusGrp;?>" />
<p id="group" style="display:none"><?= $hikeGrpTip;?></p>
<input type="hidden" name="pcnme" value="<?= $hikeGrpTip;?>" />
<h3>Cluster Hike Assignments: (Hikes with overlapping trailheads or in 
    close proximity)<br /><br />
<span style="margin-left:50px;font-size:18px;color:Brown;">
Reset Assignments:&nbsp;&nbsp;
    <input id="ignore" type="checkbox" name="nocare" /></span></h3>
<label for="ctip">&nbsp;&nbsp;Cluster: </label>
<select id="ctip" name="htool">
<?php for ($i=0; $i<$groupCount; $i++) : ?>
    <option value="<?= $cnames[$i]?>"><?= $cnames[$i];?></option>
<?php endfor; ?>
</select>&nbsp;&nbsp;
<span id="showdel" style="display:none;">You may remove the cluster
    assignment by checking here:&nbsp;<input id="deassign"
    type="checkbox" name="rmclus" value="NO" /></span>
<span id="notclus" style="display:none;">There is no currently
    assigned cluster for this hike.</span>
<input id="grpchg" type="hidden" name="chgd" value="NO" />
<p>If you are establishing a new group, select the checkbox: 
    <input id="newg" type="checkbox" name="nxtg" value="NO" />
    <input type="hidden" name="grpcnt" value="<?= $dbCount;?>" />
</p>
<p style="margin-top:-10px;margin-left:40px;">and enter the name for the 
    new group here: <input id="newt" type="text" name="newgname" size="50" />
</p>
<h3>Other Basic Hike Data</h3>
<p id="ctype" style="display:none"><?= $hikeStyle;?></p>
<label for="type">Hike Type: </label>
<select id="type" name="htype">
    <option value="Loop">Loop</option>
    <option value="Two-Cars">Two-Cars</option>
    <option value="Out-and-back">Out-and-back</option>
</select>&nbsp;&nbsp;
<label for="miles">Round-trip length in miles: </label>
<textarea id="miles" name="hlgth"><?= $hikeMiles;?></textarea>&nbsp;&nbsp;
<label for="elev">Elevation change in feet: </label>
<textarea id="elev" name="helev"><?= $hikeFeet;?></textarea><br /><br />
<p id="dif" style="display:none"><?= $hikeDiff;?></p>
<label for="diff">Level of difficulty: </label>
<select id="diff" name="hdiff">
    <option value="Easy">Easy</option>
    <option value="Easy-Moderate">Easy-Moderate</option>
    <option value="Moderate">Moderate</option>
    <option value="Med-Difficult">Medium-Difficult</option>
    <option value="Difficult">Difficult</option>
</select>
<label for="fac">Facilities at the trailhead: </label>
<textarea id="fac" name="hfac"><?= $hikeFac;?></textarea><br /><br />
<label for="wow">"Wow" Appeal: </label>
<textarea id="wow" name="hwow"><?= $hikeWow;?></textarea>&nbsp;&nbsp;
<label for="seas">Best Hiking Seasons: </label>
<textarea id="seas" name="hsea"><?= $hikeSeasons;?></textarea><br /><br />
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
<textarea id="murl" name="gdirs"><?= $hikeDirs;?></textarea><br /><br />
<!-- This next section is photo editing-->
<p id="ptype" style="display:none">Edit</p>
<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>
