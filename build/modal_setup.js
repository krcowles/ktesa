// modal object definition
var modal = (function() {
	// Local/private to "modal"
	var $window = $(window);
	var $modal = $('<div class="modal" style="background-color:white;"/>'); 
			// NOTE: "/>" is a means of self-closing the div
	var $content = $('<div class="modal-content"/>');
	var $close = $('<button role="button" class="modal-close">Close</button>');
	
	$modal.append($content);
	$modal.append($close);
	$close.on('click', function(e) {
		e.preventDefault();
		modal.close();
	});
	
	/* FUNCTION TO CONVERT FORM DATA INTO STRING FOR SAVING IN LOCAL STORAGE */
	function formToString(filledForm) {
		formObject = new Object
		filledForm.find("input, select").each(function() {
			if (this.id) {
				elem = $(this);
				if (elem.attr("type") == 'checkbox' || elem.attr("type") == 'radio') {
					formObject[this.id] = elem.attr("checked");
				} else {
					formObject[this.id] = elem.val();
				}
			}
		});
		formString = JSON.stringify(formObject);
		return formString;
	}
	// THE WORKHORSE TO SAVE THE FORM
	function formSaver() {
		var msg;
		var closeModal = true;
		var saveName = $('#savetxt').val();
		if (saveName === '') {
			window.alert("Please enter an identifier in text box!");
			$(this).attr('checked',false);
		} else {
			// save the current state of the form
			formString = formToString($('#hikeData'));
			var currentSaves = parseFloat(window.localStorage.noOfSaves);
			if (currentSaves >= 2) {
				msg = 'ALREADY SAVED 2: Nothing new will be saved';
				window.alert(msg);
			} else if (currentSaves === 1) {
				var prevName = window.localStorage.oldName1
				if (prevName == saveName) {
					msg = 'Choose a different identifier - this was previously saved';
					window.alert(msg);
					$('#edit').prop('checked',false);
					$('#closeit').prop('checked',false);
					closeModal = false;
				} else {
					msg = '<p>You have previously saved: ' + prevName + '</p>';
					window.alert(msg);
					window.localStorage.oldName2 = saveName;
					window.localStorage.oldForm2 = formString;
					window.localStorage.noOfSaves = 2;
					msg = '<input id="second" type="radio" name="restores" value="name2" />' + saveName + '<br />';
					$('#unsaver').after(msg);
					$('#second').on('click', function() {
					});
				}
			} else {
				window.localStorage.oldName1 = saveName;
				window.localStorage.oldForm1 = formString;
				window.localStorage.noOfSaves = 1;
				$('#unsaver').text('Restore a previously saved form: ');
				msg = '<input id="first" type="radio" name="restores" value="name1" />' + saveName;
				$('#unsaver').after(msg);
				$('#first').on('click', function() {
				});
			}
			if (closeModal === true) {
				modal.close();
			}
		}
	}
	
	// public
	return {   // returns object methods:
		center: function() {
			var top = Math.max($window.height() - $modal.outerHeight(), 0) / 2;
			var left = Math.max($window.width() - $modal.outerWidth(), 0) / 2;
			$modal.css({
				top: top + $window.scrollTop(),
				left: left + $window.scrollLeft()
			});
		},
		open: function(settings) {
			$content.empty().append(settings.content.html());
			$modal.css({
				width: settings.width || auto,
				height: settings.height || auto,
				border: '3px solid',
				padding: '8px'
			}).appendTo('body');
			modal.center();
			$(window).on('resize', modal.center);
			if (settings.id === 'saver') {
				$close.detach();
				$('#edit').on('click', formSaver);
				$('#closeit').on('click', function() {
					formSaver();
					window.close()
				});
				$('#dontsave').on('click', function() {
					modal.close();
				});
			}
		},
		close: function() {
			$content.empty();
			$modal.detach();
			$(window).off('resize', modal.center);
		}
	};
}());  // IIFE