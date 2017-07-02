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
    /* THIS FILE IS NOT A FULL HTML DOCUMENT AND IS MEANT TO BE INSERTED AS A TABLE
       IN VARIOUS OTHER HTML DOCS (e.g. INDEX TABLE w/MAP; hikeEditor.php)
       NOTE: There is only reading of data from the database, no writing to it */
    $datatable = '../data/database.xml';
    $tabledat = simplexml_load_file($datatable);
    if ($tabledat === false) {
        die ("Could not load convertcsv.html as simplexml");
    }
    /* some image definitions for icons that will appear as hyperlinks in the table */
    $indxIcon = '<img class="webShift" src="../images/indxCheck.png" alt="index checkbox" />';
    $webIcon = '<img class="webShift" src="../images/greencheck.jpg" alt="checkbox" />';
    $dirIcon = '<img src="../images/dirs.png" alt="google driving directions" />';
    $picIcon = '<img class="flckrShift" src="../images/album_lnk.png" alt="Flickr symbol" />';
    $sunIcon = '<img class="expShift" src="../images/sun.jpg" alt="Sunny icon" />';
    $partialIcon = '<img class="expShift" src="../images/greenshade.jpg" alt="Partial shade icon" />';
    $shadeIcon = '<img class="expShift" src="../images/shady.png" alt="Partial sun/shade icon" />';
    
    foreach ($tabledat->row as $page) {
        $hikeIndx = $page->indxNo;
        $hikeLat = $page->lat;
        $hikeLon = $page->lng;
        $hikeTrk = $page->trkfile;
        $hikeHiddenDat = 'data-indx="' . $hikeIndx . '" data-lat="' . $hikeLat . 
            '" data-lon="' . $hikeLon . '" data-track="' . $hikeTrk . '"';
        /* the following variables are assigned depending on marker types: the 
           $hikeArray supplies defaults which are over-ruled if an index page */
        $hikeWow = $page->wow;
        $hikeLgth = $page->miles;
        $hikeElev = $page->feet;
        $hikeDiff = $page->difficulty;
        $hikeExposure = $page->expo;
        if ($hikeExposure === 'Full sun') {
            $hikeExpIcon = '<td>' . $sunIcon . '</td>';
        } elseif ($hikeExposure === 'Mixed sun/shade') {
            $hikeExpIcon = '<td>' . $partialIcon . '</td>';
        } else {
            $hikeExpIcon = '<td>' . $shadeIcon . '</td>';
        }
        $hikeMainURL = rawurldecode($page->mpUrl);
        $hikePhotoLink = '<td><a href="' . $hikeMainURL . '" target="_blank">' .
            $picIcon . '</a></td>';
        $hikeLinkIcon = $webIcon;
        /* There are four types of markers to consider requiring different treatment: */
        $hikeMarker = $page->marker;
        if ($hikeMarker === 'Visitor Ctr') {
            echo '<tr class="indxd" ' . $hikeHiddenDat . ' data-org-hikes="' .
                $page->clusterStr . '">';  // Visitor centers id any subhikes
            $hikeLinkIcon = $indxIcon;
            $hikeWow = "See Indx";
            $hikeLgth = "0*";
            $hikeElev = "0*";
            $hikeDiff = "See Indx";
            $hikeExpIcon = '<td>See Indx</td>';
            $hikePhotoLink = '<td>See Indx</td>';
        } elseif ($hikeMarker === 'Cluster') {
            echo '<tr class="clustered" data-cluster="' . $page->clusGrp . '" ' .
                $hikeHiddenDat . ' data-tool="' . $page->cgName . '">';
        } elseif ($hikeMarker === 'At VC') {
            # At VC hikes will be ignored when time to create markers
            echo '<tr class="vchike"  data-vc="' . $page->clusterStr . '" '. 
                $hikeHiddenDat . '>';
        } else {  // "Normal"
            echo '<tr class="normal" ' . $hikeHiddenDat . '>';
        }
        if ($hikeMarker === 'Visitor Ctr') {
            $hikePage = 'indexPageTemplate.php?hikeIndx=' . $hikeIndx;
        } else {
            $hikePage = 'hikePageTemplate.php?hikeIndx=' . $hikeIndx;
        }
        $hikeName = $page->pgTitle;
        $hikeLocale = $page->locale;
        $hikeDirections = $page->dirs;
        /* There may be either one or two photo links... if only one, then
           post the icon for photos on the hike page summary table; regardless,
           post the "main" link here in the data table */

        //print out a row:
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
?>
    </tbody>
</table>
