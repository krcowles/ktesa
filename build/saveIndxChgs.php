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
	$database = '../data/database.csv';
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
	
	/* Convert the html table back into a string array: code copied from 'convertIndxTbls.php' */
	$htmlTbl = $info[29];
	# form an array of rows in the table
	$bodyStart = strpos($htmlTbl,"<tbody>");
	$bodyLgth = strlen($htmlTbl) - $bodyStart;
	$body = substr($htmlTbl,$bodyStart,$bodyLgth);
	$rowcount = substr_count($body,"<tr"); # NOTE, some rows are <tr class= (space after tr)
	$array_strings = array();
	for ($i=0; $i<$rowcount; $i++) {
		#isolate the next row:
		$rowend = strpos($body,"</tr>") + 5;
		$newBodyLgth = strlen($body) - $rowend;
		$row = substr($body,0,$rowend);
		#strip off row for next $body:
		$body = substr($body,$rowend,$newBodyLgth);
		# now process the row and push data into the rows array
		# NOTE: grayed out rows contain different data!
		if (strpos($row,'class="gray"') === false) {
			$gray = false;
			$rowStr = 'n^';
		} else {
			$gray = true;
			$rowStr = 'g^';
		}
		for ($j=0; $j<6; $j++) { # each row will have exactly 6 pieces of data
			if ($j === 0 || $j === 2 || $j === 3) {  # text only - same whether or not gray
				#echo " :text ";
				$td = strpos($row,"<td>") + 4;
				$tdend = strpos($row,"</td>");
				$elLgth = $tdend - $td;
				$rowStr .= substr($row,$td,$elLgth) . "^";
				#echo $rowStr;
				# strip off this data for next row item:
				$newLgth = strlen($row) - $tdend;
				$row = substr($row,$tdend+5,$newLgth-5);
			} elseif ($j === 4) {  # icon source
				if ($gray) {
					$rowStr .= "N^";
				} else {
					#echo " :icon ";
					$td = strpos($row,"src=") + 5;
					$tdend = strpos($row,'" alt');
					$elLgth = $tdend - $td;
					$rowStr .= substr($row,$td,$elLgth) . "^";
				}
				#echo $rowStr;
				$elend = strpos($row,'</td>') + 5;
				$newLgth = strlen($row) - $elend;
				$row = substr($row,$elend,$newLgth);
			} else {  # link
				if ( $j === 1 && $gray ) {	
					$rowStr .= "X^";
				} elseif ($j === 5 && $gray)  {
					$rowStr .= "X";
				} else {   # j=1,5
					#echo " :link ";
					$td = strpos($row,"href=") + 6;
					$tdend = strpos($row,'" target');
					$elLgth = $tdend - $td;
					if ($j === 5) {
						$rowStr .= substr($row,$td,$elLgth);
					} else {
						$rowStr .= substr($row,$td,$elLgth) . "^";
					}
				}
				#echo $rowStr;
				$elend = strpos($row,"</td>") + 5;
				$newLgth = strlen($row) - $elend;
				$row = substr($row,$elend,$newLgth);
			}
		}  // end of <td> processing for loop
		array_push($array_strings,$rowStr);
	}  // end of row-by-row for loop
	# replace table with new array strings:
	$tbldat = implode("|",$array_strings);
	$info[29] = $tbldat;
	
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