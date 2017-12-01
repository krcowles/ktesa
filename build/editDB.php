<?php
session_start();
require_once "../mysql/setenv.php";
$hikeNo = filter_input(INPUT_GET,'hno');
$uid = filter_input(INPUT_GET,'usr');
if (isset($_SESSION['activeTab'])) {
    $dispTab = $_SESSION['activeTab'];
} else {
    $dispTab = 1;
}
# Error output styling string:
$pstyle = '<p style="color:red;font-size:18px;">';
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Edit Database</title>
    <meta charset="utf-8" />
    <meta name="description" content="Edit the selected hike" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="editDB.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>   
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail"><?php echo $hiketype;?> Hike Editor</p>
<div id="main" style="padding:16px;">
<h3>Edits made to this hike will be retained in the New/In-Edit database, 
    and will not show up when displaying published hikes until these edits 
    have been formally released</h3>
<?php
/*
 *  Below: pull out the available cluster groups and establish association
 * with cluster group name for displaying in drop-down <select>
 */
$groups = [];
$cnames = [];
$clusterreq = "SELECT cgroup, cname FROM HIKES";
$clusterq = mysqli_query($link,$clusterreq);
if (!$clusterq) {
    die("editDB.php: Failed to extract cluster info from HIKES: " .
        mysqli_error($link));
}
while ($cluster = mysqli_fetch_assoc($clusterq)) {
    $cgrp = $cluster['cgroup'];
    if ( strlen($cgrp) !== 0) {
        # no duplicates please (NOTE: "array_unique" leaves holes)
        $match = false;
        for ($i=0; $i<count($groups); $i++) {
            if ($cgrp == $groups[$i]) {
                $match = true;
                break;
            }  
        }
        if (!$match) {
            array_push($groups,$cgrp);
            array_push($cnames,$cluster['cname']);
        }
    }
}
mysqli_free_result($clusterq);
$groupCount = count($cnames);
/*
 * EXTRACT ALL DATA, saved in various tabs
 */
$hikereq = "SELECT * FROM EHIKES WHERE indxNo = {$hikeNo};";
$hikeq = mysqli_query($link,$hikereq);
if (!$hikeq) {
    die("editDB.php: Failed to extract hike data from EHIKES: " .
        mysqli_error($link));
}
$hike = mysqli_fetch_assoc($hikeq);
# Although some fields will not be edited, they are needed for xfr to EHIKES
$hikeTitle = $hike['pgTitle'];
$hikeLocale = $hike['locale'];
$hikeMarker = $hike['marker'];
$hikeColl = $hike['collection'];
# collection will not be edited
$hikeClusGrp = $hike['cgroup'];
$hikeGrpTip = $hike['cname'];
$hikeStyle = $hike['logistics'];
$hikeMiles = $hike['miles'];
$hikeFeet = $hike['feet'];
$hikeDiff = $hike['diff'];
$hikeFac = $hike['fac'];
$hikeWow = $hike['wow'];
$hikeSeasons = $hike['seasons'];
$hikeExpos = $hike['expo'];
$hikeGpx = $hike['gpx'];
$hikeTrack = $hike['trk'];
# gpx & trk will not be edited
$hikeLat = $hike['lat'];
$hikeLng = $hike['lng'];
$hikeAddImg1 = $hike['aoimg1'];
$hikeAddImg2 = $hike['aoimg2'];
# aoimg1 & aoimg2 will not be edited
$hikeUrl1 = $hike['purl1'];
$hikeUrl2 = $hike['purl2'];
$hikeDirs = $hike['dirs'];
$hikeTips = $hike['tips'];
$hikeDetails = $hike['info'];
mysqli_free_result($hikeq);
?>
<p id="hikeNo" style='display:none'><?php echo $hikeNo;?></p>
<p id="entry" style="display:none"><?php echo $dispTab;?></p>
<em style="color:DarkBlue;font-size:18px;">Any changes below will be made for 
    the hike: "<?php echo $hikeTitle;?>". If no changes are made you may either 
    exit this page or hit the "sbumit" button.
