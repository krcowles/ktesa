$(function () { // when page is loaded...

$tbl = $('tbody tr');
$tbl.each( function(indx) {
    var pg = '../pages/hikePageTemplate.php?age=new&hikeIndx=' + $(this).data('indx');
    $(this).find('td').eq(3).children().attr('href',pg);    
});

});
