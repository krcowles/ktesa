<?php
/**
 * The hike page editor allows the user to update information contained
 * in the database, whether for a new hike or a published hike copied
 * to the editor for offline changes. Any changes made by the user will
 * not become permanently effective until the edited hike is published.
 * When this module is invoked from the hikeEditor (or submitNewPg), the
 * tab display setting will be "1". If the user clicks on 'Apply' for any
 * tab, that same tab will display again with refreshed data.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
$hikeNo = filter_input(INPUT_GET, 'hikeNo');
$hikeIndexNo = $hikeNo; // alias
$tab    = filter_input(INPUT_GET, 'tab');

/**
 * There are currently four tabs requiring data: each tab's needs are 
 * highlighted with comment blocks.
 * 
 * Tab1: [data contained in EHIKES table]
 */
$clusters = getClusters($pdo);
$hikereq = "SELECT * FROM EHIKES WHERE indxNo = :hikeno;";
$hikeq = $pdo->prepare($hikereq);
if ($hikeq->execute(["hikeno" => $hikeNo]) === false) {
    throw new Exception("Hike {$hikeNo} Not Found in EHIKES");
}
$hike = $hikeq->fetch(PDO::FETCH_ASSOC);

$pgTitle   = $hike['pgTitle'];
$hikeTitle = $pgTitle; // alias
$locale    = $hike['locale'];
$cname     = $hike['cname'];
$logistics = $hike['logistics'];
$miles    = $hike['miles'];
$feet     = $hike['feet'];
$diff     = $hike['diff'];
$fac      = $hike['fac'];
$wow      = $hike['wow'];
$seasons  = $hike['seasons'];
$expo     = $hike['expo'];
$flist    = $hike['gpxlist'];  // can contain more than one fileno, comma-separated
$curr_trk = $hike['trk'];
$lat      = !empty($hike['lat']) ? $hike['lat']/LOC_SCALE : '';
$lng      = !empty($hike['lng']) ? $hike['lng']/LOC_SCALE : '';
$preview_name = $hike['preview'];
$dirs     = $hike['dirs'];
if (empty($flist)) {
    $miles = '';
    $feet  = '';
    $lat = '';
    $lng = '';
} else {
    if (empty($miles)) {
        $miles = '';
    } else {
        $miles = sprintf("%.2f", $miles);
    }
}

/**
 * Get the EGPX metadata to id any uploaded gpx files and retrieve their names.
 * NOTE: When a main was loaded previously with additional files, the main
 * may have been deleted (represented by fileno=0) while additionals remain.
 */
$gpxs = [];
$curr_gpx = '';
if (!empty($flist)) {
    $efilenos = explode(",", $flist);
    foreach ($efilenos as $fnum) {
        if ($fnum !== '0') {
            $egpxReq = "SELECT `fname` FROM `EMETA` WHERE `fileno`=?;";
            $gpxName = $gdb->prepare($egpxReq);
            $gpxName->execute([$fnum]);
            $gpxfile = $gpxName->fetch(PDO::FETCH_NUM);
            array_push($gpxs, $gpxfile[0]);
        } else {
            array_push($gpxs, '');
        }
    }
    if ($gpxs[0] !== '') {
        $curr_gpx = $gpxs[0];
    } 
}
// id any additional files (not main gpx: 'curr_gpx')
$adders = '<ul id="addlist" style="margin-top:4px;">' . PHP_EOL;
for ($k=1; $k<count($gpxs); $k++) {
    $adders .= '<li><em>' . $gpxs[$k] . '</em>&nbsp;&nbsp;<span ' .
        'class="brown"> Do not include this file:&nbsp;&nbsp;' .
        '<input type="checkbox" name="deladd[]" value="' . $k .'" />' .
        '</span></li>' . PHP_EOL;
}
$adders .= '</ul>' . PHP_EOL;

// collect data for any unpublished cluster groups
$pubReq = "SELECT `group` FROM `CLUSTERS` WHERE `pub`='N';";
$nonpubs = $pdo->query($pubReq)->fetchAll(PDO::FETCH_COLUMN);
$jsData = [];
if (count($nonpubs) > 0) {
    foreach ($nonpubs as $unpub) {
        $getNPdataReq = "SELECT `lat`,`lng` FROM `CLUSTERS` WHERE `group`=?;";
        $getNPdata = $pdo->prepare($getNPdataReq);
        $getNPdata->execute([$unpub]);
        $coords = $getNPdata->fetch(PDO::FETCH_ASSOC);
        $clat = is_null($coords['lat']) ? '""' : $coords['lat']/LOC_SCALE;
        $clng = is_null($coords['lng']) ? '""' : $coords['lng']/LOC_SCALE;
        $groupdat = '{group:"' . $unpub . '",loc:{lat:' . $clat .
            ',lng:' . $clng . '}}';
        array_push($jsData, $groupdat);
    }
}
$newgrps = '[' . implode(",", $jsData) . ']';


// any alerts to display?
$user_alert = '';
if (isset($_SESSION['user_alert']) && !empty($_SESSION['user_alert'])) {
    $user_alert = $_SESSION['user_alert'];
    $_SESSION['user_alert'] == '';
}

/**
 * Tab2: [photo displays (already uploaded) and any waypoints]
 */
require "photoSelect.php";
require "wayPointEdits.php";

/**
 * Tab 3: [hike tips and hike descripton]
 */
$tips    = $hike['tips'];
$info    = $hike['info'];
$picdir  = getPicturesDirectory();
$prevdir = str_replace('zsize', 'previews', $picdir);
$prevImg = $prevdir . $preview_name;

/**
 * Tab 4: [GPS data]
 */
$gpsreq = "SELECT * FROM EGPSDAT WHERE indxNo = :hikeno;";
$gpsq = $pdo->prepare($gpsreq);
$gpsq->execute(["hikeno" => $hikeNo]);
$gpsdisplay = $gpsq->fetchAll(PDO::FETCH_ASSOC); // returns array, even if empty
$no_previous = count($gpsdisplay) === 0 ? true : false;
// filenames (unique by defni) are needed for tab4 display
$disp_data = [];
foreach ($gpsdisplay as $gdat) {
    $label = trim($gdat['label']);
    if ($label === 'GPX' || $label === 'GPX:') {
         // get filename
        $fnameReq = "SELECT `fname` FROM `EMETA` WHERE `fileno`=?";
        $fname = $gdb->prepare($fnameReq);
        $fname->execute([$gdat['fileno']]);
        $gpsname = $fname->fetch(PDO::FETCH_NUM);
        $disp_data[$gpsname[0]] = $gdat;
    } else {
        // strip off directory path for filename
        $nongpxname = substr($label, 8);
        $disp_data[$nongpxname] = $gdat;
    }
}
$displayGps = !empty($disp_data) ? true : false;

$clusterPage = false;
$tbl = 'new';
$rtable = 'EREFS';
$gtable = 'EGPSDAT';
require "../pages/relatedInfo.php";