</em><br /><br />
<p style="font-size:18px;color:Brown;">Preview page with current edits
    (i.e. edits already applied):&nbsp;
    <button id="preview" style="font-size:18px;color:DarkBlue;">Preview</button></p>
<!-- tabs -->
<button id="t1" class="tablist active">Basic Data</button>
<button id="t2" class="tablist">Photo Selection</button>
<button id="t3" class="tablist">Descriptive Text</button>
<button id="t4" class="tablist">Refs &amp; Links</button>
<div id="line"></div>
<!---  [NOTE: Each tab is a separate form]
 ********** TAB 1: BASIC DATA *********
-->
<div id="tab1" class="active tab-panel">
<form action="saveTab1.php" method="POST">
    <input type="hidden" name="tbl" value="<?php echo $tbl_type;?>" />
    <input type="hidden" name="hno" value="<?php echo $hikeNo;?>" />
    <input type="hidden" name="usr" value="<?php echo $uid;?>" />
    <input type="hidden" name="stat" value ="<?php echo $status;?>" />
    <input type="hidden" name="col" value="<?php echo $hikeColl;?>" />
    <!-- the following are required esp when extracting a published hike -->
    <input type="hidden" name="gpx" value="<?php echo $hikeGpx;?>" />
    <input type="hidden" name="trk" value="<?php echo $hikeTrk;?>" />
    <input type="hidden" name="ao1" value="<?php echo $hikeAddImg1;?>" />
    <input type="hidden" name="ao2" value="<?php echo $hikeAddImg2;?>" />
    <label for="hike">Hike Name: </label>
    <textarea id="hike" name="hname"><?php echo $hikeTitle;?>
    </textarea>&nbsp;&nbsp;
    <p style="display:none;" id="locality"><?php echo $hikeLocale;?>
    </p>
    <label for="area">Locale (City/POI): </label>
    <select id="area" name="locale">
      <optgroup label="North/Northeast">
            <option value="Jemez Springs">Jemez Springs</option>
            <option value="Valles Caldera">Valles Caldera</option>
            <option value="Los Alamos">Los Alamos</option>
            <option value="White Rock">White Rock</option>
            <option value="Santa Fe">Santa Fe</option>
            <option value="Ojo Caliente">Ojo Caliente</option>
            <option value="Abiquiu">Abiquiu</option>
            <option value="Pecos">Pecos</option>
            <option value="Villanueva">Villanueva</option>
            <option value="Taos">Taos</option>
            <option value="Pilar">Pilar</option>
      <optgroup label="Northwest">
            <option value="Farmington">Farmington</option>
            <option value="San Ysidro">San Ysidro</option>
            <option value="San Luis">San Luis</option>
            <option value="Cuba">Cuba</option>
            <option value="Lybrook">Lybrook</option>
      <optgroup label="Central NM">
            <option value="Cerrillos">Cerrillos</option>
            <option value="Golden">Golden</option>
            <option value="Albuquerque">Albuquerque</option>
            <option value="Placitas">Placitas</option>
            <option value="Corrales">Corrales</option>
            <option value="Tijeras">Tijeras</option>
            <option value="Tajique">Tajique</option>
      <optgroup label="West">
            <option value="Grants">Grants</option>
            <option value="Ramah">Ramah</option>
            <option value="Gallup">Gallup</option>
      <optgroup label="South Central">
            <option value="San Acacia">San Acacia</option>
            <option value="San Antonio">San Antonio</option>
            <option value="Tularosa">Tularosa</option>
      <optgroup label="Southwest">
            <option value="Silver City">Silver City</option>
            <option value="Pinos Altos">Pinos Altos</option>
            <option value="Glenwood">Glenwood</option>
    </select>&nbsp;&nbsp;
    <p id="mrkr" style="display:none"><?php echo $hikeMarker;?></p>
    <input type="hidden" name="pmrkr" value="<?php echo $hikeMarker;?>" />
    <input type="hidden" name="pclus" value="<?php echo $hikeClusGrp;?>" />
    <p id="group" style="display:none"><?php echo $hikeGrpTip;?></p>
    <input type="hidden" name="pcnme" value="<?php echo $hikeGrpTip;?>" />
    <h3>------- Cluster Hike Assignments: (Hikes with overlapping trailheads or in 
        close proximity) -------<br />
    <span style="margin-left:50px;font-size:18px;color:Brown;">Reset Assignments:&nbsp;&nbsp;
        <input id="ignore" type="checkbox" name="nocare" /></span></h3>
