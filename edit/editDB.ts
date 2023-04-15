declare var newgrps: UnpubClus[];
declare var tinymce: {
    init: (parms: settings) => void;
}
declare var hikeSources: AutoItem[];
interface AutoItem {
    value: string;
    label: string;
}
declare function forcedReset(): void;
interface settings {
    selector: string;
    plugins: string;
    toolbar: string;
    menubar?: string;
}
// unpublished Cluster Groups:
interface UnpubClus {
    group: string;
    loc: GPSLocation;
}
interface GPSLocation {
    lat: number;
    lng: number;
}
interface DOMFiles extends HTMLElement {
    files: InputFile[];
    value?: string | null;
}
interface InputFile extends HTMLElement {
    name: string;
}
interface Issue {
    [key: string]: string;
}
/**
 * @fileoverview This script handles all four tabs - presentation and data
 * validation, also initialization of settings for <select> boxes, etc.
 * 
 * @author Ken Cowles
 * 
 * @version 2.0 First release with Cluster Page editing
 * @version 2.1 Typescripted
 * @version 2.2 Updated tab1 look and feel
 * @version 2.3 Functionality added for heic converter button on tab2
 */
$( function () {
// Wysiwyg editor for tab3 hike info:
tinymce.init({
    selector: '.wysiwyg',
    plugins: 'autoresize advlist charmap image link lists',
    toolbar: 'undo redo | styleselect | bold italic | charmap | ' +
        'alignleft aligncenter alignright alignjustify | outdent indent | ' +
        'cut copy paste | forecolor backcolor | bullist numlist | link image'
});
/**
 * The framework/appearance of the edit page and buttons
 */
var tabCnt = $('.tablist').length;
var tabWidth = $('#t1').css('width');
var listwidth = tabCnt * parseInt(tabWidth); // fixed (no change w/resize)
var linewidth = <number>$('#main').width() - listwidth;
$('#line').width(linewidth);
// globals
var tabstr: string;
// the subs array holds the 'Apply' buttons for each tab, placed by positionApply()
var subs: JQuery<HTMLElement>[] = [];
for (let j=1; j<=4; j++) {
    let btn = '<input id="ap' + j + '" class="btn btn-dark" type="submit" value="Apply" />';
    let jqbtn = $(btn);
    subs[j] =jqbtn;
}
// initial button placed in order to establish global width
$('#f1').prepend(subs[1]);
const apwd = <number>subs[1].width();
// text string below which the apply button is placed
const awd = <number>$('#atxt').width();
const aht = <number>$('#atxt').height();
// initial settings on page load
var btop = 0;
var blft = 0;
var tabstr = $('#entry').text(); // tab # is saved in 'editDB.php' element
var tabint = parseInt(tabstr);
var lastA = tabint;
var tabon = '#t' + tabstr;
var issues: Issue[] = [];

/**
 * This will place the tab's 'Apply' (submit) button appropriately
 */
function positionApply(tab: number) {
    let atxt = <JQuery.Coordinates>$('#atxt').offset();
    let centerMarg  = (awd - apwd)/2 - 4; // 4: allow for right margin
    let postype = "fixed";
    btop = atxt.top + aht + 6; // 6 for spacing
    blft = atxt.left + centerMarg;
    if (tab === 3) {
        let apos = <JQuery.Coordinates>$('#atxt').offset();
        btop = apos.top + 32;
        blft = apos.left;
        postype = "absolute";
    }
    subs[tab].css({
        position: postype,
        top: btop,
        left: blft
    });
    let form = "#f" + tab;
    $(form).prepend(subs[tab]);
    return;
}
/**
 * Each 'Apply' button is added when the tab is displayed, so ensure that
 * the apply button re-establishes click behavior.
 */
 function prepareSubmit(elementId: string): void {
    $(elementId).off('click').on('click', function(evt) {
        if (issues.length > 0) {
            let msg = "There is one or more issues outstanding to resolve:\n";
            issues.forEach(function(issue) {
                let okey = Object.keys(issue); // returns array
                msg += issue[okey[0]] + "\n";
            });
            alert(msg);
            evt.preventDefault();
            return;
        } else {
            // proceed w/submit
        }
    });
}
// clicking on tab buttons:
$('button[id^=t]').on('click', function(ev) {
    ev.preventDefault();
    var tid = this.id;
    $('button').each( function() {
        if (this.id !== tid) {
            if ($(this).hasClass('active')) {
                var old = this.id;
                old = '#tab' + old.substring(1,2);
                $(old).css('display','none');
                $(this).removeClass('active');
            }
        }
    });
    $(this).addClass('active');
    tabint = parseFloat(tid.substring(1, 2));
    var newtid = '#tab' + tabint;
    $(newtid).css('display','block');
    let currbtn = "#ap" + lastA;
    $(currbtn).remove();
    // change lastA to current tab no.
    lastA = tabint;
    let newid = '#ap' + lastA;
    positionApply(lastA); // position the apply button for this tab
    prepareSubmit(newid);
});
// place correct tab (and apply button) in foreground - on page load only
$(tabon).trigger('click');

// If there is a user alert to show, set the message text:
var user_alert = '';
if (tabstr == '1' && $('#ua1').text() !== '') {
    user_alert = $('#ua1').text();
} else if (tabstr == '4' && $('#ua4').text() !== '') {
    user_alert = $('#ua4').text();
}
if (user_alert !== '') {
    alert(user_alert);
    $.get('resetAlerts.php');
}

// set max additional gpx files at 3
let listItems = $("#addlist").children();
let count = listItems.length;
if (count > 0 && count < 3) {
    let addmore = 3 - count;
    if (addmore > 0) { // can only be 1 or 2...
        $('#addno').text(addmore);
        if (addmore === 1) {
            $('#li3').hide();
            $('#li2').hide();
        } else {
            $('#li3').hide();
        } 
    } 
} else if (count === 3) {
    $('#addno').text('0');
    $('#li3').hide();
    $('#li2').hide();
    $('#li1').hide();
}
/**
 * If a main gpx file is checked for delete and there are additional files already
 * specified, AND no newmain is specified, alert user that a new main file must be
 * specified to keep the additional files.
 */
$('input[name=dgpx]').on('change', function() {
    if ($(this).is(':checked')) {
        let gpxfile_selected = <DOMFiles>$('#gpxfile1').get(0);
        if (gpxfile_selected.files.length === 0) {
            if (count > 0) {
                alert("You must specify a main file\n" +
                    "Otherwise additional files will be removed");
            } 
        }
    }
});
// only allow additional file specs if there is a main
$('input[name^=addgpx]').each(function() {
    $(this).on('change', function() {
        let inputel = <DOMFiles>document.getElementById("gpxfile1");
        let filedata = inputel.files[0];
        if (typeof filedata === 'undefined') {
            if ($('input[name=dgpx]').is(':checked') || $('#mgpx').text() === '') {
                alert("You must first specify a main gpx file\n or have one" +
                    " already uploaded [not to be deleted]");
                $(this).val('');
            }
        }
    });
});
$('#addaloc').on('click', function() {
    if ($('#addaloc').is(':checked')) {
        $('#newloc').show();
        $('#userloc').prop('required', true);
    } else {
        $('#newloc').hide();
        $('#userloc').prop('required', false);
    }
});
/**
 * This section does data validation for the 'directions' URL, and the URL's on tab4.
 * In the case of tab1, php validation filtering doesn't work when applied to google maps
 * links, so this URL is tested via js. Tab4 uses php validation filters. In either case,
 * when the URL fails validation, the textarea blinks, and an alert pops up.
 */
var blinkerItems: NodeJS.Timeout[] = [];
function tab1Url(uri: string): void {
    let trial = /^(ftp|http|https):\/\//.test(uri);
    if (!trial && uri !== '') {
      activateBlink('murl', tabstr);
    }     
}
$('#murl').on('change', function() {
    let murltxt = <HTMLTextAreaElement>this;
    let testtxt = murltxt.value;
    // without a slight delay, the focus gets lost
    setTimeout(function() {
        tab1Url(testtxt)
    }, 200);
 });
 if (tabstr == '1') {
    let urltxt = <string>$('#murl').val();
    if (urltxt !== '') {
        tab1Url(urltxt);  
    } 
}
if (tabstr == '4') {
    $('.urlbox').each(function() {
        let urlitem = <string>$(this).val();
        if (urlitem.trim() == '--- INVALID URL DETECTED ---') {
            let urlid = <string>$(this).attr('id');
            activateBlink(urlid, tabstr);
        }
    });
}
/**
 * This function cause an element to 'blink'
 */
function activateBlink(elemId: string, tabstr: string): void {
    let blink_el = <HTMLElement>document.getElementById(elemId);
    blink_el.focus({preventScroll:false});
    if (tabstr == '1') {
        window.scrollTo(0, document.body.scrollHeight);
    }
    alert("This URL appears to be invalid");
    var $elem = $('#' + elemId);
    var blinkerObject = setInterval(function() {
        if ($elem.css('visibility') == 'hidden') {
            $elem.css('visibility', 'visible');
        } else {
            $elem.css('visibility', 'hidden');
        }    
    }, 500);
    blinkerItems.push(blinkerObject);
    let ptr = blinkerItems.length - 1;
    $elem.on('mouseover', function() {
        clearInterval(blinkerItems[ptr]);
        $elem.css('visibility', 'visible');
    });
    return;
}

// Preview edit page button
var hike = $('#hikeNo').text();
$('#preview').on('click', function() {
    var prevPg = '../pages/hikePageTemplate.php?age=new&hikeIndx=' + hike;
    window.open(prevPg,"_blank");
});

// Pressing 'Return' while in textarea only adds newline chars, therefore:
window.onkeydown = function(event: KeyboardEvent) {
    let retval = true;
    if(event.key == 'Enter') {
        if(event.preventDefault) event.preventDefault();
        retval = false; // Just a workaround for old browsers
    }
    return retval;
}
if ($('#mgpx').text() === '') {
    $('#file_exists').css('color', 'gray');
} else {
    $('#file_exists').css('color', 'black');
}
// this detects when a sorted item has completed its move (args not currently used)
const refreshCapts = () => {
    forcedReset();
};
// photo reordering:
if ($("ul.reorder-photos-list").length > 0) {  // there may be no pix yet...
    $("ul.reorder-photos-list").sortable({
        tolerance: 'pointer',
        stop: refreshCapts
    });
    $("ul-reorder-photos-list").on("sortstop", refreshCapts);
}
/**
 * To import photos from another hike page:
 */
var ehikeno = $('#ehno').text();
$("#gethike").autocomplete({
    source: hikeSources,
    minLength: 1
});
$("#gethike").on("autocompleteselect", function(event, ui) {
    event.preventDefault();
    let hike = ui.item.value;
    let ajaxdata = {hike: hike, ehike: ehikeno};
    if (confirm("Do you wish to import photos from: " + hike)) {
        $.ajax({
            url: "getHikePhotos.php",
            method: "post",
            data: ajaxdata,
            dataType: "text",
            success: function(result) {
                if (result === 'ok') {
                    var curloc = location.href.replace("tab=1", "tab=2");
                    window.open(curloc, "_self");
                } else {
                    alert("Sorry: problem encountered");
                }
            },
            error: function(_jqXHR) {
                if (appMode === 'development') {
                    var newDoc = document.open();
                    newDoc.write(_jqXHR.responseText);
                    newDoc.close();
                } else {
                    alert("Error encountered: admin notified");
                }
            }
        })
    } else {
        alert("No action taken");
    }
});
// use the jpg converter for heic photos
$('#heic').on('click', function() {
    var heic_page = "heic_convert.php?ehike=" + ehikeno;
    window.open(heic_page, "_blank");
});
/**
 * The remaining script handles several features of the editor:
 *  1. Initialization of text and numeric fields based on db entries
 *  2. Registering changes in html elements that the server will utilize
 *  3. Validating data to match assigned database data types.
 * All of the cluster and references actions are grouped together as
 * noted by commments; otherwise code is segmented per the list.
 */
/**
 *              ----------- INITIALIZATION -----------
 * Each drop-down field parameter is held in a hidden <p> element;
 * the data (text) in that hidden <p> element is the default that should 
 * appear in the drop-down box on page-load;
 * The drop-down element parameters are:
 *      - locale
 *      - cluster group name
 *      - hike type
 *      - difficulty
 *      - exposure
 *      - references
 *      - waypoint icons
 * Each is treated, with identifiers, below:
*/
// Locale:
var sel = $('#locality').text();
/**
 * All of a sudden, the $('#area').val(sel) and the $('#clusters').val(orignme)
 * stopped working! Select boxes continue to be a challenge - apparently now a
 * string containing blanks must be quoted as a value. I have
 * switched to the following method, which seems to work, and yet it cannot
 * set the #area element to blank - but blank CAN be set for #clusters!???
 */
if (sel !== '') {
    $('#area option[value="' + sel + '"]').attr('selected', 'selected');
}  /* else {
    $('#area').val('');
} */
// Hike type:
var htype = $('#ctype').text();
$('#type').val(htype);
// Difficulty:
var diffic = $('#dif').text();
$('#diff').val(diffic);
// Exposure:
var exposure = $('#expo').text();
$('#sun').val(exposure);

/**
 * Cluster operation
 */
var orignme = $('#group').text(); 
// setting the Cluster assignment select box
if (orignme !== '') {
    $('#clusters option[value="' + orignme + '"]').attr('selected', 'selected'); // page load value
} else {
    $('#clusters').val('');
}
// associated displays
if (orignme == '') {  // no incoming assignment:
    $('#notclus').css('display','inline')
} else {
	$('#showdel').css('display','block');
}
var clusnme = orignme;  // clusnme can change later

/**
 * This function determines whether or not the current selection in the
 * clusters <select> drop-down box is an unpublished group (which will
 * then display lat/lng for that group). Default value when there are no
 * unpublished groups is 'no display' (see editDB.css)
 * The 'var newgrps' is established via php in tab1display.php
 */
const showClusCoords = (): void => {
    let match = false;
    let nglat = 0;
    let nglng = 0;
    if (newgrps.length > 0) {
        for (let k=0; k<newgrps.length; k++) {
            if (newgrps[k].group == clusnme) {
                nglat = newgrps[k].loc.lat;
                nglng = newgrps[k].loc.lng;
                match = true;
                break;
            }
        }
        if (match) {
            $('#cluslat').val(nglat);
            $('#cluslng').val(nglng);
            $('#newcoords').show();
            window.scrollTo(0,document.body.scrollHeight);
        } else {
            $('#newcoords').hide();
        }
    } else {
            $('#newcoords').hide();
    }
    return;
};
showClusCoords();

// Change Cluster selection:
$('#clusters').on('change', function() {
    let clusinput = <HTMLSelectElement>this;
    if (clusinput.value !== clusnme) {
        if (clusnme == '') {
            clusnme = "None assigned";
            $('#notclus').css('display','none');
            $('#showdel').css('display','block');
        }
        // let user know the existing cluster group assignment will change
        let msg = "Cluster type will be changed from:\n" + "Original setting: "
                + clusnme;
        msg += "\nTo: " + $(this).val();
        alert(msg); 
    } 
    clusnme = <string>$(this).val();
    showClusCoords();
});
// Remove an existing cluster assignment:
$('#deassign').on('change', function() {
    let cboxitem = <HTMLInputElement>this;
    if (cboxitem.checked) {
        $('#clusters').val('');
        $('#showdel').css('display','none');
        $('#notclus').css('display','inline');
        $('#newcoords').hide();
        clusnme = '';
        cboxitem.checked = false;
        alert("No group will be assigned to this hike");
    }
});
// End of cluster processing

// Waypoint icons when present in the gpx file:
var $gicons = $('[id^="gicn"]');
var $gbox   = $('[id^="gselicon"]');
$gbox.each(function(indx) {
    if ($gicons[indx].innerText == '') {
        $(this).val('googlemini');
    } else {
        $(this).val($gicons[indx].innerText);
    }
});
// Waypoint icons when present in the database
var $wicons = $('[id^="dicn"]');
var $wbox   = $('[id^="dselicon"]');
$wbox.each(function(indx) {
    if ($wicons[indx].innerText == '') {
        $(this).val('googlemini');
    } else {
        $(this).val($wicons[indx].innerText);
    }
});
$('#wpteds textarea').addClass('wpticonshift');

$(window).on('resize', function() {
    $('.subbtn').remove();
    linewidth = <number>$('#main').width() - listwidth;
    $('#line').width(linewidth);
    positionApply(tabint);
    let btn = "#ap" + tabint;
    prepareSubmit(btn);
});

});
