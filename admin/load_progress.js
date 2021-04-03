"use strict";
/**
 * @fileoverview Manage the tracking progress of load tables
 *
 * @author Ken Cowles
 * @version 2.0 Typescripted
 */
var pwidth = $('#progress').width();
var $bar = $('#bar');
var go = false;
var barinc;
var $getcnt = setInterval(function () {
    if (typeof (totq) !== "undefined") {
        clearInterval($getcnt);
        totq = totq;
        barinc = Math.floor(pwidth / totq);
        $('#progress').width(barinc * totq);
        go = true;
    }
}, 5);
var $statcheck = setInterval(function () {
    if (go) {
        $bar.width(qcnt * barinc);
    }
}, 100);
