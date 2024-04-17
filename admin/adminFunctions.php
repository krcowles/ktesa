<?php
/**
 * This module contains the functions required to carry out various
 * admin tasks.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
/**
 * This function is invoked to ensure that the proper method was invoked,
 * and a result of an attempt to run the script as intended.
 * 
 * @param string $method The type of method being invoked by the caler
 * 
 * @return null;
 */
function verifyAccess($method)
{
    $msg = "Access denied to this script";
    if ($method === 'ajax') {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            return;
        } else {
            die($msg);
        }
    }
    if ($method === 'post') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return;
        } else {
            die(
                $msg . " It must be accessed via a form submit or
            appropriate XMLHttpRequest or JQuery post method"
            );
        }
    }
    if ($method === "GET") {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        } else {
            die($msg);
        }
    }
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
    $trkchildren = $track->childNodes; // DOMNodeList object
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
        for ($k=$actualPts; $k>0; $k--) {
            $next = $newseg->appendChild($pts->item($k));
        }
        $remd = $track->removeChild($segNodes[$j]);
    }
}
/**
 * This function is used in the process of exporting all tables. Note that
 * the pdo is used to gather info, but mysqli is used to create a string value
 * for writing out to the exported database file. Since this is a file write,
 * pdo is not required. For backwards compatibility, mysqli is used for this.
 * This also leaves the db as a sql-compatible file if used by the CLI.
 *
 * @param object $pdo         caller's PDO connection object
 * @param object $mysqli      caller's mysqli db connection link
 * @param string $name        As above
 * @param array  $tables      An array containg table names to export
 * @param string $dwnld       N->not a download; C->changes only; S->site dwnld
 *                            V->only the VISITORS table
 * @param bool   $backup_name Backup name, if used
 * 
 * @return null;
 */
