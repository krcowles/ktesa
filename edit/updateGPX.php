<?php
/**
 * This script will 'keep pace' with changes made by the user
 * while editing the gpx file. The edit-in-process gpxfile will be
 * over-written by the changes herein.
 * PHP Version 7.4
 * 
 * @package Ktesa,
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
verifyAccess('ajax');
$gpxfname = 'usergpx.gpx';
$xmlfile = simplexml_load_file($gpxfname);
$type    = filter_input(INPUT_POST, 'type');
$track   = filter_input(INPUT_POST, 'trk', FILTER_VALIDATE_INT);
// A track may have more than 1 <trkseg>
$seg_lgths = [];
$no_of_segs = $xmlfile->trk[$track]->trkseg->count();
for ($k=0; $k<$no_of_segs; $k++) {
    $lgth = $xmlfile->trk[$track]->trkseg[$k]->trkpt->count();
    array_push($seg_lgths, $lgth);
}
$seg_lims = [];
$seg = [];
$next = 0;
for ($i=0; $i<$no_of_segs; $i++) {
    $seg['min'] = $next;
    $seg['max'] = $next + $seg_lgths[$i] -1;
    array_push($seg_lims, $seg);
    $next += $seg_lgths[$i];
}

if ($type === 'del') { // the most complex scenario...
    $del_strt = filter_input(INPUT_POST, 'first', FILTER_VALIDATE_INT);
    $del_end  = filter_input(INPUT_POST, 'last', FILTER_VALIDATE_INT);
    if ($no_of_segs > 1) {
        // find which trkseg(s) contain start and end of deletions
        for ($j=0; $j<$no_of_segs; $j++) {
            if ($del_strt >= $seg_lims[$j]['min'] 
                && $del_strt <= $seg_lims[$j]['max']
            ) {
                $start_seg = $j;
            }
            if ($del_end >= $seg_lims[$j]['min']
                && $del_end <= $seg_lims[$j]['max']
            ) {
                $end_seg = $j;
                break;
            }
        }
        // map delpts to trkseg ranges
        if ($start_seg !== $end_seg) {
            /**
             * This situation calls for multiple delete loops, each with
             * a 'start' and 'end' relative to the segment's size. Starting
             * with the '$del_end' segment's array, define the array's 
             * corresponding 'end', then work backwards to the 0th element.
             * The 0th element is, by definition, the 'start' of the deletions
             * in this array, and all elements from 0 to 'end' will be removed.
             * The 'end' of the previous array will be, by definition, that
             * array's last element. Determine if the 'start' lies within
             * this array, or in the preceding array. If in the preceding array,
             * remove the entire <trkseg>. If not in the preceding array, find
             * the 'start' that corresponds to $del_strt and work backwards to 
             * that point.
             */
            // In which segment does $del_end reside, and what is it's index?
            $deltrack = false; 
            for ($a=0; $a<$no_of_segs; $a++) {
                if ($del_end >= $seg_lims[$a]['min']
                    && $del_end <= $seg_lims[$a]['max']
                ) {
                    $pts = $xmlfile->trk[$track]->trkseg[$a]->children();
                    $curr_end = $del_end - $seg_lims[$a]['min'];
                    if (count($pts) === $curr_end + 1) {
                        $deltrack = true;
                        unset($xmlfile->trk[$track]->trkseg[$end_seg]);
                    }   
                    break;
                }
            }
            if (!$deltrack) {
                // eliminate backwards to 0th element
                for ($b=$curr_end; $b>=0; $b--) {
                    unset($pts[$b]);
                }
            }
            // In which segment does $del_strt reside and what is it's index?
            for ($c=0; $c<$no_of_segs; $c++) {
                if ($del_strt >= $seg_lims[$c]['min']
                    && $del_strt <= $seg_lims[$c]['max']
                ) {
                    $trackseg = $xmlfile->trk[$track]->trkseg[$c];
                    $pts = $trackseg->children();
                    $curr_strt = $del_strt - $seg_lims[$c]['min'];
                    $curr_end  = count($pts) - 1;
                    if ($c < $end_seg - 1) {
                        // eliminte tweener segs
                        for ($z=$end_seg-1; $z>$c; $z--) {
                            unset($xmlfile->trk[$track]->trkseg[$z]);
                        }
                    }
                    break;
                }
            }
            for ($d=$curr_end; $d>=$curr_strt; $d--) {
                unset($pts[$d]);
            }
            // presumably, not ALL points are being deleted! 
        } else {
            // need only one delete loop
            $trkseg = $xmlfile->trk[$track]->trkseg[$start_seg];
            $sgl_array = $trkseg->children();
            $curr_end  = $del_end - $seg_lims[$start_seg]['min'];
            $curr_strt = $del_strt - $seg_lims[$start_seg]['min'];
            if (($curr_end - $curr_strt + 1) === count($sgl_array)) {
                unset($xmlfile->trk[$track]->trkseg[$end_seg]);
            } else {
                for ($m=$curr_end; $m>=$curr_strt; $m--) {
                    unset($sgl_array[$m]);
                }
            }
        }
    } else { // common case, only 1 <trkseg>: assume not all deleted!
        $xmlarray = $xmlfile->trk[$track]->trkseg->children();
        for ($j=$del_end; $j>=$del_strt; $j--) {
            unset($xmlarray[$j]);
        }
    }
} elseif ($type === 'undo') {
    /**
     * At this point, restoring points does not include a method for
     * returning points to the same trkseg. They well be re-added to
     * the trkseg at the point before which the undos start.
     */
    $insert_start  = filter_input(INPUT_POST, 'start', FILTER_VALIDATE_INT);
    $insert_points = json_decode($_POST['undos']);
    // Which track segment holds the point before which the undo starts:
    $total_pts = 0;
    for ($j=0; $j<$no_of_segs; $j++) {
        $segpts = $xmlfile->trk[$track]->trkseg[$j]->trkpt->count();
        $total_pts += $segpts;
        if ($insert_start <= $total_pts) {
            // Simplexml has no provisions for ordering elements, so use DOMDocument
            $dom = new DOMDocument(1.0);  // DOMDocument Class
            $dom->load($gpxfname);
            $dom_trks = $dom->getElementsByTagName('trk'); // DOMNodeList
            $dom_trk  = $dom_trks->item($track); // DOMElement
            $trkchildren = $dom_trk->childNodes; // DOMNodeList
            $segno = 0;
            $segNodes = []; // Array of DOMElements
            // Eliminate non-trkseg data & collect trksegs in $segNodes
            foreach ($trkchildren as $trkchild) {
                if ($trkchild->nodeName === 'trkseg') {
                    $segNodes[$segno] = $trkchild;
                    $segno ++;
                }
            }
            // Find starting point in trksegs
            $seg_pt_cnt = 0;
            $prior_cnt  = 0;
            $position   = 0;
            for ($i=0; $i<count($segNodes); $i++) {
                $allpts = $segNodes[$i]->childNodes;
                // extract trkpts; there are text nodes etc mixed in
                $trackpts = [];
                foreach ($allpts as $pt) {
                    if ($pt->nodeName === 'trkpt') {
                        array_push($trackpts, $pt); // DOMElement
                    }
                }
                $prior_cnt += $seg_pt_cnt;
                $seg_pt_cnt += count($trackpts);
                if ($insert_start < $seg_pt_cnt) {
                    // trkpts & $trackpts have different indices
                    $position = $insert_start - $prior_cnt; 
                    break;
                }
            }
            
            foreach ($insert_points as $pt) {
                $insertPt = $trackpts[$position];
                $added_pt = $dom->createElement('trkpt');
                $added_pt->setAttribute('lat', $pt->lat);
                $added_pt->setAttribute('lon', $pt->lng);
                $insertPt->parentNode->insertBefore($added_pt, $insertPt);
            }
            $dom->save($gpxfname);
            exit;
        } 
    }
} else {
    $trk_no = filter_input(INPUT_POST, 'trk', FILTER_VALIDATE_INT);
    $pt_no  = filter_input(INPUT_POST, 'pt', FILTER_VALIDATE_INT);
    $newlat = filter_input(INPUT_POST, 'lat', FILTER_VALIDATE_FLOAT);
    $newlng = filter_input(INPUT_POST, 'lng', FILTER_VALIDATE_FLOAT);
    if ($type === 'mod') { // simplest case
        for ($p=0; $p<$no_of_segs; $p++) {
            if ($pt_no >= $seg_lims[$p]['min'] 
                && $pt_no <= $seg_lims[$p]['max']
            ) {
                $seg_pt = $pt_no - $seg_lims[$p]['min'];
                $xmlfile->trk[$trk_no]->trkseg[$p]->trkpt[$seg_pt]['lat'] = $newlat;
                $xmlfile->trk[$trk_no]->trkseg[$p]->trkpt[$seg_pt]['lon'] = $newlng;
            }
        }
    } elseif ($type === 'add') {
        /**
         * The new point will remain in the trkseg in which it occurred;
         * Simplexml has no provisions for ordering elements, so use DOMDocument
         */
        for ($t=0; $t<$no_of_segs; $t++) {
            if ($pt_no >= $seg_lims[$t]['min'] 
                && $pt_no <= $seg_lims[$t]['max']
            ) {
                $seg_pt = $pt_no - $seg_lims[$t]['min'];
                $dom = new DOMDocument(1.0);  // DOMDocument Class
                $dom->load($gpxfname);
                $added_pt = $dom->createElement('trkpt');
                $added_pt->setAttribute('lat', $newlat);
                $added_pt->setAttribute('lon', $newlng);
                $tracks = $dom->getElementsByTagName('trk'); // DOMNodeList
                $track = $tracks->item($trk_no); // DOMElement
                $trkchildren = $track->childNodes; // DOMNodeList
                $segno = 0;
                $segNodes = []; // Array of DOMElements
                // Eliminate non-trkseg data
                foreach ($trkchildren as $trkchild) {
                    if ($trkchild->nodeName === 'trkseg') {
                        $segNodes[$segno] = $trkchild;
                        $segno ++;
                    }
                }
                $pts = $segNodes[$t]->childNodes; // DOMNodeList w/subnodes 
                $trackpts = [];
                foreach ($pts as $pt) {
                    if ($pt->nodeName === 'trkpt') {
                        array_push($trackpts, $pt); // DOMElement
                    }
                }
                for ($k=0; $k<count($trackpts); $k++) {
                    $item = $trackpts[$k]; // DOMElement
                    if ($k === $pt_no) {
                        $item->parentNode->insertBefore($added_pt, $item);
                        break;
                    }
                }
                $dom->save($gpxfname);
                exit;
            }
        }
    } else {
        throw new Exception("Unrecognized change type");
    }
}
file_put_contents($gpxfname, $xmlfile->asXML());
