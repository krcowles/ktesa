<?php
$refXmlStr = "\t<refs>\n";
for ($r=0; $r<$noOfRefs; $r++) {
    $refXmlStr .= "\t\t<ref>\n";
    $refXmlStr .= "\t\t\t<rtype>";
    switch ($hikeRefTypes[$k]) {
        case 'b':
            $refXmlStr .= 'Book';
            break;
        case 'p':
            $refXmlStr .= 'Photo Essay';
            break;
        case 'w':
            $refXmlStr .= 'Website';
            break;
        case 'a':
            $refXmlStr .='App';
            break;
        case 'd':
            $refXmlStr .= 'Downloadable Doc';
            break;
        case 'l':
            $refXmlStr .= 'Blog';
            break;
        case 'r':
            $refXmlStr .= 'Related Link';
            break;
        case 'o':
            $refXmlStr .= 'On-Line Map';
            break;
        case 'm':
            $refXmlStr .= 'Magazine';
            break;
        case 's':
            $refXmlStr .= 'News Article';
            break;
        case 'g':
            $refXmlStr .= 'Meetup Group';
            break;
        case 'n':
            $refXmlStr .= '';
            break;
        default:
            echo "Unrecognized reference type passed";
    }
    $refXmlStr .= "</rtype>\n";
    $refXmlStr .= "\t\t\t<rit1>" . $hikeRefItems1[$r] . "</rit1>\n";
    $refXmlStr .= "\t\t\t<rit2>" . $hikeRefItems2[$r] . "</rit2>\n";
    $refXmlStr .= "\t\t</ref>\n";
}
$refXmlStr .= "\t</refs>\n";
$_SESSION['hikerefs'] = $refXmlStr;
$tmp = fopen("tmpRefs.xml", "w");
if ($tmp === false) {
       die ("NO OPEN");
}
fwrite($tmp,$refXmlStr);
fclose($tmp);
?>