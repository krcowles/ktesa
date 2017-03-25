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
   		4. Was a group different from the original group selected in the drop-down #ctip?  (#grpchg) chgd
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
	rid = $(refid).text();
	refname = '#ref' + i;
	$(refname).val(rid);
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

}); // end of page is loaded...