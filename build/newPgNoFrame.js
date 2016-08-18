$( function() {

// Generic used to create html content (string)
var pic;    // html for row of pictures
var cap;    // html for row of corresponding captions
var lnk;    // html for links to Flickr ablum pictures
// Generic to create debug messages
var msg;

// Get info from html elements:
var $fotos = $('#photos>li');
var noOfPics = $fotos.length;
var htmlPicNames = new Array();
var tsvFile = '../gpsv/' + $('#tsvFile').text();
var noOfPixWCaps = parseFloat( $('#noOfOthr').text() );
var $imgWCaps = $('#othrImg>li');
var $imgCapts = $('#imgCaptions>li');
var noOfNoCaps = parseFloat( $('#noOfNons').text() );
var $imgNoCaps = $('#nonCaps>li');
var totalImgCnt = noOfPics + noOfPixWCaps + noOfNoCaps;
msg = '<p>' + totalImgCnt + '</p>';
$('#tmp_dump_area').append(msg);

// For reading in the GPSVinput.tsv data:
var picNames = new Array();
var picDescs = new Array();
var picDates = new Array();
var picYear = new Array();
var picMonth = new Array();
var picDay = new Array();
var nSize = new Array();
var albPics = new Array();
var gpsv_data;
var gpsv_array = new Array();
var mo;
var countAdj;

// For calculating image sizes & captions
var rowHeight = 260;  // starting point only
var maxHeight = rowHeight; // will track the biggest optimization
var picWidths = new Array();
var rowPicWidth = $('.bodyBox').css('width');
var rowChIndx = rowPicWidth.indexOf('px');
var rowLineWidth = rowPicWidth.substring(0,rowChIndx) - 14;
var rowMargin;
var curPic = 0;
var curRowWidth = 0;
var imgRowNo = 0;
var captions = new Array();
var fctArgs = new Array();
var picId = 0;
var newHt;

msg = '<p><strong>Start javascript output here...</strong></p><p>Number of html pic names read in: ' +
    noOfPics + '</p>';
$('#tmp_dump_area').append(msg);

jQuery.get(tsvFile, function(txt_data) {
    
    /* SECTION 1: Acquire the tsvFile and process it for names, descriptions,
       album links & dates */
    gpsv_data = txt_data;
	var txtLength = gpsv_data.length;
	// determine the number of fields in the header line
	var hdrIndx = gpsv_data.indexOf('\n');
	// the first line of info reveals the columns in play
	var dataStrt = hdrIndx + 1;
	var hdrData = gpsv_data.substring(0,hdrIndx);
	var hdrArray = hdrData.split('\t');
	var hdrFlds = hdrArray.length;
	if ( hdrFlds != 5 ) {
	    msg = '<p>INCORRECT NUMBER OF HEADERS - CHECK .tsv FILE!'
	    $('#tmp_dump_area').append(msg);
	}
	// form the useful data into an array of pieces
	gpsv_data = gpsv_data.substring(dataStrt,txtLength);
	gpsv_data = gpsv_data.replace(/\n/g,'\t');
	gpsv_array = gpsv_data.split('\t');
	/* NOTE: there appears to be an anomaly whereby the last field in the gpsv
	   array (created by mkpgfile) can SOMETIMES be an unrecognizable element,
	   perhaps EOF or other. To account for this anomaly, a check is performed
	   to see if the expected number of elements is present, or one more than
	   that...  */
	if ( gpsv_array.length % hdrFlds == 0 ) {
	    msg = '<p>Expected number of fields present</p>';
	    $('#tmp_dump_area').append(msg);
	    countAdj = 0;
	} else {
    	if ( gpsv_array.length % hdrFlds == 1 ) {
    	    msg = '<p>Oddball field detected at end of gpsv array</p>';
    	    $('#tmp_dump_area').append(msg);
    	    countAdj = -1;
    	} else {
    	    msg = '<p>Array is off by ' + (gpsv_array.length) % hdrFlds;
    	    msg += '; program may fail to execute</p>';
    	    $('#tmp_dump_area').append(msg);
	    }
	}
	var i = 0;
	// start the loop at the first array element AFTER the hdrFlds
	for ( var j = 0; j < gpsv_array.length + countAdj; j+=5 ) {
			picNames[i] = gpsv_array[j];
			picDescs[i] = gpsv_array[j+1];
			albPics[i] = gpsv_array[j+2];
			picDates[i] = gpsv_array[j+3];
			nSize[i] = gpsv_array[j+4];
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
	}  // end of for loop
	var k = 0;
	$fotos.each( function() {
	    htmlPicNames[k] = this.textContent;
	    k++;
	});
    for ( r=0; r<i; r++ ) {
        msg = '<p>' + picNames[r] + ', ' + picDescs[r] + ', ' + albPics[r] +
                ', ' + nSize[r] + ', ' + captions[r] + '<p>';
        $('#tmp_dump_area').append(msg);
    }
    
    /* ****** SECTION 2: correlate the html-specified pix with the loaded array */
	msg = '<p>NO OF ARRAY ELEMENTS READ IN: ' + i + '</p>';
	$('#tmp_dump_area').append(msg);
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
	for ( var t=0; t<noOfPics; t++ ) {
	    msg += iVals[t] + ', ';
	}
	msg += '</p>';
	$('#tmp_dump_area').append(msg);
	for ( w=0; w<noOfPics; w++ ) {
	    picNames[w] = tmp_names[w];
	    picDescs[w] = tmp_descs[w];
	    albPics[w] = tmp_albs[w];
	    nSize[w] = tmp_nsize[w];
	    captions[w] = tmp_caps[w];
	}
	/* PREP: "add" all the images together for loading & measuring */
    // first, add images that have captions
    // k is next pic no.
    if ( noOfPixWCaps > 0 ) {
        for ( var s=0; s<noOfPixWCaps; s++ ) {
            nSize[k+s] = '../images/' + $imgWCaps[s].textContent;
            captions[k+s] = $imgCapts[s].textContent;
            picDescs[k+s] = 'non-photo, no album link';
        }
    }
    k += noOfPixWCaps;
    msg = '<p>Current value of i after pix w/caps added: ' + i + '</p>';
    $('#tmp_dump_area').append(msg);
    // next, add images that don't have captions
    if ( noOfNoCaps > 0 ) {
        for ( var d=0; d<noOfNoCaps; d++ ) {
            nSize[k+d] = '../images/' + $imgNoCaps[d].textContent;
            msg = '<p>nSize for non-cap: ' + nSize[i+d] + '</p>';
            $('#tmp_dump_area').append(msg);
            // dont bother filling in captions, descs, etc.
        }
    }
    // END PREP
	
	/* ****** SECTION 3: function to "grow" row height in order to optimize
	   space utilization: tabulates next row (one row only) and returns to caller
	   with array "parms": #pix in the new row; row height; remaining margin */
	function optRowHt() { 
        var parms;
        var marg;
        var picsInRow = 0;
        var optHeight = rowHeight; // leave rowHeight intact
        // this function uses newWidths to preserve original picWidths
        var newWidths = new Array();
	    var buildWidth = 0;
	    // see what fits "as is":
	    for (var n=curPic; n<totalImgCnt; n++ ) {
           newWidths[picsInRow] = picWidths[n];
           buildWidth += newWidths[picsInRow] + 14; // 14px for borders + margins
           picsInRow++;
	        if ( buildWidth > rowLineWidth ) {
                picsInRow--;
                buildWidth -= (newWidths[picsInRow] + 14);
	            break;
	       }
	    } // end FOR
	    // calculate remaining white space in this row
	    marg = rowLineWidth - buildWidth; 
	    msg = '<p>[RO] (starting point) # pix = ' + picsInRow + '; row width = ' +
	            buildWidth + '; and margin = ' + marg + '</p>';
	    $('#tmp_dump_area').append(msg);

	    // new rowHeight ONLY IF: current margins > 80px 
	    if ( marg < 80 ) {
	       return parms = [picsInRow, optHeight, marg];
	    } else {
            if ( marg > 310 ) { // at least grow it a little bit!
                optHeight = 1.4 * rowHeight;
                return parms = [picsInRow, optHeight, marg];
            } else {
    	       // try increasing rowHeight by 10% per try until optimally filled
    	       itmCnt = picsInRow;
               var mult = 1.00;
               var tryHt;
    	       buildWidth = 0;
    	       while ( marg >= 100 ) {
    	           mult = 1.1 * mult;
    	           tryHt = mult * rowHeight;
    	           msg = '<p>Current height being attempted: ' + tryHt + '</p>';
    	           $('#tmp_dump_area').append(msg);
    	           for ( var p=0; p<itmCnt; p++ ) {
    	               buildWidth += mult * newWidths[p] + 14;
    	               msg = '<p> for p = ' + p + ', bld = ' + buildWidth + '</p>';
    	               $('#tmp_dump_area').append(msg);
    	               if ( buildWidth > rowLineWidth ) {
    	                   // previous value of tryHt ( = optHeight ) was good
    	                   tryHt = optHeight;
    	                   marg = 99; // break out of the "while" - no more tries
    	                   break; // out of FOR loop
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
    	       $('#tmp_dump_area').append(msg);
    	       return parms = [picsInRow, optHeight, marg];
            }  // end ELSE try slow grow...
	    }  // end ELSE within bounds to try growing
	}  // end optRowHt FUNCTION */
	
	/* ****** SECTION 4: function to build html from row created by optRowHt() */
	function bldRow(noOfPix, rowHt) {
	    /* curPic - arg(noOfPix) is the 1st image# of this row:
	        determine if there is a "non-photo" image in this row; if so,
	        provide a different img id: "capImg" for image w/cap, "noCapImg" for
	        non-captioned images; js file will handle each case differently.
	        1st non-photo = noOfPics */
        pic += '\n<div id="row' + imgRowNo + '" class="ImgRow">';
        imgRowNo++;
        var growth = rowHt/rowHeight;
        var floorHeight = Math.floor(rowHt);
        var newWidth;
        var floorWidth;
        var imNo = curPic - noOfPix; // 1st image in this row
	    for ( var br=0; br<noOfPix; br++ ) {
    	    msg = '<p>imNo + br = ' + (imNo + br) + '</p>';
    	    $('#tmp_dump_area').append(msg);
    	    newWidth = growth * picWidths[imNo+br];
    	    floorWidth = Math.floor(newWidth);
    	    if ( (imNo + br) >= noOfPics ) { // this is a non-photo
                if ( (imNo + br) >= (noOfPics + noOfPixWCaps) ) { // this is a no-cap
                    pic += '\n\t<img id="noCapImg' + picId + '" height="' + floorHeight + 
                           '" width="' + floorWidth + '" src="' + nSize[imNo+br] + '" alt="Non-Captioned" />';
                } else {    // this is a non-photo w/cap
                    pic += '\n\t<img id="capImg' + picId + '" height="' + floorHeight +
                           '" width="' + floorWidth + '" src="' + nSize[imNo+br] + '" alt="' +
                           picDescs[imNo+br] + '" />';
                    cap += '\n\t\t<li>' + captions[imNo+br] + '</li>';
                }
    	    } else { // photo 
                pic += '\n\t<img id="pic' + picId + '" height="' + floorHeight + 
                    '" width="' + floorWidth + '" src="' + nSize[picId] + '" alt="' + picDescs[picId] + '" />';
                    cap += '\n\t\t<li>' + captions[picId] + '</li>';
                    lnk += '\n\t\t<li>' + albPics[picId] + '</li>';
    	    }
    	    picId++;
	    }
	    pic += '\n</div>';
	} // end FUNCTION bldRow
	
	/* ****** SECTION 5: function test loads a picture and captures its width:
       recursive calls are made in order to process all the named pics in the
       sequence given */
	function ldNewPic() {
	    msg = '<img height="' + rowHeight + '" src="' + nSize[curPic] + 
	            '" alt="" />';
    	$('#picload').html(msg);
    	
    	$('img').on('load', function() {
            picWidths[curPic] = $(this).width();
            msg	= '<p>Width for image ' + curPic + ' is ' + picWidths[curPic] + '</p>';
            $('#tmp_dump_area').append(msg);
            curPic++;
            if ( curPic < totalImgCnt ) {
                ldNewPic();
            } else { 
                
                /* ****** SECTION 6: when loads are done, create rows with
                   row height = "seed height" (rowHeight), then optimize each
                   row height to better fill the row (960px); create the html
                   for each row: include captions, picture links & optional images */
                $('img').off();
	            cap = '<div class="captionList">\n\t<ol>';
	            lnk = '<div class="lnkList">\n\t<ol>';
	            msg = '<p>Completed loads...begin optimizing rows;</p>' +
	                '<p>Row optimizer [RO] start: no of pix in the row, '
	                + 'row height used, and margin to edge (for 960px rows)</p>';
	            $('#tmp_dump_area').append(msg);
	            // fctArgs[0=no of pix in row; 1=new height; 2=margin]
                // set parameters for first invocation of optRowHt()
                curPic = 0;
                pic = '';
                while ( curPic < totalImgCnt ) {
                    // row-optimizer doesn't care if captioned pics or not;
                    fctArgs = optRowHt();
                    curPic += fctArgs[0]; // curPic now points to the next image
                    msg = '<p>Return values: ' + fctArgs[0] + '; ' + fctArgs[1] + 
                        '; ' + fctArgs[2] + '; new picNo is ' + curPic + '</p>';
                    $('#tmp_dump_area').append(msg);
                    // inputs to bldRow: (no of pix in row, row height)
                    bldRow(fctArgs[0], fctArgs[1]); // invoke html builder
                }
                cap += '\n\t</ol>\n</div>';
                lnk += '\n\t</ol>\n</div>';
                pic += '\n' + cap + '\n' + lnk;
                
                /* ****** SECTION 7: function to download the text file to
                   local computer */
            	function download(strData, strFileName, strMimeType) {
                    var D = document,
                        A = arguments,
                        a = D.createElement("a"),
                        d = A[0],  // 1st arg = strData (text data to send)
                        n = A[1],  // 2nd arg = strFileName (name to send to computer)
                        t = A[2] || "text/plain";
                
                    //build download link:
                    a.href = "data:" + strMimeType + "charset=utf-8," + escape(strData);
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
                }
                download(pic,'newPage.txt','text/plain');
                $('#theRows').html(pic);
                $('img').addClass('newPics'); 
                $('iframe').addClass('geomap');
            } // end ELSE - photo loads complete
	    });
	}  // end of function 'ldNewPic()'

	ldNewPic(); // I don't like using recursive calls, but it works:
	// needed to wait for each picture to load before loading its successor
})
.fail( function() {
	document.getElementById('tmp_dump_area').textContent = 'FAILED TO GET GPSV DATA';
});



});