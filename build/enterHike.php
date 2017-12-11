<?php
require_once '../mysql/setenv.php';
$hip = filter_input(INPUT_GET,'hno');  # hike-in-process
$usr = filter_input(INPUT_GET,'usr');
if ($usr === 'mstr') {
    $disp = 'block';
} else {
    $disp = 'none';
}
require 'currentTitles.php'; # list of existing hike page titles
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>New Page Edit</title>
    <meta charset="utf-8" />
    <meta name="description" content="Form for updating new hike data" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="enterHike.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <script type="text/javascript">
        var hnames = <?php echo $hnames;?>;
    </script>
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
<p id="trail">Edit New Page Data</p>

<?php
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
/* Collect cluster info from HIKES table: */
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
    $dat = mysqli_fetch_assoc($specdat);
    $indx = $dat['indxNo'];
    $title = $dat['pgTitle'];
    $marker = $dat['marker'];
    $coll = $dat['collection'];
    $clusltr = $dat['cgroup'];
    $clusnme = $dat['cname'];
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
if ($hip == '0') {  # in this case, all preloads of fields are empty...
    $entrydat = array("indxNo"=>'',"pgTitle"=>'',"locale"=>'',"logistics"=>'',
        "marker"=>'',"collection"=>'',"cgroup"=>'',"cname"=>'',"diff"=>'',
        "miles"=>'',"feet"=>'',"expo"=>'',"fac"=>'',"wow"=>'',"seasons"=>'',
        "lat"=>'',"lng"=>'',"purl1"=>'',"purl2"=>'',"dirs"=>'',"tips"=>'',
        "info"=>'');
} else {
    # Get any data recorded so far...
    $query = "SELECT * FROM EHIKES WHERE indxNo = {$hip}";
    $result = mysqli_query($link,$query);
    if (mysqli_num_rows($result) === 0) {
        die("<h2>Could not find a hike matching index " . $hip . 
                ". Contact Site Master");
    }
    if (!$result) {
        if (Ktesa_Dbug) {
            dbug_print("enterHike.php: Could not extract record for {$hip}: " . 
                    mysqli_error($link));
        } else {
            user_error_msg($rel_addr,6,0);
        }
    }
    $entrydat = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
}
?>
<div id="setup" style="display:<?php echo $disp;?>">
    <h1>STEP 1: Enter Hike Data</h1>
    <p id="intent">[SITE MASTER:] I WANT TO: &nbsp;&nbsp;[data not required are grayed out]</p>
    <input id="ctr" type="radio" name="pageType" value="vcenter" />
    <label id="VC">CREATE A NEW: Visitor Center/Index Page</label><br />
    <input id="reg" type="radio" name="pageType" value="standard" checked />
    <label id="STD">CREATE A NEW: Hike Page 
        (includes hikes from a Visitor Center)</label>
</div>

