<?php
require_once '../mysql/setenv.php';
/*
 * TblConstructor can be invoked in three different scenarios:
 *  1.  By 'mapPg.php' from the main/index page, 
 *      Here it is used to display ALL hikes and index pages regardless of 
 *      usrid; [show=all, usr=x, table=HIKES]
 *  2,  By 'hikeEditor.php' from the 'Display Options: Edit Hikes' buttons
 *      on the main/index page;
 *      Here it is used to display ONLY hikes which can be edited by the usrid;
 *        a. Editing of newly created hikes or in-edit hikes;
 *           [show=usr, usr='usr', table=EHIKES]
 *        b. Editing of a published hike which is not currently in 
 *           edit mode [show=usr, table=HIKES: if usr='mstr, show=all]
 *  3.  By 'admintools.php' : 'release.php' or 'delete.php';
 *      Here it is used to list ALL EHIKES (for master) to release or delete:
 *      [show=all, usr='mstr', table=EHIKES]
 *  Each 'calling' script must set the show,usr, and age (table) parameters
 *  In all cases, the .js will direct the web page link to the proper location.
 */
if ($age === 'new') {
    $status = '[';  # editing new hikes requires gathering the 'stat' field
    $enos = '[';    # and their corresponding EHIKES indxNo's
    $query = 'SELECT * FROM EHIKES';
    if ($show === 'usr') {
        $query .= " WHERE usrid = '{$usr}'";
    }
} elseif ($age === 'old') {
    $query = 'SELECT * FROM HIKES';
    if ($show === 'usr' && $usr !== 'mstr') {
        $query .= " WHERE usrid = '{$usr}'";
    }
    $status = '[]';
    $enos = '[]';
} else {
    die ("Unrecognized age parameter: " . $age);
}
$query .= ';';
# Now execute the query:
$tblquery = mysqli_query($link,$query);
if (!$tblquery) {
    die("TblConstructor.php: Failed to select data from table: " . 
        mysqli_error($link));
}
if ($show !== 'all') {
    $url_prefix = '../pages/';
} else {
    $url_preix = '';
}
# Icons used for table display:
$indxIcon = '<img class="webShift" src="../images/indxCheck.png" alt="index checkbox" />';
$webIcon = '<img class="webShift" src="../images/greencheck.jpg" alt="checkbox" />';
$dirIcon = '<img src="../images/dirs.png" alt="google driving directions" />';
$picIcon = '<img class="flckrShift" src="../images/album_lnk.png" alt="Flickr symbol" />';
$sunIcon = '<img class="expShift" src="../images/sun.jpg" alt="Sunny icon" />';
$partialIcon = '<img class="expShift" src="../images/greenshade.jpg" alt="Partial shade icon" />';
$shadeIcon = '<img class="expShift" src="../images/shady.png" alt="Partial sun/shade icon" />';
?>
<!-- REFERENCE TABLE OF HIKES -->
<table class="sortable">
    <colgroup>	
        <col style="width:120px">
        <col style="width:190px">
        <col style="width: 140px">
        <col style="width:80px">
        <col style="width:70px">
        <col style="width:95px">
        <col style="width:100px">
        <col style="width:70px">
        <col style="width:70px">
        <col style="width:74px">
    </colgroup>
    <thead>
        <tr>
            <th class="hdr_row" data-sort="std">Locale</th>
            <th class="hdr_row" data-sort="std">Hike/Trail Name</th>
            <th class="hdr_row" data-sort="std">WOW Factor</th>
            <th class="hdr_row">Web Pg</th>
            <th class="hdr_row" data-sort="lan">Length</th>
            <th class="hdr_row" data-sort="lan">Elev Chg</th>
            <th class="hdr_row" data-sort="std">Difficulty</th>
            <th class="hdr_row">Exposure</th>
            <th class="hdr_row">By Car</th>
            <th class="hdr_row">Photos</th>
        </tr>
    </thead>
    <tbody>
    <!-- ADD HIKE ROWS VIA PHP HERE: -->
