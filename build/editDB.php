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
<p id="trail">Hike Page Editor</p>
<div id="main" style="padding:16px;">
<?php
    # Error output styling string:
    $pstyle = '<p style="color:red;font-size:18px;">';
    
    $xmlDB = simplexml_load_file('../data/database.xml');
    if ($xmlDB === false) {
        $nold = $pstyle . "Cannot load the xml database: contact Site Master;</p>";
        die ($nold);
    }
    
    $hikeNo = filter_input(INPUT_GET,'hikeNo');
    # Below: pull out the available cluster groups and establish association
    # with cluster group name for displaying in drop-down <select>
    $groups = [];
    $cnames = [];
    foreach ($xmlDB->row as $hikeRow) {
        $cgrp = $hikeRow->clusGrp->__toString();
        if ( strlen($cgrp) !== 0) {
            # no duplicates please (NOTE: "array_unique" leaves holes)
            $match = false;
            for ($i=0; $i<count($groups); $i++) {
                #echo " -Now:" . count($groups);
                if ($cgrp == $groups[$i]) {
                    $match = true;
                    break;
                }  
            }
            if (!$match) {
                $grpPopup = $hikeRow->cgName->__toString();
                array_push($groups,$cgrp);
                array_push($cnames,$grpPopup);
            }
        }
        if ( $hikeRow->indxNo == $hikeNo) {
            $hikeTitle = $hikeRow->pgTitle->__toString();
            $hikeLocale = $hikeRow->locale->__toString();
            $hikeMarker = $hikeRow->marker->__toString();
            $hikeClusGrp = $hikeRow->clusGrp->__toString();
            $hikeStyle = $hikeRow->logistics->__toString();
            $hikeMiles = $hikeRow->miles->__toString();
            $hikeFeet = $hikeRow->feet->__toString();
            $hikeDiff = $hikeRow->difficulty->__toString();
            $hikeFac = $hikeRow->facilities->__toString();
            $hikeWow = $hikeRow->wow->__toString();
            $hikeSeasons = $hikeRow->seasons->__toString();
            $hikeExpos = $hikeRow->expo->__toString();
            $hikeGpx = $hikeRow->gpxfile->__toString();
            $hikeTrack = $hikeRow->trkfile->__toString();
            $hikeLat = $hikeRow->lat->__toString();
            $hikeLng = $hikeRow->lng->__toString();
            $hikeUrl1 = $hikeRow->mpUrl->__toString();
            $hikeUrl2 = $hikeRow->spUrl->__toString();
            $hikeDirs = $hikeRow->dirs->__toString();
            $hikeGrpTip = $hikeRow->cgName->__toString();
            $hikeTips = $hikeRow->tipsTxt->__toString();
            $hikeDetails = $hikeRow->hikeInfo->__toSTring();
            # the following are needed as xml objects (not strings):
            $hikePhotos = $hikeRow->tsv;
            $hikeRefs = $hikeRow->refs;
            $hikeProp = $hikeRow->dataProp;
            $hikeAct = $hikeRow->dataAct;
        }
    }
    $groupCount = count($cnames)
?>

<form target="_blank" action="saveChanges.php" method="POST">
<?php
echo '<input type="hidden" name="hno" value="' . $hikeNo . '" />';
?>
<em style="color:DarkBlue;font-size:18px;">Any changes below will be made for 
    the hike: "<?php echo $hikeTitle;?>". If no changes are made you may either 
    exit this page or hit the "sbumit" button.
</em><br /><br />
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
<p id="group" style="display:none"><?php echo $hikeGrpTip;?></p>
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
<!--
    This next section is photo editing
-->
<div>
    <p style="color:brown;"><em>Edit captions below each photo as needed. Images with no
            captions (e.g. maps, imported jpgs, etc.) are not shown.</em></p>
