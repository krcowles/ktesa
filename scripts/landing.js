"use strict"
/**
 * @fileoverview This script performs basic menu operations and page setup
 * for the landing site
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 First responsive design implementation
 */
$(function() {

$('#logout').on('click', function(evt) {
    evt.preventDefault();
    let data = {expire: 'N'};
    $.get('../accounts/logout.php', data, function() {
        location.reload();
    });
});

/* SEARCHBAR NOT SUPPORTED BY BOOTSTRAP
$('#searchbar').val('');
$('#searchbar').on('input', function(ev) {
    var $input = $(this),
       val = $input.val(),
       list = $input.attr('list'),
       match = $('#'+list + ' option').filter(function() {
           return ($(this).val() === val);
       });
    if(match.length > 0) {
        let hikeIndx = hikeObjects[val];
        let link = "../pages/hikePageTemplate.php?hikeIndx=" + hikeIndx;
        $('#goto').attr('href', link);
    }
});
*/

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
