<?php
/** 
 * This routine fetches album html from each of the user-specified album
 * links, and parses it for the data needed to create a list of photos with
 * links and captions. Other scripts are included. NOTE: This script is 
 * invoked as an ajax call from javascript. This means that any echoed output,
 * including 'die' statements, are sent back to the caller as data, and is 
 * handled in the calling routine.
 * PHP Version 7.1
 * 
 * @package Create
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$curldat = filter_input(INPUT_POST, 'albs');
$typedat = filter_input(INPUT_POST, 'types');
$supplied = filter_input(INPUT_POST, 'cnt');
$curlids = json_decode($curldat);
$albums = json_decode($typedat);
// output msg styling:
$pstyle = '<p style="margin-left:16px;color:red;font-size:18px;">';
$pcnt = 0;  // no of photos processed
// These arrays will be sorted by date/time after all albums have been processed
$folder = [];
$titles = [];
$descriptions = [];
$alinks = [];
$o = [];
$t = [];
$n = [];
// EXIF data arrays
$imgs = [];
$imgHt = [];
$imgWd = [];
$timeStamp = [];
$lats = [];
$lngs = [];
$gpds = [];
$gpts = [];
for ($i=0; $i<$supplied; $i++) {
    /**
     * For each album link, extract the 'orginal' size photo link, and then
     * read enough of it to ensure exif data; also extract other data items
     * needed to create the tsv file (or be stored in the database, later),
     * i.e. name, description, thumbnail, link to photo in album, medium-size
     * (may be identical to thumbnail).
     */
    if ($curlids[$i] !== '') {  // skip empty input-boxes
        $curlid = filter_var($curlids[$i], FILTER_VALIDATE_URL);
        if ($curlid === false) {
            $badurl = $pstyle . 'The value you entered is not a qualified url '
                . 'address<br />Please go back and re-enter the url with a '
                . 'valid address.</p>';
            die($badurl);
        }
        $albType = filter_var($albums[$i]);  // should always be valid from <select>
        if (($albumHtml = file_get_contents($curlid)) !== false) {
            // FLICKR:
            if ($albType === 'flckr') {
                $albOcnt = 0; // no of originals in this link
                // First, find "albumId":
                $albLoc = strpos($albumHtml, '{"albumId":"') + 12;
                $flickrInfo = substr($albumHtml, $albLoc);
                $albEnd = strpos($flickrInfo, '"');
                $albumId = substr($flickrInfo, 0, $albEnd);
                $alubmHtml = '';
                $srchPat = '{"_flickrModelRegistry":"photo-models","title":"';
                $pmodels = strpos($flickrInfo, $srchPat) + 48;
                while ($pmodels !== false && $pmodels !== 48) {
                    $folder[$pcnt] = 'Folder' . ($i+1);
                    $modelInfo = substr($flickrInfo, $pmodels);
                    $titleEnd = strpos($modelInfo, '"');
                    $titles[$pcnt] = substr($modelInfo, 0, $titleEnd);
                    /* NOTE: The description field may not exist if no description
                     * was entered in Flickr. If the first (or subsequent image
                     * PRIOR to the final image in the list) has no decription,
                     * that must be determined before encountering the next
                     * "photo-models" section (otherwise, the next or incorrect
                     * description from a succeeding photo-model is found and 
                     * falsely extracted). Find the position of that next 
                     * photo-model then test the $descPos to make sure it 
                     * occurs (or doesn't occur) prior to that point.
                     */
                    // Look ahead to see if there is at least one more model:
                    $tst4end = strpos($modelInfo, $srchPat);
                    if ($tst4end === false) {
                        $modelEnd = strlen($modelInfo);
                    } else {
                        $modelEnd = $tst4end;
                    }
                    $descPos = strpos($modelInfo, '"description":"');
                    if ($descPos === false || $descPos > $modelEnd) {
                        $descriptions[$pcnt] = null;
                    } else {
                        $descPos += 15;
                        $descEnd = strpos($modelInfo, '"', $descPos);
                        $descLgth = $descEnd - $descPos;
                        $descriptions[$pcnt] 
                            = substr($modelInfo, $descPos, $descLgth);
                    }
                    $idPos = strpos($modelInfo, '"id":"') + 6;
                    $idEnd = strpos($modelInfo, '"', $idPos);
                    $idLgth = $idEnd - $idPos;
                    $ownerId = substr($modelInfo, $idPos, $idLgth);
                    $nsidPos = strpos($modelInfo, '"ownerNsid":"') + 13;
                    $nsidEnd = strpos($modelInfo, '"', $nsidPos);
                    $nsidLgth = $nsidEnd - $nsidPos;
                    $Nsid = substr($modelInfo, $nsidPos, $nsidLgth);
                    $alinks[$pcnt] = 'https://www.flickr.com/photos/' . $Nsid .
                    '/' . $ownerId . '/in/album-' . $albumId;
                    $o[$pcnt] = getFlickrDat($modelInfo, 'o');
                    $n[$pcnt] = getFlickrDat($modelInfo, 'n');
                    $t[$pcnt] = getFlickrDat($modelInfo, 't');
                    $albOcnt++;
                    $pcnt++;
                    // adjust the search to the next photo-model:
                    $flickrInfo = $modelInfo;
                    $pmodels = strpos($flickrInfo, $srchPat) + 48;
                }  // end of while loop collecting album data for pics
                // Now capture the exif data for the $o(riginal photos) array
               include 'getExif.php';
            } elseif ($albType === 'apple') {
                // no code at this time
            } elseif ($albType === 'googl') {
                // no code at this time
            }
        } else {  // end of getting album html from link
            $noalb = $pstyle . 'Could not extract album html from ' . $curlids[$i] .
                ' please verify ' . 'that the album link is correct</p>';
            die($noalb);
        }
    }  // end of non-empty curlid
}  // end of for each album url input box
// sort the arrays based on timestamp:
require "timeSort.php";
$picinfo = json_encode($picdat);
echo $picinfo;
