<?php session_start(); ?>
<!DOCTYPE html>
<html>
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
	<link href="editDBb.css"
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
	$clusgrps = implode(",",$cgrps);
	$_SESSION['cluster_letters'] = $clusgrps;
?>
<form action="saveChanges.php" method="POST">
<?php
echo '<input type="hidden" name="hno" value="' . $hikeNo . '" />';
?>
<em style="color:DarkBlue;font-size:18px;">Any changes below will be made for the hike: "<?php echo $info[1];?>".
	If no changes are made you may either exit this page or hit the "sbumit" button.</em><br /><br />
<label for="hike">Hike Name: </label><textarea id="hike" name="hname"><?php echo $info[1]?></textarea>&nbsp;&nbsp;
<p style="display:none;" id="locality"><?php echo trim($info[2])?></p>
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
<p id="gletr" style="display:none"><?php echo $info[5];?></p>
<p id="gnme" style="display:none"><?php echo $info[28];?></p>
<label for="cgrp">Cluster: Group </label>
<?php
	echo '<select id="cgrp" name="hclus">';
	for ($j=0; $j<count($cgrps); $j++) {
		echo '<option value="' . $cgrps[$j] . '">' . $cgrps[$j] . '</option>';
	}
	echo '</select>&nbsp;&nbsp;';
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
	name="nxtg" value="YES" /> and enter the name for the new group here: <input id="newt" 
	type="text" name="newgname" size="50" /></p><br />

<p id="ctype" style="display:none"><?php echo $info[6];?></p>
<label for="type">Hike Type: </label>
<select id="type" name="htype">
	<option value="Loop">Loop</option>
	<option value="Two-Cars">Two-Cars</option>
	<option value="Out-and-back">Out-and-back</option>
</select>&nbsp;&nbsp;
<label for="miles">Round-trip length in miles: </label><textarea id="miles" name="hlgth"><?php echo $info[7];?></textarea>&nbsp;&nbsp;
<label for="elev">Elevation change in feet: </label><textarea id="elev" name="helev"><?php echo $info[8];?></textarea>&nbsp;&nbsp;
<p id="dif" style="display:none"><?php echo $info[9];?></p>
<label for="diff">Level of difficulty: </label>
<select id="diff" name="hdiff">
	<option value="Easy">Easy</option>
	<option value="Easy-Moderate">Easy-Moderate</option>
	<option value="Moderate">Moderate</option>
	<option value="Med-Difficult">Medium-Difficult</option>
	<option value="Difficult">Difficult</option>
</select><br />
<label for="fac">Facilities at the trailhead: </label><textarea id="fac" name="hfac"><?php echo $info[10];?></textarea>&nbsp;&nbsp;
<label for="wow">"Wow" Appeal: </label><textarea id="wow" name="hwow"><?php echo $info[11];?></textarea>&nbsp;&nbsp;
<label for="seas">Best Hiking Times: </label><textarea id="seas" name="hsea"><?php echo $info[12];?></textarea><br /><br />
<p id="expo" style="display:none"><?php echo $info[13];?></p>
<label for="sun">Exposure: </label>
<select id="sun" name="hexp">
	<option value="Full sun">Full sun</option>
	<option value="Mixed sun/shade">Mixed sun/shade</option>
	<option value="Good shade">Good shade</option>
</select>&nbsp;&nbsp;
<label for="lat">Trailhead: Latitude </label>
<textarea id="lat" name="hlat"><?php echo $info[19];?></textarea>&nbsp;&nbsp;
<label for="lon">Longitude </label>
<textarea id="lon" name="hlon"><?php echo $info[20];?></textarea><br />
<label for="ph1">Photo URL1 (If solo, main album link): </label>
<textarea id="ph1" name="purl1"><?php echo $info[23];?></textarea><br />
<label for="ph2">Photo URL2 (Will appear as "Tom's"): </label>
<textarea id="ph2" name="purl2"><?php echo $info[24];?></textarea><br /><br />
<label for="murl">Map Directions Link (Url): </label>
<textarea id="murl" name="gdirs"><?php echo $info[25];?></textarea><br /><br />
<?php 
	if ($info[37] !== '') {
		echo '<p>Tips Text: </p>';
		echo '<textarea id="ttxt" name="tips" rows="10" cols="140">' . $info[37] . '</textarea><br />';
	} else {
		echo '<textarea id="ttxt" name="tips" rows="10" cols="140">' . 
			'[NO TIPS FOUND]' . '</textarea><br />';
	}
