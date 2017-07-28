<!DOCTYPE html>
<html lang="en-us">
    
<head>
    <title>Convert csv to xml database</title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Interim Tool" />
    <meta name="author"
        content="Tom Sandberg and Ken Cowles" />
    <meta name="robots"
        content="nofollow" />  
</head>   
<body>
    <div>
        <p>This tool will convert the current database.csv file to the 
            properly constructed xml file for use in the new xml scripts</p>
<?php
$csvdata = 'database.csv';
//$csvfile = file($csvdata);
$csvfile = fopen($csvdata,"r");
if ($csvfile === false) {
    die ("Could not open database.csv");
}

$xmlheader = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<rows>' . "\n";
$xmlend = '</rows>';
$xml = $xmlheader;
$lineno = 0;
while ( ($hikeLine = fgetcsv($csvfile)) !== false) {
    if ($lineno !== 0) {
        $xml .= "<row>\n";
        $xml .= "\t<indxNo>" . $hikeLine[0] . "</indxNo>\n";
        $xml .= "\t<rlock></rlock>\n";
        $xml .= "\t<pgTitle>" . htmlspecialchars($hikeLine[1]) . "</pgTitle>\n";
        $xml .= "\t<locale>" . htmlspecialchars($hikeLine[2]) . "</locale>\n";
        $xml .= "\t<marker>" . $hikeLine[3] . "</marker>\n";
        $xml .= "\t<clusterStr>" . $hikeLine[4] . "</clusterStr>\n";
        $xml .= "\t<clusGrp>" . $hikeLine[5] . "</clusGrp>\n";
        $xml .= "\t<logistics>" . $hikeLine[6] . "</logistics>\n";
        $xml .= "\t<miles>" . $hikeLine[7] . "</miles>\n";
        $xml .= "\t<feet>" . $hikeLine[8] . "</feet>\n";
        $xml .= "\t<difficulty>" . $hikeLine[9] . "</difficulty>\n";
        $xml .= "\t<facilities>" . htmlspecialchars($hikeLine[10]) . "</facilities>\n";
        $xml .= "\t<wow>" . htmlspecialchars($hikeLine[11]) . "</wow>\n";
        $xml .= "\t<seasons>" . htmlspecialchars($hikeLine[12]) . "</seasons>\n";
        $xml .= "\t<expo>" . $hikeLine[13] . "</expo>\n";
        $xml .= "\t<tsv>\n\t\t<file>" . $hikeLine[14] . "</file>\n\t</tsv>\n";
        #$xml .= '<geomap>' . $hikeLine[15] . '</geomap>' . "\n";
        #$xml .= '<echart>' . $hikeLine[16] . '</echart>' . "\n";
        $xml .= "\t<gpxfile>" . $hikeLine[17] . "</gpxfile>\n";
        $xml .= "\t<trkfile>" . $hikeLine[18] . "</trkfile>\n";
        $xml .= "\t<lat>" . $hikeLine[19] . "</lat>\n";
        $xml .= "\t<lng>" . $hikeLine[20] . "</lng>\n";
        $xml .= "\t<aoimg1>" . $hikeLine[21] . "</aoimg1>\n";
        $xml .= "\t<aoimg2>" . $hikeLine[22] . "</aoimg2>\n";
        $xml .= "\t<mpUrl>" . htmlspecialchars($hikeLine[23]) . "</mpUrl>\n";
        $xml .= "\t<spUrl>" . htmlspecialchars($hikeLine[24]) . "</spUrl>\n";
        $xml .= "\t<dirs>" . htmlspecialchars($hikeLine[25]) . "</dirs>\n";
        $xml .= "\t<cgName>" . htmlspecialchars($hikeLine[28]) . "</cgName>\n";
        $xml .="\t<content>\n";
        # INDEX PAGE:
        if ($hikeLine[3] === 'Visitor Ctr') {
            if ($hikeLine[29] !== '') {
                # there are always 7 elements in each table entry
                $tblrows = explode("|",$hikeLine[29]);
                for ($i=0; $i<count($tblrows); $i++) {  # for each row of data:
                    $xml .= "\t\t<tblRow>\n";
                    $tbldat = explode("^",$tblrows[$i]);
                    if ($tbldat[0] === 'n') {
                        # completed this hike
                        $xml .= "\t\t\t<compl>Y</compl>\n";
                        $xml .= "\t\t\t<tdname>" . htmlspecialchars($tbldat[1]) . 
                            "</tdname>\n";  
                        $pgloc = strrpos($tbldat[2],"=") + 1;
                        $pglgth = strlen($tbldat[2]) - $pgloc;
                        $pgno = substr($tbldat[2],$pgloc,$pglgth);
                        $xml .= "\t\t\t<tdpg>" . $pgno . "</tdpg>\n";
                        $mipos = strpos($tbldat[3]," mile");
                        $mis = substr($tbldat[3],0,$mipos);
                        $xml .= "\t\t\t<tdmiles>" . $mis . "</tdmiles>\n";
                        $ftpos = strpos($tbldat[4]," ft");
                        $ft = substr($tbldat[4],0,$ftpos);
                        $xml .= "\t\t\t<tdft>" . $ft . "</tdft>\n";
                        if (strpos($tbldat[5],'sun') !== false) {
                            $expicon = "Sunny";
                        } elseif (strpos($tbldat[5],'shady') !== false) {
                            $expicon = "Shady";
                        } else {
                            $expicon = "Partial";
                        }
                        $xml .= "\t\t\t<tdexp>" . $expicon . "</tdexp>\n";
                        $xml .= "\t\t\t<tdalb>" . $tbldat[6] . "</tdalb>\n";
                    } else {
                        # this hike not taken yet
                        $xml .= "\t\t\t<compl>N</compl>\n";
                        $xml .= "\t\t\t<tdname>" . htmlspecialchars($tbldat[1]) . 
                            "</tdname>\n";
                        $xml .= "\t\t\t<tdpg>X</tdpg>\n";
                        $mipos = strpos($tbldat[3]," mile");
                        $mis = substr($tbldat[3],0,$mipos);
                        $xml .= "\t\t\t<tdmiles>" . $mis . "</tdmiles>\n";
                        $ftpos = strpos($tbldat[4]," ft");
                        $ft = substr($tbldat[4],0,$ftpos);
                        $xml .= "\t\t\t<tdft>" . $ft . "</tdft>\n";
                        $xml .= "\t\t\t<tdexp>X</tdexp>\n";
                        $xml .= "\t\t\t<tdalb>X</tdalb>\n";
                    }
                    $xml .= "\t\t</tblRow>\n";
                }  # end of foreach row in the table    
            }  # end if hikes present for table of hikes
        # HIKE PAGE:
        } else {
            for ($k=0; $k<6; $k++) {
                if ($hikeLine[29+$k] !== '') {
                    $xml .= "\t\t<picRow>\n";  
                    $picArray = explode("^",$hikeLine[29+$k]);
                    $noOfPix = intval($picArray[0]);
                    $xml .= "\t\t\t<rowHt>" . $picArray[1] . "</rowHt>\n";
                    $indx = 2;  # position of 1st pic data element
                    for ($m=0; $m<$noOfPix; $m++) {
                        $xml .= "\t\t\t<pic>\n";
                        $cap = ($picArray[$indx] === 'p') ? true : false;
                        $xml .= "\t\t\t\t<picWdth>" . $picArray[$indx+1] . 
                                "</picWdth>\n";
                        $xml .= "\t\t\t\t<picSrc>" . $picArray[$indx+2] . 
                                "</picSrc>\n";
                        $l = $k;
                        if ($cap) {
                            $xml .= "\t\t\t\t<picCap>" . 
                                    htmlspecialchars($picArray[$indx+3]) . 
                                    "</picCap>\n";   
                        } else {
                            $xml .= "\t\t\t\t<picCap></picCap>\n";
                        }
                        $indx += 4;
                        $xml .= "\t\t\t</pic>\n";
                    }
                    $xml .= "\t\t</picRow>\n";
                } else {
                    break;
                }
            }  # end of for each of the 6 rows in db
        }  # end of Index Pg marker
        $xml .= "\t</content>\n"; 
        $xml .= "\t<albLinks>\n";
        if ($hikeLine[36] !== '') {
            $lnks2photos = explode("^",hikeLine[36]);
            array_shift($lnks2photos);
            foreach ($lnks2photos as $plnk) {
                $xml .= "\t\t<alb>" . $plnk . "</alb>\n";
            }
        }
        $xml .= "\t</albLinks>\n";
        $xml .= "\t<tipsTxt>" . htmlspecialchars($hikeLine[37]) . "</tipsTxt>\n";
        $xml .= "\t<hikeInfo>" . htmlspecialchars($hikeLine[38]) . "</hikeInfo>\n";
        $xml .= "\t<refs>\n";
            $references = explode("^",$hikeLine[39]);
            $noOfRefs = $references[0];
            array_shift($references);
            $aindx = 0;
            for ($i=0; $i<$noOfRefs; $i++) {
                $xml .= "\t\t<ref>\n";
                $xml .= "\t\t\t<rtype>" . $references[$aindx] . "</rtype>\n";
                if ($references[$aindx] === 'n') {
                    $xml .= "\t\t\t<rit1>No References Found</rit1>\n";
                    $xml .= "\t\t\t<rit2></rit2>\n";
                    $aindx += 2;
                } else {
                    $xml .= "\t\t\t<rit1>" . 
                            htmlspecialchars($references[$aindx+1]) . "</rit1>\n";
                    $xml .= "\t\t\t<rit2>" . 
                            htmlspecialchars($references[$aindx+2]) . "</rit2>\n";
                    $aindx += 3;
                }
                $xml .= "\t\t</ref>\n";
            }
        $xml .= "\t</refs>\n";
        $xml .= "\t<dataProp>\n";
            if ($hikeLine[40] !== '') {
                $proposed = explode("^",$hikeLine[40]);
                $noOfProps = $proposed[0];
                array_shift($proposed);
                $pindx = 0;
                for ($j=0; $j<$noOfProps; $j++) {
                    $xml .= "\t\t<prop>\n\t\t\t<plbl>" . $proposed[$pindx] .
                            "</plbl>\n";
                    $xml .= "\t\t\t<purl>" . $proposed[$pindx+1] . "</purl>\n";
                    $xml .= "\t\t\t<pcot>" . 
                            htmlspecialchars($proposed[$pindx+2]) . "</pcot>\n";
                    $xml .= "\t\t</prop>\n";
                    $pindx += 3;
                }
            }
        $xml .= "\t</dataProp>\n";
        $xml .= "\t<dataAct>\n";
            if ($hikeLine[41] !== '') {
                $actuals = explode("^",$hikeLine[41]);
                $noOfActs = $actuals[0];
                array_shift($actuals);
                $aindx = 0;
                for ($k=0; $k<$noOfActs; $k++) {
                    $xml .= "\t\t<act>\n\t\t\t<albl>" . $actuals[$aindx] .
                            "</albl>\n";
                    $xml .= "\t\t\t<aurl>" . $actuals[$aindx+1] . "</aurl>\n";
                    $xml .= "\t\t\t<acot>" . 
                            htmlspecialchars($actuals[$aindx+2]) . "</acot>\n";
                    $xml .= "\t\t</act>\n";
                    $aindx += 3;
                }
            }
        $xml .= "\t</dataAct>\n";
        $xml .= '</row>' . "\n";
    }  # end of if non-header lines
    $lineno++;
}  # end of while reading in data

$xml .= $xmlend;
$xmldata = 'database.xml';
$xmlfile = fopen($xmldata,"w");
if ($xmlfile === false) {
    die ("Could not open xml file for write");
}
fwrite($xmlfile,$xml);
fclose($xmlfile);
?>    
        <p>DONE!</p>
    </div>
</body>    
</html>
