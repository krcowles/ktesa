// clear out the input file list with each refresh:
$('#file').val(null);
// styling the 'Choose file...' input box and label text:
var inputs = document.querySelectorAll( '.inputfile' );
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
			label.querySelector( 'span' ).innerHTML = fileName;
        } else {
            label.innerHTML = labelVal;
        }
	});
});
// make sure FormData and FileReader support is in the browser window:
var isAdvancedUpload = 'FormData' in window && 'FileReader' in window;
// Assuming support, set up drag-n-drop file capture:
var droppedFiles =false; // global context needed;
var $form = $('.box');
if (isAdvancedUpload) {
    $form.addClass('has-advanced-upload');
    $('.box__dragndrop').css('display', 'inline');
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
                    // create container div (to size if rotated)
                    var container = document.createElement('div');
                    container.style.cssFloat = "left";
                    // create image node:
                    var usrimg = event.target.result;
                    var img = document.createElement('img');
                    img.src = usrimg;
                    var ht = img.naturalHeight;
                    var wd = img.naturalWidth;
                    var rat = wd/ht;
                    var node = document.getElementsByClassName('box__dnd')[0];
                    // if metadata, check orientation
                    var orient = "";
                    EXIF.getData(img, function() {
                        orient = EXIF.getTag(this, "Orientation");
                    });
                    if (orient == '6') {
                        img.style.transform = "rotate(90deg)";
                        /* height/width parms unchanged by rotate, 
                         * so 'width' of rotated img is actually height of img;
                         * This requires adjusting the top margin, since the rotated
                         * image will exceed the target container size
                         */
                        var adj = Math.floor(160/rat);
                        img.height = adj;
                        var marg = (160 - adj)/2 + "px"; 
                        img.style.margin = marg + " 0px 0px 0px";
                        spacer = false;
                    } else {
                        img.height = 160;
                        img.style.margin = "0px 0px 0px 8px";
                    }
                    container.height = 160;
                    container.appendChild(img);
                    node.appendChild(container);
                }
                reader.readAsDataURL(droppedFiles[j]); // start reading the file data.
            }
        }
    });
} else {
    alert("No FormData/FileReader support for this browser");
}
// form submittal
$form.on('submit', function(e) {
    if ($form.hasClass('is-uploading')) return false;
    $form.addClass('is-uploading').removeClass('is-error');
    if (isAdvancedUpload) {
        e.preventDefault();
        var inputFiles = document.getElementById('file');
        var fileList = inputFiles.files;
        var noOfFiles = fileList.length;
        var addDropped = false;
        if (noOfFiles == 0) {
            // any dragged files?
            if (!droppedFiles) {
                alert("No files have been chosen or dragged in for upload");
                $form.removeClass('is-uploading');
                return;
            } else {
                addDropped = true;
                ajaxData = new FormData();
            }
        } else {
            // retrieve any uploads via "Choose File..." 
            var ajaxData = new FormData($form.get(0));
            if (droppedFiles) {
                addDropped = true;
            }
        }
        if (addDropped) {
            for (var j=0; j<droppedFiles.length; j++) {
                ajaxData.append('files[]', droppedFiles[j] );
            }
        }
        $.ajax({
            url: 'usrPhotos.php',
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            complete: function() {
            $form.removeClass('is-uploading');
            },
            success: function(data) {
                if (data.substring(0,4) !== "Fail") {
                    $form.addClass('is-success');
                    alert(data);
                } else {
                    $form.addClass('is-error');
                }
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
    droppedFiles = false;
});