<?php
    echo '<label for="ctip">&nbsp;&nbsp;Cluster: </label>';
    echo '<select id="ctip" name="htool">';
    for ($i=0; $i<$groupCount; $i++) {
        echo '<option value="' . $cnames[$i] . '">' . $cnames[$i] . "</option>\n";
    }
    echo "</select>&nbsp;&nbsp;\n" .
    '<span id="showdel" style="display:none;">You may remove the cluster ' .
        'assignment by checking here:&nbsp;<input id="deassign" ' .
        'type="checkbox" name="rmclus" value="NO" /></span>' . "\n" .
    '<span id="notclus" style="display:none;">There is no currently ' .
        "assigned cluster for this hike.</span>\n";
?>
    <input id="grpchg" type="hidden" name="chgd" value="NO" />
    <p>If you are establishing a new group, select the checkbox: 
        <input id="newg" type="checkbox" name="nxtg" value="NO" />
        <input id="curcnt" type="hidden" name="grpcnt" value="<?php echo $groupCount;?>" />
    </p>
    <p style="margin-top:-10px;margin-left:40px;">and enter the name for the 
        new group here: <input id="newt" type="text" name="newgname" size="50" />
    </p>
    <h3>------- End of Cluster Assignments -------</h3>
    <p id="ctype" style="display:none"><?php echo $hikeStyle;?></p>
    <label for="type">Hike Type: </label>
    <select id="type" name="htype">
        <option value="Loop">Loop</option>
        <option value="Two-Cars">Two-Cars</option>
        <option value="Out-and-back">Out-and-back</option>
    </select>&nbsp;&nbsp;
    <label for="miles">Round-trip length in miles: </label>
    <textarea id="miles" name="hlgth"><?php echo $hikeMiles;?>
    </textarea>&nbsp;&nbsp;
    <label for="elev">Elevation change in feet: </label>
    <textarea id="elev" name="helev"><?php echo $hikeFeet;?>
    </textarea><br /><br />
    <p id="dif" style="display:none"><?php echo $hikeDiff;?></p>
    <label for="diff">Level of difficulty: </label>
    <select id="diff" name="hdiff">
        <option value="Easy">Easy</option>
        <option value="Easy-Moderate">Easy-Moderate</option>
        <option value="Moderate">Moderate</option>
        <option value="Med-Difficult">Medium-Difficult</option>
        <option value="Difficult">Difficult</option>
    </select>
    <label for="fac">Facilities at the trailhead: </label>
    <textarea id="fac" name="hfac"><?php echo $hikeFac;?>
    </textarea><br /><br />
    <label for="wow">"Wow" Appeal: </label>
    <textarea id="wow" name="hwow"><?php echo $hikeWow;?>
    </textarea>&nbsp;&nbsp;
    <label for="seas">Best Hiking Seasons: </label>
    <textarea id="seas" name="hsea"><?php echo $hikeSeasons;?>
    </textarea><br /><br />
    <p id="expo" style="display:none"><?php echo $hikeExpos;?></p>
    <label for="sun">Exposure: </label>
    <select id="sun" name="hexp">
        <option value="Full sun">Full sun</option>
        <option value="Mixed sun/shade">Mixed sun/shade</option>
        <option value="Good shade">Good shade</option>
    </select>&nbsp;&nbsp;
    <label for="lat">Trailhead: Latitude </label>
    <textarea id="lat" name="hlat"><?php echo $hikeLat;?></textarea>&nbsp;&nbsp;
    <label for="lon">Longitude </label>
    <textarea id="lon" name="hlon"><?php echo $hikeLng;?></textarea><br />
    <label for="ph1">Photo URL1 ("Main"): </label>
    <textarea id="ph1" name="purl1"><?php echo $hikeUrl1;?></textarea><br />
    <label for="ph2">Photo URL2 ("Additional"): </label>
    <textarea id="ph2" name="purl2"><?php echo $hikeUrl2;?></textarea><br /><br />
    <label for="murl">Map Directions Link (Url): </label>
    <textarea id="murl" name="gdirs"><?php echo $hikeDirs;?></textarea><br /><br />
    <!-- This next section is photo editing-->
    <p id="ptype" style="display:none">Edit</p>
    <div style="margin-left:8px;">
        <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
            <input type="submit" name="savePg" value="Apply" /></p>
    </div>
