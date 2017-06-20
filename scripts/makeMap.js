// Import the map html via php and edit the iframe to include it
// Currently not used - using tmp stored version of map in iframe
$(document).ready( function() {
    var hikemap = $('#mapcode').val();
    //var test = "<p>yeh, sure</p>";
    $('#mapline').contents().find('body').html(hikemap);
});


