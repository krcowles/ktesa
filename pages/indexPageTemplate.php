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
            $refStr = $page->refs;
            /* Convert string array into real references */
            $list = explode("^",$refStr);
            $noOfItems = intval($list[0]);
            array_shift($list);
            $nxt = 0;
            $htmlout = '<ul id="refs">' . "\n";
            for ($k=0; $k<$noOfItems; $k++) {
                $tagType = $list[$nxt];
                if ($tagType === 'b') { 
                    $htmlout .= '<li>Book: <em>' . $list[$nxt+1] . '</em>' . $list[$nxt+2] . '</li>' ."\n";
                    $nxt += 3;
                } elseif ($tagType === 'p') {
                    $htmlout .= '<li>Photo Essay: <em>' . $list[$nxt+1] . '</em>' . $list[$nxt+2] . '</li>' . "\n";
                    $nxt += 3;
                } elseif ($tagType === 'n') {
                    $htmlout .= '<li>' . $list[$nxt+1] . '</li>' . "\n";
                    $nxt += 2;
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
                    $htmlout .= $tag . '<a href="' . $list[$nxt+1] . 
                        '" target="_blank">' . $list[$nxt+2] .
                        '</a></li>' . "\n";
                    $nxt += 3;
                }
            } // end of for loop in references
            $htmlout .= '</ul>' . "\n";
            # INDEX TABLE OF HIKES, if any:
            $indxTbl = 0;  # if no table elements are present, default msg shows
            $tblhtml = '<table id="siteIndx">' . "\n" . '<thead>' . "\n" . '<tr>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Trail</th>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Web Pg</th>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Trail Length</th>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Elevation</th>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Exposure</th>' . "\n";
            $tblhtml .= '<th class="hdrRow" scope="col">Photos</th>'  . "\n";
            $tblhtml .= '</tr>' . "\n" . '</thead>' . "\n" . '<tbody>' . "\n";
            foreach ($page->content->tblRow as $tdat) {
                $row = explode("^",$tdat);
                # there are always 7 pieces, counting row type
                if ($row[0] === 'n') {  // "normal" - not grayed out
                    $tblhtml .= '<tr>' . "\n" . '<td>' . $row[1] .
                        '</td>' . "\n";
                    $tblhtml .= '<td><a href="' . $row[2] .
                        '" target="_blank">' . "\n" . '<img class="webShift" ' .
                        'src="../images/greencheck.jpg" alt="checkbox" />' .
                        '</a></td>' . "\n";
                    $tblhtml .= '<td>' . $row[3] . '</td>' . "\n";
                    $tblhtml .= '<td>' . $row[4] . '</td>' . "\n";
                    $tblhtml .= '<td><img class="expShift" src="' .
                        $row[5] . '" alt="exposure icon" /></td>' . "\n";
                    $tblhtml .= '<td><a href="' . $row[6] .
                        '" target="_blank">' . "\n" . '<img class="flckrShift" ' .
                        'src="../images/album_lnk.png" alt="Photos symbol" />' .
                        '</a></td>' . "\n";
                        $tblhtml .= '</tr>' . "\n";
                } else {   // $row[0]=g  - grayed out row
                        $tblhtml .= '<tr>' . "\n" . '<td>' . $row[1] . 
                            '</td>' . "\n";
                        $tblhtml .= '<td><img class="webShift" ' .
                            'src="../images/x-box.png" alt="box with x" />' .
                            '</td>' . "\n";
                        $tblhtml .= '<td>' . $row[3] . '</td>' . "\n";
                        $tblhtml .= '<td>' . $row[4] . '</td>' . "\n";
                        $tblhtml .= '<td class="naShift">N/A</td>' . "\n";
                        $tblhtml .= '<td><img class="flckrShift" ' .
                            'src="../images/x-box.png" alt="box with x" /></td>' . "\n";
                        $tblhtml .= '</tr>' . "\n";
                }  # end of if row is grayed out...
                $indxTbl++;
            }  # of for each tblRow in xml file	
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
