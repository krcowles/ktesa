$( function () { // when page is loaded...

/**
 * The framework/appearance of the edit page and buttons
 */
var winWidth = $(document).width();
// NOTE: innerWidth provides the dimension inside the border
var bodySurplus = winWidth - $('body').innerWidth(); // Default browser margin + body border width:
if (bodySurplus < 24) {
    bodySurplus = 24;
}   // var can actually be negative on initial load if frame is smaller than body min-width
var tabCnt = $('.tablist').length;
var singletab = '#t' + tabCnt;
var tabpos = $(singletab).offset();
// For reasons not understood, $('#pos').width() gets the incorrect value...
var gettabprop = $('.tablist').css('width');
var px = gettabprop.indexOf('px');
var tabwidth = gettabprop.substring(0,px);
var listwidth = tabCnt * tabwidth;
var linewidth = winWidth - bodySurplus - listwidth - 9; // padding
$('#line').width(linewidth);
// 'Apply' buttons: positions on page
var $apply = $('div[id^="d"]'); // the divs holding the input 'Apply' button
var apwd = $('#ap1').width();
var aht = $('#atxt').height();
var awd = $('#atxt').width();
var centerMarg;
var btop;
var blft;
var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
$(window).scroll(function() {
    scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
});
function positionApply() {
    var atxt = $('#atxt').offset();  // dynamic w/resizing
    centerMarg  = (awd - apwd)/2 - 4; // 4: allow for right margin
    btop = atxt.top + aht + 6; // 6 for spacing
    blft = atxt.left + centerMarg;
}
positionApply();

var applyPos = [];
$apply.each(function(indx) {
    var $inp = $(this).children().eq(0);
    applyPos[indx] = $inp.detach();
    // NOTE: apparently, removing the contents renders the div "display:none"
});
// when window is resized:
$(window).resize( function() {
    winWidth = $(document).width();
    linewidth = winWidth - bodySurplus - listwidth - 9;
    $('#line').width(linewidth);
    positionApply();
    var divid = '#d' + lastA;
    var scrollPos = btop + scrollTop;
    $(divid).offset({top: scrollPos, left: blft});
});
// To define button appearance
function highLight() {
    $('button').on('mouseover', function() {
        if (!$(this).hasClass('active')) {
            $(this).css('background-color','blanchedalmond');
            $(this).css('color','brown');
        }
    });
    $('button').on('mouseout', function() {
        if (!$(this).hasClass('active')) {
            $(this).css('background-color','honeydew');
            $(this).css('color','darkgray');
        }
    });
}
highLight();
// tab buttons:
$('button:not(#preview, #upld)').on('click', function(ev) {
    ev.preventDefault();
    var tid = this.id;
    $('button').each( function() {
        if (this.id !== tid) {
            $(this).css('background-color','honeydew');
            $(this).css('color','darkgray');
            if ($(this).hasClass('active')) {
                var old = this.id;
                old = '#tab' + old.substring(1,2);
                $(old).css('display','none');
                $(this).removeClass('active');
            }
        }
    });
    $(this).css('background-color','#DADADA');
    $(this).css('color','black');
    $(this).addClass('active');
    var newtid = '#tab' + tid.substring(1,2);
    $(newtid).css('display','block');
    document.documentElement.scrollTop = document.body.scrollTop = 0;
    highLight();
    if (tid.substr(0, 1) === 't') {
        var oldid = '#ap' + lastA;
        $(oldid).remove();
        lastA = tid.substr(1, 1);
        var newid = '#d' + lastA;
        $(newid).prepend(applyPos[lastA - 1]); // use index number, not tab no.
        $(newid).show();
        $(newid).offset({top: btop, left: blft});
    }
});

// place correct tab (and apply button) in foreground - on page load only
var tab = $('#entry').text();
//var tab = 3;
var tabon = '#t' + tab;
$(tabon).trigger('click');
var applyAdd = '#d' + tab;
var posNo = tab - 1; // index number, not tab number
$(applyAdd).prepend(applyPos[posNo]);
$(applyAdd).show();
$(applyAdd).offset({top: btop, left: blft});
var lastA = tab;

// Look for 'bad URLs' to highlight immediately after saveTabX.php
var blinkerObject = { blinkHandle: {} };
function activateBlink(elemId, tab) {
    document.getElementById(elemId).focus();
    if (tab == '1') {
        window.scrollTo(0, document.body.scrollHeight);
    }
    var $elem = $('#' + elemId);
    var blinker = 'blink' + elemId;
    blinkerObject[blinker] = setInterval(function() {
        if ($elem.css('visibility') == 'hidden') {
            $elem.css('visibility', 'visible');
        } else {
            $elem.css('visibility', 'hidden');
        }    
    }, 500);
    //alert("Please correct the URL or leave blank");
    $elem.on('mouseover', function() {
        clearInterval(blinkerObject[blinker]);
        $elem.css('visibility', 'visible');
    });
}
if (tab == '1' && $('#murl').val().trim() == '--- INVALID URL DETECTED ---') {
    activateBlink('murl',tab);
}
if (tab == '4') {
    $('.urlbox').each(function() {
        if ($(this).val().trim() == '--- INVALID URL DETECTED ---') {
            activateBlink($(this).attr('id'), tab);
        }
    });
}

// Preview edit page button
var hike = $('#hikeNo').text();
$('#preview').on('click', function() {
    var prevPg = '../pages/hikePageTemplate.php?age=new&hikeIndx=' + hike;
    window.open(prevPg,"_blank");
});

// show/hide lat lng data entries
$('#showll').on('click', function() {
    $('#lldisp').slideToggle();
    $(this).prop('checked',false);
});

// open the photo uploader page (tab2)
$('#upld').on('click', function() {
    var user = $('input[name=usr]').val();
    var uploader = 'ktesaUploader.php?indx=' + hike + "&usr=" + user;
    window.open(uploader, "_blank");
});

// Pressing 'Return' while in textarea only adds newline chars, therefore:
window.onkeydown = function(event) {
    if(event.keyCode == 13) {
        if(event.preventDefault) event.preventDefault();
        return false; // Just a workaround for old browsers
    }
}

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
$('#area').val(sel);
// Hike type:
var htype = $('#ctype').text();
$('#type').val(htype);
// Difficulty:
var diffic = $('#dif').text();
$('#diff').val(diffic);
// Exposure:
var exposure = $('#expo').text();
$('#sun').val(exposure);
// Waypoint icons when present in the gpx file:
var $gicons = $('[id^="gicn"]');
var $gbox   = $('[id^="selgicon"]');
$gbox.each(function(indx) {
    if ($gicons[indx].innerText == '') {
        $(this).val('googlemini');
    } else {
        $(this).val($gicons[indx].innerText);
    }
});
// Waypoint icons when present in the database
var $wicons = $('[id^="dicn"]');
var $wbox   = $('[id^="seldicon"]');
$wbox.each(function(indx) {
    if ($wicons[indx].innerText == '') {
        $(this).val('googlemini');
    } else {
        $(this).val($wicons[indx].innerText);
    }
});

// Clusters
/**
 * THE FOLLOWING CODE ADDRESSES EDITS TO CLUSTER ASSIGNMENTS
 *   - Refer to the design documentation for behavior assessment
 */
var msg;
var rule = "The specified new group will be added;" + "\n" + 
	"Current Cluster assignment will be ignored" + "\n" + 
	"Uncheck box to use currently available groups";
var clusnme = $('#group').text();  // incoming cluster assgnmnt for edited hike (may be empty)
$('#ctip').val(clusnme);  // show above in the select box on page load
if ($('#greq').text() === 'YES') {
    $('#newg').attr('checked', true);
    $('#newg').val("YES");
    $('#newt').focus();
    $('#notclus').css('display','inline');
    alert("Enter your requested new group name in the highlighted text box below;\n" +
        "If you don't want a new group, uncheck the box.");
} else if (clusnme == '') {  // no incoming assignment; display info:
	$('#notclus').css('display','inline');
} else {
	$('#showdel').css('display','block');
}
/**
 * To correctly process changes involving cluster types, the following state information
 * needs to be passed to the server:
 *   1. Restore original assignments?  (#ignore)
 *   2. Is a totally new cluster group being assigned?  (#newg)
 *      (Whether or not the previous type was cluster, new info will
 *      be extracted at server, unless "ignore" is checked to restore 
 *      original state)
 *   3. Was a group different from the original group selected in 
 *      the drop-down #ctip?  (#grpChg)
 *   4. Remove an existing cluster assignment?  (#deassign)
 */
// 1. Restore original assignments
$('#ignore').change(function() {
    if (this.checked) {
        $('#ctip').val(clusnme);
        if (clusnme == '') {
            $('#notclus').css('display','inline');
        }
        $('input:checkbox[name=nxtg]').attr('checked',false);
        $('#newg').val("NO");
        $('#newt').val("");
        $('#grpChg').val("NO");
        $('#deassign').val("NO");
        $('input:checkbox[name=rmclus]').attr('checked',false);
        window.alert("Original state restored" + "\n" + "No edits at this time to clusters");
        this.checked = false;
    }
});
// 2. Assign new cluster group
$('#newg').change(function() {
    if (this.checked) {
        this.value = "YES";
        if (clusnme == '') {
                $('#notclus').css('display','none');
        }
        fieldflag = true;
        $('#grpChg').val("NO");
        window.alert(rule);
    } else {  // newg is unchecked
        this.value = "NO";
        if (clusnme == '') {
                $('#notclus').css('display','inline');
                $('#ctip').val(clusnme);
        }
        $('#newt').val("");
        fieldflag = false;				
    }
    $('#ctip').val(clusnme); // go back to original group assignment
});
// 3. Different group selected
var marker = $('#marker').text(); // Marker is hike type (At VC, Cluster, Other)
$('#ctip').change(function() {  // record any changes to the cluster assignment
    if ( $('#newg').val() === 'NO' ) {
        if (this.value !== clusnme) {
            /* If marker was not originally a cluster type, prepare to change to cluster group: */
            if (marker !== 'Cluster') {
                window.alert("Marker will be changed to cluster type");
                $('#notclus').css('display','none');
            } else {  // otherwise, let user know the existing cluster group assignment will change
                msg = "Cluster type will be changed from " + "\n" + "Original setting of: " + clusnme + "\n";
                msg += "To: " + $('#ctip').val();
                window.alert(msg);
            }
            $('#grpChg').val("YES");
        } 
    } else {  // when/if the box gets unchecked, the ctip val will be restored to original state;
        // deactivate grpChg to align with restored original cluster assignment
        $('#grpChg').val("NO");
        window.alert("Changes ignored while New Group Box is checked");
    }
});
// 4. Remove an existing cluster assignment
$('#deassign').change(function() {
    if (this.checked) {
        $('#deassign').val("YES");
    } else {
        $('#deassign').val("NO");
    }
});
// End of cluster processing
// References section:
// A: This code refers to existing refs (in database), not new ones...
var refCnt = parseInt($('#refcnt').text());
var item0;  // <p> element containing text = rtype
var rtype;
var item1;  // <p> element containing text = rit1
var rit1;
var item2;  // <p> element containing text = rit2
var rit2;
var selbox; // <select> element holding reference type selection
var boxid;
var box;
// initialize (pre-populate) the boxes:
for (var i=0; i<refCnt; i++) {
    item0 = '#rtype' + i;
    rtype = $(item0).text();  // get the rtype for this reference item
    item1 = '#rit1' + i;
    rit1 = $(item1).text();  // get the rit1 for this item (numeric for a book)
    item2 = '#rit2' + i;
    rit2 = $(item2).text();  // get the rit2 for this item
    selbox = '#sel' + i;
    $(selbox).val(rtype); // pre-populate reference type drop-down
    boxid = 'sel' + i;
    if (rtype === 'Book:' || rtype === 'Photo Essay:') {
        indx = parseInt(rit1) - 1;
        var bkname = '#bkname' + i;  // <select> element for all book names                 
        $(bkname).val(rit1);  // this will be a number corresponding to an array indx
        var auth = '#auth' + i;
        $(auth).val(authors[indx]);  // get the name from the array
        box = document.getElementById(boxid);
        // disable non-book entries
        for (var u=2; u<box.options.length; u++) {
            box.options[u].disabled = true;
        }
    } else if (rtype === 'Text:') {
        var url = '#url' + i;
        $(url).val('');
        $(url).attr('placeholder','THIS BOX IGNORED');
        // disable book type entries
        document.getElementById(boxid).options[0].disabled = true;
        document.getElementById(boxid).options[1].disabled = true;
    } else {
        // disable book type entries
        document.getElementById(boxid).options[0].disabled = true;
        document.getElementById(boxid).options[1].disabled = true;
    }
}
// B: This code refers to the new refs (if any) which can be added by the user
/*
 * This code detects when the user selects a reference type other than
 * book/photo essay and displays a different set of boxes with appropriate
 * placeholder text. 
 */
$reftags = $('select[id^="href"]');
$reftags.each( function() {
    $(this).change( function() {
        var refno = this.id;
        var elementNo = refno.substr(4,1);
        var bkid = '#bk' + elementNo;
        var nbkid = '#nbk' + elementNo;
        var box1 = '#nr1' + elementNo;
        var box2 = '#nr2' + elementNo;
        var bkbox = '#usebk' + elementNo;
        var notbk = '#notbk' + elementNo;
        if ($(this).val() === 'Book:' || $(this).val() === 'Photo Essay:') {
            $(bkid).css('display','inline');
            $(nbkid).css('display','none');
            var ttl = '#bkttl' + elementNo;
            var auth = '#bkauth' + elementNo;
            for (var n=0; n<titles.length; n++) {
                if (titles[n] === $(ttl).val()) {
                    $(auth).val(authors[n]);
                    break;
                }
            }
            $(bkbox).val('yes');
            $(notbk).val('no');
        } else if ($(this).val() !== 'Text:') {
            $(bkid).css('display','none');
            $(nbkid).css('display','inline');
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','URL');
            }
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','Clickable text');
            }
            $(bkbox).val('no');
            $(notbk).val('yes');
        } else {
            $(bkid).css('display','none');
            $(nbkid).css('display','inline');
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','Enter Text Here');
            } 
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','THIS BOX IGNORED');
            }
            $(bkbox).val('no');
            $(notbk).val('yes');
        }
    });
});
// validate length of URL's and click-on text
$('input[id^=nr1]').each(function() {
    $(this).on('change', function() {
        if($(this).val().length > 1024) {
            alert("This URL exceeds the max length of 1024 characters");
            $(this).val("");
        }
    });
});
$('input[id^=nr2]').each(function() {
    $(this).on('change', function() {
        if ($(this).val().length > 512) {
            alert("The maximum no of characters allowed in this field is 512");
            $(this).val("");
        }
    });
});
var $bktags = $('select[id^="bkttl"]');
$bktags.each( function() {
    $(this).val(''); // initialize to show no selection:
    $(this).on('change', function() {
        var bkid = this.id;
        bkid = bkid.substr(bkid.length-1, 1);
        var authid = '#bkauth' + bkid;
        var authindx = $(this).val() - 1;
        $(authid).val(authors[authindx]);
    });
});

