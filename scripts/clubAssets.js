/**
 * @fileoverview This module directs the user to a location-based set of assets.
 *               Access is only permitted to club members
 * @version 1.0 First release
 */
$(function () {
    var winwidth = window.innerWidth;
    var navbox = document.getElementById('nav');
    var logobox = document.getElementById('logo');
    var sidebar = document.getElementById('sidebar');
    var region = document.getElementById('regionid');
    var contents = document.getElementById('contents');
    var mapbox = document.getElementById('map_box');
    var tray = document.getElementById('tray');
    var image = document.getElementById('nmap');
    // position vertical bar just below logo
    var vr = document.getElementsByClassName('vertical_rule');
    var rule = vr.item(0);
    var rule_top = navbox.clientHeight + logobox.clientHeight + "px";
    rule.style.top = rule_top;
    // position 'contents' div
    var sidewidth = sidebar.getBoundingClientRect();
    var avail_width = winwidth - sidewidth.width - 60 + "px";
    contents.style.width = avail_width;
    contents.style.height = avail_width;
    // position tray inside image
    var imgbox = image.getBoundingClientRect();
    tray.style.width = 0.98 * imgbox.width + "px";
    tray.style.height = 0.99 * imgbox.height + "px";
    // position rotated text adjacent to #contents
    var content_div = contents.getBoundingClientRect();
    var midbox = content_div.height / 2;
    var tray_loc = tray.getBoundingClientRect();
    region.style.top = midbox - 40 + "px";
    region.style.left = tray_loc.left - 40 + "px";
    /**
     * If there was an alert issued from a file upload:
     */
    var upload_err = $('#alert').text();
    if (upload_err !== '') {
        alert(upload_err);
    }
    /**
     * Show region name in regionid box when cursoring over area
     */
    var region_names = [
        'NW Deserts', 'Jemez & Abiquiu', 'Sangre de Cristos',
        'NE Plains', 'Mt Taylor & Zuni', 'Sandias & Manzanos', 'Gila & Bootheel',
        'Lower Rio Grande', 'Sierra Blanca Region', 'SE New Mexico'
    ];
    $('div[id^=box]').on('mouseenter', function () {
        var id = this.id;
        var areano = id.substring(3);
        $('#pointer').text(id);
        var index = parseInt(areano) - 1; // 0-based array;
        $('#regionid').text(region_names[index]);
    });
    $('div[id^=box]').on('mouseleave', function () {
        $('#regionid').text("");
        $('#pointer').text("");
    });
    /**
     * Clicking on map regions: because boxes are overlapping elements
     * with the tray, the tray receives the click, then finds out where
     * the cursor is at the time of the click. The div id is written to
     * the #pointer elements by the code above.
     */
    $('#tray').on('click', function () {
        var loc = $('#pointer').text();
        var area = "assetsPage.php?area=" + loc;
        window.open(area, '_blank');
    });
    // to access out of state assets, the select box is utilized
    $('#out-of-state').on('click', function () {
        var oos = $('#oos_locs').val();
        var area = "assetsPage.php?area=" + oos;
        window.open(area, '_blank');
    });
    /**
     * Uploading assets
     */
    $('#uploader').on('submit', function (ev) {
        var upload = document.getElementById('filename');
        var assetfile = upload.files;
        if (assetfile.length == 0) {
            alert("There is nothing to upload");
            return false;
        }
    });
    $(window).on('resize', function () {
        location.reload();
    });
});
