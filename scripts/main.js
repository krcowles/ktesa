$( function() {  // wait until document is loaded...
// ------------------

var msg;
var indx;
var noPart1;
var noPart2;

// Establish the compare method (object)
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
};  // end of object declaration

msg = ' >> starting javascript <<';
$('#dis').append(msg);

$('.sortable').each(function() {
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
	
}); // end '.sortable each' loop


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
	$('#metric').text(state); // new button text
	$erows.each( function() {
		// index 2 is column w/distance units (miles/kms)
		// ASSUMPTION: always less than 1,000 miles or kms!
		tmpUnits = $(this).find('td').eq(2).text();
		tmpConv = parseFloat(tmpUnits);
		tmpConv = dist * tmpConv;
		tmpUnits = tmpConv.toFixed(1);
		tmpUnits = tmpUnits + ' ' + newDist;
		$(this).find('td').eq(2).text(tmpUnits);
		// index 3 is column w/elevation units (ft/m)
		tmpUnits = $(this).find('td').eq(3).text();
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
		tmpUnits = tmpConv.toFixed(0);
		tmpUnits = tmpUnits + ' ' + newElev;
		$(this).find('td').eq(3).text(tmpUnits);
		
	});  // end 'each erow'
	
	
}); // end of click on metric */
	
// ---------------
});  // end of 'wait til document loaded'
