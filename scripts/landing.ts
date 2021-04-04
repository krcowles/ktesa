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

$('#logout').on('click', function(evt) {
    evt.preventDefault();
    let data = {expire: 'N'};
    $.get('../accounts/logout.php', data, function() {
        location.reload();
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
