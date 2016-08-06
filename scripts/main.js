$( function() {  // wait until document is loaded...
// ------------------

var msg;
var indx;
var noPart1;
var noPart2;

mobile_browser = (navigator.userAgent.match(/\b(Android|Blackberry|IEMobile|iPhone|iPad|iPod|Opera Mini|webOS)\b/i) || (screen && screen.width && screen.height && (screen.width <= 480 || screen.height <= 480))) ? true : false;
if ( !mobile_browser ) {
	$('#forMobile').css('display','none');
}
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
	
// ---------------
});  // end of 'wait til document loaded'
