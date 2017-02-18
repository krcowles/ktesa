<?php
	$indexPage = $_POST['pgName'];
	$dirs = $_POST['gdirs'];
	$parkMap = $_POST['imap'];
	$indxDesc = $_POST['parkInfo'];
	$pkLat = $_POST['hlat'];
	$pkLon = $_POST['hlon'];
	$pkLocale = $_POST['hLocale'];
	# enc
	/* COLLECT REFERENCES FROM PREVIOUS PAGE */
	$refsHtml = '<ul id="refs">';
	$any = false;
	$bks = $_POST['bk'];
	$auths = $_POST['auth'];
	for ($j=0; $j<3; $j++) {
		if ($bks[$j] !== '') {
			$refsHtml = $refsHtml . '<li>Book: <em>' . $bks[$j] . '</em>, ' . $auths[$j] . '</li>';
			$any = true;
		}
	}
	$webs = $_POST['web'];
	$wtxt = $_POST['webtxt'];
	for ($k=0; $k<3; $k++) {
		if ($webs[$k] !== '') {
			$refsHtml = $refsHtml . '<li>Website: <a href="' . $webs[$k] . '" target="_blank">' . 
				$wtxt[$k] . '</a></li>';
			$any = true;
		}
	}
	if ($any) {
		$refsHtml = $refsHtml . '</ul>';
		$refEnc = rawurlencode($refsHtml);
	}
	$database = '../data/test.csv';
	$handle = fopen($database,"r");
	# table images:
	$webIcon = '<img class="webShift" src="../images/greencheck.jpg" alt="checkbox" />';
	$dirIcon = '<img src="../images/dirs.png" alt="google driving directions" />';
	$picIcon = '<img class="flckrShift" src="../images/album_lnk.png" alt="Flickr symbol" />';
	$sunIcon = '<img class="expShift" src="../images/sun.jpg" alt="Sunny icon" />';
	$partialIcon = '<img class="expShift" src="../images/greenshade.jpg" alt="Partial shade icon" />';
	$shadeIcon = '<img class="expShift" src="../images/shady.png" alt="Partial sun/shade icon" />';
	/* PULL IN ANY SELECTED HIKES FOR THE PAGE'S HIKE TABLE */
	$assocHikes = $_POST['tblList'];  //may be zero, one, or many - list of hike index nos.
	# echo "Number of hikes selected: " . count($assocHikes);
	$noInTable = count($assocHikes);
	if ($noInTable > 0) {
		$tblCode = '<table id="siteIndx"><thead><tr>';
		$tblCode = $tblCode . '<th class="hdrRow" scope="col">Trail</th>';
		$tblCode = $tblCode . '<th class="hdrRow" scope="col">Web Pg</th>';
		$tblCode = $tblCode . '<th class="hdrRow" scope="col">Trail Distance</th>';
		$tblCode = $tblCode . '<th class="hdrRow" scope="col">Elevation</th>';
		$tblCode = $tblCode . '<th class="hdrRow" scope="col">Exposure</th>';
		$tblCode = $tblCode . '<th class="hdrRow" scope="col">Photos</th>';
		$tblCode = $tblCode . '</tr></thead><tbody><tr>';
		# get hike data for each hike listed in the summary table:
		$first = true;
		for ($k=0; $k<$noInTable; $k++) {
			$selected = $assocHikes[$k];
			while ( ($hike = fgetcsv($handle)) !== false) {
				if ($selected == $hike[0]) {
					$trail = '<td>' . $hike[1] . '</td>';
					$hlnk = '<td><a href="../pages/hikePageTemplate.php?hikeIndx=' .
						$selected . '" target="_blank">' . $webIcon . '</a></td>' ;
					$lgth = '<td>' . $hike[7] . 'miles</td>';
					$elev = '<td>' . $hike[8] . 'ft</td>';
					$exp = $hike[13];
					if ($exp === 'Full sun') {
						$hikeExpIcon = '<td>' . $sunIcon . '</td>';
					} elseif ($exp === 'Mixed sun/shade') {
						$hikeExpIcon = '<td>' . $partialIcon . '</td>';
					} else {
						$hikeExpIcon = '<td>' . $shadeIcon . '</td>';
					}
					$purl = '<td><a href="' . $hike[23] . '" target="_blank">' . $picIcon. '</a></td>';
					#$hdirs = '<td><a href="' . $hike[25] . '" target="_blank">' . $dirIcon . '</a></td>';
					if ($first) {
						$clusStr = $selected;
						$first = false;
					} else {
						$clusStr = $clusStr . "." . $selected;
					}
					break;
				}
			}
			rewind($handle);
			$tblCode = $tblCode . $trail . $hlnk . $lgth . $elev . $hikeExpIcon . $purl . '</tr>';
		}
		$tblCode = $tblCode . '</tbody></table>';
		rewind($handle);
		# the Cluster Str for this park is formed of the associated hike index nos.	
	} else {
		$tblCode = '';
	}
	/* SAVE THE DATA TO THE DATABASE */
	while ( ($hikes = fgetcsv($handle)) !== false) {
		$lastIndx = $hikes[0];
	}
	fclose($handle);	
	$hikeNo = intval($lastIndx) + 1;
	$newIndx = array();
	for ($n=0; $n<42; $n++) {
		$newIndx[$n] = '';
	}  // required for implode to function properly
	$newIndx[0] = $hikeNo; 
	$newIndx[1] = $indexPage;
	$newIndx[2] = $pkLocale;
	$newIndx[3] = "Visitor Ctr";
	$newIndx[4] = $clusStr;
	$newIndx[19] = $pkLat;
	$newIndx[20] = $pkLon;
	$newIndx[21] = $parkMap;
	$newIndx[25] = $dirs;
	$newIndx[29] = $tblCode;
	$newIndx[38] = $indxDesc;
	$newIndx[39] = $refsHtml;
?>
<!DOCTYPE html>
<html>
<head>
	<title>Write Hike File</title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
		content="Write hike data to TblDB.csv" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="../styles/hikes.css"
		type="text/css" rel="stylesheet" />
</head>

<body>

<div style="margin-left:12px;padding:8px;">
<?php
	echo '<p style="margin:16px;">Name for this center is: ' . $indexPage . '</p>';
	echo '<p style="margin:16px;">The table below (if present) will consitute part of the saved Index Page:</p>';
	if ($noInTable > 0) {
		echo $tblCode;
	}
	$handle = fopen($database,"a");
	fputcsv($handle,$newIndx);
	fclose($handle);
?>
<h2 style="margin:16px;">Index Page Has Been Saved to the Database</h2>
</div>

<div data-ptype="index" data-indxno="<?php echo $hikeNo;?>" style="padding:16px;" id="more">
	<button style="font-size:16px;color:DarkBlue;" id="same">Edit this Index Page</button><br />
	<button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different Index Page</button>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="postEdit.js"></script>

</body>

</html>
	