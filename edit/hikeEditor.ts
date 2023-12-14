declare var age: string;
declare var inEdits: string[];
declare var appMode: string;
declare var include_search: string;
declare function toggleScrollSelect(state: boolean): void;
declare function setNodDatAlerts(): void;
declare function tableSort(tableid: string): void;
/**
 * @fileoverview For each link in the table of hikes, parse for the href
 * attribute, then replace with an attribute which points to the correct
 * editor for the page type.
 * 
 * @author Ken Cowles
 * 
 * @version 2.0 Support for cluster pages
 * @version 2.1 Typescripted
 * @version 2.2 Replaced local sort with new columnSort.ts/js script
 * @version 3.0 Added searchbar to scroll to a hike in 'Edit Published'
 */
if (include_search === 'EditPub') {
	$('#search').autocomplete({
		source: hikeSources,
		minLength: 1
	});
	// When user selects item in 'autocomplete'
	$("#search").on("autocompleteselect", function(event, ui) {
		// the searchbar dropdown uses 'label', but place 'value' in box & use that
		event.preventDefault();
		var entry = ui.item.value;
		$(this).val(entry);
		scrollToHike(entry);
	});
	$('#clear').on('click', function() {
		$('#search').val("");
		var searchbox = document.getElementById('search') as HTMLElement;
		searchbox.focus();
	});
	const scrollToHike = (hikename: string) => {
		var $tbl = $('#editTbl');
		var $rows = $tbl.find('tr');
		var $scroll_row = $rows.eq(0);
		$rows.each(function() {
			let hikeTitle = $(this).children().eq(0).children().eq(0).text();
			if (hikeTitle == hikename) {
				$scroll_row = $(this);
				return false;
			} else {
				return;
			}
		});
		var row_pos = $scroll_row.offset() as JQuery.Coordinates;
		$(document).scrollTop(row_pos.top - 40);
		$scroll_row.css('background-color', '#e9d0af');
		return;
	}
}
// when preview buttons are displayed:
var preview   = '../pages/hikePageTemplate.php?age=new&hikeIndx=';
var btnId     = '<a id="prev';
var btnHtml   = 'class="btn btn-outline-primary btn-sm styled" role="button"' +
	'href="' + preview;
// hike table rows:
var $rows = $('table.sortable tbody').find('tr');
/**
 * On load and after every column sort, provide corresponding preview buttons
 */
