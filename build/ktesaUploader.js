// styling the 'Choose file...' input box and label text:
var inputs = document.querySelectorAll( '.inputfile' );
var upldNames = [];
Array.prototype.forEach.call( inputs, function( input )
{
	var label	 = input.nextElementSibling;
	var labelVal = label.innerHtml;
	input.addEventListener( 'change', function( e )
	{
		var fileName = '';
		if( this.files && this.files.length > 1 ) {
			fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
        } else {
            fileName = e.target.value.split( '\\' ).pop();
        }
		if( fileName ) {
            upldNames.push(fileName);
			label.querySelector( 'span' ).innerHTML = "&nbsp;&nbsp;" + fileName;
        } else {
            label.innerHTML = labelVal;
        }
	});
});
// make sure FormData and FileReader support is in the browser window:
var fd_support = function() {
    return 'FormData' in window && 'FileReader' in window;
}();  // IIFE - true if features are supported

// Assuming support, set up drag-n-drop file capture:
var droppedFiles =false; // global context needed;
var $form = $('.box');
if (fd_support) {
    $form.addClass('supported');

    $form.on('drag dragstart dragend dragover dragenter dragleave drop', function(ev) {
        ev.preventDefault();
        ev.stopPropagation();
    })
    .on('dragover dragenter', function() {
        $form.addClass('is-dragover');
    })
    .on('dragleave dragend drop', function() {
        $form.removeClass('is-dragover');
    })
    .on('drop', function(e) {
        droppedFiles = e.originalEvent.dataTransfer.files;
        // show 'em!
        for (var j=0; j<droppedFiles.length; j++) {
            if (droppedFiles[j].type.match(/image.*/)) { // skip non-images
                var reader = new FileReader;
                reader.onload = function(event) {
                    // finished reading file data.
                    var usrimg = event.target.result;
                    var img = document.createElement('img');
                    img.src = usrimg;
                    img.height = 160;
                    var space = document.createTextNode("  ");
                    document.getElementById('box_dnd').appendChild(img);
                    document.getElementById('box_dnd').appendChild(space);
                }
                reader.readAsDataURL(droppedFiles[j]); // start reading the file data.
            }
        }
    });
}
// form submittal
$form.on('submit', function(e) {
    if ($form.hasClass('is-uploading')) {
        return false;
    }
    $form.addClass('is-uploading').removeClass('is-error');
  
    if (fd_support) {
      e.preventDefault();
      var ajaxData = new FormData($form.get(0));
      if (droppedFiles) {
            $.each( droppedFiles, function(i, file) {
                ajaxData.append( upldNames[i], file );
            });
      }
      $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: ajaxData,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        complete: function() {
          $form.removeClass('is-uploading');
        },
        success: function(data) {
            alert("GOT IT");
          $form.addClass( data.success == true ? 'is-success' : 'is-error' );
          if (!data.success) $errorMsg.text(data.error);
        },
        error: function() {
          // Log the error, show an alert, whatever works for you
        }
      });
    } else {
      // ajax for legacy browsers
    }
});
$('#clrimgs').on('click', function(ev) {
    ev.preventDefault();
    $imgs = $('img');
    $imgs.each(function() {
        $(this).remove();
    });
});
