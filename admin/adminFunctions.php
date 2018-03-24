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
 * This function specifies which track, in the list of tracks, to reverse.
 * The function will be called iteratively if multiple tracks are to be
 * reversed. When there are multiple segments within the subject track, 
 * the segments will remain in order, but the data in each segment will be 
 * reversed.
 * 
 * @param DOMNodeList $trknodes List of track objects from which to select
 * @param integer     $trkno    identifies the track number (from 0) to reverse
 * 
 * @return $modfile  xml file with track reversed.
 */
function reverseTrack($trknodes, $trkno)
{
    $track = $trknodes->item($trkno);
    $trkchildren = $track->childNodes; // DOMNodeList
    // retrieve the child nodes that are <trkseg> nodes and save them in $segNodes
    $segno = 0;
    $segNodes = [];
    /**
     * Note: cannot add any children inside the loop, because the childNodes list
     * gets updated instantly, and then the foreach iterates ad infinitum
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
        $actualPts = $pts->length - 1; // last child is trkseg's text node
        $newseg = $track->ownerDocument->createElement('trkseg');
        $track->appendChild($newseg); // will not append identical children
        $newseg->setAttribute('id', $j);
        for ($k=$actualPts; $k>0; $k--) {
            $next = $newseg->appendChild($pts->item($k));
        }
        $remd = $track->removeChild($segNodes[$j]);
    }
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
