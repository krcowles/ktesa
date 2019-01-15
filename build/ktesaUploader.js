// establish the height of images to be displayed on each row
var dheight = 160;
// get the hike no:
var ehikeIndxNo = $('#ehno').text();
// the collection of all accumulated images to be uploaded
var PageUploads = [];

// image defs
var orient;  // global required
var droppedImages = []; // array of FileReader objects loaded from dropped imgs
var loadedImages = [];  // array of DOM nodes containing dropped images
var imgNo = 0;
var droppedFiles = false;
var submittableImgs = []; // FileList of all dropped images, esp when dropped in stages
var currDropped;  // keep track of above count

// preview any images selected by the "Choose..." button
$('#file').change(function() {
    previewImgs(this.files);
});
function previewImgs(flist) {
    $('#ldg').css('display', 'inline');
    $.when( ldImgs(flist) ).then(function() {
        $.when( ldNodes(droppedImages) ).then(function() {
            dndPlace();
        });
    });
}
// general purpose functions w/deferred objects (i.e. jQuery promises)
function ldImgs(dimgs) {
    var promises = [];
    for(var i=0; i<dimgs.length; i++) {
        PageUploads.push(dimgs[i]);
        var reader = new FileReader(),
            d = new $.Deferred();
        promises.push(d);

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
                // NOTE: img is not a DOM node: ht/wd do not require "px";
                var ht = this.naturalHeight;
                var wd = this.naturalWidth;
                var ratio = wd/ht;
                var ibox = document.createElement('DIV');
                var nme = document.createElement('TEXTAREA');
                nme.style.height = "20px";
                nme.style.display = "block";
                nme.style.margin = "6px 0px 0px 6px";
                orient = "";
                EXIF.getData(this, function() {
                    orient = EXIF.getTag(this, "Orientation");
                });
                if (orient == '6') {
                    // img height/width params don't change when rotated
                    var scaledHeight = Math.floor(dheight/ratio);
                    this.width = dheight;
                    this.height = scaledHeight;
                    this.style.margin = "0px";
                    this.style.display = "block";
                    // rotation + translation (offset due to rotation) is done via class
                    this.classList.add('rotation');
                    // things placed in div still behave as tho img is NOT rotated
                    nme.style.width = (scaledHeight - 4) + "px"; // TA borders
                    nme.style.transform = "translate(0px, 40px)";
                    ibox.style.width = (scaledHeight + 16) + "px";
                    ibox.style.height = (dheight + 30) + "px";
                } else {
                    this.height = dheight;
                    this.style.margin = "0px 6px";
                    this.style.display = "block";
                    ibox.style.height = (dheight + 26) + "px"; // add in TA + space
                    var scaledWidth = Math.floor(dheight * ratio);
                    nme.style.width = (scaledWidth - 4) + "px"; // TA borders
                    ibox.style.width = (scaledWidth + 12) + "px";
                }
                ibox.style.cssFloat = "left";
                ibox.style.margin = "0px 6px 24px 6px";
                ibox.appendChild(this);
                ibox.appendChild(nme);
                containers[j] = ibox;
                this.alt = "image" + imgNo;
                imgNo++;
                // detect right-click on image:
                containers[j].addEventListener('contextmenu', function(ev) {
                    ev.preventDefault();
                    if (confirm('Do you wish to delete this image?')) {
                        alert("DELETE");
                        // find item in PageUploads: delete that and 'this'
                    }
                    return false;
                });
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
               dndPlace();
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
        if (PageUploads.length === 0) {
            alert("No files have been chosen or dragged in for upload");
                $form.removeClass('is-uploading');
            return;
        }
        ajaxData = new FormData();
        for (var k=0; k<PageUploads.length; k++) {
            ajaxData.append('files[]', PageUploads[k]);
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
    PageUploads = [];
});
function dndPlace() {
    $('#ldg').css('display', 'none');
    var dndbox = document.getElementsByClassName('box__dnd');
    var dndWidth = parseInt(dndbox[0].style.width);
    var remainingWidth = dndWidth;
    for (var k=0; k<loadedImages.length; k++) {
        var liWidth = parseInt(loadedImages[k].style.width);
        if (liWidth + 8 > remainingWidth) {
            var brk = document.createElement("<br />");
            dndbox[0].appendChild(brk);
            remainingWidth = dndWidth;
        }
        dndbox[0].appendChild(loadedImages[k]);
        remainingWidth -= liWidth;
    }
    droppedImages = [];
    loadedImages = [];
}

