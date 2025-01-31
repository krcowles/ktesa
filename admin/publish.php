<?php
/**
 * This script will  'publish' an in-edit hike or cluster page by transferring
 * the data from EHIKES and E-Tables to the HIKES and Hike Tables. The hike will
 * no longer appear in the 'in-edit' list. If the EHIKE has a cluster assignment
 * in 'cname', then update CLUSHIKES. For new Cluster pages, update the CLUSTERS
 * 'page' field with the new HIKES indxNo: already published cluster pages need
 * no adjustment in this field.
 * NOTE: There have been instances where the script did not complete, leaving
 * file status in an unknown state and making it difficult to complete the transfers.
 * For this reason, a script "actions.txt" tracks progress, files deleted, etc so
 * that the admin can more effectively troubleshoot and complete the publish.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$actions_in_progress = '';

$hikeNo      = filter_input(INPUT_GET, 'hno');
$clusterPage = isset($_GET['clus']) && $_GET['clus'] === 'y' ? true : false;
$msgout      = '';
$type        = 'Edited Page';

// Additional files to assist admin when downloaded the published page:
// If file records already exist, prepare to add to them when published
if (file_exists("deleted.txt")) { // won't exist for cluster pages
    $pubDeletes  = file_get_contents("deleted.txt");
    $prevDeletes = explode(",", $pubDeletes);
} else {
    $prevDeletes = [];
}
if (file_exists("changed.txt")) {
    $pubChanges = file_get_contents("changed.txt");
    $prevChgs   = explode(",", $pubChanges);
} else {
    $prevChgs = [];
}


// next hike no if published as brand new hike
$last = "SELECT `indxNo` FROM `HIKES` ORDER BY 1 DESC LIMIT 1;";
$lasthike = $pdo->query($last);
$item = $lasthike->fetch(PDO::FETCH_ASSOC);
$lastHikeNo = intval($item['indxNo']);
/**
 *  NOTE: Guarantee that the AUTO_INCREMENT value agrees with the value
 * of $lastHikeNo + 1 [This has caused errors during past testing!]
 */
$ai_state = <<<AI
SELECT AUTO_INCREMENT
FROM information_schema.tables
WHERE table_name = 'HIKES'
AND table_schema = DATABASE( );
AI;
$state = $pdo->query($ai_state)->fetch(PDO::FETCH_NUM);
$testNo = $lastHikeNo + 1;
if (intval($state[0]) !== $testNo) {
    throw new Exception(
        "AUTO_INCREMENT value {$state[0]} does not agree with" .
        " next hike no {$testNo}"
    );
}

$query = "SELECT * FROM EHIKES WHERE indxNo = :hikeNo;";
$ehk = $pdo->prepare($query);
$ehk->execute(["hikeNo" => $hikeNo]);
$ehike = $ehk->fetch(PDO::FETCH_ASSOC);
if ($ehike === false) {
    throw new Exception("EHIKE data not found for indxNo {$hikeNo}");
}
$clusPgField = $ehike['pgTitle']; // used if publishing a new cluster page
$cname = $ehike['cname'];
$proposed = strpos($ehike['pgTitle'], '[Proposed]') === false ? false : true;

// update actions.txt
$txt = "EHIKE no {$hikeNo}; Page ";
$cp = $clusterPage ? "is " : "is not ";
$txt .= $cp . "a cluster page;\n";
$actions_in_progress = $txt;
file_put_contents("actions.txt", $actions_in_progress);

/**
 * Validate key data: some data must not be 'empty' in order to prevent
 * viewing problems; see comments below
 */
