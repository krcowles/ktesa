/* -------- THIS SCRIPT EXECUTES DYNAMIC TABLE SIZING WHEN TABLES ARE PRESENT -------- */	
	
//global vars:
var tblHtml; // this will hold an html "wrapper" for rows id'd for inclusion by the viewport
var endTbl;  // the closing part of the wrapper

// global object used to define how table items get compared in a sort:
var noPart1;
var noPart2;
var compare = {
    std: function(a,b) {	// standard sorting - literal
        if ( a < b ) {
            return -1;
        } else {
            return a > b ? 1 : 0;
        }
    },
    lan: function(a,b) {    // "Like A Number": extract numeric portion for sort
        // commas allowed in numbers, so;
        var indx = a.indexOf(',');
        if ( indx < 0 ) {
            a = parseFloat(a);
        } else {
            noPart1 = parseFloat(a);
            msg = a.substring(indx + 1, indx + 4);
            noPart2 = msg.valueOf();
            a = noPart1 + noPart2;
        }
        indx = b.indexOf(',');
        if ( indx < 0 ) {
            b = parseFloat(b);
        } else {
            noPart1 = parseFloat(b);
            msg = b.substring(indx + 1, indx + 4);
            noPart2 = msg.valueOf();
            b = noPart1 + noPart2;
        }
        return a - b;
    } 
};  // end of COMPARE object

var refRows; // needs to be global to effect dynamic table construction & sorts
var useTbl = $('title').text() == 'Hike Map' ? false : true;
if ( useTbl ) {
    /* INITIAL PAGE LOAD OF USER TABLE */
    tblHtml = '<table class="msortable">';
    tblHtml += $('table').html();
    var indx = tblHtml.indexOf('<tbody') + 7;
    tblHtml = tblHtml.substring(0,indx);  // strip off the main body (after colgrp & hdr)
    endTbl = ' </tbody> </table>';
    $tblRows = $('#refTbl tbody tr');
    refRows = $tblRows.toArray();
    var iCnt = refRows.length;
    var fullTbl = new Array();
    for ( var x=0; x<iCnt; x++ ) {
        // every row will be used on initial page load, so create a full sequential array:
        fullTbl[x] = x;
    }
    formTbl( iCnt, fullTbl ); // form the usrTbl - variably sized later
}

// Create the html for the viewport table, using the rows identified in "tblRowsArray"
//   arg of the function below   
function formTbl ( noOfRows, tblRowsArray ) {
	// HTML CREATION:
	var thisTbl = tblHtml + ' <tr>';
	var indxRow;
	for (var m=0; m<noOfRows; m++) {
		indxRow = tblRowsArray[m];
		thisTbl += refRows[indxRow].innerHTML;
		//thisTbl += $tblRows.eq(indxRow).html();
		thisTbl += ' </tr> ';
	}
	thisTbl += endTbl;
	$('#usrTbl').html(thisTbl);
	$('#metric').css('display','block');
	// ADD SORT FUNCTIONALITY ANEW FOR EACH CREATION OF TABLE:
	$('.msortable').each(function() {
		var $table = $(this); 
		var $tbody = $table.find('tbody');
		var $controls = $table.find('th'); // store all headers
		var trows = $tbody.find('tr').toArray();  // array of rows

		$controls.on('click', function() {
			var $header = $(this);
			var order = $header.data('sort');
			var column;

			// IF defined for selected column, toggle ascending/descending class
			if ( $header.is('.ascending') || $header.is('.descending') ) {
				$header.toggleClass('ascending descending');
				$tbody.append(trows.reverse());
			} else {
			// NOT DEFINED - add 'ascending' to current; remove remaining headers' classes
				$header.addClass('ascending');
				$header.siblings().removeClass('ascending descending');
				if ( compare.hasOwnProperty(order) ) {
					column = $controls.index(this);  // index into the row array's data
					trows.sort(function(a,b) {
						a = $(a).find('td').eq(column).text();
						b = $(b).find('td').eq(column).text();
						return compare[order](a,b);
					});
					$tbody.append(trows);
				} // end if-compare
			} // end else
		}); // end on.click
	}); // end '.msortable each' loop
}  // end of FORMTBL function

