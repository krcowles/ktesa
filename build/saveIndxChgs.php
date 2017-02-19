<!DOCTYPE html>
<html>
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
	$info[1] = $_POST['hname'];
	$info[2] = $_POST['locale'];
	# NOTE: $info[3], Marker, cannot be changed from Visitor Ctr to something else;
	# NOTE: $info[4], Cluster String, is not available for edit: use new hike creation
	$info[10] = $_POST['hfac'];
	$info[11] = $_POST['hwow'];
	$info[19] = $_POST['hlat'];
	$info[20] = $_POST['hlon'];
	$info[25] = $_POST['gdirs'];
	$info[29] = $_POST['tbl'];
	$info[38] = $_POST['info'];
	# Re-form references string array:
	$rawreftypes = $_POST['rtype'];
	$noOfRefs = count($rawreftypes);  // should always be 1 or greater
	$rawrit1 = $_POST['rit1'];
	$rawrit2 = $_POST['rit2'];
	# there will always be the same no of rtype's & rit1's, BUT NOT rit2's!
	$r2indx = 0;
    $rcnt = 0;
    $refStr = '';
    /* When reading an array of checkboxes, the array order is skewed with checked 
       boxes first: check to see if any current references are being deleted */
    $refDels = $_POST['delref'];
    $skips = array();
    for ($k=0; $k<$noOfRefs; $k++) {
    	$skips[$k] = false;
    }
    foreach ($refDels as $box) {
    	if ( isset($box) ) {
    		$indx = $box;
    		$skips[$indx] = true;
    	}
    }
	for ($j=0; $j<$noOfRefs; $j++) {		
		if (!$skips[$j]) {
			if ($rawreftypes[$j] === 'b' && $rawrit1[$j] === '') { // first added empty input
				break;
			} elseif ($rawreftypes[$j] === 'n') {
				$refStr .= '^' . $rawreftypes[$j] . '^' . $rawrit1[$j];
			} else {
				$refStr .= '^' . $rawreftypes[$j] . '^' . $rawrit1[$j] . '^' . $rawrit2[$r2indx];
				$r2indx++;
			}
			$rcnt++;
        } else {
        	if ($rawreftypes[$j] !== 'n') {
        		$r2indx++;
        	}
        }
	}
	$info[39] = $rcnt . $refStr;
	
	
	/* WRITE OUT THE NEW INDEX PAGE */
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
	<button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different Index Page</button><br />
	<button style="font-size:16px;color:DarkBlue;" id="view">View Changed Page</button>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="postEdit.js"></script>


</body>

</html>