</form>
</div>
<!--  
 ********** TAB 2: PHOTO SECTION *********
-->
<div id="tab2" class="tab-panel">
<h3>You may wish to upload more photos to add to your page. The currently
    saved album links are displayed below. You may re-select a currently
    saved link in order to update your photo list, and/or you may add up to two
    more links.</h3>
<?php
    $purlsReq = "SELECT purl1,purl2 FROM EHIKES WHERE indxNo = {$hikeNo};";
    $purls = mysqli_query($link,$purlsReq);
    if (!$purls) {
        die("editDB.php: Failed to extract photo album urls for hike {$hikeNo}: " .
            mysqli_error($link));
    }
    $plnks = mysqli_fetch_assoc($purls);
    if ($plnks['purl1'] !== '') {
        echo '<input type="checkbox" name="ps[]" value="1" />&nbsp;';
        echo "Include in upload:&nbsp;&nbsp;";
        echo '<input type="text" name="lnk1" value="' . $plnks['purl1'] . 
            '" size="75" /><br />';
    }
    if ($plnks['purl2'] !== '') {
        echo '<input type="checkbox" name="ps[]" value="2" />&nbsp;';
        echo "Include in upload:&nbsp;&nbsp;";
        echo '<input type="text" name="lnk2" value="' . $plnks['purl2'] . 
            '" size="75" /><br />';
    } 
?>
<input type="checkbox" name="ps[]" value="3" />&nbsp;Include new album: 
<input type="text" name="lnk3" value="" size="75" />&nbsp;&nbsp;
Album type:&nbsp;
<select id="alb3" name="albtype[]">
    <option value="flckr">Flickr Album</option>
    <option value="apple">Apple iCloud Album</option>
    <option value="googl">Google Album</option>
</select><br />
<input type="checkbox" name="ps[]" value="4" />&nbsp;Include new album:
<input type="text" name="lnk4" value="" size="75" />&nbsp;&nbsp;
Album type:&nbsp;
<select id="alb4" name="albtype[]">
    <option value="flckr">Flickr Album</option>
    <option value="apple">Apple iCloud Album</option>
    <option value="googl">Google Album</option>
</select><br /><br />
<button id="upld" style="font-size:16px;">
    Upload Albums</button>&nbsp;&nbsp;Review these album photos for possible
    inclusion on the edit page...
<p style="color:brown;"><em>Edit captions below each photo as needed. Images with no
        captions (e.g. maps, imported jpgs, etc.) are not shown.</em></p>
<form action="saveTab2.php" method="POST">
    <input type="hidden" name="pno" value="<?php echo $hikeNo;?>" />
    <input type="hidden" name="pid" value="<?php echo $uid;?>" />
<?php
    $pgType = 'Edit';
    require "photoSelect.php";
?>
<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>
</form>            
</div>
<!--  
 ********** TAB 3: DESCRIPTIVE TEXT *********
