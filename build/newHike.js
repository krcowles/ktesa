$( function() {  // wait until document is loaded...
   
function checkName(name) {
    for (var i=0; i<hnames.length; i++) {
        if (hnames[i] == name) {
            alert("Name exists - try another");
            $('#newname').val('');
            return true;
        }
    }
    return false;
}
$('#newbie').submit( function(ev) {
    if (checkName($('#newname').val()) ) {
        ev.preventDefault();
    }
});
$('#newname').change( function() {
    checkName($(this).val()) 
});

});
