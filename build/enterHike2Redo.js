$( function () { // when page is loaded...


$('a').on('click', function(e) {
	e.preventDefault();
	var $containerCell = $(this).parent();
	var $containerRow = $containerCell.parent();
	var hikeToUse = $containerRow.data('indx');
	var callPhp = 'rebuildData.php?hikeNo=' + hikeToUse;
	window.open(callPhp,"_blank");
});

var $rows = $('table tbody tr');
var pageHtml;
var classType;
$rows.each( function() {
	classType = this.class;
	if (classType !== 'indxd') {
		$cells = $(this).children();
		pageHtml = $cells.eq(3).text();
		if (pageHtml.indexOf('PageTemplate') !== -1) {
			$cells.each( function() {
				$(this).css('background-color','DarkGray');
			});
		}
	}
});
$rows = [];

// Provide sorting:
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
/* CAN'T GET THIS TO WORK - EVEN THOUGH IT DOES ELSEWHERE!! PRODUCES TWO TABLES...
(function () {
	var $table = $('#sortable'); 
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
			//hdrRow = trows[0];
			trows.reverse();
			//trows.pop();
			//trows.unshift(hdrRow);
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
}()); // end '#sortable' loop
*/


}); // end of page is loaded...