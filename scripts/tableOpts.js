"use strict";
/**
 * @fileoverview This script contains functions that handle options in the
 * Table Options header.
 *
 * @author Ken Cowles
 *
 * @version 2.0 Reformatted table options; Fixed broken 'Units Converion'
 * @version 2.1 Typescripted
 */
var lgth_hdr;
var elev_hdr;
var hike_hdr;
var eng_units = [];
var curr_main_state;
var curr_ftbl_state;
var ftbl_init = false;
var engtxt = "Show English Units";
var mettxt = "Show Metric Units";
var scroll_to = true; // initial load has alphabetic sort of hikes
/**
 * This function will set up the converter to use the originally loaded values
 * for units (English) when recalculating from metric back to English. This
 * prevents calculation/rounding errors when switching back and forth between
 * units. It also registers the click on the conversion button (#units).
 */
function setupConverter(tblid, org) {
    var $etable = $(tblid);
    var $etbody = $etable.find('tbody');
    var $erows = $etbody.find('tr');
    if (org) {
        // save original English units from main table on the first pass
        $erows.each(function () {
            var $trail = $(this).find('td').eq(hike_hdr);
            var trail = $trail.text();
            var hike = $(this).data('indx');
            var $disttd = $(this).find('td').eq(lgth_hdr);
            var orgdist = $disttd.text();
            var $elevtd = $(this).find('td').eq(elev_hdr);
            var orgelev = $elevtd.text();
            var gpxdat = $(this).data('gpx');
            var orgdata = { trail: trail, hikeno: hike, dist: orgdist, elev: orgelev, gpx: gpxdat };
            eng_units.push(orgdata);
        });
    }
    $('#units').off('click').on('click', function () {
        var goto = $(this).text();
        var newunits = '';
        var newbtn = '';
        if (goto.indexOf('Metric') !== -1) { // "Show Metric..."
            newunits = 'Metric';
            newbtn = engtxt;
        }
        else { // "Show English..."
            newunits = 'English';
            newbtn = mettxt;
        }
        if (tblid === '#maintbl') {
            curr_main_state = newbtn;
        }
        else {
            curr_ftbl_state = newbtn;
        }
        convert(tblid, $etbody, newunits);
    });
}
/**
 * This function will convert units on the table currently displayed: i.e. either
 * '#maintbl', or one of the incarnations of '#ftable' (jqTable).
 */
function convert(tblid, jqTable, to_units) {
    // conversion variables:
    var newDist;
    var newElev;
    var newUnits;
    var dist;
    var elev;
    if (to_units === 'Metric') { // currently metric; revert to originals
        newDist = ' kms';
        newElev = ' m';
        dist = 1.61;
        elev = 0.305;
        newUnits = engtxt;
    }
    else { // currently English; convert TO metric
        newUnits = mettxt;
    }
    if (tblid === '#maintbl') {
        curr_main_state = newUnits;
    }
    else {
        curr_ftbl_state = newUnits;
    }
    /**
     * The conversion will take place on both 'length' and 'elevation change';
     * Convert to metric, or replace original values when converting to English
     * Re-collect $erows, as order may have changed.
     */
    var $erows = jqTable.find('tr');
    if (to_units === 'Metric') {
        $erows.each(function () {
            var $disttd = $(this).find('td').eq(lgth_hdr);
            var dist_txt = $disttd.text();
            var dconv = parseFloat(dist_txt);
            var newdist = dconv * dist;
            newDist = newdist.toFixed(2) + ' kms';
            $disttd.text(newDist);
            var $elevtd = $(this).find('td').eq(elev_hdr);
            var elev_txt = $elevtd.text();
            var econv = parseFloat(elev_txt);
            var newelev = econv * elev;
            newElev = newelev.toFixed() + ' m';
            $elevtd.text(newElev);
        });
    }
    else {
        $erows.each(function () {
            var hike = $(this).data('indx');
            var $disttd = $(this).find('td').eq(lgth_hdr);
            var $elevtd = $(this).find('td').eq(elev_hdr);
            eng_units.forEach(function (obj) {
                if (obj.hikeno === hike) {
                    var newdist = obj.dist;
                    $disttd.text(newdist);
                    var newelev = obj.elev;
                    $elevtd.text(newelev);
                    return;
                }
            });
        });
    }
    $('#units').text(newUnits); // new data element text
    return;
}
/**
 * This will show an alert to the user when he/she mouses over the icon
 * in the 'Exposure' column if the icon is a group icon, or otherwise has
 * no rating, as in a proposed hike.
 */
function setNodatAlerts() {
    $('.nodats').each(function () {
        $(this).on('mouseover', function () {
            var iconpos = $(this).offset();
            $('#nodata').show();
            $('#nodata').css({
                top: iconpos.top,
                left: iconpos.left - 160
            });
        });
        $(this).on('mouseout', function () {
            $('#nodata').hide();
        });
    });
}
/**
 * Turn on/off the 'Scroll to:' selector based on current settings:
 * >> When hike title not sorted in ascending order
 * >> When filter table is showing
 * NOTE: Attempting to change the background color of the select box destroyed its
 * 'hover' behavior, so the author simply places a disabled gray selector on top of
 * the working when to make it appear disabled.
 */
