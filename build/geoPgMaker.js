// Generic variables used to create html content (string)
var pic;    // html for row of pictures
var cap;    // html for row of corresponding captions
var lnk;    // html for links to Flickr ablum pictures
// Generic variable to create debug messages to be output on the page during runtime
var msg;

/* GET all the info from the main page that will be used to load and size photos and 
   create corresponding captions acquired from the Flickr description fields */
var $htmlNameLocs = $('li'); // an array holding the list element locations [pic names]
var noOfPics = $htmlNameLocs.length;
// Get the user-specified photo names from the html:
var htmlPicNames = new Array();
for ( var m=0; m<noOfPics; m++ ) {
	htmlPicNames[m] = $htmlNameLocs[m].innerText;
}
var geoMap = $('#frm_name').text();
var tsvFile = $('#tsv_file').text();
//var tsvFile = '/Users/kencowles/src/ktesa/gpsv/traders.tsv' //+ $('#tsv_file').text();

// get elevation chart(s), if any
var eopt = $('#inclElev').text();
eopt = parseFloat(eopt);
var $elevCharts = $('.egraph');
if ( eopt > 0 ) {
	var egraph = new Array();
	for ( var egno=0; egno<eopt; egno++ ) {
		egraph[egno] = $elevCharts[egno].textContent;
		msg = '<p>Elevation Chart loaded: ' + egraph[egno] + '</p>';
		$('#dbug').append(msg);
	}
}
var ewidth;

// Make sure the required files are present:
// .tsv file:
$.ajax({
    url:tsvFile,
    type:'HEAD',
    error: function()
    {
        msg = '<p><strong>.tsv FILE NOT FOUND</strong></p>';
        $('#dbug').append(msg);
    }
});
// geomap:
$.ajax({
    url:geoMap,
    type:'HEAD',
    error: function()
    {
        msg = '<p><strong>GEOMAP NOT FOUND</strong></p>';
        $('#dbug').append(msg);
    }
});


