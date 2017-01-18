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
	<link href="../styles/hikes.css"
		type="text/css" rel="stylesheet" />
</head>

<body>

<div style="padding:16px;">
Edit the URLs or html code as desired, then click on the "Submit" button.
</div>
<div style="padding:16px;">
<?php
	$database = '../data/test.csv';
	$dbfile = file($database);
	$hikeNo = intval($_GET['hikeNo']);
	$info = str_getcsv($dbfile[$hikeNo]);
	$hikeName = $info[1];
	$photoURL1 = rawurldecode($info[23]);
	$photoURL2 = rawurldecode($info[24]);
	$dirs = rawurldecode($info[25]);
	$hikeTips = rawurldecode($info[37]);
	$hikeInfo = rawurldecode($info[38]);
	$refs = rawurldecode($info[39]);
	$pDat = rawurldecode($info[40]);
	$adat = rawurldecode($info[41]);
?>
<form action="saveChanges.php" method="POST">
	<em style="color:DarkBlue;">Any changes below will be made for the hike: "<?php echo $hikeName;?>". If no changes 
	are made you may either exit this page or hit the "sbumit" button.</em><br /><br />
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
	<textarea name="adat" rows="8" cols="130" wrap="hard"><?php echo $pDat;?></textarea><br /><br />
	
	<input type="hidden" name="hno" value="<?php echo $hikeNo;?>" />
	<input type="hidden" name="nme" value="<?php echo $hikeName;?>" />
	<input type="submit" value="Save Changes" />
</form>

</div>

</body>
</html>