-->
<div id='tab3' class='tab-panel'>
<form action="saveTab3.php" method="POST">
<?php
if ($hikeTips !== '') {
    echo '<p>Tips Text: </p>';
    echo '<textarea id="ttxt" name="tips" rows="10" cols="130">' . $hikeTips . '</textarea><br />' . "\n";
} else {
    echo '<textarea id="ttxt" name="tips" rows="10" cols="130">' . 
       '[NO TIPS FOUND]</textarea><br />' . "\n";
}
?>  
<p>Hike Information:</p>
<textarea id="info" name="hinfo" rows="16" 
        cols="130"><?php echo $hikeDetails;?></textarea>
<input type="hidden" name="dno" value="<?php echo $hikeNo;?>" />
<input type="hidden" name="did" value="<?php echo $uid;?>" />
<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>
</form>
</div>
<!--  
 ********** TAB 4: REFS & LINKS *********
-->
<div id="tab4" class="tab-panel">
<h3>Hike Reference Sources: (NOTE: Book type cannot be changed - if needed,
    delete and add a new one)</h3>
<form action="saveTab4.php" method="POST">
    <input type="hidden" name="rno" value="<?php echo $hikeNo;?>" />
    <input type="hidden" name="rid" value="<?php echo $uid;?>" />
<?php
    $z = 0;  # index for creating unique id's
    $refreq = "SELECT * FROM EREFS WHERE indxNo = '{$hikeNo}';";
    $refq = mysqli_query($link,$refreq);
    if (!$refq) {
        die("editDB.php: Failed to extract references from EREFS: " .
            mysqli_error($link));
    }
    while ($ritem = mysqli_fetch_assoc($refq)) {
        $rid = 'rid' . $z;
        $reftype = 'ref' . $z;
        $thisref = $ritem['rtype'];
        echo '<p id="' . $rid  . '" style="display:none">' . $thisref . "</p>\n";
        echo '<label for="' . $reftype . '">Reference Type: </label>' . "\n";
        echo '<select id="' . $reftype . '" style="height:26px;width:150px;" name="rtype[]">' . "\n";
        echo '<option value="Book:" >Book</option>' . "\n";
        echo '<option value="Photo Essay:">Photo Essay</option>' . "\n";
        echo '<option value="Website:">Website</option>' . "\n";
        echo '<option value="Link:">Website</option>' . "\n"; # leftover category from index pages
        echo '<option value="App:">App</option>' . "\n";
        echo '<option value="Downloadable Doc:">Downloadable Doc</option>' . "\n";
        echo '<option value="Blog:">Blog</option>' . "\n";
        echo '<option value="On-line Map:">On-line Map</option>' . "\n";
        echo '<option value="Magazine:">Magazine</option>' . "\n";
        echo '<option value="News Article:">News Article</option>' . "\n";
        echo '<option value="Meetup Group:">Meetup Group</option>' . "\n";
        echo '<option value="Related Link:">Related Link</option>' . "\n";
        echo '<option value="Text:">Text Only - No Link</option>' . "\n";
        echo '</select><br />' . "\n";
        $decrit1 = $ritem['rit1'];
        if ($thisref === 'Book:' || $thisref === 'Photo Essay:') {
            echo '<label style="text-indent:24px;">Title: </label>'
                . '<textarea style="height:20px;width:320px" name="rit1[]">' .
                $decrit1 . '</textarea>&nbsp;&nbsp;';
            echo '<label>Author: </label>' 
                . '<textarea style="height:20px;width:320px" name="rit2[]">' .
                $ritem['rit2'] . '</textarea>&nbsp;&nbsp;'
                . '<label>Delete: </label>' .
                '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="'.
                $z . '"><br /><br />' . "\n";
        } elseif ($thisref === 'Text') {
            echo '<label>Text only item: </label><textarea style="height:20px;width:320px;" name="rit1[]">' .
                $decrit1 . '</textarea><label>Delete: </label>' .
                '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="' .
                $z . '"><br /><br />' . "\n";
        } else {
            echo '<label>Item link: </label><textarea style="height:20px;width:500px;" name="rit1[]">' .
                $decrit1 . '</textarea>&nbsp;&nbsp;<label>Cick text: </label><textarea style="height:20px;width:330px;" name="rit2[]">' . 
                $ritem['rit2'] . '</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
                '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="' .
                $z . '"><br /><br />' . "\n";
        }  
        $z++;
    }
    mysqli_free_result($refq);
    echo '<p id="refcnt" style="display:none">' . $z . '</p>';
