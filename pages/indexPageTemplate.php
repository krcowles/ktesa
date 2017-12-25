<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeIndexNo = filter_input(INPUT_GET, 'hikeIndx');
$table = "HIKES";  # may add Edit/Creation EHIKES later...
$query = "SELECT pgTitle,lat,lng,aoimg1,dirs,info " .
        "FROM {$table} WHERE indxNo = '{$hikeIndexNo}';";
$request = mysqli_query($link, $query);
if (!$request) {
    die("indexPageTemplate.php: Unable to get Index Page data: " .
        $mysqli_error($link));
}
$row = mysqli_fetch_assoc($request);
$indxTitle = $row['pgTitle'];

$lnkText = str_replace('Index', '', $indxTitle);

$parkMap = unserialize($row['aoimg1']);  # array
$mapsrc = '../images/' . $parkMap[0];
# currently loading map without ht/width attributes, i.e. $parkMap[1] & [2]
$parkDirs = $row['dirs'];
$parkInfo = $row['info'];
# form html for references:
$rtable = 'REFS';
include '../mysql/get_REFS_row.php';  # $refHtml output
# INDEX TABLE OF HIKES, if any:
$tblcnt = 0;  # if no table elements are present, default msg shows
# table header:
$tblhtml = '<table id="siteIndx">' . "\n" . '<thead>' . "\n" . '<tr>' . "\n";
$tblhtml .= '<th class="hdrRow" scope="col">Trail</th>' . "\n";
$tblhtml .= '<th class="hdrRow" scope="col">Web Pg</th>' . "\n";
$tblhtml .= '<th class="hdrRow" scope="col">Trail Length</th>' . "\n";
$tblhtml .= '<th class="hdrRow" scope="col">Elevation</th>' . "\n";
$tblhtml .= '<th class="hdrRow" scope="col">Exposure</th>' . "\n";
$tblhtml .= '<th class="hdrRow" scope="col">Photos</th>'  . "\n";
$tblhtml .= '</tr>' . "\n" . '</thead>' . "\n" . '<tbody>' . "\n";
$ipdat = 'IPTBLS';
$iptblsreq = "SELECT compl,tdname,tdpg,tdmiles,tdft,tdexp,tdalb " .
    "FROM {$ipdat} WHERE indxNo = '{$hikeIndexNo}';";
$iptbl = mysqli_query($link, $iptblsreq);
if (!$iptbl) {
    die("indexPageTemplate.php: Failed to extract table data from {$ipdat}: " .
        mysqli_error($link));
}
if (mysqli_num_rows($iptbl) !== 0) {
    $tblcnt = 1;  # non-zero, basically
    while ($indxTbl = mysqli_fetch_assoc($iptbl)) {
        # Exposure settings:
        $expos = $indxTbl['tdexp'];
        if ($expos == 'Sunny') {
            $exposure = '../images/sun.jpg';
        } elseif ($expos == 'Partial') {
            $exposure = '../images/greenshade.jpg';
        } elseif ($expos == 'Shady') {
            $exposure = '../images/shady.png';
        } elseif ($expos == 'X') {
            $exposure = '';
        }
        $hiked = ($indxTbl['compl'] == 'Y') ? true : false;
        if ($hiked) {
            $tblhtml .= '<tr>' . "\n" . '<td>' . $indxTbl['tdname'] .
                '</td>' . "\n";
            $tblhtml .= '<td><a href="hikePageTemplate.php?hikeIndx=' .
                $indxTbl['tdpg'] . '" target="_blank">' . "\n" .
                '<img class="webShift" src="../images/greencheck.jpg"' .
                ' alt="website click-on icon" /></a></td>' . "\n";
            $tblhtml .= '<td>' . $indxTbl['tdmiles'] . ' miles</td>' . "\n";
            $tblhtml .= '<td>' . $indxTbl['tdft'] . ' ft</td>' . "\n";
            $tblhtml .= '<td><img class="expShift" src="' .
                $exposure . '" alt="exposure icon" /></td>' . "\n";
            $tblhtml .= '<td><a href="' . $indxTbl['tdalb'] .
                '" target="_blank">' . "\n" . '<img class="flckrShift" ' .
                'src="../images/album_lnk.png" alt="Photos symbol" />' .
                '</a></td>' . "\n";
            $tblhtml .= '</tr>' . "\n";
        } else {  # not hiked yet
            $tblhtml .= '<tr>' . "\n" . '<td>' . $indxTbl['tdname'] .
                '</td>' . "\n";
            $tblhtml .= '<td><img class="webShift" ' .
                'src="../images/x-box.png" alt="box with x" />' .
                '</td>' . "\n";
            $miles = $indxTbl['tdmiles'];
            if (strlen($miles) === 0) {
                $miles = '?';
            }
            $tblhtml .= '<td>' . $miles . ' miles</td>' . "\n";
            $feet = $indxTbl['tdft'];
            if (strlen($feet) === 0) {
                $feet = '?';
            }
            $tblhtml .= '<td>' . $feet . ' ft</td>' . "\n";
            $tblhtml .= '<td class="naShift">N/A</td>' . "\n";
            $tblhtml .= '<td><img class="flckrShift" ' .
                'src="../images/x-box.png" alt="box with x" /></td>' . "\n";
            $tblhtml .= '</tr>' . "\n";
        }
    }  # end of while (fetch each table row)
}
$tblhtml .= '</tbody>' . "\n" . '</table>' . "\n";
mysqli_free_result($iptbl);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $indxTitle;?></title>
    <meta charset="utf-8" />
    <meta name="language"
                    content="EN" />
    <meta name="description"
            content="Details about the {$hikeTitle} hike" />
    <meta name="author"
            content="Tom Sandberg and Ken Cowles" />
    <meta name="robots"
            content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="../styles/subindx.css" type="text/css" rel="stylesheet" />
</head>

<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>

    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail"><?php echo $indxTitle;?></p>

<img class="mainPic" src="<?php echo $mapsrc;?>" alt="Park Service Map" />
<p id="dirs"><a href="<?php echo $parkDirs;?>" target="_blank">
    Directions to the <?php echo $lnkText;?> Visitor Center</a></p>
<?php
    echo '<p id="indxContent">' . $parkInfo . '</p>' . "\n";
    echo '<fieldset><legend id="fldrefs">References &amp; Links</legend>';
    echo $refHtml . '</fieldset>' . "\n";
?>
<div id="hdrContainer">
<p id="tblHdr">Hiking & Walking Opportunities at <?php echo $lnkText;?>:</p>
</div>
<div>
<?php
if ($tblcnt !== 0) {
    echo $tblhtml;
} else {
    echo '<p style="text-align:center;">No hikes yet associated with this park</p>';
    echo '<p style="margin-left:16px;">Total no. of hikes read from tblRow: ' . $i . '</p>';
}
?>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>

</body>

</html>