<?php
if (mysqli_num_rows($tblquery) === 0) {
    echo "<tr><td>You have no hikes to edit</td></tr>";
} else {
    while ($row = mysqli_fetch_assoc($tblquery)) {
        if ($age === 'new') {
            $status .= '"' . $row['stat'] . '",';
            $enos .= '"' . $row['indxNo'] . '",';
        }
        $indx = $row['indxNo'];
        $hikeLat = $row['lat'];
        $hikeLon = $row['lng'];
        $hikeTrk = $row['trk'];
        $hikeHiddenDat = 'data-indx="' . $indx . '" data-lat="' . $hikeLat . 
            '" data-lon="' . $hikeLon . '" data-track="' . $hikeTrk . '"';
        $hikeWow = $row['wow'];
        $hikeLgth = $row['miles'];
        $hikeElev = $row['feet'];
        $hikeDiff = $row['diff'];
        $hikeExposure = $row['expo'];
        if ($hikeExposure == 'Full sun') {
            $hikeExpIcon = '<td>' . $sunIcon . '</td>';
        } elseif ($hikeExposure == 'Mixed sun/shade') {
            $hikeExpIcon = '<td>' . $partialIcon . '</td>';
        } else {
            $hikeExpIcon = '<td>' . $shadeIcon . '</td>';
        }
        $hikeMainURL = rawurldecode($row['purl1']);
        $hikePhotoLink = '<td><a href="' . $hikeMainURL . '" target="_blank">' .
            $picIcon . '</a></td>';
        $hikeLinkIcon = $webIcon;
        $hikeMarker = $row['marker'];
        if ($hikeMarker == 'Visitor Ctr') {
            echo '<tr class="indxd" ' . $hikeHiddenDat . ' data-org-hikes="' .
                $row['collection'] . '">';  // Visitor centers id any subhikes
            $hikeLinkIcon = $indxIcon;
            $hikeWow = "See Indx";
            $hikeLgth = "0*";
            $hikeElev = "0*";
            $hikeDiff = "See Indx";
            $hikeExpIcon = '<td>See Indx</td>';
            $hikePhotoLink = '<td>See Indx</td>';
        } elseif ($hikeMarker == 'Cluster') {
            echo '<tr class="clustered" data-cluster="' . $row['cgroup'] . '" ' .
                $hikeHiddenDat . ' data-tool="' . $row['cname'] . '">';
        } elseif ($hikeMarker == 'At VC') {
            echo '<tr class="vchike"  data-vc="' . $row['collection'] . '" '. 
                $hikeHiddenDat . '>';
        } else {  // "Normal"
            echo '<tr class="normal" ' . $hikeHiddenDat . '>';
        }
        if ($hikeMarker == 'Visitor Ctr') {
            $hikePage = $url_prefix . 'indexPageTemplate.php?hikeIndx=' . $indx;
        } else {
            $hikePage = $url_prefix .'hikePageTemplate.php?hikeIndx=' . $indx;
        }
        $hikeName = $row['pgTitle'];
        $hikeLocale = $row['locale'];
        $hikeDirections = $row['dirs'];
        #print out a row:
        echo '<td>' . $hikeLocale . '</td>';
        echo '<td>' . $hikeName . '</td>';
        echo '<td>' . $hikeWow . '</td>';
        echo '<td><a href="' . $hikePage . '" target="_blank">' . $hikeLinkIcon . '</a></td>';
        echo '<td>' . $hikeLgth . ' miles</td>';
        echo '<td>' . $hikeElev . ' ft</td>';
        echo '<td>' . $hikeDiff . '</td>';
        echo $hikeExpIcon;
        echo '<td style="text-align:center"><a href="' . $hikeDirections . '" target="_blank">' .
            $dirIcon . '</a></td>';
        echo $hikePhotoLink;
        echo '</tr>';
    }
    mysqli_free_result($tblquery);
    if ($age === 'new') { # forming javascript array data
        $status = substr($status,0,strlen($status)-1);
        $status .= ']';
        $enos = substr($enos,0,strlen($enos)-1);
        $enos .= ']';
    }
}
?>
    </tbody>
</table>