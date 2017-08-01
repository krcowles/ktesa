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

    
    $hikeNo = filter_input(INPUT_POST,'hno');
    $hikeDat = simplexml_load_file('../data/database.xml');
    if ($hikeDat === false) {
        $nodb = '<p style="color:red;font-size:18px;margin-left:16px;">' .
                'Could not open database.xml to save edits: Contact Site Master</p>';
        die ($nodb);
    }
    foreach ($hikeDat->row as $hikeLine) {
        if ($hikeLine->indxNo == $hikeNo) {
            $hTitle = filter_input(INPUT_POST,'hname');
            $hLoc = filter_input(INPUT_POST,'locale');
            /*  CLUSTER/MARKER ASSIGNMENT PROCESSING:
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
                $hikeLine->marker = 'Normal';
                $hikeLine->clusGrp = '';
                $hikeLine->cgName = '';
            # 2.
            } elseif ( isset($nextGrp) && $nextGrp === 'YES' ) {	
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
                $hikeLine->marker = 'Cluster';
                $hikeLine->clusGrp = $newgrp;
                $hikeLine->cgName = filter_input(INPUT_POST,'newgname');
            # 3. (NOTE: marker will be assigned to 'Cluster' regardless of 
            #       whether previously cluster type or not
            } elseif ($grpChg  === 'YES') {
                $hikeLine->marker = 'Cluster';
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
                $hikeLine->clusGrp = $newgrp;
                $hikeLine->cgName = $newname;
            # 4.
            } else {
                # No Changes Assigned to marker, clusGrp, cgName
            }
            $hType = filter_input(INPUT_POST,'htype');
            $hLgth = filter_input(INPUT_POST,'hlgth');
            $hElev = filter_input(INPUT_POST,'helev');
            $hDiff = filter_input(INPUT_POST,'hdiff');
            $hFac = filter_input(INPUT_POST,'hfac');
            $hWow = filter_input(INPUT_POST,'hwow');
            $hSeas = filter_input(INPUT_POST,'hsea');
            $hExpos = filter_input(INPUT_POST,'hexp');
            $hLat = filter_input(INPUT_POST,'hlat');
            $hLon = filter_input(INPUT_POST,'hlon');
            $hPurl1 = filter_input(INPUT_POST,'purl1');
            $hPurl2 = filter_input(INPUT_POST,'purl2');
            $hDirs = filter_input(INPUT_POST,'gdirs');
            $hRows = $hikeLine->content;
            $hTips = filter_input(INPUT_POST,'htips');
            $hInfo = filter_input(INPUT_POST,'hinfo');
            $hRefs = $hikeLine->refs;
            $hProps = $hikeLine->dataProp;
            $hActs = $hikeLine->dataAct;
            break;
        }  # end of THE EDITED HIKE
    }  
    include "refEdits.php";
    include "propactEdits.php";
    include "picEdits.php";

    
    #ROW EDITING: RE-SCALE to final page row width
    for ($k=0; $k<6; $k++) {
        if ($rows[$k] !== '') {
            $resizedRow = scaleRow($rows[$k],$scale[$k]);
            $info[29+$k] = $resizedRow;
        } else {
            $info[29+$k] = '';
        }
    }

    $info[36] = filter_input(INPUT_POST,'editedLinks');
    $htips = filter_input(INPUT_POST,'tips');
    if (substr($htips,0,15) !== '[NO TIPS FOUND]') {
            $info[37] = $htips;
    } else {
            $info[37] = '';
    }

    
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