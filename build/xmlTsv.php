<?php
# Form the <picDat> xml element for the page, to be written at 'Save Page' time
for ($a=0; $a<count($o); $a++) {
    $tsvStr = "\t\t<picDat>\n";
    $tsvStr .= "\t\t\t<folder>" . $folder . "</folder>\n";
    $tsvStr .= "\t\t\t<title>" . $titles[$a] . "</title>\n";
    $tsvStr .= "\t\t\t<desc>" . $descriptions[$a] . "</desc>\n";
    $tsvStr .= "\t\t\t<lat>" . $lats[$a] . "</lat>\n";
    $tsvStr .= "\t\t\t<lng>" . $lngs[$a] . "</lng>\n";
    $tsvStr .= "\t\t\t<thumb>" . $t[$a] . "</thumb>\n";
    $tsvStr .= "\t\t\t<alblnk>" . $alinks[$a] . "</alblnk>\n";
    $tsvStr .= "\t\t\t<date>" . $timeStamp[$a] . "</date>\n";
    $tsvStr .= "\t\t\t<mid>" . $n[$a] . "</mid>\n";
    $tsvStr .= "\t\t\t<imgHt>" . $imgHt[$a] . "</imgHt>\n";
    $tsvStr .= "\t\t\t<imgWd>" . $imgWd[$a] . "</imgWd>\n";
    $tsvStr .= "\t\t\t<org>" . $o[$a] . "</org>\n";
    $tsvStr .= "\t\t</picDat>\n";
    $xmlTsvStr .= $tsvStr;
}
# this is the last executed statement from 'getPicDat.php
$xmlTsvStr .= $tsvStr;
?>