<?php
    /* 
     * Create rows showing photos with captions in a textarea box below;
     * Any changes to the captions will be saved to the database
     */
    $rwidth = 940;
    $nomHt = 240;
    $curwidth = 0;
    $rowCnt = 1;
    $rowHtml = '';
    $capHtml = '';
    $delHtml = '';
    $delrows = [];
    $picrows = [];
    $caprows = [];
    $cnt = 0;
    $delItem = 0;
    foreach ($hikePhotos->picDat as $photo) {
        if ($photo->hpg == 'Y') {
            # scale photo dims to nomHt
            $scale = $nomHt/$photo->imgHt;
            $width = intval($scale * $photo->imgWd);
            # description w/o date
            $ecap = $photo->desc;
            if (substr($ecap,0,1) == '"') {
                $elgth = strlen($ecap) - 2;
                $ecap = substr($ecap,1,$elgth);
            }
            $curwidth += $width + 2; # 2px left margin
            if ($curwidth > $rwidth) {
                $rowCnt++;
                array_push($delrows,$delHtml);
                array_push($picrows,$rowHtml);
                array_push($caprows,$capHtml);
                $delHtml = '';
                $rowHtml = '';
                $capHtml = '';
                $curwidth = $width;
                $cnt = 0;
            }
            $rowHtml .= '<img style="margin-left:2px;" height="' . $nomHt . 
                '" width="' . $width . '" src="' . $photo->mid . 
                '" alt="' . $ecap . '" />' . "\n";
            $tawidth = $width - 12;
            $capHtml .= '<textarea name="ecap[]" style="height:64px;width:' . $tawidth . 
                    'px">' . $ecap . "</textarea>\n"; 
            if ($cnt < 1) {
                $delwidth = $width - 66;
            } else {
                $delwidth = $width - 62;
            }
            $delHtml .= '<span style="margin-right:' . $delwidth . 'px;">' .
                '<input type="checkbox" name="delpic[]" value="'
                    . $delItem . '" />&nbsp;Delete</span>';
            $delItem++;
            $cnt++;
        }  
    }
    # last row:
    $rowCnt++;
    array_push($delrows,$delHtml);
    array_push($picrows,$rowHtml);
    array_push($caprows,$capHtml);
    for ($j=0; $j<$rowCnt; $j++) {
        echo $delrows[$j] . "<br />";
        echo $picrows[$j] . "<br />";
        echo $caprows[$j] . "<br />";
    }
    echo "</div>\n";
    
    if ($hikeTips !== '') {
        echo '<p>Tips Text: </p>';
        echo '<textarea id="ttxt" name="tips" rows="10" cols="130">' . $hikeTips . '</textarea><br />' . "\n";
    } else {
        echo '<textarea id="ttxt" name="tips" rows="10" cols="130">' . 
           '[NO TIPS FOUND]' . '</textarea><br />' . "\n";
    }
    
?>
            
<p>Hike Information:</p>
<textarea id="info" name="hinfo" rows="16" cols="130"><?php echo $hikeDetails;?></textarea>
<h3>Hike Reference Sources: (NOTE: Book type cannot be changed - if needed, delete and add a new one)</h3>
<?php
    $z = 0;  # index for creating unique id's
    foreach ($hikeRefs->ref as $ritem) {
        $rid = 'rid' . $z;
        $reftype = 'ref' . $z;
        $thisref = $ritem->rtype->__toString();
        echo '<p id="' . $rid  . '" style="display:none">' . $thisref . "</p>\n";
        echo '<label for="' . $reftype . '">Reference Type: </label>' . "\n";
        echo '<select id="' . $reftype . '" style="height:26px;width:150px;" name="rtype[]">' . "\n";
        echo '<option value="b" >Book</option>' . "\n";
        echo '<option value="p">Photo Essay</option>' . "\n";
        echo '<option value="w">Website</option>' . "\n";
        echo '<option value="h">Website</option>' . "\n"; # leftover category from index pages
        echo '<option value="a">App</option>' . "\n";
        echo '<option value="d">Downloadable Doc</option>' . "\n";
        echo '<option value="l">Blog</option>' . "\n";
        echo '<option value="o">On-line Map</option>' . "\n";
        echo '<option value="m">Magazine</option>' . "\n";
        echo '<option value="s">News Article</option>' . "\n";
        echo '<option value="g">Meetup Group</option>' . "\n";
        echo '<option value="r">Related Link</option>' . "\n";
        echo '<option value="n">Text Only - No Link</option>' . "\n";
        echo '</select><br />' . "\n";
        if ($thisref === 'b' || $thisref === 'p') {
            echo '<label style="text-indent:24px;">Title: </label><textarea style="height:20px;width:320px" name="rit1[]">' .
                $ritem->rit1->__toString() . '</textarea>&nbsp;&nbsp;';
            echo '<label>Author: </label><textarea style="height:20px;width:320px" name="rit2[]">' .
                $ritem->rit2->__toString() . '</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
               '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="'.
                    $z . '"><br /><br />' . "\n";
        } elseif ($thisref === 'n') {
            echo '<label>Text only item: </label><textarea style="height:20px;width:320px;" name="rit1[]">' .
                $ritem->rit1->__toString() . '</textarea><label>Delete: </label>' .
                '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="' .
                $z . '"><br /><br />' . "\n";
        } else {
            echo '<label>Item link: </label><textarea style="height:20px;width:500px;" name="rit1[]">' .
                $ritem->rit1->__toString() . '</textarea>&nbsp;&nbsp;<label>Cick text: </label><textarea style="height:20px;width:330px;" name="rit2[]">' . 
                $ritem->rit2->__toString() . '</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
                '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="' .
                $z . '"><br /><br />' . "\n";
        }  
        $z++;
    }
    echo '<p id="refcnt" style="display:none">' . $z . '</p>';
    echo '<input type="hidden" name = "orgrefs" value="' . $z . '" />';
