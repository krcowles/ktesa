var allIncs = 5;
var circleStart = 87.964; // full value of stroke-dashoffset
var inc = circleStart/allIncs;
var meterNo = 0;  // start with the first meter;
var prog = 0;

$progcheck = setInterval( function() {
    if (typeof(finpart) !== 'undefined') {
        if (finpart < 4) {
            prog = (1 - finpart/allIncs) * inc;
            var $progress = $('#mtr');
            var mdate = new Date();
            timers += "progress: " + mdate + " ";
            //$progress[0].setAttribute('stroke-dashoffset', prog);
        } else {
            clearInterval($progcheck);
        }
    }
}, 1);
