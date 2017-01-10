<?php
	$database = '../data/test.csv';
	$wholeCSV = file($database);
	echo '<div style="display:none;"><ul>';
	foreach ($wholeCSV as $hikeData) {
		$harray = str_getcsv($hikeData);
		#echo "item: " . $harray[14];
		if ($harray[14] == '') {
			echo "<li>" . $harray[0] . '</li>';
		}
	}
	echo '</ul></div>';
?>