<?php
require_once "setenv.php";
# NOTE: not using 'usrid' yet...
$query = "SELECT folder,title,hpg,mpg,`desc`,lat,lng,thumb,alblnk,date," .
        "mid,imgHt,imgWd FROM TSV WHERE indxNo = '{$hikeIndexNo}';";
$result = mysqli_query($link,$query);
if (!$result) {
    die ("get_REFS_row.php: Unable to extract references from REFS: " .
            mysqli_error());
}
$months = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug",
    "Sep","Oct","Nov","Dec");
$descs = [];
$alblnks = [];
$piclnks = [];
$captions = [];
$aspects = [];
$widths = [];
while ($pics = mysqli_fetch_assoc($result)) {
    if ($pics['hpg'] === 'Y') {
        array_push($descs,$pics['title']);
        array_push($alblnks,$pics['alblnk']);
        array_push($piclnks,$pics['mid']);
        $pDesc = $pics['desc'];
        $dateStr = $pics['date'];
            if ($dateStr == '') {
                array_push($captions,$pDesc);
            } else {
                $year = substr($dateStr,0,4);
                $month = intval(substr($dateStr,5,2));
                $day = intval(substr($dateStr,8,2));  # intval strips leading 0
                $date = $months[$month-1] . ' ' . $day . ', ' . $year .
                        ': ' . $pDesc;
                array_push($captions,$date);
            }
            $ht = intval($pics['imgHt']);
            $wd = intval($pics['imgWd']);
            array_push($widths,$wd);
            $picRatio = $wd/$ht;
            array_push($aspects,$picRatio);
    }
}

