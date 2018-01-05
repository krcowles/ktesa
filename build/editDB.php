<?php
session_start();
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_GET, 'hno');
$uid = filter_input(INPUT_GET, 'usr');
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
$clusterq = mysqli_query($link, $clusterreq);
if (!$clusterq) {
    die($pstyle . "editDB.php: Failed to extract cluster info from HIKES: " .
        mysqli_error($link) . "</p>");
}
while ($cluster = mysqli_fetch_assoc($clusterq)) {
    $cgrp = $cluster['cgroup'];
    if (strlen($cgrp) !== 0) {
        # no duplicates please (NOTE: "array_unique" leaves holes)
        $match = false;
        for ($i=0; $i<count($groups); $i++) {
            if ($cgrp == $groups[$i]) {
                $match = true;
                break;
            }
        }
        if (!$match) {
            array_push($groups, $cgrp);
            array_push($cnames, $cluster['cname']);
        }
    }
}
mysqli_free_result($clusterq);
$groupCount = count($cnames);
/*
 * EXTRACT ALL DATA, saved in various tabs
 */
$hikereq = "SELECT * FROM EHIKES WHERE indxNo = {$hikeNo};";
$hikeq = mysqli_query($link, $hikereq);
if (!$hikeq) {
    die($pstyle . "editDB.php: Failed to extract hike data from EHIKES: " .
        mysqli_error($link) . "</p>");
}
$hike = mysqli_fetch_assoc($hikeq);
# Although some fields will not be edited, they are needed for xfr into EHIKES
function fetch($var)
{
    $clean = is_null($var) ? '' : $var;
    return trim($clean);
}
$status = trim($hike['stat']);
$hikeTitle = trim($hike['pgTitle']);  # this should never be null!if (is_null($hike['locale'])) {
$hikeLocale = fetch($hike['locale']);
$hikeMarker = fetch($hike['marker']);  # this also should never be null...
$hikeColl = fetch($hike['collection']);
# collection will not be edited
$hikeClusGrp = fetch($hike['cgroup']);
$hikeGrpTip = fetch($hike['cname']);
$hikeStyle = fetch($hike['logistics']);
$hikeMiles = fetch($hike['miles']);
$hikeFeet = fetch($hike['feet']);
$hikeDiff = fetch($hike['diff']);
$hikeFac = fetch($hike['fac']);
$hikeWow = fetch($hike['wow']);
$hikeSeasons = fetch($hike['seasons']);
$hikeExpos = fetch($hike['expo']);
$hikeGpx = fetch($hike['gpx']);
$hikeTrack = fetch($hike['trk']);
# gpx & trk will not be edited
$hikeLat = fetch($hike['lat']);
$hikeLng = fetch($hike['lng']);
$hikeAddImg1 = fetch($hike['aoimg1']);
$hikeAddImg2 = fetch($hike['aoimg2']);
# aoimg1 & aoimg2 will not be edited
$hikeUrl1 = fetch($hike['purl1']);
$hikeUrl2 = fetch($hike['purl2']);
$hikeDirs = fetch($hike['dirs']);
$hikeTips = fetch($hike['tips']);
$hikeDetails = fetch($hike['info']);
mysqli_free_result($hikeq);
?>
<p id="hikeNo" style='display:none'><?php echo $hikeNo;?></p>
<p id="entry" style="display:none"><?php echo $dispTab;?></p>
<em style="color:DarkBlue;font-size:18px;">Any changes below will be made for 
    the hike: "<?php echo $hikeTitle;?>". To save your edits, select the 
    'Apply' button at the bottom. When you are done applying edits, or if no
    edits are being made, you may simply exit this page.
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
<div id="tab1" class="active tab-panel">
<form action="saveTab1.php" method="POST" enctype="multipart/form-data">
    <?php
    require 'tab1display.php';
    ?>
</form>
</div>
<div id="tab2" class="tab-panel">
<form action="newPhotos.php" method="POST">
    <?php
    require 'tab2display.php';
    ?>
</form>            
</div>
<div id='tab3' class='tab-panel'>
<form action="saveTab3.php" method="POST">
    <?php
    require 'tab3display.php';
    ?>
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
    $refq = mysqli_query($link, $refreq);
if (!$refq) {
    die("editDB.php: Failed to extract references from EREFS: " .
        mysqli_error($link));
}
while ($ritem = mysqli_fetch_assoc($refq)) {
    $rid = 'rid' . $z;
    $reftype = 'ref' . $z;
    $thisref = fetch($ritem['rtype']);
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
    $rit1 = fetch($ritem['rit1']);
    $rit2 = fetch($ritem['rit2']);
    if ($thisref === 'Book:' || $thisref === 'Photo Essay:') {
        echo '<label style="text-indent:24px;">Title: </label>'
            . '<textarea style="height:20px;width:320px" name="rit1[]">' .
            $rit1 . '</textarea>&nbsp;&nbsp;';
        echo '<label>Author: </label>'
            . '<textarea style="height:20px;width:320px" name="rit2[]">' .
            $rit2 . '</textarea>&nbsp;&nbsp;'
            . '<label>Delete: </label>' .
            '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="'.
            $z . '"><br /><br />' . "\n";
    } elseif ($thisref === 'Text') {
        echo '<label>Text only item: </label><textarea style="height:20px;width:320px;" name="rit1[]">' .
            $rit1 . '</textarea><label>Delete: </label>' .
            '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="' .
            $z . '"><br /><br />' . "\n";
    } else {
        echo '<label>Item link: </label><textarea style="height:20px;width:500px;" name="rit1[]">' .
            $rit1 . '</textarea>&nbsp;&nbsp;<label>Cick text: </label><textarea style="height:20px;width:330px;" name="rit2[]">' .
            $rit2 . '</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
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
    $propq = mysqli_query($link, $propreq);
if (!$propq) {
    die("editDB.php: Failed to extract Proposed Data from EGPSDAT: " .
        mysqli_error($link));
}
if (mysqli_num_rows($propq) !== 0) {
    $x = 0;
    while ($pdat = mysqli_fetch_assoc($propq)) {
        $pl = fetch($pdat['label']);
        $pu = fetch($pdat['url']);
        $pc = fetch($pdat['clickText']);
        echo 'Label: <textarea class="tstyle1" name="plabl[]">' .
                $pl . '</textarea>&nbsp;&nbsp;';
        echo 'Url: <textarea class="tstyle2" name="plnk[]">' .
                $pu . '</textarea>&nbsp;&nbsp;';
        echo 'Click-on text: <textarea class="tstyle3" name="pctxt[]">' .
                $pc . '</textarea>&nbsp;&nbsp;'
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
    $actq = mysqli_query($link, $actreq);
if (!$actq) {
    die("editDB.php: Failed to extract Actual Data from EGPSDAT: " .
        mysqli_error($link));
}
if (mysqli_num_rows !== 0) {
    $y = 0;
    while ($adat = mysqli_fetch_assoc($actq)) {
        $al = fetch($adat['label']);
        $au = fetch($adat['url']);
        $ac = fetch($adat['clickText']);
        echo 'Label: <textarea class="tstyle1" name="alabl[]">' .
                $ac . '</textarea>&nbsp;&nbsp;';
        echo 'Url: <textarea class="tstyle2" name="alnk[]">' .
                $au . '</textarea>&nbsp;&nbsp;';
        echo 'Click-on text: <textarea class="tstyle3" name="actxt[]">' .
                $ac . '</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
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
