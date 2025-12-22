<?php
/**
 * This page will list hike tracks available on pages having either
 * 'additional' gpx files, or GPS Data, as these tracks are not listed
 * elsewhere. It is designed to give the user more information about
 * what is available
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$hikeListReq = "SELECT `indxNo`,`pgTitle`FROM `HIKES`;";
$hikeList = $pdo->query($hikeListReq)->fetchAll(PDO::FETCH_KEY_PAIR);
$rows = [];
$adds = [ 'add1', 'add2', 'add3' ];
$cols = [ false, false ];
$data = '';
foreach ($hikeList as $hikeNo => $title) {
    $extras = getGpxArray($pdo, $hikeNo, 'pub');
    if (!empty($extras['main'])) {
        foreach ($adds as $addno) {
            if (!empty($extras[$addno])) {
                if ($addno == 'add2') {
                    $cols[0] = true;
                }
                if ($addno == 'add3') {
                    $cols[1] = true;
                }
                $data  = "<td><a href='../pages/hikePageTemplate.php?" .
                    "hikeIndx={$hikeNo}' target='_blank'>{$title}</a></td>";
                $tds   = 1;
                $data .= "<td><div>";
                $add_array = array_values($extras[$addno]);
                $tracks = array_values($add_array[0]);
                foreach ($tracks as $file) {
                    $name = getTrackNameFromFile($file);
                    if ($tds === 1) {
                        $data .= $name;
                    } else {
                        $data .= "<br />{$name}";
                    }
                    $tds += 1;
                }
                $data .= "</div></td>";
                for ($i=$tds; $i<=3; $i++) {
                    $data .= "<td></td>";
                }
                $row = "<tr>" . $data . "</tr>";
                array_push($rows, $row);
            }
        }   
    }
}
$gpsdatReq = "SELECT `indxNo`,`url`,`clickText` FROM `GPSDAT` WHERE `label` " .
    "LIKE 'GPX%';";
$gpsGpxFiles = $pdo->query($gpsdatReq)->fetchAll(PDO::FETCH_ASSOC);
$gpsrows = [];
foreach ($gpsGpxFiles as $gpx) {
    $pgTitle = "SELECT `pgTitle` FROM `HIKES` WHERE `indxNo`={$gpx['indxNo']};";
    $page_name = $pdo->query($pgTitle)->fetch(PDO::FETCH_ASSOC);
    $page
        = "<td><a href='../pages/hikePageTemplate.php?hikeIndx=" .
        "{$gpx['indxNo']} 'target='_blank'>{$page_name['pgTitle']}</a></td>";
    $link = "<td>{$gpx['clickText']}</td>";
    $tds = 1;
    $tracks = "<td><div>";
    $url_json = json_decode($gpx['url'], true);
    $track_array = array_values($url_json)[0];
    foreach ($track_array as $file) {
        $name = getTrackNameFromFile($file);
        if ($tds === 1) {
            $tracks .= $name;
        } else {
            $tracks .= "<br />{$name}";
        }
        $tds++;
    }
    $tracks .= "</div></td>";
    $gpsdat = "<tr>{$page}{$link}{$tracks}</tr>";
    array_push($gpsrows, $gpsdat);
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Unlisted Hikes</title>
    <meta charset="utf-8" />
    <meta name="description" content="Unlisted Hikes" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/additionals.css" rel="stylesheet" />
    <?php require "../pages/favicon.html"; ?>
    <script src="../scripts/jquery.js"></script>
</head>
<body>
<!-- body tag must be read prior to invoking bootstrap.js -->
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaPanel.php"; ?>
<p id="trail">Unlisted Hikes</p>

<div id="container">
    <h5>
    Some of the hike pages include 'additional' tracks that are related to
    the main track, and which appear in the track box in the upper right corner
    of the interactive map. These will not appear in the home page listing,
    so the table below shows those 'unlisted' hikes and on which hike page 
    they can be found. A member is allowed to add up to 3 additional gpx files
    to a page, and each gpx file may contain multiple tracks.</h5>
    <table id="ulhikes">
        <thead>
            <tr>
                <th>Source Page</th>
                <th>Unlisted Track[s] for GPX-1</th>
                <th>Unlisted Track[s] for GPX-2</th>
                <th>Unlisted Track[s] for GPX-3</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $table_row) {
                echo $table_row;
            }
            ?>
        </tbody>
    </table><br /><br />
    <h5>
    The following tracks are included in the 'GPS DATA'
    section of some of the hike pages. You can see all included tracks
    by clicking on the 'View As Map' link adjacent to the listed item.</h5>
    <table id="gpshikes">
        <thead>
            <tr>
                <th>Source Page</th>
                <th>GPS Link Name</th>
                <th>Track Name</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gpsrows as $tr) {
                echo $tr;
            }
            ?>
        </tbody>
    </table><br />
</div>

<script type="text/javascript">
    var add2;
    var add3;
    <?php if (!$cols[0]) : ?>
        add2 = true;
    <?php else : ?>
        add2 = false;
    <?php endif; ?>
    <?php if (!$cols[1]) : ?>
        add3 = true;
    <?php else : ?>
        add3 = false;
    <?php endif; ?>
    if (!add3) {
        var table = $("#ulhikes");
        var rows = table.find("tr");
        var hdrs  = rows.eq(0).find("th");
        hdrs[3].remove();
        $("#ulhikes tbody tr").each(function() {
            $(this).find("td:eq(3)").remove();
        });
    }
    if (!add2) {
        var table = $("#ulhikes");
        var rows = table.find("tr");
        var hdrs  = rows.eq(0).find("th");
        hdrs[2].remove();
        $("#ulhikes tbody tr").each(function() {
            $(this).find("td:eq(2)").remove();
        });
    }
</script>

</body>
</html>
