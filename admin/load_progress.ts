// variables defined via php and embedded in loader.php
declare var totq: number;
declare var qcnt: number;
/**
 * @fileoverview Manage the tracking progress of load tables
 * 
 * @author Ken Cowles
 * @version 2.0 Typescripted
 */
var pwidth = <number>$('#progress').width();
var $bar = $('#bar');
var go = false;
var barinc: number;
let $getcnt = setInterval( function () {
    if (typeof(totq) !== "undefined") {
        clearInterval($getcnt);
        totq = totq;
        barinc = Math.floor(pwidth/totq);
        $('#progress').width(barinc * totq);
        go = true;
    }
}, 5);
let $statcheck = setInterval( function() {        
    if (go) {
        $bar.width(qcnt * barinc);
    }
}, 100);


