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
	$indxName = $_POST['nme'];
	# get the hike number's line and convert to array
	$info = str_getcsv($dbfile[$hikeNo]);
	# NOTE: when I tried to save via "rawurlencode($string), it wouldn't work, but
	#	    if I encoded first, then saved, it worked... ???
	$googleDirs = $_POST['gdirs'];
	$encGDirs = rawurlencode($googleDirs);
	$indxInfo = $_POST['info'];
	$encInfo = rawurlencode($indxInfo);
	$indxRefs = $_POST['ref'];
	$encRefs = rawurlencode($indxRefs);
	$indxTbl = $_POST['tbl'];
	$encTbl = rawurlencode($indxTbl);
	$info[25] = $encGDirs;
	$info[38] = $encInfo;
	$info[39] = $encRefs;
	$info[29] = $encTbl;
	# convert info back to a csv string and replace the old hike line with the new
	$replace = implode(",",$info);
	$dbfile[$hikeNo] = $replace."\n";
	$newfile = implode($dbfile);
	$dbhandle = fopen($database,"w");
	fputs($dbhandle, $newfile);
	fclose($dbhandle);
?>
<div style="padding:16px;">
<h2>The changes submitted for <?php echo $indxName;?> (if any) have been saved to the database.</h2>
</div>

<div data-ptype="index" data-indxno="<?php echo $hikeNo;?>" style="padding:16px;" id="more">
	<button style="font-size:16px;color:DarkBlue;" id="same">Re-edit this Index Page</button><br />
	<button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different Index Page</button>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="postEdit.js"></script>


</body>

</html>