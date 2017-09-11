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
    $database = '../data/database.xml';
    $hikeNo = filter_input(INPUT_POST,'hno');
    $hikeDat = simplexml_load_file($database);
    if ($hikeDat === false) {
        $nodb = '<p style="color:red;font-size:18px;margin-left:16px;">' .
                'Could not open database.xml to save edits: Contact Site Master</p>';
        die ($nodb);
    }
    # Extract cluster list - and inherent 1 to 1 association with cluster name:
    $groups = [];
    $cnames = [];
    foreach ($hikeDat->row as $hikeRow) {
        $cgrp = $hikeRow->clusGrp->__toString();
        if ( strlen($cgrp) !== 0) {
            # no duplicates please (NOTE: "array_unique" leaves holes)
            $match = false;
            for ($i=0; $i<count($groups); $i++) {
                if ($cgrp == $groups[$i]) {
                    $match = true;
                    break;
                }  
            }
            if (!$match) {
                $grpName = $hikeRow->cgName->__toString();
                array_push($groups,$cgrp);
                array_push($cnames,$grpName);
            }
        }
    }
    # end of cluster group assignment extraction
    foreach ($hikeDat->row as $hikeLine) {
        if ($hikeLine->indxNo == $hikeNo) {
            $hTitle = filter_input(INPUT_POST,'hname');
            $hikeLine->pgTitle = $hTitle;
            $hLoc = filter_input(INPUT_POST,'locale');
            $hikeLine->locale = $hLoc;
            /*  CLUSTER/MARKER ASSIGNMENT PROCESSING:
             *     The order of changes processed are in the following priority:
             *     1. Existing assignment deleted: Marker changes to "Normal"
             *	   2. New Group Assignment
             *     3. Group Assignment Changed
             *     4. Nothing Changed
            */
            $delClus = filter_input(INPUT_POST,'rmclus');
            $nextGrp = filter_input(INPUT_POST,'nxtg');
            $grpChg = filter_input(INPUT_POST,'chgd');
            # 1.
            if ( isset($delClus) && $delClus === 'YES' ) {
                $hikeLine->marker = 'Normal';
                $hikeLine->clusGrp = '';
                $hikeLine->cgName = '';
            # 2.
            } elseif ( isset($nextGrp) && $nextGrp === 'YES' ) {	
                $availLtrs = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $doubleLtrs = 'AABBCCDDEEFFGGHHIIJJKKLLMMNNOOPPQQRRSSTTUUVVWWXXYYZZ';
                # add another group of letters later if needed
                $nextmem = filter_input(INPUT_POST,'grpcnt',FILTER_SANITIZE_NUMBER_INT);
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
                for ($i=0; $i<count($cnames); $i++) {
                    if ($cnames[$i] == $newname) {
                        $newgrp = $groups[$i];
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
            $hikeLine->logistics = $htype;
            $hLgth = filter_input(INPUT_POST,'hlgth');
            $hikeLine->miles = $hLgth;
            $hElev = filter_input(INPUT_POST,'helev');
            $hikeLine->feet = $hElev;
            $hDiff = filter_input(INPUT_POST,'hdiff');
            $hikeLine->difficulty = $hDiff;
            $hFac = filter_input(INPUT_POST,'hfac');
            $hikeLine->facilities = $hFac;
            $hWow = filter_input(INPUT_POST,'hwow');
            $hikeLine->wow = $hWow;
            $hSeas = filter_input(INPUT_POST,'hsea');
            $hikeLine->seasons = $hSeas;
            $hExpos = filter_input(INPUT_POST,'hexp');
            $hikeLine->expo = $hExpos;
            $hLat = filter_input(INPUT_POST,'hlat');
            $hikeLine->lat = $hLat;
            $hLon = filter_input(INPUT_POST,'hlon');
            $hikeLine->lng = $hLon;
            $hPurl1 = filter_input(INPUT_POST,'purl1');
            $hikeLine->mpUrl = $hPurl1;
            $hPurl2 = filter_input(INPUT_POST,'purl2');
            $hikeLine->spUrl = $hPurl2;
            $hDirs = filter_input(INPUT_POST,'gdirs');
            $hikeLine->dirs = $hDirs;
            $hTips = filter_input(INPUT_POST,'tips');
            # Save captions:
            $caps = $_POST['ecap'];
            $p = 0;
            foreach ($hikeLine->tsv->picDat as $photo) {
                if ($photo->hpg == 'Y') {
                    $photo->desc = $caps[$p];
                    $p++;
                }
            }
            # delete any checked photos from the database:
            $dels = $_POST['delpic']; # only passes the CHECKED boxes!
            $pcnt = $hikeLine->tsv->picDat->count() - 1;
            $delno = count($dels) - 1;
            for ($p=$pcnt; $p>=0; $p--) {
                if ( $delno >= 0 && $dels[$delno] == $p ) {
                    #echo "POST: " . $dels[$delno] . "->" . $hikeLine->tsv->picDat[$p]->desc . "; <br />";
                    $hikeLine->tsv->picDat[$p]->hpg = "N";
                    #unset($hikeLine->tsv->picDat[$p]);
                    $delno--;
                }
            }
            # revise tips if no tips were added:
            if (substr($hTips,0,15) === '[NO TIPS FOUND]') {
                    $hTips = '';
            }
            $hikeLine->tipsTxt = $hTips;
            $hInfo = filter_input(INPUT_POST,'hinfo');
            $hikeLine->hikeInfo = $hInfo;
            include "refEdits.php";
            include "propactEdits.php";
            $hikeDat->asXML($database);
            break;
        }  # end of THE EDITED HIKE
    }  
        
?>
<div style="padding:16px;">
<h2>The changes submitted for <?php echo $hTitle . $msgout;?></h2>
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