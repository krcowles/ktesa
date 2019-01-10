// clear out the input file list with each refresh:
$('#file').val(null);
// get the hike no:
var ehikeIndxNo = $('#ehno').text();

// dropped image handling
var orient;  // global required
var droppedImages = []; // array of FileReader objects loaded from dropped imgs
var loadedImages = [];  // array of DOM nodes containing dropped images
var imgNo = 0;
var droppedFiles = false;
var submittableImgs = []; // FileList of all dropped images, esp when dropped in stages
var currDropped;  // keep track of above count
function ldImgs(dimgs) {
    var promises = [];
    for(var i=0; i<dimgs.length; i++) {
        var reader = new FileReader(),
            d = new $.Deferred();
        promises.push(d);

        // Make sure we "capture" the correct 'd'
        (function(d){
            reader.onload = function (evt) {
                droppedImages.push(evt.target.result);
                d.resolve();
            }
        }(d));
        reader.readAsDataURL(dimgs[i]);
    }
    return $.when.apply($, promises); // apply 'promises' array to jQuery
}
function ldNodes(files) {
    var promises = [];
    var containers = []; // DOM nodes containing images
    var imgs = [];
    for (var j=0; j<files.length; j++) {
        // create image node:
        imgs[j] = document.createElement('img');
        var def = new $.Deferred();
        promises.push(def);

        (function(def){
            imgs[j].onload = function() {
                var ht = this.naturalHeight;
                var wd = this.naturalWidth;
                var ratio = wd/ht;
                orient = "";
                EXIF.getData(this, function() {
                    orient = EXIF.getTag(this, "Orientation");
                });
                if (orient == '6') {
                    this.style.transform = "rotate(90deg)";
                    /* height/width parms unchanged by rotate, 
                    * so 'width' of rotated img is actually height of img;
                    * This requires adjusting the top margin, since the rotated
                    * image will exceed the target container size
                    */
                    var adj = Math.floor(160/ratio);
                    this.height = adj;
                    var marg = (160 - adj)/2 + "px"; 
                    this.style.margin = marg + " 0px 0px 0px";
                } else {
                    this.height = 160;
                    this.style.margin = "0px 0px 0px 8px";
                }
                this.alt = "Drop" + imgNo;
                imgNo++;
                containers[j] = document.createElement('div');
                containers[j].style.cssFloat = "left";
                containers[j].appendChild(this);
                loadedImages.push(containers[j]);
                def.resolve();
            }
        }(def));
        imgs[j].src = files[j];
    }
    return $.when.apply($, promises);            
}

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
var $form = $('.box');
// if it is...
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
        $('#ldg').css('display', 'inline');
        droppedFiles = e.originalEvent.dataTransfer.files;
        currDropped = submittableImgs.length;
        var fileno = 0;
        for (var n=currDropped; n<currDropped + droppedFiles.length; n++) {
            submittableImgs[n] = droppedFiles[fileno];
            fileno++;
        }
        $.when( ldImgs(droppedFiles) ).then(function() {
            $.when( ldNodes(droppedImages) ).then(function() {
                $('#ldg').css('display', 'none');
                var dndbox = document.getElementsByClassName('box__dnd');
                for (var k=0; k<loadedImages.length; k++) {
                    dndbox[0].appendChild(loadedImages[k]);
                }
                // in case more get dropped later
                droppedImages = [];
                loadedImages = [];
            });
        });
    });
} else {
    alert("Dropping of images not supported for this browser.");
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
            for (var j=0; j<submittableImgs.length; j++) {
                ajaxData.append('files[]', submittableImgs[j] );
            }
        }
        ajaxData.append('indx', ehikeIndxNo);
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
    droppedImages = [];
    loadedImages = [];
    submittableImgs = [];
});
