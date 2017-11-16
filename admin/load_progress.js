var pwidth = parseInt($('#progress').width());
var $bar = $('#bar');
var go = false;
var barinc;
$getcnt = setInterval( function () {
    if (typeof(totq) !== "undefined") {
        clearInterval($getcnt);
        totq = parseInt(totq);
        barinc = Math.floor(pwidth/totq);
        $('#progress').width(barinc * totq);
        go = true;
    }
}, 5);
$statcheck = setInterval( function() {        
    if (go) {
        $bar.width(qcnt * barinc);
    }
}, 100);


