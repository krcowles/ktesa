<?php
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($dev) {
    $rel_addr = '../mysql/';
    require_once "../mysql/local_mysql_connect.php";
} else {
    $rel_addr = '../php/';
    require_once "../php/000mysql_connect.php";
}
if ($age === 'new') {
    $table = 'EHIKES';
    $getusr = "SELECT COUNT(*) FROM EHIKES WHERE usrid = '" . $usr . "'";
    $usrhikes = mysqli_query($link,$getusr);
    if (!$usrhikes) {
        if (Ktesa_Dbug) {
            dbug_print('TblConstructor.php: Could not retrieve user hike count: ' . 
                    mysqli_error($link));
        } else {
            user_error_msg($rel_addr,6,0);
        }
    }
    $usrcnt_result = mysqli_fetch_row($usrhikes);
    $usrcnt = $usrcnt_result[0];
} elseif ($usr === 'mstr') {
    $table = 'HIKES';
    $lastid = "SELECT indxNo FROM HIKES ORDER BY indxNo DESC LIMIT 1";
    $getid = mysqli_query($link,$lastid);
    if (!$getid) {
        if (Ktesa_Dbug) {
            dbug_print('TblConstructor.php: Could not retrieve highest indxNo: ' . 
                    mysqli_error($link));
        } else {
            user_error_msg($rel_addr,6,0);
        }
    }
    $lastindx = mysqli_fetch_row($getid);
    $usrcnt = $lastindx[0];
} else {
    $table = 'HIKES';
    $getusr = "SELECT COUNT(*) FROM HIKES WHERE usrid = " . $usr;
    $usrhikes = mysqli_query($link,$getusr);
    if (!$usrhikes) {
        if (Ktesa_Dbug) {
            dbug_print('TblConstructor.php: Could not retrieve user hike count: ' . 
                    mysqli_error($link));
        } else {
            user_error_msg($rel_addr,6,0);
        }
    }
    $usrcnt_result = mysqli_fetch_row($usrhikes);
    $usrcnt = $usrcnt_result[0];
}
mysqli_free_result($getid);
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
if ($usrcnt == 0) {
    echo "<tr><td>You have no hikes to edit</td></tr>";
} else {
    for ($i = 1; $i<=$usrcnt; $i++) {
        if ($usr === 'mstr') {
            $query = "SELECT * FROM " . $table . " WHERE indxNo = " . $i;
        } else {
            $query = "SELECT * FROM " . $table . " WHERE usrid = " . $type;
        }
        $result = mysqli_query($link,$query);
        if (!$result) {
            if (Ktesa_Dbug) {
                dbug_print('TblConstructor.php: failed to extract row ' . $i . ': ' . 
                        mysqli_error($link));
            } 
        }
        $row = mysqli_fetch_assoc($result);
        $hikeLat = $row['lat'];
        $hikeLon = $row['lng'];
        $hikeTrk = $row['trk'];
        $hikeHiddenDat = 'data-indx="' . $i . '" data-lat="' . $hikeLat . 
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
            $hikePage = 'indexPageTemplate.php?hikeIndx=' . $hikeIndx;
        } else {
            $hikePage = 'hikePageTemplate.php?hikeIndx=' . $hikeIndx;
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
    mysqli_free_result($result);
}
?>
    </tbody>
</table>