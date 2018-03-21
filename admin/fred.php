<?php
$file = '../gpx/BlackCanyonComposite.gpx';
$xml = simplexml_load_file($file);
// start a new file:
$newgpx = new SimpleXMLElement("<gpx></gpx>");
// get the "kids"
$allnodes = $xml->children();
$out = '';
foreach ($allnodes as $node) {
   if ($node->getName() === "trk") {
       $trkchildren = $node->children();
       foreach ($trkchildren as $childtag) {
           if ($childtag !== 'trkseg') {
               $out .= $childtag->asXML();
           }
       }
   } else {
       $out .= $node->asXML();
   }
}
echo $out;
