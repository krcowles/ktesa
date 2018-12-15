<?php
/**
 * This file should be included wherever database access is required.
 * Multiple functions provide access capability with error reporting.
 * 
 * @package Database_Acess
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
/**
 * This is the default exception handler, when thrown exceptions are not
 * otherwise caught.
 * 
 * @param object $exception 'Throwable' type Exception object
 */
function default_exceptions($exception) {
    echo "The routine has encountered an error: "
        . $exception->getMessage() . PHP_EOL;
}
/**
 * This function utilizes the PDO database access management class and establishes
 * the database connection using charset="utf8". It will indicate file and line no.
 * within the caller's file. Note that without a catch block (or implicit
 * default exception handler [see above]), the zend engine will terminate the script
 * (and display a back trace). This website uses only one dsn which is included
 * within this function.
 * 
 * @param string $src_file The source file from which the routine is called
 * @param string $src_line The line in the caller's source file
 * 
 * @return object $pdo PDO class object with connection established
 */
function dbConnect($src_file, $src_line) {
    // establish localized mode settings for the secure settings file:
    require "../admin/mode_settings.php";  // always relative to the code version
    require_once $_SERVER["DOCUMENT_ROOT"] . "/../settings.php";
    // ERRMODE_EXCEPTION: throws a PDOException, besides setting error code
    $options = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    );
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',$HOSTNAME, $DATABASE, $CHARSET);
    try {
        $pdo_obj = new PDO( $dsn, $USERNAME, $PASSWORD, $options);
    }
    catch (\PDOException $e) {
        echo "A problem was encountered connecting to the database: " 
            . $e->getMessage() . " with error code: " . (int)$e->getCode();
        echo "<br />The caller was " . $src_file . " at line " . $src_line;
        echo "<br />The error occurred in " . $e->getFile() . " at line " . $e->getLine() . " <br />";
        throw new Exception("Failure to connect to db");
    }
    return $pdo_obj;
}
/**
 * This function is intended to insert data into the specified table
 * 
 * @param object $link  The database connection
 * @param string $table The table into which data will be inserted
 * @param string $file  The caller's filename
 * @param string $line  The line in the caller's routine from which called
 * 
 * @return integer $index[0] The value of the primary key added
 */
function insertDbRow($link, $table, $file, $line)
{
    $insQuery = "INSERT INTO {$table} () VALUES ();";
    $insResults = mysqli_query($link, $insQuery);
    if (!$insResults) {
        echo "insQuery: {$insQuery} <br>";
        die(
            "Function insertDbRow failed to insert into table: " .
            "{$table} when called from file {$file}: line: {$line}: " . 
            mysqli_error($link)
        );
    }
    mysqli_free_result($insResults);
    // Get index number
    $selQuery = "SELECT * FROM {$table} ORDER BY 1 DESC LIMIT 1;";
    $selResults = mysqli_query($link, $selQuery);
    $index = mysqli_fetch_row($selResults);
    if (!$selResults) {
        echo "selQuery: {$selQuery} <br>";
        die(
            "Function insertDbRow failed to get index from table: " .
            "{$table} when called from file {$file}: line: {$line}: " .
            mysqli_error($link)
        );
    }
    mysqli_free_result($selResults);
    return $index[0];
}
/**
 * This function executes any query not reguiring a return value
 * 
 * @param object $link  The database connection
 * @param string $query The query to be executed
 * @param string $file  The caller's filename
 * @param string $line  The line in the caller's routine from which called
 * 
 * @return null
 */
function doQuery($link, $query, $file, $line)
{
    $results = mysqli_query($link, $query) or die(
        "Function doQuery failed when called " .
        "from file {$file}: line: {$line} with query : {$query} <br> " .
        mysqli_error($link)
    );

}
/**
 * This function retrieves the last row number in a table
 * 
 * @param object $link  The database connection
 * @param string $table The table to be queried
 * @param string $file  The caller's filename
 * @param string $line  The line in the caller's routine from which called
 * 
 * @return integer $newrow The 
 */
function getDbRowNum($link, $table, $file, $line)
{
    $query = "SELECT * FROM {$table} ORDER BY 1 DESC LIMIT 1;";
    $results = mysqli_query($link, $query) or die(
        "Function getDbRowNum failed when called " .
        "from file {$file}: line: {$line} with query : {$query} <br> " .
        mysqli_error($link)
    );
    $newRow = mysqli_fetch_row($results);
    mysqli_free_result($results);
    return $newRow[0];
}
/**
 * This function returns the number of rows retrieved from a query
 * 
 * @param object $link  The database connection
 * @param string $query The query to be executed
 * @param string $file  The caller's filename
 * @param string $line  The line in the caller's routine from which called
 * 
 * @return integer $rowCount The number of rows in the query result
 */
function getDbRowCount($link, $query, $file, $line)
{
    $results = mysqli_query($link, $query) or die(
        "Function getDbRowCount failed when called " .
        "from file {$file}: line: {$line} with query : {$query} <br> " .
        mysqli_error($link)
    );
    $rowCount = mysqli_num_rows($results);
    mysqli_free_result($results);
    return $rowCount;
}
/** 
 * This function is designed to update a single field in the specified
 * table while preserving the null value
 * 
 * @param object $link    The database connection
 * @param string $table   The table to be updated
 * @param string $row     The row id to be updated
 * @param string $field   The field of the row to be updated
 * @param string $indexId The value of the key to search for $row id
 * @param mixed  $content A value which may be null
 * @param string $file    The caller's filename
 * @param string $line    The line in the caller's routine from which called
 * 
 * @return null
 */
function updateDbRow(
    $link, $table, $row, $field, $indexId, $content, $file, $line
) {
    if (is_null($content)) {
        $query = "UPDATE {$table} SET {$field} = NULL WHERE {$indexId} = {$row};";
    } else {
        $content_e = mysqli_real_escape_string($link, $content);
        $query = "UPDATE {$table} SET {$field} = '{$content_e}' " .
            "WHERE {$indexId} = '{$row}';";
    }
    $result = mysqli_query($link, $query);
    if (!$result) {
        echo 'query: ' . $query . '<br>';
        die(
            "Function updateDbRow failed on table: {$table} when called " .
            "from file {$file}: line: {$line}: " . mysqli_error($link)
        );
    }
}
/**
 * This function establishes the connection to the database
 * 
 * @param string $file The caller's filename
 * @param string $line The line in the caller's routine from which called
 * 
 * @return object $link The connection handle
 */
function connectToDb($file, $line)
{
    include_once "../mysql/setenv.php";
    $link = mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE);
    if (!$link) {
        die(
            "Function connectToDb failed when called from file {$file}: " .
            "line: {$line}: " . mysqli_connect_error($link)
        );
    }
    include "../admin/set_sql_mode.php";
    if (!mysqli_set_charset($link, "utf8")) {
        die(
            "Function mysqli_set_charset failed when called from file {$file}: " .
            "line: {$line}: " . mysqli_error($link)
        );
    }
    doQuery($link, 'SET NAMES utf8', __FILE__, __LINE__);
    return $link;
}
