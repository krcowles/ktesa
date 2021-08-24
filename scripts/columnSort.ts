declare var previewSort: boolean;
declare function assignPreviews(): void;
/**
 * @fileoverview This reusable module specifies the sorting method for all
 * sortable tables (e.g. Table Only: #maintbl, #ftable; Hike Editor table:
 * #editTbl, Publish request table: #pubTbl). 
 * 
 * 
 * @author Ken Cowles
 * @version 1.0 Removes duplicate code in several modules
 */

// To use a variable as an index into an object, the following helper function is
// required to overcome typescript complaints - compliments of
// https://dev.to/mapleleaf/indexing-objects-in-typescript-1cgi
function hasKey<O>(obj: O, key: keyof any): key is keyof O {
	return key in obj
}

/**
 * Global object used to define how table items get compared in a sort:
 */
var noPart1: number;
var noPart2: number;
var aout: number;
var bout: number;
var compare = {
    std: function(a: number | string, b: number | string) {	// standard sorting - literal
        if ( a < b ) {
            return -1;
        } else {
            return a > b ? 1 : 0;
        }
    },
    lan: function(a: string, b: string) {    // "Like A Number": extract numeric portion for sort
        // commas allowed in numbers, so;
        var indx = a.indexOf(',');
        if ( indx < 0 ) {
            aout = parseFloat(a);
        } else {
            noPart1 = 1000 * parseFloat(a);
            let thou = a.substring(indx + 1, indx + 4);
            noPart2 = parseInt(thou);
            aout = noPart1 + noPart2;
        }
        indx = b.indexOf(',');
        if ( indx < 0 ) {
            bout = parseFloat(b);
        } else {
            noPart1 = 1000 * parseFloat(b);
            let thou = b.substring(indx + 1, indx + 4);
            noPart2 = parseInt(thou);
            bout = noPart1 + noPart2;
        }
        return aout - bout;
    },
    icn: function(a: string, b: string) {	// standard sorting - literal
        if ( a < b ) {
            return -1;
        } else {
            return a > b ? 1 : 0;
        }
    },
};  // end of COMPARE object

/**
 * This function translates an exposure icon (<img> src) into sortable text
 */
 function iconText(imgsrc: string): string {
	let type: string;
	if (imgsrc.indexOf("fullSun") !== -1) {
		type = "fullsun";
	} else if (imgsrc.indexOf("partShade") !== -1) {
		type = "partshade";
	} else if (imgsrc.indexOf("goodShade") !== -1) {
		type = "reasonableshade";
	} else {
		type = "zgroup";
	}
	return type;
}

/**
 * Apply this function to a table to provide sortable headers (table columns)
 */
 function tableSort(tableid: string) {
	let $table = $(tableid);
	let $tbody = $table.find('tbody');
	let $controls = $table.find('th');
	if (tableid === '#ftable') { // see Table Only page
		// every time a new ftable is formed, old sort criteria
		// from previous tables must be cleared:
		$controls.each(function() {
			$(this).removeClass('ascending');
			$(this).removeClass('descending');
		});
	}
	$controls.each(function() {
		// Setup hikename w/class 'ascending', since hikes are pre-sorted
		if ($(this).text() === 'Hike/Trail Name') {
			$(this).addClass('ascending');
			return;
		}
	}); 
	let $grows = $tbody.find('tr').toArray();
	$controls.each(function() {
		$(this).off('click').on('click', function() {
			let success = true;
			var $header = $(this);
			if (tableid === '#maintbl') {
				if ($header.text() === 'Hike/Trail Name') {
					toggleScrollSelect(true);
					scroll_to = true;
				} else { 
					toggleScrollSelect(false);
					scroll_to = false;
				}
			}
			var order: string = $header.data('sort');
			if (order === 'no') {
				alert("This column cannot be sorted");
				success = false;
			} else {
				$tbody.empty();
				var column: number;
				// begin the sort process
				if ($header.hasClass('ascending') || $header.hasClass('descending')) {
					$header.toggleClass('ascending descending');
					$tbody.append($grows.reverse());
					if (typeof previewSort !== 'undefined' && previewSort) {
						assignPreviews();
					}
				} else {
					$header.addClass('ascending');
					$header.siblings().removeClass('ascending descending');
					if (compare.hasOwnProperty(order)) {  // compare object needs method for var order
						column = $controls.index(this);
						$grows.sort(function(a, b) {
							let acell: string;
							let bcell: string;
							let icn: string;
							let ael = $(a).find('td').eq(column);
							if (order === 'icn') {
								icn = <string>ael.children().eq(0).attr('src');
								acell = iconText(icn);
							} else {
								acell = ael.text();
							}
							let bel = $(b).find('td').eq(column);
							if (order === 'icn') {
								icn = <string>bel.children().eq(0).attr('src');
								bcell = iconText(icn);
							} else {
								bcell = bel.text();
							}
							let retval = 0;
							if (hasKey(compare, order)) {
								retval = <number>compare[order](acell , bcell);
							}
							return retval;
						
						});
						$tbody.append($grows);
						if (typeof previewSort !== 'undefined' && previewSort) {
							assignPreviews();
						}
					} else {
						alert("Compare failed for this header");
						success = false;
					}
				}
			}
			if ($('#active').length !== 0 && $('#active').text() === 'Table') {
				setNodatAlerts();
			}
			return success;
		});
	});
}