?>
<p>Hike Information:</p>
<textarea id="info" name="hinfo" rows="16" cols="130"><?php echo $info[38];?></textarea>
<h3>Hike Reference Sources: (NOTE: Book type cannot be changed - if needed, delete and add a new one)</h3>
<?php
	$refs = explode("^",$info[39]);
	$rcnt = $refs[0];
	$noOfRefs = intval($rcnt);
	echo '<p id="refcnt" style="display:none">' . $noOfRefs . '</p>';
	echo '<input type="hidden" name = "orgrefs" value="' . $noOfRefs . '" />';
	array_shift($refs);
	$nxt = 0;
	for ($j=0; $j<$noOfRefs; $j++) {
		$rtype = 'rid' . $j;
		$reftype = 'ref' . $j;
		echo '<p id="' . $rtype . '" style="display:none">' . $refs[$nxt] . '</p>';
		echo '<label for="' . $reftype . '">Reference Type: </label>';
		echo '<select id="' . $reftype . '" style="height:26px;width:150px;" name="rtype[]">';
		echo '<option value="b">Book</option>';
		echo '<option value="p">Photo Essay</option>';
		echo '<option value="w">Website</option>';
		echo '<option value="a">App</option>';
		echo '<option value="d">Downloadable Doc</option>';
		echo '<option value="l">Blog</option>';
		echo '<option value="o">On-line Map</option>';
		echo '<option value="m">Magazine</option>';
		echo '<option value="s">News Article</option>';
		echo '<option value="g">Meetup Group</option>';
		echo '<option value="r">Related Link</option>';
		echo '<option value="n">Text Only - No Link</option>';
		echo '</select><br />';
		if ($refs[$nxt] === 'b' || $refs[$nxt] === 'p') {
			echo '<label style="text-indent:24px;">Title: </label><textarea style="height:20px;width:320px" name="rit1[]">' .
				$refs[$nxt+1] . '</textarea>&nbsp;&nbsp;';
			echo '<label>Author: </label><textarea style="height:20px;width:320px" name="rit2[]">' .
				$refs[$nxt+2] . '</textarea>&nbsp;&nbsp<label>Delete this: </label>' .
			   '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="'.
			   		$j . '"><br /><br />';
			$nxt +=3;
		} elseif ($refs[$nxt] === 'n') {
			echo '<label>Text only item: </label><textarea style="height:20px;width:320px;" name="rit1[]">' .
				$refs[$nxt+1] . '</textarea><label>Delete this: </label>' .
				'<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="' .
					$j . '"><br /><br />';
			$nxt += 2;
		} else {
			echo '<label>Item link: </label><textarea style="height:20px;width:500px;" name="rit1[]">' .
				$refs[$nxt+1] . '</textarea>&nbsp;&nbsp;<label>Cick text: </label><textarea style="height:20px;width:330px;" name="rit2[]">' . 
				$refs[$nxt+2] . '</textarea>&nbsp;&nbsp;<label>Delete this: </label>' .
				'<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="' .
					$j . '"><br /><br />';
			$nxt += 3;
		}
	}
