"use strict";
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
function hasKey(obj, key) {
    return key in obj;
}
/**
 * Global object used to define how table items get compared in a sort:
 */
var noPart1;
var noPart2;
var aout;
var bout;
var compare = {
    std: function (a, b) {
        if (a < b) {
            return -1;
        }
        else {
            return a > b ? 1 : 0;
        }
    },
    lan: function (a, b) {
        // commas allowed in numbers, so;
        var indx = a.indexOf(',');
        if (indx < 0) {
            aout = parseFloat(a);
        }
        else {
            noPart1 = 1000 * parseFloat(a);
            var thou = a.substring(indx + 1, indx + 4);
            noPart2 = parseInt(thou);
            aout = noPart1 + noPart2;
        }
        indx = b.indexOf(',');
        if (indx < 0) {
            bout = parseFloat(b);
        }
        else {
            noPart1 = 1000 * parseFloat(b);
            var thou = b.substring(indx + 1, indx + 4);
            noPart2 = parseInt(thou);
            bout = noPart1 + noPart2;
        }
        return aout - bout;
    },
    icn: function (a, b) {
        if (a < b) {
            return -1;
        }
        else {
            return a > b ? 1 : 0;
        }
    },
}; // end of COMPARE object
/**
 * This function translates an exposure icon (<img> src) into sortable text
 */
function iconText(imgsrc) {
    var type;
    if (imgsrc.indexOf("fullSun") !== -1) {
        type = "fullsun";
    }
    else if (imgsrc.indexOf("partShade") !== -1) {
        type = "partshade";
    }
    else if (imgsrc.indexOf("goodShade") !== -1) {
        type = "reasonableshade";
    }
    else {
        type = "zgroup";
    }
    return type;
}
/**
 * Apply this function to a table to provide sortable headers (table columns)
 */
function tableSort(tableid) {
    var $table = $(tableid);
    var $tbody = $table.find('tbody');
    var $controls = $table.find('th');
    if (tableid === '#ftable') { // see Table Only page
        // every time a new ftable is formed, old sort criteria
        // from previous tables must be cleared:
        $controls.each(function () {
            $(this).removeClass('ascending');
            $(this).removeClass('descending');
        });
    }
    $controls.each(function () {
        // Setup hikename w/class 'ascending', since hikes are pre-sorted
        if ($(this).text() === 'Hike/Trail Name') {
            $(this).addClass('ascending');
            return;
        }
    });
    var $grows = $tbody.find('tr').toArray();
    $controls.each(function () {
        $(this).off('click').on('click', function () {
            var success = true;
            var $header = $(this);
            if (tableid === '#maintbl') {
                if ($header.text() === 'Hike/Trail Name') {
                    toggleScrollSelect(true);
                    scroll_to = true;
                }
                else {
                    toggleScrollSelect(false);
                    scroll_to = false;
                }
            }
            var order = $header.data('sort');
            if (order === 'no') {
                alert("This column cannot be sorted");
                success = false;
            }
            else {
                $tbody.empty();
                var column;
                // begin the sort process
                if ($header.hasClass('ascending') || $header.hasClass('descending')) {
                    $header.toggleClass('ascending descending');
                    $tbody.append($grows.reverse());
                }
                else {
                    $header.addClass('ascending');
                    $header.siblings().removeClass('ascending descending');
                    if (compare.hasOwnProperty(order)) { // compare object needs method for var order
                        column = $controls.index(this);
                        $grows.sort(function (a, b) {
                            var acell;
                            var bcell;
                            var icn;
                            var ael = $(a).find('td').eq(column);
                            if (order === 'icn') {
                                icn = ael.children().eq(0).attr('src');
                                acell = iconText(icn);
                            }
                            else {
                                acell = ael.text();
                            }
                            var bel = $(b).find('td').eq(column);
                            if (order === 'icn') {
                                icn = bel.children().eq(0).attr('src');
                                bcell = iconText(icn);
                            }
                            else {
                                bcell = bel.text();
                            }
                            var retval = 0;
                            if (hasKey(compare, order)) {
                                retval = compare[order](acell, bcell);
                            }
                            return retval;
                        });
                        $tbody.append($grows);
                    }
                    else {
                        alert("Compare failed for this header");
                        success = false;
                    }
                }
            }
            setNodatAlerts();
            return success;
        });
    });
}
