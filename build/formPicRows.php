<?php
 /*
  * This routine will use the already-created $photo xml object to access
  * picture data. 
  */ 
# predefine array for months...
$month = array("Jan","Feb","Mar","Apr","May","Jun",
                                "Jul","Aug","Sep","Oct","Nov","Dec");
# for each of the <user-selected> pix, define needed arrays
$i = 0;
foreach ($photos->picDat as $upic) {
    if ($upic->hpg == 'Y') {
        $picYear = substr($upic->date,0,4);
        $picMoDigits = substr($upic->date,5,2) - 1;
        $picMonth = $month[$picMoDigits];
        $picDay = substr($upic->date,8,2);
        if (substr($picDay,0,1) === '0') {
            $picDay = substr($picDay,1,1);
        }
        $caption[$i] = "{$picMonth} {$picDay}, {$picYear}: {$upic->desc}";
        $picWidth[$i] = $upic->imgWd;
        $picHeight[$i] = $upic->imgHt;
        $name[$i] = $upic->title;
        $desc[$i] = $upic->desc;
        $album[$i] = $upic->alblnk;
        $photolink[$i] = $upic->mid;
        $i++;
    }
}
# the list of links to photos will be passed as a string to 'saveHike.php'
$albStr = $noOfPix . '^' . implode("^",$album);

