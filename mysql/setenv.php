<?php
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($dev) {
    $rel_addr = '../mysql/';
    require_once "../mysql/local_mysql_connect.php";
} else {
    $rel_addr = '../mysql/';
    require_once "../mysql/000mysql_connect.php";
}
function insertDbRow($link,$table,$file,$line) {
    $insQuery = "INSERT INTO {$table} () VALUES ();";
    $insResults = mysqli_query($link,$insQuery);
    if (!$insResults) {
        echo "insQuery: {$insQuery} <br>";
        die ("Function insertDbRow failed to insert into table: {$table} when called from file {$file}: line: {$line}: "
        . mysqli_error($link));
    }
    mysqli_free_result($insResults);

    # Get index number
    $selQuery = "SELECT * FROM {$table} ORDER BY 1 DESC LIMIT 1;";
    $selResults = mysqli_query($link,$selQuery);
    $index = mysqli_fetch_row($selResults);
    if (!$selResults) {
        echo "selQuery: {$insQuery} <br>";
        die ("Function insertDbRow failed to get index from table: {$table} when called from file {$file}: line: {$line}: "
        . mysqli_error($link));
    }
    mysqli_free_result($selResults);
    return $index[0];
}
function insertDbRowMulti($link,$query,$file,$line) {
    $results = mysqli_query($link,$query);
    if (!$results) {
        die ("Function insertDbRowMulti failed when called " . 
        "from file {$file}: line: {$line} " . 
        "with query : {$query} <br> " . 
        mysqli_error($link));
    }
    mysqli_free_result($insResults);
}
#
function getDbRowNum($link,$table,$file,$line) {
    # Get index number
    $query = "SELECT * FROM {$table} ORDER BY 1 DESC LIMIT 1;";
    $results = mysqli_query($link,$query);
    if (!$results) {
        die ("Function getDbRowNum failed when called " . 
        "from file {$file}: line: {$line} " . 
        "with query : {$query} <br> " . 
        mysqli_error($link));
    }
    $newRow = mysqli_fetch_row($results);
    mysqli_free_result($results);
    return $newRow[0];
}
function updateDbRow($link,$table,$row,$field,$indexId,$content,$file,$line) {
    if (is_null($content)) {
        $query = "UPDATE {$table} SET {$field} = NULL WHERE {$indexId} = {$row};";
    }
    else {
        $content_e = mysqli_real_escape_string($link,$content);
        $query = "UPDATE {$table} SET {$field} = '{$content_e}' WHERE {$indexId} = '{$row}';";
    }
    $result = mysqli_query($link,$query);
    if (!$result) {
        echo 'query: ' . $query . '<br>';
        die ("Function updateDbRow failed on table: {$table} when called from file {$file}: line: {$line}: "
        . mysqli_error($link));
    }
    mysqli_free_result($result);
}
/*
error_reporting(-1);
ini_set('display_errors', 'On');
set_error_handler("var_dump");
 */
