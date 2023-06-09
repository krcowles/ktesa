declare var gpxLatDeg: string[];
declare var gpxLngDeg: string[];
declare var gpxLatDM: string[];
declare var gpxLatDMS: string[];
declare var gpxLngDM: string[];
declare var gpxLngDMS: string[];
declare var wLatDeg: string[];
declare var wLatDM: string[];
declare var wLatDMS: string[];
declare var wLngDeg: string[];
declare var wLngDM: string[];
declare var wLngDMS: string[];
/**
 * @fileoverview This script was separated from editDB.ts to simplify maintenance
 * and pertains only to waypoint editing on tab2.
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 Handling multiple formats for lat/lngs
 * 
 */
$( function () {

const checkForFractionalEntry = (entry: number) => {
    var result = false;
    if ((entry % 1) > 0) {
        alert("Only whole numbers are allowed in this field;\n"
            + "NOTE: Other formats will not be recalculated!");
        result = true;
    }
    return result;
}

const recalculateFormats = (
    format: string,  // deg, dm, or dms
    target: JQuery<HTMLTextAreaElement>
) => {
    var degs = 0.00;
    var mins = 0.00;
    var secs = 0.00;
    switch(format) {
        case 'deg':  // tested
            degs = parseFloat(<string>target.val());
            // store new value in hidden input:
            target.parent().prev().val(degs.toFixed(7));
            // update dm and dms
            var act = Math.abs(degs);
            degs = Math.trunc(degs); // retains negative sign if present
            //var tdeg = gpstype === 'lat' ? degs : "-" + degs; // same for dm & dms
            var mant = act - Math.abs(degs);
            var mins = mant * 60;
            var tmin = mins.toFixed(5);
            // dm span
            var $tas = target.parent().next().children();
            $tas.eq(0).val(degs);
            $tas.eq(1).val(tmin);
            var dmin = Math.floor(mins);
            mant = mins - dmin
            var secs = mant * 60;
            var tsec = secs.toFixed(3);
            // dms span
            $tas = target.parent().next().next().children();
            $tas.eq(0).val(degs);
            $tas.eq(1).val(dmin);
            $tas.eq(2).val(tsec);
            break;
        case 'dm':
            var $parent = target.parent();  // regardles of which of the two changed
            // test if new waypt (gpx or db):
            var $els = $parent.children();
            var dm_input = $parent.prev().prev().attr('name') as string;
            if (dm_input.substring(0,1) === 'n') {
                /**
                 * For new entries, don't calculate until all fields are specified
                 */
                if ($els.eq(0).val() !== "" && $els.eq(1).val() !== "") {
                    // recalc the deg lat/lng:
                    var degs = parseFloat($els.eq(0).val() as string);
                    var mins = parseFloat($els.eq(1).val() as string);
                    var mant = mins/60;
                    var whole = degs < 0 ? (degs - mant).toFixed(7) : (degs + mant).toFixed(7);
                    $parent.prev().children().eq(0).val(whole);
                    $parent.prev().prev().val(whole);
                    // now calc dms values (degrees won't change)
                    var tamin = Math.floor(mins);
                    mant = mins - tamin;
                    var tasec = (60 * mant).toFixed(3);
                    var $dms_span = target.parent().next(); // dms span
                    var $dms = $dms_span.children();
                    $dms.eq(0).val(degs);
                    $dms.eq(1).val(tamin);
                    $dms.eq(2).val(tasec);
                } else {
                    alert("Waypt not completely specified yet: other formats not calculated")
                }
            }       
            break;
        case 'dms':
            var $dms_parent = target.parent(); // regardless of which of the three changed
            var $dms_els = $dms_parent.children();
            var dms_input = $dms_parent.prev().prev().prev().attr('name') as string;
            if (dms_input.substring(0,1) === 'n') {
                /**
                 * For new entries, don't calculate until all fields are specified
                 */
                if ($dms_els.eq(0).val() !== "" && $dms_els.eq(1).val() !== ""
                && $dms_els.eq(2).val() !== ""
                ) {
                    // recalc deg value
                    var dms_deg = parseFloat($dms_els.eq(0).val() as string);
                    var dms_min = parseFloat($dms_els.eq(1).val() as string);
                    var dms_sec = parseFloat($dms_els.eq(2).val() as string);
                    var degree  = dms_deg < 0 ? dms_deg - (dms_min + dms_sec/60)/60 :
                        dms_deg + (dms_min + dms_sec/60)/60;
                    $dms_parent.prev().prev().prev().val(degree.toFixed(7)); // input el
                    $dms_parent.prev().prev().children().eq(0).val(degree.toFixed(7)); // degree ta
                    // recalc dm values
                    var dm_degree = Math.trunc(degree);
                    var dm_deg = Math.abs(dm_degree); // retains negative value
                    var dm_fract = Math.abs(degree) - dm_deg;
                    var ta_min = (dm_fract * 60).toFixed(5);
                    var $dm_els = $dms_parent.prev().children();
                    $dm_els.eq(0).val(Math.trunc(dm_degree));
                    $dm_els.eq(1).val(ta_min);
                }  else {
                    alert("Waypt not completely specified yet: other formats not calculated")
                }
            }
    }
    return;
};
const non_num_entry = /[^\-\+0-9\.]/;

// Display default waypoint format:
$('.show_deg').show();
$('.show_dm').hide();
$('.show_dms').hide();
// Check for presence of div's requiring initialization
var gpxwpts  = $('#gpts').length ? true : false;
var dbwpts   = $('#wpts').length ? true : false;
/**
 * Initialize states;
 * Only the gpx waypts and existing (stored) database waypts need be initialized
 */
if (gpxwpts) { // there are gpx wpts on page...
    var $gLatDeg = $('.glat_deg') as JQuery<HTMLSpanElement>;
    var $gLatDM  = $('.glat_dm')  as JQuery<HTMLSpanElement>;
    var $gLatDMS = $('.glat_dms') as JQuery<HTMLSpanElement>;
    var $gLngDeg = $('.glng_deg') as JQuery<HTMLSpanElement>;
    var $gLngDM  = $('.glng_dm')  as JQuery<HTMLSpanElement>;
    var $gLngDMS = $('.glng_dms') as JQuery<HTMLSpanElement>;
    gpxLatDeg.forEach(function(lat:string, i:number) {
        $($gLatDeg[i]).children().eq(0).val(lat);
        var dm = gpxLatDM[i].split("|");
        $($gLatDM[i]).children().eq(0).val(dm[0]);
        $($gLatDM[i]).children().eq(1).val(dm[1]);
        var dms = gpxLatDMS[i].split("|");
        $($gLatDMS[i]).children().eq(0).val(dms[0]);
        $($gLatDMS[i]).children().eq(1).val(dms[1]);
        $($gLatDMS[i]).children().eq(2).val(dms[2]);
    });
    gpxLngDeg.forEach(function(lng:string, i:number) {
        $($gLngDeg[i]).children().eq(0).val(lng);
        var dm = gpxLngDM[i].split("|");
        $($gLngDM[i]).children().eq(0).val(dm[0]);
        $($gLngDM[i]).children().eq(1).val(dm[1]);
        var dms = gpxLngDMS[i].split("|");
        $($gLngDMS[i]).children().eq(0).val(dms[0]);
        $($gLngDMS[i]).children().eq(1).val(dms[1]);
        $($gLngDMS[i]).children().eq(2).val(dms[2]);      
    });
}
if (dbwpts) { // there are wpts from the database on page...
    var $wLatDeg = $('.dlat_deg') as JQuery<HTMLSpanElement>;
    var $wLatDM  = $('.dlat_dm')  as JQuery<HTMLSpanElement>;
    var $wLatDMS = $('.dlat_dms') as JQuery<HTMLSpanElement>;
    var $wLngDeg = $('.dlng_deg') as JQuery<HTMLSpanElement>;
    var $wLngDM  = $('.dlng_dm')  as JQuery<HTMLSpanElement>;
    var $wLngDMS = $('.dlng_dms') as JQuery<HTMLSpanElement>;
    wLatDeg.forEach(function(lat:string, i:number) {
        $($wLatDeg[i]).children().eq(0).val(lat);
        var dm = wLatDM[i].split("|");
        $($wLatDM[i]).children().eq(0).val(dm[0]);
        $($wLatDM[i]).children().eq(1).val(dm[1]);
        var dms = wLatDMS[i].split("|");
        $($wLatDMS[i]).children().eq(0).val(dms[0]);
        $($wLatDMS[i]).children().eq(1).val(dms[1]);
        $($wLatDMS[i]).children().eq(2).val(dms[2]);
    });
    wLngDeg.forEach(function(lng:string, i:number) {
        $($wLngDeg[i]).children().eq(0).val(lng);
        var dm = wLngDM[i].split("|");
        $($wLngDM[i]).children().eq(0).val(dm[0]);
        $($wLngDM[i]).children().eq(1).val(dm[1]);
        var dms = wLngDMS[i].split("|");
        $($wLngDMS[i]).children().eq(0).val(dms[0]);
        $($wLngDMS[i]).children().eq(1).val(dms[1]);
        $($wLngDMS[i]).children().eq(2).val(dms[2]);
    });
}
/**
 * When a lat/lng value changes, in any format, all formats are recalculated for that wpt;
 * Also, fields are checked for legitimate values
 */
$('.deg, .dm, .dms').on('change', function() {
    var type = 'none';
    var $jqTA = $(this) as JQuery<HTMLTextAreaElement>;
    var raw_value = $jqTA.val() as string | number;
    /**
     * Multiple integrity checks are made on entry:
     * Some test cases may be missing, e.g. point too large or too small,
     * but the following should suffice for most user entry errors.
     */
    // Tests non-numeric values
    if (non_num_entry.test(raw_value as string)) {
        alert("Please enter only numbers:\nNOTE: Other formats will not be recalculated!");
        $jqTA.val("");
        return false;
    }
    // Will find '-' or '.' if not at the beginning
    if (raw_value !== "") {
        if (isNaN(raw_value as number)) {
            alert("This is not a number:\nNOTE: Other formats will not be recalculated!");
            $jqTA.val("");
            return false;
        }
    }
    // eliminate any '+' signs
    var interim_value = raw_value as string === "" ? "" : parseFloat(raw_value as string);
    $jqTA.val(interim_value); 
    var value = interim_value as string === "" ? 0 : interim_value as number;
    // no fractions on certain fields
    var nofract = $jqTA.hasClass('tstyle1') ? true : false;
    if (nofract) {
        if (checkForFractionalEntry(value)) {
            $jqTA.val("");
            return false;
        }
    }
    // certain fields cannot be negative or have a value greater than 60
    if ($jqTA.hasClass('tstyle5') || ($jqTA.hasClass('tstyle1') && $jqTA.hasClass('noneg'))) {
        if (value < 0) {
            alert("This field cannot be negative:\n" +
                "NOTE: Other formats will not be recalculated");
                $jqTA.val("");
            return false;
        } else if (value > 60) {
            alert("Minutes/degrees can have a max value of 60:\n" +
                +"NOTE: Other formats will not be recalculated");
            $jqTA.val("");
            return false;
        }
    }
    // Finished integrity tests, proceed to recalculate data for all formats
    
    if ($jqTA.hasClass('deg')) {
        type = 'deg';
    } else if ($jqTA.hasClass('dm')) {
        type = 'dm';
    } else {
        type = 'dms';
    }
    recalculateFormats(type, $jqTA);
    return;
});

$('#wptstyle').on('change', function() {
    var format = $(this).val() as string;
    switch (format) {
        case "deg":
            $('.show_deg').show(); // defaults
            $('.show_dm').hide();
            $('.show_dms').hide();
            break;
        case "dm":
            $('.show_deg').hide(); // defaults
            $('.show_dm').show();
            $('.show_dms').hide();
            break;
        case "dms":
            $('.show_deg').hide(); // defaults
            $('.show_dm').hide();
            $('.show_dms').show();
    }
});
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

});