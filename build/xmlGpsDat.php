<?php
$xmlPDat = "\t<dataProp>\n";
for ($p=0; $p<$noOfPDats; $p++) {
    $xmlPDat .= "\t\t<prop>\n";
    $xmlPDat .= "\t\t\t<plbl>" . $hikePDatLbls[$p] . "</plbl>\n";
    $xmlPDat .= "\t\t\t<purl>" . $hikePDatUrls[$p] . "</purl>\n";
    $xmlPDat .= "\t\t\t<pcot>" . $hikePDatCTxts[$p] . "</pcot>\n";
    $xmlPDat .= "\t\t</prop>\n";
}
$xmlPDat .= "\t</dataProp>\n";
$_SESSION['propdata'] = $xmlPDat;
$xmlADat = "\t<dataAct>\n";
for ($q=0; $q<$noOfADats; $q++) {
    $xmlADat .= "\t\t<act>\n";
    $xmlADat .= "\t\t\t<albl>" . $hikeADatLbls[$q] . "</albl>\n";
    $xmlADat .= "\t\t\t<aurl>" . $hikeADatUrls[$q] . "</aurl>\n";
    $xmlADat .= "\t\t\t<acot>" . $hikeADatCTxts[$q] . "</acot>\n";
    $xmlADat .= "\t\t</act>\n";
}
$xmlADat .= "\t</dataAct>\n";
$_SESSION['actdata'] = $xmlADat;
$tmp = fopen("gpsDat.xml","w");
if ($tmp === false) {
    die("NO FILE FOR GPS MAPS AND DATA");
}
fwrite($tmp,$xmlPDat);
fwrite($tmp,$xmlADat);
fclose($tmp);
?>