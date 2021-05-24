/**
 * @fileoverview This script performs basic menu operations and page setup
 * for the landing site
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 First responsive design implementation
 * @version 1.1 Typescripted
 */
$(function() {

// Setup modal as a user presentation for any ajax errors.
var ajaxerror = new bootstrap.Modal(<HTMLElement>document.getElementById('ajaxerr'), {
    keyboard: false
});

$('#logout').on('click', function(evt) {
    evt.preventDefault();
    let data = {expire: 'N'};
    $.ajax({
        url: '../accounts/logout.php',
        data: data,
        success: function() {
            location.reload();
        },
        error: function() {
            ajaxerror.show();
            let errobj = {err: "Failure in mobile logout"};
            $.post('../php/ajaxError.php', errobj);
        }
    });
});
/**
 * Page links
 */
$('#choice1').on('click', function() {
    window.open("../pages/responsiveTable.php", "_self");
});
$('#choice2').on('click', function() {
    window.open("../pages/mapOnly.php", "_self");
});

});
