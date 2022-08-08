/// <reference path="./map.d.ts" />
declare var infoWin_zoom: boolean;
declare var hikeSources: AutoItem[];
interface AutoItem {
    value: string;
    label: string;
}
/**
 * @file This script acts on user selection in searchbar
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 Separated from sideTables.js to provide reusable functionality
 * @version 1.1 Typescripted
 * @version 2.0 Switched from HTML datalist to jquery-ui autocomplete for searches
 */

// Turn off search on load - wait until map is displayed
//$('#offOnLoad').hide();
/**
 * Autocomplete search bar (jQueryUI):
 * HTML Special Characters are properly rendered in an undisplayed ul on the page,
 * and used for autocompleteselect, below
 */ 
 var rendered = $('#specchars').children(); // the <li> elements in the undisplayed <ul>
 $("#search").autocomplete({
     source: hikeSources,
     minLength: 2
 });
 $("#search").on("autocompleteselect", function(event, ui) {
     /**
      * Apparently, since the menu items are js objects, the HTML special characters
      * don't render when the item.label is replaced by item.value. Hence, the properly
      * rendered items are placed in an undisplayed ul, and the javascript extracts these
      * rendered items to replace the label instead of using default method
      */
     event.preventDefault();
     var replaceTxt = '';
     if (ui.item.value !== ui.item.label) {
         var lino = parseInt(ui.item.value);
         replaceTxt = rendered[lino].innerText; // this <li> contains the rendered text
     } else {
         replaceTxt = ui.item.value;
     }
     $(this).val(replaceTxt);
     var val = translate(replaceTxt);
     popupHikeName(val);
 });
 /**
  * HTML presents on the page, and to javascript, an accented letter (letter w/diacritical
  * mark) when it encounters either an HTML entity number, or an HTML entity name - the user
  * may choose either when typing in the title during hike creation. These special entities
  * are listed in the ISO 8859-1 table of characters. When the function 'translate()' is invoked,
  * it looks to see if an HTML special character has been rendered by the HTML as an accented
  * letter. Note that any entity -names- have been converted to entity -numbers- in mapJsData.php.
  * If there is an accented letter in the searchbar input, the function replaces it with its
  * entity number. In this way, the 'translated' name can be successfully compared with the list
  * of hikes as prepared by PHP. PHP, of course, does not render HTML special characters, and so
  * will list the hike as a string with the HTML entity number intact.
  */
  function translate(hike: string): string {
     var i = hike.length,
         a: string[] = []; // translated string chars
     while (i--) {
         var code = hike.charCodeAt(i);
        if (code > 191 && code <= 255) {  // special entity characters should be the only chars here
             a[i] = '&#' + code + ';';
         } else {
             a[i] = hike[i];
         }
     }
     return a.join('');
 }
/**
 * This function [coupled with infoWin()] 'clicks' the infoWin
 * for the corresponding hike
 */
function popupHikeName(hikename: string) {
    var found = false;
    if (pgnames.includes(hikename)) { // These are 'Cluster Pages', not hikes
            let indx = pgnames.indexOf(hikename);
            hilite_obj = {obj: CL[indx].hikes, type: 'cl'};
            infoWin(CL[indx].group, CL[indx].loc);
            found = true;
    } else {
        for (let i=0; i<CL.length; i++) {
            for (let j=0; j<CL[i].hikes.length; j++) {
                if (CL[i].hikes[j].name == hikename) {
                    hilite_obj = {obj: CL[i].hikes[j], type: 'nm'};
                    infoWin(CL[i].group, CL[i].loc);
                    found = true;
                    break;
                }
            }
        }
    }
    if (!found) {
        for (let k=0; k<NM.length; k++) {
            if (NM[k].name == hikename) {
                hilite_obj = {obj: NM[k], type: 'nm'};
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
 * 
 * @param {string} hike The name of the hike whose infoWindow will be clicked
 * @returns {null}
 */
function infoWin(hike: string, loc: GPS_Coord) {
    // highlight track for either searchbar or zoom-to icon:
    applyHighlighting = true;
    // find the marker associated with the input parameters and pop up its info window
    for (let k=0; k<locaters.length; k++) {
        if (locaters[k].hikeid == hike) {
            let thismarker = locaters[k].pin;
            if (thismarker.clicked === false) {
                zoomLevel = map.getZoom();
                // clicking will set (prototype) marker.clicked = true
                google.maps.event.trigger(locaters[k].pin, 'click');
            } else {
                map.setCenter(loc);
            }
            break;
        }
    }
    return;
}
