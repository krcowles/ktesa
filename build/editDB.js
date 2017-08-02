$( function () { // when page is loaded...

/* Each drop-down field parameter is held in a hidden <p> element; the data (text)
   in that hidden <p> element is the default that should appear in the drop-down box
   The drop-down style parameters are:
        - locale
        - cluster group name
        - hike type
        - difficult
        - exposure
        - references
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
        3. Was marker changed from a non-cluster type to a cluster?  (#mrkrchg) chg2clus
        4. Was a group different from the original group selected in 
            the drop-down #ctip?  (#grpchg) chgd
        5. Remove an existing cluster assignment?  (#deassign) rmclus
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
        $('#mrkrchg').val("NO");
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
                $('#mrkrchg').val("YES");	
        }
        fieldflag = true;
        $('#grpchg').val("NO");
        window.alert(rule);
    } else {  // newg is unchecked
        this.value = "NO";
        if (clusnme == '') {
                $('#notclus').css('display','inline');
                $('#mrkrchg').val("NO");
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
                $('#mrkrchg').val("YES");
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

// FOR USE BY picNplace ROUTINE:
$('#loadimg').change( function(e) {
    e.preventDefault();
    var isrc = $('#picurl').val();
    var newimg = '<img style="margin-right:10px;" id= "newpic" draggable="true" ondragstart="drag(event)" src="' + 
            isrc + '" alt="image from url" />';
    $('#getimg').append(newimg);
    $('img').load( function() {
        var loadedImg = document.getElementById('newpic');
        var oldht = loadedImg.height;
        var oldwd = loadedImg.width;
        var scale = oldwd/oldht;
        var newwd = Math.floor(scale * 200);
        loadedImg.height = 200; // start with reasonably sized image
        loadedImg.width = newwd;
    });
    $(this).attr('checked',false);
});

$('#addbox').change( function(e) {
    e.preventDefault();
    var $currRows = $('div[id^="row"]');
    var rowno = $currRows.length;
    var newins = '<div id="insRow' + rowno + '" class="ins">';
    newins += '<img id="lead' + rowno + '" style="float:left;" ondrop="drop(event)"' +
            ' ondragover="allowDrop(event)" height="30" width="30" src="insert.png" alt="drop-point" />';
    newins += '</div>';
    var olddiv = '#caps' + (rowno - 1);
    $(newins).insertAfter(olddiv);
    var newimg = '<div id="row' + rowno + '" class="ImgRow" style="margin-left:20px;clear:both;"></div>';
    var insdiv = '#insRow' + rowno;
    $(newimg).insertAfter(insdiv);
    var newcap = '<div id="caps' + rowno + '" style="margin-left:20px;"></div>';
    var capdiv = '#row' + rowno;
    $(newcap).insertAfter(capdiv);
    $(this).attr('checked',false);
    // add new row to rows object
    $rows = null;
    rowSetup();  // see picNplace.js
    var newrowid = 'r' + rowno;
    var photoSect = document.getElementById('picdiv');
    var addedrow = document.createElement('input');
    addedrow.setAttribute('id',newrowid);
    addedrow.setAttribute('type','hidden');
    addedrow.name = "row[]";
    photoSect.appendChild(addedrow);
});

// placeholder text for reference input boxes (copied from enterHike.js)
$reftags = $('select[id^="href"]');
$reftags.each( function() {
    $(this).change( function() {
        var selId = this.id;
        var elNo = parseInt(selId.substring(4,5));
        var elStr = "ABCDEFGH".substring(elNo-1,elNo);
        var box1 = '#rit' + elStr + '1';
        var box2 = '#rit' + elStr + '2';
        if ($(this).val() === 'b') {
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','Book Title');
            }
            if ($(box2).val() === '') {
                $(box2).attr('placeholder',', by Author Name');
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

});  // end of 'page (DOM) loading complete'