if ($clusterPage) {
    // Data omission here prevents displaying cluster on main map (mapJsData.php)
    $cpClustersReq = "SELECT `lat`,`lng` FROM `CLUSTERS` WHERE `group`=?;";
    $clusterData = $pdo->prepare($cpClustersReq);
    $clusterData->execute([$clusPgField]);
    $cdat = $clusterData->fetch(PDO::FETCH_ASSOC);
    if (is_null($cdat['lat']) || is_null($cdat['lng'])) {
        $msgout 
            .= "<p class='brown'>Missing lat or lng for Cluster {$clusPgField}</p>";
    }
} else {
    // Data omission here will cause issues in mapJsData.php on home page,
    // or other problems (including execution errors)
    if (!empty($cname)) {
        // if a group hike page, validate data in CLUSTERS (whether or not published)
        $clusterDataReq = "SELECT * FROM `CLUSTERS` WHERE `group`=?;";
        $clusterData = $pdo->prepare($clusterDataReq);
        $clusterData->execute([$cname]);
        $clusdat = $clusterData->fetch(PDO::FETCH_ASSOC);
        if ($clusdat === false) {
            throw new Exception("Could not find {$cname} in CLUSTERS");
        }
        if (empty($clusdat['lat']) || empty($clusdat['lng'])) {
            $msgout .= '<p class="brown">Missing lat or lng data in CLUSTERS</p>';
        }
    }
    if (empty($ehike['miles']) || empty($ehike['feet'])|| empty($ehike['diff'])) {
        $msgout .= '<p class="brown">Missing miles, feet, or difficulty data</p>';
    }
    if (empty($ehike['lat']) || empty($ehike['lng'])) {
        $msgout .= '<p class="brown">Missing lat or lng data</p>';
    }
    if (!$proposed) {
        if (empty($ehike['last_hiked'])) {
            $msgout .= '<p class="brown">Missing last_hiked data</p>';
        }
        if (empty($ehike['preview'])) {
            $msgout .= '<p class="brown">Missing preview/thumb data</p>';
        }
        if (empty($ehike['bounds'])) {
            $msgout .= '<p class="brown">Missing hike bounds box</p>';
        }
    }
}
if (empty($ehike['dirs'])) {
    $msgout .= '<p class="brown">Missing directions link</p>';
}
$status = intval($ehike['stat']);
if ($status > $lastHikeNo || $status < 0) {
    $msgout .="<p class='brown'>Status out-of-range: {$status}</p>";
}

/**
 * Continue ONLY IF NO ERRORS...
 * Get all associated json track files, which must have their filenames
 * altered to correspond to the new production hike no.
 */
