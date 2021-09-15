"use strict";
/**
 * @fileoverview For mobile applications only - display hike page [released hikes only]
 *
 * @author Ken Cowles
 * @version 1.0 First release of responsive design
 */
var title = $('#trail').text();
$('#ctr').text(title);
// hike number
var hikeno = $('#hikeno').text();
// js modal
var statsEl = document.getElementById('hikeData');
var hike_stats = new bootstrap.Modal(statsEl, {
    keyboard: false
});
// Move the buttons towards the bottom to prevent blocking collapsed drop-down menu
var chartPlaced = $.Deferred(); // placed in dynamicChart.js
/**
 * Position the hike stats button first, as the favorites will sit on top
 */
var buttonPos = function () {
    var chartpos = $('#chartline').offset();
    var hinfoTop = chartpos.top - 80 + "px";
    $('#hinfo').css('left', '4px');
    $('#hinfo').css('top', hinfoTop);
    return;
};
/**
 * Place the favorites button above the hike stats button
 */
var favoritesPos = function () {
    var statsPos = $('#hinfo').offset();
    var favtop = statsPos.top - 40 + "px";
    var favwidth = $('#favs').width();
    $('#favs').css('left', '4px');
    $('#favs').css('top', favtop);
    $('#hinfo').width(favwidth);
    return;
};
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
        error: function (jqXHR) {
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
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