function assignPreviews(): void {
	/**
	 * After sorting or resizing, prepend preview buttons
	 */
	var row1_loc= <JQuery.Coordinates>$rows.eq(0).offset();
	$('#prev_btns').offset({ top: row1_loc.top, left: row1_loc.left - 72 });
	var $sorted_rows: JQuery<HTMLTableRowElement> | null;
	$sorted_rows = null; // erase previous history
	$('#prev_btns').empty();
	$sorted_rows = $('table.sortable tbody').find('tr');
	$sorted_rows.each(function(indx) {
		let trow_ht = <number>$(this).height();
		let trow_pos = <JQuery.Coordinates>$(this).offset();
		let link_pos = { top: trow_pos.top, left: trow_pos.left -72 };
		// get link
		let $alink = $(this).find('td').eq(0).children().eq(0);
		let href = <string>$alink.attr('href');
		let hike_no_pos = href.indexOf('hikeNo') + 7
		let hike_no = href.substring(hike_no_pos);
		let btn_link = '<div>' + btnId + indx + '" style="height:' + trow_ht + '" ' +
			btnHtml + hike_no + '">Preview</a></div>';
		$('#prev_btns').append(btn_link);
		$('#prev'+indx).offset(link_pos);
	});
}
$(window).on('resize', function() {
	assignPreviews();
});
$( function () { // DOM loaded
/**
 * This script responds based on the current scenario:
 *  1. The user selected Contribute->Edit A Published Hike;
 *     A list of pages already published is presented as a table.
 *     A list of any published hikes already in edit is parsed. If the hike
 *     is in edit, the corresponding table cells are grayed out, and a message
 *     appears on mouseover that the hike page is currently in edit. 
 *     The working links for these pages are modified by this script to point
 *     to the xfrPub.php script, which will transfer a copy of the page into
 *     the edit tables. If the page is a cluster page, the link contains a
 *     query string parameter to identify it (clus=y).
 *  2. The user selected Contribute->Continue Editing Your Hikes
 *     In this case, a list of pages already in edit mode is presented
 *     as a table. The links for these pages are modified by this
 *     script to point either to editDB.php if the page is a hike page,
 *     or to editClusterPage.php if the page is a cluster page.
 *  3. The user selected Contribute->Submit for Publication
 * 	   In this case, a list of all in-edit hikes created by the user is displayed.
 *     The display will be the same as Item 2, above. The links however will
 * 	   generate an email to the admin for processing the request to publish the
 *     selected hike page.
 */
var page_type = $('#active').text();
var display_preview = page_type === 'Edit' && include_search !== 'EditPub'
	? true : false;
var useHikeEd = 'editDB.php?tab=1&hikeNo=';
var useClusEd = 'editClusterPage.php?hikeNo=';
var xfrPage   = 'xfrPub.php?hikeNo=';
var shipit    = '../edit/notifyAdmin.php?hikeNo=';	
$('table.sortable').attr('id', 'editTbl');
var hikeno: string;
var lnk: string;
$rows.each(function() {
	let $tdlink = $(this).children().eq(0);
	let $anchor = $tdlink.children().eq(0);
	let ptr = $anchor.prop('href');
	let pubpg = $anchor.text();
	let clushike =  ptr.indexOf('clus=y') !== -1 ? true : false;
	if (ptr.indexOf('hikeIndx') !== -1) {
		// find the hike no:
		let hikeloc = ptr.indexOf('hikeIndx=') + 9;
		hikeno = ptr.substr(hikeloc); // NOTE: this picks up the clus=y parm if present
	} else {
		hikeno = '0';
		alert("Could not locate hike");
	}
	if (page_type === 'PubReq') {
		let loc = shipit + hikeno;
		// This is a request to publish a hike
		$anchor.on('click', function(ev) {
			ev.preventDefault();
			$.ajax({
				url: loc,
				method: 'get',
				dataType: 'text',
				success: function(results) {
					if (results === "OK") {
						alert("An email has been sent to the admin");
					} // no other results available in script
				},
				error: function (jqXHR) {
					if (appMode === 'development') {
						let newDoc = document.open();
						newDoc.write(jqXHR.responseText);
						newDoc.close();
					} else {
						let msg = "Problem encountered sending admin mail\n" +
							"The admin has been notified.";
						alert(msg);
					}
				}
			});
		});
	} else {
		// *** THESE HIKES ARE NEW (EHIKES) ***
		if (age === 'new') { // age is established in hikeEditor.php
			if (clushike) {
				lnk = useClusEd + hikeno;
			} else {
				lnk = useHikeEd + hikeno;
			}
		// *** THESE HIKES ARE PUBLSIHED ***
		} else {
			lnk = xfrPage + hikeno;	
		}
		// gray out hikes already in-edit
		if (age === 'old' && inEdits.indexOf(pubpg) !== -1) {
			$(this).css('background-color', 'gainsboro');
			$anchor.css('cursor', 'default');
			$anchor.on('click', function(ev) {
				ev.preventDefault();
			});
			let $pubrow = $anchor.parent().parent();
			$pubrow.on('mouseover', function() {
				$(this).css('cursor', 'pointer');
				let affected = <JQuery.Coordinates>$(this).offset();
				$('#ineditModal').css({
					top: affected.top + 25,
					left: affected.left - 28,
					paddingTop: '4px',
					paddingBottom: '6px'
				});
				$('#ineditModal').show();
				return;
			});
			$pubrow.on('mouseout', function() {
				$('#ineditModal').hide();
				return;
			});
		} else {
			$anchor.attr('href', lnk);
			$anchor.attr('target', '_self');
		}
	}
});
tableSort('#editTbl');
if (display_preview) {
	assignPreviews();
}

});