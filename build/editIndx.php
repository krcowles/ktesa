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
	<link href="editIndx.css"
		type="text/css" rel="stylesheet" />
</head>

<body>

<div style="padding:16px;">
<?php
	$database = '../data/database.csv';
	$dbhandle = fopen($database,"r");
	$hikeNo = $_GET['hikeNo'];
	while ( ($indxdat = fgetcsv($dbhandle)) !== false) {
		if ( $indxdat[0] == $hikeNo) {
			$info = $indxdat;
			break;
		}
	}
	fclose($dbhandle);
	$indxName = $info[1];
	/* NOTE: The cluster string ($info[4]) will not be available for editing here: the
	   proper means of adding a hike to the Index Page is to edit that hike
	   and add the Visitor Center association; */
	# Until database has settled with unencoded tables, the following will not alter data
	$dirs = $info[25];
	$indxInfo = $info[38];
	$refs = $info[39];
	$indxTbl = $info[29];
	/* The following code is copied from 'indexPageTemplate.php' in order to produce a
	   readable html file for the user to edit. It will be converted back into a string
	   array when saved */
	$rows = explode("|",$indxTbl);
	$tblhtml = '<table id="siteIndx">' . "\n" . '<thead>' . "\n" . '<tr>' . "\n";
	$tblhtml .= '<th class="hdrRow" scope="col">Trail</th>' . "\n";
	$tblhtml .= '<th class="hdrRow" scope="col">Web Pg</th>' . "\n";
	$tblhtml .= '<th class="hdrRow" scope="col">Trail Length</th>' . "\n";
	$tblhtml .= '<th class="hdrRow" scope="col">Elevation</th>' . "\n";
	$tblhtml .= '<th class="hdrRow" scope="col">Exposure</th>' . "\n";
	$tblhtml .= '<th class="hdrRow" scope="col">Photos</th>'  . "\n";
	$tblhtml .= '</tr>' . "\n" . '</thead>' . "\n" . '<tbody>' . "\n";
	$rowcnt = count($rows);
	#echo "Seeing " . $rowcnt . " rows...";
	for ($j=0; $j<$rowcnt; $j++) {
		$row = explode("^",$rows[$j]);
		# there are always 7 pieces, counting row type
		if ($row[0] === 'n') {  // "normal" - not grayed out
			$tblhtml .= '<tr>' . "\n" . '<td>' . $row[1] . '</td>' . "\n";
			$tblhtml .= '<td><a href="' . $row[2] . '" target="_blank">' . "\n" .
				'<img class="webShift" src="../images/greencheck.jpg" alt="checkbox" /></a></td>' . "\n";
			$tblhtml .= '<td>' . $row[3] . '</td>' . "\n";
			$tblhtml .= '<td>' . $row[4] . '</td>' . "\n";
			$tblhtml .= '<td><img class="expShift" src="' . $row[5] . '" alt="exposure icon" /></td>' . "\n";
			$tblhtml .= '<td><a href="' . $row[6] . '" target="_blank">' . "\n" .
				'<img class="flckrShift" src="../images/album_lnk.png" alt="Photos symbol" /></a></td>' . "\n";
			$tblhtml .= '</tr>' . "\n";
		} else {   // $row[0]=g  - grayed out row
			$tblhtml .= '<tr>' . "\n" . '<td>' . $row[1] . '</td>' . "\n";
			$tblhtml .= '<td><img class="webShift" src="../images/x-box.png" alt="box with x" /></td>' . "\n";
			$tblhtml .= '<td>' . $row[3] . '</td>' . "\n";
			$tblhtml .= '<td>' . $row[4] . '</td>' . "\n";
			$tblhtml .= '<td class="naShift">N/A</td>' . "\n";
			$tblhtml .= '<td><img class="flckrShift" src="../images/x-box.png" alt="box with x" /></td>' . "\n";
			$tblhtml .= '</tr>' . "\n";
		}  # end of if row is grayed out...
	}  #end of row data-processing loop	
	$tblhtml .= '</tbody>' . "\n" . '</table>' . "\n";
	$indxTbl = $tblhtml;	   
?>
<form action="saveIndxChgs.php" method="POST">

<em style="color:DarkBlue;">Any changes below will be made for the Index Page: "<?php echo $indxName;?>". If no changes 
are made you may either exit this page or hit the "sbumit" button.</em><br /><br />

<label for="hike">Index Page Name: </label><textarea style="height:20px;" id="hike" name="hname"><?php echo $info[1]?></textarea>&nbsp;&nbsp;
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
<label for="facil">Facilities at the trailhead: </label><textarea style="height:20px;" id="facil" name="hfac"><?php echo $info[10];?></textarea><br /><br />
<label for="wow">"Wow" Appeal: </label><textarea style="height:20px;" id="wow" name="hwow"><?php echo $info[11];?></textarea>&nbsp;&nbsp;
<label for="lat">Visitor Center Location: Latitude </label>
<textarea style="height:20px;" id="lat" name="hlat"><?php echo $info[19];?></textarea>&nbsp;&nbsp;
<label for="lon">Longitude </label>
<textarea style="height:20px;" id="lon" name="hlon"><?php echo $info[20];?></textarea><br /><br />
Enter or change the Google Maps Directions to the Visitor Center [NOTE: this is a single line, despite text-wrapping]<br />
<textarea id="vcdirs" name="gdirs" rows="1" cols="140" wrap="soft"><?php echo $dirs;?></textarea><br /><br />

Edit the Park Information as desired:<br />
<textarea name="info" rows="12" cols="120" wrap="soft"><?php echo $indxInfo;?></textarea><br /><br />
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
		echo '<option value="h">Website</option>';
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
			echo '<label style="text-indent:20px;height:20px;">Title: </label><textarea style="height:34px;width:320px" name="rit1[]">' .
				$refs[$nxt+1] . '</textarea>&nbsp;&nbsp;';
			echo '<label>Author: </label><textarea style="height:20px;width:320px" name="rit2[]">' .
				$refs[$nxt+2] . '</textarea>&nbsp;&nbsp<label>Delete: </label>' .
			   '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="'.
			   		$j . '"><br /><br />';
			$nxt +=3;
		} elseif ($refs[$nxt] === 'n') {
			echo '<label>Text only item: </label><textarea style="height:20px;width:320px;" name="rit1[]">' .
				$refs[$nxt+1] . '</textarea><label>Delete: </label>' .
				'<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="' .
					$j . '"><br /><br />';
			$nxt += 2;
		} else {
			echo '<label>Item link: </label><textarea style="height:20px;width:500px;" name="rit1[]">' .
				$refs[$nxt+1] . '</textarea>&nbsp;&nbsp;<label>Cick text: </label><textarea style="height:20px;width:330px;" name="rit2[]">' . 
				$refs[$nxt+2] . '</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
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

<h2>HTML Code for Table of Hikes Associated With This Park</h2>
<textarea name="tbl" rows="20" cols="120" wrap="soft">
<?php echo $indxTbl;?></textarea><br /><br />
	
<input type="hidden" name="hno" value="<?php echo $hikeNo;?>" />
<input type="hidden" name="nme" value="<?php echo $indxName;?>" />
<input type="submit" value="Save Changes" />
</form>

</div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="editIndx.js"></script>
</body>
</html>