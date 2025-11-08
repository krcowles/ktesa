/**
 * @fileoverview This is reusable code for sizing logo elements
 * 
 * @author Ken Cowles
 * @version 1.0 First release / responsive design
 * @version 1.1 Typescripted
 */

/**
 * Small screens:
 */
var ss = (vw: number) => {
    if (vw < 500) {
        $('#logo_left').text('Hike');
        $('#logo_right').text('NM');
    }
    return;
};

// @media (width)
var vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
ss(vw);
// for testing only
$(window).on('resize', function() {
    vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
    ss(vw);
});
// position title in the logo
var title = $('#trail').text();
