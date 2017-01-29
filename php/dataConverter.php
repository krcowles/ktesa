
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
    
# **** NOTE: Currently reading from test.csv but saving results to tmptest.csv:
$database = '../data/test.csv';
$newbase = '../data/tmptest.csv';
$handle = fopen($newbase,"a");

$oldDat = file($database);
#$fileLgth = count($oldDat);
function reformList($htmlList) {
	$strt = strpos($htmlList,"<li>");
	$newlgth = strlen($htmlList) - $strt;
	$headless = substr($htmlList,$strt,$newlgth);
	#strip off end too...
	$end = strrpos($headless,"</li>");
	$trunk = substr($headless,0,$end);
	$newlist = str_replace("<li>","^",$trunk);
	$newlist = str_replace("</li>","",$newlist);
	$itemCnt = substr_count($newlist,"^");
	return $itemCnt . $newlist;
}

foreach($oldDat as $oldLine) {
	$hikeDat = str_getcsv($oldLine,",");
	$hdr = trim($hikeDat[0]);
	$hdr = substr($hdr,0,3);
	if ($hdr !== 'Ind') {
/* GENERIC PAGE PROCESSING BEGINS HERE */
		$hikeNo = $hikeDat[0];
		/* First, process those items that need decoding regardless of whether index
	       or hike page: i.e. google directions; hike info; and refs */
		$hikeDat[25] = rawurldecode($hikeDat[25]); // google directions
		$hikeInfo = rawurldecode($hikeDat[38]); // hike info (or index page info)
		$hikeDat[38] = preg_replace("/\s/"," ",$hikeInfo);
/* REFERENCES PROCESSING */
		/* references are lists which require additonal processing to strip html */
		$itemStr = rawurldecode($hikeDat[39]);
		#$itemStr = strip_tags($itemStr,"<a><em>");
		$itemStr = preg_replace("/\n|\r|\t/","",$itemStr); // can't use \s
		$itemStr = trim($itemStr); // essential!
		$itemStr = reformList($itemStr);
		$cntEnd = strpos($itemStr,"^");
		$refCnt = intval(substr($itemStr,0,$cntEnd));		
		$remlgth = strlen($itemStr) - ($cntEnd + 1);
		$itemStr = substr($itemStr,$cntEnd+1,$remlgth);
		$refStr = $refCnt;
		$tagType = substr($itemStr,0,3);
		/* There are currently eleven items types: Book, Website, App, Downloadable Doc,
		   blog, Related link, On-line Map, Magazine, News Article, html link, and Nothing
		   Relevant;
		   the array string formed will use the following identifiers:
				- Book ->       b:	b^title^author
				- Photo Essay -> p: p^title^author
				- Website ->    w:  w^href^clicktxt
				- App ->        a:  a^href^clicktxt
				- Downloadable doc -> d:  d^href^clicktxt
				- Blog ->       l:  l^href^clicktxt 
				- Related link -> r:  r^href^clicktxt
				- On-line Map -> o:   o^href^clicktxt (or Map)
				- Magazine ->   m:  m^href^clicktxt
				- News article -> n:  n^href^clicktxt
				- Meetup Group -> g:  g^href^clicktxt
				- Html link ->  h:  h^href^clicktxt
				- String only -> n: n^string      */
		for ($j=0; $j<$refCnt; $j++) {
			if ($tagType == 'Boo' || $tagType == 'Pho') {
				$emStrt = strpos($itemStr,'<em>') + 4;
				$emEnd = strpos($itemStr,'</em>');
				$titleLgth = $emEnd - $emStrt;
				$title = substr($itemStr,$emStrt,$titleLgth);
				#echo "Book: " . $title;
				# author begins after the </em> tag; author ends at </li>
				$authStrt = $emEnd + 5;
				if ($j === $refCnt - 1) {
					$authLgth = strlen($itemStr) - $authStrt;
				} else {
					$nextItemStrt = strpos($itemStr,"^");
					$authLgth = $nextItemStrt - $authStrt;
				}
				$author = substr($itemStr,$authStrt,$authLgth);
				#echo "; Author: " . $author;
				if ($tagType == 'Boo') {
					$refStr = $refStr . "^b^" . $title . "^" . $author;
				} else {
					$refStr = $refStr . "^p^" . $title . "^" . $author;
				}
			} elseif ($tagType == 'Not' || $tagType == 'See' || $tagType == 'No ') {
				$refStr = $refStr . "^n";
			} else { // everything else has an <a> tag:
				$hrefStrt = strpos($itemStr,"href") + 6;
				$hrefEnd = strpos($itemStr,'target=');
				/* NOTE: there may be some issue where end of href and start of
				   target= has more than one space or some goofy character, so
				   finding target will leave at least the closing " on the href */
				$hrefLgth = $hrefEnd - $hrefStrt;
				$href = substr($itemStr,$hrefStrt,$hrefLgth);
				$href = trim($href);
				$endRef = strlen($href) - 1;
				$href = substr($href,0,$endRef);
				#echo "This href is " . $href . "\n";
				if ($j === $refCnt - 1) {
					$thisItemEnd = strlen($itemStr);
				} else {
					$thisItemEnd = strpos($itemStr,"^");
				}
				$thisItem = substr($itemStr,0,$thisItemEnd);
				$clickTxt = strip_tags($thisItem);
				/* NOTE: if there is text preceding the link other than standard,
				   it will be included as click text */
				if ($tagType === 'Web') {
					$linkType = 'w';
				} elseif ($tagType === 'App') {
					$linkType = 'a';
				} elseif ($tagType === 'Dow') {
					$linkType = 'd';
				} elseif ($tagType === '<a ') {
					$linkType = 'h';
				} elseif ($tagType === 'Blo') {
					$linkType = 'l';
				} elseif ($tagType === 'Rel') {
					$linkType = 'r';
				} elseif ($tagType === 'On-' || $tagType === 'Map') {
					$linkType = 'o';
				} elseif ($tagType === 'Mag') {
					$linkType = 'm';
				} elseif ($tagType === 'New') {
					$linkType = 's';
				} elseif ($tagType === 'Mee') {
					$linkType = 'g';
				} else {
					echo "Unrecognizable tag type in Hike " . $hikeNo . ": " . $tagType;
					echo " --Remaining string: " . $itemStr;
					$linkType = "u";
				}
				$refStr = $refStr . "^" . $linkType . "^" . $href . "^" . $clickTxt;
			}
			# find next tag
			$tagstrt = strpos($itemStr,"^") + 1;
			$remlgth = strlen($itemStr) - $tagstrt;
			$itemStr = substr($itemStr,$tagstrt,$remlgth);
			$tagType = substr($itemStr,0,3);
			
		}  // end of for loop processing refs
		$hikeDat[39] = $refStr;
		/* NOW PROCEED BASED ON PAGE TYPE - INDEX PAGE or HIKE PAGE
		   So far converted [25], [38], and [39] */
		$markertype = trim($hikeDat[3]);
/* INDEX PAGE  SPECIFIC PROCESSING */
		if ($markertype === 'Visitor Ctr') {
			# do some processing to convert the table data
			$x++;
		} else {
/* HIKE PAGE SPECIFIC PROCESSING */
			# HIKE PAGE:
			# Photo Urls
			if ($hikeDat[23] !== '') {
				$hikeDat[23] = rawurldecode($hikeDat[23]);
			}
			if ($hikeDat[24] !== '') {
				$hikeDat[24] = rawurldecode($hikeDat[24]);
			}
/* CAPTIONS PROCESSING */
			# get captions for image in rows:
			$oldCaps = rawurldecode($hikeDat[35]);
			$oldCaps = trim($oldCaps);
			$capsStr = reformList($oldCaps);
			$hikeDat[35] = $capsStr;
			$caps = explode("^",$capsStr);
			$firstoff = array_shift($caps);
/* IMAGE ROW PROCESSING */
			# ROWS: already working by the time I made fct reformList
			$picNo = 0;
			for ($i=29; $i<35; $i++) {
				# ROW DATA
				if ($hikeDat[$i] == '') {
					break;
				} else {
					$rowHt = '0';  # when this is not 0, search no more for height!
					$oldrow = rawurldecode($hikeDat[$i]);
					$oldrow = trim($oldrow);
					$oldrow = preg_replace("/\n|\r|\t/","",$oldrow); // can't use \s
					$oldrow = trim($oldrow);
					$oldlgth = strlen($oldrow);
					$imgPos = strpos($oldrow,">")+2;  // this is the END of the <div> tag
					$sublgth = $oldlgth - $imgPos;
					$strleft = substr($oldrow,$imgPos,$sublgth);
					$tagtype = substr($strleft,0,3);
					$imgCnt = 0;
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
									$width = intval($imgParms[0]);
									$height = intval($imgParms[1]);
									$htFactor = $rowHt/$height;
									# maintain aspect ratio
									$width = intval($htFactor * $width);
								}
								$imgStr = 'n^' . $width . '^' . $src;
							}
							array_push($rowEls,$imgStr);
							$nexttagpos = strpos($strleft,">")+2;
							$tagtype = substr($strleft,$nexttagpos,3);
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
							# get past the </iframe> tag:
							$endtag = strpos($strleft,"</iframe>") + 9;
							$nxtlgth = strlen($strleft) - $endtag;
							$strleft = substr($strleft,$endtag,$nxtlgth);
							$tagtype = substr($strleft,1,3);
						} else {
							#echo "First n chars: " . ord(substr($strlen,0,1));
						}
						# end of the row yet? look at the next tag:
						$imgCnt++;
					}  // end of while (looking at one row here)
					$rowStr = $imgCnt . '^' . $rowHt . '^' . implode("^",$rowEls);
					$hikeDat[$i] = $rowStr;
				}
			}  // end of for loop row processing
