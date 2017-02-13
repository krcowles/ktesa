/* Table sorting for the table-only page is different than the others
   as there is one table only, the refTbl. In phpDynamicTbls.js, the sortable
   usrTbl script is applied to a table constructed based on the map viewport. */
$( function() {  // wait until document is loaded...

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

}); // end of page-loading wait statement