$( function () { // when page is loaded...

var msgA;  // generic
var msgB;
var msgC;
var msgD;
var msgE;

// Turn text blue to indicate user has uploaded a file
$('#geomap').change( function() {
	$('#l_gmap').css('color','DarkBlue');
});
$('#elev').change( function() {
	$('#l_elev').css('color','DarkBlue');
});
$('#gpxfile').change( function() {
	$('#l_gpx').css('color','DarkBlue');
});
$('#trkfile').change( function() {
	$('#l_trk').css('color','DarkBlue');
});
$('#addon1').change( function() {
	$('#l_add1').css('color','DarkBlue');
});
$('#addon2').change( function() {
	$('#l_add2').css('color','DarkBlue');
});

// CURRENTLY, UPLOADING A FILE WITH NEW PAGE INFO IS DISABLED:
$('#bypass').css('display','none');

/*       Setting the page-creation type for the submit button
			NOTE: the "pageType" radio buttons are not part of the form submitted
*/
$('input[name="pageType"]').click( function() {
	if($('input:radio[name=pageType]:checked').val() == "vcenter") {
		useIndexPg();
		$('input[name="mstyle"][value="center"]').prop('checked',true);
	} else {
		useStdPg();
		$('input[name="mstyle"][value="center"]').prop('checked',false);
	}
});
$('input[name="mstyle"]').click( function() {
	if($('input:radio[name=mstyle]:checked').val() == "center") {
		useIndexPg();
		$('input[name="pageType"][value="standard"]').prop('checked',false);
		$('input[name="pageType"][value="vcenter"]').prop('checked',true);
	} else {
		useStdPg();
		$('input[name="pageType"][value="standard"]').prop('checked',true);
		$('input[name="pageType"][value="vcenter"]').prop('checked',false);
	}
});
function useIndexPg() {
	pageSelector = "displayIndexPg.php";
	msgA = "Index Page Name (Include 'Index' at end of descriptor): ";
	$('.notVC').css('color','Gray');
	msgB = "Provide image for Index Page: ";
	msgC = "Visitor Center/Park Map  [ image size around 700px x 450px ]: ";
	$('#l_add1').css('color','Black');
	$('#pgTitleText').text(msgA);
	$('#spImg').text(msgB);
	$('#l_add1').text(msgC);
	msgD = $('#ifac').text();
	msgD = msgD.replace("Trailhead, if any","Visitor Center");
	$('#ifac').text(msgD);
	msgE = $('#iwow').text();
	msgE = msgE.replace('hike','place');
	$('#iwow').text(msgE);
	$('.honly').css('display','none');
}
function useStdPg() {
	pageSelector = "validateHike.php";
	msgA = "Hike Name (As it will appear in the table & window tab): ";
	$('.notVC').css('color','Black');
	msgB = "Additional Images - optional";
	msgC = "Other image (pop-up captions not provided at this time): [resides in images/] ";
	$('#l_add1').css('color','Gray');
	$('#pgTitleText').text(msgA);
	$('#spImg').text(msgB);
	$('#l_add1').text(msgC);
	msgD = $('#ifac').text();
	msgD = msgD.replace("Visitor Center","Trailhead, if any");
	$('#ifac').text(msgD);
	msgE = $('#iwow').text();
	msgE = msgE.replace('place','hike');
	$('#iwow').text(msgE);
	$('.honly').css('display','block');
}
$('#clrIt').on('click', function(e) {
	e.preventDefault();
	document.getElementById("xl").value = "";
});
/* END OF page-creation type */

// PARTIALLY FILLED FORM-SAVING
if (typeof(Storage) !== undefined) {
	var msg; //debug outputs
	var previousSaves;
	var uploadedFile = '';
	var buttonName;
	var ulName;
	
	// FUNCTION FOR RESTORING FORM DATA FROM A STRING
	function stringToForm(formString, unfilledForm) {
		formObject = JSON.parse(formString);
		$('input:text, input:radio, input:checkbox, select, textarea').each(function() {
			if (this.id) {
				id = this.id;
				elem = $(this); 
				if (elem.attr("type") == "checkbox" || elem.attr("type") == "radio" ) {
					elem.prop("checked", formObject[id]);
				} else {
					elem.val(formObject[id]);
				}
			}
		});
	}

	// only needed to save the filename in the ul div so that a save/restore can provide it
	$('#tsv').change(function() {
		var fullPath = document.getElementById('tsv').value;
		if (fullPath) {
			var startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
			var uploadedFile = fullPath.substring(startIndex);
			if (uploadedFile.indexOf('\\') === 0 || uploadedFile.indexOf('/') === 0) {
				uploadedFile = uploadedFile.substring(1);
			}
		}
		$('#l_tsv').css('color','DarkBlue');
		$('#ul').append(uploadedFile);
	});
	
	// MODAL WINDOW SETUP AND EVENT DEFINITION
	var $savePopup = $('#save-modal').detach();
	$('#saver').on('click', function() {
		modal.open({id: 'saver', content: $savePopup, width:400, height:200});
		/* NOTE: "id" key added as it was thought there would be other modal windows to process,
		   and the routine (modal_setup.js) would need to know which type modal to produce */
	});
	
/* DEBUG CODE
	$('#dbugr').on('click', function() {
		var a = window.localStorage.noOfSaves;
		var b = window.localStorage.oldName1;
		var c = window.localStorage.oldForm1;
		var d = window.localStorage.oldFile1;
		var e = window.localStorage.oldName2;
		var f = window.localStorage.oldForm2;
		var g = window.localStorage.oldList2;
		var dout = '<p>Current storage data:</p> ';
		dout += '<p>Saves: ' + a + '</p><p> Item1: ' + b + ' / ' + d + ';   ' + c + '</p>';
		dout += '<p>Item2: ' + e + ' / ' + g + '; ' + f + '</p>';
		$(this).prepend(dout);
	}); 
	$('#cleaner').on('click', function() {
		window.localStorage.noOfSaves = 0;
		$(this).append(window.localStorage.noOfSaves);
		window.localStorage.removeItem('oldName1');
		window.localStorage.removeItem('oldForm1');
		window.localStorage.removeItem('oldFile1');
		window.localStorage.removeItem('oldName2');
		window.localStorage.removeItem('oldForm2');
		window.localStorage.removeITem('oldFile2');
	});
*/

	// Load previous saves and set event handlers
	previousSaves = parseFloat(window.localStorage.noOfSaves);
	if (previousSaves > 0) {
		$('#unsaver').text('Restore a previously saved form:');
		buttonName = window.localStorage.oldName1;
		ulName = window.localStorage.oldFile1;
		msg = '<label id="lbl1"><input id="save1" type="radio" name="restores" /> ' + 
				buttonName + '; &nbsp;&nbsp;Uploaded file: ' + ulName + '</label>';
		$('#left1').append(msg);
		msg = '<label id="lblr1"><input id="kill1" type="radio" name="delRestore" /> Remove ' +
				buttonName + '</label>';
		$('#right1').append(msg);
		// ensure ul filename space is empty to start with
		$('#ul').text('');
		$('#save1').on('click', function() {
			var restoredForm = window.localStorage.oldForm1;
			stringToForm(restoredForm, $('#hikeData'));
			window.alert("Form Restored - Please Re-enter files: they cannot be saved");
			$(this).attr('checked',false);
		});
		$('#kill1').on('click', function() {
			var currSaves = parseFloat(window.localStorage.noOfSaves);
			currSaves -= 1;
			if (currSaves === 0) {
				$('#unsaver').text('Restore a previously saved form: (currently none)');
			}
			window.localStorage.noOfSaves = currSaves;
			window.localStorage.removeItem('oldName1');	
			window.localStorage.removeItem('oldForm1');
			window.localStorage.removeItem('oldFile1');
			$('#lbl1').remove();
			$('#lblr1').remove();
		});
	}
	if (previousSaves > 1) {
		buttonName = window.localStorage.oldName2;
		ulName = window.localStorage.oldName2;
		msg = '<label id="lbl2"><input id="save2" type="radio" name="restores" /> ' + 
			buttonName + '; &nbsp;&nbsp;Uploaded file: ' + ulName + '</label>';
		$('#left2').append(msg);
		msg = '<label id="lblr2"><input id="kill2" type="radio" name="delRestore" /> Remove ' +
			buttonName + '</label>';
		$('#right2').append(msg);
		// clear out the uploaded file name for use in other saves
		$('#ul').text('');
		$('#save2').on('click', function() {
			var restoredForm = window.localStorage.oldForm2;
			stringToForm(restoredForm, $('#hikeData'));
			window.alert("File Resotred");
			$(this).attr('checked',false);
		});
		$('#kill2').on('click', function() {
			var savesNow = parseFloat(window.localStorage.noOfSaves);
			savesNow -= 1;
			if (savesNow === 0) {
				$('#unsaver').text('Restore a previously saved form: (currently none)');
			}
			window.localStorage.noOfSaves = savesNow;
			window.localStorage.removeItem('oldName2');
			window.localStorage.removeItem('oldForm2');
			window.localStorage.removeItem('oldFile2');
			$('#lbl2').remove();
			$('#lblr2').remove();
		});
	}
} else {
  	window.alert('Sorry - no local web storage: cannot save form data for later use');   
}  // END OF FORM-SAVING

}); // end of page is loaded...