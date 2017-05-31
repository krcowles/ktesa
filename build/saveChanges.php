<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Save Changes to Database</title>
    <meta charset="utf-8" />
    <meta name="description"
            content="Save any hike page changes back to the database" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="../styles/hikes.css" type="text/css" rel="stylesheet" />
</head>

<body>

<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>

    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Saving Hike Page Edits</p>

<?php
    # Function to resize rows to standard page width
    function scaleRow($rowString,$scaling) {
        $rowEls = explode("^",$rowString);
        $noOfImgs = $rowEls[0];
        array_shift($rowEls);
        $oldht = intval($rowEls[0]);
        $newHeight = floor($scaling * $oldht);
        array_shift($rowEls);
        # remaining $rowEls are images
        $indx = 0;
        $returnString = '';
        for ($j=0; $j<$noOfImgs; $j++) {
            $itype = $rowEls[$indx];
            $owdth = $rowEls[$indx+1];
            $iwdth = floor($scaling * $owdth);
            $isrc = $rowEls[$indx+2];
            if ($itype === 'p') {
                $returnString .= '^p^' . $iwdth . '^' . $isrc . '^' . 
                   $rowEls[$indx+3];
                $indx += 4;
            } else {
                $returnString .= '^n^' . $iwdth . '^' . $isrc;
                $indx += 3;
            }
        }
        $returnString = $noOfImgs . "^" . $newHeight . $returnString;
        return $returnString;
    }
    # END FUNCTION
    
    $database = '../data/database.csv';
    $dbhandle = fopen($database,"r");	
    $hikeNo = filter_input(INPUT_POST,'hno');
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
    $info[1] = filter_input(INPUT_POST,'hname');
    $info[2] = filter_input(INPUT_POST,'locale');	
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
    $delClus = filter_input(INPUT_POST,'rmclus');
    $nextGrp = filter_input(INPUT_POST,'nxtg');
    $grpChg = filter_input(INPUT_POST,'chgd');
    if ( isset($delClus) && $delClus === 'YES' ) {
        $info[3] = 'Normal';
        $info[5] = '';
        $info[28] = '';
    } elseif ( isset($nextGrp) && $nextGrp === 'YES' ) {	
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
        $info[28] = filter_input(INPUT_POST,'newgname');
    } elseif ($grpChg  === 'YES') {
    # 3. (NOTE: marker can be assigned to 'Cluster' regardless of whether previously cluster type
        $info[3] = 'Cluster';
        $newname = filter_input(INPUT_POST,'htool');
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
    $info[6] = filter_input(INPUT_POST,'htype');
    $info[7] = filter_input(INPUT_POST,'hlgth');
    $info[8] = filter_input(INPUT_POST,'helev');
    $info[9] = filter_input(INPUT_POST,'hdiff');
    $info[10] = filter_input(INPUT_POST,'hfac');
    $info[11] = filter_input(INPUT_POST,'hwow');
    $info[12] = filter_input(INPUT_POST,'hsea');
    $info[13] = filter_input(INPUT_POST,'hexp');
    $info[19] = filter_input(INPUT_POST,'hlat');
    $info[20] = filter_input(INPUT_POST,'hlon');
    $info[23] = filter_input(INPUT_POST,'purl1');
    $info[24] = filter_input(INPUT_POST,'purl2');
    $info[25] = filter_input(INPUT_POST,'gdirs');

    include('formRowStrings.php');  /* $rows array is formed from passed strings
     * along with the scaling factor to be applied to restore the row to 
     * the standard page width (950 = 960 - margin)
     */
    
    #ROW EDITING: RE-SCALE to final page row width
    for ($k=0; $k<6; $k++) {
        if ($rows[$k] !== '') {
            $resizedRow = scaleRow($rows[$k],$scale[$k]);
            $info[29+$k] = $resizedRow;
        } else {
            $info[29+$k] = '';
        }
    }
    /* CAPTIONS - no longer used
    $noOfCaps = count($capts);
    $dbCaps = implode("^",$capts);
    $info[35] = $noOfCaps . "^" . $dbCaps;
     */

    $htips = filter_input(INPUT_POST,'tips');
    if (substr($htips,0,15) !== '[NO TIPS FOUND]') {
            $info[37] = $htips;
    } else {
            $info[37] = '';
    }
    $info[38] = filter_input(INPUT_POST,'hinfo');
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
    $rawprops = $_POST['plabl'];  // these three are arrays
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
    $rawacts = $_POST['alabl'];  // these three are arrays
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
    
    /* Save changes based on whether or not site master: registered users
     * will have a temporary database change saved for review by the site
     * master, to be integrated with the site after acceptance. The temp
     * database will contain only the modified page, not the entire db.
     * NOTE: THIS IS PRELIMINARY AND BY NO MEANS A VETTED USER PROCESS!!!!
     */
    $user = true;
    if (filter_input(INPUT_POST,'savePg') === 'Site Master') {
        $passwd = filter_input(INPUT_POST,'mpass');
        if ($passwd !== '000ktesa') {
            die('<p style="color:brown;">Incorrect Password - save not executed</p>');
        }
        $user = false;
        $msgout = " have been saved on the site";
        $dbhandle = fopen($database,"w");
        foreach ($wholeDB as $hikedat) {
            if ($hikedat[0] == $hikeNo) {
                fputcsv($dbhandle,$info);
            } else {
                fputcsv($dbhandle,$hikedat);
            }
        }
        fclose($dbhandle);
    } else if (filter_input(INPUT_POST,'savePg') === 'Submit for Review') {
        $database = '../data/reviewdat.csv';
        $dbhandle = fopen($database,"a");
        fputcsv($dbhandle,$info);
        fclose($dbhandle);
        $msgout = " have been submitted for review by the site masters";
    } else {
        die('<p style="color:brown;">Contact Site Master: Submission not recognized');
    } 
    
?>
<div style="padding:16px;">
<h2>The changes submitted for <?php echo $info[1] . $msgout;?></h2>
</div>

<?php
if (!$user) {
    echo '<div data-ptype="hike" data-indxno="' . $hikeNo . '" style="padding:16px;" id="more">';
    echo '<button style="font-size:16px;color:DarkBlue;" id="same">Re-edit this hike</button><br />';
    echo '<button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different hike</button><br />';
    echo '<button style="font-size:16px;color:DarkBlue;" id="view">View the Edited Page</button>';
    echo '</div>';
}
?>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="postEdit.js"></script>

</body>

</html>