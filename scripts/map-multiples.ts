/**
 * @fileoverview Performance of the 'map multiple hikes on a page' function.
 * 
 * @author Ken Cowles
 * @version 1.0 First release
 */

/**
 * Make the usermodal draggable:
 */
 dragElement(<HTMLElement>document.getElementById("usermodal"));
function dragElement(elmnt: HTMLElement) {
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    let header = <HTMLElement>document.getElementById("modalhdr")
    header.onmousedown = dragMouseDown;
    /**
     * When mouse clicks down on header
     */
    function dragMouseDown(e: MouseEvent) {
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
    function elementDrag(e: MouseEvent) {
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
var selectedHikes: string[] = [];
var orgHt = <number>$('#usermodal').height();
/**
 * Add a hike to the 'Selected' list
 */
function addToList(hike: string) {
    for (let k=0; k< eng_units.length; k++) {
        if (eng_units[k].trail === hike) {
            let gpxinfo = eng_units[k].gpx;
            if (gpxinfo.indexOf(',') !== -1) {
                // this hike has multiple track files
                let gpxfiles = gpxinfo.split(",");
                for (let i=0; i<gpxfiles.length; i++) {
                    selectedHikes.push(gpxfiles[i]);
                }
            } else {
                selectedHikes.push(gpxinfo);
            }
            break;
        }
    }
    $('#hlist').css('color', 'black');
    let item = '<li class="selectlist">' + hike + '</li>';
    let modalheight = <number>$('#usermodal').height() + 18;
    $('#selections').append(item);
    $('#usermodal').height(modalheight);
    $('#hike2map').val('');
}
// capture user selected hikes
 var mapHikes: string[] = [];

$('#closer').on('click', function() {
    $('#usermodal').hide();
});

$('#hike2map').on('input', function() {
    var $input = $(this),
        val = <string>$input.val(),
        list = $input.attr('list'),
        match = $('#'+list + ' option').filter(function() {
            return ($(this).val() === val);
        });
    if (match.length > 0) {
        addToList(val);
    }
});
$('#hikeclr').on('click', function() {
    $('ul li').remove();
    selectedHikes = [];
    $('#usermodal').height(orgHt);
});

// draw the map
$('#mapem').on('click', function() {
    let query = '';
	for (let k=0; k<selectedHikes.length; k++) {
		query += "m[]=" + selectedHikes[k] + "&";
	}
	query = query.substring(0, query.length-1);
	window.open("../php/multiMap.php?" + query);
});
