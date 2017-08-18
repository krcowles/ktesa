<?php
# output msg styling (when error encountered)
$pstyle = '<p style="margin-left:16px;color:red;font-size:20px;">';

# indicator for hikePageTemplate.php
$building = true;
$usetsv = false;

$database = '../data/database.xml';
$xml = simplexml_load_file($database);
if ($xml === false) {
    $noload = $pstyle . "Could not load database.xml: contact Site Master</p>";
    die ($noload);
}
# Incoming hike no is already decremented to match indices
$hikeRow = intval(filter_input(INPUT_POST,'hikeno'));

$usePix = filter_input(INPUT_POST,'usepics');

# -------------------------- PIC ROW CONSTRUCTION --------------------------

# Adjust database to register picture choices: hike pg & map
if ($usePix == 'YES') {
    $picarray = $_POST['pix'];  # boxes checked for inclusion on hike page
    $noOfPix = count($picarray);
    if ($noOfPix === 0) {
        $nopix = $pstyle . 'No pictures were selected for inclusion on the ' .
                'hike page: if this is correct, continue; else go back and ' .
                'select the desired items</p>';
        echo $nopix;
    } else {
        for ($z=0; $z<$noOfPix; $z++) {
            # change the 'N' to a 'Y' in the database
            foreach ($xml->row[$hikeRow]->tsv->picDat as $picel) {
                if ($picel->title == $picarray[$z]) {
                    $picel->hpg = 'Y';
                    break;
                }
            }
        }
    }
    # retrieve array of photos checked for inclusion on map:
    $maparray = $_POST['mapit'];
    $noOfMapPix = count($maparray);
    if ($noOfMapPix === 0) {
        $nomappix = $pstyle . 'No pictures were selected for inclusion on the '
                . 'hike map: if this is correct, continue; else go back and'
                . ' select desired items</p>';
        echo $nomappix;
    } else {
        for ($q=0; $q<$noOfMapPix; $q++) {
            # change the 'N' to a 'Y' in the $photos xml object
            foreach ($xml->row[$hikeRow]->tsv->picDat as $picel) {
                if ($picel->title == $maparray[$q]) {
                    $picel->mpg = 'Y';
                    break;
                }
            }
        } 
    }
    include 'formPicRows.php';
}
/*
    ------------------------------ PIC ROW CONSTRUCTION -------------------------
*/
$xml->asXML($database);
include "../pages/hikePageTemplate.php";
?>