$( function() {  // wait until document is loaded...
/*
 * To prevent someone hitting the 'Enter' key and accidentally submitting
 */ 
$(function(){
    var keyStop = {
        //8: ":not(input:text, textarea, input:file, input:password)", // stop backspace = back
        13: "input:text", // stop enter = submit 
        end: null
    };
    $(document).on("keydown", function(event){
        var selector = keyStop[event.which];
        if(selector !== undefined && $(event.target).is(selector)) {
            event.preventDefault(); //stop event
        }
        return true;
    });
});   
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
    $('#advise').css('display','block');
});
$('#newname').change( function() {
    checkName($(this).val()) 
});

});
