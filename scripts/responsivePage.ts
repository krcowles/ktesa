/**
 * @fileoverview For mobile applications only - display hike page [released hikes only]
 * 
 * @author Ken Cowles
 * @version 1.0 First release of responsive design
 */
let title = $('#trail').text();
$('#ctr').text(title);

// hike number
var hikeno = $('#hikeno').text();

// js modal
var statsEl = <HTMLElement>document.getElementById('hikeData');
var hike_stats = new bootstrap.Modal(statsEl, {
    keyboard: false
});

// Move the buttons towards the bottom to prevent blocking collapsed drop-down menu
var chartPlaced = $.Deferred(); // placed in dynamicChart.js
/**
 * Position the hike stats button first, as the favorites will sit on top
 */
const buttonPos = () => {
    let chartpos = <JQuery.Coordinates>$('#chartline').offset();
    let hinfoTop = `${chartpos.top - 80}px`;
    $('#hinfo').css('left', '4px');
    $('#hinfo').css('top', hinfoTop);
    return;
}
/**
 * Place the favorites button above the hike stats button
 */
const favoritesPos = () => {
    let statsPos = <JQuery.Coordinates>$('#hinfo').offset();
    let favtop   = `${statsPos.top - 40}px`;
    let favwidth = <number>$('#favs').width();
    $('#favs').css('left', '4px');
    $('#favs').css('top', favtop);
    $('#hinfo').width(favwidth);
    return;
}
$.when( chartPlaced ).then(function() {
    buttonPos();
    favoritesPos();
});

$('#favs').on('click', function() {
    let newtext: string;
    let favtype: string;
    if ($('#favs').text() === 'Unmark Favorite') {
        favtype = 'delete';
        newtext = 'Mark as Favorite';
    } else {
        favtype = 'add';
        newtext = 'Unmark Favorite';
    }
    let ajaxdata = {action: favtype, no: hikeno};
    $.ajax({
        url: 'markFavorites.php',
        data: ajaxdata,
        method: "post",
        success: function() {
            if (favtype === 'add') {
                $('#favs').removeClass('btn-primary');
                $('#favs').addClass('btn-danger');
            } else {
                $('#favs').removeClass('btn-danger');
                $('#favs').addClass('btn-primary');
            }
            $('#favs').text(newtext);
        },
        error: function (jqXHR) {
            let newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
        }
    });
});
window.addEventListener('orientationchange', function() {
    location.reload();
});

/**
 * For testing purposes only:
    $(window).on('resize', function() {
        buttonPos();
        favoritesPos();
    });
*/