?>
<p>Add references here:</p>
<p>Select the type of reference (with above, up to 8 total) and its accompanying data below:</p>
<select style="height:26px;" name="rtype[]">
	<option value="b">Book</option>
	<option value="p">Photo Essay</option>
	<option value="w">Website</option>
	<option value="a">App</option>
	<option value="d">Downloadable Doc</option>
	<option value="l">Blog</option>
	<option value="o">On-line Map</option>
	<option value="m">Magazine</option>
	<option value="s">News Article</option>
	<option value="g">Meetup Group</option>
	<option value="r">Related Link</option>
	<option value="n">Text Only - No Link</option>
</select>
Book Title/Link URL:<input type="text" name="rit1[]" size="55" />&nbsp;
Author/Click-on Text<input type="text" name="rit2[]" size="35" /><br /><br />
<select style="height:26px;" name="rtype[]">
	<option value="b">Book</option>
	<option value="p">Photo Essay</option>
	<option value="w">Website</option>
	<option value="a">App</option>
	<option value="d">Downloadable Doc</option>
	<option value="l">Blog</option>
	<option value="o">On-line Map</option>
	<option value="m">Magazine</option>
	<option value="s">News Article</option>
	<option value="g">Meetup Group</option>
	<option value="r">Related Link</option>
	<option value="n">Text Only - No Link</option>
</select>
Book Title/Link URL:<input type="text" name="rit1[]" size="55" />&nbsp;
Author/Click-on Text<input type="text" name="rit2[]" size="35" /><br />

<h3>Proposed Data:</h3>
<?php 
	if ($info[40] !== '') {
		$prop = explode("^",$info[40]);
		$pcnt = intval($prop[0]);
		array_shift($prop);
		$nxt = 0;
		for ($i=0; $i<$pcnt; $i++) {
			echo 'Label: <textarea class="tstyle1" name="plabl[]">' . $prop[$nxt] . '</textarea>&nbsp;&nbsp;';
			echo 'Url: <textarea class="tstyle2" name="plnk[]">' . $prop[$nxt+1] . '</textarea>&nbsp;&nbsp;';
			echo 'Click-on text: <textarea class="tstyle3" name="pctxt[]">' . $prop[$nxt+2] . 
				'</textarea><br />';
			$nxt +=3;
		}
	}
?>
<p>Add Proposed Data:</p>
<label>Label: </label><input class="tstyle1" name="plabl[]" size="30" />&nbsp;&nbsp;
<label>Url: </label><input class="tstyle2" name="plnk[]" size="55" />
<label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="pctxt[]" size="30" /><br />
<label>Label: </label><input class="tstyle1" name="plbl[]" size="30" />&nbsp;&nbsp;
<label>Url: </label><input class="tstyle2" name="ltxt[]" size="55" />
<label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="ctxt[]" size="30" />

<h3>Actual Data:</h3>

<?php
	if ($info[41] !== '') {
		$act = explode("^",$info[41]);
		$acnt = intval($act[0]);
		array_shift($act);
		$nxt =0;
		for ($j=0; $j<$acnt; $j++) { 
			echo 'Label: <textarea class="tstyle1" name="alabl[]">' . $act[$nxt] . '</textarea>&nbsp;&nbsp;';
			echo 'Url: <textarea class="tstyle2" name="alnk[]">' . $act[$nxt+1] . '</textarea>&nbsp;&nbsp;';
			echo 'Click-on text: <textarea class="tstyle3" name="actxt[]">' . $act[$nxt+2] . 
				'</textarea><br />';
			$nxt +=3;
		}
	}
?>
<p>Add Actual Data:</p>
<label>Label: </label><input class="tstyle1" name="alabl[]" size="30" />&nbsp;&nbsp;
<label>Url: </label><input class="tstyle2" name="alnk[]" size="55" />
<label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="actxt[]" size="30" /><br />
<label>Label: </label><input class="tstyle1" name="alabl[]" size="30" />&nbsp;&nbsp;
<label>Url: </label><input class="tstyle2" name="alnk[]" size="55" />
<label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="actxt[]" size="30" /><br />

<br /><input id="go" type="submit" value="Save Changes" />
</form>

</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="editDB.js"></script>
</body>
</html>