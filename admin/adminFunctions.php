<?php
/**
 * This module contains a function (to be moved later) that
 * will reverse the designated track in a gpx file and output
 * a new file. Track numbers begin at 0.
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
/**
 * This function will accept two SimpleXML node objects and append
 * one ('from') after the other ('to').
 * 
 * @param SimpleXMLElement $to   The xml node on which $from will be appended
 * @param SimpleXMLElement $from The xml node being appended to $to
 * 
 * @return object xml object with node appended
 */
function sxml_append(SimpleXMLElement $to, SimpleXMLElement $from)
{
    $toDom = dom_import_simplexml($to);
    $fromDom = dom_import_simplexml($from);
    $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
}
/**
 * This function accepts a simplexml file and a track number as input.
 * The single track no indicates which track is to have its trkpts reversed.
 * The function will be called iteratively if multiple tracks are to be
 * reversed. When there are multiple segments within the subject track, 
 * the segments will remain in order, but the data in each segment will be 
 * reversed.
 * 
 * @param string  $sxml  simpleXML tree loaded from  gpx file
 * @param integer $trkno identifies the track number (from 0) to reverse
 * 
 * @return simpleXMLElement $modfile  file as simpleXml tree with track reversed.
 */
function reverseTrack($sxml, $trkno)
{
    // locate the designated track:
    $track = $sxml->trk[$trkno];
    // Note that there will likely be intervening tags between <trk>and <trkseg>
    foreach ($track->children() as $trkseg) {
        // reverse the contents of each <trkseg>
        if ($trkseg->getName() === 'trkseg') {
            $trkpts = [];
            $ptlist = $trkseg->children(); // assumes all trkseg children are trkpts
            foreach ($ptlist as $trkpt) {
                array_push($trkpts, $trkpt);
            }
            $rtrkpts = array_reverse($trkpts);
            // form a simpleXML node from the reversed array
            $newNode = '<trkseg>' . PHP_EOL;
            foreach ($rtrkpts as $node) {
                $newNode .= $node->asXML() . PHP_EOL;
            }
            $newNode .= '</trkseg>';
            $newChild = new SimpleXMLElement($newNode);
            sxml_append($trkseg, $newChild);
        }
    }
    echo "New No of Trk Segs: " . $sxml->trk[$trkno]->trkseg->count();
    return $sxml;
}
/**
 * This function supplies a message appropriate to the type of upload
 * error encountered.
 * 
 * @param integer $errdat The flag supplied by the upload error check
 * 
 * @return string 
 */
function uploadErr($errdat)
{
    if ($errdat === UPLOAD_ERR_INI_SIZE || $errdat === UPLOAD_ERR_FORM_SIZE) {
        return 'File is too large for upload';
    }
    if ($errdat === UPLOAD_ERR_PARTIAL) {
        return 'The file was only partially uploaded (no further information';
    }
    if ($errdat === UPLOAD_ERR_NO_FILE) {
        return 'The file was not uploaded (no further information';
    }
    if ($errdat === UPLOAD_ERR_CANT_WRITE) {
        return 'Failed to write file to disk';
    }
    if ($errdat === UPLOAD_ERR_EXTENSION) {
        return 'A PHP extension stopped the upload';
    }
}