?>
<p><em style="font-weight:bold;">Add</em> references here:</p>
<p>Select the type of reference and its accompanying data below:</p>
<select id="href1" style="height:26px;" name="rtype[]">
    <option value="b" selected="selected">Book</option>
    <option value="p">Photo Essay</option>
    <option value="w">Website</option>
    <option value="a">App</option>
    <option value="d">Downloadable Doc</option>
    <option value="l">Blog</option>
    <option value="o">On-line Map</option>
    <option value="m">Magazine</option>
    <option value="s">News Article</option>
    <option value="g">Meetup Group</option>
    <option value="r">Related Link</option>
    <option value="n">Text Only - No Link</option>
</select>
Book Title/Link URL:<input id="ritA1" type="text" name="rit1[]" size="55" 
    placeholder="Book Title" />&nbsp;
Author/Click-on Text<input id="ritA2" type="text" name="rit2[]" size="35" 
    placeholder=", by Author Name" /><br /><br />
<select id="href2" style="height:26px;" name="rtype[]">
    <option value="b" selected="selected">Book</option>
    <option value="p">Photo Essay</option>
    <option value="w">Website</option>
    <option value="a">App</option>
    <option value="d">Downloadable Doc</option>
    <option value="l">Blog</option>
    <option value="o">On-line Map</option>
    <option value="m">Magazine</option>
    <option value="s">News Article</option>
    <option value="g">Meetup Group</option>
    <option value="r">Related Link</option>
    <option value="n">Text Only - No Link</option>
</select>
Book Title/Link URL:<input id="ritB1" type="text" name="rit1[]" size="55" 
    placeholder="Book Title" />&nbsp;
Author/Click-on Text<input id="ritB2" type="text" name="rit2[]" size="35" 
    placeholder=", by Author Name" /><br />

<h3>Proposed Data:</h3>
<?php 
    if (strlen($hikeProp) !== 0) {
        $x = 0;
        foreach ($hikeProp->prop as $pdat) {
            echo 'Label: <textarea class="tstyle1" name="plabl[]">' . 
                    $pdat->plbl->__toString() . '</textarea>&nbsp;&nbsp;';
            echo 'Url: <textarea class="tstyle2" name="plnk[]">' . 
                    $pdat->purl->__toString() . '</textarea>&nbsp;&nbsp;';
            echo 'Click-on text: <textarea class="tstyle3" name="pctxt[]">' . 
                    $pdat->pcot->__toString() . '</textarea>&nbsp;&nbsp;'
                    . '<label>Delete: </label>' .
                    '<input style="height:18px;width:18px;" type="checkbox" '
                    . 'name="delprop[]" value="' . $x . '"><br /><br />';
            $x++;
        }
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
    if (strlen($hikeAct) !== 0) {
        $y = 0;
        foreach ($hikeAct->act as $adat) {
            echo 'Label: <textarea class="tstyle1" name="alabl[]">' . 
                    $adat->albl->__toString() . '</textarea>&nbsp;&nbsp;';
            echo 'Url: <textarea class="tstyle2" name="alnk[]">' . 
                    $adat->aurl->__toString() . '</textarea>&nbsp;&nbsp;';
            echo 'Click-on text: <textarea class="tstyle3" name="actxt[]">' . 
                    $adat->acot->__toString() . '</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
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
<h3>Save the changes!</h3>
<p>
    <input type="submit" name="savePg" value="Save Edits" />
</p>

</div>	

</form>

</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="editDB.js"></script>
</body>
</html>