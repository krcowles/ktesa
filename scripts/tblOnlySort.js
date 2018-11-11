$( function() {  // wait until document is loaded...

filterSetup();

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

$('.sortable').each( function() {
	var $table = $(this);
	var $tbody = $table.find('tbody');
	var $controls = $table.find('th');
	var rows = $tbody.find('tr').toArray();
	
	$controls.on('click', function() {
		$header = $(this);
		var order = $header.data('sort');
		var column;
		
		if ($header.is('.ascending') || $header.is('descending')) {
			$header.toggleClass('ascending descending');
			$tbody.append(rows.reverse());
		} else {
			$header.addClass('ascending');
			$header.siblings().removeClass('ascending descending');
			if (compare.hasOwnProperty(order)) {  // compare object needs method for var order
				column =$controls.index(this);
				
				rows.sort( function(a,b) {
					a = $(a).find('td').eq(column).text();
					b = $(b).find('td').eq(column).text();
					return compare[order](a,b);
				});
				
				$tbody.append(rows);
			}  // end compare
		}  // end else
	});

});  // end sortable each loop
				
// METRIC CONVERSION BUTTON:
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
}); // end of click on metric

}); // end of page-loading wait statement