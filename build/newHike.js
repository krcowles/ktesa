/*
 * To prevent someone hitting the 'Enter' key and over-riding the duplicate
 * name-checking alert/reset:
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

// DETECT duplicate name:
var match = false;
$('#newname').change(function() {
    for (var i=0; i<hnames.length; i++) {
        if (hnames[i] == $(this).val()) {
            match = true;
        }
        if (match) {
            alert("Name exists - try another");
            $(this).val('');
            break;
        } 
    } 
    if ( match || $(this).val() === '' ) {
        dupName = true;
    } else {
        dupName = false;
    }
    match = false;
}); 

$('#startpg').on('submit', function(event) {
    event.preventDefault();
    if ( $('#newname').val() === '') {
        alert("Please provide a name");
    }
    if (!dupName) {
        $(this).off('submit').submit();
        $('#closeit').css('display','none');
        $('#advise').css('display','block');
    }
});
