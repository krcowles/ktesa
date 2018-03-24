<?php
$gpxfile = "../gpsv.gpx";
$dom = new DOMDocument();
$dom->load($gpxfile);
$tracks = $dom->getElementsByTagName('trk');
$trkitem = 1; // designated track;
// select the subject track (to be an argument in a function call specifying
// which track is to be reversed when the function is created):
$track = $tracks->item($trkitem);
$trkchildren = $track->childNodes; // DOMNodeList
$segno = 0;
$segNodes = [];
foreach ($trkchildren as $trkchild) {
    if ($trkchild->nodeName === 'trkseg') {
        $segNodes[$segno] = $trkchild;
        $segno ++;
    }   
}
$segCnt = count($segNodes);
$pts0 = $segNodes[0]->childNodes;
echo "TEST2: '2 + 2 <> 4'" . PHP_EOL . PHP_EOL;
echo "NOTE: EXPERIMENTS PERFORMED ON SECOND TRACK, NOT FIRST" . PHP_EOL . PHP_EOL;
echo "Because of the 'interesting' result when listing a count of child nodes in " . PHP_EOL;
echo "the DOMNodeList object for trkseg, the expectation would be that in order " . PHP_EOL;
echo "to print the nodes using 'saveXML()', one should iterate through a foreach" . PHP_EOL;
echo "loop (or for loop) child by child and apply the method. As it is, that produces" . PHP_EOL;
echo "an odd result." .PHP_EOL;
echo "Attempting a child by child node echo w/: foreach(trpkpts as trkpt) { " .
    "echo dom->saveXML(trkpt); } results in: " . PHP_EOL;
foreach ($pts0 as $trkpt) {
    echo "Child: ";
    echo $dom->saveXML($trkpt) . PHP_EOL;
}
echo PHP_EOL . PHP_EOL;
echo "Now, attempt to print out every-other-node, skipping over the '#text'" . PHP_EOL;
echo "nodes: [next section blank as it simply does not work]" . PHP_EOL;
$lim = $pts0->length - 1;
for ($i=0; $i<$lim; $i+=2) {
    echo $dom->saveXML($pts0->item($i)) . PHP_EOL;
}
echo "Yet another experiment: create an element for trkseg then appendChild nodes" . PHP_EOL;
echo "for the full count of children:" . PHP_EOL;
echo "More wierdness: 1) if you use the EXISTING set of pts to append to the " . PHP_EOL;
echo "newly created trkseg, that new seg will come up empty. 2) If you REVERSE the" . PHP_EOL;
echo "order of the pts, the original seg will be empty, and the pts will appear " . PHP_EOL;
echo "in reverse order in the newly created trkseg. 3) If you simply make up some" . PHP_EOL;
echo "nodes with fake data and append, both segs show up: the orginal as is, and" . PHP_EOL;
echo "the newseg with fake data. Note that in case 1 & 2, you must loop through " . PHP_EOL;
echo "ALL children MINUS 1, or it will not execute!" . PHP_EOL . PHP_EOL;
echo "Here's 2) REVERSED DATA in new seg, with empty original seg: " . PHP_EOL;
$newseg = $dom->createElement('trkseg');
$track->appendChild($newseg);
$newseg->setAttribute('id', 29);
for ($j=$lim; $j>0; $j--) {
    $node = $pts0->item($j);
    $next = $newseg->appendChild($node);
}
echo $dom->saveXML();
echo PHP_EOL . "Here's 3) FAKE DATA with original data in original seg, and new seg " . PHP_EOL;
echo "w/fake data" . PHP_EOL;
$newdom = new DOMDocument();
$newdom->formatOutput = true;
$newdom->load($gpxfile);
$newtracks = $newdom->getElementsByTagName('trk');
$newtrack = $newtracks->item($trkitem);

$fakedat = $newdom->createElement('trkseg');
$newtrack->appendChild($fakedat);
$fakedat->setAttribute('id', 30);
for ($k=0; $k<6; $k++) {
    $dat = '100.0' . $k;
    $node = $newdom->createElement('trkpt');
    $node->setAttribute('lat', $dat);
    $node->setAttribute('lon', $dat);
    $fnext = $fakedat->appendChild($node);
}
echo $newdom->saveXML();
