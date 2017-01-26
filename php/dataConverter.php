
<?php
/* This routine is a temporary script to convert encoded data in the test.csv file
    to unencoded data. Further, those items which contain html tags will be processed
    to remove the html structure and replace it with an array of items, which will be
    represented as a string separated by the ^ character. The items requiring removal
    of html structure are: "Rows", "Captions", "Photo Links", "Refs", "Prop Data", and
    "Act Data". The "Tips Txt" and "HikeInfo" data will have white space (newlines,
    tabs, etc.) replaced with a single space character to resolve issues where the
    data would otherwise be written across several cells in csv. The new page template
    will parse the array strings to re-create the html sections appropriately. */
$database = '../data/test.csv';
$newbase = '../data/tmptest.csv';
$oldDat = file($database);
$newDat = array();
$i = 0;
foreach($oldDat as $oldLine) {
	$hikeDat = str_getcsv($oldLine,",");
	$hdr = trim($hikeDat[0]);
	$hdr = substr($hdr,0,3);
	if ( $hdr === 'Ind') {
		# copy the header row
		$newDat[0] = $oldLine;
	} else {
		$hikeNo = $hikeDat[0];
		/* First, process those items that need decoding regardless of whether index
	       or hike page: i.e. google directions; hike info; and refs */
		$hikeDat[25] = rawurldecode($hikeDat[25]); // google directions
		$hikeInfo = rawurldecode($hikeDat[38]); // hike info (or index page info)
		$hikeDat[38] = preg_replace("/\s/"," ",$hikeInfo);
		/* references are lists which require additonal processing to strip html */
		$itemStr = rawurldecode($hikeDat[39]);
		# refs are <ul> lists: (every entry has refs, no testing for existence req'd)
		$listStart = strpos($itemStr,"<li");
		$itemCnt = 0;
		$refStr = array();
		while ($listStart) {
			$listStart += 4; // gets to actual item
			$remainLgth = strlen($itemStr) - $listStart;
			$itemStr = substr($itemStr,$listStart,$remainLgth);
			/* There are currently four items types: Book, Website, App, and html link;
			   the array string formed will use the following identifiers:
			   		- Book ->  b:	b^title^author
			   		- Website -> w
			   		- App -> a
			   		- Downloadable doc -> d
			   		- Html linke -> h */
			$itemType = substr($itemStr,0,3);
			$itemCnt++;
			if ($itemType === 'Boo') {
				$emStrt = strpos($itemStr,'<em>') + 4;
				$emEnd = strpos($itemStr,'</em>');
				$titleLgth = $emEnd - $emStrt;
				$title = substr($itemStr,$emStrt,$titleLgth);
				$authEnd = strlen($itemStr) - $emEnd - 5;
				$author = substr($itemStr,$emEnd+5,$authEnd);
				$bookStr = 'b^' . $title . '^' . $author;
				array_push($refStr,$bookStr);
			} elseif ($itemType === 'Web' || $itemType === '<a ' || $itemType === 'App' || $itemType === 'Dow') {
				$hrefStrt = strpos($itemStr,"href") + 6;
				$hrefEnd = strpos($itemStr,">");
				$hrefLgth = $hrefEnd - $hrefStrt;
				$href = substr($itemStr,$hrefStrt,$hrefLgth);
				$clickStrt = $hrefEnd + 1;
				$clickEnd = strpos($itemStr,"</a>");
				$clickLgth = $clickEnd - $clickStrt;
				$clickTxt = substr($itemStr,$clickStrt,$clickLgth);
				if ($itemType === 'Web') {
					$linkType = 'w';
				} elseif ($itemType === 'App') {
					$linkType = 'a';
				} elseif ($itemType === 'Dow') {
					$linkType = 'd';
				} else {
					$linkType = 'h';
				}
				$linkStr = $linkType . '^' . $href . '^' . $clickTxt;
				array_push($refStr,$linkStr);
			} else {
				$urtype = 'u^Unknown^';  // in case something weird gets written out
				echo "Unrecognized reference list item: " . $itemStr;
				array_push($refStr,$urtype);
			}
			$listStart = strpos($itemStr,"<li"); // this gets past the </li>
		}
		# trim off the last ^
		$hikeDat[39] = $itemCnt . '^' . implode("^",$refStr);
		/* NOW PROCEED BASED ON PAGE TYPE - INDEX PAGE or HIKE PAGE
		   So far converted [25], [38], and [39] */
		$markertype = trim($hikeDat[3]);
		if ($markertype === 'Visitor Ctr') {
			# INDEX PAGE:
			array_push($newDat,$oldLine);
			# width & height parameters not required here
			# table processing:	
			#
		} else {
			# HIKE PAGE:
			# Photo Urls
			if ($hikeDat[23] !== '') {
				$hikeDat[23] = rawurldecode($hikeDat[23]);
			}
			if ($hikeDat[24] !== '') {
				$hikeDat[24] = rawurldecode($hikeDat[24]);
			}
			# get captions for image in rows:
			$oldCaps = rawurldecode($hikeDat[35]);
			$strFrag = $oldCaps;
			$caps = array();
			$cStrt = strpos($strFrag,"<li>");
			for ($k=0; $k<100; $k++) {
				$cStrt += 4;
				$cEnd = strpos($strFrag,"</li>") + 5;
				$cLgth = $cEnd - $cStrt;
				$caption = substr($strFrag,$cStrt,$cLgth);
				array_push($caps,$caption);
				$fragLgth = strlen($strFrag) - $cEnd;
				$strFrag = substr($strFrag,$cEnd,$fragLgth);
				$cStrt = strpos($strFrag,"<li>");
				if ($cStrt === false) {
					break;
				}
			} 
			$caparray = implode("^",$caps);
			$capCnt = count($caps);
			# ROWS:
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
					$imgCnt = 0;
					$picNo = 0;
					# each row is enclosed in <div> </div> tags; procure images tag by tag
					$rowEls = array();
					while ($tagtype !== '/di') {
						/* images have all been formed the same way, so assume 
						    that each attribute is in the same relative position within the
						    tag, i.e. width before height, height before src, etc. */
						if ($tagtype === 'img') {
							# id this as either a photo (p) or a non-captioned image (n)
							$idPos = strpos($strleft,'id="') + 4;
							$idType = substr($strleft,$idPos,3);
							if ($idType === 'pic') {
								$idType = 'p';
							} else {
								$idType = 'n';
							}
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
							if ($idType === 'p') {
								$imgStr = 'p^' . $width . '^' . $src . '^' . $caps[$picNo];
								$picNo++;
							} else {
								# some jpgs don't seem to have a width param:
								if ($width == '') {
									$imgParms = getimagesize($src);
									$width = $imgParms[0];
								}
								$imgStr = 'n^' . $width . '^' . $src;
							}
							array_push($rowEls,$imgStr);
							/* THIS ROUTINE HAS EXPOSED A BUG: jpg maps have no width?? */
						} elseif ($tagtype === 'ifr') {
							# iframes have no caption; ht & wdth are the same
							if ($rowHt === '0') {  // iframe is first element in row:
								$htpos = strpos($strleft,"height")+8;
								$sublgth = strlen($strleft) - $htpos;
								$strleft = substr($strleft,$htpos,$sublgth);
								$htclose = strpos($strleft,'"');
								$rowHt = substr($strleft,0,$htclose);
								$width = $rowHt;
							} else {
								# get this img's width attribute
								$widpos = strpos($strleft,"width")+7;
								$sublgth = strlen($strleft) - $widpos;
								$strleft = substr($strleft,$widpos,$sublgth);
								$widclose = strpos($strleft,'"');
								$width = substr($strleft,0,$widclose);
							}
							$srcpos = strpos($strleft,"src")+5;
							$sublgth = strlen($strleft) - $srcpos;
							$strleft = substr($strleft,$srcpos,$sublgth);
							$srcclose = strpos($strleft,'"');
							$src = substr($strleft,0,$srcclose);
							$imgStr = 'f^' . $width . '^' . $src;
							array_push($rowEls,$imgStr);
						} else {
							echo "Unrecognizable tag in image row! " . $strleft;
						}
						# end of the row yet? look at the next tag:
						$nexttagpos = strpos($strleft,">")+2;
						$tagtype = substr($strleft,$nexttagpos,3);
						$imgCnt++;
					}  // end of while (looking at one row here)
					$rowStr = $imgCnt . '^' . $rowHt . '^' . implode("^",$rowEls);
					$rowno = $i - 29;
					echo 'hike ' . $hikeNo . ': Row no. ' . $rowno . '; ' . $rowStr . ' ;     ';
					$hikeDat[$i] = $rowStr;
				}
			}  // end of for loop row processing
		}  // end of else to process non-index-page line
	}  // end of else to process non-header line
	$i++;
	if ($i > 5) {
		break;
	}
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