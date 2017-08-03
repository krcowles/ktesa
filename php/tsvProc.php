<?php
/* 
 * Note that there are multiple 'versions' of tsv files, depending on 
 * which phase of the project evolution was in place at the time of its
 * creation. For this reason, the process includes a means of identifying
 * the required fields in the file, regardless of exactly where they occur.
 */
$gpsvPath = '../gpsv/' . $gpsvfile;
$gpsvData = file($gpsvPath);
if ($gpsvData === false) {
    die ($tsvmsg . $gpsvPath . $close);
} 
# default large nos to verify index value gets set...
$folder = 10000;
$desc = 10000;
$nme = 10000;
$tsvLat = 10000;
$tsvLng = 10000;
$thumb = 10000;
$albumUrl = 10000;
$icn = 10000;
$icolor = 10000;
$headerline = true;
foreach ($gpsvData as $entry) {
    # eliminate ending "\n" via $stripped:
    $stripped = substr($entry,0,strlen($entry)-1);
    $rawdat = explode("\t",$stripped);
    if ($headerline) {
        # use header row to get indices
        for ($p=0; $p<count($rawdat); $p++) {
            switch ($rawdat[$p]) {
                case 'folder':
                    $folder = $p;
                    break;
                case 'desc':
                    $desc = $p;
                    break;
                case 'name':
                    $nme = $p;
                    break;
                case 'Latitude':
                    $tsvLat = $p;
                    break;
                case 'Longitude':
                    $tsvLng = $p;
                    break;
                case 'thumbnail':
                    $thumb = $p;
                    break;
                case 'url':
                    $albumUrl = $p;
                    break;
                case 'symbol':
                    $icn = $p;
                    break;
                case 'color':
                    $icolor = $p;
                    break;
                default:
                    break;
            }
        }
        $headerline = false;
    } else {
        if ($icolor === 10000) {
            $icon = $defIconColor;
        } else {
            #$out = "indx: " . $icolor . " value: " . (string)$rawdat[$icolor];
            $icon = $rawdat[$icolor];
        }
        if ($icn === 10000) {
            $mapicon = '';
        } else {
            $mapicon = $rawdat[$icn];
        }
        $procName = preg_replace("/'/","\'",$rawdat[$nme]);
        $procName = preg_replace('/"/','\"',$procName);
        $plnk = "GV_Draw_Marker({lat:" . $rawdat[$tsvLat] . ",lon:" . $rawdat[$tsvLng] . 
            ",name:'" . $procName . "',desc:'" . $rawdat[$desc] . 
            "',color:'" . $icon . "',icon:'" . $mapicon . 
            "',url:'" . $rawdat[$albumUrl] . "',thumbnail:'" . $rawdat[$thumb] .
            "',folder:'" . $rawdat[$folder] . "'});";
        array_push($plnks,$plnk);
    }   
}