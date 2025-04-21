// items held on hikeEditor.php page
declare var age: string;
declare var inEdits: string[];
declare var appMode: string;
declare var include_search: string;  // which type of page is displayed
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
 * @version 4.0 Added deleted button for user to delete own hikes
 * @version 5.0 Modified publication request: pre-screen hike
 */
var appMode = $('#appMode').text() as string;
/**
 * First, reduce space consumed by the table: alter column widths and eliminate 
 * 'Exposure' and Driving Directions columns; makes room for buttons
 */
var columns = document.getElementsByTagName('col');
var headers = document.getElementsByTagName('th');
var $rows    = $('table.sortable tbody').find('tr');
if (columns.length !== 8) {
	alert("Unexpected table in editor!")
}
// reduce table width to allow for buttons
columns[7].parentNode?.removeChild(columns[7]);
columns[6].parentNode?.removeChild(columns[6]);
headers[7].parentNode?.removeChild(headers[7]);
headers[6].parentNode?.removeChild(headers[6]);
$rows.each(function() {
	let colspec = columns[0] as HTMLTableColElement;
	colspec.style.width = '160px';
	this.removeChild(this.children[7]);
	this.removeChild(this.children[6]);
	$(this).children().css('padding-bottom', '6px');
});

var page_type = $('#active').text();
var display_preview = page_type === 'Edit' && include_search !== 'EditPub'
	? true : false;

// Remaining actions depend on page type: include_search var
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
var delId      = '<a id="hike_'
var delHtml    =  'class="udels btn btn-outline-danger btn-sm styled" role="button"' +
	' href="#">Delete</a>';
/**
 * On load and after every column sort, provide corresponding preview buttons
 */
function assignPreviews(): void {
	/**
	 * After sorting or resizing, prepend preview buttons
	 */
	var row1_loc= <JQuery.Coordinates>$rows.eq(0).offset();
	$('#user_btns').offset({ top: row1_loc.top, left: row1_loc.left - 136 });
	var $sorted_rows: JQuery<HTMLTableRowElement> | null;
	$sorted_rows = null; // erase previous history
	$('#user_btns').empty();
	$sorted_rows = $('table.sortable tbody').find('tr');
	$sorted_rows.each(function(indx) {
		let trow_ht = <number>$(this).height();
		let trow_pos = <JQuery.Coordinates>$(this).offset();
		let prev_pos = { top: trow_pos.top, left: trow_pos.left -72 };
		let del_pos  = { top: trow_pos.top };
		// get link from 1st cell => editor with tab1 and hikeIndx
		let $alink = $(this).find('td').eq(0).children().eq(0);
		let href = <string>$alink.attr('href');
		// strip off queries relating to clusters...
		let ampersand = href.indexOf("&clus");
		if (ampersand !== -1) {
			href = href.substring(0, ampersand);
		}
		let hike_no_pos = href.indexOf('hikeNo') + 7
		let hike_no = href.substring(hike_no_pos);
		let btn_link = btnId + indx + '" style="height:' + trow_ht + '" ' +
			btnHtml + hike_no + '">Preview</a>';
		let del_link = delId + hike_no + '" ' + delHtml;
		$('#user_btns').append(del_link);
		$('#user_btns').append(btn_link);
		$('#prev'+indx).offset(prev_pos);
		$('#hike_'+hike_no).offset(del_pos);
	});
	// set up click events for delete buttons
	$('.udels').each(function() { // delete EHIKE function
		let hindx = $(this).attr('id') as string;
		// get hike no from string; "hike_" = 5 chars
		let hike = hindx.substring(5);
		let delscript = '../php/delete.php?hno=' + hike;
		$(this).on('click', function() {
			let ans = confirm("All hike data will be lost:\nDo you really " +
				"want to delete this hike?");
			if (ans) {
				window.open(delscript, "_self");
			}
		});
	});
}

$(window).on('resize', function() {
	if (display_preview) {
		assignPreviews();
	}
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
var useHikeEd = 'editDB.php?tab=1&hikeNo=';
var useClusEd = 'editClusterPage.php?hikeNo=';
var xfrPage   = 'xfrPub.php?hikeNo=';
var shipit    = '../edit/notifyAdmin.php?hikeNo=';	
$('table.sortable').attr('id', 'editTbl');
var hikeno: string;
var lnk: string;
function setupClickBehavior(a: JQuery<HTMLElement>, name: string, 
	hno: string, clus: string, mail: string)
{
	a.on('click', function(ev) {
		ev.preventDefault();
		var ajaxdata = {hikeNo: hno, cluster: clus};
		var $a = $(this);
		$.ajax({
			url: "validatePubRequest.php",
			method: 'get',
			data: ajaxdata,
			dataType: 'text',
			success: function(result) {
				if (result === 'OK') {
					var disabled = '<span style="color:gray;">' + name + '</span>';
					$a.replaceWith(disabled);
					$.ajax({
						url: mail,
						method: 'get',
						dataType: 'text',
						success: function(results) {
							if (results === "OK") {
								alert("An email has been sent to the admin");
								window.location.replace(window.location.href);
							} // no other results available in script
						},
						error: function (_jqXHR, _textStatus, _errorThrown) {
							let msg = "hikeEditor.js: attempting to access " +
								"notifyAdmin.php";
							ajaxError(appMode, _jqXHR, _textStatus, msg);
						}
					});
				} else {
					alert(result);
				}
			},
			error: function(_jqXHR, _textStatus, _errorThrown) {
				let msg = "hikeEditor.js: attempting to validate request " +
					"via validatePubRequest.php";
				ajaxError(appMode, _jqXHR, _textStatus, msg);
			}
		});
	});
}
$rows.each(function() {
	var $tdlink = $(this).children().eq(0);
	var $anchor = $tdlink.children().eq(0);
	var chosenHike = $anchor.text() as string;
	var ptr = $anchor.prop('href');
	var pubpg = $anchor.text();
	var clushike =  ptr.indexOf('clus=y') !== -1 ? true : false;
	// find the hike no:
	var hikeloc = ptr.indexOf('hikeIndx=') + 9;
	hikeno = ptr.substr(hikeloc); // NOTE: this picks up the clus=y parm if present
	if (ptr.indexOf('hikeIndx') == -1) {
		alert("Could not locate hike");
		return false;
	}
	if (page_type === 'PubReq') {
		var hindx: string;
		var clus_parm = hikeno.indexOf('&');
		if (clus_parm === -1) {
			hindx = hikeno;
		} else {
			hindx = hikeno.substring(0, clus_parm);
		}
		var clus = clushike ? 'Y' : 'N';
		var loc = shipit + hindx + "&name=" + chosenHike;
		// This is a request to publish a hike
		setupClickBehavior($anchor, chosenHike, hindx, clus, loc);
	} else {
		// *** THESE HIKES ARE NEW (EHIKES) ***
		if (age === 'new') { // age is established in hikeEditor.php
			if (clushike) {
				lnk = useClusEd + hikeno; // includes &clus=
			} else {
				lnk = useHikeEd + hikeno;
			}
		// *** THESE HIKES ARE PUBLSIHED ***
		} else {
			lnk = xfrPage + hikeno;	// includes &clus=  if present
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
	return;
});
tableSort('#editTbl');
if (display_preview) {
	assignPreviews();
}

});