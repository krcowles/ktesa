<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Edit Database</title>
    <meta charset="utf-8" />
    <meta name="description" content="Edit the selected Index Pg" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="editIndx.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>

<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>

<div style="padding:16px;">
<?php
    $database = '../data/database.xml';
    $xmlDat = simplexml_load_file($database);
    if ($xmlDat === false) {
        $emsg = '<p style="color:red;font-size:20px;margin-left:16px">' .
                'Could not load xml database: contact site master</p>';
        die($emsg);
    }
    echo "LOADED";
    $hikeNo = $_GET['hikeNo'];
    echo "NO " . $hikeNo;
    foreach( $xmlDat->row as $indxrow) {
        if ( $indxrow->indxNo == $hikeNo) {
            $indxName = $indxrow->pgTitle;
            echo "GOT: " . $indxName;
            $indxLocale = $indxrow->locale;
            $indxLat = $indxrow->lat;
            $indxLng = $indxrow->lng;
            $indxDirs = $indxrow->dirs;
            $indxInfo = $indxrow->hikeInfo;
            $indxRefs = $indxrow->refs;
            break;
        }
    }
?>
<p id="trail"><?php echo $indxName;?></p>

<form target="_blank" action="saveIndxChgs.php" method="POST">

<em style="color:DarkBlue;">Any changes below will be made for the Index Page: 
    "<?php echo $indxName;?>". If no changes 
are made you may either exit this page or hit the "sbumit" button.</em><br /><br />

<label for="hike">Index Page Name: </label>
<textarea style="height:20px;" id="hike" name="hname">
    <?php echo $indxName?></textarea>&nbsp;&nbsp;
    
<p style="display:none;" id="locality"><?php echo $indxLocale;?></p>
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
    <option value="Taos">Taos</option>
    <option value="Pilar">Pilar</option>
    <option value="Villanueva">Villanueva</option>
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
</select><br />

<label for="lat">Visitor Center Location: Latitude </label>
<textarea style="height:20px;" id="lat" name="hlat"><?php echo $indxLat;?></textarea>&nbsp;&nbsp;
<label for="lon">Longitude </label>
<textarea style="height:20px;" id="lon" name="hlon"><?php echo $indxLng;?></textarea><br /><br />

Enter or change the Google Maps Directions to the Visitor Center [NOTE: this is a single line, despite text-wrapping]<br />
<textarea id="vcdirs" name="gdirs" rows="1" cols="140" wrap="soft">
    <?php echo $indxDirs;?></textarea><br /><br />

Edit the Park Information as desired:<br />
<textarea name="info" rows="12" cols="120" wrap="soft">
    <?php echo $indxInfo;?></textarea><br /><br />
    
<h3>Hike Reference Sources: (NOTE: Book type cannot be changed - if needed, 
    delete and add a new one)</h3>
<?php
    /*
     * References display
     */
    $noOfRefs = 0;
    foreach ($indxRefs->ref as $ref) {
        $noOfRefs++;
    }
    echo '<p id="refcnt" style="display:none">' . $noOfRefs . '</p>';
    $z = 0;  # index for creating unique id's
    foreach ($indxRefs->ref as $ritem) {
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
            echo '<label style="text-indent:24px;">Title: </label>'
                . '<textarea style="height:20px;width:320px" name="rit1[]">' .
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
                $ritem->rit1->__toString() . '</textarea>&nbsp;&nbsp;'
                    . '<label>Cick text: </label><textarea style="height:20px;width:330px;" name="rit2[]">' . 
                $ritem->rit2->__toString() . '</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
                '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="' .
                $z . '"><br /><br />' . "\n";
        }  
        $z++;
    }
    # Table Header setup
    $tblhtml = '<table id="siteIndx">' . "\n" . '<thead>' . "\n" . '<tr>' . "\n";
    $tblhtml .= '<th class="hdrRow" scope="col">Trail</th>' . "\n";
    $tblhtml .= '<th class="hdrRow" scope="col">Web Pg</th>' . "\n";
    $tblhtml .= '<th class="hdrRow" scope="col">Trail Length</th>' . "\n";
    $tblhtml .= '<th class="hdrRow" scope="col">Elevation</th>' . "\n";
    $tblhtml .= '<th class="hdrRow" scope="col">Exposure</th>' . "\n";
    $tblhtml .= '<th class="hdrRow" scope="col">Photos</th>'  . "\n";
    $tblhtml .= '</tr>' . "\n" . '</thead>' . "\n" . "<tbody></tbody></table>\n";
