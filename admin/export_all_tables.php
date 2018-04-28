<?php
/** 
 * This script will export all tables automatically and download
 * them to the client machine's browser. 
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once '../mysql/dbFunctions.php';
// create array of tables to export: 
//    NOTE: due to foreign keys, EHIKES must be first
$link = connectToDb(__FILE__, __LINE__);
$tables = array('EHIKES');
$tbl_list = mysqli_query($link, "SHOW TABLES;") or die(
    __FILE__ . " Line " . __LINE__ . "Failed to get list of tables: "
    . mysqli_error($link)
);
while ($row = mysqli_fetch_row($tbl_list)) {
    if ($row[0] !== 'EHIKES') {
        array_push($tables, $row[0]);
    }
}
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($dev) {
    $mysqlUserName = USERNAME_LOC;
    $mysqlPassword = PASSWORD_LOC;
    $mysqlHostName = HOSTNAME_LOC;
    $DbName = DATABASE_LOC;
} else {
    $mysqlUserName = USERNAME_000;
    $mysqlPassword = PASSWORD_000;
    $mysqlHostName = HOSTNAME_000;
    $DbName = DATABASE_000;
}
$backup_name = "mybackup.sql";
Export_Database($mysqlHostName, $mysqlUserName, $mysqlPassword, $DbName, $tables, $backup_name = false);

function Export_Database($host, $user, $pass, $name, $tables, $backup_name = false)
{
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
