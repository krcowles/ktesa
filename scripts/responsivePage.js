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
var setMobileView = function () {
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
var buttonPos = function () {
    var chartpos = $('#chartline').offset();
    var hinfoTop = "".concat(chartpos.top - 80, "px");
    $('#hinfo').css('left', '4px');
    $('#hinfo').css('top', hinfoTop);
    return;
};
/**
 * Place the favorites button above the hike stats button
 */
var favoritesPos = function () {
    var statsPos = $('#hinfo').offset();
    var favtop = "".concat(statsPos.top - 40, "px");
    var favwidth = $('#favs').width();
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
    var newtext;
    var favtype;
    if ($('#favs').text() === 'Unmark Favorite') {
        favtype = 'delete';
        newtext = 'Mark as Favorite';
    }
    else {
        favtype = 'add';
        newtext = 'Unmark Favorite';
    }
    var ajaxdata = { action: favtype, no: hikeno };
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
