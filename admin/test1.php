<?php
$gpxfile = "../gpsv.gpx";
$dom = new DOMDocument();
$dom->load($gpxfile);
$tracks = $dom->getElementsByTagName('trk');
$trkitem = 0; // designated track;
// select the subject track (to be an argument in a function call specifying
// which track is to be reversed when the function is created):
$track = $tracks->item($trkitem);
$trkchildren = $track->childNodes; // DOMNodeList
echo "TEST1: 'Wild Child'" . PHP_EOL . PHP_EOL;
echo "NOTE: EXPERIMENTS PERFORMED ON FIRST TRACK" . PHP_EOL . PHP_EOL;
echo "This is a list of the children of the first track, followed by a list " . PHP_EOL;
echo "of the children of that track's first segment, or <trkseg>." . PHP_EOL;
echo "These lists were obtained by using the 'childNodes' property of" . PHP_EOL;
echo "the DOMNode object (DOMDocument extends that class and thereby inherits" . PHP_EOL;
echo "its methods). Note that the object type returned is: DOMNodeList - which" . PHP_EOL;
echo "is 'countable' and 'traversable', meaning it is easily placed into " . PHP_EOL;
echo "a for or foreach loop." . PHP_EOL . PHP_EOL;
echo "Since there are more children than one would expect, the results for" . PHP_EOL;
echo "the <trkseg> children are correlated by echoing out the nodeName property" . PHP_EOL;
echo "for each child, as well as the line no in the source file where that child" . PHP_EOL;
echo "appears. There is a text node for each trkpt node <trkpt> in the source file. " . PHP_EOL . PHP_EOL;
echo "As the trkpts only have attributes and child nodes, those text nodes are" . PHP_EOL;
echo "empty in this case. Surprisingly though, there is a 'final' unassociated " . PHP_EOL;
echo "child node, which, based on its location in the source file, must be the " . PHP_EOL;
echo "closing tag for the <trkseg>. Why this appears as a child of <trkseg> is " . PHP_EOL;
echo "not clear." . PHP_EOL . PHP_EOL;
echo "The no of children seen in this track: " . $trkchildren->length . PHP_EOL;
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
echo "This track's first <trkseg> node has " . $pts0->length . " children." . 
    PHP_EOL . PHP_EOL;
echo "A listing of the <trkseg> childNodes using foreach:" . PHP_EOL;
foreach ($pts0 as $trkpt) {
    echo "At line no " . $trkpt->getLineNo();
    echo " nodeName is " . $trkpt->nodeName;
    echo " (attempt to print nodeValue: [" . $trkpt->nodeValue . "] );";
    echo "is child of ";
    echo $trkpt->parentNode->nodeName . PHP_EOL;
}
echo "Note that the last line no corresponds to the closing trkseg tag" . PHP_EOL;
