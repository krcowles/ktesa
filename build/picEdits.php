<?php
/* Each row retrieved is the html for that row's div, consisting of all the
 * images contained in that row. When retrieved, it is in string form, so the 
 * html string must be parsed to extract the data required update the xml
 * database. The POSTed images may be either: 
 *      <img id="pic...,        or:   <img ....  [not pic id]
 * Remember that the edit page scaled the pix down a bit for easier 
 * manipulation (and to allow room for insertion points), so they
 * need to be re-sized for the default page (950 = 960-margin)
 */
$allrows = $_POST['row'];  # html for rows after editing
$noOfRows = count($allrows);

# clean out old data and save relevant counts of tags
# tried and could not use  $xml->children() to set values = ''
$prevRows = 0;
$imgs = [];
foreach ($hRows->picRow as $xmlrow) {
    $prevRows++;
    $xmlrow->rowHt = '';
    $imgInRow = 0;
    foreach ($xmlrow->pic as $xmlpic) {
        $imgInRow++;
        $xmlpic->picWdth = '';
        $xmlpic->picSrc = '';
        $xmlpic->picCap = '';
    }
    array_push($imgs,$imgInRow);
}

# DEBUG:
echo "CLEARED: " . $hRows->asXML();
#$tmp = fopen('pic.tst',"w");

# translate to xml:
$picxml = '';
$width = [];
$src = [];
$cap = [];
for ($w=0; $w<$noOfRows; $w++) {
    # for each post-edited row:
    # DEBUG:
    #fwrite($tmp,$allrows[$w]);
    
    $icnt = substr_count($allrows[$w],"img");
    $picStr = $allrows[$w];
    $htpos = strpos($picStr,"height") + 8;
    $htend = strpos($picStr,'"',$htpos);
    $htlgth = $htend - $htpos;
    $height = substr($picStr,$htpos,$htlgth);
    # for each row, process images:
    for ($v=0; $v<$icnt; $v++) {
        $spos = strpos($picStr,"src") + 5;
        $sepos = strpos($picStr,'"',$spos);
        $slgth = $sepos - $spos;
        $src[$v] = substr($picStr,$spos,$slgth);
        $cpos = strpos($picStr,"alt") + 5;
        $cepos = strpos($picStr,'"',$cpos);
        $clgth = $cepos - $cpos;
        $cap[$v] = substr($picStr,$cpos,$clgth);
        $wpos = strpos($picStr,"width") + 7;
        $wepos = strpos($picStr,'"',$wpos);
        $wlgth = $wepos - $wpos;
        $width[$v] = substr($picStr,$wpos,$wlgth);
        $newpicpos = strpos($picStr,"img",10);
        $picStr = substr($picStr,$newpicpos);
    }
    # enter as xml:
    if ($w < $prevRows) {
        $postRow = $hRows->picRow[$w];
        $postRow->rowHt = $height;
        for ($j=0; $j<$icnt; $j++) {  # for each of the new (post-edited) images
            if ($j < $imgs[$w]) {  # if cleared out tag exists, use it
                $postRow->pic[$j]->picWdth = $width[$j];
                $postRow->pic[$j]->picSrc = $src[$j];
                $postRow->pic[$j]->picCap = $cap[$j];    
            } else {  # otherwise, add children pics
                
                
            }      
        }
    }
        
   /*
    }
    else {  # extra rows needed
        #$picxml .= "\t\t\t<rowHt>" . $height . "</rowHt>\n";
        #$picxml .= "\t\t\t<pic>\n\t\t\t\t<picSrc>" . $src . "</picSrc>\n";
        #$picxml .= "\t\t\t\t<picCap>" . $cap . "</picCap>\n";
        #$picxml .= "\t\t\t\t<picWdth>" . $width . "</picWdth>\n\t\t\t</pic>\n";
    }
         *
         */
}  # end of for each row
echo "<br />----- END RESULT: " . $hRows->asXML();
#fclose($tmp);
die ("PIX");

?>