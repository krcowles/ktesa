"use strict";
/**
 * @fileoverview Performance of the 'map multiple hikes on a page' function.
 *
 * @author Ken Cowles
 * @version 3.0 Modified to replace HTML datalist with JQueryUI autocomplete.
 */
/**
 * Make the usermodal for map multiple hikes draggable:
 */
dragElement(document.getElementById("usermodal"));
function dragElement(elmnt) {
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    var header = document.getElementById("modalhdr");
    header.onmousedown = dragMouseDown;
    /**
     * When mouse clicks down on header
     */
    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        // get the mouse cursor position at startup:
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        // call a function whenever the cursor moves:
        document.onmousemove = elementDrag;
    }
    /**
     * When mouse is held down on header and moved
     */
    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        // calculate the new cursor position:
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        // set the element's new position:
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
    }
    /**
     * When mouse releases after moving
     */
    function closeDragElement() {
        // stop moving when mouse button is released:
        document.onmouseup = null;
        document.onmousemove = null;
    }
}
var selectedHikes = []; // a list of the hike's track files
var orgHt = $('#usermodal').height();
/**
 * Add a hike to the 'Selected' list
 */
function addToList(hike) {
    for (var k = 0; k < eng_units.length; k++) {
        if (eng_units[k].trail === hike) {
            var htracks = eng_units[k].tracks.split(",");
            selectedHikes = selectedHikes.concat(htracks);
            break;
        }
    }
    $('#hlist').css('color', 'black');
    var item = '<li class="selectlist">' + hike + '</li>';
    var modalheight = $('#usermodal').height() + 18;
    $('#selections').append(item);
    $('#usermodal').height(modalheight);
    $('#hike2map').val('');
}
// capture user selected hikes
var mapHikes = [];
$('#closer').on('click', function () {
    $('#usermodal').hide();
});
/**
 * jQuery UI Autocomplete inputs are used in two places:
 * The hikeselection box (filter), and the map multiple box (tableOpts)
 */
// Clear their contents when user clicks on the "X"
$('#clear1').on('click', function () {
    $('#usehike').val("");
});
$("body").on("click", "#clear2", function () {
    $('#hike2map').val("");
});
// Establish jQueryUI widgets for both
$("#usehike").autocomplete({
    source: hikelist,
    minLength: 2
});
$("#hike2map").autocomplete({
    source: hikelist,
    minLength: 2
});
// When user selects item from hikelist:
$("#usehike").on("autocompleteselect", function (event, ui) {
    // the dropdown list uses 'label', but place 'value' in box & use that
    event.preventDefault();
    var entry = ui.item.value;
    $(this).val(entry);
});
$('#hike2map').on('autocompleteselect', function (event, ui) {
    // the dropdown list uses 'label', but place 'value' in box & use that
    event.preventDefault();
    var item = ui.item.value;
    $(this).val(item);
    addToList(item);
});
$('#hikeclr').on('click', function () {
    $('ul li').remove();
    selectedHikes = [];
    $('#usermodal').height(orgHt);
});
// draw the map
$('#mapem').on('click', function () {
    var query = '';
    for (var k = 0; k < selectedHikes.length; k++) {
        query += "m[]=" + selectedHikes[k] + "&";
    }
    query = query.substring(0, query.length - 1);
    window.open("../php/multiMap.php?" + query);
});
