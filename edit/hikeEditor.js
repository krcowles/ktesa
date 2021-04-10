"use strict";
/**
 * @fileoverview For each link in the table of hikes, parse for the href
 * attribute, then replace with an attribute which points to the correct
 * editor for the page type.
 *
 * @author Ken Cowles
 *
 * @version 2.0 Support for cluster pages
 * @version 2.1 Typescripted
 */
// To use a variable as an index into an object, the following helper function is required
// to overcome typescript complaints - compliments of
// https://dev.to/mapleleaf/indexing-objects-in-typescript-1cgi
function hasKey(obj, key) {
    return key in obj;
}
$(function () {
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
     */
    var page_type = $('#page_id').text();
    var useHikeEd = 'editDB.php?tab=1&hikeNo=';
    var useClusEd = 'editClusterPage.php?hikeNo=';
    var xfrPage = 'xfrPub.php?hikeNo=';
    var shipit = '../edit/notifyAdmin.php?hikeNo=';
    var $rows = $('tbody').find('tr');
    var hikeno;
    var lnk;
    $rows.each(function () {
        var $tdlink = $(this).children().eq(0);
        var $anchor = $tdlink.children().eq(0);
        var ptr = $anchor.prop('href');
        var pubpg = $anchor.text();
        var clushike = ptr.indexOf('clus=y') !== -1 ? true : false;
        if (ptr.indexOf('hikeIndx') !== -1) {
            // find the hike no:
            var hikeloc = ptr.indexOf('hikeIndx=') + 9;
            hikeno = ptr.substr(hikeloc); // NOTE: this picks up the clus=y parm if present
        }
        else {
            hikeno = '0';
            alert("Could not locate hike");
        }
        if (page_type === 'PubReq') {
            var loc_1 = shipit + hikeno;
            // This is a request to publish a hike
            $anchor.on('click', function (ev) {
                ev.preventDefault();
                $.ajax({
                    url: loc_1,
                    method: 'get',
                    dataType: 'text',
                    success: function (results) {
                        if (results === "OK") {
                            alert("An email has been sent to the admin");
                        }
                        else {
                            alert("The email did not get sent: please use Help->Contact Us");
                        }
                    },
                    error: function (jqXHR) {
                        var newDoc = document.open();
                        newDoc.write(jqXHR.responseText);
                        newDoc.close();
                    }
                });
            });
        }
        else {
            // *** THESE HIKES ARE NEW (EHIKES) ***
            if (age === 'new') { // age is established in hikeEditor.php
                if (clushike) {
                    lnk = useClusEd + hikeno;
                }
                else {
                    lnk = useHikeEd + hikeno;
                }
                // *** THESE HIKES ARE PUBLSIHED ***
            }
            else {
                lnk = xfrPage + hikeno;
            }
            // gray out hikes already in-edit
            if (age === 'old' && inEdits.indexOf(pubpg) !== -1) {
                $(this).css('background-color', 'gainsboro');
                $anchor.css('cursor', 'default');
                $anchor.on('click', function (ev) {
                    ev.preventDefault();
                });
                var $pubrow = $anchor.parent().parent();
                $pubrow.on('mouseover', function () {
                    $(this).css('cursor', 'pointer');
                    var affected = $(this).offset();
                    $('#ineditModal').css({
                        top: affected.top + 25,
                        left: affected.left - 28
                    });
                    $('#ineditModal').show();
                    return;
                });
                $pubrow.on('mouseout', function () {
                    $('#ineditModal').hide();
                    return;
                });
            }
            else {
                $anchor.attr('href', lnk);
                $anchor.attr('target', '_self');
            }
        }
    });
    // global object used to define how table items get compared in a sort:
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
        }
    }; // end of COMPARE object
    $('.sortable').each(function () {
        var $table = $(this);
        var $tbody = $table.find('tbody');
        var $controls = $table.find('th');
        var rows = $tbody.find('tr').toArray();
        $controls.on('click', function () {
            var $header = $(this);
            var order = $header.data('sort');
            var column;
            if ($header.hasClass('ascending') || $header.hasClass('descending')) {
                $header.toggleClass('ascending descending');
                $tbody.append(rows.reverse());
            }
            else {
                $header.addClass('ascending');
                $header.siblings().removeClass('ascending descending');
                if (compare.hasOwnProperty(order)) { // compare object needs method for var order
                    column = $controls.index(this);
                    rows.sort(function (a, b) {
                        var $ael = $(a).find('td').eq(column);
                        var acell = $ael.text();
                        var $bel = $(b).find('td').eq(column);
                        var bcell = $bel.text();
                        var retval = 0;
                        if (hasKey(compare, order)) {
                            retval = compare[order](acell, bcell);
                        }
                        return retval;
                    });
                    $tbody.append(rows);
                } // end compare
            } // end else
        });
    });
});
