<?php
session_start();
?>
<input type="hidden" name="hno" value="<?php echo $hikeNo;?>" />
<input type="hidden" name="usr" value="<?php echo $uid;?>" />
<input type="hidden" name="stat" value ="<?php echo $status;?>" />
<input type="hidden" name="col" value="<?php echo $hikeColl;?>" />
<!-- the following are required esp when extracting a published hike -->
<input type="hidden" name="gpx" value="<?php echo $hikeGpx;?>" />
<input type="hidden" name="trk" value="<?php echo $hikeTrk;?>" />
<input type="hidden" name="ao1" value="<?php echo $hikeAddImg1;?>" />
<input type="hidden" name="ao2" value="<?php echo $hikeAddImg2;?>" />
<label for="hike">Hike Name: </label>
<textarea id="hike" name="hname"><?php echo $hikeTitle;?>
</textarea>&nbsp;&nbsp;
<p style="display:none;" id="locality"><?php echo $hikeLocale;?></p>
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
<p id="mrkr" style="display:none"><?php echo $hikeMarker;?></p>
<input type="hidden" name="pmrkr" value="<?php echo $hikeMarker;?>" />
<input type="hidden" name="pclus" value="<?php echo $hikeClusGrp;?>" />
<p id="group" style="display:none"><?php echo $hikeGrpTip;?></p>
<input type="hidden" name="pcnme" value="<?php echo $hikeGrpTip;?>" />
<?php
// NOTE: This file is included in editDB.php, so MySQL connection exists
$gpx_req = "SELECT gpx,trk FROM EHIKES WHERE indxNo = {$hikeNo};";
$gpx_query = mysqli_query($link, $gpx_req);
if (!$gpx_query) {
    die(
        __FILE__ . ": Could not extract gpx file name from EHIKES " .
        "hike no. {$hikeNo}: " . mysqli_error($link)
    );
}
$gpxdat = mysqli_fetch_row($gpx_query);
$curr_gpx = $gpxdat[0];
$curr_trk = $gpxdat[1];
echo '<input type="hidden" name="mgpx" value="' . $curr_gpx . '" />';
echo '<input type="hidden" name="mtrk" value="' . $curr_trk . '" />';
?>
<h3>Hike Page Map and Track:</h3>
<?php
if (isset($_SESSION['uplmsg']) && $_SESSION['uplmsg'] !== '') {
    echo '<p style="font-size:18px;color:Blue;">The following ' .
        'action has resulted from your latest "APPLY":</p>';
    echo $_SESSION['uplmsg'];
    $_SESSION['uplmsg'] = '';
}
?>
<p><span style="color:brown;">Current Main Hike Track File: </span>
    <?php
    if ($curr_gpx == '') {
        echo '<em>None Specified</em><br />'; 
    } else {
        echo "<em>{$curr_gpx}</em>&nbsp;&nbsp;" .
        '<span style="color:brown;">Check to Delete&nbsp;&nbsp;</span>' .
        '<input type="checkbox" name="dgpx" /><br />';
    }
    ?>
    [NOTE: If a gpx file exists, and a new one is uploaded without deleting
    the old one, the new one will be used for display of the hike map and track]
    <br /><span style="color:brown;">Upload new gpx file:&nbsp;</span>
    <input type="file" name="newgpx" />
</p>
<h3>Cluster Hike Assignments: (Hikes with overlapping trailheads or in 
    close proximity)<br /><br />
<span style="margin-left:50px;font-size:18px;color:Brown;">Reset Assignments:&nbsp;&nbsp;
    <input id="ignore" type="checkbox" name="nocare" /></span></h3>
<?php
echo '<label for="ctip">&nbsp;&nbsp;Cluster: </label>';
echo '<select id="ctip" name="htool">';
for ($i=0; $i<$groupCount; $i++) {
echo '<option value="' . $cnames[$i] . '">' . $cnames[$i] . "</option>\n";
}
echo "</select>&nbsp;&nbsp;\n" .
'<span id="showdel" style="display:none;">You may remove the cluster ' .
    'assignment by checking here:&nbsp;<input id="deassign" ' .
    'type="checkbox" name="rmclus" value="NO" /></span>' . "\n" .
'<span id="notclus" style="display:none;">There is no currently ' .
    "assigned cluster for this hike.</span>\n";
?>
<input id="grpchg" type="hidden" name="chgd" value="NO" />
<p>If you are establishing a new group, select the checkbox: 
    <input id="newg" type="checkbox" name="nxtg" value="NO" />
    <input id="curcnt" type="hidden" name="grpcnt" value="<?php echo $groupCount;?>" />
</p>
<p style="margin-top:-10px;margin-left:40px;">and enter the name for the 
    new group here: <input id="newt" type="text" name="newgname" size="50" />
</p>
<h3>Other Basic Hike Data</h3>
<p id="ctype" style="display:none"><?php echo $hikeStyle;?></p>
<label for="type">Hike Type: </label>
<select id="type" name="htype">
    <option value="Loop">Loop</option>
    <option value="Two-Cars">Two-Cars</option>
    <option value="Out-and-back">Out-and-back</option>
</select>&nbsp;&nbsp;
<label for="miles">Round-trip length in miles: </label>
<textarea id="miles" name="hlgth"><?php echo $hikeMiles;?>
</textarea>&nbsp;&nbsp;
<label for="elev">Elevation change in feet: </label>
<textarea id="elev" name="helev"><?php echo $hikeFeet;?>
</textarea><br /><br />
<p id="dif" style="display:none"><?php echo $hikeDiff;?></p>
<label for="diff">Level of difficulty: </label>
<select id="diff" name="hdiff">
    <option value="Easy">Easy</option>
    <option value="Easy-Moderate">Easy-Moderate</option>
    <option value="Moderate">Moderate</option>
    <option value="Med-Difficult">Medium-Difficult</option>
    <option value="Difficult">Difficult</option>
</select>
<label for="fac">Facilities at the trailhead: </label>
<textarea id="fac" name="hfac"><?php echo $hikeFac;?>
</textarea><br /><br />
<label for="wow">"Wow" Appeal: </label>
<textarea id="wow" name="hwow"><?php echo $hikeWow;?>
</textarea>&nbsp;&nbsp;
<label for="seas">Best Hiking Seasons: </label>
<textarea id="seas" name="hsea"><?php echo $hikeSeasons;?>
</textarea><br /><br />
<p id="expo" style="display:none"><?php echo $hikeExpos;?></p>
<label for="sun">Exposure: </label>
<select id="sun" name="hexp">
    <option value="Full sun">Full sun</option>
    <option value="Mixed sun/shade">Mixed sun/shade</option>
    <option value="Good shade">Good shade</option>
</select>&nbsp;&nbsp;
<label for="lat">Trailhead: Latitude </label>
<textarea id="lat" name="hlat"><?php echo $hikeLat;?></textarea>&nbsp;&nbsp;
<label for="lon">Longitude </label>
<textarea id="lon" name="hlon"><?php echo $hikeLng;?></textarea><br />
<label for="ph1">Photo URL1 ("Main"): </label>
<textarea id="ph1" name="purl1"><?php echo $hikeUrl1;?></textarea><br />
<label for="ph2">Photo URL2 ("Additional"): </label>
<textarea id="ph2" name="purl2"><?php echo $hikeUrl2;?></textarea><br /><br />
<label for="murl">Map Directions Link (Url): </label>
<textarea id="murl" name="gdirs"><?php echo $hikeDirs;?></textarea><br /><br />
<!-- This next section is photo editing-->
<p id="ptype" style="display:none">Edit</p>
<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>