<div id="theForm">
<form id="hikeData" onsubmit="page_type(this);" method="POST"
    enctype="multipart/form-data">
    <p id="dbloc" style="display:none;"><?php echo $entrydat['locale'];?></p>
    <p id="dblog" style="display:none;"><?php echo $entrydat['logistics'];?></p>
    <p id="dbmrk" style="display:none;"><?php echo $entrydat['marker'];?></p>
    <p id="dbvch" style="display:none"><?php echo $entrydat['collection'];?></p>
    <?php $csel = $entrydat['cgroup'] . ':' . $entrydat['cname'];?>
    <p id="dbcgr" style="display:none;"><?php echo $csel;?></p>
    <p id="dbdif" style="display:none;"><?php echo $entrydat['diff'];?></p>
    <p id="dbexp" style="display:none;"><?php echo $entrydat['expo'];?></p>
    <p id="dbur1" style="display:none;"><?php echo $entrydat['purl1'];?></p>
    <p id="dbur2" style="display:none;"><?php echo $entrydat['purl2'];?></p>
    <input type="hidden" name="hno" value="<?php echo $hip;?>" />
    <input type="hidden" name="usr" value="<?php echo $usr;?>" />
    <input id="stat" type="hidden" name="state" value="std" />
    <fieldset id="basic">
        <legend>Basic Hike Data</legend>
        <label id="pgTitleText" for="htitle">Hike Name (As it will appear 
            in the table):</label> 
        <input id="htitle" type="text" name="hpgTitle" 
               size="35" value="<?php echo $entrydat['pgTitle'];?>" 
               required />&nbsp;&nbsp;
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
        <label id="e1" class="notVC" for="sunny">Full Sun</label>
        <input id="shady" type="radio" name="expos" value="Good shade" />
        <label id="e2" class="notVC" for="shady">Good Shade</label>
        <input id="partly" type="radio" name="expos" value="Mixed sun/shade" />
        <label id="e3" class="notVC" for="partly">Mixed Sun &amp; Shade</label>
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
                <input id="curl1" type="text" name="phpcurl[]" size="80" /><br />
                Type of album:&nbsp;
                <select id="alb2" name="albtype[]">
                    <option value="flckr">Flickr Album</option>
                    <option value="apple">Apple iCloud Album</option>
                    <option value="googl">Google Album</option>
                </select>&nbsp;&nbsp;Album URL:&nbsp;
                <input id="curl2" type="text" name="phpcurl[]" size="80" /><br />
                Type of album:&nbsp;
                <select id="alb3" name="albtype[]">
                    <option value="flckr">Flickr Album</option>
                    <option value="apple">Apple iCloud Album</option>
                    <option value="googl">Google Album</option>
                </select>&nbsp;&nbsp;Album URL:&nbsp;
                <input id="curl3" type="text" name="phpcurl[]" size="80" /><br />
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
                </select><br />
                <em style="color:brown">[NOTE: Icon color not saved until 
                    album is uploaded]</em><br /><br />
            </div>
            <label id="l_gpx" class="notVC" for="gpxfile" style="color:Brown">
                GPX File: [RECOMMENDED]&nbsp;</label>
            <input id="gpxfile" type="file" name="gpxname" /><br /><br />
        </div>
        
        <div class="indxFile">
            OPTIONAL FILES:<br />
            <em style="color:brown;">If you select files (below) to be referenced in the "Proposed" or
            "Actual" Data Sections of "GPS Maps &amp; Data" on the hike page,
            they must be of type '.gpx', '.GPX', '.kml', or '.kmz' for track files,
            or of type '.html' (as with GPSVisualizer files) or '.pdf' for maps</em><br />
            <label for="pmap">Proposed Data: User File1 (Track/Map): &nbsp;</label>
            <input id="pmap" type="file" name="propmap" /><br />
            <label for="pgpx">Proposed Data: User File2 (Track/Map): &nbsp;</label>
            <input id="pgpx" type="file" name="propgpx" /><br />
            <label for="amap">Actual Data: User File1 (Track/Map): &nbsp;</label>
            <input id="amap" type="file" name="actmap" /><br />
            <label for="agpx">Actual Data: User File2 (Track/Map): &nbsp;</label>
            <input id="agpx" type="file" name="actgpx" /><br />
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
        <label id="m1" for="vc">Visitor Center [New Index Page]</label><br />
        <input id="vch" type="radio" name="mstyle" value="ctrhike" />
        <label id="m2" for="vch">Hike At / In Close Proximity To Visitor 
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
        <label id="m3" for="ch">Trailhead Common to Multiple Hikes</label><br />
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
                        $pass = $clnos[$j] . ":" . $clhikes[$j];
                        # Database needs both clusletter & clusname
                        echo '<option value="' . $pass . '">' . 
                                $clhikes[$j] . "</option>\n";
                    }
                    ?>                  
                    </select>
                </div>
                    
        <input id="othr" type="radio" name="mstyle" value="other" />
        <label id="m4" for="othr">All Others</label><br />
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
    if ($hip == '0') {
        $rowcnt = 0;
    } else {
        $refquery = "SELECT * FROM EREFS WHERE indxNo = '{$hip}';";
        $refdata = mysqli_query($link,$refquery);
        if (!$refdata) {
            die ("enterHike.php: Could not access EREFS table'" . mysqli_error($link));
        }
        $rowcnt = mysqli_num_rows($refdata);
    }
    if ($rowcnt === 0) {
        for ($z=0; $z<6; $z++) {
            $rtype[$z] = '';
            $rit1[$z] = '';
            $rit2[$z] = '';
        }
    } else {
        $rcnt = 0;
        while($refs = mysqli_fetch_assoc($refdata)) {
            $rtype[$rcnt] = $refs['rtype'];
            $rit1[$rcnt] = $refs['rit1'];
            $rit2[$rcnt] = $refs['rit2'];
            $rcnt++;
        }
        for ($w=$rcnt; $w<6; $w++){
                $rtype[$w] = '';
                $rit1[$w] = '';
                $rit2[$w] = '';
        }
    }
    mysqli_free_result($refdata);
    echo '<p id="dbrt1" style="display:none">' . $rtype[0] . "</p>\n"; 
    echo '<p id="dbrt2" style="display:none">' . $rtype[1] . "</p>\n";  
    echo '<p id="dbrt3" style="display:none">' . $rtype[2] . "</p>\n"; 
    echo '<p id="dbrt4" style="display:none">' . $rtype[3] . "</p>\n";  
    echo '<p id="dbrt5" style="display:none">' . $rtype[4] . "</p>\n";  
    echo '<p id="dbrt6" style="display:none">' . $rtype[5] . "</p>\n";  
    /*
     * TO INCREASE COUNT:
    echo '<p id="dbrt7" style="display:none">' . $entrydat['ref[6]['rtype . "</p>\n";  
    echo '<p id="dbrt8" style="display:none">' . $entrydat['ref[7]['rtype . "</p>\n"; 
     */
    ?>
    <fieldset id="refdat">
        <legend>Hike References</legend>
        <p>Select the type of reference (up to 6) and its accompanying data below:</p>
        <select id="href1" name="rtype[]">
            <option value="Book:" selected="selected">Book</option>
            <option value="Photo Essay:">Photo Essay</option>
            <option value="Website:">Website</option>
            <option value="App:">App</option>
            <option value="Downloadable Doc:">Downloadable Doc</option>
            <option value="Blog:">Blog</option>
            <option value="Map:">Map</option>
            <option value="Magazine:">Magazine</option>
            <option value="News Article:">News Article</option>
            <option value="Meetup Group:">Meetup Group</option>
            <option value="Related Site:">Related Site</option>
            <option value="Text">Text (100 char max)</option>
        </select>
        Book Title/Link URL:<input id="ritA1" type="text" name="rit1[]" size="45" 
            placeholder="Book Title" value="<?php echo $rit1[0]?>" />&nbsp;
        Author/Click-on Text<input id="ritA2" type="text" name="rit2[]" size="30" 
            placeholder="Author Name" value="<?php echo $rit2[0];?>" /><br />
        <select id="href2" name="rtype[]">
            <option value="Book:" selected="selected">Book</option>
            <option value="Photo Essay:">Photo Essay</option>
            <option value="Website:">Website</option>
            <option value="App:">App</option>
            <option value="Downloadable Doc:">Downloadable Doc</option>
            <option value="Blog:">Blog</option>
            <option value="Map:">Map</option>
            <option value="Magazine:">Magazine</option>
            <option value="News Article:">News Article</option>
            <option value="Meetup Group:">Meetup Group</option>
            <option value="Related Site:">Related Site</option>
            <option value="Text">Text (100 char max)</option>
        </select>
        Book Title/Link URL:<input id="ritB1" type="text" name="rit1[]" size="45" 
            placeholder="Book Title" value="<?php echo $rit1[1];?>" />&nbsp;
        Author/Click-on Text<input id="ritB2" type="text" name="rit2[]" size="30" 
            placeholder="Author Name" value="<?php echo $rit2[1];?>" /><br />
        <select id="href3" name="rtype[]">
            <option value="Book:" selected="selected">Book</option>
            <option value="Photo Essay:">Photo Essay</option>
            <option value="Website:">Website</option>
            <option value="App:">App</option>
            <option value="Downloadable Doc:">Downloadable Doc</option>
            <option value="Blog:">Blog</option>
            <option value="Map:">Map</option>
            <option value="Magazine:">Magazine</option>
            <option value="News Article:">News Article</option>
            <option value="Meetup Group:">Meetup Group</option>
            <option value="Related Site:">Related Site</option>
            <option value="Text">Text (100 char max)</option>
        </select>
        Book Title/Link URL:<input id="ritC1" type="text" name="rit1[]" size="45" 
            placeholder="Book Title" value="<?php echo $rit1[2];?>" />&nbsp;
        Author/Click-on Text<input id="ritC2" type="text" name="rit2[]" size="30" 
            placeholder="Author Name" value="<?php echo $rit2[2];?>" /><br />
        <select id="href4" name="rtype[]">
            <option value="Book:" selected="selected">Book</option>
            <option value="Photo Essay:">Photo Essay</option>
            <option value="Website:">Website</option>
            <option value="App:">App</option>
            <option value="Downloadable Doc:">Downloadable Doc</option>
            <option value="Blog:">Blog</option>
            <option value="Map:">Map</option>
            <option value="Magazine:">Magazine</option>
            <option value="News Article:">News Article</option>
            <option value="Meetup Group:">Meetup Group</option>
            <option value="Related Site:">Related Site</option>
            <option value="Text">Text (100 char max)</option>
        </select>
        Book Title/Link URL:<input id="ritD1" type="text" name="rit1[]" size="45" 
            placeholder="Book Title" value="<?php echo $rit1[3];?>" />&nbsp;
        Author/Click-on Text<input id="ritD2" type="text" name="rit2[]" size="30" 
            placeholder="Author Name" value="<?php echo $rit2[3];?>"/><br />
        <select id="href5" name="rtype[]">
            <option value="Book:" selected="selected">Book</option>
            <option value="Photo Essay:">Photo Essay</option>
            <option value="Website:">Website</option>
            <option value="App:">App</option>
            <option value="Downloadable Doc:">Downloadable Doc</option>
            <option value="Blog:">Blog</option>
            <option value="Map:">Map</option>
            <option value="Magazine:">Magazine</option>
            <option value="News Article:">News Article</option>
            <option value="Meetup Group:">Meetup Group</option>
            <option value="Related Site:">Related Site</option>
            <option value="Text">Text (100 char max)</option>
        </select>
        Book Title/Link URL:<input id="ritE1" type="text" name="rit1[]" size="45" 
            placeholder="Book Title" value="<?php echo $rit1[4];?>" />&nbsp;
        Author/Click-on Text<input id="ritE2" type="text" name="rit2[]" size="30" 
            placeholder="Author Name" value="<?php echo $rit2[4];?>" /><br />
        <select id="href6" name="rtype[]">
            <option value="Book:" selected="selected">Book</option>
            <option value="Photo Essay:">Photo Essay</option>
            <option value="Website:">Website</option>
            <option value="App:">App</option>
            <option value="Downloadable Doc:">Downloadable Doc</option>
            <option value="Blog:">Blog</option>
            <option value="Map:">Map</option>
            <option value="Magazine:">Magazine</option>
            <option value="News Article:">News Article</option>
            <option value="Meetup Group:">Meetup Group</option>
            <option value="Related Site:">Related Site</option>
            <option value="Text">Text (100 char max)</option>
        </select>
        Book Title/Link URL:<input id="ritF1" type="text" name="rit1[]" size="45" 
            placeholder="Book Title" value="<?php echo $rit1[5];?>" />&nbsp;
        Author/Click-on Text<input id="ritF2" type="text" name="rit2[]" size="30" 
            placeholder="Author Name" value="<?php echo $rit2[5];?>" /><br />
        <!-- ADD LATER IF NEEDED
        <select id="href7" name="rtype[]">
            <option value="Book:" selected="selected">Book</option>
            <option value="Photo Essay:">Photo Essay</option>
            <option value="Website:">Website</option>
            <option value="App:">App</option>
            <option value="Downloadable Doc:">Downloadable Doc</option>
            <option value="Blog:">Blog</option>
            <option value="Map:">Map</option>
            <option value="Magazine:">Magazine</option>
            <option value="News Article:">News Article</option>
            <option value="Meetup Group:">Meetup Group</option>
            <option value="Related Site:">Related Site</option>
            <option value="Text">Text (100 char max)</option>
        </select>
        Book Title/Link URL:<input id="ritG1" type="text" name="rit1[]" size="45" 
            placeholder="Book Title" />&nbsp;
        Author/Click-on Text<input id="ritG2" type="text" name="rit2[]" size="30" 
            placeholder="Author Name" /><br />
        <select id="href8" name="rtype[]">
            <option value="Book:" selected="selected">Book</option>
            <option value="Photo Essay:">Photo Essay</option>
            <option value="Website:">Website</option>
            <option value="App:">App</option>
            <option value="Downloadable Doc:">Downloadable Doc</option>
            <option value="Blog:">Blog</option>
            <option value="Map:">Map</option>
            <option value="Magazine:">Magazine</option>
            <option value="News Article:">News Article</option>
            <option value="Meetup Group:">Meetup Group</option>
            <option value="Related Site:">Related Site</option>
            <option value="Text">Text (100 char max)</option>
        </select>
        Book Title/Link URL:<input id="ritH1" type="text" name="rit1[]" size="45" 
            placeholder="Book Title" />&nbsp;
        Author/Click-on Text<input id="ritH2" type="text" name="rit2[]" size="30" 
            placeholder="Author Name" /><br />
        -->
    </fieldset>
    <?php
    # Set proposed data values, if any
    if ($hip == '0') {
        $prows = 0;
    } else {
        $pquery = "SELECT * FROM EGPSDAT WHERE indxNo = '{$hip}' AND datType = 'P';";
        $pdata = mysqli_query($link,$pquery);
        if (!$pdata) {
            die ("enterHike.php: Could not access 'P' in GPSDAT table: " . mysqli_error($link));
        }
        $prows = mysqli_num_rows($pdata);
    }
    if ($prows === 0) {
        for ($a=0; $a<4; $a++) {
            $plbl[$a] = '';
            $purl[$a] = '';
            $pcot[$a] = '';
        }
    } else {
        $pcnt = 0;
        while($props = mysqli_fetch_assoc($pdata)) {
            $plbl[$pcnt] = $props['label'];
            $purl[$pcnt] = $props['url'];
            $pcot[$pcnt] = $props['clickText'];
            $pcnt++;
        }
        for ($y=$pcnt; $y<4; $y++){
                $plbl[$y] = '';
                $purl[$y] = '';
                $pcot[$y] = '';
        } 
    }
    mysqli_free_result($pdata);
    # Set proposed data values, if any
    
    if ($hip == '0') {
        $arows = 0;
    } else {
        $aquery = "SELECT * FROM EGPSDAT WHERE indxNo = '{$hip}' AND datType = 'A';";
        $adata = mysqli_query($link,$aquery);
        if (!$adata) {
            die ("enterHike.php: Could not access 'A' in GPSDAT table: " . mysqli_error($link));
        }
        $arows = mysqli_num_rows($adata);
    }
    if ($arows === 0) {
        for ($a=0; $a<4; $a++) {
            $albl[$a] = '';
            $aurl[$a] = '';
            $acot[$a] = '';
        }
    } else {
        $acnt = 0;
        while($acts = mysqli_fetch_assoc($adata)) {
            $albl[$acnt] = $acts['label'];
            $aurl[$acnt] = $acts['url'];
            $acot[$acnt] = $acts['clickText'];
            $acnt++;
        }
        for ($y=$acnt; $y<4; $y++){
                $albl[$y] = '';
                $aurl[$y] = '';
                $acot[$y] = '';
        } 
    }
    mysqli_free_result($adata);
    ?>
    <div class="honly">
        <fieldset id="datasect">
            <legend>GPS Maps &amp; Data</legend>
            <p>Proposed Hike Data: Choose up to 4 elements (Maps, GPX/KML Files, etc)</p>
            Label Text: <input id="lt1" type="text" name="plbl[]" size="6"
                value="<?php echo $plbl[0];?>" /> 
            Item URL: <input id="ur1" type="text" name="purl[]" size="50"
                value="<?php echo $purl[0];?>" /> 
            Text to Click On: <input id="ct1" type="text" name="pctxt[]" size="30" 
                value="<?php echo $pcot[0];?>" /><br />
            Label Text: <input id="lt2" type="text" name="plbl[]" size="6" 
                value="<?php echo $plbl[1];?>" /> 
            Item URL: <input id="ur2" type="text" name="purl[]" size="50" 
                value="<?php echo $purl[1];?>" />
            Text to Click On: <input id="ct2" type="text" name="pctxt[]" size="30" 
                value="<?php echo $pcot[1];?>" /><br />
            Label Text: <input id="lt3" type="text" name="plbl[]" size="6"
                value="<?php echo $plbl[2];?>" /> 
            Item URL: <input id="ur3" type="text" name="purl[]" size="50" 
                value="<?php echo $purl[2];?>" />
            Text to Click On: <input id="ct3" type="text" name="pctxt[]" size="30" 
                value="<?php echo $pcot[2];?>" /><br />
            Label Text: <input id="lt4" type="text" name="plbl[]" size="6" 
                value="<?php echo $plbl[3];?>" /> 
            Item URL: <input id="ur4" type="text" name="purl[]" size="50" 
                value="<?php echo $purl[3];?>" />
            Text to Click On: <input id="ct4" type="text" name="pctxt[]" size="30" 
                value="<?php echo $pcot[3];?>" /><br />
    
            <p>Actual Hike Data: Choose up to 4 elements (Maps, GPX/KML Files, etc)</p>
            Label Text: <input id="lt5" type="text" name="albl[]" size="6" 
                value="<?php echo $albl[0];?>" /> 
            Item URL: <input id="ur5" type="text" name="aurl[]" size="50" 
                value="<?php echo $aurl[0];?>" />
            Text to Click On: <input id="ct5" type="text" name="actxt[]" size="30" 
                value="<?php echo $acot[0];?>" /><br />
            Label Text: <input id="lt6" type="text" name="albl[]" size="6" 
                value="<?php echo $albl[1];?>" /> 
            Item URL: <input id="ur6" type="text" name="aurl[]" size="50" 
                value="<?php echo $aurl[1];?>" />
            Text to Click On: <input id="ct6" type="text" name="actxt[]" size="30" 
                value="<?php echo $acot[1];?>" /><br />
            Label Text: <input id="lt7" type="text" name="albl[]" size="6" 
                value="<?php echo $albl[2];?>" /> 
            Item URL: <input id="ur7" type="text" name="aurl[]" size="50" 
                value="<?php echo $aurl[2];?>" />
            Text to Click On: <input id="ct7" type="text" name="actxt[]" size="30" 
                value="<?php echo $acot[2];?>" /><br />
            Label Text: <input id="lt8" type="text" name="albl[]" size="6" 
                value="<?php echo $albl[3];?>" /> 
            Item URL: <input id="ur8" type="text" name="aurl[]" size="50" 
                value="<?php echo $aurl[3];?>"/>
            Text to Click On: <input id="ct8" type="text" name="actxt[]" size="30" 
                value="<?php echo $acot[3];?>"/><br />
        </fieldset>
    </div>

    <fieldset id="urls">
        <legend>Other URL's</legend>
        <label class="notVC" for="url1">URL for Photo Album Site:</label>
        <input id="url1" type="text" name="photo1" size="75" 
               value="<?php echo $entrydat['purl1'];?>" /><br />
        <label class="notVC" for="url2">URL for Secondary Photo Album (Tom or Ken):</label>
        <input id="url2" type="text" name="photo2" size="75" 
               value="<?php echo $entrydat['purl2'];?>" /><br />
        <label for="gdir">Google Map Directions:</label> 
        <input id="gdir" type="text" name="dirs" size="150" 
               value="<?php echo $entrydat['dirs'];?>" />
    </fieldset>

    <fieldset id="submissions">
        <input id="val" type="submit" name="valdat" value="Validate Data" />
        <input id="saver" type="submit" name="saveit" value="Save Data" />
        <input id="res" type="reset" value="Clear and Restart" />

    </fieldset>
</form>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="../scripts/modal_setup.js"></script>
<script src="enterHike.js"></script>
</body>

</html>