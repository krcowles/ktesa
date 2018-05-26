$(function () { // when page is loaded...

var $tbl = $('tbody tr');
var hikeCol;
var $hdr = $('table thead').find('th');
$hdr.each( function(indx) {
    if ($(this).text() === 'Hike/Trail Name') {
        hikeCol = indx;
    }
});
$tbl.each( function() {
    var pg = '../pages/hikePageTemplate.php?age=new&hikeIndx=' + $(this).data('indx');
    $(this).find('td').eq(hikeCol).children().attr('href',pg);    
});

});
