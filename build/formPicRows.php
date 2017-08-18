<?php
# image processing definitions - see include file: formPicRows.php
define("SPACING", 14, true);
define("MAXWIDTH", 960, true);
define("ROWHT", 260, true);
define("TOOMUCHMARGIN", 80, true);
define("MIN_IFRAME_SIZE", 270, true);

$addonImg[0] = $xml->row[$hikeRow]->aoimg1;
$imgIndx = 0;
if (strlen($addonImg[0]) === 0) {
    $noOfOthr = 0;
} else {
    $noOfOthr = 1;
    $firstimg = getimagesize("../images/" . $addonImg[0]);
    $othrWidth[$imgIndx] = $firstimg[0];
    $othrHeight[$imgIndx] = $firstimg[1];
    $imgIndx += 1;
    $img1File = '../images/' . $addonImg[0];
}
$addonImg[1] = $xml->row[$hikeRow]->aoimg2;
if (strlen($addonImg[1]) !== 0) {
    $noOfOthr += 1;
    $secondimg = getimagesize("../images/" . $addonImg[1]);
    $othrWidth[$imgIndx] = $secondimg[0];
    $othrHeight[$imgIndx] = $secondimg[1];
    $img2File = '../images/' . $addonImg[1];
}
# predefine array for months...
$month = array("Jan","Feb","Mar","Apr","May","Jun",
                                "Jul","Aug","Sep","Oct","Nov","Dec");
# for each of the <user-selected> pix, define needed arrays
$i = 0;
foreach ($xml->row[$hikeRow]->tsv->picDat as $upic) {
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
    for ($i=0; $i<$items; $i++) {
        /*
         *  This begins a new row; each row ends after exiting the 'for' loop
         *  contained in the if($curWidth > $rowWidth) statement.
         */
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
            $newPicRow = $xml->row[$hikeRow]->content->addChild('picRow');
            $newPicRow->addChild('rowHt',$actualHt);
            $thisRow = '';
            $imgCnt = 0;
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
                        '" alt="' . $caption[$n] . '" />' . "\n";	
                    $newPicEl = $newPicRow->addChild('pic');
                    $newPicEl->addChild('picWdth',$picWidth[$n]);
                    $newPicEl->addChild('picSrc',$photolink[$n]);
                    $newPicEl->addChild('picCap',$caption[$n]);
                } else {  # its an additional non-captioned image
                    $othrWidth[$othrIndx] = floor($scaleFactor * $widthAtMax[$n]);
                    $othrHeight[$othrIndx] = $actualHt;
                    $thisRow = $thisRow . '<img style="' . $styling . '" width="' . $othrWidth[$n] .
                            '" height="' . $actualHt . '" src="../images/' . $addonImg[$othrIndx] .
                            '" alt="Additional non-captioned image" />' . "\n";
                    $newPicEl = $newPicRow->addChild('pic');
                    $newPicEl->addChild('picWdth', $othrWidth[$n]);
                    $newPicEl->addChild('picSrc',$addonImg[$othrIndx]);
                    $newPicEl->addChild('picCap','');
                    $othrIndx += 1;
                }
                $imgCnt++;
            }  # end of for loop $n
            
            # thisRow is completed and will be used below in different ways:
            $rowHtml = $rowHtml . $thisRow . "\n</div>\n";
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
        $newPicRow = $xml->row[$hikeRow]->content->addChild('picRow');
        $newPicRow->addChild('rowHt',$maxRowHt);
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
                    $newPicEl = $newPicRow->addChild('pic');
                    $newPicEl->addChild('picWdth',$picWidth[$startIndx]);
                    $newPicEl->addChild('picSrc',$photolink[$startIndx]);
                    $newPicEl->addChild('picCap',$caption[$startIndx]);
                    $startIndx += 1;
                } else {
                    $othrWidth[$othrIndx] = $widthAtMax[$startIndx];
                    $othrHeight[$othrIndx] = $maxRowHt;
                    $thisRow = $thisRow . '<img style="' . $styling . '" width="' . $othrWidth[$othrIndx] . '" height="' .
                            $maxRowHt . '" src="../images/' . $addonImg[$othrIndx] .
                            '" alt="Additional page image" />';
                    $newPicEl = $newPicRow->addChild('pic');
                    $newPicEl->addChild('picWdth',$picWidth[$othrIndx]);
                    $newPicEl->addChild('picSrc',$photolink[$othrIndx]);
                    $newPicEl->addChild('picCap','');
                    $othrIndx += 1;
                    $startIndx += 1;
                }
                $imgCnt++;
            } // end of for loop processing
            $imgRows[$rowNo] = $thisRow . "</div>";
            $rowHtml = $rowHtml . $thisRow . "\n</div>\n";
    } // end of last row conditional
    # place album links:
    foreach ($album as $link) {
        $xml->row[$hikeRow]->albLinks->addChild('alb',$link);
    }
    ?>