?>
    <p><em style="font-weight:bold;">Add</em> references here:</p>
    <p>Select the type of reference and its accompanying data below:</p>
    <select id="href1" style="height:26px;" name="rtype[]">
        <option value="Book:" selected="selected">Book</option>
        <option value="Photo Essay:">Photo Essay</option>
        <option value="Website:">Website</option>
        <option value="App:">App</option>
        <option value="Downloadable Doc:">Downloadable Doc</option>
        <option value="Blog:">Blog</option>
        <option value="On-line Map:">On-line Map</option>
        <option value="Magazine:">Magazine</option>
        <option value="News Article:">News Article</option>
        <option value="Meetup Group:">Meetup Group</option>
        <option value="Related Link:">Related Link</option>
        <option value="Text:">Text Only - No Link</option>
    </select>
    Book Title/Link URL:<input id="ritA1" type="text" name="rit1[]" size="55" 
        placeholder="Book Title" />&nbsp;
    Author/Click-on Text<input id="ritA2" type="text" name="rit2[]" size="35" 
        placeholder=", by Author Name" /><br /><br />
    <select id="href2" style="height:26px;" name="rtype[]">
        <option value="Book:" selected="selected">Book</option>
        <option value="Photo Essay:">Photo Essay</option>
        <option value="Website:">Website</option>
        <option value="App:">App</option>
        <option value="Downloadable Doc:">Downloadable Doc</option>
        <option value="Blog:">Blog</option>
        <option value="On-line Map:">On-line Map</option>
        <option value="Magazine:">Magazine</option>
        <option value="News Article:">News Article</option>
        <option value="Meetup Group:">Meetup Group</option>
        <option value="Related Link:">Related Link</option>
        <option value="Text:">Text Only - No Link</option>
    </select>
    Book Title/Link URL:<input id="ritB1" type="text" name="rit1[]" size="55" 
        placeholder="Book Title" />&nbsp;
    Author/Click-on Text<input id="ritB2" type="text" name="rit2[]" size="35" 
        placeholder=", by Author Name" /><br /><br />
    <select id="href3" style="height:26px;" name="rtype[]">
        <option value="Book:" selected="selected">Book</option>
        <option value="Photo Essay:">Photo Essay</option>
        <option value="Website:">Website</option>
        <option value="App:">App</option>
        <option value="Downloadable Doc:">Downloadable Doc</option>
        <option value="Blog:">Blog</option>
        <option value="On-line Map:">On-line Map</option>
        <option value="Magazine:">Magazine</option>
        <option value="News Article:">News Article</option>
        <option value="Meetup Group:">Meetup Group</option>
        <option value="Related Link:">Related Link</option>
        <option value="Text:">Text Only - No Link</option>
    </select>
    Book Title/Link URL:<input id="ritC1" type="text" name="rit1[]" size="55" 
        placeholder="Book Title" />&nbsp;
    Author/Click-on Text<input id="ritC2" type="text" name="rit2[]" size="35" 
        placeholder=", by Author Name" /><br /><br />
    <select id="href4" style="height:26px;" name="rtype[]">
        <option value="Book:" selected="selected">Book</option>
        <option value="Photo Essay:">Photo Essay</option>
        <option value="Website:">Website</option>
        <option value="App:">App</option>
        <option value="Downloadable Doc:">Downloadable Doc</option>
        <option value="Blog:">Blog</option>
        <option value="On-line Map:">On-line Map</option>
        <option value="Magazine:">Magazine</option>
        <option value="News Article:">News Article</option>
        <option value="Meetup Group:">Meetup Group</option>
        <option value="Related Link:">Related Link</option>
        <option value="Text:">Text Only - No Link</option>
    </select>
    Book Title/Link URL:<input id="ritD1" type="text" name="rit1[]" size="55" 
        placeholder="Book Title" />&nbsp;
    Author/Click-on Text<input id="ritD2" type="text" name="rit2[]" size="35" 
        placeholder=", by Author Name" /><br />

    <h3>Proposed Data:</h3>
