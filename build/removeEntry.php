<!DOCTYPE html>
<html>
<head>
	<title>Save Database Deletion</title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
		content="Reomve a line in the database" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="../styles/hikes.css"
		type="text/css" rel="stylesheet" />
</head>

<body>	

<?php
	$database = '../data/database.csv';
	$dbhandle = fopen($database,"r");	
	$pageNo = $_GET['deleteNo'];
	$delNo = intval($pageNo);
	$wholeDB = array();
	$dbindx = 0;
	while ( ($hikeDat = fgetcsv($dbhandle)) !== false ) {
		$wholeDB[$dbindx] = $hikeDat;
		$dbindx++;
	}
	fclose($dbhandle);
	$deleted = $wholeDB[$delNo];
	
	# Now eliminate the one page and save;
	# NOTE: index numbers are reassigned... !!
	$dbhandle = fopen($database,"w");
	$newIndx = 0;
	foreach ($wholeDB as $hikedat) {
		if ($hikedat[0] !== $pageNo) {
			if (trim($hikedat[0]) !== 'Indx#') {
				$hikedat[0] = $newIndx;
				$newIndx++;
			}
			fputcsv($dbhandle,$hikedat);
		}
	}
	fclose($dbhandle);
?>
<div style="padding:16px;font-size:16px;">
The page [<?php echo $deleted[1];?>] has been deleted from the database
</div>

</body>

</html>