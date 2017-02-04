<!DOCTYPE html>
<html>

<head>
	<title>Fix Hike File</title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
		content="Fix test.csv" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="../styles/hikes.css"
		type="text/css" rel="stylesheet" />
</head>

<body>
<?php
/* Fixes data converter error: click text contains pre-link text */
$database = '../data/test.csv';
$hikes = file($database);
if ($hikes === false) {
	echo "File access meltdown...:";
}
# Strip off the newline chars:
foreach ($hikes as &$hline) {
	$trimlgth = strlen($hline) - 1;
	$hline = substr($hline,0,$trimlgth);
}
$tmpdat = '../data/tmptest.csv';
$handle = fopen($tmpdat,"a");
foreach ($hikes as $hdat) {
	# each line is an array, $info:
	$info = str_getcsv($hdat,",");
	if ($info[39] !== '' && trim($info[39]) !== 'Refs') {
		$refs = explode("^",$info[39]);
		$refcnt = intval($refs[0]);
		array_shift($refs);
		$nxt = 0;
		for ($k=0; $k<$refcnt; $k++) {
			$item = $refs[$nxt];
			if ($item !== 'b' && $item !== 'p' && $item !== 'n' && $item !== 'h') {
				# bad boy is $nxt+2
				$bb = $refs[$nxt+2];
				$strt = strpos($bb,":") + 1;
				# sometimes there is a space after the colon: eliminate
				if ( substr($bb,$strt,1) === ' ' ) {
					$strt++;
				}
				$lgth = strlen($bb) - $strt;
				$refs[$nxt+2] = substr($bb,$strt,$lgth);
				$nxt += 3;
			} elseif ($item === 'n') {
				$nxt += 2;
			} else {  # 'h', 'b', and 'p', require no stripping: just increment pointer
				$nxt +=  3;
			}
		}
		$info[39] = $refcnt . '^' . implode("^",$refs);
		echo "<p>Newstr: " . $info[39]. '</p>';
	}
	#$hikedat = implode(",",$info);
	fputcsv($handle, $info);
}
?>

<div>FPUTCSV: DONE!</div>
</body>
</html>