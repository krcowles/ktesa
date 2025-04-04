/**
 * @fileoverview This module directs the user to a location-based set of assets.
 *  Access is only permitted to club members
 * @version 1.0 First release
 */
const navbox   = document.getElementById('nav') as HTMLElement;
const logobox  = document.getElementById('logo') as HTMLElement;
const sidebar  = document.getElementById('sidebar') as HTMLElement;
const navht    = navbox.getBoundingClientRect();
const logoht   = logobox.getBoundingClientRect();
const rule_top = navbox.clientHeight + logobox.clientHeight + "px";
const side_top = navbox.clientHeight + logobox.clientHeight + 40 + "px";
const vr = document.getElementsByClassName('vertical_rule') as HTMLCollection;
const rule = vr.item(0) as HTMLElement;
rule.style.top = rule_top;
sidebar.style.top = side_top;;

/**
 * Clicking on page items
 */
 $('.location').on('click', function() {
    var locid = this.id;
    var loc_pos = locid.indexOf('sect');
    var area  = "assets.php?area=" + locid.substring(loc_pos);
    window.open(area, '_blank');
 });
 $('#out-of-state').on('click', function() {
    var oos = $('#oos_locs').val();
    var area = "assets.php?area=" + oos;
    window.open(area, '_blank');
 });

 /**
  * Map regions & placement
  */
 var map_img = document.getElementById('nm') as HTMLImageElement;
 var map_dims = map_img.getBoundingClientRect();
 var offset = 0.05*map_dims.height;
 var tray_box = document.getElementById('tray') as HTMLElement;
 tray_box.style.top = offset + "px";
 tray_box.style.height = map_dims.height - 2 * offset + "px";
 var contents = document.getElementById('contents') as HTMLElement;
 var content_box = contents.getBoundingClientRect();
 var map_box = document.getElementById('map_box') as HTMLElement;
 var mapbox_dims = map_box.getBoundingClientRect();
 var overlays_pos = map_box.getBoundingClientRect();
 // position overlay elements wrt/nm map
 $('#howto').css('left', content_box.left);
 // set overlay elements widths wrt/ tray
 var tray_wd = $('#tray').width() as number;
 var per16 = 0.16 * tray_wd;
 var per28 = 0.28 * tray_wd;
 var per33 = 0.33 * tray_wd;
 var per36 = 0.36 * tray_wd;
 var per39 = 0.39 * tray_wd;
 var per40 = 0.40 * tray_wd;
 var per45 = 0.45 * tray_wd;
 var per55 = 0.55 * tray_wd;
 var per60 = 0.60 * tray_wd;
 $('#nw1').width(per36);
 $('#nw2').width(per28);
 $('#nw3').width(per36);
 $('#lnw1').width(per39);
 $('#lnw2').width(per28);
 $('#lnw3').width(per33);
 $('#c1').width(per39);
 $('#c2').width(per16);
 $('#c3').width(per45);
 $('#cs1').width(per45);
 $('#cs2').width(per55);
 $('#s1').width(per40);
 $('#s2').width(per60);

 // position overlays
 var shift = overlays_pos.left - content_box.left; // left from map_box div
 $('#nw2').css('left', per36);
 $('#nw3').css('left', per36 + per28);
 $('#lnw2').css('left', per39);
 $('#lnw3').css('left', per39 + per28);
 $('#c2').css('left', per39);
 $('#c3').css('left', per39 + per16);
 $('#cs2').css('left', per45);
 $('#s2').css('left', per40);

 $('.sections').on('click', function() {
     var id = this.id;
     alert("Assets for " + id);
 });
 $(window).on('resize', function () {
     location.reload();
 });

