<?php session_start(); ?>
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
	function scaleRow($rowArray,$indx,&$heights) {
		$oldWidth = 0;
		foreach( $rowArray as $istr ) {
			$strArray = explode("^",$istr);
			$oldWidth += intval($strArray[1]);
		}
		$newscale = 950/$oldWidth;
		# adjust the row height:
		$heights[$indx] = floor($newscale * $heights[$indx]);
		# scale each image width:
		foreach( $rowArray as &$imgStr ) {
			$strArray = explode("^",$imgStr);
			$oldWidth = intval($strArray[1]);
			$newWidth = floor($newscale * $oldWidth);
			$strArray[1] = $newWidth;
			$imgStr = implode("^",$strArray);
		}
		return implode("^",$rowArray);
	}
	$database = '../data/database.csv';
	$dbhandle = fopen($database,"r");	
	$hikeNo = $_POST['hno'];
	$wholeDB = array();
	$dbindx = 0;
	while ( ($hikeDat = fgetcsv($dbhandle)) !== false ) {
		$wholeDB[$dbindx] = $hikeDat;
		$dbindx++;
	}
	fclose($dbhandle);
	foreach ($wholeDB as $hikeLine) {
		if ($hikeLine[0] == $hikeNo) {
			$info = $hikeLine;
			break;
		}
	}
	$info[1] = $_POST['hname'];
	$info[2] = $_POST['locale'];	
	/*  CLUSTER ASSIGNMENT PROCESSING 
	 *     The order of changes processed are in the following priority:
	 *     1. Existing assignment deleted: Marker changes to "Normal"
	 *	   2. New Group Assignment
	 *     3. Group Assignment Changed
	 *     4. Nothing Changed
	 */
	$clusters = $_SESSION['allClusters'];
	$clusArray = explode(";",$clusters);
	# 1.
	if ( isset($_POST['rmclus']) && $_POST['rmclus'] === 'YES' ) {
		$info[3] = 'Normal';
		$info[5] = '';
		$info[28] = '';
	} elseif ( isset($_POST['nxtg']) && $_POST['nxtg'] === 'YES' ) {	
	# 2.
		$availLtrs = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$doubleLtrs = 'AABBCCDDEEFFGGHHIIJJKKLLMMNNOOPPQQRRSSTTUUVVWWXXYYZZ';
		# add another group of letters later if needed
		$nextmem = count($clusArray);
		# group letters are assigned sequentially
		if ($nextmem < 26) {
			$newgrp = substr($availLtrs,$nextmem,1);
		} else {
			#assign from doubleLtrs:
			$pos = 2*($nextmem - 26);
			$newgrp = substr($doubleLtrs,$pos,2);
		}  # elseif more groups of letters are added later...
        $info[3] = 'Cluster';
		$info[5] = $newgrp;
		$info[28] = $_POST['newgname'];
	} elseif ( $_POST['chgd'] === 'YES' ) {
	# 3. (NOTE: marker can be assigned to 'Cluster' regardless of whether previously cluster type
		$info[3] = 'Cluster';
		$newname = $_POST['htool'];
		# get association with group letter
		for ($i=0; $i<count($clusArray); $i++) {
			$dollarpos = strpos($clusArray[$i],"$") + 1;
			$nmLgth = strlen($clusArray[$i]) - $dollarpos;
			$cname = substr($clusArray[$i],$dollarpos,$nmLgth);
			if ($cname == $newname) {
				# get group assignment:
				$grpLgth = $dollarpos - 1;
				$newgrp = substr($clusArray[$i],0,$grpLgth);
				break;
			}
		}
		$info[5] = $newgrp;
		$info[28] = $newname;
	} else {
	# 4.
		# No Changes Assigned to $info[3,5,28]
	}
	
	# $info[4] is string for index pages only
	$info[6] = $_POST['htype'];
	$info[7] = $_POST['hlgth'];
	$info[8] = $_POST['helev'];
	$info[9] = $_POST['hdiff'];
	$info[10] = $_POST['hfac'];
	$info[11] = $_POST['hwow'];
	$info[12] = $_POST['hsea'];
	$info[13] = $_POST['hexp'];
	$info[19] = $_POST['hlat'];
	$info[20] = $_POST['hlon'];
	$info[23] = $_POST['purl1'];
	$info[24] = $_POST['purl2'];
	$info[25] = $_POST['gdirs'];
	
	include('formRowStrings.php');
	/* ROW EDITING: RE-SCALE to 950  (960 - margin) */
	$info[29] = $rows[0];
	$info[30] = $rows[1];
	$info[31] = $rows[2];
	$info[32] = $rows[3];
	$info[33] = $rows[4];
	$info[34] = $rows[5];
	
	
	$htips = $_POST['tips'];
	if (substr($htips,0,15) !== '[NO TIPS FOUND]') {
		$info[37] = $htips;
	} else {
		$info[37] = '';
	}
	$info[38] = $_POST['hinfo'];
	# Re-assemble ref string
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
	# Re-assemble Proposed Data String
	$rawprops = $_POST['plabl'];
	$rawplnks = $_POST['plnk'];
	$rawpctxt = $_POST['pctxt'];
	$noOfPs = count($rawprops);
    $pcnt = 0;
    $propStr = '';
	if ($rawprops[0] !== '') {
		$skips = array();
		$delProps = $_POST['delprop'];
		for ($n=0; $n<$noOfPs; $n++) {
			$skips[$n] = false;
		}
		foreach ($delProps as $box) {
			if ( isset($box) ) {
				$indx = $box;
				$skips[$indx] = true;
			}
		}
		for ($k=0; $k<$noOfPs; $k++) {
			if (!$skips[$k]) {
                if ($rawprops[$k] === '') { // first empty props box added
                    break;
            	} else {
                    $propStr .= '^' . $rawprops[$k] . '^' . $rawplnks[$k] . '^' . $rawpctxt[$k];
            	}
            	$pcnt++;
            } // end of not skipped
		}
		if ($pcnt > 0) {
        	$propStr = $pcnt . $propStr;
        } else {
        	$propStr = '';
        }
	}  // end of processing proposed data, if present
	$info[40] = $propStr;
	# Re-assemble Acutal Data String
	$rawacts = $_POST['alabl'];
	$rawalnks = $_POST['alnk'];
	$rawactxt = $_POST['actxt'];
	$noOfAs = count($rawacts);
    $acnt = 0;
    $actStr = '';
	if ($rawacts[0] !== '') {
		$skips = array();
		$delActs = $_POST['delact'];
		for ($m=0; $m<$noOfAs; $m++) {
			$skips[$m] = false;
		}
		foreach ($delActs as $box) {
			if ( isset($box) ) {
				$indx = $box;
				$skips[$indx] = true;
			}
		}
		for ($i=0; $i<$noOfAs; $i++) {
			if (!$skips[$i]) {
				if ($rawacts[$i] === '') {  // first empty actual data box
					break;
				} else {
					$actStr .= '^' . $rawacts[$i] . '^' . $rawalnks[$i] . '^' . $rawactxt[$i];
				}
				$acnt++;
			}
        }
        if ($acnt > 0) {
        	$actStr = $acnt . $actStr;	
        } else {
        	$actStr = '';
        }
	}  // end of actual data processing, if present
	$info[41] = $actStr;
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
<h2>The changes submitted for <?php echo $info[1];?> (if any) have been saved to the database.</h2>
</div>

<div data-ptype="hike" data-indxno="<?php echo $hikeNo;?>" style="padding:16px;" id="more">
	<button style="font-size:16px;color:DarkBlue;" id="same">Re-edit this hike</button><br />
	<button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different hike</button><br />
	<button style="font-size:16px;color:DarkBlue;" id="view">View the Edited Page</button>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="postEdit.js"></script>

</body>

</html>