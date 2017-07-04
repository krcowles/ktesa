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
        $xml .= '<row>' . "\n";
        $xml .= '<indxNo>' . $hikeLine[0] . '</indxNo>' . "\n";
        $xml .= '<pgTitle>' . htmlspecialchars($hikeLine[1]) . '</pgTitle>' . "\n";
        $xml .= '<locale>' . htmlspecialchars($hikeLine[2]) . '</locale>' . "\n";
        $xml .= '<marker>' . $hikeLine[3] . '</marker>' . "\n";
        $xml .= '<clusterStr>' . $hikeLine[4] . '</clusterStr>' . "\n";
        $xml .= '<clusGrp>' . $hikeLine[5] . '</clusGrp>' . "\n";
        $xml .= '<logistics>' . $hikeLine[6] . '</logistics>' . "\n";
        $xml .= '<miles>' . $hikeLine[7] . '</miles>' . "\n";
        $xml .= '<feet>' . $hikeLine[8] . '</feet>' . "\n";
        $xml .= '<difficulty>' . $hikeLine[9] . '</difficulty>' . "\n";
        $xml .= '<facilities>' . htmlspecialchars($hikeLine[10]) . '</facilities>' . "\n";
        $xml .= '<wow>' . htmlspecialchars($hikeLine[11]) . '</wow>' . "\n";
        $xml .= '<seasons>' . htmlspecialchars($hikeLine[12]) . '</seasons>' . "\n";
        $xml .= '<expo>' . $hikeLine[13] . '</expo>' . "\n";
        $xml .= '<tsv>' . $hikeLine[14] . '</tsv>' . "\n";
        $xml .= '<geomap>' . $hikeLine[15] . '</geomap>' . "\n";
        $xml .= '<echart>' . $hikeLine[16] . '</echart>' . "\n";
        $xml .= '<gpxfile>' . $hikeLine[17] . '</gpxfile>' . "\n";
        $xml .= '<trkfile>' . $hikeLine[18] . '</trkfile>' . "\n";
        $xml .= '<lat>' . $hikeLine[19] . '</lat>' . "\n";
        $xml .= '<lng>' . $hikeLine[20] . '</lng>' . "\n";
        $xml .= '<aoimg1>' . $hikeLine[21] . '</aoimg1>' . "\n";
        $xml .= '<aoimg2>' . $hikeLine[22] . '</aoimg2>' . "\n";
        $xml .= '<mpUrl>' . htmlspecialchars($hikeLine[23]) . '</mpUrl>' . "\n";
        $xml .= '<spUrl>' . htmlspecialchars($hikeLine[24]) . '</spUrl>' . "\n";
        $xml .= '<dirs>' . htmlspecialchars($hikeLine[25]) . '</dirs>' . "\n";
        $xml .= '<obs1></obs1>' . "\n";
        $xml .= '<obs2></obs2>' . "\n";
        $xml .= '<cgName>' . htmlspecialchars($hikeLine[28]) . '</cgName>' . "\n";
        $xml .='<content>' . "\n";
        # INDEX PAGE:
        if ($hikeLine[3] === 'Visitor Ctr') {
            if ($hikeLine[29] !== '') {
                # there are always 7 elements in each table entry
                $tblrows = explode("|",$hikeLine[29]);
                for ($i=0; $i<count($tblrows); $i++) {  # for each row of data:
                    $xml .= '<tblRow>' . "\n";
                    $tbldat = explode("^",$tblrows[$i]);
                    if ($tbldat[0] === 'n') {
                        # completed this hike
                        $xml .= '<compl>Y</compl>' . "\n";
                        $xml .= '<tdname>' . htmlspecialchars($tbldat[1]) . 
                            '</tdname>' . "\n";  
                        $pgloc = strrpos($tbldat[2],"=") + 1;
                        $pglgth = strlen($tbldat[2]) - $pgloc;
                        $pgno = substr($tbldat[2],$pgloc,$pglgth);
                        $xml .= '<tdpg>' . $pgno . '</tdpg>' . "\n";
                        $mipos = strpos($tbldat[3]," mile");
                        $mis = substr($tbldat[3],0,$mipos);
                        $xml .= '<tdmiles>' . $mis . '</tdmiles>' . "\n";
                        $ftpos = strpos($tbldat[4]," ft");
                        $ft = substr($tbldat[4],0,$ftpos);
                        $xml .= '<tdft>' . $ft . '</tdft>' . "\n";
                        if (strpos($tbldat[5],'sun') !== false) {
                            $expicon = "Sunny";
                        } elseif (strpos($tbldat[5],'shady') !== false) {
                            $expicon = "Shady";
                        } else {
                            $expicon = "Partial";
                        }
                        $xml .= '<tdexp>' . $expicon . '</tdexp>' . "\n";
                        $xml .= '<tdalb>' . $tbldat[6] . '</tdalb>' . "\n";
                    } else {
                        # this hike not taken yet
                        $xml .= '<compl>N</compl>' . "\n";
                        $xml .= '<tdname>' . htmlspecialchars($tbldat[1]) . 
                            '</tdname>' . "\n";
                        $xml .= '<tdpg>X</tdpg>' . "\n";
                        $mipos = strpos($tbldat[3]," mile");
                        $mis = substr($tbldat[3],0,$mipos);
                        $xml .= '<tdmiles>' . $mis . '</tdmiles>' . "\n";
                        $ftpos = strpos($tbldat[4]," ft");
                        $ft = substr($tbldat[4],0,$ftpos);
                        $xml .= '<tdft>' . $ft . '</tdft>' . "\n";
                        $xml .= '<tdexp>X</tdexp>' . "\n";
                        $xml .= '<tdalb>X</tdalb>' . "\n";
                    }
                    $xml .= '</tblRow>' . "\n";
                }  # end of foreach row in the table    
            }  # end if hikes present for table of hikes
        # HIKE PAGE:
        } else {
            for ($k=0; $k<6; $k++) {
                if ($hikeLine[29+$k] !== '') {
                    $xml .= '<picRow>' . htmlspecialchars($hikeLine[29+$k]) . '</picRow>' . "\n";
                } else {
                    break;
                }
            }
        }  # end of Index Pg marker
        $xml .= '</content>' . "\n";   
        $xml .= '<obs3></obs3>' . "\n";
        $xml .= '<albLinks>' . htmlspecialchars($hikeLine[36]) . '</albLinks>' . "\n";
        $xml .= '<tipsTxt>' . htmlspecialchars($hikeLine[37]) . '</tipsTxt>' . "\n";
        $xml .= '<hikeInfo>' . htmlspecialchars($hikeLine[38]) . '</hikeInfo>' . "\n";
        $xml .= '<refs>' . htmlspecialchars($hikeLine[39]) . '</refs>' . "\n";
        $xml .= '<dataProp>' . htmlspecialchars($hikeLine[40]) . '</dataProp>' . "\n";
        $xml .= '<dataAct>' . htmlspecialchars($hikeLine[41]) . '</dataAct>' . "\n";
        $xml .= '</row>' . "\n";
    }  # end of if non-header lines
    $lineno++;
}  # end of while reading in data

$xml .= $xmlend;
#echo substr($xml,2,10);
$xmldata = 'database.xml';
$xmlfile = fopen($xmldata,"w");
if ($xmlfile === false) {
    die ("Could not open xml file for write");
}
$test = "Now is the time for all good men to come to the aid of their party.";
fwrite($xmlfile,$xml);
fclose($xmlfile);
?>    
        <p>DONE!</p>
    </div>
</body>    
</html>
