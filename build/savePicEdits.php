<?php
# The following 2 variables must be defined on entry: $tbl_type; $hikeNo
# gather up the captions:
$ecapts = $_POST['ecap'];
$noOfPix = count($ecapts);
# capture the states of each for displaying on page or and/or on map
$displayPg = $_POST['pix'];
$displayMap = $_POST['mapit'];
# if the edited file was in HIKES, transfer data over to EHIKES:
if ($tbl_type === 'old') {
    # all TSV data needs to be copied to ETSV before updating
    $getReq = "SELECT * FROM TSV WHERE indxNo = {$hikeNo};";
    $getq = mysqli_query($link,$getReq);
    if (!$getq) {
        die("savePicEdits.php: Failed to pull TSV data for move to ETSV: " .
            mysqli_error($link));
    }
    while ($p = mysqli_fetch_assoc($getq)) {
        $fldr = mysqli_real_escape_string($link,$p['folder']);
        $ttle = mysqli_real_escape_string($link,$p['title']);
        $phpg = mysqli_real_escape_string($link,$p['hpg']);
        $pmpg = mysqli_real_escape_string($link,$p['mpg']);
        $pdes = mysqli_real_escape_string($link,$p['desc']);
        $plat = mysqli_real_escape_string($link,$p['lat']);
        $plon = mysqli_real_escape_string($link,$p['lng']);
        $thmb = mysqli_real_escape_string($link,$p['thumb']);
        $palb = mysqli_real_escape_string($link,$p['alblnk']);
        $date = mysqli_real_escape_string($link,$p['date']);
        $pmid = mysqli_real_escape_string($link,$p['mid']);
        $pxht = mysqli_real_escape_string($link,$p['imgHt']);
        $pxwd = mysqli_real_escape_string($link,$p['imgWd']);
        $pclr = mysqli_real_escape_string($link,$p['iclr']);
        $porg = mysqli_real_escape_string($link,$p['org']);
        $addPicReq = "INSERT INTO ETSV (indxNo,folder,title," .
            "hpg,mpg,`desc`,lat,lng,thumb,alblnk,date,mid,imgHt,imgWd," .
            "iclr,org) VALUES ('{$newNo}','{$fldr}','{$ttle}'," .
            "'{$phpg}','{$pmpg}','{$pdes}','{$plat}','{$plon}','{$thmb}'," .
            "'{$palb}','{$date}','{$pmid}','{$pxht}','{$pxwd}'," .
            "'{$pclr}','{$porg}');";
        $picq = mysqli_query($link,$addPicReq);
        if (!$picq) {
            die("savePicEdits.php: Failed to insert data into ETSV: " .
                mysqli_error($link));
        }
    }
    mysqli_free_result($getq);
    mysqli_free_result($picq); 
    $useNo = $newNo; # use newno in upcoming requests
} else { 
    $useNo = $hikeNo; # use given no in upcoming requests
}
/* Now, update all displayed photos, marking any which were 'deleted' by
 * setting its corresponding hpg to "N", and updating all captions.
 * (The order of the pics in the table corresponds to the id no's of deletes
 * for all hpg="Y" settings. 
 */
$photoReq = "SELECT picIdx,title,hpg,mpg,`desc` FROM ETSV WHERE indxNo = '{$useNo}'";
$photoq = mysqli_query($link,$photoReq);
if (!$photoq) {
    die("savePicEdits.php: Failed to extract 'hpg' from ETSV: " .
        mysqli_error($link));
}
if (count($ecapts) !== mysqli_num_rows($photoq)) {
    echo '<p style="color:red;font-size:20px;margin-left:16px;">'
    . "WARNING: Retrieved photo count and no of captions don't match..</p>";
}
$p = 0;
while ($photo = mysqli_fetch_assoc($photoq)) {
    $thisid = $photo['picIdx'];
    $thispic = $photo['title'];
    $newcap = mysqli_real_escape_string($link,$ecapts[$p]);
    # determine if $thispic has a corresponding checkbox value:
    # NOTE: If not checked, array will not contain $thispic
    $disph = 'N';
    for ($i=0; $i<$noOfPix; $i++) {
        if ($thispic == $displayPg[$i]) {
            $disph = 'Y';
            break;
        }
    }
    $dispm = 'N';
    for ($j=0; $j<$noOfPix; $j++) {
        if ($thispic == $displayMap[$j]) {
            $dispm = 'Y';
            break;
        }
    } 
    $updtreq = "UPDATE ETSV SET hpg = '{$disph}',mpg = '{$dispm}',"
        ."`desc` = '{$newcap}' WHERE picIdx = {$thisid};";
    $update = mysqli_query($link,$updtreq);
    if (!$update) {
        die("savePicEdits.php: Failed to update ETSV table for hike {$hikeNo}: "
            . msyqli_error($link));
    }
    $p++;
}
mysqli_free_result($update);
mysqli_free_result($photoq);