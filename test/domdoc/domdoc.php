<?php
/**
 * This is a test module for developing a working knowledge
 * of the DOMDocument class, and utilizing it to modify gpx
 * files (e.g. reverse a designated track's <trkpt>s);
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
$gpxfile = "../gpsv.gpx";
//$gpxfile = "../gpx/BlackCanyonComposite.gpx";
$dom = new DOMDocument();
$dom->load($gpxfile);
$tracks = $dom->getElementsByTagName('trk');
$trkitem = 1;
// select the subject track
$track = $tracks->item($trkitem);
$trkchildren = $track->childNodes; // DOMNodeList
// retrieve the child nodes that are <trkseg> nodes and save them in $segNodes
$segno = 0;
$segNodes = [];
/**
 * Note: cannot add any children inside the loop, because the childNodes list
 * gets updated automatically, and the for each iterates ad infinitum
 */
foreach ($trkchildren as $trkchild) {
    if ($trkchild->nodeName === 'trkseg') {
        $segNodes[$segno] = $trkchild;
        $segno ++;
    }   
}
$segCnt = count($segNodes);
for ($j=0; $j<$segCnt; $j++) {
    // process each trkseg node separately:
    $pts = $segNodes[$j]->childNodes;
    $actualPts = $pts->length - 1;
    $newseg = $dom->createElement('trkseg');
    $track->appendChild($newseg); // will not append identical children
    $newseg->setAttribute('id', $j);
    for ($k=$actualPts; $k>0; $k--) {
        $next = $newseg->appendChild($pts->item($k));
    }
    $remd = $track->removeChild($segNodes[$j]);
}
echo $dom->saveXML();
