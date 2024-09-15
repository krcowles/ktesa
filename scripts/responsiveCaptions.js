"use strict";
/**
 * @fileoverview This module is responsible for placing and displaying photo captions
 * once the photo rows have been established (using deferred 'picSetupDone')
 *
 * @author Ken Cowles
 * @version 2.0 Added responsive design capability
 * @version 2.1 Typescripted
 */
$.when(picSetupDone, docReady).then(function () {
    /**
     * Find photos and add captioning display on touch
     */
    var photos = [];
    var $images = $('img[id^="pic"]');
    // images do not necessary have sequential picid's, so re-order into $photos
    for (var n = 0; n < $images.length; n++) {
        var picid = $images[n].id;
        var id = parseInt(picid.substr(3));
        photos[id] = $images[n];
    }
    var $photos = $(photos);
    var lasttap = 0;
    /**
     * Double tap detection for showing enlarged photos; do nothing
     * on first tap
     */
    var doubletap = function (src) {
        var now = new Date().getTime();
        var timesince = now - lasttap;
        if ((timesince < 600) && (timesince > 0)) {
            // This doesn't execute, though the code actually gets here...
            lasttap = new Date().getTime();
            window.open(src, "_blank");
            return;
        }
        lasttap = new Date().getTime();
        return;
    };
    /**
     * Function responsible for showing the caption and turning back on the
     * previously hidden button, if there is one.
     */
    var picPop = function (pupid, buttonid) {
        // get the corresponding description
        var $popup = $(pupid);
        var picNo = parseInt(pupid.substr(4));
        $popup.css({
            display: 'block',
            top: capTop[picNo],
            left: capLeft[picNo],
            width: capWidth[picNo],
            zIndex: '100'
        });
        var pup = $popup.get(0);
        // touch to turn off again
        pup.addEventListener('click', function () {
            $popup.hide();
            $('#' + buttonid).show();
        });
        return;
    };
    // set initial caption button placement and event triggers
    $('.imgs').each(function () {
        // caption id
        var pupid = '#pup' + this.id;
        var btnid = "cbtn" + this.id;
        // get image location
        var $dpimg = $(this).children().eq(0);
        var imgpos = $dpimg.offset();
        // establish caption button location and event
        var $caption_btn = $(capbtn);
        $caption_btn.attr('id', btnid);
        $caption_btn.css({
            position: 'absolute',
            top: imgpos.top,
            left: imgpos.left,
            zIndex: '100'
        });
        $(this).prepend($caption_btn);
        // add touchstart listener
        var button = $caption_btn.get(0);
        button.addEventListener('touchstart', function () {
            $caption_btn.hide();
            picPop(pupid, btnid);
        });
        var photo = $dpimg.get(0);
        photo.addEventListener('touchstart', function () {
            doubletap(this.src);
        });
    });
    //var noOfPix = $photos.length;
    var capTop = [];
    var capLeft = [];
    var capWidth = [];
    var picPos;
    /**
     * Get the width of each image for positioning captions
     */
    var captureWidths = function () {
        $photos.each(function (i) {
            var item = this;
            capWidth[i] = $(item).width + 'px';
        });
        return;
    };
    /**
     * Get the position of each image on the page for positioning captions
     */
    var calcPos = function () {
        $photos.each(function (j) {
            picPos = $(this).offset();
            capTop[j] = Math.round(picPos.top) + 'px';
            capLeft[j] = Math.round(picPos.left) + 'px';
        });
        return;
    };
    captureWidths();
    calcPos();
});
