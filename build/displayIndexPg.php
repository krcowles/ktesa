<!DOCTYPE html>
<html lang="en-us">

<?php 
$database = '../data/database.csv';
$handle = fopen($database,"r");
while ( ($indxInfo = fgetcsv($handle)) !== false ) {
    $lastNo = $indxInfo[0];
}
$index[0] = intval($lastNo) + 1;
# hike form entry
$index[1] = filter_input(INPUT_POST,'hpgTitle');
$index[2] = filter_input(INPUT_POST,'locale');
$index[3] = 'Visitor Ctr';
# NOTE: No cluster string yet - assigned as hikes are created
$index[10] = filter_input(INPUT_POST,'fac');
$index[11] = filter_input(INPUT_POST,'wow_factor');
$index[19] = filter_input(INPUT_POST,'lat');
$index[20] = filter_input(INPUT_POST,'lon');
# File Upload Checking & Saving
$fexists1 = '<p style="margin-left:8px;margin-top:-12px;color:brown;"><em>NOTE: ';
$fexists2 = ' has been previously saved on the server; ' .
            'Check here to overwrite: ';
$fexists3 = '</em></p>' . "\n";
$uploads = "tmp/";
$pkmapdup = "NO";
$pkmapow = "NO";
$parkMap = $_FILES['othr1']['tmp_name'];
$parkMapSize = filesize($parkMap);
$parkMapType = $_FILES['othr1']['type'];
$parkMapName = $_FILES['othr1']['name'];
$parkStat = $_FILES['othr1']['error'];
if($parkMapName == '') {
    $mapMsg = '<p style="color:red;font-size:20px;">Park Map Not Specified</p>';
    die($mapMsg);
} 
$siteLoc = '../images/' . $parkMapName;
if ( file_exists($siteLoc) ) {
    echo $fexists1 . $parkMapName . $fexists2 . 
        '<input id="owpkmap" type="checkbox" name="pkmapchk" />' . $fexists3;
    $pkmapdup = 'YES';
} 
$pkmapUpload = $uploads . 'images/' . $parkMapName;
if ($parkStat === UPLOAD_ERR_OK) {
    if (!move_uploaded_file($parkMap,$pkmapUpload)) {
        die("Could not save park map file - contact site master...");
    }
}
$index[21] = $siteLoc;
$index[25] = filter_input(INPUT_POST,'dirs');
# No table yet ( $info[29] )

/* ASSEMBLE REFERENCES  NOTE: not sure how to filter input arrays yet... */
$hikeRefTypes = $_POST['rtype'];
$hikeRefItems1 = $_POST['rit1'];
$hikeRefItems2 = $_POST['rit2'];
/* get a count of items actually specified: */
$noOfRefs = count($hikeRefTypes);
for ($k=0; $k<$noOfRefs; $k++) {
    if ($hikeRefItems1[$k] == '') {
        $noOfRefs = $k;
        break;
    }
}
$refLbls = array();
for ($k=0; $k<$noOfRefs; $k++) {
    switch ($hikeRefTypes[$k]) {
        case 'b':
            array_push($refLbls,'Book: ');
            break;
        case 'p':
            array_push($refLbls,'Photo Essay: ');
            break;
        case 'w':
            array_push($refLbls,'Website: ');
            break;
        case 'a':
            array_push($refLblbs,'App: ');
            break;
        case 'd':
            array_push($refLbls,'Downloadable Doc: ');
            break;
        case 'l':
            array_push($refLbls,'Blog: ');
            break;
        case 'r':
            array_push($refLbls,'Related Link: ');
            break;
        case 'o':
            array_push($refLbls,'On-Line Map: ');
            break;
        case 'm':
            array_push($refLbls,'Magazine: ');
            break;
        case 's':
            array_push($refLbls,'News Article: ');
            break;
        case 'g':
            array_push($refLbls,'Meetup Group: ');
            break;
        case 'n':
            array_push($refLbls,'');
            break;
        default:
            echo "Unrecognized reference type passed";
    }
}
$index[38] = filter_input(INPUT_POST,'hiketxt');
?>

<head>
    <title><?php echo $index[1];?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Index Page Creation" />
    <meta name="author" content="Tom Sandberg & Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/subindx.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>

