<?php
function uploadErr($errdat) {
    if ($errdat === UPLOAD_ERR_INI_SIZE || $errdat === UPLOAD_ERR_FORM_SIZE) {
        return 'File is too large for upload';
    }
    if ($errdat === UPLOAD_ERR_PARTIAL) {
        return 'The file was only partially uploaded (no further information';
    }
    if ($errdat === UPLOAD_ERR_NO_FILE) {
        return 'The file was not uploaded (no further information';
    }
    if ($errdat === UPLOAD_ERR_CANT_WRITE) {
        return 'Failed to write file to disk';
    }
    if ($errdat === UPLOAD_ERR_EXTENSION) {
        return 'A PHP extension stopped the upload';
    }
}
function dupFileName($oldname) {
    $extpos = strrpos($oldname,".");
    $fbase = substr($oldname,0,$extpos) . '_DUP.';
    $extpos++;
    $extlgth = strlen($oldname) - $extpos;
    $fext = substr($oldname,$extpos,$extlgth);
    $newname = $fbase . $fext;
    $fout = '<p style="margin-left:20px;margin-top:-12px;color:brown;">' .
        '<em>NOTE: ' . $oldname . ' has been previously saved on the '.
        'server;</em> A new file name was created: ' . $newname . '</p>';
    echo $fout;
    return $newname;
}
/* 
 * This script checks for uploaded files, looking for presence / absence of
 * files. The data type for each uploaded file is also checked for correctness.
 * If a filename is found corresponding to an existing host file, the user
 * is alerted, and an alternate name is saved to the site. If an error occurs
 * in the process, that information is presented to  the user.
 */
# Styling for error output messages:
$pstyle = '<p style="margin-left:20px;color:red;font-size:18px;">';
$badType = $pstyle . '<strong>Incorrect file type for ';
$norm = '<p style="margin-left:20px;color:brown;font-size:18px;">';

# GPX FILE OPS
$gpxFile = $_FILES['gpxname']['tmp_name'];
$hikeGpx = basename($_FILES['gpxname']['name']);
$gpxType = $_FILES['gpxname']['type'];
$gpxStat = $_FILES['gpxname']['error'];
if ($hikeGpx !== '') {
    if ($gpxStat !== UPLOAD_ERR_OK) {
        $errmsg = $pstyle . uploadErr($gpxStat) . '</p>';
        die ($errmsg);
    }
    if ( preg_match("/octet-stream/",$gpxType) === 0 ) {
        $msgout = $badType . $hikeGpx . ': should be "octet-stream (.gpx)"</p>';
        die($msgout);
    }
    $gpxLoc = '../gpx/' . $hikeGpx;
    if ( file_exists($gpxLoc) ) {
        $hikeGpx = dupFileName($hikeGpx);
        $gpxLoc = '../gpx/' . $hikeGpx;
    } 
    if (!move_uploaded_file($gpxFile,$gpxLoc)) {
        $nomove = $pstyle . "Could not save " . $hikeGpx . 
            ' to site: contact Site Master</p>';
        die($nomove);
    } else {
        echo $norm . $hikeGpx . ' Successfully uploaded to site</p>';
    }
    # gpx file, if present, will be used as a base for creating file names
    # NOTE: name changed if _DUP
    $ext = strrpos($hikeGpx,".");
    $baseName = substr($hikeGpx,0,$ext);
    $haveGpx = true;
} else {
    echo $pstyle . 'As no gpx file is present, no track file will be '
        . 'created, and no latitiude/longitude will be calculated for '
        . 'the trailhead.</p>';
    $haveGpx = false;
}

/* JSON FILE OPS: */
if ($haveGpx) {
    $cwd = getcwd();  # currently in project/build directory...
    $sitePos = strpos($cwd,"build");
    $siteLoc = substr($cwd,0,$sitePos);
    $trkcmd = $siteLoc . 'tools/mktrk.sh -f ' . $siteLoc . 'gpx/' . $hikeGpx . 
        ' -p ' . $siteLoc . 'json';
    $json = exec($trkcmd);
    if ( preg_match("/DONE/",$json) === 1 ) {
        echo $norm . 'Track file created from GPX and saved</p>';
    } else {
        $trkfail =  $pstyle . 'Track file creation from ' . $hikeGpx . ' failed: contact' .
            'Site Master</p>';
        die ($trkfail);
    }
    $trkfile = $baseName . '.json';
}

