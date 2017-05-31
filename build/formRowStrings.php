<?php
/* Each row retrieved is the html for that row's div, consisting of all the
 * images contained in that row. When retrieved, it is in string form, so the 
 * html string must be parsed to extract the data required to form the string
 * arrays, which are ulitmately saved in the database. The POSTed images may 
 * be either: <img id="pic..., or <img (not pic id). The algorithm below forms 
 * the row string by concatenating image data with ^ separators. Remember that 
 * the edit page scaled the pix down a bit for easier manipulation, so they
 * need to be re-sized for the default page (950 = 960-margin)
 */

/* This function accepts a portion of the html text (for one image only)
 * and forms the corresponding array string segment.
 */
function stringForm($targetTxt) {
    $sym = '';
    $imgid = '';
    if (preg_match("/id/",$targetTxt) > 0) {
        $idpos = strpos($targetTxt,"id") + 4;
        $imgid = substr($targetTxt,$idpos,3); 
    } 
    # get the image width;
    $wdpos = strpos($targetTxt,"width") + 7;
    # assumption: width will be either 3 or 2 digits
    $imgwd = substr($targetTxt,$wdpos,3);
    if ( !is_numeric($imgwd) ) {
        $imgwd = substr($targetTxt,$wdpos,2);
        if ( !is_numeric($imgwd) ) {
            echo "FAILED TO EXTRACT PIC WIDTH!";
            $imgwd = 20;
        }
    }
    $srcpos = strpos($targetTxt,"src") + 5;
    $srcend = strpos($targetTxt,"alt=") - 2;
    $srclgth = $srcend - $srcpos;
    $src = substr($targetTxt,$srcpos,$srclgth); 

    if ($imgid === 'pic') {
        $retstr = 'p^' . $imgwd . '^' . $src;
        $sym = 'p';
    } else {
        $retstr = 'n^' . $imgwd . '^' . $src;
        $sym = 'n';
    }
    return array($sym,$retstr,$imgwd);
}
/* -------      MAIN      ------- */
$rows = ['','','','','',''];
$capts = [];
$prevRowWidth = 0;
for ($i=0; $i<6;$i++) {
    $tag = 'row' . $i;
    $rowhtml = filter_input(INPUT_POST,$tag);
    if ($rowhtml !== '') {
        /* As the processing of images proceeds, collect the accumulated row
         * width so that the rescaling to page width can be executed following
         * this routine.
         */
        $rowWidth = 0;
        $imgCnt = 0;
        # prime the loop
        $type = substr($rowhtml,1,3); # should always be img (formerly had map divs)
        $nextsympos = strpos($rowhtml,">");
        $stringSeg = substr($rowhtml,1,$nextsympos);
        $unproclgth = strlen($rowhtml) - $nextsympos;
        $unprocStr = substr($rowhtml,$nextsympos+2,$unproclgth); // strip off beginning "<"
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
            $stringEls = stringForm($stringSeg);
            $rowWidth += $stringEls[2];
            $imgstr .= '^' . $stringEls[1];
            $idtype = substr($imgstr,1,1);
            if ($stringEls[0] === 'p') {
                $altpos = strpos($stringSeg,"alt") + 5;
                $altstrlgth = strlen($stringSeg) - $altpos;
                $altstr = substr($stringSeg,$altpos,$altstrlgth);
                $altend = strpos($altstr,'"');
                $alt = substr($altstr,0,$altend);
                array_push($capts,$alt);
                $imgstr .= "^" . $alt;
            }
            # trim off the lead and use the targetTxt
            $type = substr($unprocStr,0,3);
            $nextstrend = strpos($unprocStr,">");
            $stringSeg = substr($unprocStr,0,$nextstrend);
            $nextstrlgth = strlen($unprocStr) - $nextstrend;
            $unprocStr = substr($unprocStr,$nextstrend+2,$nextstrlgth);
        }
        # now form the complete string:
        $rowstr = $imgCnt . "^" . $rowht . $imgstr;
        $rows[$i] = $rowstr;
        /* 
         * if the previous row is bigger than the current row by > 10px, scale = 1.0;
         * this should happen only when the last row on the page w/images has 
         * too few images to fill the whole row.
         * 
         */
        if ($prevRowWidth > 0) {
          if ($prevRowWidth > ($rowWidth + 10)) {
              $scale[$i] = 1.000;
          } else {
              $scale[$i] = 950/$rowWidth;
          }
        } 
        $prevRowWidth = $rowWidth;
    } else {
        break;
    }
}
?>