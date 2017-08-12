$( function() {  // wait until document is loaded...
   
$('#newname').change(function() {
    for (var i=0; i<hnames.length; i++) {
        if (hnames[i] == $(this).val()) {
            alert("Name exists - try another");
            break;
        }
    }
}); 

});