<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail"><?php echo $index[1];?></p>
	
<div class="container_16 clearfix">
    <img class="mainPic" src="<?php echo $pkmapUpload;?>" alt="Park Map" /><br />
    <p id="dirs"><a href="<?php echo $index[25]?>" target="_blank">Directions to the Visitor Center</a></p>
    <p id="indxContent"><?php echo $index[38];?></p>

    <?php 
    /* There SHOULD always be at least one reference, however, if there is not,
       a message will appear in this section: No References Found */
    $refhtml = '<fieldset><legend id="fldrefs">References &amp; Links</legend><ul id="refs">';
    if ($noOfRefs === 0) {
        $refStr = '1^n^No References Found';
        $refhtml .= '<li>No References Found</li>';
    } else {
        $refStr = $noOfRefs;
        for ($j=0; $j<$noOfRefs; $j++) {
            $x = $hikeRefTypes[$j];
            $refStr .= '^' . $x;
            if ($x === 'n') {
                # only one item in this list element: the text
                $refhtml .= '<li>' . $hikeRefItems1[$j] . '</li>';
                $refStr .= '^' . $hikeRefItems1[$j];
            } else {
                # all other items have two parts + the id label
                $refStr .= '^' . $hikeRefItems1[$j] . '^' . $hikeRefItems2[$j];
                $refhtml .= '<li>' . $refLbls[$j];
                if ($x === 'b' || $x === 'p') {
                    # no links in these
                    $refhtml .= '<em>' . $hikeRefItems1[$j] . '</em>' . $hikeRefItems2[$j] . '</li>';
                } else {
                    $refhtml .= '<a href="' . $hikeRefItems1[$j] . '" target="_blank">' . 
                        $hikeRefItems2[$j] . '</a></li>';
                }
            }
        }  // end of for loop processing
    }  // end of if-else
    $refhtml .= '</ul></fieldset>';
    echo $refhtml;
    $index[39] = $refStr;
    ?>	

    <div id="hdrContainer">
    <p id="tblHdr">Hiking & Walking Opportunities [EMPTY AT THIS TIME]</p>
    </div>
    <form target="_blank" action="saveIndex.php" method="POST">
        <input type="hidden" name="pmapdup" value ="<?php echo $pkmapdup;?>" />
        <input id="owflag" type="hidden" name="pmapow" value ="<?php echo $pkmapow;?>" />"
        <input type="hidden" name="indx[]" value="<?php echo $index[0];?>" />
        <input type="hidden" name="indx[]" value="<?php echo $index[1];?>" />
        <input type="hidden" name="indx[]" value="<?php echo $index[2];?>" />
        <input type="hidden" name="indx[]" value="<?php echo $index[3];?>" />
        <input type="hidden" name="indx[]" value="<?php echo $index[10];?>" />
        <input type="hidden" name="indx[]" value="<?php echo $index[11];?>" />
        <input type="hidden" name="indx[]" value="<?php echo $index[19];?>" />
        <input type="hidden" name="indx[]" value="<?php echo $index[20];?>" />
        <input type="hidden" name="indx[]" value="<?php echo $index[21];?>" />"
        <input type="hidden" name="indx[]" value="<?php echo $parkMapName;?>" />
        <input type="hidden" name="indx[]" value="<?php echo $index[25];?>" />
        <input type="hidden" name="indx[]" value="<?php echo $index[38];?>" />
        <input type="hidden" name="indx[]" value="<?php echo $index[39];?>" />

        <div style="margin-left:8px;">
            <h3>Select an option below to save this Index page</h3>
            <p><em>Site Master:</em> Enter Password to Save to Site&nbsp;&nbsp;
                <input id="master" type="password" name="mpass" size="12" maxlength="10" 
                       title="8-character code required" />&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="submit" name="savePg" value="Site Master" />
            </p>
            <p><em>Registered Users:</em> Select button to submit for review&nbsp;&nbsp;
                <input type="submit" name="savePg" value="Submit for Review" />
            </p>
        </div>
    </form>
		
</div>  <!-- END OF CONTAINER 16 -->

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="../scripts/displayIndexPg.js"></script>
</body>
</html>
