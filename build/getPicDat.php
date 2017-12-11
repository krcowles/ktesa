<?php
/* 
 * This routine fetches album html from each of the user-specified album
 * links, and parses it for the data needed to create hike page photos with
 * links and captions. Other scripts are included.
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
if ( isset($caller) && $caller === 'newPhotos') {
    $opt = 'adds';
} else {
    $opt = 'validates';
}
# output msg styling:
$pstyle = '<p style="margin-left:16px;color:red;font-size:20px;">';
#default icon color:
$supplied = 0;
if ($opt === 'adds') {
    $supplied = count($curlids);
    $icon_clr = 'Google default';
    # curlids and albums already defined
} else {
    $icon_clr = filter_input(INPUT_POST,'icon');
    if ($icon_clr === '') {
        $icon_clr = 'Google default';
    }
    # get any supplied urls and if none, return to user
    $curlids = $_POST['phpcurl']; # array of urls
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
}
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
                    # Look ahead to see if there is at least one more model:
                    $tst4end = strpos($modelInfo,$srchPat);
                    if ($tst4end === false) {
                        $modelEnd = strlen($modelInfo);
                    } else {
                        $modelEnd = $tst4end;
                    }
                    $descPos = strpos($modelInfo, '"description":"');
                    if ($descPos === false || $descPos > $modelEnd) {
                        $descriptions[$pcnt] = 'Enter description here';
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
# sort the arrays based on timestamp:
include "timeSort.php";
if ($opt === 'validates') {
    foreach ($picdat as $ph) {  # each item is an array of data
        $foldr = mysqli_real_escape_string($link,$ph['folder']);
        $ttl = mysqli_real_escape_string($link,$ph['pic']);
        $des = mysqli_real_escape_string($link,$ph['desc']);
        $lt = mysqli_real_escape_string($link,$ph['lat']);
        $ln = mysqli_real_escape_string($link,$ph['lng']);
        $th = mysqli_real_escape_string($link,$ph['thumb']);
        $al = mysqli_real_escape_string($link,$ph['alb']);
        $dt = mysqli_real_escape_string($link,$ph['taken']);
        $sz = mysqli_real_escape_string($link,$ph['nsize']);
        $ht = mysqli_real_escape_string($link,$ph['pHt']);
        $wd = mysqli_real_escape_string($link,$ph['pWd']);
        $ic = mysqli_real_escape_string($link,$icon_clr);
        $og = mysqli_real_escape_string($link,$ph['org']);
        $photoQuery = "INSERT INTO ETSV ( indxNo,folder,title," .
            "hpg,mpg,`desc`,lat,lng,thumb,alblnk,date,mid," .
            "imgHt,imgWd,iclr,org ) VALUES ( '{$hikeNo}','{$foldr}'," .
            "'{$ttl}','N','N','{$des}','{$lt}','{$ln}','{$th}'," .
            "'{$al}','{$dt}','{$sz}','{$ht}','{$wd}','{$ic}','{$og}' );";
        $photoResults = mysqli_query($link,$photoQuery);
        if (!$photoResults) {
            die ("getPicDat.php: Could not insert data into ETSV: " . mysqli_error($link));
        }
    }
    mysqli_free_result($photoResult);
}