if ($msgout == '') { 
    /**
     * It will be necessary to collect all of this hike's 'exx.json' files and
     * move them to the corresponding correct names for pubished file ('pxx.json)
     * noting that the hikeIndxNo will also change when moved. To advise the 
     * admin of which files need to be deleted/updated in localhost, two arrays
     * are set up to capture relevant information. Note that in some cases, an 
     * exx.json file may already exist on localhost owing to a long time residency
     * of a hike-in-edit. 
     */ 
    $deleted_json = [];
    $added_or_chgd_json = [];
    // Get column names for building query strings
    $result = $pdo->query("SHOW COLUMNS FROM EHIKES;");
    $columns = $result->fetchAll(PDO::FETCH_BOTH);
    // NOTE: HIKES gpx field is not updated/inserted here, as it will be set later
    if ($status > 0) { // this is an existing hike, UPDATE its record in HIKES
        $query = "UPDATE HIKES, EHIKES SET ";
        foreach ($columns as $column) {
            if (($column[0] !== "indxNo") && ($column[0] !== "stat")
                && $column[0] !== "cname" && ($column[0] !== "gpx")
            ) {
                $query .= "HIKES.{$column[0]} = EHIKES.{$column[0]}, ";
            }
        }
        $query = rtrim($query, ", "); // remove final comma and space
        $query .= " WHERE HIKES.indxNo = :status AND EHIKES.indxNo = :hikeNo;";
        $updte = $pdo->prepare($query);
        $updte->bindValue(":status", $status);
        $updte->bindValue(":hikeNo", $hikeNo);
    } else { // this is a new hike, INSERT its record into HIKES
        $query = "INSERT INTO HIKES (";
        $fields = '';
        foreach ($columns as $column) {
            if (($column[0] !== "indxNo") && ($column[0] !== "stat")
                && ($column[0] !== "cname") && ($column[0] !== "gpx")
            ) {
                $fields .= "{$column[0]}, ";
            }
        }
        $fields = rtrim($fields, ", "); // remove final comma and space
        $query .= $fields . ") SELECT " . $fields .
            " FROM EHIKES WHERE indxNo = :hikeNo;";
        $updte = $pdo->prepare($query);
        $updte->bindValue(":hikeNo", $hikeNo);
    }
    $updte->execute();
    
    // Assign the hike number for the remaining tables based on status:
    if ($status === 0) { // this will be the newly added hikeno.
        $indxNo = $lastHikeNo + 1;
        $type   = 'New Hike';
        $actions_in_progress .= "This is new hike no. {$indxNo};\n";
    } else { // this will be the already published hikeno
        $indxNo = $status;
        $actions_in_progress .= "This is an update to hike no {$indxNo};\n";
    }
    $actions_in_progress
        .= "HIKES table has changed (but not gpx field) by transferring " .
        "data from EHIKES -> EHIKES data not removed;\n";
    file_put_contents("actions.txt", $actions_in_progress);

    $newPage = "../pages/hikePageTemplate.php?hikeIndx=" . $indxNo;

    if (!$clusterPage) { // NOTE: gpx field remains empty on cluster pages
        /**
         * For already published hikes, first remove old Published json files
         * [There may be more currently published json tracks than there are
         * replacements!].
         */
        if ($status > 0) {
            $pub_main_files = getTrackFileNames($pdo, $indxNo, 'pub')[0];
            foreach ($pub_main_files as $old) {
                $jfile = '../json/' . $old;
                if (file_exists($jfile)) {
                    unlink($jfile);
                    $actions_in_progress .= "{$old} [pre-existing] deleted;\n";
                    array_push($deleted_json, $old);
                } else {
                    $actions_in_progress .= "{$old} [pre-existing] was not found;\n";
                }

            }
            file_put_contents("actions.txt", $actions_in_progress);
        }
        /**
         * Retrieve the EHIKE gpx data and convert it to HIKE gpx data
         */
        $main_val = [];
        $add1_val = [];
        $add2_val = [];
        $add3_val = [];
        // $hikeNo is guaranteed = EHIKES hike no
        $egpx_array = getGpxArray($pdo, $hikeNo, 'edit');
        $emain = $egpx_array['main'];
        if (empty($emain)) {
            throw new Exception(
                "No EHIKES gpx file has been uploaded for EHIKE No. {$hikeNo}"
            );
        }
        $eadd1 = empty($egpx_array['add1']) ? [] : $egpx_array['add1'];
        $eadd2 = empty($egpx_array['add2']) ? [] : $egpx_array['add2'];
        $eadd3 = empty($egpx_array['add3']) ? [] : $egpx_array['add3'];
        $emain_gpx = array_keys($emain)[0];
        $eadd1_gpx = empty($eadd1) ? '' : array_keys($eadd1)[0];
        $eadd2_gpx = empty($eadd2) ? '' : array_keys($eadd2)[0];
        $eadd3_gpx = empty($eadd3) ? '' : array_keys($eadd3)[0];
        $ehike_main_files = getTrackFileNames($pdo, $hikeNo, 'edit')[0];
        foreach ($ehike_main_files as $fname) {
            $ftype        = substr($fname, 1, 2);  // 'mn', 'a1', 'a2', or 'a3'
            $extensionloc = strpos($fname, "_");
            $extension    = substr($fname, $extensionloc); // _#.json
            $new_fname    = 'p' . $ftype . $indxNo . $extension;
            switch ($ftype) {
            case "mn":
                array_push($main_val, $new_fname);
                break;
            case "a1":
                array_push($add1_val, $new_fname);
                break;
            case "a2":
                array_push($add2_val, $new_fname);
                break;
            default:
                array_push($add3_val, $new_fname);
            }
            $old_loc = "../json/" . $fname;
            $new_loc = "../json/" . $new_fname;
            if (file_exists($old_loc)) {
                rename($old_loc, $new_loc);
                $actions_in_progress .= "Moved {$fname} to {$new_fname}\n";
                array_push($deleted_json, $fname);
                array_push($added_or_chgd_json, $new_fname);
            } else {
                $actions_in_progress
                    .= "{$fname} not present so {$new_fname} not created;\n";
            } 
        }
        $actions_in_progress .= "Json files (if present) moved but gpx field " .
            "not written;\n";
        file_put_contents("actions.txt", $actions_in_progress);
        
        $add1_array = empty($eadd1_gpx) ? [] : array($eadd1_gpx => $add1_val);
        $add2_array = empty($eadd2_gpx) ? [] : array($eadd2_gpx => $add2_val);
        $add3_array = empty($eadd3_gpx) ? [] : array($eadd3_gpx => $add3_val);
        $new_gpx_array = array(
            "main" => [$emain_gpx => $main_val],
            "add1" => $add1_array,
            "add2" => $add2_array,
            "add3" => $add3_array
        );
        // set HIKES gpx field with new gpx array
        $new_gpx = json_encode($new_gpx_array);
        $updateGpxReq = "UPDATE `HIKES` SET `gpx`=? WHERE `indxNo`=?;";
        $updateGpx = $pdo->prepare($updateGpxReq);
        $updateGpx->execute([$new_gpx, $indxNo]);
        $actions_in_progress .= "HIKES gpx field updated;\n";
        file_put_contents("actions.txt", $actions_in_progress);
        /**
         * In the cases of EGPSDAT, EREFS, ETSV, and EWAYPTS elements may have been
         * deleted during edit, therefore, remove ALL the old data if the
         * hike was type 'published'. Insert new data (no UPDATEs, only INSERTs)
         */
        //  ---------------------  GPSDAT -------------------
        if ($status > 0) { // eliminate any existing data
            $pubDataReq = "SELECT `url` FROM `GPSDAT` WHERE `label` LIKE 'GPX%' " .
                "AND `indxNo`=?;";
            $pubData = $pdo->prepare($pubDataReq);
            $pubData->execute([$indxNo]);
            $pub_urls = $pubData->fetchAll(PDO::FETCH_ASSOC);
            foreach ($pub_urls as $pub) {
                $gpsUrlData = getGPSurlData($pub['url']);
                foreach ($gpsUrlData[1] as $json) {
                    $jfile = '../json/' . $json;
                    if (file_exists($jfile)) {
                        unlink('../json/' . $json);
                        array_push($deleted_json, $json);
                        $actions_in_progress .= "Old GPSDAT json file {$json} " .
                            "deleted for updated hike;\n";
                    } else {
                        $actions_in_progress .= "{$json} of GPSDAT not found\n";
                    } 
                }
            }
            $actions_in_progress .= "Old GPSDAT json files processed, but GPSDAT " .
                "no yet deleted;\n";
            file_put_contents("actions.txt", $actions_in_progress); 
            $query = "DELETE FROM `GPSDAT` WHERE `indxNo` = :pubNo;";
            $pubdat = $pdo->prepare($query);
            $pubdat->bindValue(":pubNo", $indxNo);
            $pubdat->execute();
            $actions_in_progress .= "Old GPSDAT data deleted for existing hike;\n";
            file_put_contents("actions.txt", $actions_in_progress);
        }
        // Find current EGPSDAT entries, if any, and decode
        $getEGPSDAT_Req = "SELECT * FROM `EGPSDAT` WHERE `indxNo`=?;";
        $egpsdat = $pdo->prepare($getEGPSDAT_Req);
        $egpsdat->execute([$hikeNo]);
        $egps = $egpsdat->fetchAll(PDO::FETCH_ASSOC);
        if (count($egps) > 0) {
            foreach ($egps as $old) {
                if (strpos($old['label'], 'GPX') !== false) {
                    $egpsData = getGPSurlData($old['url']);
                    $url_gpx = $egpsData[0];
                    $url_arr = $egpsData[1];
                    $new_arr = [];
                    foreach ($url_arr as $json) {
                        $extensionLoc = strpos($json, "_");
                        $extension    = substr($json, $extensionLoc);  
                        $new_name = 'pgp' . $indxNo . $extension;
                        array_push($new_arr, $new_name);
                        $old_loc = '../json/' . $json;
                        $new_loc = '../json/' . $new_name;
                        if (file_exists($old_loc)) {
                            rename($old_loc, $new_loc); 
                            array_push($deleted_json, $json);
                            array_push($added_or_chgd_json, $new_name);
                            $actions_in_progress .= "EGPSDAT {$json} moved to " .
                                "{$new_name};\n";
                        } else {
                            $actions_in_progress .= "{$json} not found in EGPSDAT, ".
                                "hence not moved to {$new_name}\n";
                        }
                    }
                    file_put_contents("actions.txt", $actions_in_progress);

                    $new_url = [$url_gpx => $new_arr];
                    $db_entry = [
                        $indxNo, 'GPX:', json_encode($new_url), $old['clickText']
                    ]; 
                } else { // a map or kml entry
                    $db_entry = [
                        $indxNo, $old['label'], $old['url'], $old['clickText']
                    ];
                }
                $newGTableReq = "INSERT INTO `GPSDAT` (`indxNo`,`label`,`url`," .
                    "`clickText`) VALUES (?,?,?,?);";
                $new_db_value = $pdo->prepare($newGTableReq);
                $new_db_value->execute(
                    [$db_entry[0], $db_entry[1], $db_entry[2], $db_entry[3]]
                );
            }
            $actions_in_progress .= "EGPSDAT processed but not removed;\n";
            file_put_contents("actions.txt", $actions_in_progress);
        }
        // ---------------------  TSV -------------------
        if ($status > 0) { // eliminate any existing data
            $query = "DELETE FROM TSV WHERE indxNo = :indxNo;";
            $deltsv = $pdo->prepare($query);
            $deltsv->bindValue(":indxNo", $indxNo);
            $deltsv->execute();
            $actions_in_progress .= "Old TSV data eliminated;\n";
            file_put_contents("actions.txt", $actions_in_progress);
        }
        // insert new data whether old or new hike
        $query
            = "INSERT INTO TSV 
            (indxNo,folder,title,hpg,mpg,`desc`,lat,lng,
            thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) 
            SELECT
            :indxNo,folder,title,hpg,mpg,`desc`,lat,lng,
            thumb,alblnk,date,mid,imgHt,imgWd,iclr,org
            FROM ETSV WHERE indxNo = :ehikeNo;";
        $instsv = $pdo->prepare($query);
        $instsv->bindValue(":indxNo", $indxNo); // the indxNo of the new/updated hike
        $instsv->bindValue(":ehikeNo", $hikeNo); // the EHIKES indxNo
        $instsv->execute();
        $actions_in_progress .= "New TSV data entered; ETSV data not yet removed;\n";
        file_put_contents("actions.txt", $actions_in_progress);
        // insert new data for WAYPTS
        if ($status > 0) {
            $query = "DELETE FROM `WAYPTS` WHERE `indxNo` = :indxNo;";
            $delwpt = $pdo->prepare($query);
            $delwpt->execute([$indxNo]);
            $actions_in_progress .= "Old Waypt data removed; not yet updated;\n";
            file_put_contents("actions.txt", $actions_in_progress);
        }
        $query = "INSERT INTO `WAYPTS` (`indxNo`,`type`,`name`,`lat`,`lng`,`sym`) " .
            "SELECT ?,`type`,`name`,`lat`,`lng`,`sym` FROM `EWAYPTS` WHERE " .
            "`indxNo`=?;";
        $inswpt = $pdo->prepare($query);
        $inswpt->execute([$indxNo, $hikeNo]);
        $actions_in_progress .= "WAYPTS table updated for page;\n";
        file_put_contents("actions.txt", $actions_in_progress);
    }
    // Cluster pages also receive REFS updates
    // ---------------------  REFS -------------------
    if ($status > 0) { // eliminate any existing data for published hike
        $dquery = "DELETE FROM REFS WHERE indxNo = :indxNo;";
        $delref = $pdo->prepare($dquery);
        $delref->bindValue(":indxNo", $indxNo);
        $delref->execute();
        $actions_in_progress .= "Old REFS removed but not yet updated;\n";
        file_put_contents("actions.txt", $actions_in_progress);
    }
    // insert new data whether old or new hike
    $query
        = "INSERT INTO REFS (indxNo,rtype,rit1,rit2) 
        SELECT :indxNo,rtype,rit1,rit2
        FROM EREFS WHERE indxNo = :ehikeNo;";
    $insref = $pdo->prepare($query);
    $insref->bindValue(":indxNo", $indxNo); // the indxNo of the new/updated hike
    $insref->bindValue(":ehikeNo", $hikeNo); // the EHIKES indxNo
    $insref->execute();
    $actions_in_progress .= "REFS table updated\n";
    file_put_contents("actions.txt", $actions_in_progress);

    /**
     * If a 'cname' field was populated in EHIKES, then one or more CLUSHIKE
     * entries also exist for this page. 
     * If corresponding 'group' in CLUSTERS has pub=N, then the group needs to be
     * published along with the group hike page.
     * NOTE: 'cname' will not be populated for cluster pgs or 'normal' hikes
     * ALSO: pub=N => indxNo is EHIKES indxNo; pub=Y => is HIKES indxNo
     * ALSO: data has already been validated above for these cases.
     */
    if (!empty($cname)) {
        $clushike_req = "UPDATE `CLUSHIKES` SET `pub`='Y', `indxNo`=? WHERE  " .
             "`indxNo`=? AND `pub`='N';";
        $clushike = $pdo->prepare($clushike_req);
        $clushike->execute([$indxNo, $hikeNo]); //$hikeNo is (old) EHIKE indxNo
        if ($clusdat['pub'] == 'N') { // $clusdat established near script beginning
            $updateClusReq = "UPDATE `CLUSTERS` SET `pub`='Y' WHERE `clusid`=?;";
            $updateClus = $pdo->prepare($updateClusReq);
            $updateClus->execute([$clusdat['clusid']]);
        } 
        $actions_in_progress .= "CLUSHIKES and CLUSTERS updated for {$cname};\n";
        file_put_contents("actions.txt", $actions_in_progress);
    }

    /**
     * If this hike was for a NEW cluster page, update the CLUSTERS group to point
     * to the new indxNo in HIKES; Already published cluster pages require no change.
     */
    if ($clusterPage && $status == 0) {
        $newPgReq = "UPDATE `CLUSTERS` SET `page`=?,`pub`='Y' WHERE `group`=?;";
        $newPg = $pdo->prepare($newPgReq);
        $newPg->execute([$indxNo, $clusPgField]);
        $actions_in_progress .= "New cluster page CLUSTERS entered;\n";
        file_put_contents("actions.txt", $actions_in_progress);
    }

    /**
     * Regardless of state, remove this hike from EHIKES et al:
     * Foreign Keys ensures deletion in remaining E-tables (EGPSDAT, etc.)
     */
    $query = "DELETE FROM `EHIKES` WHERE `indxNo` = :ehikeNo;";
    $dele = $pdo->prepare($query);
    $dele->bindValue(":ehikeNo", $hikeNo);
    $dele->execute();
    file_put_contents("deleted.txt", implode(",", $deleted_json));
    file_put_contents("changed.txt", implode(",", $added_or_chgd_json));
    $actions_in_progress
        .= "Hike deleted from EHIKES and FOREIGN KEY tables updated;\n";
    file_put_contents("actions.txt", $actions_in_progress);
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Release to Main Site</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/admintools.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        .brown { color: brown }
    </style>
    <script src="../scripts/jquery.js"></script>
</head>
<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Release EHIKE No. <?=$hikeNo;?></p>
<p id="active" style="display:none">Admin</p>


<div style="margin-left:16px;font-size:22px">
    <?php if ($msgout !== '') : ?>
        <?=$msgout;?>
    <?php else : ?>
        <p>E-Hike <?=$hikeNo;?> Has Been Released to the Main Site and 
            may now be viewed from the main page as hike no <?=$indxNo;?>
            (<a href="<?=$newPage;?>"><?=$type;?></a>)</p>
        <p>Edited hike has been removed from the list of New/In-Edit Hikes and
            checksums have been regenerated.
        </p>
        <script type="text/javascript">
            $.get('manageChecksums.php', {action: 'gen'});
        </script>
    <?php endif; ?>
</div>

</body>
</html>
