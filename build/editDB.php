<head>
	<title>Edit Database</title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
		content="Edit a given hike" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="editDB.css"
		type="text/css" rel="stylesheet" />
</head>

<body>

<div style="padding:16px;">
<?php
	$database = '../data/test.csv';
	$dbfile = file($database);
	$hikeNo = intval($_GET['hikeNo']);
	$info = str_getcsv($dbfile[$hikeNo]);
	# parameterize the data for presentation in the text boxes
	# cannot change the marker type here ($info[3])
	# 'cluster string' ($info[4]) is associated with index pages
	# pull out the cluster groups
	$cnames = array();
	$cgrps = array();
	foreach ($dbfile as $hikeline) {
		$hdat = str_getcsv($hikeline);
		if ($hdat[5] !== '') {
			$match = false;
			$noOfGrps = count($cgrps);
			for ($k=0; $k<$noOfGrps; $k++) {
				if ($hdat[5] == $cgrps[$k]) {
					$match = true;
					break;
				}
			}
			if ($match === false) {
				array_push($cgrps,$hdat[5]);
				array_push($cnames,$hdat[28]);
			}
		}
	} 
?>
<form action="saveChanges.php" method="POST">
<em style="color:DarkBlue;font-size:18px;">Any changes below will be made for the hike: "<?php echo $info[1];?>".
	If no changes are made you may either exit this page or hit the "sbumit" button.</em><br /><br />
<p style="display:none;" id="locality"><?php echo trim($info[2])?></p>
<label for="hike">Hike Name: </label><textarea id="hike" name="hname"><?php echo $info[1]?></textarea>&nbsp;&nbsp;
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
	<option value="Taos">Taos</option>
	<option value="Pilar">Pilar</option>
	<option value="Villanueva">Villanueva</option>
  <optgroup label="Northwest">
	<option value="Farmington">Farmington</option>
	<option value="San Ysidro">San Ysidro</option>
	<option value="San Luis">San Luis</option>
	<option value="Cuba">Cuba</option>
	<option value="Lybrook">Lybrook</option>
  <optgroup label="Central NM">
	<option value="Cerrillos">Cerrillos</option>
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
<p id="gletr" style="display:none"><?php echo $info[5];?></p>
<p id="gnme" style="display:none"><?php echo $info[28];?></p>
<label for="cgrp">Cluster: Group </label>
<?php
	echo '<select id="cgrp" name="hclus">';
	for ($j=0; $j<count($cgrps); $j++) {
		echo '<option value="' . $cgrps[$j] . '">' . $cgrps[$j] . '</option>';
	}
	echo '</select>';
?>
<label for="ctip">Name </label>
<?php
	echo '<select id="ctip" name="htool">';
	for ($i=0; $i<count($cnames); $i++) {
		echo '<option value="' . $cnames[$i] . '">' . $cnames[$i] . '</option>';
	}
	echo '</select>';
?>
<p>If you are establishing a new group, select the checkbox: <input id="newg" type="checkbox"
name="nxtg" /> and enter the name for the new group here: <input id="newt" type="text" size="50" /></p><br />

<p id="ctype" style="display:none"><?php echo $info[6];?></p>
<label for="type">Hike Type: </label>
<select id="type" name="htype">
	<option value="Loop">Loop</option>
	<option value="Two-Cars">Two-Cars</option>
	<option value="Out-and-back">Out-and-back</option>
</select>&nbsp;&nbsp;
<label for="miles">Round-trip length in miles: <textarea id="miles" name="hlgth"><?php echo $info[7];?></textarea>&nbsp;&nbsp;
<label for="elev">Elevation change in feet: <textarea id="elev" name="helev"><?php echo $info[8];?></textarea>&nbsp;&nbsp;
<p id="dif" style="display:none"><?php echo $info[9];?></p>
<label for="diff">Level of difficulty: </label>
<select id="diff" name="hdiff">
	<option value="Easy">Easy</option>
	<option value="Easy-Moderate">Easy-Moderate</option>
	<option value="Moderate">Moderate</option>
	<option value="Med-Difficult">Medium-Difficult</option>
	<option value="Difficult">Difficult</option>
</select><br />
<label for="fac">Facilities at the trailhead: <textarea id="fac" name="hfac"><?php echo $info[10];?></textarea>&nbsp;&nbsp;
<label for="wow">"Wow" Appeal: <textarea id="wow" name="hwow"><?php echo $info[11];?></textarea>&nbsp;&nbsp;
<label for="seas">Best Hiking Times: <textarea id="seas" name="hsea"><?php echo $info[12];?></textarea><br />


<!--	
</div>	

	Enter or change the Main Photo URL (link listed in index table):<br />
	<textarea name="purl1" rows="1" cols="130"><?php echo $photoURL1;?></textarea><br /><br />
	Enter or change the Secondary Photo URL (if present: if not, you may add one):<br />
	<textarea name="purl2" rows="1" cols="130"><?php echo $photoURL2;?></textarea><br /><br />
	Enter or change the Google Maps Directions URL [NOTE: this is a single line, despite text-wrapping]<br />
	<textarea name="gdirs" rows="1" cols="130" wrap="soft"><?php echo $dirs;?></textarea><br /><br />
	Enter or change any "Tips Text" for the hike:<br />
	<textarea name="tips" rows="12" cols="130" wrap="hard"><?php echo $hikeTips;?></textarea><br /><br />
	Edit the hike information as desired:<br />
	<textarea name="info" rows="20" cols="130" wrap="hard"><?php echo $hikeInfo;?></textarea><br /><br />
	Edit the following references list:<br />
	<textarea name="refs" rows="12" cols="130" wrap="hard"><?php echo $refs;?></textarea><br /><br />
	Edit or add to any "Proposed Data" list elements:<br />
	<textarea name="pdat" rows="8" cols="130" wrap="hard"><?php echo $pDat;?></textarea><br /><br />
	Edit or add to any "Actual Data" list elements:<br />
	<textarea name="adat" rows="8" cols="130" wrap="hard"><?php echo $aDat;?></textarea><br /><br />
	
	<input type="hidden" name="hno" value="<?php echo $hikeNo;?>" />
	<input type="hidden" name="nme" value="<?php echo $hikeName;?>" />
	<input type="submit" value="Save Changes" /> -->
</form>

</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="editDB.js"></script>
</body>
</html>