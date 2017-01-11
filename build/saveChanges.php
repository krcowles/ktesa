<head>
	<title>Save Changes to Database</title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
		content="Edit a given hike" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="../styles/hikes.css"
		type="text/css" rel="stylesheet" />
</head>

<body>

<?php
	$database = '../data/test.csv';
	$dbfile = file($database);
	if (count($dbfile) < 10) {
		die("did not retrieve data base file!");
	}	
	$hikeNo = intval($_POST['hno']);
	$hikeName = $_POST['nme'];
	# get the hike number's line and convert to array
	$info = str_getcsv($dbfile[$hikeNo]);
	# NOTE: when I tried to save via "rawurlencode($string), it wouldn't work, but
	#	    if I encoded first, then saved, it worked... ???
	$purlA = $_POST['purl1'];
	$encPurl1 = rawurlencode($purlA);
	$purlB = $_POST['purl2'];
	$encPurl2 = rawurlencode($purlB);
	$googleDirs = $_POST['gdirs'];
	$encGDirs = rawurlencode($googleDirs);
	$tips = $_POST['tips'];
	$encTips = rawurlencode($tips);
	$hikeInfo = $_POST['info'];
	$encInfo = rawurlencode($hikeInfo);
	$hrefs = $_POST['refs'];
	$encRefs = rawurlencode($hrefs);
	$pdat = $_POST['pdat'];
	$encpDat = rawurlencode($pdat);
	$adat = $_POST['adat'];
	$encaDat = rawurlencode($adat);
	$info[23] = $encPurl1;
	$info[24] = $encPurl2;
	$info[25] = $encGDirs;
	$info[37] = $encTips;
	$info[38] = $encInfo;
	$info[39] = $encRefs;
	$info[40] = $encpDat;
	$info[41] = $encaDat;
	# convert info back to a csv string and replace the old hike line with the new
	$replace = implode(",",$info);
	$dbfile[$hikeNo] = $replace."\n";
	$newfile = implode($dbfile);
	$dbhandle = fopen($database,"w");
	fputs($dbhandle, $newfile."\n");
	fclose($dbhandle);
?>
<div style="padding:16px;">
<h2>The changes submitted for <?php echo $hikeName;?> (if any) have been saved to the database.</h2>
</div>


</body>

</html>