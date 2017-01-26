
<?php
/* This routine is a temporary script to convert test.csv "Rows" from urlencoded strings
	to unencoded array strings with no html. The page template will parse the array string
	to form the images in the rows dynamically. In addition, the "Proposed Data" and
	"Actual Data", and the photo and google maps directions urls will be unencoded. */
$database = '../data/test.csv';
$newbase = '../data/tmptest.csv';
$oldDat = file($database);
$newDat = array();
foreach($oldDat as $oldLine) {
	$hikeDat = str_getcsv($oldLine,",");
	$hdr = trim($hikeDat[0]);
	$hdr = substr($hdr,0,3);
	if ( $hdr === 'Ind') {
		# copy the header row
		$newDat[0] = $oldLine;
	} else {
		$markertype = trim($hikeDat[3]);
		if ($markertype === 'Visitor Ctr') {
			array_push($newDat,$oldLine);
		} else {
			# process the old hike line
			if ($hikeDat[23] !== '') {
				$hikeDat[23] = rawurldecode($hikeDat[23]);
			}
			if ($hikeDat[24] !== '') {
				$hikeDat[24] = rawurldecode($hikeDat[24]);
			}
			$hikeDat[25] = rawurldecode($hikeDat[25]);
			for ($i=29; $i<35; $i++) {
				# ROW DATA
				if ($hikeDat[$i] == '') {
					break;
				} else {
					$rowHt = '0';  # when this is not 0, search no more for height!
					$oldrow = rawurldecode($hikeDat[$i]);
					$oldlgth = strlen($oldrow);
					$imgPos = strpos($oldrow,">")+2;  // this is the END of the <div> tag
					$sublgth = $oldlgth - $imgPos;
					$strleft = substr($oldrow,$imgPos,$sublgth);
					$tagtype = substr($strleft,0,3);
					# is next image an <img> or an <iframe>?
					# each row is enclosed in <div> </div> tags
					while ($tagtype !== '/di') {
						/* these rows have all been formed the same way, so assume 
						    that each attribute is in the same relative position within the
						    tag, i.e. width before height, height before src, etc. */
						if ($tagtype === 'img') {
							# get this img's width attribute
							$widpos = strpos($strleft,"width")+7;
							$sublgth = strlen($strleft) - $widpos;
							$strleft = substr($strleft,$widpos,$sublgth);
							$widclose = strpos($strleft,'"');
							$width = substr($strleft,0,$widclose);
							# get this row's height attribute
							if ($rowHt === '0') { #rowheight need be determined only once
								$htpos = strpos($strleft,"height")+8;
								$sublgth = strlen($strleft) - $htpos;
								$strleft = substr($strleft,$htpos,$sublgth);
								$htclose = strpos($strleft,'"');
								$rowHt = substr($strleft,0,$htclose);
							}
							# get this img's src attribute:
							$srcpos = strpos($strleft,"src")+5;
							$sublgth = strlen($strleft) - $srcpos;
							$strleft = substr($strleft,$srcpos,$sublgth);
							$srcclose = strpos($strleft,'"');
							$src = substr($strleft,0,$srcclose);
							echo "this row-> NO:|" . $rowHt . "|" . $width . "|" . $src . "  ; ";
							# get this img's caption:
							
							/* THIS ROUTINE HAS EXPOSED A BUG: jpg maps have no width?? */
						} elseif ($tagtype === 'ifr') {
							# iframes have no caption
						
						
							echo "strleft is " . $strleft;
						} else {
							echo "Unrecognizable tag in image row! " . $strleft;
						}
						# end of the row yet? look at the next tag:
						$nexttagpos = strpos($strleft,">")+2;
						$tagtype = substr($strleft,$nexttagpos,3);
					}  // end of while (looking at one row here)
				}
			}  // end of for loop row processing
			break;
		}  // end of else to process non-index-page line
	}  // end of else to process non-header line
}  // end of foreach
?>
<!DOCTYPE html>
<head>
	<title>Row Converter</title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
		content="Convert row data" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="../styles/hikes.css"
		type="text/css" rel="stylesheet" />
</head>

<body>

<div>
DONE!
</div>

</body>
</html>