<!DOCTYPE html>
<html lang="en-us">

<?php 
# File Upload Checking & Saving error msgs:
$fexists1 = '<p style="margin-left:8px;margin-top:-12px;color:brown;"><em>NOTE: ';
$fexists2 = ' has been previously saved on the server; ' .
            'Check here to overwrite: ';
$fexists3 = '</em></p>' . "\n";
# File uploads (expecting at least a park map)
$uploads = "tmp/";
$pkmapdup = "NO";
$pkmapow = "NO";
$parkMap = $_FILES['othr1']['tmp_name'];
$parkMapSize = filesize($parkMap);
$parkMapType = $_FILES['othr1']['type'];
$parkMapName = $_FILES['othr1']['name'];
$parkStat = $_FILES['othr1']['error'];

$database = '../data/database.xml';
$indxPg = simplexml_load_file($database);
if ($indxPg === false) {
    $noindxpg = '<p style="color:red;font-size:18px;margin-left:12px>' .
            'Could not load xml database during display Index Page</p>';
    die ($noindxpg);
}
foreach ($indxPg->row as $pg) {
    $lastNo = $pg->indxNo;
}
$nxtPg = intval($lastNo->__toString()) + 1;
$newIndxPg = $indxPg->addChild('row');
# hike form entry
$indxTitle = filter_input(INPUT_POST,'hpgTitle');
$indxLoc = filter_input(INPUT_POST,'locale');
# NOTE: No cluster string yet - assigned as hikes are created
$indxLat = filter_input(INPUT_POST,'lat');
$indxLng = filter_input(INPUT_POST,'lon');
$indxDirs = filter_input(INPUT_POST,'dirs');
$indxInfo = filter_input(INPUT_POST,'hiketxt');
$newIndxPg->addChild('indxNo',$nxtPg);
$newIndxPg->addChild('rlock','Create');
$newIndxPg->addChild('pgTitle',$indxTitle);
$newIndxPg->addChild('locale',$indxLoc);
$newIndxPg->addChild('marker','Visitor Ctr');
$newIndxPg->addChild('clusterStr');
$newIndxPg->addChild('clusGrp');
$newIndxPg->addChild('logistics');
$newIndxPg->addChild('miles');
$newIndxPg->addChild('feet');
$newIndxPg->addChild('difficulty');
$newIndxPg->addChild('facilities');
$newIndxPg->addChild('wow');
$newIndxPg->addChild('seasons');
$newIndxPg->addChild('expo');
$newIndxPg->addChild('tsv');
$newIndxPg->addChild('gpxfile');
$newIndxPg->addChild('trkfile');
$newIndxPg->addChild('lat',$indxLat);
$newIndxPg->addChild('lng',$indxLng);
$newIndxPg->addChild('aoimg1',$parkMapName);
$newIndxPg->addChild('aoimg2');
$newIndxPg->addChild('mpUrl');
$newIndxPg->addChild('spUrl');
$newIndxPg->addChild('dirs',$indxDirs);
$newIndxPg->addChild('cgName');
$newIndxPg->addChild('content');
$newIndxPg->addChild('albLinks');
$newIndxPg->addChild('tipsTxt');
$newIndxPg->addChild('hikeInfo',$indxInfo);
$indxRefs = $newIndxPg->addChild('refs');
$newIndxPg->addChild('dataProp');
$newIndxPg->addChild('dataAct');

# No table yet

/* ASSEMBLE REFERENCES  NOTE: not sure how to filter input arrays .. */
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
            array_push($refLbls,'App: ');
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
/* There SHOULD always be at least one reference, however, if there is not,
    a message will appear in this section: No References Found */
 $refhtml = "<fieldset>\n" . '<legend id="fldrefs">References &amp; '
         . 'Links</legend><ul id="refs">' . "\n";
 if ($noOfRefs === 0) {
     $refhtml .= '<li>No References Found</li>';
 } else {
     for ($j=0; $j<$noOfRefs; $j++) {
         $newRefs = $indxRefs->addChild('ref');
         $newRefs->addChild('rtype',$hikeRefTypes[$j]);
         if ($hikeRefTypes[$j] === 'n') {
             # only one item in this list element: the text
             $refhtml .= '<li>' . $hikeRefItems1[$j] . '</li>';
         } else {
             # all other items have two parts + the id label
             $refhtml .= '<li>' . $refLbls[$j];
             if ($hikeRefTypes[$j] === 'b' || $hikeRefTypes[$j] === 'p') {
                 # no links in these
                 $refhtml .= '<em>' . $hikeRefItems1[$j] . '</em>' . 
                         $hikeRefItems2[$j] . "</li>\n";
             } else {
                 $refhtml .= '<a href="' . $hikeRefItems1[$j] . '" target="_blank">' . 
                     $hikeRefItems2[$j] . "</a></li>\n";
             }
             $newRefs->addChild('rit1',$hikeRefItems1[$j]);
             $newRefs->addChild('rit2',$hikeRefItems2[$j]);
         }
     }  // end of for loop processing
 }  // end of if-else
$refhtml .= "</ul>\n</fieldset>\n"; 
?>

<head>
    <title><?php echo $indxTitle;?></title>
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
<p id="trail"><?php echo $indxTitle;?></p>

<?php
    if($parkMapName == '') {
        $mapMsg = '<p style="color:red;font-size:20px;">Park Map Not Specified</p>';
        die($mapMsg);
    } 
    $pkmapUpload = $uploads . 'images/' . $parkMapName;
?>

<div>
    <img class="mainPic" src="<?php echo $pkmapUpload;?>" alt="Park Map" /><br />
    <p id="dirs"><a href="<?php echo $indxDirs?>" target="_blank">Directions to the Visitor Center</a></p>
    <p id="indxContent"><?php echo $indxInfo;?></p>
</div>

<?php
    echo $refhtml;
?>
    <div id="hdrContainer">
    <p id="tblHdr">Hiking & Walking Opportunities [EMPTY AT THIS TIME]</p>
    </div>
<div style="margin-left:8px;">
    <h3>File upload information:</h3>
</div>
<?php
    $siteLoc = '../images/' . $parkMapName;
    if ( file_exists($siteLoc) ) {
        echo $fexists1 . $parkMapName . $fexists2 . 
            '<input id="owpkmap" type="checkbox" name="pkmapchk" />' . $fexists3;
        $pkmapdup = 'YES';
    } 
    if ($parkStat === UPLOAD_ERR_OK) {
        if (!move_uploaded_file($parkMap,$pkmapUpload)) {
            die("Could not save park map file - contact site master...");
        }
    }
    # Save the locked entry:
    $indxPg->asXML('../data/database.xml');
?>
    <form target="_blank" action="saveIndex.php" method="POST">
        <input type="hidden" name="ptitle" value="<?php echo $indxTitle;?>" />
        <input type="hidden" name="newno" value="<?php echo $nxtPg;?>" />
        <input type="hidden" name="pmapdup" value="<?php echo $pkmapdup;?>" />
        <input type="hidden" name="pmapow" value="<?php echo $pkmapow;?>" />
        <input type="hidden" name="pkmap" value="<?php echo $parkMapName;?>" />
        
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
	
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="../scripts/displayIndexPg.js"></script>
</body>
</html>
