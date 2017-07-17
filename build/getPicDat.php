<?php
/* 
 * This routine fetches album html from each of the user-specified album
 * links, and parses it for the data needed to create hike page photos with
 * links and captions. While developed in the 'convert2xml' branch, it can at
 * first be used with database.csv code, where it will create a 'tsv' file.
 * Later, no tsv file will be created - the data will instead be stored
 * directly in the xml database.
 */

# output msg styling:
$pstyle = '<p style="margin-left:16px;color:red;font-size:20px;">';
/* IMAGE TYPES CURRENTLY SUPPORTED: only .jpg, .JPG - expandable below:
 * Note: file extensions converted to all lower case during search
 */
$supportedImgs = array('jpg');
$noOfImgTypes = count($supportedImgs);

$curlids = $_POST['phpcurl'];
$albums = $_POST['albtype'];
for ($i=0; $i<count($curlids); $i++) {
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
                # First, find "albumId":
                $albLoc = strpos($albumHtml, '{"albumId":"') + 12;
                $flickrInfo = substr($albumHtml, $albLoc);
                $albEnd = strpos($flickrInfo, '"');
                $albumId = substr($flickrInfo, 0, $albEnd);
                #echo "<p>Album ID: " . $albumId . '</p>';
                $alubmHtml = '';
                $srchPat = '{"_flickrModelRegistry":"photo-models","title":"';
                $patCnt = 0;
                $titles = [];
                $descriptions = [];
                $alinks = [];  
                $o = [];
                $t = [];
                $n = [];
                $pmodels = strpos($flickrInfo, $srchPat) + 48;
                while ( $pmodels !== false && $pmodels !== 48 ) {
                    $modelInfo = substr($flickrInfo, $pmodels);
                    $titleEnd = strpos($modelInfo, '"');
                    $titles[$patCnt] = substr($modelInfo, 0, $titleEnd);
                    # if the 'description' field does not exist, use default desc. below:
                    $descPos = strpos($modelInfo, '"description":"');
                    if ($descPos === false) {
                        $descriptions[$j] = 'Enter description here';
                    } else {
                        $descPos += 15;
                        $descEnd = strpos($modelInfo, '"', $descPos);
                        $descLgth = $descEnd - $descPos;
                        $descriptions[$patCnt] = substr($modelInfo, $descPos, $descLgth);
                    }
                    $idPos = strpos($modelInfo, '"id":"') + 6;
                    $idEnd = strpos($modelInfo, '"', $idPos);
                    $idLgth = $idEnd - $idPos;
                    $ownerId = substr($modelInfo, $idPos, $idLgth);
                    $nsidPos = strpos($modelInfo, '"ownerNsid":"') + 13;
                    $nsidEnd = strpos($modelInfo, '"', $nsidPos);
                    $nsidLgth = $nsidEnd - $nsidPos;
                    $Nsid = substr($modelInfo, $nsidPos, $nsidLgth);
                    $alinks[$patCnt] = 'https://www.flickr.com/photos/' . $Nsid .
                    '/' . $ownerId . '/in/album-' . $albumId;
                    $o[$patCnt] = getDat($modelInfo,'o');
                    $n[$patcnt] = getDat($modelInfo,'n');
                    $t[$patCnt] = getDat($modelInfo,'t');
                    $patCnt++;
                    # adjust the search to the next photo-model:
                    $flickrInfo = $modelInfo;
                    $pmodels = strpos($flickrInfo, $srchPat) + 48;
                }  # end of while loop collecting album data for pics
                echo "No of pictures in album is " . $patCnt . '<br />';
                # Now capture the exif data for the $o(riginal photos) array
                require 'getExif.php';
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
die ("QUIT HERE");

?>