// Function to DOWNLOAD the resulting html
function download(strData, strFileName, strMimeType) {
    var D = document,
        A = arguments,
        a = D.createElement("a"),
        d = A[0],  // 1st arg = strData (text data to send)
        n = A[1],  // 2nd arg = strFileName (name to send to computer)
        t = A[2] || "text/plain";

    //build download link:
    /* escape fct deprecated: use encodeURI() instead 
    a.href = "data:" + strMimeType + "charset=utf-8," + escape(strData); */
    a.href = "data:" + strMimeType + "charset=utf-8," + encodeURI(strData);
	if ('download' in a) { //FF20, CH19
			a.setAttribute("download", n);
			a.innerHTML = "downloading...";
			D.body.appendChild(a);
			setTimeout(function() {
				var e = D.createEvent("MouseEvents");
				e.initMouseEvent("click", true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
				a.dispatchEvent(e);
				D.body.removeChild(a);
			}, 66);
			return true;
	}; // end if('download' in a)

    //do iframe dataURL download: (older W3)
    var f = D.createElement("iframe");
    D.body.appendChild(f);
    f.src = "data:" + (A[2] ? A[2] : "application/octet-stream") + (window.btoa ? ";base64" : "") + "," + (window.btoa ? window.btoa : escape)(strData);
    setTimeout(function() {
        D.body.removeChild(f);
    }, 333);
    return true;
} // END OF DOWNLOAD FUNCTION

var xhr = new XMLHttpRequest();

xhr.onload = function() {
	if ( xhr.status === 200 ) {

		var picNames = new Array();
		var picDescs = new Array();
		var picDates = new Array();
		var picYear = new Array();
		var picMonth = new Array();
		var picDay = new Array();
		var nSize = new Array();
		var albPics = new Array();
		var captions = new Array();
		var gpsv_data;
		var gpsv_array = new Array();
		var mo;
		var countAdj;
	
		/* ****** SECTION 1: Read in the (*ENTIRE*) GPSVinput.tsv file
		   as specified by the user (the 'tsvFile' var) */
		gpsv_data = xhr.responseText;
		var txtLength = gpsv_data.length;
		// determine the number of fields in the header line
		var hdrIndx = gpsv_data.indexOf('\n');
		// the first line of info reveals the columns in play
		var dataStrt = hdrIndx + 1;
		var hdrData = gpsv_data.substring(0,hdrIndx);
		var hdrArray = hdrData.split('\t');
		var hdrFlds = hdrArray.length;
		// form the useful data into an array of pieces
		gpsv_data = gpsv_data.substring(dataStrt,txtLength);
		gpsv_data = gpsv_data.replace(/\n/g,'\t');
		gpsv_array = gpsv_data.split('\t');
		// gpsv_array[0] should always indicate the first field of any line
		var i = 0;
		var k = 0;
	
		/* NOTE: there appears to be an anomaly whereby the last field in the gpsv
		   array (created by mkgpsv) can SOMETIMES be an unrecognizable element,
		   perhaps EOF or other. To account for this anomaly, a check is performed
		   to see if the expected number of elements is present, or one more than
		   that...  */
		if ( gpsv_array.length % hdrFlds == 0 ) {
			msg = '<p>Expected number of fields present</p>';
			$('#dbug').append(msg);
			countAdj = 0;
		} else {
			if ( gpsv_array.length % hdrFlds == 1 ) {
				msg = '<p>Oddball field detected at end of gpsv array</p>';
				$('#dbug').append(msg);
				countAdj = -1;
			} else {
				msg = '<p>Array is off by ' + (gpsv_array.length) % hdrFlds;
				msg += '; program may fail to execute</p>';
				$('#dbug').append(msg);
			}
		}
	   
	   msg = '<p>Length of gpsv_array is ' + gpsv_array.length + '</p>';
	   $('#dbug').append(msg);
	   
		// start the data extraction at the first array element AFTER the hdrFlds
		for ( var j = 0; j < gpsv_array.length; j++ ) {
			if ( gpsv_array[j] == gpsv_array[0] ) {
				picNames[i] = gpsv_array[j+1];
				picDescs[i] = gpsv_array[j+2];
				albPics[i] = gpsv_array[j+6];
				picDates[i] = gpsv_array[j+7];
				nSize[i] = gpsv_array[j+8];
				// end of date acquisition
				// extract month, day and year
				picYear[i] = picDates[i].substring(0,4);
				mo = picDates[i].substring(5,7);
				switch ( mo ) {
					case '01' : 
						picMonth[i] = 'Jan';
						break;
					case '02' :
						picMonth[i] = 'Feb';
						break;
					case '03' :
						picMonth[i] = 'Mar';
						break;
					case '04' :
						picMonth[i] = 'Apr';
						break;
					case '05' :
						picMonth[i] = 'May';
						break;
					case '06' :
						picMonth[i] = 'Jun';
						break;
					case '07' :
						picMonth[i] = 'Jul';
						break;
					case '08' :
						picMonth[i] = 'Aug';
						break;
					case '09' :
						picMonth[i] = 'Sep';
						break;
					case '10' :
						picMonth[i] = 'Oct';
						break;
					case '11' :
						picMonth[i] = 'Nov';
						break;
					case '12' :
						picMonth[i] = 'Dec';
						break;
				}
				picDay[i] = picDates[i].substring(8,10);
				if ( picDay[i].charAt(0) == '0' ) {
					picDay[i] = picDay[i].charAt(1);
				}
				captions[i] = picMonth[i] + ' ' + picDay[i] + ', ' + picYear[i] +
					': ' + picDescs[i];
				i += 1;
			}  // end of if statement: (gpsv_array[j] == gpsv_array[0])
		}  // end of 'for j=' loop
		
		/* ****** SECTION 2: correlate the html-specified pix with the loaded .tsv array */
		msg = '<p>NO OF ARRAY ELEMENTS READ IN: ' + i + '</p>';
		$('#dbug').append(msg);
		var tmp_names = new Array();
		var tmp_descs = new Array();
		var tmp_albs = new Array();
		var tmp_nsize = new Array();
		var tmp_caps = new Array();
		var iVals = new Array();
		var z = 0;
		for ( var x=0; x<noOfPics; x++ ) {
			for ( var y=0; y<i; y++ ) {
				if (htmlPicNames[x] == picNames[y] ) {
					tmp_names[z] = picNames[y];
					tmp_descs[z] = picDescs[y];
					tmp_nsize[z] = nSize[y];
					tmp_albs[z] = albPics[y];
					tmp_caps[z] = captions[y];
					iVals[z] = y;
					z++;
				}
			}
		}
		// iVals should hold all indices for the desired pix
		msg = '<p>indices: ';
		for ( t=0; t<noOfPics; t++ ) {
			msg += iVals[t] + ', ';
		}
		msg += '</p>';
		$('#dbug').append(msg);
		for ( w=0; w<noOfPics; w++ ) {
			picNames[w] = tmp_names[w];
			picDescs[w] = tmp_descs[w];
			albPics[w] = tmp_albs[w];
			nSize[w] = tmp_nsize[w];
			captions[w] = tmp_caps[w];
		}
		// Print the js output message for all further report-back
		msg = '<p><strong>Start javascript output here...</strong></p>' +
      		  '<p>Number of html pic names read in: ' + noOfPics + '</p>';
		$('#dbug').append(msg);
		
		/* ****** SECTION 3: function to "grow" row height in order to optimize
	   space utilization across the page width: tabulates row size (one row only)
	   and returns to caller with array "parms": #pix in the new row; row height;
	   iframe included (boolean); remaining margin */
	   
		// For calculating image sizes & captions
		var rowHeight = 260;  // starting point only, but this var won't ever change
		var maxHeight = rowHeight; // will track the biggest optimization
		var picWidths = new Array();
		var rowPicWidth = $('.bodyBox').css('width');
		var rowChIndx = rowPicWidth.indexOf('px');
		var rowLineWidth = rowPicWidth.substring(0,rowChIndx) - 14;
		var rowMargin;
		var curPic = 0;
		var curRowWidth = 0;
		var imgRowNo = 0;
		var fctArgs = new Array();
		var picId = 0;
		var newHt;
		var soloMap = false;
	
		function optRowHt() { 
			var parms;
			var marg;
			var picsInRow = 0;
			var optHeight = rowHeight; // leave rowHeight intact
			var inclFrame = false;
			// this function uses newWidths to preserve original picWidths
			var newWidths = new Array();
			var buildWidth = 0;
			if ( curPic == noOfPics ) {
				soloMap = true;
				// iframe only in this row: optimize to maxHeight of rows thus far
				msg = '<p>[RO] iframe is only item in this row; row width '
					+ '= row height (default): ' + maxHeight + '</p>';
				$('#dbug').append(msg);
				marg = rowLineWidth - (maxHeight + 5);
				parms = [0, maxHeight, true, marg];
				return parms;
			}
			// see what fits "as is":
			var itmCnt = noOfPics + 1; // add iframe into total count
			for (var n=curPic; n<itmCnt; n++ ) {
				if ( n ==  (itmCnt-1) ) { // item is an iframe
				   buildWidth += optHeight + 5; // iframe is square + margin
				   inclFrame = true;
				} else {
				   newWidths[picsInRow] = picWidths[n];
				   buildWidth += newWidths[picsInRow] + 14; // 14px for borders + margins
				   picsInRow++;
				}
				if ( buildWidth > rowLineWidth ) {
					if ( inclFrame == true ) {
						buildWidth -= (optHeight + 5);
						inclFrame = false;
					} else {
						picsInRow--;
						buildWidth -= (newWidths[picsInRow] + 14);
					}
					break;
			   }
			} // end FOR
			// calculate remaining white space in this row
			marg = rowLineWidth - buildWidth; 
			msg = '<p>[RO] # pix = ' + picsInRow + '; frame is ' + inclFrame + '; row width = ' +
					buildWidth + '; and margin = ' + marg + '</p>';
			$('#dbug').append(msg);

			// new rowHeight ONLY IF: current margins > 80px 
			if ( marg < 80 ) {
			   return parms = [picsInRow, optHeight, inclFrame, marg];
			} else {
				if ( marg > 310 ) { // big margin, at least grow it a little bit!
					optHeight = 1.3 * rowHeight;
					// calculate new picWidths (if only iFrame, routine already exited)
					for ( var m=curPic; m<(curPic + picsInRow); m++ ) {
						picWidths[m] = 1.3 * picWidths[m];
					}
					return parms = [picsInRow, optHeight, inclFrame, marg];
				} else {
				   // try increasing rowHeight by 10% per try until optimally filled
				   if ( inclFrame ) {
					   itmCnt = picsInRow + 1;
				   } else {
					   itmCnt = picsInRow;
				   }
				   var mult = 1.00;
				   var tryHt;
				   var lastWidth;
				   buildWidth = 0;
				   var calcNewWidth;
				   while ( marg >= 100 ) {
					   mult = 1.1 * mult;
					   tryHt = mult * rowHeight;
					   msg = '<p>Current height being attempted: ' + tryHt + '</p>';
					   $('#dbug').append(msg);
					   for ( var p=0; p<itmCnt; p++ ) {
						   if ( p == (itmCnt-1) && inclFrame ) {
							   buildWidth += tryHt + 5; // 5px for iframe border
							   calcNewWidth = false;
						   } else {
							   buildWidth += mult * newWidths[p] + 14;
							   calcNewWidth = true;
						   }
						   msg = '<p> for p = ' + p + ', bld = ' + buildWidth + '</p>';
						   $('#dbug').append(msg);
						   if ( buildWidth > rowLineWidth ) {
							   // previous value of tryHt ( = optHeight ) was good
							   tryHt = optHeight;
							   marg = 99; // break out of the "while" - no more tries
							   break; // out of FOR loop
						   }
						   // capture the new picWidth for html before trying hext height
						   if ( calcNewWidth ) {
								picWidths[curPic+p] = mult * newWidths[p];
						   }
						   marg = rowLineWidth - buildWidth;
					   }  // end FOR
				   
					   buildWidth = 0; // start over for next attempt in "while"
					   optHeight = tryHt; // this represents the last successful try
					   if ( optHeight > maxHeight ) {
						   maxHeight = optHeight;
					   }
			   
				   }  //end WHILE
				   msg = '<p>New Row Ht: ' + optHeight + '</p>';
				   $('#dbug').append(msg);
				   return parms = [picsInRow, optHeight, inclFrame, marg];
				}  // end ELSE - try slow grow...
			}  // end ELSE - within bounds to try growing
		}  // end FUNCTION optRowHt()
		
		/* ****** SECTION 4: function to build html from row created by optRowHt() 
	    NOTE: Safari can't extract widths implicitly, so width explicitly provided */
		function bldRow(noOfPix, rowHt, isLast) {
			var floorWidth;
			var floorHeight = Math.floor(rowHt);
			pic += '\n<div id="row' + imgRowNo + '" class="ImgRow">';
			imgRowNo++;
			if ( isLast == true ) { // 0 to n images + iframe
				if ( noOfPix == 0 ) {  // iframe only - no images
					msg = '<p>Input rowHt = ' + floorHeight + '</p>';
					$('#dbug').append(msg);
					pic += '\n\t<iframe id="theMap" height="' + floorHeight +
						   '" width="' + floorHeight + '" src="../maps/' + geoMap +
						   '"></iframe>\n</div>';
				} else {  // images + iframe
					for ( var q=0; q<noOfPix; q++ ) {
						// first, place image(s)
						floorWidth = Math.floor(picWidths[picId]);
						msg = '<p>[HB] LAST: FRAME w/PIX; q = ' + q + '; noOfPix = '
							+ noOfPix + '</p>';
						$('#dbug').append(msg);
						pic += '\n\t<img id="pic' + picId + '" height="' + floorHeight + 
							'" width="' + floorWidth + '" src="' + nSize[picId] +
							'" alt="' + picDescs[picId] + '" />';
						cap += '\n\t\t<li>' + captions[picId] + '</li>';
						lnk += '\n\t\t<li>' + albPics[picId] + '</li>';
						picId++;
					} // end FOR, next, place iframe
					pic += '\n\t<iframe id="theMap" height="' + floorHeight +
						   '" width="' + floorHeight + '" src="' + geoMap +
						   '"></iframe>\n</div>';
				} // end IF-ELSE
			} else { // images only loop:
				for ( var r=0; r<noOfPix; r++ ) {
					floorWidth = Math.floor(picWidths[picId]);
					pic += '\n\t<img id="pic' + picId + '" height="' + floorHeight + 
						'" width="' + floorWidth + '" src="' + nSize[picId] + 
						'" alt="' + picDescs[picId] + '" />';
					cap += '\n\t\t<li>' + captions[picId] + '</li>';
					lnk += '\n\t\t<li>' + albPics[picId] + '</li>';
					picId++;
				}
				pic += '\n</div>';
			} // end IF-ELSE
		} // end FUNCTION bldRow()
		
		/* ****** SECTION 5: Function to test load a picture and capture its width:
       recursive calls are made in order to process all the named pics in the
       sequence specified by the html */
		function ldNewPic() {
			msg = '<img height="' + rowHeight + '" src="' + nSize[curPic] + 
					'" alt="" />';
			$('#picload').html(msg);
		
			$('img').on('load', function() {
				picWidths[curPic] = $(this).width();
				msg	= '<p>Width for image ' + curPic + ' is ' + picWidths[curPic] + '</p>';
				$('#dbug').append(msg);
				curPic++;
				if ( curPic < noOfPics ) {
					ldNewPic();
				} else { 
				
					/* ****** SECTION 6: when loads are done, create the html - row
					   by row; include captions, picture links & optional chart */
					$('img').off();
					if ( eopt > 0 ) {  // chart(s) treated differently - no popup
						noOfPics -= eopt; // however, width has been captured
					}
					cap = '<div class="captionList">\n\t<ol>';
					lnk = '<div class="lnkList">\n\t<ol>';
					msg = '<p>Completed loads...begin optimizing rows;</p>' +
						'<p>Row optimizer [RO] returns [prior to sizing]: no of pix in the row, '
						+ 'if iframe is present, pixel width of space consumed, and'
						+ ' left-over space to edge (for 960-wide rows);</p>';
					msg += '"Return values" are: no. of images placed, row height used '
						  + 'to create row, whether iframe is present, and next pic '
						  + 'to be placed, if any remain</p>';
					$('#dbug').append(msg);
					// set parameters for first invocation of optRowHt()
					curPic = 0;
					fctArgs[2] = false;
					pic = '';
					while ( fctArgs[2] == false ) {
						fctArgs = optRowHt(); // invoke row-optimizer function
						curPic += fctArgs[0];
						rowMargin = fctArgs[3];
						msg = '<p>Return values: ' + fctArgs[0] + '; ' + fctArgs[1] + 
							'; ' + fctArgs[2] + '; new picNo is ' + curPic +
							'; margin = ' + fctArgs[3] + '</p>';
						$('#dbug').append(msg);
						bldRow(fctArgs[0], fctArgs[1], fctArgs[2]); // invoke html builder
					}
				
					// if Elevation Charts are present, add them
					if ( eopt > 0 ) {
						msg = '<p>Add graph(s) here if present</p>';
						$('#dbug').append(msg);
						// don't grow the charts: they are relatively large already,
						// and if in row w/soloMap, shrink map to chart-size
						var availMarg = fctArgs[3];
						for ( gno=0; gno<eopt; gno++ ) {
							var chartWidth = picWidths[noOfPics+gno];
							// now see if graph fits on last row...
							if ( chartWidth + 7 <= availMarg ) {
								// ok, append graph
								msg = '<p>Chart ' + gno + ' appended to last row</p>';
								$('#dbug').append(msg);
								msg = '\n\t<img class="chart" height ="' + rowHeight +
										'" width="' + chartWidth + '" src="../images/' +
										egraph[gno] + '" alt="Elevation Chart" />';
								msg += '\n</div>';
								/* When the only other item in this row is a map,
								   adjust the map size down to 'rowHeight', so that
								   the chart doesn't grow */
								if ( soloMap ) {
									// find the iframe in the html code, extract height param.
									var frmIndx = pic.indexOf('iframe');
									var htIndx = frmIndx + 27;
									var closeIndx = pic.indexOf('</iframe');
									var oldHt = pic.substring(htIndx,htIndx+3);
									var srchStr = pic.substring(frmIndx,closeIndx);
									// replace the oldHt with rowHeight
									var replStr = srchStr.replace(oldHt,rowHeight);
									replStr = replStr.replace(oldHt,rowHeight);
									pic = pic.replace(srchStr,replStr);
								}
								pic = pic.substring(0,pic.length-7) + msg;
								availMarg -= (chartWidth + 7);
							} else {
								/* new row for graph:
									** ASSUMES 2 GRAPHS WONT FIT ON SAME ROW */
								msg = '<p>Solo row for elevation chart</p>';
								$('#dbug').append(msg);
								imgRowNo++;
								msg = '\n<div id="row' + imgRowNo + '" class="ImgRow Solo">'
									+ '\n\t<img class="chart" height="' + rowHeight + 
									'" width="' + chartWidth + '" src="../images/' +
									egraph[gno] + '" alt="Elevation Chart" />\n</div>';
								pic += msg;
							}
						} // end for loop
					}  // end if eopt > 0 [Elevation Chart processing]
				
					cap += '\n\t</ol>\n</div>';
					lnk += '\n\t</ol>\n</div>';
					pic += '\n' + cap + '\n' + lnk;
					download(pic,'newPage.txt','text/plain');
					$('#theRows').html(pic);
					$('img').addClass('newPics'); 
					$('iframe').addClass('geomap');
				}
			});
		}  // end of function 'ldNewPic()'
	
		if ( eopt > 0 ) {
			for ( var nog=0; nog<eopt; nog++ ) {
				nSize[noOfPics] = egraph[nog];
				noOfPics++;  // add the chart images into the count before sizing all
			}
		}
		ldNewPic(); // I don't like using recursive calls, but it works:
		
	} // END OF SUCCESSFUL 'GET' OF TSV FILE; xhr.status === 200
	
	if ( xhr.status === 404 ) {
		outTxt = '<p>URL NOT FOUND (ye olde message)</p>';
		$('#dbug').append(outTxt);
	}
	
	if (xhr.status === 500 ) {
		outTxt = '<p>Some kind of internal server error...</p>';
		$('#dbug').append(outTxt);
	}
}  // END OF 'onload' FUNCTION


xhr.open('GET',tsvFile,true);
xhr.send(null);

