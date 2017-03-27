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
	<link href="editDB.css"
		type="text/css" rel="stylesheet" />
	<!-- early scripts to define drag functions for images -->
	<script src="../scripts/jquery-1.12.1.js"></script>
	<script src="picNplace.js"></script>
	
</head>

<body>

<div style="padding:16px;">
<?php
	$loadIcon = 'insert.png';
	$database = '../data/database.csv';
	$db = fopen($database,"r");
	$hikeNo = $_GET['hikeNo'];
	# Below: pull out the available cluster groups and establish $info array as hike data
	# NOTE: Organize by cluster letter to simplify editing process
	$clusters = array();
	$groups = array();
	$cnames = array();
	while ( ($hdat = fgetcsv($db)) !== false) {
		if ($hdat[0] == $hikeNo) {
			$info = $hdat;
		}
		# Check to see if this is a cluster hike:
		if ($hdat[28] !== '' && trim($hdat[28]) !== 'Clus tooltip') { # don't include header row
			$match = false;
			for ($k=0; $k<count($groups); $k++) {
				if (trim($hdat[5]) == $groups[$k]) {
					$match = true;
					break;
				}
			}
			if ($match === false) {
				# form an association of group to tooltip:
				array_push($groups,trim($hdat[5]));
				array_push($cnames,trim($hdat[28]));
				$assoc = trim($hdat[5]) . "$" . trim($hdat[28]);
				array_push($clusters,$assoc);
			}
		}
	} 
	fclose($db);
	$grpCnt = count($groups);
	$clusStr = implode(";",$clusters);
	$_SESSION['allClusters'] = $clusStr;	
?>
<form target="_blank" action="saveChanges.php" method="POST">
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
<p id="mrkr" style="display:none"><?php echo $info[3];?></p>
<p id="group" style="display:none"><?php echo $info[28];?></p>
<h3>------- Cluster Hike Assignments: (Hikes with overlapping trailheads or in close proximity) &nbsp;&nbsp;&nbsp;
<span style="font-size:18px;color:Brown;">Reset Assignments:&nbsp;&nbsp;
	<input id="ignore" type="checkbox" name="nocare" /></span></h3>
<?php
	echo '<label for="ctip">&nbsp;&nbsp;Cluster: </label>';
	echo '<select id="ctip" name="htool">';
	for ($i=0; $i<$grpCnt; $i++) {
		echo '<option value="' . $cnames[$i] . '">' . $cnames[$i] . '</option>';
	}
	echo '</select>&nbsp;&nbsp;';
?>
<span id="notclus" style="display:none;">There is no currently assigned cluster for this hike</span>
<input id="mrkrchg" type="hidden" name="chg2clus" value="NO" />
<input id="grpchg" type="hidden" name="chgd" value="NO" />
<p>If you are establishing a new group, select the checkbox: <input id="newg" type="checkbox"
	name="nxtg" value="NO" /> and enter the name for the new group here: <input id="newt" 
	type="text" name="newgname" size="50" /></p>
<p id="showdel" style="display:none;">You may remove the cluster assignment by checking here:&nbsp;&nbsp;
	<input id="deassign" type="checkbox" name="rmclus" value="NO" /></p>
