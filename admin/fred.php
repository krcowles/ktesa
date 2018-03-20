<?php
$file = '../gpsv.gpx';
$xml = simplexml_load_file($file);
echo $xml->gpx->asXML();
$allnodes = $xml->children();
$out = '';
foreach ($allnodes as $node) {
    $out .= $node->asXML();
}
echo $out;
