<?php
#Assumes that $gpxdat holds the gpx file uploaded as simplexml]
$gpxlats = [];
$gpxlons = [];
$gpxelev = [];  # not used at this point
$plat = 0;
$plng = 0;
foreach($gpxdat->trk->trkseg as $trackdat) {
    foreach ($trackdat->trkpt as $datum) {
        if ( !( $datum['lat'] === $plat && $datum['lon'] === $plng ) ) {
            $plat = $datum['lat'];
            $plng = $datum['lon'];
            array_push($gpxlats,(float)$plat);
            array_push($gpxlons,(float)$plng);
            $meters = $datum->ele;
            $feet = round(3.28084 * $meters,1);
            array_push($gpxelev,$feet);
        }
    }
}
if ($json) {
    $jdat = '[';   # array of objects
    for ($n=0; $n<count($gpxlats); $n++) {
        $jdat .= '{"lat":' . $gpxlats[$n] . ',"lng":' . $gpxlons[$n] . '},';
    }
    $jdat = substr($jdat,0,strlen($jdat)-1);
    $jdat .= ']';
}