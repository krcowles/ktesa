/**
 * @fileoverview Manage what shows up in the logo based on available space
 * @author Ken Cowles
 * @version 3.0 Revised for offline maps
 */
// NOTE: By definition this file applies to mobile devices only
var wwd = window.innerWidth;
// manage logo items: see landing.js for 'bennies' display
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
logoMgr();
// position title in the logo
var title = $('#trail').text();

$(window).on('resize', function() {
    wwd = window.innerWidth;
    logoMgr();
});
