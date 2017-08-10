$( function() {  // wait until document is loaded...
    
$('#save').on( 'click', function() {
    var goto = 'newSave.php';
    window.open(goto,"_blank");
});
$('#cont').on( 'click', function() {
    var goto = 'enterHike.php?hikeno=' + $('#assigned').text();
    window.opoen(goto,"_blank");
});
    
});
