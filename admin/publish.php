<?php
require_once '../mysql/setenv.php';
$hikeNo = filter_input(INPUT_GET,'hno');
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Release to Main Site</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Release EHIKE No. <?php echo $hikeNo;?></p>
<div style="margin-left:16px;font-size:22px">
    <?php
    $oldquery = "SELECT * FROM EHIKES WHERE indxNo = {$hikeNo};";
    $ehike = mysqli_query($link,$oldquery);
    if (!$ehike) {
        die("publish.php: EHIKE could not be retrieved: " . mysqli_error($link));
    }
    if (mysqli_num_rows($ehike) === 0) {
        echo "<p style=color:brown>Hike {$hikeNo} has no data!</p>";
    } else {
        $hike = mysqli_fetch_assoc($ehike);
        $status = $hike['stat'];
        $pg = mysqli_real_escape_string($link,$hike['pgTitle']);
        $ud = mysqli_real_escape_string($link,$hike['usrid']);
        $lo = mysqli_real_escape_string($link,$hike['locale']);
        $mr = mysqli_real_escape_string($link,$hike['marker']);
        $co = mysqli_real_escape_string($link,$hike['collection']);
        $cg = mysqli_real_escape_string($link,$hike['cgroup']);
        $cn = mysqli_real_escape_string($link,$hike['cname']);
        $lg = mysqli_real_escape_string($link,$hike['logistics']);
        $mi = mysqli_real_escape_string($link,$hike['miles']);
        $ft = mysqli_real_escape_string($link,$hike['feet']);
        $df = mysqli_real_escape_string($link,$hike['diff']);
        $fa = mysqli_real_escape_string($link,$hike['fac']);
        $ww = mysqli_real_escape_string($link,$hike['wow']);
        $sn = mysqli_real_escape_string($link,$hike['seasons']);
        $ex = mysqli_real_escape_string($link,$hike['expo']);
        $gx = mysqli_real_escape_string($link,$hike['gpx']);
        $tk = mysqli_real_escape_string($link,$hike['trk']);
        $la = mysqli_real_escape_string($link,$hike['lat']);
        $ln = mysqli_real_escape_string($link,$hike['lng']);
        $a1 = mysqli_real_escape_string($link,$hike['aoimg1']);
        $a2 = mysqli_real_escape_string($link,$hike['aoimg2']);
        $p1 = mysqli_real_escape_string($link,$hike['purl1']);
        $p2 = mysqli_real_escape_string($link,$hike['purl2']);
        $dr = mysqli_real_escape_string($link,$hike['dirs']);
        $tp = mysqli_real_escape_string($link,$hike['tips']);
        $in = mysqli_real_escape_string($link,$hike['info']);
        if ($status === 'pub') { # don't add this hike, update it
            # ****** TEMPORARILY FOR VERIFICATION:
            $addit = "INSERT INTO HIKES (pgTitle,usrid,locale,marker," .
                "collection,cgroup,cname,logistics,miles,feet,diff,fac,wow," .
                "seasons,expo,gpx,trk,lat,lng,aoimg1,aoimg2,purl1,purl2,dirs," .
                "tips,info) VALUES ('{$pg}','{$ud}','{$lo}','{$mr}','{$co}'," .
                "'{$cg}','{$cn}','{$lg}','{$mi}','{$ft}','{$df}','{$fa}'," .
                "'{$ww}','{$sn}','{$ex}','{$gx}','{$tk}','{$la}','{$ln}'," .
                "'{$a1}','{$a2}','{$p1}','{$p2}','{$dr}','{$tp}','{$in}');";
            echo $addit;
        } elseif ($status === 'new' || $status === 'upl') {
            echo '<p style="color:brown;">This hike is not ready for publication! ' .
                'The status field is ' . $status . '</p>';
        } elseif ($status === 'sub') {
            
        }
    }
    ?>
    <p>Hike <?php echo $hikeNo;?> Has Been Released to the Main Site and 
        may now be viewed from the main page</p>
</div>
</body>
</html>