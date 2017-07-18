<?php

/* The following function converts the exif data latitude/longitude into a decimal no.
 * Each lat/lng passed in via exif is an array with three parts: 
 *  1. degrees / divisor: usually 1
 *  2. minutes / divisor: usually 1
 *  3. seconds / divisor: often 100)
 */
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
function getDat($photomodel,$size) {
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
function convtTime($GPStime) {
    $hrs = explode("/",$GPStime[0]);
    $hr = intval($hrs[0]/$hrs[1]);
    $mins = explode("/",$GPStime[1]);
    $min = intval($mins[0]/$mins[1]);
    $secs = explode("/",$GPStime[2]);
    $sec = intval($secs[0]/$secs[1]);
    $tstring = $hr . ':' . $min . ":" . $sec;
    return $tstring;
}
# output msg styling:
$pstyle = '<p style="margin-left:16px;color:red;font-size:20px;">';
/*
 * Extract and validate the form data:
 * If there is something missing, the user will be notified;
 * If there are bad paths, patterns, etc. the user will be notified;
 * If there is no $curlid, nothing happens in this routine;
 */
$curlid = filter_input(INPUT_POST, 'phpcurl', FILTER_VALIDATE_URL);
if ($curlid === false) {
    $badmsg = $pstyle . 'The value you entered is not a qualified url address<br />' .
            'Please go back and re-enter the url with a valid address.</p>';
    die($badmsg);
}
if ($curlid !== '') {
    /*
     * IMAGE TYPES CURRENTLY SUPPORTED: only .jpg, .JPG - expandable below:
     * Note: file extensions converted to all lower case during search
     */
    $supportedImgs = array('jpg');
    $noOfImgTypes = count($supportedImgs);
    # retrieve album type...
    $albType = filter_input(INPUT_POST, 'albtype');
    /*
     * First get a list of all the image files in the local directory
     */
    $localPath = filter_input(INPUT_POST, 'lphotos');
    if ($localPath === '') {
        $nofind = $pstyle .
                'There is no specified path to your local copy of the photos:' .
                '<br />Please go back and re-enter a valid path.</p>';
        die($nofind);
    }
    $cwdStart = getcwd();
    if (chdir($localPath) === false) {
        $badpath = $pstyle . 'Could not access ' . $localPath . ';<br />Please ' .
                'verify that the path is correct and has appropriate permissions</p>';
        die($badpath);
    }
    if (($files = scandir($localPath)) === false) {
        $nofiles = $pstyle .'Could not find files in ' . $localPath . ';<br />' .
                'Please verify that the path you supplied is a readable directory</p>';
    } else {
        # collect the photos into an array
        $photos = [];
        foreach ($files as $direntry) {
            $iext = strrpos($direntry, '.') + 1;
            $itype = strtolower(substr($direntry, $iext));
            $supported = false;
            for ($i = 0; $i < $noOfImgTypes; $i++) {
                if ($itype === $supportedImgs[$i]) {
                    $supported = true;
                }
            }
            if ($direntry !== '.' && $direntry !== '..' && $supported) {
                array_push($photos, $direntry);
            }
        }
        $noOfImgs = count($photos);
        if ($noOfImgs === 0) {
            $noimgs = $pstyle . 'No supported (jpg) photos were found in the ' . 
                    $localPath . ' directory</p>';
            die($noimgs);
        }
        /*
         * This section extracts the metadata from the jpg files...
         */
        $imgs = [];
        $imgHt = [];
        $imgWd = [];
        $imgPh = [];  // not currently used for anything
        $timeStamp = [];
        $lats = [];
        $lngs = [];
        $elev = [];   // not currently used for anything...
        $gpds = [];   // GPS DateStamp
        $gpts = [];   // GPS TimeStamp array
        for ($x = 0; $x < $noOfImgs; $x++) {
            $exifdata = exif_read_data($photos[$x], ANY_TAG, EXIF);
            if ($exifdata !== false) {
                foreach ($exifdata as $key => $section) {
                    foreach ($section as $name => $val) {
                        switch ($key) {
                            case 'FILE':
                                if ($name === 'FileName') {
                                    $ext = strrpos($val,".");
                                    $imgName = substr($val,0,$ext);
                                    $imgs[$x] = $imgName;
                                }
                                break;
                            case 'COMPUTED':
                                if ($name === 'Height') {
                                    $imgHt[$x] = $val;
                                } elseif ($name === 'Width') {
                                    $imgWd[$x] = $val;
                                }
                                break;
                            case 'IFD0':
                                if ($name === 'Model') {
                                    $imgPh[$x] = $val;
                                }
                                break;
                            case 'EXIF':
                                if ($name === 'DateTimeOriginal') {
                                    $timeStamp[$x] = $val;
                                    if ($val == '') {
                                        echo "WARNING: No date/time data found " .
                                                'for ' . $photos[$x] . '</p>';
                                    }
                                }
                            case 'GPS':
                                if ($name === 'GPSLatitude') {
                                    $lats[$x] = mantissa($val);
                                } elseif ($name === 'GPSLongitude') {
                                    $lngs[$x] = -1 * mantissa($val);
                                } elseif ($name === 'GPSAltitude') {
                                    $elev[$x] = $val;
                                } elseif ($name === 'GPSDateStamp') {
                                    $gpds[$x] = $val;
                                } elseif ($name === 'GPSTimeStamp') {
                                    // array
                                    $gpts[$x] = $val;
                                }
                              
                                break;
                            default:
                                break;
                        }  // end of switch statement
                    }  // end of foreach $name loop
                }  // end of foreach $section loop
                if ($lats[$x] == 0 || $lngs[$x] == 0) {
                    echo $pstyle . "WARNING: No latitude/longitude data obtained for " .
                        $photos[$x] . '</p>';
                }
            } else {  // end of 'if exif data found 
                echo $pstyle . 'WARNING: Could not read exif data: please ' .
                        'verify that the photos contain metadata, and check '
                        . 'file permissions</p>';
            }
        }  // end of loop to extract all photo exif data 
    }  // end of 'if got photos'else scandir'
    /*
     * The following section processes the album html to match entries with the
     * list of photos and obtain album supplier's urls
     */
    if (($albumHtml = file_get_contents($curlid)) !== false) {
        # each online album will have unique html.
        $icon_clr = filter_input(INPUT_POST,'icon');
        if ($icon_clr === '') {
            $icon_clr = 'Google default';
        }
        # FLICKR:
        if ($albType === 'flckr') {
            # First, find    "albumId":
            $albLoc = strpos($albumHtml, '{"albumId":"') + 12;
            $flickrInfo = substr($albumHtml, $albLoc);
            $albEnd = strpos($flickrInfo, '"');
            $albumId = substr($flickrInfo, 0, $albEnd);
            #echo "<p>Album ID: " . $albumId . '</p>';
            $alubmHtml = '';
            /*
             * There is a "_data": section which is an array of js objects,
             * defined as key:value pairs; The pattern to find is the
             * flickrModelRegistry key (see below $srchPat). There is an
             * object for each image in the album. Other keys of value to this
             * script are title, description, sizes, etc. The "sizes": key
             * identifies all the image sizes available (as an embedded object).
             * There are many image sizes available (default phone is Samsung)
             *  TYPE          KEY     IMAGE SIZE      NOTES
             * -------------------------------------------------------------
             *  Square 75     sq      75 x 75         Both phones
             *  Square 150    q       150 x 150       Both phones
             *  Thumbnail     t       100 x 60        iPhone6: 100 x 75
             *  Small 240     s       240 x 144       iPhone6: 240 x 180
             *  Small 320     n       320 x 192       iPhone6: 320 x 240
             *  Medium 500    m       500 x 300       iPhone6: 500 x 375
             *  Medium 640    z       640 x 384       iPhone6: 640 x 480
             *  Medium 800    c       800 x 480       iPhone6: 800 x 600
             *  Large 1024    l       1024 x 614      iPhone6: 1024 x 768
             *  Large 1600    h       1600 x 960      iPhone6: 1600 x 1200
             *  Large 2048    k       2048 x 1229     iPhone6: 2048 x 1536
             *  Original      o       2560 x 1536     iPhone6: 3264 x 2448
             */
            $srchPat = '{"_flickrModelRegistry":"photo-models","title":"';
            $patCnt = 0;
            $ownerIds = [];
            $Nsids = [];
            $titles = [];
            $descriptions = [];
            $allSizes = array('c', 'h', 'k', 'l', 'm', 'n', 'o', 'q', 's', 'sq', 't', 'z');
            $noOfSizes = count($allSizes);
            $c = [];
            $h = [];
            $k = [];
            $l = [];
            $m = [];
            $n = [];
            $o = [];
            $q = [];
            $s = [];
            $sq = [];
            $t = [];
            $z = [];
            #for ($j = 0; $j < $noOfImgs; $j++) {
            /* The pattern of interest is $srchPat, and there should be
             * one such pattern for each picture in $photos, no more
             * no less. This routine will scan the Flickr html and go
             * through all the $rschPat's in the file, looking for a photo
             * match.
             */
            $j = 0;
            $pmodels = strpos($flickrInfo, $srchPat) + 48;
            while ( $pmodels !== false && $pmodels !== 48 ) {
                $patCnt++;
                if ($patCnt > $noOfImgs) {
                    echo '<p style="color:red;font-size:20px;margin-left:16px;">' .
                            'WARNING: There are fewer photos in the local album '
                            . 'than are contained in the Flickr album...</p>';
                }
                $modelInfo = substr($flickrInfo, $pmodels);
                $titleEnd = strpos($modelInfo, '"');
                $titles[$j] = substr($modelInfo, 0, $titleEnd);
                # if the 'description' field does not exist, use default desc. below:
                $descPos = strpos($modelInfo, '"description":"');
                if ($descPos === false) {
                    $descriptions[$j] = 'Enter description here';
                } else {
                    $descPos += 15;
                    $descEnd = strpos($modelInfo, '"', $descPos);
                    $descLgth = $descEnd - $descPos;
                    $descriptions[$j] = substr($modelInfo, $descPos, $descLgth);
                }
                $idPos = strpos($modelInfo, '"id":"') + 6;
                $idEnd = strpos($modelInfo, '"', $idPos);
                $idLgth = $idEnd - $idPos;
                $ownerIds[$j] = substr($modelInfo, $idPos, $idLgth);
                $nsidPos = strpos($modelInfo, '"ownerNsid":"') + 13;
                $nsidEnd = strpos($modelInfo, '"', $nsidPos);
                $nsidLgth = $nsidEnd - $nsidPos;
                $Nsids[$j] = substr($modelInfo, $nsidPos, $nsidLgth);
                for ($y = 0; $y < $noOfSizes; $y++) {
                    switch ($allSizes[$y]) {
                        case 'c':
                            $c[$j] = getDat($modelInfo,'c');
                            break;
                        case 'h':
                            $h[$j] = getDat($modelInfo,'h');
                            break;
                        case 'k':
                            $k[$j] = getDat($modelInfo,'k');
                            break;
                        case 'l':
                            $l[$j] = getDat($modelInfo,'l');
                            break;
                        case 'm':
                            $m[$j] = getDat($modelInfo,'m');
                            break;
                        case 'n':
                            $n[$j] = getDat($modelInfo,'n');
                            break;
                        case 'o':
                            $o[$j] = getDat($modelInfo,'o');
                            break;
                        case 'q':
                            $q[$j] = getDat($modelInfo,'q');
                            break;
                        case 's':
                            $s[$j] = getDat($modelInfo,'s');
                            break;
                        case 'sq':
                            $sq[$j] = getDat($modelInfo,'sq');
                            break;
                        case 't':
                            $t[$j] = getDat($modelInfo,'t');
                            break;
                        case 'z':
                            $z[$j] = getDat($modelInfo,'z');
                            break;
                        default:
                            echo "UNRECOGNIZED SIZE";
                            break;
                    }  // end of switch
                }  // end of all sizes loop
                # adjust the search to the next photo-model:
                $flickrInfo = $modelInfo;
                $pmodels = strpos($flickrInfo, $srchPat) + 48;
                $j++;
            }  // end of while $srchPat exists
            if ($noOfImgs > $patCnt) {
                echo '<p style="color:red;font-size:20px;margin-left:16px;">' .
                        'WARNING: There are more photos in the local album ' .
                        'than are contained in the Flickr album.<br />' .
                        'NOTE: Extra local images will NOT appear at the '
                        . 'bottom of the page</p>';
            }
            /*
             * Create the tsv file with the above data:
             * Order is the same as original 'mkgpsv.sh' utility
             */
            # name for tsv file
            $tmpnme = str_replace(":","_",$hikeName); // O/S doesnt like ":"
            $tsvName = str_replace(" ","_",$tmpnme) . '.tsv';
            $newtsv = 'tmp/gpsv/' . $tsvName;
            if (chdir($cwdStart) === false) {
                $noreturn = '<p style="margin-left:16px;color:red;font-size:20px;"' .
                    'Could not return to build directory - contact Site Master</p>';
                die ($noreturn);
            }
            if( ($tsvfile = fopen($newtsv,"w")) === false) {
                echo "COULD NOT OPEN FILE FOR TSV OUTPUT IN tmp/gpsv";
            }    
            $header = array('folder','desc','name','Latitude','Longitude',
                'thumbnail','url','date','n-size','symbol','icon_size','color',
                'c-size','h-size','k-size','l-size','m-size','o-size',
                'q-size','s-size','sq-size','t-size','z-size');
            fputcsv($tsvfile,$header,"\t");
            # Go in the order of images appearing in the album:
            # arbitrary choice for thumbnail: 't-size'
            for ($a=0; $a<$noOfImgs; $a++) {
                for ($b=0; $b<$noOfImgs; $b++) {
                    if ($titles[$a] === $imgs[$b]) {
                        $ino = $b;
                        break;
                    }  
                }
                $gpsDateTime = $gpds[$a] . " " . convtTime($gpts[$a]);
                $plink = 'https://www.flickr.com/photos/' . $Nsids[$a] .
                    '/' . $ownerIds[$a] . '/in/album-' . $albumId;
                $outdat = array('Folder1',$titles[$a],$descriptions[$a],
                    $lats[$ino],$lngs[$ino],$n[$a],$plink,$timeStamp[$ino],
                    $n[$a],'','',$icon_clr,$c[$a],$h[$a],$k[$a],$l[$a],$m[$a],
                    $o[$a],$q[$a],$s[$a],$sq[$a],$t[$a],$z[$a],$gpsDateTime);
                fputcsv($tsvfile,$outdat,"\t");
            }
            fclose($tsvfile);
            $tsvSize = filesize($newtsv);
            $uploadedTsv = false;
        } elseif ($albType === 'apple') {
            # no code at this time
        } elseif ($albType === 'googl') {
            # no code at this time
        }
    } else {
        $noread = $pstyle . 'Could not read the album link: try re-entering '
                . 'the url or contact the Site Master</p>';
        die($noread);
    }
} else {
    $badmsg = $pstyle . 'The URL field is blank: please go back and supply ' .
            'a valid URL for the on-line photo album</p>';
    die ($badmsg);
}
?>