var lgth_hdr; // column number containing 'Length'
var elev_hdr; // column number containing 'Elev Chg'
var $htable = $('table thead');
var $hdrs = $htable.eq(0).find('th');
$hdrs.each( function(indx) {
	if ($(this).text() === 'Length') {
		lgth_hdr = indx;
	}
	if ($(this).text() === 'Elev Chg') {
		elev_hdr = indx;
	}
});
// Event Delegation for metric conversion button
$(document).on('click', '#metric', function() {
	// table locators:
	var $etable = $('table');
	var $etbody = $etable.find('tbody');
	var $erows = $etbody.find('tr');
	var state = this.textContent;
	// conversion variables:
	var tmpUnits;
	var tmpConv;
	var newDist;
	var newElev;
	var dist;
	var elev;
	// determine which state to convert from
	var mindx = state.indexOf('metric');
	if ( mindx < 0 ) { // currently metric; convert TO English
		newDist = 'miles';
		newElev = 'ft';
		state = state.replace('English','metric');
		dist = 0.6214;
		elev = 3.278;
	} else { // currently English; convert TO metric
		newDist = 'kms';
		newElev = 'm';
		state = state.replace('metric','English');
		dist = 1.61;
		elev = 0.305;
	}
	$('#metric').text(state); // new data element text
	$erows.each( function() {
		// ASSUMPTION: always less than 1,000 miles or kms!
		tmpUnits = $(this).find('td').eq(lgth_hdr).text();
		tmpConv = parseFloat(tmpUnits);
		tmpConv = dist * tmpConv;
		var indxLoc = tmpUnits.substring(0,2);
		if ( indxLoc === '0*' ) {
			tmpUnits = '0* ' + newDist;
		} else {
			tmpUnits = tmpConv.toFixed(1);
			tmpUnits = tmpUnits + ' ' + newDist;
		}
		$(this).find('td').eq(lgth_hdr).text(tmpUnits);
		// index 4 is column w/elevation units (ft/m)
		tmpUnits = $(this).find('td').eq(elev_hdr).text();
		// need to worry about commas...
		mindx = tmpUnits.indexOf(',');
		if ( mindx < 0 ) {
			tmpConv = parseFloat(tmpUnits);
		} else {
			noPart1 = parseFloat(tmpUnits);
			noPart2 = tmpUnits.substring(mindx + 1,mindx + 4);
			noPart2 = noPart2.valueOf();
			tmpConv = noPart1 + noPart2;
		}
		tmpConv = dist * tmpConv;
		indxLoc = tmpUnits.substring(0,2);
		if ( indxLoc === '0*' ) {
			tmpUnits = '0* ' + newElev;
		} else {
			tmpUnits = tmpConv.toFixed(0);
			tmpUnits = tmpUnits + ' ' + newElev;
		}
		$(this).find('td').eq(elev_hdr).text(tmpUnits);
	});  // end 'each erow'	
});
// Function to find elements within current bounds and display them in a table
function IdTableElements(boundsStr) {
    // ESTABLISH CURRENT VIEWPORT BOUNDS:
    var beginA = boundsStr.indexOf('((') + 2;
    var leftParm = boundsStr.substring(beginA,boundsStr.length);
    var beginB = leftParm.indexOf('(') + 1;
    var rightParm = leftParm.substring(beginB,leftParm.length);
    var south = parseFloat(leftParm);
    var north = parseFloat(rightParm);
    var westIndx = leftParm.indexOf(',') + 1;
    var westStr = leftParm.substring(westIndx,leftParm.length);
    var west = parseFloat(westStr);
    var eastIndx = rightParm.indexOf(',') + 1;
    var eastStr = rightParm.substring(eastIndx,rightParm.length);
    var east = parseFloat(eastStr);
    var hikeSet = new Array();
    var tblEl = new Array(); // holds the index into the row number array: tblRows
    var pinLat;
	// REMOVE previous items:
	if ($('#nohikes').length) {
		$('#nohikes').remove();
	}
	$('div #usrTbl').replaceWith('<div id="usrTbl"></div>');
    /* FIND HIKES WITHIN THE CURRENT VIEWPORT BOUNDS */
    var n = 0;
    var rowCnt = 0;
    var $allRows = $('#refTbl tbody tr');
    for (i=0; i<$allRows.length; i++) {
        var tmpLat = $($allRows[i]).data('lat');
        var tmpLng = $($allRows[i]).data('lon');
        pinLat = parseFloat(tmpLat);
        pinLon = parseFloat(tmpLng);	
        if( pinLon <= east && pinLon >= west && pinLat <= north && pinLat >= south ) {
            tblEl[n] = i;
            n++;
            rowCnt ++;
        }	
    }
    if ( rowCnt === 0 ) {
		msg = '<p id="nohikes" style="color:brown;margin-left:24px;">NO hikes in this area</p>';
		$('#usrTbl').after(msg);
        formTbl( 0, tblEl );
    } else {
        formTbl( rowCnt, tblEl );
    }
} // END: IdTableElements() FUNCTION

// Process events when the 'mapit' icon is clicked:
$rows = $('#refTbl tbody tr');
$('.gotomap').each( function() {
	$(this).css('cursor', 'pointer');
});
$(document).on('click', '.gotomap', function(ev) {
	var hno = parseInt($(this).attr('id')) - 1;
	$tr = $rows.eq(hno);
	var hlat = parseFloat($tr.data('lat'));
	var hlon = parseFloat($tr.data('lon'));
	var zoomOn = {lat: hlat, lng: hlon};
	map.setCenter(zoomOn);
	map.setZoom(13);
	window.scrollTo(0, 0);
});
