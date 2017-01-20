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
	$indxName = $info[1];
	$dirs = rawurldecode($info[25]);
	$indxInfo = rawurldecode($info[38]);
	$refs = rawurldecode($info[39]);
	$indxTbl = rawurldecode($info[29]);
?>
<form action="saveIndxChgs.php" method="POST">
	<em style="color:DarkBlue;">Any changes below will be made for the Index Page: "<?php echo $indxName;?>". If no changes 
	are made you may either exit this page or hit the "sbumit" button.</em><br /><br />
	Enter or change the Google Maps Directions URL [NOTE: this is a single line, despite text-wrapping]<br />
	<textarea name="gdirs" rows="1" cols="130" wrap="soft"><?php echo $dirs;?></textarea><br /><br />
	Edit the hike information as desired:<br />
	<textarea name="info" rows="20" cols="130" wrap="hard"><?php echo $indxInfo;?></textarea><br /><br />
	Edit the following references list:<br />
	<textarea name="ref" rows="12" cols="130" wrap="hard"><?php echo $refs;?></textarea><br /><br />
	Edit the table of associated hikes:<br />
	<textarea name="tbl" rows="20" cols="130" wrap="hard"><?php echo $indxTbl;?></textarea>
	
	<input type="hidden" name="hno" value="<?php echo $hikeNo;?>" />
	<input type="hidden" name="nme" value="<?php echo $indxName;?>" />
	<input type="submit" value="Save Changes" />
</form>

</div>

</body>
</html>