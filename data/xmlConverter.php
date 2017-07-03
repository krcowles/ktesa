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
        $xml .= '<indxNo>' . htmlspecialchars($hikeLine[0]) . '</indxNo>' . "\n";
        $xml .= '<pgTitle>' . htmlspecialchars($hikeLine[1]) . '</pgTitle>' . "\n";
        $xml .= '<locale>' . htmlspecialchars($hikeLine[2]) . '</locale>' . "\n";
        $xml .= '<marker>' . htmlspecialchars($hikeLine[3]) . '</marker>' . "\n";
        $xml .= '<clusterStr>' . htmlspecialchars($hikeLine[4]) . '</clusterStr>' . "\n";
        $xml .= '<clusGrp>' . htmlspecialchars($hikeLine[5]) . '</clusGrp>' . "\n";
        $xml .= '<logistics>' . htmlspecialchars($hikeLine[6]) . '</logistics>' . "\n";
        $xml .= '<miles>' . htmlspecialchars($hikeLine[7]) . '</miles>' . "\n";
        $xml .= '<feet>' . htmlspecialchars($hikeLine[8]) . '</feet>' . "\n";
        $xml .= '<difficulty>' . htmlspecialchars($hikeLine[9]) . '</difficulty>' . "\n";
        $xml .= '<facilities>' . htmlspecialchars($hikeLine[10]) . '</facilities>' . "\n";
        $xml .= '<wow>' . htmlspecialchars($hikeLine[11]) . '</wow>' . "\n";
        $xml .= '<seasons>' . htmlspecialchars($hikeLine[12]) . '</seasons>' . "\n";
        $xml .= '<expo>' . htmlspecialchars($hikeLine[13]) . '</expo>' . "\n";
        $xml .= '<tsv>' . htmlspecialchars($hikeLine[14]) . '</tsv>' . "\n";
        $xml .= '<geomap>' . htmlspecialchars($hikeLine[15]) . '</geomap>' . "\n";
        $xml .= '<echart>' . htmlspecialchars($hikeLine[16]) . '</echart>' . "\n";
        $xml .= '<gpxfile>' . htmlspecialchars($hikeLine[17]) . '</gpxfile>' . "\n";
        $xml .= '<trkfile>' . htmlspecialchars($hikeLine[18]) . '</trkfile>' . "\n";
        $xml .= '<lat>' . htmlspecialchars($hikeLine[19]) . '</lat>' . "\n";
        $xml .= '<lng>' . htmlspecialchars($hikeLine[20]) . '</lng>' . "\n";
        $xml .= '<aoimg1>' . htmlspecialchars($hikeLine[21]) . '</aoimg1>' . "\n";
        $xml .= '<aoimg2>' . htmlspecialchars($hikeLine[22]) . '</aoimg2>' . "\n";
        $xml .= '<mpUrl>' . htmlspecialchars($hikeLine[23]) . '</mpUrl>' . "\n";
        $xml .= '<spUrl>' . htmlspecialchars($hikeLine[24]) . '</spUrl>' . "\n";
        $xml .= '<dirs>' . htmlspecialchars($hikeLine[25]) . '</dirs>' . "\n";
        $xml .= '<obs1></obs1>' . "\n";
        $xml .= '<obs2></obs2>' . "\n";
        $xml .= '<cgName>' . htmlspecialchars($hikeLine[28]) . '</cgName>' . "\n";
        $xml .= '<picRow0>' . htmlspecialchars($hikeLine[29]) . '</picRow0>' . "\n";
        $xml .= '<picRow1>' . htmlspecialchars($hikeLine[30]) . '</picRow1>' . "\n";
        $xml .= '<picRow2>' . htmlspecialchars($hikeLine[31]) . '</picRow2>' . "\n";
        $xml .= '<picRow3>' . htmlspecialchars($hikeLine[32]) . '</picRow3>' . "\n";
        $xml .= '<picRow4>' . htmlspecialchars($hikeLine[33]) . '</picRow4>' . "\n";
        $xml .= '<picRow5>' . htmlspecialchars($hikeLine[34]) . '</picRow5>' . "\n";
        $xml .= '<obs3></obs3>' . "\n";
        $xml .= '<albLinks>' . htmlspecialchars($hikeLine[36]) . '</albLinks>' . "\n";
        $xml .= '<tipsTxt>' . htmlspecialchars($hikeLine[37]) . '</tipsTxt>' . "\n";
        $xml .= '<hikeInfo>' . htmlspecialchars($hikeLine[38]) . '</hikeInfo>' . "\n";
        $xml .= '<refs>' . htmlspecialchars($hikeLine[39]) . '</refs>' . "\n";
        $xml .= '<dataProp>' . htmlspecialchars($hikeLine[40]) . '</dataProp>' . "\n";
        $xml .= '<dataAct>' . htmlspecialchars($hikeLine[41]) . '</dataAct>' . "\n";
        $xml .= '</row>' . "\n";
    }
    $lineno++;
}

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
