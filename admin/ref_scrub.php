<?php
require_once "../mysql/setenv.php";
$lastid = "SELECT refId FROM REFS ORDER BY refId DESC LIMIT 1";
$getid = mysqli_query($link,$lastid);
if (!$getid) {
    if (Ktesa_Dbug) {
        dbug_print('delete_tbl_row.php: Could not retrieve highest indxNo: ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,6,0);
    }
}
$lastindx = mysqli_fetch_row($getid);
$tblcnt = $lastindx[0];
for ($i=0; $i<$tblcnt; $i++) {
    $rno = $i + 1;
    $rowreq = "SELECT rtype,rit2 FROM REFS WHERE refId = '{$rno}';";
    $getrow = mysqli_query($link,$rowreq);
    if (!$getrow) {
        die ("load_REFS.php: Could not retrieve line item " . $rno .
            " from REFS: " . mysqli_error());
    }
    $ritems = mysqli_fetch_assoc($getrow);
    if ($ritems['rtype'] === 'Book:') {
        $auth = trim($ritems['rit2']);
        $nm1 = strpos($auth,"the Northern"); 
        $nm2 = strpos($auth,"The Northern"); # various mistypes in this one...
        if ($nm1 !== false || $nm2 !== false) {
            $auth = "The Northern New Mexico Group of the Sierra Club";
        }
        $a = true; # data scrub -> the Mike Coltrin
        $b = true; # data scrub -> the Craig Martin
        $c = true;
        $d = true;
        if (strpos($auth,", by the Mike") === false) {
            $a = false;
            if (strpos($auth,", by the Craig") === false) {
                $b = false;
                if (strpos($auth,", by ") === false ) {
                    $c = false;
                    if(strpos($auth,"by ") === false) {
                        $d = false;
                    }
                }
            }
        }
        if ($a || $b || $c || $d) {
            if ($a) {
                $authstrt = 9;
            } elseif ($b) {
                $authstrt = 9;
            } elseif ($c) {
                $authstrt = 5;
            } else {
                $authstrt = 3;
            }
            $authlgth = strlen($auth) - $authstrt;
            $auth = trim(substr($auth,$authstrt,$authlgth));
        }
        $mauth = mysqli_real_escape_string($link,$auth);
        $update = "UPDATE REFS SET rit2 = '{$mauth}' WHERE refId = '{$rno}';";
        $author = mysqli_query($link,$update);
        if (!$author) {
            die("load_REFS.php: failed to update author in row {$rno}: " .
                    mysqli_error());
        }
    }
}
?>
