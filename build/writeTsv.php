<?php
echo "Called write to " . $tmpTsv;
if ($tsvOut === false) {
    die ("DEAD IN THE WATER");
}
for ($a=0; $a<count($o); $a++) {
    $outdat = array($folder,$titles[$a],$descriptions[$a],
        $lats[$a],$lngs[$a],$t[$a],$alinks[$a],$timeStamp[$a],
        $n[$a],'','',$icon_clr);
    fputcsv($tsvOut,$outdat,"\t");
}
#$tsvSize = filesize($newtsv);
?>