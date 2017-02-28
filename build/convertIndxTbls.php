<?php
$indxPgNo = $_GET['hikeNo'];
$wholeDB = array();
$dbIndx = 0;
if ( ($dbhandle = fopen('../data/database.csv',"r")) !== false ) {
	while ( ($hikedat = fgetcsv($dbhandle)) !== false ) {
		$wholeDB[$dbIndx] = $hikedat;
		$dbIndx++;
	}
} else {
	echo "Failed to open database.csv";
}
fclose($dbhandle);
# find the index page to convert:
foreach ($wholeDB as $hikeline) {
	if ($hikeline[0] == $indxPgNo) {
		$indxPage = $hikeline;
		break;
	}
}

# Now convert the table
$htmlTbl = $indxPage[29];
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
$indxPage[29] = $tbldat;

/* WRITE OUT THE NEW INDEX PAGE */
$dbhandle = fopen('../data/database.csv',"w");
foreach ($wholeDB as $hikedat) {
	if ($hikedat[0] == $indxPgNo) {
		fputcsv($dbhandle,$indxPage);
	} else {
		fputcsv($dbhandle,$hikedat);
	}

}
fclose($dbhandle);
?>
<!DOCTYPE html>
<html>

<head>
	<title>Indx Pg Converter</title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
		content="Converting tables to array strings" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="../styles/960_16_col.css"
		type="text/css" rel="stylesheet" />
	<link href="../styles/hikes.css"
		type="text/css" rel="stylesheet" />
</head>

<body>

<p>Work converting index page tales in progress...</p>
<p>New database.csv saved</p>
</body>
</html>