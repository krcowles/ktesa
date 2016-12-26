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

// Get the index table of hikes and place the html in <div id="refTbl">
//var databaseLoc = '../data/hikeDataTbl.html';
var refRows; // needs to be global to effect dynamic table construction & sorts
// flag for which page is invoking this script:
var useTbl = $('title').text() == 'Hike Map' ? false : true;

// php has already put ref table in place...
/*
$.ajax({
	dataType: "html",
	url: databaseLoc,
	type: 'GET',
	success: function(data) {
		$('#refTbl').append($(data));
*/
		if ( useTbl ) {
			// Create the html wrapper that goes around the viewport rows	
				// -- when row-finding is enabled, use the next 2 lines instead...
				//tblHtml = '<table class="msortable" onMouseOver="javascript:findPinFromRow(event);"'
				//tblHtml += ' onMouseOut="javascript:undoMarker();">';
			tblHtml = '<table class="msortable">';
			tblHtml += $('table').html();
			var indx = tblHtml.indexOf('<tbody') + 7;
			tblHtml = tblHtml.substring(0,indx);  // strip off the main body
			endTbl = ' </tbody> </table>';
			endTbl += ' <div> <p id="metric" class="dressing">Click here for metric units</p> </div>';
			// For display, sorting, etc., create the full table, put it in <div id="usrTbl">
			$tblRows = $('#refTbl tbody tr');

			/* THERE IS AN INEXPLICABLE ANOMALY IN THE BEHAVIOR - PERHAPS A FUNCTION OF THE
			ajax PARSER:  THE <tr> ELEMENT *PRIOR TO* <tbody> IS INCLUDED IN THE SELECTION
			'$tblRows', THOUGH IT IS NOT A CHILD OR DESCENDANT OF <tbody>; THIS HAS RESULTED
			IN CODE ADJUSTMENTS, MARKED WITH "HACK" COMMENTS FOR REFERENCE */
			refRows = $tblRows.toArray(); // HACK
			refRows.shift(); // HACK (eliminate hdr from row array)
			//hdrRow = $tblRows[0].innerHTML; // HACK
			tblHtml += $tblRows[0].innerHTML; // HACK: NOTE - this places hdrs in <tbody>; req's sorting HACK
			var iCnt = refRows.length;
			var fullTbl = new Array();
			for ( var x=0; x<iCnt; x++ ) {
				// every row will be used, so create a sequential array:
				fullTbl[x] = x;
			}
			formTbl( iCnt, fullTbl ); // form the usrTbl - variably sized later
		}
/*
	},  // end of SUCCESS reading data
	error: function(xhrStat, errCode, errObj) {
		errmsg = errObj.textContent;
		msg = 'ajax request for hikeDataTbl failed: ' + errmsg;
		window.alert(msg);
	}	
});
*/

// Create the html for the viewport table, using the rows identified in "tblRowsArray"
// arg of the function below   
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
				// HACK
				hdrRow = trows[0];
				trows.reverse();
				trows.pop();
				trows.unshift(hdrRow);
				// END
				$tbody.append(trows);
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
	// ADD METRIC CONVERSION ANEW FOR EACH CREATION OF TABLE:
	$('#metric').on('click', function() {
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
			// index 4 is column w/distance units (miles/kms)
			// ASSUMPTION: always less than 1,000 miles or kms!
			tmpUnits = $(this).find('td').eq(4).text();
			tmpConv = parseFloat(tmpUnits);
			tmpConv = dist * tmpConv;
			var indxLoc = tmpUnits.substring(0,2);
			if ( indxLoc === '0*' ) {
				tmpUnits = '0* ' + newDist;
			} else {
				tmpUnits = tmpConv.toFixed(1);
				tmpUnits = tmpUnits + ' ' + newDist;
			}
			$(this).find('td').eq(4).text(tmpUnits);
			// index 5 is column w/elevation units (ft/m)
			tmpUnits = $(this).find('td').eq(5).text();
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
			$(this).find('td').eq(5).text(tmpUnits);

		});  // end 'each erow'	
	}); // end of click on metric
}  // end of FORMTBL function

// ROW-FINDING FUNCTIONS FOR mouseover TABLE... [not currently enabled]
/*
function findPinFromRow(eventArg) {
	if ( !eventArg ) {
		eventArg = window.event;
	}
	// IE browsers:
	if ( eventArg.srcElement ) {
		getRowNo(eventArg.srcElement);
	} else if ( eventArg.target ) {
		getRowNo(eventArg.target)
	}
}
function getRowNo(El) {
	if ( El.nodeName == "TD" ) {
		El = El.parentNode;
		msg = '<p>Now El is ' + El.nodeName + '; row indx is ' + El.rowIndex;
		var cellDat = El.cells[1].textContent;
		msg += 'w/Cell data = ' + cellDat + '</p>';
		$('#dbug').append(msg);
	} else return;
}
function undoMarker() {
	msg = '<p>Mouse out of row...</p>';
	//$('#features').append(msg);
}
// END OF ROW-FINDING FUNCTIONS
*/

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
	var pinLng;
	// REMOVE previous table:
	$('div #usrTbl').replaceWith('<div id="usrTbl"></div>');
	/* FIND HIKES WITHIN THE CURRENT VIEWPORT BOUNDS */
	// First, check to see if any ctrPinHikes are within the viewport;
	// if so, include them in the table
	var n = 0;
	var rowCnt = 0;
	$('table tbody tr').each( function() {
		pinLat = parseFloat($(this).data('lat'));
		pinLon = parseFloat($(this).data('lon'));	
		if( pinLon <= east && pinLon >= west && pinLat <= north && pinLat >= south ) {
			tblEl[n] = j;
			n++;
			rowCnt ++;
		}	
	});
	if ( rowCnt === 0 ) {
		msg = '<p>NO hikes in this area</p>';;
		$('#usrTbl').append(msg);
	} else {
		formTbl( rowCnt, tblEl );
	}
} // END: IdTableElements() FUNCTION
