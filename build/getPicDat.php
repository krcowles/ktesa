<?php
/* 
 * This routine fetches album html from each of the user-specified album
 * links, and parses it for the data needed to create hike page photos with
 * links and captions. 
 */
function getFlickrDat($photomodel,$size) {
    $ltrSize = strlen($size);  # NOTE: at least 1 size is two letters
    $offset = 4 + $ltrSize;
    $modelLtr = '"' . $size . '":{';
    $sizePos = strpos($photomodel,$modelLtr) + $offset;
    $urlPos = strpos($photomodel, '"url":"',$sizePos) + 7;
    $urlEnd = strpos($photomodel, '"', $urlPos);
    $urlLgth = $urlEnd - $urlPos;
    $rawurl = substr($photomodel, $urlPos, $urlLgth);
    $url = 'https:' . preg_replace('/\\\\/','',$rawurl);
    return $url;
}
function mantissa($degrees) {
    $coords = 0;
    for ($z = 0; $z < 3; $z++) {
        $div = strpos($degrees[$z], '/');
        $body = substr($degrees[$z], 0, $div);
        $divisor = substr($degrees[$z], $div + 1);
        switch ($z) {
            case 0:
                $coords = $body / $divisor;
                break;
            case 1:
                $mins = $body / $divisor;
                break;
            case 2:
                $secs = $body / $divisor;
                break;
        }
    }
    $coords += ($mins + $secs / 60) / 60;
    return $coords;
}
# output msg styling:
$pstyle = '<p style="margin-left:16px;color:red;font-size:20px;">';
#default icon color:
$icon_clr = filter_input(INPUT_POST,'icon');
if ($icon_clr === '') {
    $icon_clr = 'Google default';
}

$curlids = $_POST['phpcurl'];
$supplied = 0;
for ($n=0; $n<count($curlids); $n++) {
    if ($curlids[$n] !== '') {
        $supplied++;
    }
}
if ($supplied === 0) {
    $nourls = $pstyle . 'No urls were specified for photo albums: please go '
            . 'back and provide one or more legitimate photo album urls, or '
            . 'select the box for "I do not wish to specify pictures at '
            . 'this time"</p>';
    die ($nourls);
}

# variables used in processing data from albums:
$albums = $_POST['albtype'];
$xmlTsvStr = '';
$pcnt = 0;  # no of photos processed
# These arrays will be sorted by date/time after all albums have been processed
$folder = [];
$titles = [];
$descriptions = [];
$alinks = [];  
$o = [];
$t = [];
$n = [];
# EXIF data arrays
$imgs = [];
$imgHt = [];
$imgWd = [];
$timeStamp = [];
$lats = [];
$lngs = [];
$gpds = [];
$gpts = [];

for ($i=0; $i<$supplied; $i++) {
    /* For each album link, extract the 'orginal' size photo link, and then
     * read enough of it to ensure exif data; also extract other data items
     * needed to create the tsv file (or be stored in the database, later),
     * i.e. name, description, thumbnail, link to photo in album, medium-size
     * (may be identical to thumbnail).
     */
    
    if ($curlids[$i] !== '') {  # skip empty input-boxes
        $curlid = filter_var($curlids[$i],FILTER_VALIDATE_URL);
        if ($curlid === false) {
            $badurl = $pstyle . 'The value you entered is not a qualified url '
                    . 'address<br />Please go back and re-enter the url with a '
                    . 'valid address.</p>';
            die($badurl);
        }
        $albType = filter_var($albums[$i]);  # should always be valid from <select>
        if (($albumHtml = file_get_contents($curlid)) !== false) {
            # FLICKR:
            if ($albType === 'flckr') {
                $albOcnt = 0; # no of originals in this link
                # First, find "albumId":
                $albLoc = strpos($albumHtml, '{"albumId":"') + 12;
                $flickrInfo = substr($albumHtml, $albLoc);
                $albEnd = strpos($flickrInfo, '"');
                $albumId = substr($flickrInfo, 0, $albEnd);
                $alubmHtml = '';
                $srchPat = '{"_flickrModelRegistry":"photo-models","title":"';              
                $pmodels = strpos($flickrInfo, $srchPat) + 48;
                while ( $pmodels !== false && $pmodels !== 48 ) {
                    $folder[$pcnt] = 'Folder' . ($i+1);
                    $modelInfo = substr($flickrInfo, $pmodels);
                    $titleEnd = strpos($modelInfo, '"');
                    $titles[$pcnt] = substr($modelInfo, 0, $titleEnd);
                    # if the 'description' field does not exist, use default desc. below:
                    $descPos = strpos($modelInfo, '"description":"');
                    if ($descPos === false) {
                        $descriptions[$j] = 'Enter description here';
                    } else {
                        $descPos += 15;
                        $descEnd = strpos($modelInfo, '"', $descPos);
                        $descLgth = $descEnd - $descPos;
                        $descriptions[$pcnt] = substr($modelInfo, $descPos, $descLgth);
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
                    $o[$pcnt] = getFlickrDat($modelInfo,'o');
                    $n[$pcnt] = getFlickrDat($modelInfo,'n');
                    $t[$pcnt] = getFlickrDat($modelInfo,'t');
                    $albOcnt++;
                    $pcnt++;
                    # adjust the search to the next photo-model:
                    $flickrInfo = $modelInfo;
                    $pmodels = strpos($flickrInfo, $srchPat) + 48;
                }  # end of while loop collecting album data for pics            
                # Now capture the exif data for the $o(riginal photos) array
                include 'getExif.php';
            # APPLE:
            } elseif ($albType === 'apple') {
                # no code at this time
            # GOOGLE:
            } elseif ($albType === 'googl') {
                # no code at this time
            }
        } else {  # end of getting album html from link
            $noalb = $pstyle . 'Could not extract album html: please verify '
                    . 'that the album link is correct</p>';
            die ($noalb);
        }
    }  # end of non-empty curlid
}  # end of for each album url input box
# sort the arrays based on timestamp: (all data will end up in $xmlout array
include "timeSort.php";
foreach ($xmlout as $tsvdata) {  # each item is an array of data
    # NOTE: $hikeNo has not yet been decremented to correlate hike no w/ indx no
    $newpic = $xml->row[$hikeNo-1]->tsv->addChild('picDat');
    $newpic->addChild('title',htmlspecialchars($tsvdata['pic']));
    $newpic->addChild('hpg','N');
    $newpic->addChild('mpg','N');
    $newpic->addChild('desc',htmlspecialchars($tsvdata['desc']));
    $newpic->addChild('lat',$tsvdata['lat']);
    $newpic->addChild('lng',$tsvdata['lng']);
    $newpic->addChild('thumb',$tsvdata['thumb']);
    $newpic->addChild('alblnk',$tsvdata['alb']);
    $newpic->addChild('date',$tsvdata['taken']);
    $newpic->addChild('mid',$tsvdata['nsize']);
    $newpic->addChild('imgHt',$tsvdata['pHt']);
    $newpic->addChild('imgWd',$tsvdata['pWd']);
    $newpic->addChild('iclr',$icon_clr);
    $newpic->addChild('org',$tsvdata['org']);
}
?>
