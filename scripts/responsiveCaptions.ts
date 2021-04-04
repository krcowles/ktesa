/**
 * @fileoverview This module is responsible for placing and displaying photo captions
 * one the photo rows have been established (using deferred 'picSetupDone')
 * 
 * @author Ken Cowles
 * @version 2.0 Added responsive design capability
 * @version 2.1 Typescripted
 */
$.when(picSetupDone).then(function() {
    var vpHeight = window.innerHeight;
    var navbarht = <number>$('nav').height();
    var logo_ht  = <number>$('#logo').height();
    var mapHt = vpHeight - (navbarht + logo_ht);
    //var chartHt = Math.floor(0.5 * mapHt);
    var mapHeight = mapHt + 'px';
    //var chartHeight = chartHt + 'px';
    $('#mapline').css('height',mapHeight);
    /**
     * Find photos and add captioning display on touch
     */
    var photos: HTMLElement[] = [];
    var $images = <JQuery<HTMLElement>>$('img[id^="pic"]');
    // images do not necessary have sequential picid's, so re-order into $photos
    for (let n=0; n<$images.length; n++) {
        let picid = $images[n].id;
        let id = parseInt(picid.substr(3));
        photos[id] = $images[n];
    }
    var $photos = $(photos); 
    var lasttap = 0;
    /**
     * Double tap detection for showing enlarged photos; do nothing
     * on first tap
     */
    const doubletap = (src: string) => {
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
     */
    const picPop = (pupid: string, buttonid: string) => {
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
        let pup = $popup.get(0);
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
        let $dpimg = <JQuery<HTMLImageElement>>$(this).children().eq(0);
        let imgpos = <JQuery.Coordinates>$dpimg.offset();
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
    
    //var noOfPix = $photos.length;
    var capTop: string[] = [];
    var capLeft: string[] = [];
    var capWidth: string[] = [];
    var picPos: JQuery.Coordinates;
    
    /**
     * Get the width of each image for positioning captions
     */
    const captureWidths = () => {
        $photos.each( function(i) {
            let item = this;
            capWidth[i] = $(item).width + 'px';
        });
        return;
    };
    /**
     * Get the position of each image on the page for positioning captions
     */
    const calcPos = () => {
        $photos.each( function(j) {
            picPos = <JQuery.Coordinates>$(this).offset();
            capTop[j] = Math.round(picPos.top) + 'px';
            capLeft[j] = Math.round(picPos.left) + 'px';
        });
        return;
    };
    captureWidths();
    calcPos();
});