# ADDITIONAL IMAGES FILES (IF ANY):
$othrImg1 = $_FILES['othr1']['tmp_name'];
$hikeOthrImage1 = basename($_FILES['othr1']['name']);
$othrImg1Type = $_FILES['othr1']['type'];
$img1Stat = $_FILES['othr1']['error'];
if ($hikeOthrImage1 !== '') {
    $imageFile1 = true;
    if ($img1Stat !== UPLOAD_ERR_OK) {
        $errmsg = $pstyle . uploadErr($img1Stat) . '</p>';
        die ($errmsg);
    }
    $img1Loc = '../images/' . $hikeOthrImage1;
    if ( file_exists($img1Loc) ) {
        $hikeOthrImage1 = dupFileName($hikeOthrImage1);
        $img1Loc = '../images/' . $hikeOthrImage1;
    }
    if (!move_uploaded_file($othrImg1,$img1Loc)) {
        $nomove = $pstyle . "Could not save " . $hikeOthrImage1 . 
            ' to site: contact Site Master</p>';
        die($nomove);
    } else {
        echo $norm . $hikeOthrImage1 . ' Successfully uploaded to site</p>';
    }
}
$othrImg2 = $_FILES['othr2']['tmp_name'];
$hikeOthrImage2 = basename($_FILES['othr2']['name']);
$othrImg2Type = $_FILES['othr2']['type'];
$img2Stat = $_FILES['othr2']['error'];
if ($hikeOthrImage2 !== '') {
    $imageFile2 = true;
    if ($img2Stat !== UPLOAD_ERR_OK) {
        $errmsg = $pstyle . uploadErr($img2Stat) . '</p>';
        die ($errmsg);
    }
    $img2Loc = '../images/' . $hikeOthrImage2;
    if ( file_exists($img2Loc) ) {
        $hikeOthrImage2 = dupFileName($hikeOthrImage2);
        $img2Loc = '../images/' . $hikeOthrImage2;
    }
    if (!move_uploaded_file($othrImg2,$img2Loc)) {
        $nomove = $pstyle . "Could not save " . $hikeOthrImage2 . 
            ' to site: contact Site Master</p>';
        die($nomove);
    } else {
        echo $norm . $hikeOthrImage2 . ' Successfully uploaded to site</p>';
    }
}
/* GPS MAPS & DATA SECTION: PROPOSED FILE OPS:
 *  There are potentially two sections for user data entry:
 *  PROPOSED: User enters either maps or gpx files as references
 *  ACTUAL: User enters either maps or gpx files as references
 * At this time, only two files of either type are provided for;
 * These files are expected to be ONLY gpx or html files
 * NOTE: if file exists, user notified but no action taken
 */
$noup = ': This file has been previously uploaded; ' .
    'No further action taken on this file</p>';

# PROPOSED DATA FILE1:
$pdatf1 = $_FILES['propmap']['tmp_name'];
$pfile1 = basename($_FILES['propmap']['name']);
$pf1Type = $_FILES['propmap']['type'];
$pf1Stat = $_FILES['propmap']['error'];
if ( $pfile1 !== '') {
    $propFiles = true;
    if ($pf1Stat !== UPLOAD_ERR_OK) {
        $errmsg = $pstyle . uploadErr($pf1Stat) . '</p>';
        die ($errmsg);
    }
    $pf1loc = filter_input(INPUT_POST,'f1');
    if ($pf1loc === 'maps') {
        $ftype = "/html/";
    } else {
        $ftype = "/octet-stream/";
    }
    if ( preg_match($ftype,$pf1Type) === 0 ) {
        $msgout = $badType . $pfile1 . ': expected '. $ftype . '</p>';
        die($msgout);
    }
    $pf1site = '../' . $pf1loc . '/' . $pfile1;  # either ../gpx or ../html
    # Check against previously uploaded files
    if ( file_exists($pf1site) ) {
    	echo $norm . $pfile1 . $noup;
    } else {
        if (!move_uploaded_file($pdatf1,$pf1site)) {
            $nomove = $pstyle . "Could not save " . $pfile1 . 
                ' to site: contact Site Master</p>';
            die($nomove);
        } else {
            echo $norm . $pfile1 . ' Successfully uploaded to site</p>';
        }
    }
}