?>
<p>Add references here:</p>
<p>Select the type of reference (with above, up to 8 total) and its accompanying data below:</p>
<select style="height:26px;" name="rtype[]">
    <option value="b">Book</option>
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
Book Title/Link URL:<input type="text" name="rit1[]" size="55" />&nbsp;
Author/Click-on Text<input type="text" name="rit2[]" size="35" /><br /><br />
<select style="height:26px;" name="rtype[]">
    <option value="b">Book</option>
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
Book Title/Link URL:<input type="text" name="rit1[]" size="55" />&nbsp;
Author/Click-on Text<input type="text" name="rit2[]" size="35" /><br />

<h2>The Table of Hikes Cannot Be Edited At This Time</h2>
<?php
    echo $tblhtml;
/*
    $rowcnt = count($rows);
    for ($j=0; $j<$rowcnt; $j++) {
        $row = explode("^",$rows[$j]);
        # there are always 7 pieces, counting row type
        if ($row[0] === 'n') {  // "normal" - not grayed out
            $tblhtml .= '<tr>' . "\n" . '<td>' . $row[1] . '</td>' . "\n";
            $tblhtml .= '<td><a href="' . $row[2] . '" target="_blank">' . "\n" .
                '<img class="webShift" src="../images/greencheck.jpg" alt="checkbox" /></a></td>' . "\n";
            $tblhtml .= '<td>' . $row[3] . '</td>' . "\n";
            $tblhtml .= '<td>' . $row[4] . '</td>' . "\n";
            $tblhtml .= '<td><img class="expShift" src="' . $row[5] . '" alt="exposure icon" /></td>' . "\n";
            $tblhtml .= '<td><a href="' . $row[6] . '" target="_blank">' . "\n" .
                '<img class="flckrShift" src="../images/album_lnk.png" alt="Photos symbol" /></a></td>' . "\n";
            $tblhtml .= '</tr>' . "\n";
        } else {   // $row[0]=g  - grayed out row
            $tblhtml .= '<tr>' . "\n" . '<td>' . $row[1] . '</td>' . "\n";
            $tblhtml .= '<td><img class="webShift" src="../images/x-box.png" alt="box with x" /></td>' . "\n";
            $tblhtml .= '<td>' . $row[3] . '</td>' . "\n";
            $tblhtml .= '<td>' . $row[4] . '</td>' . "\n";
            $tblhtml .= '<td class="naShift">N/A</td>' . "\n";
            $tblhtml .= '<td><img class="flckrShift" src="../images/x-box.png" alt="box with x" /></td>' . "\n";
            $tblhtml .= '</tr>' . "\n";
        }  # end of if row is grayed out...
    }  #end of row data-processing loop	
    $tblhtml .= '</tbody>' . "\n" . '</table>' . "\n";
    $indxTbl = $tblhtml;
 */	   
?>	
<input type="hidden" name="hno" value="<?php echo $hikeNo;?>" />

<div style="margin-left:8px;">
<h3>Select an option below to save the edits</h3>

<p><em>All Users:</em> Save Current Changes and Re-edit Later&nbsp;&nbsp;
    <input type="submit" name="savePg" value="Save for Re-edit" />
</p>
<p><em>Site Master:</em> Enter Password to Save to Site&nbsp;&nbsp;
    <input id="master" type="password" name="mpass" size="12" maxlength="10" 
        title="8-character code required" />&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="submit" name="savePg" value="Site Master" />
</p>
<p><em>Registered Users:</em> Select button to submit for review&nbsp;&nbsp;
    <input type="submit" name="savePg" value="Submit for Review" />
</p>
</div>	

</form>

</div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="editIndx.js"></script>
</body>
</html>