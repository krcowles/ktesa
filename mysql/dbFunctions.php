<?php
function insertDbRow($link, $table, $file, $line)
{
    $insQuery = "INSERT INTO {$table} () VALUES ();";
    $insResults = mysqli_query($link, $insQuery);
    if (!$insResults) {
        echo "insQuery: {$insQuery} <br>";
        die("Function insertDbRow failed to insert into table: {$table} when called from file {$file}: line: {$line}: "
        . mysqli_error($link));
    }
    mysqli_free_result($insResults);

    # Get index number
    $selQuery = "SELECT * FROM {$table} ORDER BY 1 DESC LIMIT 1;";
    $selResults = mysqli_query($link, $selQuery);
    $index = mysqli_fetch_row($selResults);
    if (!$selResults) {
        echo "selQuery: {$selQuery} <br>";
        die("Function insertDbRow failed to get index from table: {$table} when called from file {$file}: line: {$line}: "
        . mysqli_error($link));
    }
    mysqli_free_result($selResults);
    return $index[0];
}
function doQuery($link, $query, $file, $line)
{
    $results = mysqli_query($link, $query);
    if (!$results) {
        die("Function doQuery failed when called " .
        "from file {$file}: line: {$line} " .
        "with query : {$query} <br> " .
        mysqli_error($link));
    }
    mysqli_free_result($results);
}
#
function getDbRowNum($link, $table, $file, $line)
{
    # Get index number
    $query = "SELECT * FROM {$table} ORDER BY 1 DESC LIMIT 1;";
    $results = mysqli_query($link, $query);
    if (!$results) {
        die("Function getDbRowNum failed when called " .
        "from file {$file}: line: {$line} " .
        "with query : {$query} <br> " .
        mysqli_error($link));
    }
    $newRow = mysqli_fetch_row($results);
    mysqli_free_result($results);
    return $newRow[0];
}
#
function getDbRowCount($link, $query, $file, $line)
{
    $results = mysqli_query($link, $query);
    if (!$results) {
        die("Function getDbRowCount failed when called " .
        "from file {$file}: line: {$line} " .
        "with query : {$query} <br> " .
        mysqli_error($link));
    }
    $rowCount = mysqli_num_rows($results);
    mysqli_free_result($results);
    return $rowCount;
}
function updateDbRow($link, $table, $row, $field, $indexId, $content, $file, $line)
{
    if (is_null($content)) {
        $query = "UPDATE {$table} SET {$field} = NULL WHERE {$indexId} = {$row};";
    } else {
        $content_e = mysqli_real_escape_string($link, $content);
        $query = "UPDATE {$table} SET {$field} = '{$content_e}' WHERE {$indexId} = '{$row}';";
    }
    $result = mysqli_query($link, $query);
    if (!$result) {
        echo 'query: ' . $query . '<br>';
        die("Function updateDbRow failed on table: {$table} when called from file {$file}: line: {$line}: "
        . mysqli_error($link));
    }
    mysqli_free_result($result);
}
function connectToDb($file, $line)
{
#    DEFINE("KTESA_DBUG", true, true);
    require_once "../mysql/setenv.php";
    $dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
    if ($dev) {
        $link = mysqli_connect(HOSTNAME_LOC, USERNAME_LOC, PASSWORD_LOC, DATABASE_LOC);
    } else {
        $link = mysqli_connect(HOSTNAME_000, USERNAME_000, PASSWORD_000, DATABASE_000);
    }
    if (!$link) {
        die("Function connectToDb failed when called from file {$file}: line: {$line}: "
        . mysqli_connect_error($link));
    }
    require "../admin/set_sql_mode.php";
    return $link;
}
