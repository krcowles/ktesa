$(function () { // when page is loaded...

var page = $('#action').text();
var linkbase;
if (page === 'Release') {
    linkbase = 'publish.php?hno=';
} else if (page === 'Delete') {
    linkbase = 'remove.php?hno=';
}
$tbl = $('tbody tr');
$tbl.each( function(indx) {
    var hikeNo = indx + 1;
    var newlink = linkbase + hikeNo;
    $(this).find('td').eq(3).children().attr('href',newlink);    
});


});


