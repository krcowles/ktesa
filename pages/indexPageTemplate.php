<?php
    $hikeIndexNo = filter_input(INPUT_GET,'hikeIndx');
    $datatable = '../data/database.xml';
    $tabledat = simplexml_load_file($datatable);
    if ($tabledat === false) {
        die ("Could not load database.xml as simplexml");
    }
    foreach ($tabledat->row as $page) {
        if ($hikeIndexNo == $page->indxNo) {  // find the target hike
            $indxTitle = $page->pgTitle;
            $lnkText = str_replace('Index','',$indxTitle);
            $parkMap = $page->aoimg1;
            $parkDirs = $page->dirs;
            $parkInfo = $page->hikeInfo;
            $obj = $page->refs;
            $htmlout = '<ul id="refs">';
            foreach ($obj->ref as $item) {
                $tagType = $item->rtype->__toString();
                if ($tagType === 'b') { 
                    $htmlout .= '<li>Book: <em>' . $item->rit1 . '</em>' . $item->rit2 . '</li>';
                } elseif ($tagType === 'p') {
                    $htmlout .= '<li>Photo Essay: <em>' . $item->rit1 . '</em>' . $item->rit2 . '</li>';
                } elseif ($tagType === 'n') {
                    $htmlout .= '<li>' . $item->rit1 . '</li>';
                } else {
                    if ($tagType === 'w') {
                        $tag = '<li>Website: ';
                    } elseif ($tagType === 'a') {
                        $tag = '<li>App: ';
                    } elseif ($tagType === 'd') {
                        $tag = '<li>Downloadable Doc: ';
                    } elseif ($tagType === 'h') {
                        $tag = '<li>';
                    } elseif ($tagType === 'l') {
                        $tag = '<li>Blog: ';
                    } elseif ($tagType === 'r') {
                        $tag = '<li>Related Site: ';
                    } elseif ($tagType === 'o') {
                        $tag = '<li>Map: ';
                    } elseif ($tagType === 'm') {
                        $tag = '<li>Magazine: ';
                    } elseif ($tagType === 's') {
                        $tag = '<li>News article: ';
                    } elseif ($tagType === 'g') {
                        $tag = '<li>Meetup Group: ';
                    } else {
                        $tag = '<li>CHECK DATABASE: ';
                    }
                    $htmlout .= $tag . '<a href="' . $item->rit1 . '" target="_blank">' .
                        $item->rit2 . '</a></li>';
                }
            } // end of foreach
            $htmlout .= '</ul>' . "\n";
            # INDEX TABLE OF HIKES, if any:
            $indxTbl = 0;  # if no table elements are present, default msg shows
            # table header:
            $tblhtml = '<table id="siteIndx">' . "\n" . '<thead>' . "\n" . '<tr>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Trail</th>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Web Pg</th>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Trail Length</th>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Elevation</th>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Exposure</th>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Photos</th>'  . "\n";
            $tblhtml .= '</tr>' . "\n" . '</thead>' . "\n" . '<tbody>' . "\n";
            foreach ($page->content->tblRow as $tdat) {
                # Exposure settings:
                if ($tdat->tdexp == 'Sunny') {
                    $exposure = '../images/sun.jpg';
                } elseif ($tdat->tdexp == 'Partial') {
                    $exposure = '../images/greenshade.jpg';
                } elseif ($tdat->tdexp == 'Shady') {
                    $exposure = '../images/shady.png';
                } elseif ($tdat->tdexp == 'X') {
                    $exposure = '';
                }
                $hiked = ($tdat->compl == 'Y') ? true : false;
                if ($hiked) {
                    $tblhtml .= '<tr>' . "\n" . '<td>' . $tdat->tdname .
                        '</td>' . "\n";
                    $tblhtml .= '<td><a href="hikePageTemplate.php?hikeIndx=' .
                        $tdat->tdpg . '" target="_blank">' . "\n" . 
                        '<img class="webShift" src="../images/greencheck.jpg"' .
                        ' alt="website click-on icon" /></a></td>' . "\n";
                    $tblhtml .= '<td>' . $tdat->tdmiles . ' miles</td>' . "\n";
                    $tblhtml .= '<td>' . $tdat->tdft . ' ft</td>' . "\n";
                    $tblhtml .= '<td><img class="expShift" src="' .
                        $exposure . '" alt="exposure icon" /></td>' . "\n";
                    $tblhtml .= '<td><a href="' . $tdat->tdalb .
                        '" target="_blank">' . "\n" . '<img class="flckrShift" ' .
                        'src="../images/album_lnk.png" alt="Photos symbol" />' .
                        '</a></td>' . "\n";
                        $tblhtml .= '</tr>' . "\n";
                } else {  # not hiked yet
                    $tblhtml .= '<tr>' . "\n" . '<td>' . $tdat->tdname . 
                        '</td>' . "\n";
                    $tblhtml .= '<td><img class="webShift" ' .
                        'src="../images/x-box.png" alt="box with x" />' .
                        '</td>' . "\n";
                    $miles = $tdat->tdmiles;
                    if (strlen($miles) === 0) {
                        $miles = '?';
                    }
                    $tblhtml .= '<td>' . $miles . ' miles</td>' . "\n";
                    $feet = $tdat->tdft;
                    if (strlen($feet) === 0) {
                        $feet = '?';
                    }
                    $tblhtml .= '<td>' . $feet . ' ft</td>' . "\n";
                    $tblhtml .= '<td class="naShift">N/A</td>' . "\n";
                    $tblhtml .= '<td><img class="flckrShift" ' .
                        'src="../images/x-box.png" alt="box with x" /></td>' . "\n";
                    $tblhtml .= '</tr>' . "\n";
                }
                $indxTbl++;
            }  # end of foreach $tdat
            $tblhtml .= '</tbody>' . "\n" . '</table>' . "\n";    
            break;
        }  # if target hike indx no
    }  # end of 'foreach $page'
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $indxTitle;?></title>
    <meta charset="utf-8" />
    <meta name="language"
                    content="EN" />
    <meta name="description"
            content="Details about the {$hikeTitle} hike" />
    <meta name="author"
            content="Tom Sandberg and Ken Cowles" />
    <meta name="robots"
            content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="../styles/subindx.css" type="text/css" rel="stylesheet" />
</head>

<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>

    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail"><?php echo $indxTitle;?></p>

<img class="mainPic" src="<?php echo '../images/' . $parkMap;?>" alt="Park Service Map" />
<p id="dirs"><a href="<?php echo $parkDirs;?>" target="_blank">
    Directions to the <?php echo $lnkText;?></a></p>
<?php
    echo '<p id="indxContent">' . $parkInfo . '</p>' . "\n";
    echo '<fieldset><legend id="fldrefs">References &amp; Links</legend>';
    echo $htmlout . '</fieldset>' . "\n";
?>
<div id="hdrContainer">
<p id="tblHdr">Hiking & Walking Opportunities at <?php echo $lnkText;?>:</p>
</div>
<div>
<?php 
    if ($indxTbl !== '') {
        echo $tblhtml;
    } else {
        echo '<p style="text-align:center;">No hikes yet associated with this park</p>';
        echo '<p style="margin-left:16px;">Total no. of hikes read from tblRow: ' . $i . '</p>';
    }
?>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>

</body>

</html>