$imgRows = [];          # no limit on number of rows for now...
$maxRowHt = 260;	# change as desired
$rowWidth = 950;	# change as desired, current page width is 960
# start by calculating the various images' widths when rowht = maxRowHt
$widthAtMax = [];
# NOTE: all photos first, then other non-captioned images (currently 2 max)
# PHOTOS:
for ($j=0; $j<$noOfPix; $j++) {
    $widthAtMax[$j] = floor($picWidth[$j] * ($maxRowHt/$picHeight[$j]));
}
# OTHER IMAGES: 
for ($l=0; $l<$noOfOthr; $l++) {
    $indx = $noOfPix + $l;
    $widthAtMax[$indx] = floor($othrWidth[$l] * ($maxRowHt/$othrHeight[$l]));
}
$items = $noOfPix + $noOfOthr;
# initialize starting rowWidth, counters, and starting point for html creation
$curWidth = 0;	# row Width as it's being built
$startIndx = 0;	# when creating html, index to set loop start
$rowHtml = '';
$rowxml = '';
$rowNo = 0;
$totalProcessed = 0;
$othrIndx = 0;	 # counter for number of other images being loaded
$leftMostImg = true;
$rowStr = array();
    for ($i=0; $i<$items; $i++) {
        if ($leftMostImg === false) {  # modify width for added pic margins for all but first img
                $curWidth += 1;
        }
        $rowCompleted = false;
        $curWidth += $widthAtMax[$i];
        $leftMostImg = false;
        if ($i < $noOfPix) {
            $itype[$i] = "picture";
        } else {
            $itype[$i] = "image";
        }
        # add images to curWidth until it exceeds rowWidth, then force fit:
        if ($curWidth > $rowWidth) {
            $rowItems = $i - $startIndx + 1;
            $totalProcessed += $rowItems;
            $scaleFactor = $rowWidth/$curWidth;
            $actualHt = floor($scaleFactor * $maxRowHt);
            # ALL rows concatenated in $rowHtml
            $rowHtml = $rowHtml . '<div id="row' . $rowNo . '" class="ImgRow">' . "\n";
            $rowxml .= "\t<picRow>\n\t\t<rowHt>" . $actualHt . "</rowHt>\n";
            $thisRow = '';
            $imgCnt = 0;
            $imel = '';
            for ($n=$startIndx; $n<=$i; $n++) {
                if ($n === $startIndx) {
                    $styling = ''; # don't add left-margin to leftmost image
                } else {
                    $styling = 'margin-left:1px;';
                }
                if ($itype[$n] === "picture") {
                    $picWidth[$n] = floor($scaleFactor * $widthAtMax[$n]);
                    $picHeight[$n] = $actualHt;
                    $thisRow = $thisRow . '<img id="pic' .$n . '" style="' . $styling . '" width="' .
                        $picWidth[$n] . '" height="' . $actualHt . '" src="' . $photolink[$n] . 
                        '" alt="' . $caption[$n] . '" />';	
                    $imel .= 'p^' . $picWidth[$n] . '^' . $photolink[$n] . '^' . $caption[$n];
                    $rowxml .= "\t\t<pic>\n\t\t\t<picWdth>" . $picWidth[$n] .
                            "</picWdth>\n\t\t\t<picSrc>" . $photolink[$n] .
                            "</picSrc>\n\t\t\t<picCap>" . $caption[$n] .
                            "</picCap>\n\t\t</pic>\n";
                } else {  # its an additional non-captioned image
                    $othrWidth[$othrIndx] = floor($scaleFactor * $widthAtMax[$n]);
                    $othrHeight[$othrIndx] = $actualHt;
                    $thisRow = $thisRow . '<img style="' . $styling . '" width="' . $othrWidth[$n] .
                            '" height="' . $actualHt . '" src="../images/' . $addonImg[$othrIndx] .
                            '" alt="Additional non-captioned image" />';
                    $imel .= 'n^' . $othrWidth[$n] . '^' . $addonImg[$othrIndx];
                    $rowxml .= "\t\t<pic>\n\t\t\t<picWdth>" . $othrWidth[$n] .
                            "</picWdth>\n\t\t\t<picSrc>../images/" . 
                            $addonImg[$othrIndx] . "</picSrc>\n\t\t\</pic>\n";
                    $othrIndx += 1;
                }
                $imgCnt++;
                $imel .= '^';
            }  # end of for loop $n
            # thisRow is completed and will be used below in different ways:
            $imel = $imgCnt . '^' . $actualHt . '^' . $imel;
            array_push($rowStr,$imel);
            $rowHtml = $rowHtml . $thisRow . "\n</div>\n";
            $rowxml .= "\t</picRow>\n";   
            $rowNo += 1;
            $startIndx += $rowItems;
            $curWidth = 0;
            $rowCompleted = true;
            $leftMostImg = true;
        }  # end of if currentWidth > rowWidth
    } # end of for loop creating initial rows
    # last row may not be filled, and will be at maxRowHt
    # last item index was "startIndx"; coming into last row as $leftMostImg = true
    if ($rowCompleted === false) {
        $itemsLeft = $items - $totalProcessed;
        $leftMostImg = true;
        $thisRow = '<div id="row' . $rowNo . '" class="ImgRow">' . "\n";
        $rowxml .= "\t<picRow>\n\t\t<rowHt>" . $maxRowHt . "</rowHt>\n";
        $imel = '';
        $imgCnt = 0;
            for ($i=0; $i<$itemsLeft; $i++) {
                if ($leftMostImg) {
                    $styling = ''; 
                    $leftMostImg = false;
                } else {
                    $styling = 'margin-left:1px;';
                }
                if ($itype[$startIndx] === "picture") {
                    $picWidth[$startIndx] = $widthAtMax[$startIndx];
                    $picHeight[$startIndx] = $maxRowHt;
                    $thisRow = $thisRow . '<img id="pic' . $startIndx . '" style="' . $styling .
                            '" width="' . $picWidth[$startIndx] . '" height="' . $maxRowHt . '" src="' . 
                            $photolink[$startIndx] . '" alt="' . $caption[$startIndx] . '" />';
                    $imel .= 'p^' . $picWidth[$startIndx] . '^' . $photolink[$startIndx] . 
                            '^' . $caption[$startIndx];
                    $rowxml .= "\t\t<pic>\n\t\t\t<picWdth>" . $picWidth[$startIndx] .
                            "</picWdth>\n\t\t\t<picSrc>" . $photolink[$startIndx] .
                            "</picSrc>\n\t\t\t<picCap>" . $caption[$startIndx] .
                            "</picCap>\n\t\t</pic>\n";
                    $startIndx += 1;
                } else {
                    $othrWidth[$othrIndx] = $widthAtMax[$startIndx];
                    $othrHeight[$othrIndx] = $maxRowHt;
                    $thisRow = $thisRow . '<img style="' . $styling . '" width="' . $othrWidth[$othrIndx] . '" height="' .
                            $maxRowHt . '" src="../images/' . $addonImg[$othrIndx] .
                            '" alt="Additional page image" />';
                    $imel .= 'n^' . $othrWidth[$othrIndx] . '^' . $addonImg[$othrIndx];
                    $rowxml .= "\t\t<pic>\n\t\t\t<picWdth>" . $othrWidth[$othrIndx] .
                            "</picWdth>\n\t\t\t<picSrc>../images/" . 
                            $addonImg[$othIndx] . "</picSrc>\n\t\t</pic>\n";
                    $othrIndx += 1;
                    $startIndx += 1;
                }
                $imgCnt++;
                $imel .=  '^';
            } // end of for loop processing
            $imel = $imgCnt . '^' . $maxRowHt . '^' . $imel;
            array_push($rowStr,$imel);
            $imgRows[$rowNo] = $thisRow . "</div>";
            $rowHtml = $rowHtml . $thisRow . "\n</div>\n";
            $rowxml .= "\t</picRow>\n"; 
    } // end of last row conditional
    # all items have been processed and actual width/heights retained
    $_SESSION['picrows'] = $rowxml;
    # Create the list of album links
    $albumHtml = '<div class="lnkList"><ol>';
    for ($k=0; $k<$noOfPix; $k++ ) {
            $albumHtml = $albumHtml . "<li>{$album[$k]}</li>";
    }
    $albumHtml = $albumHtml . "</ol></div>";
    
    echo $rowHtml;
    echo $albumHtml;
    /*  DEBUG
    $tmprows = fopen("rowXml.xml","w");
    if ($tmprows === false) {
        die ("CANT OPEN tmprows");
    }
    fwrite($tmprows,$rowxml);
    fclose($tmprows);
     */
    ?>