/* ALBUM-LINKS-FOR-PHOTOS - PROCESSING */
			# Now get the link list for the photos:
			$rawlist = rawurldecode($hikeDat[36]);
			$rawlist = trim($rawlist);
			$linklist = reformList($rawlist); 
			$hikeDat[36] = $linklist;
/* TIPS PROCESSING: */
			# Now process any Tips Text:
			$tips = rawurldecode($hikeDat[37]);
			if ($tips !== '') {
				$hikeDat[37] = preg_replace("/\s/"," ",$tips);
			}
/* PROPOSED DATA SECTION PROCESSING */
			# If there is any Proposed Data:
			# There are 3 pieces: label, href, click txt
			$propDat = rawurldecode($hikeDat[40]);
			$propDat = preg_replace("/\n|\r|\t/","",$propDat);
			$propDat = trim($propDat);
			$actDat = rawurldecode($hikeDat[41]);
			$actDat = preg_replace("/\n|\r|\t/","",$actDat);
			$actDat = trim($actDat);
			if ($propDat !== '') {
				#echo "Prop data found... Hike " . $hikeNo . "\n";
				$proplist = reformList($propDat);
				$pCntEnd = strpos($proplist,"^");
				$pCnt = intval(substr($proplist,0,$pCntEnd));
				$pStr = $pCnt;
				$pLgth = strlen($proplist) - ($pCntEnd + 1);
				$proplist = substr($proplist,$pCntEnd+1,$pLgth);
				for ($j=0; $j<$pCnt; $j++) {
					# LABEL:
					$lblEnd = strpos($proplist,"<a");
					$label = substr($proplist,0,$lblEnd);
					if ($j === $pCnt - 1) {
						$pEnd = strlen($proplist) - $lblEnd;
					} else {
						$pEnd = strpos($proplist,"^") - $lblEnd;
					}
					$thisp = substr($proplist,$lblEnd,$pEnd);
					# HREF:
					$hrefStrt = strpos($thisp,"href") + 6;
					$hrefEnd = strpos($thisp,'target=');
					/* NOTE: there may be some issue where end of href and start of
					   target= has more than one space or some goofy character, so
					   finding target will leave at least the closing " on the href */
					$hrefLgth = $hrefEnd - $hrefStrt;
					$href = substr($thisp,$hrefStrt,$hrefLgth);
					$href = trim($href);
					$endRef = strlen($href) - 1;
					$href = substr($href,0,$endRef);
					# CLICK TXT:
					$clickTxt = strip_tags($thisp);
					$clickTxt = trim($clickTxt);
					# prep for next iteration
					$pStr = $pStr . "^" . $label . "^" . $href . "^" . $clickTxt;
					$nxtpStrt = strpos($proplist,"^") + 1;
					$nxtpLgth = strlen($proplist) - $nxtpStrt;
					$proplist = substr($proplist,$nxtpStrt,$nxtpLgth);
				}
				$hikeDat[40] = $pStr;
			} // end of propdat
