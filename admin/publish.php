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
        $state = $hike['stat'];
        $status = substr($state,0,3);
        if (strlen($state) > 3) {
            $pubHike = substr($state,3,strlen($state)-3);
            if ($pubHike <= 0) {
                die("publish.php: Impossible hikeNo in HIKES");
            }
        }
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
            $actionreq = "UPDATE HIKES SET pgTitle = '{$pg}',usrid = '{$ud}'," .
                "locale = '{$lo}',marker = '{$mr}',collection = '{$co}'," .
                "cgroup = '{$cg}',cname = '{$cn}',logistics = '{$lg}'," .
                "miles = '{$mi}',feet = '{$ft}',diff = '{$df}',fac = '{$fa}'," .
                "wow = '{$ww}',seasons = '{$sn}',expo = '{$ex}',gpx = '{$gx}'," .
                "trk = '{$tk}',lat = '{$la}',lng = '{$ln}',aoimg1 = '{$a1}'," .
                "aoimg2 = '{$a2}',purl1 = '{$p1}',purl2 = '{$p2}'," .
                "dirs = '{$dr}',tips = '{$tp}',info = '{$in}' WHERE indxNo = " .
                "{$pubHike};";
        } elseif ($status === 'new' || $status === 'upl') {
            die('<p style="color:brown;">This hike is not ready for publication! ' .
                'The status field is ' . $status . '</p>');
        } elseif ($status === 'sub') {
            $actionreq = "INSERT INTO HIKES (pgTitle,usrid,locale,marker," .
                "collection,cgroup,cname,logistics,miles,feet,diff,fac,wow," .
                "seasons,expo,gpx,trk,lat,lng,aoimg1,aoimg2,purl1,purl2,dirs," .
                "tips,info) VALUES ('{$pg}','{$ud}','{$lo}','{$mr}','{$co}'," .
                "'{$cg}','{$cn}','{$lg}','{$mi}','{$ft}','{$df}','{$fa}'," .
                "'{$ww}','{$sn}','{$ex}','{$gx}','{$tk}','{$la}','{$ln}'," .
                "'{$a1}','{$a2}','{$p1}','{$p2}','{$dr}','{$tp}','{$in}');";
        }
        $action = mysqli_query($link,$actionreq);
        if (!$action) {
            die("publish.php: Failed to release - HIKES update failed: " . 
                mysqli_error($link));
        }
        mysqli_free_result($action);
        # Assign the hike number for the remaining tables based on status:
        if ($status === 'sub') { # this will be the newly added no.
            $lastidreq = "SELECT indxNo FROM HIKES ORDER BY indxNo DESC LIMIT 1;";
            $lastid = mysqli_query($link,$lastidreq);
            if (!$lastid) {
                die("publish.php: Failed to extract added hike number: " .
                    mysqli_error($link));
            }
            $id = mysqli_fetch_row($lastid);
            $indxNo = $id[0];
            /* NOTE: If this newly submitted hike (not previously published) is
             * a hike that is of type 'At VC', then the index page table for that
             * Visitor Center needs to be updated with the newly added hike
             */
            if (trim($hike['marker']) === 'At VC') {
                $sunIcon = '<img class="expShift" src="../images/sun.jpg" alt="Sunny icon" />';
                $partialIcon = '<img class="expShift" src="../images/greenshade.jpg" alt="Partial shade icon" />';
                $shadeIcon = '<img class="expShift" src="../images/shady.png" alt="Partial sun/shade icon" />';
                $newEntry = "<tr>\n<td>{$pg}<\td>\n";
                $newEntry .= '<td><a href="hikePageTemplate.php?hikeIndx=' . 
                    $indxNo . '" target="_blank">' . PHP_EOL;
                $newEntry .= '<img class="webShift" src="../images/greencheck.jpg" ' .
                    'alt="website click-on icon" /></a></td>' . PHP_EOL;
                $miles = round($mi,2);
                $newEntry .= "<td>{$miles} miles</td>\n<td>{$ft} feet</td>\n";
                if ($ex === 'Full sun') {
                    $newEntry .= "<td>" . $sunIcon . "</td>\n";
                } elseif ($ex === 'Good shade') {
                    $newEntry .= "<td>" . $shadeIcon . "</td>\n";
                } else {
                    $newEntry .= "<td>" . $partialIcon . "</td>\n";
                }
                $newEntry .= '<td><a href="' . $p1 . 'target="_blank">';
                $newEntry .= '<img class="flckrShift" src="../images/album_lnk.png" ' .
                    'alt="Photos symbol" /></a></td>' . "\n</tr>\n";
                # Get the correct index page:
                $ixReq = "SELECT aoimg1 FROM HIKES WHERE indxNo = {$co};";
                $ix = mysqli_query($link,$ixReq);
                if (!$ix) {
                    die("publish.php: Failed to extract value for serialized " .
                        "table from Index Page {$co}: " . mysqli_error($link));
                }
                $tbls = mysqli_fetch_row($ix);
                $oldtbls = unserialize($tbls[0]);
                $tbl = $oldtbls . $newEntry;
                mysqli_free_result($ix);
                echo $tbl;
                $ixtbl = serialize($tbl);
                $newtbl = mysqli_real_escape_string($link,$ixtbl);
                $updtReq = "UPDATE EHIKES SET aoimg1 = '{$newtbl}' WHERE " .
                    "indxNo = {$co};";
                $updt = mysqli_query($link,$updtReq);
                if (!$updt) {
                    die("publish.php: Failed to update table for index page {$co}: " .
                        mysqli_error($link));
                }
                mysqli_free_result($updt);
            }
            
        } else { # this will be the hike being modified, already on the site
            $indxNo = $pubHike;
        }
        /* 
         * In the cases of EGPSDAT, EREFS, and ETSV, elements may have been
         * deleted during edit, therefore, remove ALL the old data if the
         * hike was type 'pub'. Insert new data (no UPDATEs, only INSERTs)
         */
        # ---------------------  GPSDAT -------------------
        if ($status === 'pub') { # eliminate any existing data
            $delreq = "DELETE FROM GPSDAT WHERE indxNo = '{$pubHike}';";
            $del = mysqli_query($link,$delreq);
            if (!$del) {
                die("publish.php: Failed to delete data from GPSDAT for hike " .
                "{$pubHike}: " . mysqli_error($link));
            }
            mysqli_free_result($del);
        }
        $gpsreq = "SELECT * FROM EGPSDAT WHERE indxNo = {$hikeNo};";
        $gpsdat = mysqli_query($link,$gpsreq);
        if (!$gpsdat) {
            die("publish.php: Failed to extract GPS data from EGPSDAT: " .
                mysqli_error($link));
        }
        while ($ginfo = mysqli_fetch_assoc($gpsdat)) {
            $dat = mysqli_real_escape_string($link,$ginfo['datType']);
            $lbl = mysqli_real_escape_string($link,$ginfo['label']);
            $loc = mysqli_real_escape_string($link,$ginfo['url']);
            $cot = mysqli_real_escape_string($link,$ginfo['clickText']);
            $addreq = "INSERT INTO GPSDAT (indxNo,datType,label,url,clickText) " .
                "VALUES ('{$indxNo}','{$dat}','{$lbl}','{$loc}','{$cot}');";
            $add = mysqli_query($link,$addreq);
            if (!$add) {
                die("publish.php: Failed to add new GPSDAT for hike {$indxNo}: " .
                    mysqli_error($link));
            }
        }
        mysqli_free_result($add);
        mysqli_free_result($gpsdat);
        # ---------------------  REFS -------------------
        if ($status === 'pub') {
            $delreq = "DELETE FROM REFS WHERE indxNo = '{$pubHike}';";
            $del = mysqli_query($link,$delreq);
            if (!$del) {
                die("publish.php: Failed to delete data from REFS for hike " .
                "{$pubHike}: " . mysqli_error($link));
            }
            mysqli_free_result($del);
        }
        $refreq = "SELECT * FROM EREFS WHERE indxNo = {$hikeNo};";
        $refdat = mysqli_query($link,$refreq);
        if (!$refdat) {
            die("publish.php: Failed to extract references from EREFS: " .
                mysqli_error($link));
        }
        while ($ref = mysqli_fetch_assoc($refdat)) {
            $rt = mysqli_real_escape_string($link,$ref['rtype']);
            $r1 = mysqli_real_escape_string($link,$ref['rit1']);
            $r2 = mysqli_real_escape_string($link,$ref['rit2']);
            $addrefreq = "INSERT INTO REFS (indxNo,rtype,rit1,rit2) VALUES " .
                "('{$indxNo}','{$rt}','{$r1}','{$r2}');";
            $addref = mysqli_query($link,$addrefreq);
            if (!$addref) {
                die("publish.php: Failed to add references for hike {$indxNo}: " .
                    mysqli_error($link));
            } 
        }
        mysqli_free_result($addref);
        mysqli_free_result($refdat);
        # ---------------------  TSV -------------------
        if ($status === 'pub') {
            $delreq = "DELETE FROM TSV WHERE indxNo = '{$pubHike}';";
            $del = mysqli_query($link,$delreq);
            if (!$del) {
                die("publish.php: Failed to delete pics from TSV for hike " .
                "{$pubHike}: " . mysqli_error($link));
            }
            mysqli_free_result($del);
        }
        $picreq = "SELECT * FROM ETSV WHERE indxNo = {$hikeNo};";
        $picdat = mysqli_query($link,$picreq);
        if (!$picdat) {
            die("publish.php: Failed to extract pic data from ETSV: " .
                mysqli_error($link));
        } 
        while ($pic = mysqli_fetch_assoc($picdat)) {
            $f = mysqli_real_escape_string($link,$pic['folder']);
            $ti = mysqli_real_escape_string($link,$pic['title']);
            $h = mysqli_real_escape_string($link,$pic['hpg']);
            $m = mysqli_real_escape_string($link,$pic['mpg']);
            $de = mysqli_real_escape_string($link,$pic['desc']);
            $la = mysqli_real_escape_string($link,$pic['lat']);
            $lo = mysqli_real_escape_string($link,$pic['lng']);
            $th = mysqli_real_escape_string($link,$pic['thumb']);
            $al = mysqli_real_escape_string($link,$pic['alblnk']);
            $dt = mysqli_real_escape_string($link,$pic['date']);
            $md = mysqli_real_escape_string($link,$pic['mid']);
            $ht = mysqli_real_escape_string($link,$pic['imgHt']);
            $wd = mysqli_real_escape_string($link,$pic['imgWd']);
            $ic = mysqli_real_escape_string($link,$pic['iclr']);
            $or = mysqli_real_escape_string($link,$pic['org']);
            $picreq = "INSERT INTO TSV (indxNo,folder,title,hpg,mpg,`desc`," .
                "lat,lng,thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) VALUES " .
                "('{$indxNo}','{$f}','{$ti}','{$h}','{$m}','{$de}','{$la}'," .
                "'{$lo}','{$th}','{$al}','{$dt}','{$md}','{$ht}','{$wd}'," .
                "'{$ic}','{$or}');";
            $pics = mysqli_query($link,$picreq);
            if (!$pics) {
                die("publish.php: Failed to add pic data to TSV for hike " .
                "{$indxNo}: " . mysqli_error($link));
            }
        }
        mysqli_free_result($pics);
        mysqli_free_result($picdat);

        /* Regardless of state, remove this hike from EHIKES et al:
         * Foreign Keys ensures deletion in remaining E-tables
         */
        $remHikeReq = "DELETE FROM EHIKES WHERE indxNo = {$hikeNo};";
        $remHike = mysqli_query($link,$remHikeReq);
        if (!$remHike) {
            die("publish.php: Failed to remove hike {$hikeNo} from EHIKES: " .
                mysqli_error($link));
        }
        echo "<p>Hike has been removed from the list of New/In-Edit Hikes</p>";
        mysqli_free_result($remHike);
    }
    mysqli_free_result($ehike);
    
    ?>
    <p>E-Hike <?php echo $hikeNo;?> Has Been Released to the Main Site and 
        may now be viewed from the main page</p>
</div>
</body>
</html>