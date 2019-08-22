$( function() {  // wait until document is loaded...

// ktesaPanel adjustments for unique page constraints of home page:
function centerLabel() {
    var bodyLoc  = $('body').offset();
    var txtLeft = bodyLoc.left + 415 + 'px';
    var txtTop  = bodyLoc.top + 33 + 'px';
    $('#homeLabel').css('left', txtLeft);
    $('#homeLabel').css('top', txtTop);
}
centerLabel();
$(window).resize(centerLabel);

/**
 * This section manages the 'twisty' text on the bottom of the page
 */
function toggleTwisty(tid, ttxt, dashed) {
    var feature = $('#' + ttxt);
    var twisty  = $('#' + tid);
    var list = $('#' + dashed);
    if (twisty.hasClass('twisty-right')) {
        twisty.removeClass('twisty-right');
        twisty.addClass('twisty-down');
        feature.css('top', '-6px');
    } else {
        twisty.removeClass('twisty-down');
        twisty.addClass('twisty-right');
        feature.css('top', '-4px');
    }
    list.slideToggle();
}
$('#mapfeat').on('click', function() {
    toggleTwisty('m', 'mapfeat', 'mul');
});
$('#tblfeat').on('click', function() {
    toggleTwisty('t', 'tblfeat', 'tul');
});
$('#hikefeat').on('click', function() {
    toggleTwisty('h', 'hikefeat', 'hul');
});

}); // end of page-loading wait statement
