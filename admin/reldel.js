$(function () { // when page is loaded...

var exe = $('#action').text();
var linkbase;
if (exe === 'rel') {
    linkbase = 'publish.php?hno=';
} else if (exe === 'del') {
    linkbase = 'delete.php?hno=';
}
var $tbl = $('tbody tr');
var hikeCol;
var $hdr = $('table thead').find('th');
$hdr.each( function(indx) {
    if ($(this).text() === 'Hike/Trail Name') {
        hikeCol = indx;
    }
});
$tbl.each( function(indx) {

    var newlink = linkbase + enos[indx];
    $(this).find('td').eq(hikeCol).children().attr('href',newlink); 
    $(this).find('td').eq(hikeCol).children().attr('target','');   
});

});
