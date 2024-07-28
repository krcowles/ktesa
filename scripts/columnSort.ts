declare var previewSort: boolean;
declare function assignPreviews(): void;
type StandardNum = (a: string | number, b: string | number) => 0 | 1 | -1;
type LikeANumber = (a: string, b: string) => 0 | 1 | -1;
type IconString  = (a: string, b: string) => 0 | 1 | -1;
type CompareType = {
	[index:string]: StandardNum | LikeANumber | IconString;
}

/**
 * @fileoverview This reusable module specifies the sorting method for all
 * sortable tables (e.g. Table Only: #maintbl, #ftable; Hike Editor table:
 * #editTbl, Publish request table: #pubTbl). 
 * 
 * 
 * @author Ken Cowles
 * @version 1.0 Removes duplicate code in several modules
 * @version 2.0 Eliminate 'hasKey' function to bypass typescript issues
 */

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
    lan: function(a: string, b: string) {
		// "Like A Number": extract numeric portion for sort
        // Commas allowed in numbers; nothing greater than 9,999
        var indx = a.indexOf(',');
        if ( indx === -1 ) {
            aout = parseInt(a);
        } else {
            noPart1 = 1000 * parseInt(a);
            let thou = a.substring(indx + 1, indx + 4); 
            noPart2 = parseInt(thou);
            aout = noPart1 + noPart2;
        }
        indx = b.indexOf(',');
        if ( indx === -1 ) {
            bout = parseInt(b);
        } else {
            noPart1 = 1000 * parseInt(b);
            let thou = b.substring(indx + 1, indx + 4);
            noPart2 = parseInt(thou);
            bout = noPart1 + noPart2;
        }
        if (aout < bout) {
			return -1;
		} else {
			return aout > bout ? 1 : 0;
		}
    },
    icn: function(a: string, b: string) {	// standard sorting - literal
        if ( a < b ) {
            return -1;
        } else {
            return a > b ? 1 : 0;
        }
    },
} as CompareType;  // end of COMPARE object

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
	if (tableid === '#ftable') {
		/** 
		 * for Table Only page:
		 *  every time a new #ftable is formed, old sort criteria
		 * from previous tables must be cleared
		 */
		$controls.each(function() {
			$(this).removeClass('ascending');
			$(this).removeClass('descending');
		});
	}
	$controls.each(function() {
		// Setup Hike/Trail Name header w/class 'ascending': hikes are pre-sorted on load
		if ($(this).text() === 'Hike/Trail Name') {
			$(this).addClass('ascending');
		}
	}); 
	let $grows = $tbody.find('tr').toArray();
	/**
	 * Table's click behavior after loading table...
	 */
	$controls.each(function() {
		$(this).off('click').on('click', function() {
			let success = true;
			var $header = $(this);
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
							if (compare.hasOwnProperty(order)) {
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
			scrollCheck(tableid);
			return success;
		});
	});
}
/**
 * This function applies only to the #maintbl on the 'Table Only Page'
 * When to show/hide the scroller became too complex to control in the
 * sort routine itself, hence whenever a relevant event occurs (on 'Table
 * Only Page), conditions are examined to see whether or not to show the
 * scroller. The conditions themselves are easy to identify.
 */
function scrollCheck(table_id: string) {
	if ($('#active').text() === 'Table') {
		if (table_id === '#maintbl') {
			if  ($('#tblfilter').css('display') === 'none') {
				let $headers = $('#maintbl').find('th');
				let title = $headers.get(0) as HTMLTableCellElement;
				if ($(title).hasClass('ascending')) {
					toggleScrollSelect(true);
				} else {
					toggleScrollSelect(false);
				}
			} else {
				toggleScrollSelect(false);
			}
		}
	}
}
/**
 * Turn on/off the 'Scroll to:' selector based on current settings:
 * >> When hike title not sorted in ascending order
 * >> When filter table is showing
 * NOTE: Attempting to change the background color of the select box destroyed its
 * 'hover' behavior, so the author simply places a disabled gray selector on top of
 * the working to make it appear disabled.
 */
 function toggleScrollSelect(state: boolean) {
	if (state) {
		$('#gray').remove();
	} else {
		if (!$('#gray').length) {
			let cover = '<select id="gray"><option value="no">Scroll to:</option></select>';
			let selpos = <JQuery.Coordinates>$('#scroller').offset();
			$('#opt4').append(cover);
			$('#gray').offset({top: selpos.top, left: selpos.left});
			$('#gray').css('z-index', '1000');
			$('#gray').attr('disabled', 'disabled');
		}
	}
}
