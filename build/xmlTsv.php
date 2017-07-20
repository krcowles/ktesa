<?php
# Form the <picDat> xml element for the page, to be written at 'Save Page' time
for ($a=0; $a<$pcnt; $a++) {
    $tsvStr = "\t\t<picDat>\n";
    $tsvStr .= "\t\t\t<folder>" . $xmlout[$a]['folder'] . "</folder>\n";
    $tsvStr .= "\t\t\t<title>" . $xmlout[$a]['pic]'] . "</title>\n";
    $tsvStr .= "\t\t\t<desc>" . $xmlout[$a]['desc'] . "</desc>\n";
    $tsvStr .= "\t\t\t<lat>" . $xmlout[$a]['lat'] . "</lat>\n";
    $tsvStr .= "\t\t\t<lng>" . $xmlout[$a]['lng'] . "</lng>\n";
    $tsvStr .= "\t\t\t<thumb>" . $xmlout[$a]['thumb'] . "</thumb>\n";
    $tsvStr .= "\t\t\t<alblnk>" . $xmlout[$a]['alb'] . "</alblnk>\n";
    $tsvStr .= "\t\t\t<date>" . $xmlout[$a]['taken'] . "</date>\n";
    $tsvStr .= "\t\t\t<mid>" . $xmlout[$a]['nsize'] . "</mid>\n";
    $tsvStr .= "\t\t\t<imgHt>" . $xmlout[$a]['pHt'] . "</imgHt>\n";
    $tsvStr .= "\t\t\t<imgWd>" . $xmlout[$a]['pWd'] . "</imgWd>\n";
    $tsvStr .= "\t\t\t<org>" . $xmlout[$a]['org'] . "</org>\n";
    $tsvStr .= "\t\t</picDat>\n";
    $xmlTsvStr .= $tsvStr;
}
# this is the last executed statement from 'getPicDat.php
$xmlTsvStr .= $tsvStr;
?>