<h3>------- End of Cluster Assignments</h3>

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
	$alpha = 30;	# insert-icon size
	$beta = 10;  # space between images
	$rowCnt = 0;
	$rows = array();
	$inserts = array();
	$insNo = 0;
	$picNo = 0;
	$nonCap = 0;
	for ($i=0; $i<6; $i++) {
		if ($info[29+$i] !== '') {
			/*
			 * START NEW ROW
			 */
			$imgDat = array();
			$rowDat = explode("^",$info[29+$i]);
			$noOfImgs = $rowDat[0];
			$firstMarg = 0;
			$lastMarg = $noOfImgs - 1;
			$noOfInserts = $noOfImgs + 1;
			$extraSpace = 2 * $alpha + 10 * ($noOfImgs - 1); // consumed by row spacing & insert icons
			$scale = (960 - $extraSpace)/960;
			# insert icons:
			$insRow = '<div id="insRow' . $rowCnt . '" class="ins">';
			$insRow .= '<img class="lead" style="float:left;" height="' . $alpha . '" width="' .
						$alpha . '" src="' . $loadIcon . '" alt="drop-point" />';
			$rowHt = floor($scale * $rowDat[1]);
			array_push($imgDat,$rowHt);
			$nxtIndx = 2;
			$rowHtml = '<div id="row' . $rowCnt . '" class="ImgRow" style="margin-left:30px;clear:both;">';
			$capTxt = array();
			for ($j=0; $j<$noOfImgs; $j++) {  // FOR EACH IMAGE IN THIS ROW...
				$sym = $rowDat[$nxtIndx];
				$strtImgWd = $rowDat[$nxtIndx+1];
				$imgWd = floor($scale * $strtImgWd);
				if ( $j === $firstMarg || $j === $lastMarg ) {
					$insPos = $imgWd - 10;   # use symbols instead of numbers....
				} else { 
					$insPos = $imgWd - 20;   # use symbols instead of numbers....
				}
				$insRow .= '<img style="float:left;margin-left:' . $insPos . 'px;" id="ins' . 
					$insNo . '" height="' . $alpha . '" width="' . $alpha . '" src="' . $loadIcon . 
					'" alt="drop-point" />';
				$insNo++;
				array_push($imgDat,$imgWd);
				if ($sym === 'p') {
					$rowHtml .= '<img id="pic' . $picNo . '" style="margin-right:' . $beta . 'px;" ' .
						'draggable="true" ondragstart="drag(event)" height="' . $rowHt . 
						'" width="' . $imgWd . '" src="' .
						$rowDat[$nxtIndx+2] . '" alt="' . $rowDat[$nxtIndx+3] . '" />';
					array_push($capTxt,$rowDat[$nxtIndx+3]);
					$picNo++;
					$nxtIndx += 4;
				} elseif ($sym === 'f') { // to make draggable, place inside draggable div
					$rowHtml .= '<div style="display:inline-block;margin-right:' . 
						$beta . 'px;" id="map0" draggable="true" ' .
						'ondragstart="drag(event)"><iframe id="theMap" height="' .$rowHt . 
						'" width="' . $imgWd . '" src="' . $rowDat[$nxtIndx+2] .'"></iframe></div>';
					$nxtIndx += 3;
				} else { 
					$rowHtml .= '<img id="nocap' . $nonCap . '" style="margin-right:' . $beta . 'px;" ' .
						'draggable="true" ondragstart="drag(event)" height="' . $rowHt . 
						'" width="' . $imgWd . '" src="' . $rowDat[$nxtIndx+2] . 
						'" alt="no Caption" />';
					$nonCap++;
					$nxtIndx += 3;
				}
			} // end of for creating images in row & inserts
			# have not yet saved $imgDat array....
			$rowHtml .= '</div>';
			array_push($rows,$rowHtml);
			$insRow .= '</div>';
			//echo $insRow;
			array_push($inserts,$insRow);
			$rowCnt++;
		}  # end of if 'row with images'
	}  # end of for all possible rows
	for ($j=0; $j<$rowCnt; $j++) {
		echo $inserts[$j];
		echo $rows[$j];
	}

	if ($info[37] !== '') {
		echo '<p>Tips Text: </p>';
		echo '<textarea id="ttxt" name="tips" rows="10" cols="130">' . $info[37] . '</textarea><br />';
	} else {
		echo '<textarea id="ttxt" name="tips" rows="10" cols="130">' . 
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
		echo '<option value="h">Website</option>'; # leftover category from index pages
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
				'</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
				'<input style="height:18px;width:18px;" type="checkbox" name="delprop[]" value="' .
					$i . '"><br /><br />';
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
				'</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
				'<input style="height:18px;width:18px;" type="checkbox" name="delact[]" value="' .
					$j . '"><br /><br />';
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

<script src="editDB.js"></script>
</body>
</html>