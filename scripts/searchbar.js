"use strict";
/// <reference path="./map.d.ts" />
/**
 * @file This script acts on user selection in searchbar
 *
 * @author Ken Cowles
 *
 * @version 3.0 Modified method for deploying Latin1 charset
 * @version 4.0 Changed to enable clearing searchbar
 */
var hilite_obj;
// Clear searchbar contents when user clicks on the "X"
$('#clear').on('click', function () {
    $('#search').val("");
    var searchbox = document.getElementById('search');
    searchbox.focus();
});
// Autocomplete search bar (jQueryUI):
$("#search").autocomplete({
    source: hikeSources,
    minLength: 2
});
$("#search").on("autocompleteselect", function (event, ui) {
    event.preventDefault();
    var entry = ui.item.value;
    $(this).val(entry);
    popupHikeName(entry);
});
/**
 * This function [coupled with infoWin()] 'clicks' the infoWin
 * for the corresponding hike
 */
function popupHikeName(hikename) {
    var found = false;
    if (pgnames.includes(hikename)) { // These are 'Cluster Pages', not hikes
        let indx = pgnames.indexOf(hikename);
        hilite_obj = { obj: CL[indx].hikes, type: 'cl' };
        infoWin(CL[indx].group, CL[indx].loc);
        found = true;
    }
    else {
        for (let i = 0; i < CL.length; i++) {
            for (let j = 0; j < CL[i].hikes.length; j++) {
                if (CL[i].hikes[j].name == hikename) {
                    hilite_obj = { obj: CL[i].hikes[j], type: 'nm' };
                    infoWin(CL[i].group, CL[i].loc);
                    found = true;
                    break;
                }
            }
        }
    }
    if (!found) {
        for (let k = 0; k < NM.length; k++) {
            if (NM[k].name == hikename) {
                hilite_obj = { obj: NM[k], type: 'nm' };
                infoWin(NM[k].name, NM[k].loc);
                found = true;
                break;
            }
        }
    }
    if (!found) {
        alert("This hike cannot be located in the list of hikes");
        infoWin_zoom = false;
    }
}
/**
 * This function will click the subject hike's marker, which will pop up
 * the marker's info window (see map.js). If the marker was previously clicked,
 * then the map re-centers at the marker
 */
function infoWin(hike, loc) {
    // find the marker associated with the input parameters and pop up its info window
    for (let k = 0; k < locaters.length; k++) {
        if (locaters[k].hikeid == hike) {
            if (locaters[k].clicked === false) {
                zoom_level = map.getZoom();
                // clicking will set (prototype) marker.clicked = true
                google.maps.event.trigger(locaters[k].pin, 'click');
            }
            else {
                map.setCenter(loc);
            }
            break;
        }
    }
    return;
}
