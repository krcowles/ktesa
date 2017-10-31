<?php
/* To already be defined on entry:
 * $tbl_type; $uid; $hikeNo
 */
# gather up the captions:
$ecapts = $_POST['ecap'];
# if the edited file was in HIKES, transfer data over to EHIKES:
if ($tbl_type === 'old') {
        # all TSV data needs to be copied to ETSV
        # EHIKES was just updated w/pub data; new hike no established: retrieve
        $indxReq = "SELECT indxNo FROM EHIKES ORDER BY indxNo DESC LIMIT 1;";
        $indxq = mysqli_query($link,$indxReq);
        if (!$indxq) {
            die("saveChanges.php: Did not retrieve new EHIKES indx no: " .
                mysqli_error($link));
        }
        $indxNo = mysqli_fetch_row($indxq);
        $newNo = $indxNo[0];
        mysqli_free_result($indxq);
        $getReq = "SELECT * FROM TSV WHERE indxNo = {$hikeNo};";
        $getq = mysqil_query($link,$getReq);
        if (!$getq) {
            die("saveChanges.php: Failed to pull TSV data for move to ETSV: " .
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
            $addPicReq = "INSERT INTO ETSV (indxNo,folder,usrid,title," .
                "hpg,mpg,`desc`,lat,lng,thumb,alblnk,date,mid,imgHt,imgWd," .
                "iclr,org) VALUES ('{$newNo}','{$fldr}','{$uid}','{$ttle}'," .
                "'{$phpg}','{$pmpg}','{$pdes}','{$plat}','{$plon}','{$thmb}'," .
                "'{$palb}','{$date}','{$pmid}','{$pxht}','{$pxwd}'," .
                "'{$pclr}','{$porg}');";
            $picq = mysqli_query($link,$addPicReq);
            if (!$picq) {
                die("saveChanges.php: Failed to insert data into ETSV: " .
                    mysqli_error($link));
            }
        }
        mysqli_free_result($getq);
        mysqli_free_result($picq); 
        $useNo = $newNo; # use newno in upcoming requests
    } else { 
        $useNo = $hikeNo; # use given no in upcoming requests
    }
    /* Now, pull 'hpg' data out of ETSV, corresponding to hike no;
     * 'delete' (mark as no-show) any checked photos in the database:
     * The order of the pics in the table corresponds to the id no's of deletes
     */
    $photoReq = "SELECT picIdx,hpg FROM ETSV WHERE indxNo = '{$useNo}' AND " .
            "hpg = 'Y';";
    $photoq = mysqli_query($link,$photoReq);
    if (!$photoq) {
        die("saveChange.php: Failed to extract 'hpg' from ETSV: " .
            mysqli_error($link));
    }
    $dels = $_POST['delpic']; # only passes the CHECKED boxes (these have id#'s)
    $dcnt = 0;
    $delindx = 0;
    while ($picpg = mysqli_fetch_assoc($photoq)) {
        if ($dels[$delindx] == $dcnt) {
            # mark this one as 'deleted' (hpg = "N")
            echo " ---row " . $picpg['picIdx'] . "; setting; " . $picpg['hpg'];
            $delindx++;
            if ($delindx >= count($dels)) {
                break;
            }
        } else {
            echo " :skip " . $picpg['picIdx'];
        }
        $dcnt++;
    }
    mysqli_free_result($photoq);
