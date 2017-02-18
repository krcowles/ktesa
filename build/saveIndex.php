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
	for ($j=0; $j<41; $j++) {
		$indx[$j] = '';
	}
	# NOTE: when retrieving an array, all the values/checks/etc are listed first, out-of-order
	$import = $_POST['indx'];
	$indx[0] = $import[0];
	$indx[1] = $import[1];
	$indx[2] = $import[2];
	$indx[3] = $import[3];
	$indx[10] = $import[4];
	$indx[11] = $import[5];
	$indx[19] = $import[6];
	$indx[20] = $import[7];
	$indx[21] = $import[8];
	$indx[25] = $import[9];
	$indx[38] = $import[10];
	$indx[39] = $import[11];
	ksort($indx, SORT_NUMERIC);
	echo '<p style="margin:16px;">Name for this center is: ' . $indx[0] . '</p>';
	$database = '../data/test.csv';
	$handle = fopen($database,"a");
	fputcsv($handle,$indx);
	fclose($handle);
?>
<h2 style="margin:16px;">Index Page Has Been Saved to the Database</h2>
</div>

<div data-ptype="index" data-indxno="<?php echo $indx[0];?>" style="padding:16px;" id="more">
	<button style="font-size:16px;color:DarkBlue;" id="same">Edit this Index Page</button><br />
	<button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different Index Page</button><br />
	<button style="font-size:16px;color:DarkBlue;" id="view">View this completed page</button>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="postEdit.js"></script>

</body>

</html>
	