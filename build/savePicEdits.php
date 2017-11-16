<?php
/* To already be defined on entry:
 * $tbl_type; $hikeNo
 */
# gather up the captions:
$ecapts = $_POST['ecap'];
# if the edited file was in HIKES, transfer data over to EHIKES:
if ($tbl_type === 'old') {
    # all TSV data needs to be copied to ETSV before updating
    mysqli_free_result($indxq);
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
/* Now, update all displayed photos, marking any which were deleted by
 * setting its corresponding hpg to "N", and updating all captions.
 * (The order of the pics in the table corresponds to the id no's of deletes
 * for all hpg="Y" settings
 */
$photoReq = "SELECT picIdx,hpg,`desc` FROM ETSV WHERE indxNo = '{$useNo}' AND " .
    "hpg = 'Y';";
$photoq = mysqli_query($link,$photoReq);
if (!$photoq) {
    die("savePicEdits.php: Failed to extract 'hpg' from ETSV: " .
        mysqli_error($link));
}
$dels = $_POST['delpic']; # only passes the CHECKED boxes (these have id#'s)
$dcnt = 0;
$delindx = 0;
$piccnt = 0;
while ($picrow = mysqli_fetch_assoc($photoq)) {
    $thisid = $picrow['picIdx'];
    $newcap = mysqli_real_escape_string($link,$ecapts[$piccnt]);
    $capReq = "UPDATE ETSV SET `desc` = '{$newcap}' WHERE picIdx = {$thisid};";
    $capq = mysqli_query($link,$capReq);
    if (!$capq) {
        die("savePicEdits.php: Failed to update caption for id {$thisid}: " .
            mysqli_error($link));
    }
    
    if ($dels[$delindx] == $dcnt) {
        $noDispReq = "UPDATE ETSV SET hpg = 'N' WHERE picIdx = {$thisid};";
        $noDisp = mysqli_query($link,$noDispReq);
        if (!$noDisp) {
            die("savePicEdits.php: Failed to 'delete' hpg for id {$thisid}: " .
                mysqli_error($link));
        }
        $delindx++;
    } 
    $dcnt++;
    $piccnt++;
}
mysqli_free_result($capq);
mysqli_free_result($noDisp);
mysqli_free_result($photoq);
