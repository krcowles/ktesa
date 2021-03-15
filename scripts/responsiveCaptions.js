/**
 * @fileoverview This module is responsible for placing and displaying photo captions
 * one the photo rows have been established (using deferred 'picSetupDone')
 * 
 * @author Ken Cowles
 * @version 2.0 Added responsive design capability
 */
$.when(picSetupDone).then(function() {
    var vpHeight = window.innerHeight;
    var navbarht = $('nav').height();
    var logo_ht  = $('#logo').height();
    var mapHeight = vpHeight - (navbarht + logo_ht);
    var chartHeight = Math.floor(0.5 * mapHeight);
    mapHeight += 'px';
    chartHeight += 'px';
    $('#mapline').css('height',mapHeight);
    /**
     * Find photos and add captioning display on touch
     */
    var photos = [];
    var $images = $('img[id^="pic"]');
    // images do not necessary have sequential picid's, so re-order into $photos
    for (let n=0; n<$images.length; n++) {
        let picid = $images[n].id;
        let id = picid.substr(3);
        photos[id] = $images[n];
    }
    $photos = $(photos);
    var lasttap = 0;
    /**
     * Double tap detection for showing enlarged photos; do nothing
     * on first tap
     * 
     * @returns {null}
     */
    const doubletap = (src) => {
        var now = new Date().getTime();
        var timesince = now - lasttap;
        if((timesince < 600) && (timesince > 0)){
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
     * @param {string} picTarget The image for which a caption is to be displayed
     * @param {string} buttonid The last button to have been hidden
     * 
     * @return {null}
     */
    const picPop = (pupid, buttonid) => {
        // get the corresponding description
        let $popup = $(pupid);
        let picNo = parseInt(pupid.substr(4));
        $popup.css({
            display: 'block',
            top: capTop[picNo],
            left: capLeft[picNo],
            width: capWidth[picNo],
            zIndex: '100'
        });
        pup = $popup.get(0);
        // touch to turn off again
        pup.addEventListener('click', function() {
            $popup.hide();
            $('#' + buttonid).show();
        });
        return;
    };
    // set initial caption button placement and event triggers
    $('.imgs').each(function() {
        // caption id
        let pupid = '#pup' + this.id;
        let btnid = "cbtn" + this.id;
        // get image location
        let $dpimg = $(this).children().eq(0);
        let imgpos = $dpimg.offset();
        // establish caption button location and event
        let $caption_btn = $(capbtn);
        $caption_btn.attr('id', btnid);
        $caption_btn.css({
            position: 'absolute',
            top: imgpos.top,
            left: imgpos.left,
            zIndex: '100'
        });
        $(this).prepend($caption_btn);
        // add touchstart listener
        let button = $caption_btn.get(0);
        button.addEventListener('touchstart', function() {
            $caption_btn.hide();
            picPop(pupid, btnid);
        });
        let photo = $dpimg.get(0);
        photo.addEventListener('touchstart', function() {
            doubletap(this.src);
        });
    });
    
    var noOfPix = $photos.length;
    var capTop = [];
    var capLeft = [];
    var capWidth = [];
    var picPos;
    
    /**
     * Get the width of each image for positioning captions
     * 
     * @return {null}
     */
    const captureWidths = () => {
        $photos.each( function(i) {
            capWidth[i] = this.width + 'px';
        });
        return;
    };
    /**
     * Get the position of each image on the page for positioning captions
     * 
     * @returns {null}
     */
    const calcPos = () => {
        $photos.each( function(j) {
            picPos = $(this).offset();
            capTop[j] = Math.round(picPos.top) + 'px';
            capLeft[j] = Math.round(picPos.left) + 'px';
        });
        return;
    };
    captureWidths();
    calcPos();
});