function exportDatabase($pdo, $mysqli, $name, $tables, $dwnld, $backup_name = false)
{
    foreach ($tables as $table) {
        $tbl_data       = $pdo->query("SELECT * FROM {$table}");
        $tbl_fields     = $tbl_data->columnCount();
        $rows_num       = $tbl_data->rowCount();
        $rows           = $tbl_data->fetchAll(PDO::FETCH_NUM);
        // the essence of the CREATE TABLE statement: (tblCreate[1])
        $tblCreate      = $pdo->query('SHOW CREATE TABLE '. $table);
        $showCreate     = $tblCreate->fetch(PDO::FETCH_NUM);
        $content        = (!isset($content) ?  '' : $content) 
            . "\n\n" . $showCreate[1].";\n\n";
        $st_counter = 0;
        foreach ($rows as $row) {
            //when started (and every after 100 command cycle):
            if ($st_counter%100 == 0 || $st_counter == 0) {
                $content .= "\nINSERT INTO " . $table . " VALUES";
            }
            $content .= "\n(";
            for ($j=0; $j<$tbl_fields; $j++) {
                if (is_null($row[$j])) {
                    $content .= "NULL";
                } else {
                    $row[$j] = $mysqli->real_escape_string($row[$j]);
                    if (isset($row[$j])) {
                        $content .= "'" . $row[$j] . "'" ;
                    }
                }
                if ($j<($tbl_fields-1)) {
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
        $content .= "\n\n\n";
    }
    $backup_name = $backup_name ? $backup_name : $name.".sql";
    if ($dwnld !== 'N' && $dwnld !== 'V') {
        // save the new db to the standard data directory
        $loc = sys_get_temp_dir() . '/' . $backup_name;
        file_put_contents($loc, $content);
        if ($dwnld === 'C') {
            include 'zipArchive.php';
        } elseif ($dwnld === 'S') {
            include 'buildPhar.php';
        } else {
            throw new Exception("Unrecognized parameter in query string");
        }
    } else {
        setcookie("DownloadDisplayed", "1234", time() + 60);
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"".$backup_name."\"");
        echo $content;
        exit;
    }
}
/**
 * This function will create an array of all the tables currently
 * residing in the database. That table can then be used by the caller
 * to display the results. In the specific case where a table is
 * specified for creation (show tables precedes this), then an
 * error message is constructed noting that the tables already 
 * exists. Otherwise, this argument will be an empty string.
 * 
 * @param object $pdo   The database connection
 * @param string $table A table specified for creation
 * 
 * @return array
 */
function showTables($pdo, $table) 
{
    $tbl_list = [];
    $errmsg = '';
    $req = $pdo->query("SHOW TABLES;");
    $tables = $req->fetchALL(PDO::FETCH_NUM);
    foreach ($tables as $row) {
        if ($row[0] === $table) {
            $errmsg .= "You must first DROP {$table}";
        } else {
            array_push($tbl_list, $row[0]); 
        }
    }
    return array($tbl_list, $errmsg);
}
/**
 * This function will list the contents (fields) of the specified
 * table. An array will be constructed whose elements are each
 * an array of cells to appear in the displayed table.
 * 
 * @param object $pdo   the database connection
 * @param string $table the table to be described
 * 
 * @return array
 */
function describeTable($pdo, $table) 
{
    $rows = [];
    $cells = [];
    $desc = $pdo->query("DESCRIBE {$table};");
    $list = $desc->fetchALL(PDO::FETCH_NUM);
    foreach ($list as $row) {
        for ($i=0; $i<count($row); $i++) {
            array_push($cells, $row[$i]);
        }
        array_push($rows, $cells);
        $cells = [];
    }
    return $rows;
}
/**
 * Update the count of failures in the LOCKS table when failed login
 * attempts are encountered.
 * 
 * @param integer $noOfFailures The current number of failed attempts for this user
 * @param string  $ipAddr       The ip address of this user
 * @param PDO     $pdo          Database connection object
 * 
 * @return null
 */
function updateFailures($noOfFailures, $ipAddr, $pdo)
{
    if ($noOfFailures >= 3) {
        $latest = new DateTime("now", new DateTimeZone('America/Denver'));
        $latest->modify('+ 1 hour'); // current wait time = 1 hour
        $unlock =  $latest->format('Y-m-d H:i:s'); // MySQL DATETIME requires string
        $updateReq = "UPDATE `LOCKS` SET `fails`=?, `lockout`=? WHERE `ipaddr`=?;";
        $update = $pdo->prepare($updateReq);
        $update->execute([$noOfFailures, $unlock, $ipAddr]);
    } else {
        $updateReq = "UPDATE `LOCKS` SET `fails`=? WHERE `ipaddr`=?;";
        $update = $pdo->prepare($updateReq);
        $update->execute([$noOfFailures, $ipAddr]);
    }
    return;
}
/**
 * Eliminate LOCKS or entries in LOCKS based on number of entries
 * 
 * @param integer $count The number of entries in the LOCKS table
 * @param string  $ipadd The subject ip address in the LOCKS table
 * @param PDO     $pdo   The connection object for the database
 * 
 * @return null
 */
function reduceLocks($count, $ipadd, $pdo)
{
    if ($count === 1) {
        $pdo->query("DROP TABLE `LOCKS`;");
    } else {
        $dropLocks = $pdo->prepare(
            "DELETE FROM `LOCKS` WHERE `ipaddr`=?;"
        );
        $dropLocks->execute([$ipadd]);
    }
}
/**
 * A function to id the visitors browser type.
 * NOTE: this code did not pass certain use cases as copied from
 * https://stackoverflow.com/questions/2199793/php-get-the-browser-name
 * and has thus been modified to improve rigor
 * 
 * @return string browser type
 */
function getBrowserType()
{
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        return array(
            'userAgent' => 'NOT SET',
            'name'      => 'unknown',
            'version'   => 'unknown',
            'platform'  => 'unknown',
            'pattern'   => 'unknown'
        );
    } else {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
    }
    if (!empty($u_agent)) {
        $bname    = 'Unknown';
        $platform = 'Unknown';
        $version  = "";
        $ub       = "Unknown";

        //First get the platform
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }
        // Next get the name of the user agent (yes seperately and for good reason)
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } 

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i > 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {   
                if (isset($matches['version'][0])) {
                    $version= $matches['version'][0];
                }
            } else {
                if (isset($matches['version'][1])) {
                    $version= $matches['version'][1];
                }
            }
        } else { // 0 or 1 ??
            if (isset($matches['version'][0])) {
                $version= $matches['version'][0];
            }
        }
        
        // check if we have a number [Default was set to ""]
        if ($version == "") {
            $version = "?";
        }
    } else {
        $u_agent  = "Not found";
        $bname    = "Not found";
        $version  = "?";
        $platform = "Not found";
        $pattern   = "No Pattern";
    }
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'   => $pattern
    );
}
/**
 * Discover which page is being visited by the visitor
 * 
 * @return string page url
 */
function selfURL()
{ 
    $s = (empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on")) ? "s" : "";
    $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/") . $s; 
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 
    return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI']; 
}
/**
 * A function to get page access protocol
 * 
 * @param string $s1 server protocol
 * @param string $s2 slash character
 * 
 * @return string info
 */
function strleft($s1, $s2)
{
    return substr($s1, 0, strpos($s1, $s2)); 
}
/**
 * This function will compare two arrays - if they are different,
 * the difference is reported.
 * 
 * @param array $a1 an array to compare
 * @param array $a2 an array to compare: this is compared to $a1 for missing files
 * 
 * @return array an array containing the difference between the two
 */
function arrdiff($a1, $a2)
{
    $res = array();
    foreach ($a2 as $a) {
        if (array_search($a, $a1) === false) {
            $res[] = $a;
        }
    }
    return $res;
}
