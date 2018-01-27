$( function () { // when page is loaded...
    
/* Each drop-down field parameter is held in a hidden <p> element;
 * the data (text) in that hidden <p> element is the default that should 
 * appear in the drop-down box on page-load;
 * The drop-down element parameters are:
 *      - locale
 *      - cluster group name
 *      - hike type
 *      - difficult
 *      - exposure
 *      - references
*/
// Locale:
var sel = $('#locality').text();
$('#area').val(sel);

/* 
 * THE FOLLOWING CODE ADDRESSES EDITS TO CLUSTER ASSIGNMENTS
 *   - Refer to the state machine for behavior assessment
 */
var msg;
var rule = "The specified new group will be added;" + "\n" + 
	"Current Cluster assignment will be ignored" + "\n" + 
	"Uncheck box to use currently available groups";
var clusnme = $('#group').text();  // incoming cluster assignment for hike being edited (may be empty)
//alert(clusnme);
$('#ctip').val(clusnme);  // show above in the select box on page load
if (clusnme == '') {  // no incoming assignment; marker is not cluster type; display info:
	$('#notclus').css('display','inline');
} else {
	$('#showdel').css('display','block');
}
var mrkr = $('#mrkr').text();
/* To correctly process changes involving cluster types, the following state information
   needs to be passed to the server:
        1. Restore original assignments?  ignore
        2. Is a totally new cluster group being assigned?  (#newg) nxtg
           (Whether or not the previous type was cluster, new info will be extracted at server,
            unless "ignore" is checked to restore original state)
        3. Was a group different from the original group selected in 
            the drop-down #ctip?  (#grpchg) chgd
        4. Remove an existing cluster assignment?  (#deassign) rmclus
*/
var fieldflag = false;  // validation: make sure newt gets entered when newg is checked
// RESTORE INCOMING DEFAULTS:
$('#ignore').change(function() {
    if (this.checked) {
        $('#ctip').val(clusnme);
        if (clusnme == '') {
            $('#notclus').css('display','inline');
        }
        $('input:checkbox[name=nxtg]').attr('checked',false);
        $('#newg').val("NO");
        $('#newt').val("");
        $('#grpchg').val("NO");
        $('#deassign').val("NO");
        $('input:checkbox[name=rmclus]').attr('checked',false);
        fieldflag = false;
        window.alert("Original state restored" + "\n" + "No edits at this time to clusters");
        this.checked = false;
    }
});
// TELL SERVER TO REMOVE THE CLUSTER ASSIGNMENT AND CHANGE MARKER TO NORMAL
$('#deassign').change(function() {
    if (this.checked) {
        $('#deassign').val("YES");
    } else {
        $('#deassign').val("NO");
    }
});
// ASSIGN BRAND NEW GROUP:
$('#newg').change(function() {
    if (this.checked) {
        this.value = "YES";
        if (clusnme == '') {
                $('#notclus').css('display','none');
        }
        fieldflag = true;
        $('#grpchg').val("NO");
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
// GROUP ASSIGNMENT DROP_DOWN VALUE IS CHANGED:
$('#ctip').change(function() {  // record any changes to the cluster assignment
    if ( $('#newg').val() === 'NO' ) {
        if (this.value !== clusnme) {
            /* If marker was not originally a cluster type, prepare to change to cluster group: */
            if (mrkr !== 'Cluster') {
                window.alert("Marker will be changed to cluster type");
                $('#notclus').css('display','none');
            } else {  // otherwise, let user know the existing cluster group assignment will change
                msg = "Cluster type will be changed from " + "\n" + "Original setting of: " + clusnme + "\n";
                msg += "To: " + $('#ctip').val();
                window.alert(msg);
            }
            $('#grpchg').val("YES");
        } 
    } else {  // when/if the box gets unchecked, the ctip val will be restored to original state;
        // deactivate grpchg to align with restored original cluster assignment
        $('#grpchg').val("NO");
        window.alert("Changes ignored while New Group Box is checked");
    }
});
// --------- end of cluster processing

// Hike type:
var htype = $('#ctype').text();
$('#type').val(htype);
// Difficulty:
var diffic = $('#dif').text();
$('#diff').val(diffic);
// Exposure:
var exposure = $('#expo').text();
$('#sun').val(exposure);

// References section:
// A: This code refers to existing refs, not new ones...
var refCnt = parseFloat($('#refcnt').text());
var refid;
var rid;
var refname;
for (var i=0; i<refCnt; i++) {
    refid = '#rid' + i;
    rid = $(refid).text();  // single letter in xml
    refname = '#ref' + i;
    $(refname).val(rid);
}
// B: This code refers to the new refs (if any) added by the user
// placeholder text for reference input boxes (copied from enterHike.js)
$reftags = $('select[id^="href"]');
$reftags.each( function() {
    $(this).change( function() {
        var selId = this.id;
        var elNo = parseInt(selId.substring(4,5)); // NOTE: no more than 10 boxes!
        var elStr = "ABCDEFGHIJ".substring(elNo-1,elNo);
        var box1 = '#rit' + elStr + '1';
        var box2 = '#rit' + elStr + '2';
        if ($(this).val() === 'b') {
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','Book Title');
            }
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','Author');
            }
        } else if ($(this).val() !== 'n') {
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','URL');
            }
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','Clickable text');
            }
        } else {
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','Enter Text Here');
            } 
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','THIS BOX IGNORED');
            }
        }
    });
});

var winWidth = $(document).width();
// NOTE: innerWidth provides the dimension inside the border
var bodySurplus = winWidth - $('body').innerWidth(); // Default browser margin + body border width:
if (bodySurplus < 24) {
    bodySurplus = 24;
}   // var can actually be negative on initial load if frame is smaller than body min-width
var tablist = $('#t5').offset();
var down = Math.floor(tablist.top) + $('#t5').height();
// For reasons not understood, $('#pos').width() gets the incorrect value...
var gettabprop = $('.tablist').css('width');
var px = gettabprop.indexOf('px');
var tabwidth = gettabprop.substring(0,px);
var listwidth = 5 * tabwidth;
var linewidth = winWidth - bodySurplus - listwidth - 9; // padding
//alert("lw:" + linewidth + ", dn:" + down + ", list:" + listwidth);
$('#line').width(linewidth);
$(window).resize( function() {
    winWidth = $(document).width();
    linewidth = winWidth - bodySurplus - listwidth - 9;
    $('#line').width(linewidth);
});
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
$('button:not(#preview)').on('click', function(ev) {
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
    highLight();
});

$('#preview').on('click', function() {
    var hno = $('#hikeNo').text();
    var prevPg = '../pages/hikePageTemplate.php?age=new&hikeIndx=' + hno;
    window.open(prevPg,"_blank");
});

var tab = $('#entry').text();
var tabon = '#t' + tab;
$(tabon).trigger('click');

$('.phurl').each( function() {
    $(this).change( function() {
        $(this).css('color','blue');
        $(this).css('font-weight','bold');
        $(this).css('border-color','black');
    });
});
$('#upld').on('click', function() {
    window.open('newPhotos.php');
});
$('#showll').on('click', function() {
    $('#lldisp').slideToggle();
    $(this).prop('checked',false);
});

});  // end of 'page (DOM) loading complete'








