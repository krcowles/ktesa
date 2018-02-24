$(function () { // when page is loaded...
// imported vars from php: cnt (int) pgLinks (json array) albTypes (json array)
var alinks = JSON.stringify(pgLinks);
var atypes = JSON.stringify(albTypes);
var ajaxdata = { 'cnt': cnt, 'albs': alinks, 'types': atypes };
$.ajax({
    type: 'POST',
    url: 'getPicDat.php',
    dataType: 'text',
    data: ajaxdata,
    success: function(result) {
        // result is an array of objects in string form
        picdata = JSON.parse(result);
        alert("Result: " + picdata[0]);
        $('#loader').css('display','none');
        $('#main').css('display','block');
    },
    error: function(jq, errmsg, stat) {
        var emsg = "Failed to execute getPicDat.php: " + errmsg + "; " + stat;
        alert(emsg);
    }
});

});