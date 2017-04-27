<!DOCTYPE html>
<html lang="en-us">
<head>
	<title>Base php Template</title>
	<meta charset="utf-8" />
	<meta name="description"
		content="Start a new php script" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<!-- LINK TO CSS? 
	<link href="../styles/mapTblPg.css" type="text/css" rel="stylesheet" />  -->

</head>

<body>

<?php
$database = 'database.csv';
$db = fopen($database,"r");
$moddat = array();
$wholeDB = array();
$i = 0;  # line number counter
while ( ($dat = fgetcsv($db)) != false ) {  # $dat is an array = a row of data
    $rowcnt = 0;
    $wholeDB[$i] = $dat;
    if ($dat[3] !== 'Visitor Ctr') {  # don't process Index Pages
        #echo " ->TYPE: " . $dat[3];
        for ($j=0; $j<6; $j++) {
            if ($i === 0) {
                break;  # header row
            }
            $rowdat = $dat[29+$j];
            if ($rowdat === '') {
                break;
            }
            $rowcnt++;
            $geomap = strpos($rowdat,"html");
            $chart = preg_match("/elev/i",$rowdat);
            $newrow = array();
            if ( $geomap > 0 || $chart === 1 ) {  # re-write the row w/o these
                #if ( $i === 12) { echo "[11] Prev: " . $rowdat; }
                $oldrow = explode("^",$rowdat);
                $itemCnt = intval($oldrow[0]);
                array_shift($oldrow);
                $orgHt = $oldrow[0];
                array_shift($oldrow);
                if ($itemCnt === 1) {
                    # by defn., this is the last row, and will be eliminated
                    #echo "ROW OUT!";
                    $dat[29+$j] = "";
                    break;
                } else {
                    for ($k=0; $k<count($oldrow); $k++) {
                        if ($oldrow[$k] === "p") {  # PHOTO
                            for ($l=0; $l<4; $l++) {
                               array_push($newrow,$oldrow[$k+$l]);
                            }
                            #echo "GOT PHOTO...";
                            $k += 3;  # $k is incremented at end of loop!
                        } elseif ($oldrow[$k] === 'f') {  # IFRAME
                           $itemCnt--;
                           $k += 2;  # $k is incremented at end of loop!
                        } elseif (preg_match("/elev/i",$oldrow[$k+2]) === 1) {  # CHART
                           $itemCnt--;
                           $k += 2;  # $k is incremented at end of loop!
                           if ($i === 12) { echo "GOT CHART; cnt now " . $itemCnt; }
                        } else {  # this is a non-captioned image (not chart)
                            #echo "GOT non-captioned IMAGE...";
                            for ($m=0; $m<3; $m++) {
                               array_push($newrow,$oldrow[$k+$m]);
                            }
                            $k += 2;  # $k is incremented at end of loop!
                        }
                        if ($itemCnt === 0) {
                           break;
                        }
                        #echo "loop item " . ($k+1) . ", next symbol: " . $oldrow[$k+1];
                    }  # end of loop to process actual iframe or chart removal
                    #  all items have been pushed back into $newrow
                    if ($itemCnt === 0) {
                        #echo "oldrow gone!";
                        $rowdat = '';
                    } else {
                        array_unshift($newrow,$itemCnt,$orgHt);
                        $rowdat = implode("^",$newrow);
                    }
                    #if ($i === 12) { echo "****" . $rowdat . "****"; }
                }  # end of else to process elmination of items   
            }  /* end of 'if there is a chart or geomap 
             * $rowdat is now fully determined:
             *  a) unchanged because no chart or iframe;
             *  b) changed in above uf statement
             * change the corresponding $dat field (row string):
             */
            $dat[29+$j] = $rowdat;
        }  # end of row processing loop     
    }  # end of "if not Index Page", $dat can be saved back to database
    #if ($i === 12) { echo "-------[" . $j . "]" . $rowdat . " ---------- "; }
    $wholeDB[$i] = $dat;
    #if ($rowdat === "") { echo "EMPTY NOW: line " . $i . ", row " . $j; }
    #if ($i === 12) { echo "Critical rows: ------------->" . $dat[31] . '<----------------' . $dat[32] . "$$$"; }
    $i++;
}  #end while
fclose($db);

/*
echo "<p>---------DATA: ";
for ($q=0; $q<42; $q++) {
    echo $wholeDB[12][$q] . '</p><p>';
}
echo '</p>';
 * 
 */
$tstout = 'test.csv';
$tst = fopen($tstout,"w");
foreach ($wholeDB as $hikedat) {
    if( (fputcsv($tst,$hikedat) === false) ) { echo "FAILED LINE WRITE"; }
}
fclose($tst);

?>
<div style="padding:16px;">
iframes and charts removed from database!
</div>

</body>

</html>