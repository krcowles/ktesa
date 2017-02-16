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
	$db = fopen($database,"r");	
	$hikeNo = $_POST['hno'];
	$indxName = $_POST['nme'];
	$wholeDB = array();
	$windx = 0;
	while ( ($info = fgetcsv($db)) !== false ) {
		$wholeDB[$windx] = $info;
		$windx++;
	}
	fclose($db);
	foreach ($wholeDB as $hikeline) {
		if ($hikeline[0] == $hikeNo) {
			$info = $hikeline;
			break;
		}
	}
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
	$dbhandle = fopen($database,"w");
	foreach ($wholeDB as $hikedat) {
		if ($hikedat[0] == $hikeNo) {
			fputcsv($dbhandle,$info);
		} else {
			fputcsv($dbhandle,$hikedat);
		}
  
	}
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