/**
 * Database data validation: does item conform to database data type?
 * Note: there is no 'range' validation for numbers, neither is there a
 * test to see if < 0, etc. The only qualification is conformity to db spec.
 * In addition, if a user clicks 'Apply' while the user is entering invalid
 * data, form submission is halted. The subject field is converted to an
 * empty string, after which the user may 'Apply' as is, or change the data.
 */
 var submit = true;  // unless invalid data is entered... (may prevent submit)

// miles: numeric, and up to two decimal points
var orgmiles = $('#miles').val(); // original value loaded
$('#miles').on('change', function() {
    var warn = "Please enter a number less than 100 for 'miles'\n" +
        "with a maximum of 2 decimal places";
    var milesEntry = $('#miles').val();
    if ($.isNumeric(milesEntry)) {
        milesEntry = Number(milesEntry); // textareas are strings
        var milesString = milesEntry.toString();
        var regexp = /^\d+(\.\d{1,2})?$/;
        if (regexp.test(milesString)) {
            if (Math.abs(milesEntry) > 99.99) {
                alert(warn);
                $('#miles').val(orgmiles);
                submit = false;
            } else {
                $('input[name=usrmiles]').val("YES");
                submit = true;
            }
        } else {
            var strlen = milesEntry.length;
            if (milesString.indexOf('.') !== -1 && milesString.indexOf('.') !== strlen -1) {
                    alert(warn);
                    $('#miles').val(orgmiles);
                    submit = false;
            } else {
                $('input[name=usrmiles]').val("YES");
                submit = true;
            }
        }
    } else {
        alert(warn);
        $('#miles').val(orgmiles);
        submit = false;
    }
});
// elevation: up to five digits, integer
var orgelev = $('#elev').val();  // original value loaded
$('#elev').on('change', function() {
    var feet = Number($('#elev').val());
    alarm = "Only integers less than 100,000 are allowed for 'Elevation Change'";
    if ($.isNumeric(feet)) {
        if (Number.isInteger(feet)) {
            if (Math.abs(feet) > 99999) {
                alert(alarm);
                $('#elev').val(orgelev);
                submit = false;
            } else {
                $('input[name=usrfeet]').val("YES");
                submit = true;
            }
        } else {
            alert(alarm);
            $('#elev').val(orgelev);
            submit = false;
        }
    } else {
        alert(alarm);
        $('#elev').val(orgelev);
        submit = false;
    }

});
// gpx: file name length 1024; NOTE: This also covers GPS Data uploads
$('input[type=file]').on('change', function() {
    var newname = this.files[0].name;
    if (newname.length > 1024) {
        alert("Only 1024 Characters are allowed for file names");
        this.value = null;
    }
});
// lat: float (13.10)
var orglat = $('#lat').val();  // original value loaded
$('#lat').on('change', function() {
    var lat = Number($(this).val());
    var notlat = "The value entered does not conform to a lat/lng";
    if ($.isNumeric(lat)) {
        decimal = /^[-+]?[0-9]+\.[0-9]+$/; 
        if (decimal.test(lat)) {
            if (Math.abs(lat) > 180) {
                alert(notlat);
                $(this).val(orglat);
                submit = false;
            } else {
                submit = true;
            }
        } else {
            alert(notlat);
            $(this).val(orglat);
            submit = false;
        }
    } else {
        alert(notlat);
        $(this).val(orglat);
        submit = false;
    }
});
// lng: float (13.10)
var orglng = $('#lon').val();  // original value loaded
$('#lon').on('change', function() {
    var lng = Number($(this).val());
    var notlng = "The value entered does not conform to a lat/lng";
    if ($.isNumeric(lng)) {
        decimal = /^[-+]?[0-9]+\.[0-9]+$/; 
        if (decimal.test(lng)) {
            if (Math.abs(lng) > 180) {
                alert(notlng);
                $(this).val(orglng);
                submit = false;
            } else {
                submit = true;
            }
        } else {
            alert(notlng);
            $(this).val(orglng);
            submit = false;
        }
    } else {
        alert(notlng);
        $(this).val(orglng);
        submit = false;
    }
});
// GPS Data: 
// label
$('input[name^=labl]').each(function() {
    $(this).on('change', function() {
        if ($(this).val().length > 128) {
            alert("Only 128 characters are allowed");
            $(this).val("");
            submit = false;
        }
    });
});
// url length
$('input[name^=lnk]').each(function() {
    $(this).on('change', function() {
        if ($(this).val().length > 1024) {
            alert("Only 1024 characters are allowed");
            submit = false;
        }
    });
});
// click-on-text length
$('input[name^=ctxt]').each(function() {
    $(this).on('change', function() {
        if ($(this).val().length > 256) {
            alert("Only 256 characters are allowed");
            submit = false;
        }
    });
});

// Form submission if 'submit' is still true
$('input[name=savePg]').on('click', function(evt) {
    if (submit) {
        // make sure that a new group name has been specified if the checkbox is checked
        if ($('#newg').prop('checked')) {
            if ($('#newt').val() == '') {
                evt.preventDefault();
                alert("No new cluster name has been specified:\n" +
                    "please enter a name or uncheck the 'new nMW' checkbox");
            } else {
                if ($('#newt').val().length > 25) {  // db limit
                    evt.preventDefault();
                    alert("Only 25 characters allowed");
                }
            }
        }
    } else {
        submit = true;
        return false;
    }
});

});  // end of 'page (DOM) loading complete'