function toggleScrollSelect(state) {
    if (state) {
        $('#gray').remove();
    }
    else {
        if (!$('#gray').length) {
            var cover = '<select id="gray"><option value="no">Scroll to:</option></select>';
            var selpos = $('#scroller').offset();
            $('#opt4').append(cover);
            $('#gray').offset({ top: selpos.top, left: selpos.left });
            $('#gray').css('z-index', '1000');
            $('#gray').attr('disabled', 'disabled');
        }
    }
}
/**
 * When page is loaded and table is estabished:
 */
$(function () {
    // Provide table id for main table (originally loaded with class only)
    $('.sortable')[0].id = 'maintbl';
    tableSort('#maintbl');
    $('#maintbl').css('margin-bottom', '26px');
    // 'Return to top' div, when selecting scroll
    var tablepos = $('#maintbl').offset();
    var table_left = (tablepos.left - 78) + 'px';
    $('#backup').css('left', table_left);
    $(window).on('scroll', function () {
        var s = $(window).scrollTop();
        if (s < 200) {
            $('#backup').hide();
            $('#scroller').val("none");
        }
    });
    // Initialize the results table html by adding column headers from main table
    var tblHdrs = $('#maintbl').html();
    var bdystrt = tblHdrs.indexOf('<tbody>');
    tblHdrs = tblHdrs.substr(0, bdystrt);
    $('#ftable').prepend(tblHdrs);
    // get column nos. for 'Hike/Trail Name', 'Length' and 'Elev' data
    // Note: both main and filter tables use same column
    var $htable = $('#maintbl thead');
    var $hdrs = $htable.eq(0).find('th');
    $hdrs.each(function (indx) {
        if ($(this).text() === 'Hike/Trail Name') {
            hike_hdr = indx;
        }
        if ($(this).text() === 'Length') {
            lgth_hdr = indx;
        }
        if ($(this).text() === 'Elev Chg') {
            elev_hdr = indx;
        }
    });
    // first state of newly loaded table:
    curr_main_state = mettxt;
    curr_ftbl_state = mettxt;
    setupConverter('#maintbl', true);
    $('#showfilter').on('click', function () {
        if (!ftbl_init) {
            // there is no ftable yet, so don't alter converter setup, just toggle displays
            if ($('#tblfilter').css('display') === 'none') {
                $('#tblfilter').show();
                $(this).text("Close Filter");
                toggleScrollSelect(false);
            }
            else {
                $('#tblfilter').hide();
                $(this).text("Filter Hikes");
                if (scroll_to) {
                    toggleScrollSelect(true);
                }
            }
        }
        else {
            if ($('#tblfilter').css('display') === 'none') {
                $('#tblfilter').show();
                $(this).text("Close Filter");
                $('#refTbl').hide();
                $('#units').text(curr_ftbl_state);
                setupConverter('#ftable', false);
                toggleScrollSelect(false);
            }
            else {
                $('#tblfilter').hide();
                $(this).text("Filter Hikes");
                $('#refTbl').show();
                $('#units').text(curr_main_state);
                setupConverter('#maintbl', false);
                if (scroll_to) {
                    toggleScrollSelect(true);
                }
            }
        }
    });
    $('#multimap').on('click', function () {
        $('#usermodal').show();
    });
    // "Scroll to" drop-down setup
    var hikelist = [];
    var $alph = $('#maintbl').find('tbody tr');
    $alph.each(function () {
        var $link = $(this).children().eq(0).children().eq(0);
        var hikename = $link.text().toLowerCase();
        hikelist.push(hikename[0]);
    });
    var ele_indx = [];
    var scroll = [];
    ele_indx[0] = 0;
    ele_indx[1] = hikelist.indexOf('c') - 1;
    ele_indx[2] = hikelist.indexOf('e') - 1;
    ele_indx[3] = hikelist.indexOf('l') - 1;
    ele_indx[4] = hikelist.indexOf('p') - 1;
    ele_indx[5] = hikelist.indexOf('t') - 1;
    for (var k = 0; k < ele_indx.length; k++) {
        var $elemnt = $alph.eq(ele_indx[k]);
        var coords_1 = $elemnt.offset();
        scroll[k] = coords_1.top;
    }
    $('#scroller').on('change', function () {
        var selected = $(this).val();
        if (selected === 'none') {
            $(window).scrollTop(0);
            $('#backup').hide();
        }
        else {
            var position = parseInt(selected);
            if (position === 0) {
                $(window).scrollTop(0);
                $('#backup').hide();
            }
            else {
                $(window).scrollTop(scroll[position]);
                // sticky position of *rotated* div will always be 60px from top of new viewport
                $('#backup').css('top', '60px');
                $('#backup').show();
            }
        }
    });
    $('#backup').on('click', function () {
        $(this).hide();
        $(window).scrollTop(0);
        $('#scroller').val(0);
    });
    setNodatAlerts();
    $(window).on('resize', function () {
        tablepos = $('#maintbl').offset();
        table_left = (tablepos.left - 78) + 'px';
        $('#backup').css('left', table_left);
    });
});
