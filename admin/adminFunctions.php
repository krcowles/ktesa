<?php
/**
 * This module contains the functions required to carry out various
 * admin tasks.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
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
/**
 * This function is used in the process of exporting all tables.
 * 
 * @param string $host        Specify host to use based on invoker
 * @param string $user        As above
 * @param string $pass        As above
 * @param string $name        As above
 * @param array  $tables      An array containg table names to export
 * @param bool   $backup_name Backup name, if used
 * 
 * @return null;
 */
function exportDatabase(
    $host, $user, $pass, $name, $tables, $backup_name = false
) {
    $mysqli = new mysqli($host, $user, $pass, $name);
    $mysqli->select_db($name);
    $mysqli->query("SET NAMES 'utf8'");
    foreach ($tables as $table) {
        $result         = $mysqli->query('SELECT * FROM '. $table);
        $fields_amount  = $result->field_count;
        $rows_num       = $mysqli->affected_rows;
        $res            = $mysqli->query('SHOW CREATE TABLE '. $table);
        $TableMLine     = $res->fetch_row();
        $content        = (!isset($content) ?  '' : $content) 
            . "\n\n" . $TableMLine[1].";\n\n";
        for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter=0) {
            while ($row = $result->fetch_row()) {
                //when started (and every after 100 command cycle):
                if ($st_counter%100 == 0 || $st_counter == 0) {
                    $content .= "\nINSERT INTO " . $table . " VALUES";
                }
                $content .= "\n(";
                for ($j=0; $j<$fields_amount; $j++) {
                    if (is_null($row[$j])) {
                        $content .= "NULL";
                    } else {
                        $row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
                        if (isset($row[$j])) {
                            $content .= "'" . $row[$j] . "'" ;
                        }
                    }
                    if ($j<($fields_amount-1)) {
                        $content.= ',';
                    }
                }
                $content .=")";
                //every after 100 command cycle [or at last line] 
                //  ...p.s. but should be inserted 1 cycle eariler
                if ((($st_counter+1)%100 == 0 && $st_counter != 0) 
                    || $st_counter+1==$rows_num
                ) {
                    $content .= ";";
                } else {
                    $content .= ",";
                }
                $st_counter = $st_counter + 1;
            }
        } $content .= "\n\n\n";
    }
    $backup_name = $backup_name ? $backup_name : $name.".sql";
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"".$backup_name."\"");
    echo $content;
    exit;
}
