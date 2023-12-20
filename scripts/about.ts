/**
 * @fileoverview A simple page explaining the site's features
 * @author Ken Cowles
 * @version 2.0 Typescripted
 */
$( function() {  // wait until document is loaded...

/**
 * This section manages the 'twisty' text on the bottom of the page
 */
function toggleTwisty(tid:string , ttxt: string, dashed: string) {
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
$('#navfeat').on('click', function() {
    toggleTwisty('n', 'navfeat', 'nul');
});
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