<?php 
    $propreq = "SELECT * FROM EGPSDAT WHERE indxNo = '{$hikeNo}' AND datType = 'P';";
    $propq = mysqli_query($link,$propreq);
    if (!$propq) {
        die("editDB.php: Failed to extract Proposed Data from EGPSDAT: " .
            mysqli_error($link));
    }
    if (mysqli_num_rows($propq) !== 0) {
        $x = 0;
        while ($pdat = mysqli_fetch_assoc($propq)) {
            echo 'Label: <textarea class="tstyle1" name="plabl[]">' . 
                    $pdat['label'] . '</textarea>&nbsp;&nbsp;';
            echo 'Url: <textarea class="tstyle2" name="plnk[]">' . 
                    $pdat['url'] . '</textarea>&nbsp;&nbsp;';
            echo 'Click-on text: <textarea class="tstyle3" name="pctxt[]">' . 
                    $pdat['clickText'] . '</textarea>&nbsp;&nbsp;'
                    . '<label>Delete: </label>' .
                    '<input style="height:18px;width:18px;" type="checkbox" '
                    . 'name="delprop[]" value="' . $x . '"><br /><br />';
            $x++;
        }
        mysqli_free_result($propq);
    }
    
?>
    <p><em style="color:brown;font-weight:bold;">Add</em> Proposed Data:</p>
    <label>Label: </label><input class="tstyle1" name="plabl[]" size="30" />&nbsp;&nbsp;
    <label>Url: </label><input class="tstyle2" name="plnk[]" size="55" />
    <label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="pctxt[]" size="30" /><br />
    <label>Label: </label><input class="tstyle1" name="plabl[]" size="30" />&nbsp;&nbsp;
    <label>Url: </label><input class="tstyle2" name="ltxt[]" size="55" />
    <label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="ctxt[]" size="30" />

    <h3>Actual Data:</h3>
<?php
    $actreq = "SELECT * FROM EGPSDAT WHERE indxNo = '{$hikeNo}' AND datType = 'A';";
    $actq = mysqli_query($link,$actreq);
    if (!$actq) {
        die("editDB.php: Failed to extract Actual Data from EGPSDAT: " .
            mysqli_error($link));
    }
    if (mysqli_num_rows !== 0) {
        $y = 0;
        while ($adat = mysqli_fetch_assoc($actq)) {
            echo 'Label: <textarea class="tstyle1" name="alabl[]">' . 
                    $adat['label'] . '</textarea>&nbsp;&nbsp;';
            echo 'Url: <textarea class="tstyle2" name="alnk[]">' . 
                    $adat['url'] . '</textarea>&nbsp;&nbsp;';
            echo 'Click-on text: <textarea class="tstyle3" name="actxt[]">' . 
                    $adat['clickText'] . '</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
                    '<input style="height:18px;width:18px;" type="checkbox" '
                    . 'name="delact[]" value="' . $y . '"><br /><br />';
            $y++;
        }
    }
?>
    <p><em style="color:brown;font-weight:bold;">Add</em> Actual Data:</p>
    <label>Label: </label><input class="tstyle1" name="alabl[]" size="30" />&nbsp;&nbsp;
    <label>Url: </label><input class="tstyle2" name="alnk[]" size="55" />
    <label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="actxt[]" size="30" /><br />
    <label>Label: </label><input class="tstyle1" name="alabl[]" size="30" />&nbsp;&nbsp;
    <label>Url: </label><input class="tstyle2" name="alnk[]" size="55" />
    <label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="actxt[]" size="30" />
    <br /><br />
    <div style="margin-left:8px;">
        <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
            <input type="submit" name="savePg" value="Apply" /></p>
    </div>	
</form>
</div>

</div>
<div class="popupCap"></div>
<!-- jQuery script source is included in photoSelect.php -->
<script src="editDB.js"></script>
</body>
</html>