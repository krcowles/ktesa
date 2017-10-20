<?php
require '../admin/setenv.php';
$hip = filter_input(INPUT_GET,'hikeNo');  # hike-in-process
# This script will always utilize only the EHIKES table
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Hike or Index Page Creation</title>
    <meta charset="utf-8" />
    <meta name="description" content="Form for entering new hike data" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="enterHike.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>

<!-- Setup function to be able to change which php gets called -->
<script type="text/javascript">
    var pageSelector = "validateHike.php"
    function page_type(form) {
        form.action = pageSelector;
    }
</script>

<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Create A New Page</p>

<?php
/* Collect cluster info from HIKES table: */
$lastid = "SELECT indxNo FROM HIKES ORDER BY indxNo DESC LIMIT 1";
$getid = mysqli_query($link,$lastid);
if (!$getid) {
    if (Ktesa_Dbug) {
        dbug_print('enterHike.php: Could not retrieve highest indxNo: ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,6,0);
    }
}
$lastindx = mysqli_fetch_row($getid);
$tblcnt = $lastindx[0];
mysqli_free_result($getid);
$vchikes = [];
$vcnos = [];
$clhikes = [];
$clnos = [];
for ($i=1; $i<=$tblcnt; $i++) {
    $hquery = "SELECT indxNo,pgTitle,marker,collection,cgroup,cname "
            ."FROM HIKES WHERE indxNo = '{$i}'";
    $specdat = mysqli_query($link,$hquery);
    if (!$specdat) {
        if (Ktesa_Dbug) {
            dbug_print('enterHike.php: Could not retrieve vc/cluster info: ' . 
                    mysqli_error($link));
        } else {
            user_error_msg($rel_addr,6,0);
        }
    }
    $dat = mysqli_fetch_row($specdat);
    $indx = $dat[0];
    $title = $dat[1];
    $marker = $dat[2];
    $coll = $dat[3];
    $clusltr = $dat[4];
    $clusnme = $dat[5];
    if($marker == 'Visitor Ctr') {
        array_push($vchikes,$title);
        array_push($vcnos,$indx);
    } elseif ($marker == 'Cluster') {
        $dup = false;
        for ($l=0; $l<count($clhikes); $l++) {
            if ($clhikes[$l] == $clusnme) {
                $dup = true;
            }
        }
        if (!$dup) {
            array_push($clhikes,$clusnme);
            array_push($clnos,$clusltr);
        }
    }
}
mysqli_free_result($specdat);
$vccnt = count($vchikes);
$clcnt = count($clhikes);
# Get any data recorded so far...
$query = "SELECT * FROM EHIKES WHERE indxNo = '{$hip}'";
$result = mysqli_query($link,$query);
if (!$result) {
    if (Ktesa_Dbug) {
        dbug_print("enterHike.php: Could not extract record for {$hip}: " . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,6,0);
    }
}
$entrydat = mysqli_fetch_assoc($result);
?>
<div id="setup">
    <h1>STEP 1: Enter Hike Data</h1>
    <p id="intent">I WANT TO: &nbsp;&nbsp;[data not required are grayed out]</p>
    <input id="ctr" type="radio" name="pageType" value="vcenter" />
    <label id="VC">CREATE A NEW: Visitor Center/Index Page</label><br />
    <input id="reg" type="radio" name="pageType" value="standard" checked />
    <label id="STD">CREATE A NEW: Hike Page 
        (includes hikes from a Visitor Center)</label>
</div>

<div id="theForm">
<form id="hikeData" target="_blank" onsubmit="page_type(this);" method="POST"
    enctype="multipart/form-data">

    <p id="dbhno" style="display:none"><?php echo $entrydat['indxNo'];?></p>
    <p id="dbhnm" style="display:none"><?php echo $entrydat['pgTitle'];?></p>
    <p id="dbloc" style="display:none"><?php echo $entrydat['locale'];?></p>
    <p id="dblog" style="display:none"><?php echo $entrydat['logistics'];?></p>
    <p id="dbmrk" style="display:none"><?php echo $entrydat['marker'];?></p>
    <p id="dbcst" style="display:none"><?php echo $entrydat['cgroup'];?></p>
    <p id="dbcgr" style="display:none"><?php echo $entrydat['cname'];?></p>
    <p id="dbdif" style="display:none"><?php echo $entrydat['diff'];?></p>
    <p id="dbexp" style="display:none"><?php echo $entrydat['expo'];?></p>
    <input type="hidden" name="hno" value="<?php echo $hip;?>" />
    <fieldset id="basic">
        <legend>Basic Hike Data</legend>
        <label id="pgTitleText" for="htitle">Hike Name (As it will appear 
            in the table):</label> 
        <input id="htitle" type="text" name="hpgTitle" 
               size="35" value="<?php echo $entrydat['pgTitle'];?>" />&nbsp;&nbsp;
        <label for="area">Locale (Nearest city/landmark):</label>
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
            <option value="Golden">Golden</option>
            <option value="Cerrillos">Cerrillos</option>
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
        <label class="notVC" for="type">Hike Type:</label>
        <select id="type" name="htype">
            <option value="Loop">Loop</option>
            <option value="Two-Cars">Two-Cars</option>
            <option value="Out-and-back">Out-and-Back</option>
        </select>&nbsp;&nbsp;
        <label class="notVC" for="lgth">Total Length (Miles):</label>
            <input id="lgth" type="text" name="dist" size="6" 
                   value="<?php echo $entrydat['miles'];?>" />
        <label class="notVC" for="ht">Elevation Change (Feet):</label>
            <input id="ht" type="text" name="elev" size="8"
                   value="<?php echo $entrydat['feet'];?>" />
        <label class="notVC" for="ease">Relative Difficulty:</label>
        <select id="ease" name="diff">
            <option value="Easy">Easy</option>
            <option value="Easy-Moderate">Easy-Moderate</option>
            <option value="Moderate">Moderate</option>
            <option value="Med-Difficult">Medium-Difficult</option>
            <option value="Difficult">Difficult</option>
        </select><br />
        <label class="notVC" id="ifac" for="useful">Facilities Available at Trailhead, 
            if any:</label>
        <input id="useful" type="text" name="fac" size="25" 
               value="<?php echo $entrydat['fac'];?>" />
        <label class="notVC" id="iwow" for="wow">"Wow" Factor (What makes this hike 
            special):</label> 
        <input id="wow" type="text" name="wow_factor" size="24" 
               value="<?php echo $entrydat['wow'];?>" /><br />
        <label class="notVC" for="times">Seasons</label>
        <input id="times" type="text" name="seas" size="40" 
               value="<?php echo $entrydat['seasons'];?>" /><br />
    </fieldset>

    <fieldset id="exposure">
        <legend>Exposure Factor</legend>
        <em id="selexp" class="notVC">Select Exposure to Sun: </em>
        <input id="sunny" type="radio" name="expos" value="Full sun" />
        <label class="notVC" for="sunny">Full Sun</label>
        <input id="shady" type="radio" name="expos" value="Good shade" />
        <label class="notVC" for="shady">Good Shade</label>
        <input id="partly" type="radio" name="expos" value="Mixed sun/shade" />
        <label class="notVC" for="partly">Mixed Sun &amp; Shade</label>
    </fieldset>

    <fieldset>
        <legend>File Data</legend>
        <div class="indxFile">
            <em style="font-size:18px;color:Brown;">I don't want to specify 
                pictures at this time:</em>&nbsp;&nbsp;
            <input id="nopics" type="checkbox" name="nopix" /><br />
            <div id="picopt">
                <span style="text-decoration:underline;line-height:200%;">
                    Otherwise:</span><br/>
                Specify one or more on-line photo albums below (currently 3 max) - 
                    types and urls:
                <div style="margin-left:12px;">    
                Type of album:&nbsp;
                <select id="alb1" name="albtype[]">
                    <option value="flckr">Flickr Album</option>
                    <option value="apple">Apple iCloud Album</option>
                    <option value="googl">Google Album</option>
                </select>&nbsp;&nbsp;Album URL:&nbsp;
                <input id="curl1" name="phpcurl[]" size="80" /><br />
                Type of album:&nbsp;
                <select id="alb2" name="albtype[]">
                    <option value="flckr">Flickr Album</option>
                    <option value="apple">Apple iCloud Album</option>
                    <option value="googl">Google Album</option>
                </select>&nbsp;&nbsp;Album URL:&nbsp;
                <input id="curl2" name="phpcurl[]" size="80" /><br />
                Type of album:&nbsp;
                <select id="alb3" name="albtype[]">
                    <option value="flckr">Flickr Album</option>
                    <option value="apple">Apple iCloud Album</option>
                    <option value="googl">Google Album</option>
                </select>&nbsp;&nbsp;Album URL:&nbsp;
                <input id="curl3" name="phpcurl[]" size="80" /><br />
                </div><br />
                
                Select the color of the icon which will be used to mark photo 
                locations on the map:&nbsp;&nbsp;
                <select id="icolor" name="icon">
                    <option value="pink">Pink</option>
                    <option value="red">Red</option>
                    <option value="maroon">Maroon</option>
                    <option value="orange">Orange</option>
                    <option value="yellow">Yellow</option>
                    <option value="olive">Olive</option>
                    <option value="lime">Lime</option>
                    <option value="green">Green</option>
                    <option value="aqua">Aqua</option>
                    <option value="teal">Teal</option>
                    <option value="blue">Blue</option>
                    <option value="navy">Navy</option>
                    <option value="violet">Violet</option>
                    <option value="purple">Purple</option>
                    <option value="fuchsia">Fuchsia</option>
                    <option value="silver">Silver</option>
                    <option value="gray">Gray</option>
                    <option value="black">Black</option>
                    <option value="tan">Tan</option>
                    <option value="brown">Brown</option>
                    <option value="Google default">Google default</option>
                </select><br /><br />
            </div>
            <label id="l_gpx" class="notVC" for="gpxfile" style="color:Brown">
                GPX File: [RECOMMENDED]&nbsp;</label>
            <input id="gpxfile" type="file" name="gpxname" /><br /><br />
        </div>
        
        <div class="indxFile">
            OPTIONAL FILES:<br />
            <em>The following files are to be referenced in the "Proposed" or
            "Actual" Data Sections of "GPS Maps &amp; Data"</em><br />
            <label for="pmap">Proposed Data: User File1 (e.g. Map): &nbsp;</label>
            <input id="pmap" type="file" name="propmap" /> &nbsp;
            Storage Location: &nbsp;<select name="f1">
                <option value="maps">Maps</option>
                <option value="gpx">Gpx</option>
            </select><br />
            <label for="pgpx">Proposed Data: User File2 (e.g. GPX): &nbsp;</label>
            <input id="pgpx" type="file" name="propgpx" /> &nbsp;
            Storage Location: &nbsp;<select name="f2">
                <option value="maps">Maps</option>
                <option value="gpx">Gpx</option>
            </select><br />
            <label for="amap">Actual Data: User File1 (e.g. Map): &nbsp;</label>
            <input id="amap" type="file" name="actmap" /> &nbsp;
            Storage Location: &nbsp;<select name="f3">
                <option value="maps">Maps</option>
                <option value="gpx">Gpx</option>
            </select><br />
            <label for="agpx">Actual Data: User File2 (e.g. GPX): &nbsp;</label>
            <input id="agpx" type="file" name="actgpx" /> &nbsp;
            Storage Location: &nbsp;<select name="f4">
                <option value="maps">Maps</option>
                <option value="gpx">Gpx</option>
            </select><br />
            <em>Additional images (not photos from album) may be specified below:</em><br />
        </div>
        <label id="l_add1" for="addon1">Other image (pop-up captions not 
            provided at this time): &nbsp;</label>
        <input id="addon1" type="file" name="othr1" /><br />
        <label id="l_add2" class="notVC" for="addon2">Other image (pop-up 
            captions not provided at this time): &nbsp;</label>
        <input id="addon2" type="file" name="othr2" /><br />
        
    </fieldset>

    <fieldset id="latlng">
        <legend>Latitude & Longitude of Visitor Center</legend>
        <label for="n-s">Enter decimal values here:</label>
        <input id="n-s" type="text" name="lat" size="16" />&nbsp;&nbsp;
        <label for="e-w">Longitude (decimal value):</label>
        <input id="e-w" type="text" name="lon" size="16" />&nbsp;&nbsp; 
    </fieldset>
        
    <fieldset id="marker">
        <legend>Google Maps Marker Style</legend>
        <input id="vc" type="radio" name="mstyle" value="center" />
        <label for="vc">Visitor Center [New Index Page]</label><br />
        <input id="vch" type="radio" name="mstyle" value="ctrhike" />
        <label for="vch">Hike At / In Close Proximity To Visitor 
            Center</label><br />
        <span style="color:brown;margin-left:32px;">[NOTE: Visitor Center
                Page must already exist:</span>&nbsp; if not, save this page, 
                <span style="text-decoration:underline">exit</span>, and 
                create the new Index Page before restoring this page]<br />
                <div id="newvch" style="margin-left:32px;display:none;">
                    <em style="color:DarkBlue;">Select Visitor Center 
                        associated with this new hike:</em> &nbsp;
                    <select id="nvch" name="vchike">
                    <?php
                    for ($i=0;$i<$vccnt;$i++) {
                        echo '<option value="' . $vcnos[$i] . '">' . 
                                $vchikes[$i] . "</option>\n";
                    }
                    ?>
                    </select>
                </div>
        <input id="ch" type="radio" name="mstyle" value="cluster" />
        <label for="ch">Trailhead Common to Multiple Hikes</label><br />
            <span style="color:brown;margin-left:32px;">[NOTE: Group must already 
            exist in database:</span> &nbsp;if not, save this page, 
                <span style="text-decoration:underline">exit</span>, and edit 
                the companion hike,<br /><span style="margin-left:32px;">providing
                    a new group name before restoring this page]</span><br />
                <div id="newcl" style="margin-left:32px;display:none;">
                    <em style="color:DarkBlue;">Select group in which to 
                        include this new hike:</em> &nbsp;
                    <select id="nclus" name="clusgrp">
                    <?php
                    for ($j=0;$j<$clcnt;$j++) {
                        echo '<option value="' . $clnos[$j] . '">' . 
                                $clhikes[$j] . "</option>\n";
                    }
                    ?>                  
                    </select>
                </div>
                    
        <input id="othr" type="radio" name="mstyle" value="other" />
        <label for="othr">All Others</label><br />
    </fieldset>

    <fieldset id="txtdat">
        <legend>Text Sections</legend>
        <textarea id="usrtips" class="honly" name="tipstxt" rows="10" 
            cols="130"><?php
                if ($entrydat['tips'] == '' ) {
                    echo "[OPTIONAL] Enter 'Tips Text' here";
                } else {
                    echo $entrydat['tips'];
                } ?>
        </textarea><br />
        <textarea id="usrinfo" name="hiketxt" rows="20" cols="130"><?php
                if ($entrydat['info'] == '') {
                    echo "Enter the description of the hike here, as it will " .
                        "appear on the completed hike page...";
                } else {
                    echo $entrydat['info'];
                } ?>
        </textarea>
    </fieldset>

    <?php
    # refs is a serialized array of strings (imploded arrays)
    if ($entrydat['refs'] == '') {
        for ($z=0; $z<6; $z++) {
            $rtype[$z] = '';
            $rit1[$z] = '';
            $rit2[$z] = '';
        }
    } else {
        $refs = unserialize($entrydat['refs']);
        for ($y=0; $y<6; $y++) {
            if ($refs[$y] !== '') {
                $ref = explode("^",$refs[$y]);
                $rtype[$y] = $ref[0];
                $rit1[$y] = $ref[1];
                $rit2[$y] = $ref[2];
            } else {
                $rtype[$y] = '';
                $rit1[$y] = '';
                $rit2[$y] = '';
            }
        }
    }
    echo '<p id="dbrt1" style="display:none">' . $rtype[0] . "</p>\n"; 
    echo '<p id="dbrt2" style="display:none">' . $rtype[1] . "</p>\n";  
    echo '<p id="dbrt3" style="display:none">' . $rtype[2] . "</p>\n"; 
    echo '<p id="dbrt4" style="display:none">' . $rtype[3] . "</p>\n";  
    echo '<p id="dbrt5" style="display:none">' . $rtype[4] . "</p>\n";  
    echo '<p id="dbrt6" style="display:none">' . $rtype[5] . "</p>\n";  
    /*
    echo '<p id="dbrt7" style="display:none">' . $entrydat['ref[6]['rtype . "</p>\n";  
    echo '<p id="dbrt8" style="display:none">' . $entrydat['ref[7]['rtype . "</p>\n"; 
     */
    ?>
    <fieldset id="refdat">
        <legend>Hike References</legend>
        <p>Select the type of reference (up to 8) and its accompanying data below:</p>
        <select id="href1" name="rtype[]">
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
            placeholder="Book Title" value="<?php echo $rit1[0]?>" />&nbsp;
        Author/Click-on Text<input id="ritA2" type="text" name="rit2[]" size="35" 
            placeholder=", by Author" value="<?php echo $rit2[0];?>" /><br />
        <select id="href2" name="rtype[]">
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
            placeholder="Book Title" value="<?php echo $rit1[1];?>" />&nbsp;
        Author/Click-on Text<input id="ritB2" type="text" name="rit2[]" size="35" 
            placeholder=", by Author" value="<?php echo $rit2[1];?>" /><br />
        <select id="href3" name="rtype[]">
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
        Book Title/Link URL:<input id="ritC1" type="text" name="rit1[]" size="55" 
            placeholder="Book Title" value="<?php echo $rit1[2];?>" />&nbsp;
        Author/Click-on Text<input id="ritC2" type="text" name="rit2[]" size="35" 
            placeholder=", by Author" value="<?php echo $rit2[2];?>" /><br />
        <select id="href4" name="rtype[]">
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
        Book Title/Link URL:<input id="ritD1" type="text" name="rit1[]" size="55" 
            placeholder="Book Title" value="<?php echo $rit1[3];?>" />&nbsp;
        Author/Click-on Text<input id="ritD2" type="text" name="rit2[]" size="35" 
            placeholder=", by Author" value="<?php echo $rit2[3];?>"/><br />
        <select id="href5" name="rtype[]">
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
        Book Title/Link URL:<input id="ritE1" type="text" name="rit1[]" size="55" 
            placeholder="Book Title" value="<?php echo $rit1[4];?>" />&nbsp;
        Author/Click-on Text<input id="ritE2" type="text" name="rit2[]" size="35" 
            placeholder=", by Author" value="<?php echo $rit2[4];?>" /><br />
        <select id="href6" name="rtype[]">
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
        Book Title/Link URL:<input id="ritF1" type="text" name="rit1[]" size="55" 
            placeholder="Book Title" value="<?php echo $rit1[5];?>" />&nbsp;
        Author/Click-on Text<input id="ritF2" type="text" name="rit2[]" size="35" 
            placeholder=", by Author" value="<?php echo $rit2[5];?>" /><br />
        
</form>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="../scripts/modal_setup.js"></script>
<script src="enterHike.js"></script>
</body>

</html>