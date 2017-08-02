<?php
/* Each row retrieved is the html for that row's div, consisting of all the
 * images contained in that row. When retrieved, it is in string form, so the 
 * html string must be parsed to extract the data required update the xml
 * database. The POSTed images may be either: 
 *      <img id="pic...,        or:   <img ....  [not pic id]
 * Remember that the edit page scaled the pix down a bit for easier 
 * manipulation (and to allow room for insertion points), so they
 * need to be re-sized for the default page width: 950  (= 960-margin)
 */
$allrows = $_POST['row'];  # html for rows after editing
$noOfRows = count($allrows);

$hikeLine->content = '';
$hRows = $hikeLine->content;

# extract data needed for xml:
$picxml = '';
$width = [];
$src = [];
$cap = [];
for ($w=0; $w<$noOfRows; $w++) {
    # for each post-edited row:
    $icnt = substr_count($allrows[$w],"img");
    $picStr = $allrows[$w];
    $htpos = strpos($picStr,"height") + 8;
    $htend = strpos($picStr,'"',$htpos);
    $htlgth = $htend - $htpos;
    $height = substr($picStr,$htpos,$htlgth);
    $rowWidth = 0;
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
        $rowWidth += $width[$v];
        $newpicpos = strpos($picStr,"img",10);
        $picStr = substr($picStr,$newpicpos);
    }
    /* To resize to full width:
     *  - remember that the edit row width was total image width 
     *      PLUS 2*alpha + 10*($icnt-1)  [ie 60 + 10(i-1)]
     *  - AND it was scaled down to 900px; therefore, add above space
     *      back into rowWdith and scale up to 950px:
     *  - Don't scale if too few images, ie. rowWidth < 850 (?)
     * 
     */
    $rowWidth += 60 + 10 * ($icnt -1); # 1px space between images, and 10px each end
    $scale = 950/$rowWidth;
    if ($rowWidth > 850) {
        $height = floor($scale * $height);
        for ($n=0; $n<$icnt; $n++) {
            $width[$n] = floor($scale * $width[$n]);
        }
    }
    
    # save as xml:
    $newRow = $hRows->addChild('picRow');
    $newRow->addChild('rowHt',$height);
    for ($p=0; $p<$icnt; $p++) {
        $newPic = $newRow->addChild('pic');
        $newPic->addChild('picWdth',$width[$p]);
        $newPic->addChild('picSrc',$src[$p]);
        $newPic->addChild('picCap',$cap[$p]);
    }
}  # end of for each row
#echo "RESULT: -- " . $hRows->asXML();
?>