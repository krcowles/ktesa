// Globals
var isMobile, isTablet, isAndroid, isiPhone, isiPad, supported, portrait;
isMobile = navigator.userAgent.toLowerCase().match(/mobile/i) ?
    true : false;
isTablet = navigator.userAgent.toLowerCase().match(/tablet/i) ?
    true : false;
isAndroid = navigator.userAgent.toLowerCase().match(/android/i) ?
    true : false;
isiPhone = navigator.userAgent.toLowerCase().match(/iphone/i) ?
    true : false;
isiPad = navigator.userAgent.toLowerCase().match(/ipad/i) ?
    true : false;
supported = isMobile && !isTablet && !isiPad;
// Landscape/portrait assessment
portrait = window.matchMedia("(orientation: portrait)").matches;
// Detect portrait/landscape toggling
window.matchMedia("(orientation: portrait)").addEventListener("change", function (e) {
    portrait = e.matches;
    logoMgr();
});
var wht = window.innerHeight;
var wwd = window.innerWidth;
var choices_pos = $('.usr_choices').offset();
var choices_ht = $('.usr_choices').height();
var bottom = choices_pos.top + choices_ht + 12; // 12 for margin
// manage logo items (want hoisting here, so no arrow function)
function logoMgr() {
    if (portrait) {
        $('#logo_left').text('Hike');
        $('#logo_left').css('font-size', '.8rem');
        $('#logo_right').text('NM');
        $('#logo_right').css('font-size', '.8rem;');
        $('#vopts').hide();
        if (wht - bottom > 220) {
            $('#bennies').show();
        }
        else {
            $('#bennies').hide();
        }
    }
    else {
        $('#logo_left').text('Hike New Mexico');
        $('#logo_left').css('font-size', '1rem');
        $('#logo_right').text('W/ Tom & Ken');
        $('#logo_right').css('font-size', '1rem;');
        $('#vopts').show();
        $('#bennies').hide();
    }
    return;
}
logoMgr();
// position title in the logo
var title = $('#trail').text();
// temp
// When testing on laptop and not on phone...
if (wwd <= 450) {
    portrait = true;
    logoMgr();
}
else {
    portrait = false;
    logoMgr();
}
$('#sizes').text(wwd + "," + wht);
$(window).on('resize', function () {
    wht = window.innerHeight;
    wwd = window.innerWidth;
    $('#sizes').text(wwd + "," + wht);
    logoMgr();
});
//
