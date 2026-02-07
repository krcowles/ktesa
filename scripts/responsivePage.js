"use strict";
/**
 * @fileoverview For mobile applications only - display hike page [released hikes only]
 *
 * @author Ken Cowles
 * @version 1.0 First release of responsive design
 * @version 1.1 Added typescript declaration for 'title' found in logo.js
 */
$('#ctr').text(title);
var appMode = $('#appMode').text();
// hike number
var hikeno = $('#hikeno').text();
// js modal
var statsEl = document.getElementById('hikeData');
var hike_stats = new bootstrap.Modal(statsEl, {
    keyboard: false
});
// asynch promises
var chartPlaced = $.Deferred(); // placed in dynamicChart.js 
var docReady = $.Deferred(); // Timing required to set captions properly on top of pix
// establish globals for placing map & chart in viewport
var $mapEl;
var $chartEl;
var canvasEl;
// Establish the placement of the map & chart in the viewport on load & resize
const setMobileView = () => {
    var canvasWidth;
    // Height calcs
    var vpHeight = window.innerHeight;
    var consumed = $('#nav').height() + $('#logo').height();
    var usable = vpHeight - consumed;
    var mapHt = Math.floor(0.64 * usable);
    var chartHt = Math.floor(0.35 * usable);
    $mapEl.height(mapHt);
    $chartEl.height(chartHt);
    // Width calcs
    var availWidth = $(window).width();
    availWidth = Math.floor(availWidth) - 2;
    $mapEl.width(availWidth);
    $chartEl.width(availWidth);
    // set up canvas inside chartline div
    if (chartHt < 100) {
        $chartEl.height(100);
        canvasEl.height = 100;
    }
    else {
        canvasEl.height = chartHt;
    }
    canvasWidth = availWidth;
    canvasEl.width = canvasWidth;
};
$(function () {
    $mapEl = $('#mapline');
    $chartEl = $('#chartline');
    canvasEl = document.getElementById('grph');
    setMobileView();
    docReady.resolve();
});
/**
 * Position the hike stats button first, as the favorites will sit on top
 */
const buttonPos = () => {
    let chartpos = $('#chartline').offset();
    let hinfoTop = `${chartpos.top - 80}px`;
    $('#hinfo').css('left', '4px');
    $('#hinfo').css('top', hinfoTop);
    return;
};
/**
 * Place the favorites button above the hike stats button
 */
const favoritesPos = () => {
    let statsPos = $('#hinfo').offset();
    let favtop = `${statsPos.top - 40}px`;
    let favwidth = $('#favs').width();
    $('#favs').css('left', '4px');
    $('#favs').css('top', favtop);
    $('#hinfo').width(favwidth);
    return;
};
// Move the buttons towards the bottom to prevent blocking collapsed drop-down menu
$.when(chartPlaced).then(function () {
    buttonPos();
    favoritesPos();
});
$('#favs').on('click', function () {
    let newtext;
    let favtype;
    if ($('#favs').text() === 'Unmark Favorite') {
        favtype = 'delete';
        newtext = 'Mark as Favorite';
    }
    else {
        favtype = 'add';
        newtext = 'Unmark Favorite';
    }
    let ajaxdata = { action: favtype, no: hikeno };
    $.ajax({
        url: 'markFavorites.php',
        data: ajaxdata,
        method: "post",
        success: function () {
            if (favtype === 'add') {
                $('#favs').removeClass('btn-primary');
                $('#favs').addClass('btn-danger');
            }
            else {
                $('#favs').removeClass('btn-danger');
                $('#favs').addClass('btn-primary');
            }
            $('#favs').text(newtext);
        },
        error: function (_jqXHR, _textStatus, _errorThrown) {
            if (appMode === 'development') {
                var newDoc = document.open();
                newDoc.write(_jqXHR.responseText);
                newDoc.close();
            }
            else { // production
                var msg = "An error has occurred: " +
                    "We apologize for any inconvenience\n" +
                    "The webmaster has been notified; please try again later";
                alert(msg);
                var ajaxerr = "Trying to access [];\nError text: " +
                    _textStatus + "; Error: " + _errorThrown + ";\njqXHR: " +
                    _jqXHR.responseText;
                var errobj = { err: ajaxerr };
                $.post('../php/ajaxError.php', errobj);
            }
        }
    });
});
var multiDwnld = new bootstrap.Modal(document.getElementById('multigpx'));
/**
 * The hike info modal has an option to download the hike's gpx file
 * NOTE:
 * couldn't get php headers to download via $.post, so created a local
 * download file and then removed it after downloading via javascript.
 */
function downloadURI(gpxfile) {
    var link = document.createElement("a");
    link.download = gpxfile;
    link.href = gpxfile;
    link.click();
    // without delay, the download doesn't complete for multiple gpx
    setTimeout(function () {
        $.post("deleteGpx.php", { gpx: gpxfile }, function () {
            link.remove();
        });
    }, 250);
}
$('#dwn').on('click', function (ev) {
    ev.preventDefault();
    $('#idfiles').empty();
    if (gpx_file_list.length > 1) {
        // multiple gpx files...
        var ajax_files;
        for (var k = 0; k < gpx_file_list.length; k++) {
            var dwnldItem = gpx_file_list[k];
            var gpx_val = Object.keys(dwnldItem);
            var gpx_filename = gpx_val[0];
            var json_arr = dwnldItem[gpx_filename];
            ajax_files = '';
            json_arr.forEach(function (file, i) {
                if (i === 0) {
                    ajax_files = file;
                }
                else {
                    ajax_files += ',' + file;
                }
            });
            var list_el = '<li><a class="dwnldgpx" href="#">' +
                gpx_filename + '</a><span style="display:none;">' +
                ajax_files + '</span></li>';
            $('#idfiles').append(list_el);
        }
        multiDwnld.show();
    }
    else {
        // single gpx file: may be multiple json files...
        var ajax_files = '';
        var main = gpx_file_list[0];
        var gpx_val = Object.keys(main);
        var gpx_filename = gpx_val[0];
        var json_arr = main[gpx_filename];
        json_arr.forEach(function (file, i) {
            if (i === 0) {
                ajax_files = file;
            }
            else {
                ajax_files += ',' + file;
            }
        });
        // there is no error callback for $.post()
        $.post("makeGpx.php", { id: hikeno, name: gpx_filename, json_files: ajax_files }, function () {
            downloadURI(gpx_filename);
        });
    }
});
$('body').on('click', '.dwnldgpx', function (ev) {
    ev.preventDefault();
    var gpx = $(this).text();
    var file_list = $(this).next().text();
    $.post("makeGpx.php", { id: hikeno, name: gpx, json_files: file_list }, function () {
        downloadURI(gpx);
    });
    return;
});
window.addEventListener('orientationchange', function () {
    location.reload();
});
/**
 * For testing purposes only:
    $(window).on('resize', function() {
        buttonPos();
        favoritesPos();
    });
*/ 
