<?php
require_once '../mysql/setenv.php';

// Temporary variable, used to store current query
$templine = '';
// Read in entire file
$lines = file("../data/database.sql");
// Loop through each line
foreach ($lines as $line)
{
	// Skip it if it's a comment
	if (substr($line, 0, 2) == '--' || $line == '')
	    continue;

	// Add this line to the current segment
	$templine .= $line;
	// If it has a semicolon at the end, it's the end of the query
	if (substr(trim($line), -1, 1) == ';')
	{
	    // Perform the query
	    $req = mysqli_query($link, $templine);
	    if (!$req) {
	        die ("<p>load_all_tables.php: Failed: " .
	            mysqli_error($link) . "</p>");
	    }
    $templine = '';    // Reset temp variable to empty
	}
}
 echo "Tables imported successfully";
mysqli_free_result($req);
mysqli_close($link);
?>
