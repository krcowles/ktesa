interface DivPos {
    top: number;
    left: number;
}
/**
 * @fileoverview This module directs the user to a location-based set of assets.
 *               Access is only permitted to club members
 * @version 1.0 First release
 */
$( function() {
    const winwidth  = window.innerWidth;
    const navbox   = document.getElementById('nav') as HTMLElement;
    const logobox  = document.getElementById('logo') as HTMLElement;
    const sidebar  = document.getElementById('sidebar') as HTMLElement;
    const region = document.getElementById('regionid') as HTMLElement;
    const contents = document.getElementById('contents') as HTMLElement;
    const mapbox   = document.getElementById('map_box') as HTMLElement;
    const tray     = document.getElementById('tray') as HTMLElement;
    const image    = document.getElementById('nmap') as HTMLElement;
    // position vertical bar just below logo
    const vr   = document.getElementsByClassName('vertical_rule') as HTMLCollection;
    const rule = vr.item(0) as HTMLElement;
    const rule_top = navbox.clientHeight + logobox.clientHeight + "px";
    rule.style.top = rule_top;
    // position 'contents' div
    const sidewidth = sidebar.getBoundingClientRect();
    const avail_width = winwidth - sidewidth.width - 60 + "px";
    contents.style.width = avail_width;
    contents.style.height = avail_width;
    // position tray inside image
    const imgbox = image.getBoundingClientRect();
    tray.style.width = 0.98 * imgbox.width + "px";
    tray.style.height = 0.99 * imgbox.height + "px";
    // position rotated text adjacent to #contents
    const content_div = contents.getBoundingClientRect();
    const midbox = content_div.height/2;
    const tray_loc = tray.getBoundingClientRect();
    region.style.top = midbox - 40 + "px";
    region.style.left = tray_loc.left - 40 + "px";
  
    /**
     * If there was an alert issued from a file upload:
     */
    const upload_err = $('#alert').text();
    if (upload_err !== '') {
        alert(upload_err );
    }
    /**
     * Show region name in regionid box when cursoring over area
     */
    const region_names = [
        'NW Deserts', 'Abiquiu & Chama', 'Taos', 'Raton & NE', 'Jemez',
        'Pecos', 'Mt Taylor & Zuni', 'Sandias & Manzanos',
        'Gila & Bootheel', 'Lower Rio Grande', 'Sierra Blanca Region',
        'Pecos Valley & SE'
    ];
    $('div[id^=box]').on('mouseenter', function() {
        let id = this.id;
        let areano = id.substring(3); 
        $('#pointer').text(id);
        let index = parseInt(areano) - 1; // into 0-based array;
        $('#regionid').text(region_names[index]);
    });
    $('div[id^=box]').on('mouseleave', function() {
        $('#regionid').text("");
        $('#pointer').text("");
    });

    /**
     * Clicking on map regions: because boxes are overlapping elements
     * with the tray, the tray receives the click, then finds out where
     * the cursor is at the time of the click. The div id is written to
     * the #pointer elements by the code above.
     */
    $('#tray').on('click', function() {
        var loc = $('#pointer').text();
        var reg = parseInt(loc.substring(3));
        var region = region_names[reg-1];
        var new_loc = encodeURIComponent(region);
        var area  = "assetsPage.php?area=" + new_loc;
        window.open(area, '_blank');
    })
    // to access out of state assets, the select box is utilized
    $('#out-of-state').on('click', function() {
        var oos = $('#oos_locs').val();
        var area = "assetsPage.php?area=" + oos;
        window.open(area, '_blank');
    });
    /**
     * Uploading assets: check for empty files before submitting form
     */
    $('#uploader').on('submit', function(ev) {
        let upload = document.getElementById('filename') as HTMLInputElement;
        let assetfile = upload.files as FileList;
        if (assetfile.length == 0) {
            alert("There is nothing to upload");
            return false;
        }
        let region = $('#uload_loc').val();
        if (region === 'None') {
            alert("Please select a region");
            return false;
        }
    });

    $(window).on('resize', function () {
        location.reload();
    });

});