/* ACTUAL DATA PROCESSING SECTION: */
			if ($actDat !== '') {
				#echo "Act data found... Hike " . $hikeNo;
				$actlist = reformList($actDat);
				$aCntEnd = strpos($actlist,"^");
				$aCnt = intval(substr($actlist,0,$aCntEnd));
				$aLgth = strlen($actlist) - ($aCntEnd + 1);
				$aStr = $aCnt;
				$actlist = substr($actlist,$aCntEnd+1,$aLgth);
				for ($j=0; $j<$aCnt; $j++) {
					# LABEL:
					$lblEnd = strpos($actlist,"<a");
					$label = substr($actlist,0,$lblEnd);
					if ($j === $aCnt - 1) {
						$aEnd = strlen($actlist) - $lblEnd;
					} else {
						$aEnd = strpos($actlist,"^") - $lblEnd;
					}
					$thisa = substr($actlist,$lblEnd,$aEnd);
					# HREF:
					$hrefStrt = strpos($thisa,"href") + 6;
					$hrefEnd = strpos($thisa,'target');
					/* NOTE: there may be some issue where end of href and start of
					   target= has more than one space or some goofy character, so
					   finding target will leave at least the closing " on the href */
					$hrefLgth = $hrefEnd - $hrefStrt;
					$href = substr($thisa,$hrefStrt,$hrefLgth);
					$href = trim($href);
					$endRef = strlen($href) - 1;
					$href = substr($href,0,$endRef);
					# CLICK TXT:
					$clickTxt = strip_tags($thisp);
					$clickTxt = trim($clickTxt);
					# prep for next iteration
					$aStr = $aStr . "^" . $label . "^" . $href . "^" . $clickTxt;
					$nxtaStrt = strpos($actlist,"^") + 1;
					$nxtaLgth = strlen($actlist) - $nxtaStrt;
					$actlist = substr($actlist,$nxtaStrt,$nxtaLgth);
				}
				$hikeDat[41] = $aStr;
			}
			$x++;
		}  // end of if-else to separate index page and hike page processing
	}  // end of "if $hdr !== 'Ind'  [test to see if header row]
	fputcsv($handle,$hikeDat);
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
DATA CONVERTER FULLY FUNCTIONAL & EXECUTED (But -- there may be an anomaly or two!)
</div>

</body>
</html>