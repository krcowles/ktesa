<?php
/* Each row retrieved is the html for that row's div element, consisting of all the
 * images contained in that row. When retrieved, it is in string form, so the html string
 * must be parsed to extract all the images. The images may be either <img id="pic...,
 * <iframe>, or <img (not pic id). The algorithm below then forms the row string for the
 * row in the form of a concatenated string with "^" separators.
 */
function stringForm($sym,$remainder) {
    $flag = '';
    if ($sym !== 'div') {  # maps are surrounded by a div w/draggable border
        $imgid = '';
        if (preg_match("/id/",$remainder) > 0) {
            $idpos = strpos($remainder,"id") + 4;
            $imgid = substr($remainder,$idpos,3); 
        } 
        # get the image width;
        $wdpos = strpos($remainder,"width") + 7;
        $imgwd = substr($remainder,$wdpos,3);
        if ( !is_numeric($imgwd) ) {
            $imgwd = substr($remainder,$wdpos,2);
            if ( !is_numeric($imgwd) ) {
                echo "FAILED TO EXTRACT PIC WIDTH!";
                $imgwd = 20;
            }
        }
        $srcpos = strpos($remainder,"src") + 5;
        $srcend = strpos($remainder,"alt=") - 2;
        $srclgth = $srcend - $srcpos;
        $src = substr($remainder,$srcpos,$srclgth); 
        if ($imgid === 'pic') {
            $retstr = 'p^' . $imgwd . '^' . $src;
            $flag = 'p';
        } else {
            $retstr = 'n^' . $imgwd . '^' . $src;
            $flag = 'n';
        }
    } else {  #mapdiv
        # note: both div & map have widths:
        $divpos = strpos($remainder,"width") + 7;
        $wdpos = strpos($remainder,"width",$divpos) + 7;
        $imgwd = substr($remainder,$wdpos,3);
        if ( !is_numeric($imgwd) ) {
            $imgwd = substr($remainder,$wdpos,2);
            if ( !is_numeric($imgwd) ) {
                echo "FAILED TO EXTRACT PIC WIDTH!";
                $imgwd = 20;
            }
        }
        $srcpos = strpos($remainder,"src") + 5;
        $refend = strpos($remainder,"map_name");
        $srcend = strpos($remainder,'"',$refend);
        $srclgth = $srcend - $srcpos;
        $src = substr($remainder,$srcpos,$srclgth); 
        $retstr = 'f^' . $imgwd . "^" . $src;
        $flag = 'f';
        #echo "MAP DATA:" . $retstr;
    }
    return array($flag,$retstr);
}
/* -------      MAIN      ------- */
$rows = ['','','','','',''];
for ($i=0; $i<6;$i++) {
    $tag = 'row' . $i;
    #echo "***ROW " . $i;
    $rowhtml = $_POST[$tag];
    if ($rowhtml !== '') {
        $imgCnt = 0;
        # first img will always start immediately...
        $type = substr($rowhtml,1,3);
        if ($type !== 'div') {
            $nextsympos = strpos($rowhtml,">");
        } else {
            $nextsympos = strpos($rowhtml,"/div>") + 4;
        }
        $stringSeg = substr($rowhtml,1,$nextsympos);
        $unproclgth = strlen($rowhtml) - $nextsympos;
        $unprocStr = substr($rowhtml,$nextsympos+2,$unproclgth); // strip off beginning "<"
        #echo "-------remainder at beginning of loop----------" . $unprocStr;
        # get the row ht (same for all images)
        $htpos = strpos($stringSeg,"height") + 8;
        $rowht = substr($stringSeg,$htpos,3);
        if ( !is_numeric($rowht) ) {
            $rowht = substr($stringSeg,$htpos,2);
            if ( !is_numeric($rowht)) {
                echo "FAILED TO EXTRACT ROW HEIGHT!";
                $rowht = 20;
            }
        }
        $imgstr = '';
        while ($stringSeg !== '') {
            $imgCnt++;
            $stringEls = stringForm($type,$stringSeg);
            $imgstr .= '^' . $stringEls[1];
            $idtype = substr($imgstr,1,1);
            if ($stringEls[0] === 'p') {
                $altpos = strpos($stringSeg,"alt") + 5;
                $altstrlgth = strlen($stringSeg) - $altpos;
                $altstr = substr($stringSeg,$altpos,$altstrlgth);
                $altend = strpos($altstr,'"');
                $alt = substr($altstr,0,$altend);
                $imgstr .= "^" . $alt;
            }
            #echo "**RETURNED STRING**: " . $imgstr;
            # trim off the lead and use the remainder
            $type = substr($unprocStr,0,3);
            if ($type !== 'div') {
                $nextstrend = strpos($unprocStr,">");
            } else {
                $nextstrend = strpos($unprocStr,"/div>") + 4;
            }
            $stringSeg = substr($unprocStr,0,$nextstrend);
            $nextstrlgth = strlen($unprocStr) - $nextstrend;
            $unprocStr = substr($unprocStr,$nextstrend+2,$nextstrlgth);
            #echo "NEXT type: " .$type . "; -- next: " . $stringSeg . "--remainder: " . $unprocStr;
        }
        # now form the complete string:
        $rowstr = $imgCnt . "^" . $rowht . $imgstr;
        $rows[$i] = $rowstr;
    }
}
?>