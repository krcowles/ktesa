// Generic used to create html content (string)
var pic;    // html for row of pictures
var cap;    // html for row of corresponding captions
// Generic to create debug messages
var msg;

// Get info from html elements:
var $htmlNameLocs = $('li'); // an array holding the list element locations
var noOfPics = $htmlNameLocs.length;
var htmlPicNames = new Array();
var geoMap = $('#frm_name').text();
var tsvFile = $('#tsv_file').text();

// For reading in the GPSVinput.tsv data:
var picNames = new Array();
var picDescs = new Array();
var picDates = new Array();
var picYear = new Array();
var picMonth = new Array();
var picDay = new Array();
var mo;
var gpsv_data;
var gpsv_array = new Array();

// For calculating image sizes & captions
var rowHeight = 260; 
var picWidths = new Array();
var rowPicWidth; // bodyBox row width in pixels ('px' is part of the returned value)
var rowChIndx; // index of 'px' in above
var rowLineWidth; // numeric part of rowPicWidth
var curPic = 0;
var curRowWidth;
var imgRowNo = 0;
var captions = new Array();

// add error checking for presence of geomap & tsvFile (length > 1) 
msg = '<p><strong>Start javascript output here...</strong></p><p>Number of pic names read in: ' +
    $htmlNameLocs.length + '</p>';
$('#tmp_dump_area').append(msg);
// Get the creator-specified photo names from the html:
for ( var m=0; m<noOfPics; m++ ) {
	htmlPicNames[m] = $htmlNameLocs[m].innerText;
}

/* Read in the GPSVinput.tsv file:
  NOTE: Only if a line in  GPSVinput.tsv does NOT contain name & desc does this process fail
  and that is highly unlikely, since a default for a desc is "Enter Description Here"
  and the names come right from the Flickr album, which at least have a default name... */
jQuery.get(tsvFile, function(txt_data) {
	gpsv_data = txt_data;
	var txtLength = gpsv_data.length;
	// determine the number of fields in the header line
	var hdrIndx = gpsv_data.indexOf('\n');
	var dataStrt = hdrIndx + 1;
	var hdrData = gpsv_data.substring(0,hdrIndx);
	var hdrArray = hdrData.split('\t');
	var hdrFlds = hdrArray.length;
	// form the useful data into an array of pieces
	gpsv_data = gpsv_data.substring(dataStrt,txtLength);
	gpsv_data = gpsv_data.replace(/\n/g,'\t');
	gpsv_array = gpsv_data.split('\t');
	// gpsv_array[0] should always indicate the first field of any line
	// even if the previous line had missing data. This element is normally 'Folder1'
	var i = 0;
	var k = 0;
	// start the loop at the first array element AFTER the hdrFlds
	for ( var j = 0; j < gpsv_array.length; j++ ) {
		if ( gpsv_array[j] == gpsv_array[0] ) {
			picNames[i] = gpsv_array[j+1];
			picDescs[i] = gpsv_array[j+2];
			// date info is the last field prior to the next ~ gpsv_array[0] 
			for ( k = j + 2; k < gpsv_array.length; k++ ) {
				if ( k == gpsv_array.length - 1 ) {
					picDates[i] = gpsv_array[k];
				} else {
					if ( gpsv_array[k] == gpsv_array[0] ) {
						picDates[i] = gpsv_array[k-1];
					}
				}
			}
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
		}  // end of if statement (gpsv_array[j] == gpsv_array[0])
	}  // end of for loop
	
	// this function will test load a picture, capture the width, then form
	// the html string to eventually be used in the new site
	// it calls itself in order to process all the named pics in the sequence given
	function ldNewPic() {
	    pic = '<img height="' + rowHeight + '" src="' + picNames[curPic] + 
	            '.jpg" alt="" />';
    	$('#picload').html(pic);
    	curPic++;
    	
    	$('img').on('load', function() {
            picWidths[curPic] = $(this).width();
            msg	= '<p>Width for image ' + curPic + ' is ' + picWidths[curPic] + '</p>';
            $('#tmp_dump_area').append(msg);
            if ( curPic < noOfPics ) {
                ldNewPic();
            } else {
                // Get max-width for picture rows
	            rowPicWidth =  $('.bodyBox').css('width');
	            msg = '<p>Specified row width for bodyBox is ' + rowPicWidth;
	            $('#tmp_dump_area').append(msg);
	            rowChIndx = rowPicWidth.indexOf('px');
	            rowLineWidth = rowPicWidth.substring(0,rowChIndx);
	            msg = '<p>Numeric width of image line is ' + rowLineWidth + ' </p>';
	            $('#tmp_dump_area').append(msg);
	            curRowWidth = 0;
	            imgRowNo = 0;
	            pic = '<div class="ImgRow">'; // one class per row
	            cap = '<div class="captionList">\n\t<ol>';
	            // build html, one pic at a time: don't forget borders, etc.
	            // current margins + border = 14px
                for ( var bld=0; bld<noOfPics; bld++ ) {
                    curRowWidth += picWidths[bld+1] + 14;
                    if ( curRowWidth > rowLineWidth ) {
                        imgRowNo++;
                        pic += '\n</div>\n<div class="ImgRow">';
                        curRowWidth = 0;
                    }
                    pic += '\n\t<img id="pic' + bld + '" height="' + rowHeight + '" src="' + picNames[bld] +
                        '.jpg" alt="' + picDescs[bld] + '" />';
                    cap += '\n\t\t<li>' + captions[bld] + '</li>';
                }
                msg = '<p>The number of rows created was ' + imgRowNo + ' prior to iframe</p>';
                $('#tmp_dump_area').append(msg);
                curRowWidth += rowHeight; // will iframe exceed the line width?
                if ( curRowWidth > rowLineWidth ) {
                    imgRowNo++;
                    pic += '\n</div>\n<div class="ImgRow">';
                }
                pic += '\n\t<scan><iframe height="' + rowHeight + '" width="' + rowHeight +
                    '" src="' + geoMap + '"></iframe></scan>\n</div>';
                cap += '\n\t</ol>\n</div>';
                pic += '\n' + cap;
                cap = '\n<div class="popupCap"></div>';
                pic += cap;
                $('#theRows').html(pic);
                $('img').addClass('newPics'); 
                $('iframe').addClass('geomap');
                
                
                // save it to a local file
            	function download(strData, strFileName, strMimeType) {
                    var D = document,
                        A = arguments,
                        a = D.createElement("a"),
                        d = A[0],
                        n = A[1],
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
            }
	    });
	}  // end of function 'ldNewPic()'
	
	ldNewPic(); // I don't like using recursive calls, but it works:
	// needed to wait for each picture to load before loading its successor
	
})
.fail( function() {
	document.getElementById('tmp_dump_area').textContent = 'FAILED TO GET GPSV DATA';
});



