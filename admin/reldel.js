$(function () { // when page is loaded...

var exe = $('#action').text();
var linkbase;
if (exe === 'rel') {
    linkbase = 'publish.php?hno=';
} else if (exe === 'del') {
    linkbase = 'delete.php?hno=';
}
$tbl = $('tbody tr');
$tbl.each( function(indx) {
    var newlink = linkbase + enos[indx];
    $(this).find('td').eq(3).children().attr('href',newlink);    
});

});

