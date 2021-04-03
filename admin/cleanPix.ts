/**
 * @fileoverview This script simply manages checkbox stat3s
 * 
 * @author Ken Cowles
 * @version 2.0 Typescripted
 */
$(function () { // when page is loaded...

var $chkboxes = $('[id^="chkbox"]');
$('#all').on('click', function() {
    if ($(this).prop('checked')) {
        $chkboxes.each( function() {
            $(this).prop('checked', true);
        });
    } else {
        $chkboxes.each( function() {
            $(this).prop('checked', false);
        });
    }
});

});