# PROPOSED DATA FILE2
$pdatf2 = $_FILES['propgpx']['tmp_name'];
$pfile2 = basename($_FILES['propgpx']['name']);
$pf2Size = filesize($pdatf2);
$pf2Type = $_FILES['propgpx']['type'];
$pf2Stat = $_FILES['propgpx']['error'];
if ( $pfile2 !== '') {
    $propFiles = true;
    if ($pf2Stat !== UPLOAD_ERR_OK) {
        $errmsg = $pstyle . uploadErr($pf2Stat) . '</p>';
        die ($errmsg);
    }
    $pf2loc = filter_input(INPUT_POST,'f2');
    if ($pf2loc === 'maps') {
        $ftype = "/html/";
    } else {
        $ftype = "/octet-stream/";
    }
    if ( preg_match($ftype,$pf2Type) === 0 ) {
        $msgout = $badType . $pfile2 . ': expected '. $ftype . '</p>';
        die($msgout);
    }
    $pf2site = '../' . $pf2loc . '/' . $pfile2;  # either ../gpx or ../html
    # Check against previously uploaded files
    if ( file_exists($pf2site) ) {
    	echo $norm . $pfile2 . $noup;
    } else {
        if (!move_uploaded_file($pdatf2,$pf2site)) {
            $nomove = $pstyle . "Could not save " . $pfile2 . 
                ' to site: contact Site Master</p>';
            die($nomove);
        } else {
            echo $norm . $pfile2 . ' Successfully uploaded to site</p>';
        }
    }
}

# ACTUAL DATA FILE1:
$adatf1 = $_FILES['actmap']['tmp_name'];
$afile1 = basename($_FILES['actmap']['name']);
$af1Type = $_FILES['actmap']['type'];
$af1Stat = $_FILES['actmap']['error'];
if ( $afile1 !== '') {
    $actFiles = true;
    if ($af1Stat !== UPLOAD_ERR_OK) {
        $errmsg = $pstyle . uploadErr($af1Stat) . '</p>';
        die ($errmsg);
    }
    $af1loc = filter_input(INPUT_POST,'f3');
    if ($af1loc === 'maps') {
        $ftype = "/html/";
    } else {
        $ftype = "/octet-stream/";
    }
    if ( preg_match($ftype,$af1Type) === 0 ) {
        $msgout = $badType . $afile1 . ': expected '. $ftype . '</p>';
        die($msgout);
    }
    $af1site = '../' . $af1loc . '/' . $afile1;  # either ../gpx or ../html
    # Check against previously uploaded files
    if ( file_exists($af1site) ) {
    	echo $norm . $afile1 . $noup;
    } else {
        if (!move_uploaded_file($adatf1,$af1site)) {
            $nomove = $pstyle . "Could not save " . $afile1 . 
                ' to site: contact Site Master</p>';
            die($nomove);
        } else {
            echo $norm . $afile1 . ' Successfully uploaded to site</p>';
        }
    }
}

# ACTUAL DATA FILE2
$adatf2 = $_FILES['actgpx']['tmp_name'];
$afile2 = basename($_FILES['actgpx']['name']);
$af2Type = $_FILES['actgpx']['type'];
$af2Stat = $_FILES['actgpx']['error'];
if ( $afile2 !== '') {
    $actFiles = true;
    if ($af2Stat !== UPLOAD_ERR_OK) {
        $errmsg = $pstyle . uploadErr($af2Stat) . '</p>';
        die ($errmsg);
    }
    $af2loc = filter_input(INPUT_POST,'f4');
    if ($af2loc === 'maps') {
        $ftype = "/html/";
    } else {
        $ftype = "/octet-stream/";
    }
    if ( preg_match($ftype,$af2Type) === 0 ) {
        $msgout = $badType . $afile2 . ': expected '. $ftype . '</p>';
        die($msgout);
    }
    $af2site = '../' . $af2loc . '/' . $afile2;  # either ../gpx or ../html
    # Check against previously uploaded files
    if ( file_exists($af2site) ) {
    	echo $norm . $afile2 . $noup;
    } else {
        if (!move_uploaded_file($adatf2,$af2site)) {
            $nomove = $pstyle . "Could not save " . $afile2 . 
                ' to site: contact Site Master</p>';
            die($nomove);
        } else {
            echo $norm . $afile2 . ' Successfully uploaded to site</p>';
        }
    }
}
?>