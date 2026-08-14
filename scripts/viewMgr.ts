interface OrientTarget extends EventTarget {
    type: string;
}
/**
 * @fileoverview Manage what shows up in the logo based on available space
 * @author Ken Cowles
 * @version 3.0 Revised for offline maps
 * @version 4.0 Revised for new offline/GPS tracking app
 */
// position title in the logo
var title = $('#trail').text();
var wwd: number;

function displayScreen () {
    const pix = $('.pair > img') as JQuery<HTMLImageElement>;
    switch (screen.orientation.type) {
        case 'landscape-primary':
        case 'landscape-secondary':
            // Reduce 'choices' size
            pix[0].style.height = '110px';
            pix[0].style.width  = 'auto';
            pix[1].style.height = '110px';
            pix[1].style.width  = 'auto';
            // Show benefits, depending on available space...
            benniesDisplay();
            wwd = window.innerWidth;
            logoMgr();
            break;
        case 'portrait-primary':
        case 'portrait-secondary':
            pix[0].style.height = `${pix[0].naturalHeight}`;
            pix[0].style.width  = 'auto';
            pix[1].style.height = `${pix[1].naturalHeight}`;
            pix[1].style.width  = 'auto';
            benniesDisplay();
            wwd = window.innerWidth;
            logoMgr();
    }
}
// Show or hide the benefits div
function benniesDisplay() {
    $('#bennies').css('top', '12px'); // see consumed_space expression
        const logo_ht = $('#logo').outerHeight(true) as number;
        const opts = $('#opts').outerHeight(true) as number;
        const user_ht = $('#usr_choices').outerHeight() as number;
        const bene_space = $('#bennies').outerHeight(true) as number;
        const consumed_space = logo_ht + opts + user_ht + 12;
        const view_space = window.innerHeight;
        const bene_alloc = view_space - consumed_space;
        if (bene_alloc <= bene_space) {
            $('#bennies').hide();
        } else {
            if ($('#member').text() !== '0') { // logged in member
                $('#bennies').hide();
            } else { // not logged in
                $('#bennies').show();
            }   
        }
        wwd = window.innerWidth;
        logoMgr();
}
// Manage logo items
const logoMgr = () => {
    // calculate space available for logo items
    const logo = document.getElementById('pgheader') as HTMLDivElement;
    const logo_wd = logo.offsetWidth;
    // left icons have margin-left; right icon have margin-right
    const hikers = document.getElementById('hikers') as HTMLImageElement;
    const hikers_wd = hikers.offsetWidth + 10; // margin-left
    const map_icon = document.getElementById('tmap') as HTMLImageElement;
    const map_wd = map_icon.offsetWidth + 8; // margin-right
    const left_txt = document.getElementById('logo_left') as HTMLSpanElement;
    const right_txt = document.getElementById('logo_right') as HTMLSpanElement;
    const lwidth = left_txt.offsetWidth + 12; // margin-left
    const rwidth = right_txt.offsetWidth + 10; // margin-right
    const center = document.getElementById('ctr') as HTMLDivElement;
    const cwidth = center.offsetWidth;
    const available = logo_wd - cwidth;
     // add 4px to increase margins
    const sum_all = hikers_wd + lwidth + map_wd + rwidth + 4;
    const sum_icons = hikers_wd + map_wd + 4;
    if (sum_all > available) {
        if (sum_icons > available) {
            left_txt.style.display = "none";
            right_txt.style.display = "none";
            hikers.style.display = "none";
            map_icon.style.display = "none";
        } else {
            left_txt.style.display = "none";
            right_txt.style.display = "none";
        }
    }
};
displayScreen();
screen.orientation.addEventListener("change", () => {
    displayScreen;
});
