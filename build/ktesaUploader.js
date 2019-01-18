/**
 * Initialization:
 */
// get the hike no:
var ehikeIndxNo = $('#ehno').text();
// the collection of all accumulated items to be uploaded
var imageUploads = [];
var nameUploads = [];
var descUploads = [];
/**
 * While the following items can be changed, changing the image height (iheight)
 * will required adjustment of the 'rotation' class parameters in css.
 */
var iheight = 160; // image height on page
var nheight = 20;  // height of 'name' box
var dheight = 44;  // height of 'description' box
// where to place images
var dndbox = document.getElementsByClassName('box__dnd'); // [0] is box__dnd
// image defs
var orient;  // photo exif orientation data: global required
var droppedImages = []; // array of FileReader objects loaded from drop/selection
var loadedImages = [];  // array of DOM nodes containing dropped/selected images
var imgNo = 0; // used for 'alt' descripton of <img> tags
var imgSizes = [];
// specific to dropped files
var droppedFiles = false;
var submittableImgs = []; // FileList of all dropped images, esp when dropped in stages
var currDropped;  // keep track of above count
// track available space in current row for placement of next image
var row = false;
var frm = document.getElementsByClassName('box');
var dndWidth = frm[0].clientWidth;
remainingWidth = dndWidth;
// upload progress
var $progressBar = $('#prog');
$progressBar.css('display', 'none');
var upldProg = 0;
var upldCnt = 0;

$('#clrimgs').on('click', function(ev) {
    ev.preventDefault();
    $('.img-row').remove();
    droppedFiles = false;
    droppedImages = [];
    loadedImages = [];
    submittableImgs = [];
    imgSizes = [];
    imageUploads = [];
    nameUploads = [];
    descUploads = [];
    upldCnt = 0;
    remainingWidth = dndWidth;
    imgNo = 0;
    row = false;
});

// general purpose functions w/deferred objects (i.e. jQuery promises)
function ldImgs(dimgs) {
    var promises = [];
    for(var i=0; i<dimgs.length; i++) {
        imageUploads.push(dimgs[i]);
        imgSizes.push(dimgs[i].size);
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
    var containers = []; // DOM nodes containing images & textareas
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
                // create the div holding textarea boxes
                var tbox = document.createElement('DIV');
                tbox.classList.add('txtdata');
                // textarea for picture 'name'
                var nme = document.createElement('TEXTAREA');
                nme.style.height = nheight + "px";
                nme.style.display = "block";
                
                nme.placeholder = "Picture name";
                nme.classList.add('nmeVal');
                // textarea for picture 'description'
                var des = document.createElement('TEXTAREA');
                des.style.height = dheight + "px";
                des.style.display = "block";
                
                des.placeholder = "Picture description";
                des.classList.add('desVal');
                orient = "";
                EXIF.getData(this, function() {
                    orient = EXIF.getTag(this, "Orientation");
                });
                if (orient == '6') {
                    // NOTE: image height/width parameters DO NOT CHANGE WHEN ROTATED
                    var scaledHeight = Math.floor(iheight/ratio);
                    this.width = iheight;
                    this.height = scaledHeight;
                    this.style.margin = "0px";
                    this.style.display = "block";
                    /**
                     * When rotating the image about it's center, the old 'width'
                     * becomes the new 'height'. The DOM behaves as if the image
                     * was NOT rotated, so the rotated image overflows it's old height 
                     * boundary on each end by (Iwd - Iht)/2. Similarly, the old
                     * 'height' is the new 'width', and there is empty space on
                     * each side corresponding to (Iwd - Iht)/2. In order to place
                     * the top left corner of the rotatated image where the original
                     * top left corner of the image was, it is necessary to 'translate'
                     * the (x,y) coordinates of the center by (Iwd - Iht)/2. It is
                     * also necessary to adjust the height/width of the container div
                     * to correspond to the newly dimensioned (rotated) image. If 
                     * the div is wider than the rotated image, to center the image
                     * in the div, translation of (Dwd - Iht)/2 is necessary instead
                     * of (Iwd - Iht)/2. Since the rotation reverses the x/y axes,
                     * items being rotated must be translated as if (y, x). Rotation and
                     * translation for the image are done in the css class 'rotation'. 
                     */
                    this.classList.add('rotation');
                    // place the textarea boxes in tbox:
                    nme.style.width = (scaledHeight - 4) + "px"; // 4 for TA borders
                    nme.style.margin = "6px 0px 6px 2px";
                    des.style.width = (scaledHeight - 4) + "px";
                    des.style.margin = "0px 0px 0px 2px";
                    tbox.style.width = scaledHeight + "px";
                    tbox.appendChild(nme);
                    tbox.appendChild(des);
                    tbox.style.margin = "0px 0px 0px 6px";
                    // items placed below the image act as if image is NOT rotated
                    tbox.style.transform = "translate(0px, 40px)";
                    // fix the image container
                    ibox.style.width = (scaledHeight + 16) + "px";
                    var accumht = iheight + (nheight + 4 + 12) + (dheight + 4) + "px";
                    ibox.style.height = accumht;
                } else {
                    var scaledWidth = Math.floor(iheight * ratio);
                    this.height = iheight;
                    this.style.margin = "0px 6px";
                    this.style.display = "block";
                    nme.style.width = (scaledWidth - 4) + "px"; // subtract TA borders
                    nme.style.margin = "6px 0px 6px 6px";
                    des.style.width = (scaledWidth - 4) + "px";
                    des.style.margin = "0px 0px 0px 6px";
                    tbox.style.width = scaledWidth + "px";
                    tbox.appendChild(nme);
                    tbox.appendChild(des);
                    /**
                     * Each textarea has 2px of border, top & bottom, ie 4px total
                     * There is a margin (6px) on the top & bottom of the name textarea,
                     * for 12px total, no margin on the description ta:
                     */
                    var accumht = iheight + (nheight + 4 + 12) + dheight + "px"; 
                    ibox.style.height = accumht;
                    ibox.style.width = (scaledWidth + 12) + "px";
                }
                this.alt = "image" + imgNo++;
                ibox.classList.add('imgbox');
                ibox.style.cssFloat = "left";
                ibox.style.margin = "0px 6px 24px 6px";
                ibox.appendChild(this);
                ibox.appendChild(tbox);
                containers[j] = ibox;
                // detect right-click on image:
                containers[j].addEventListener('contextmenu', function(ev) {
                    ev.preventDefault();
                    if (confirm('Do you wish to delete this image?')) {
                        alert("DELETE");
                        // find item in imageUploads: delete that and 'this'
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
// when files are dropped, or chosen by button,  place them on the page
function dndPlace() {
    $('#ldg').css('display', 'none');
    if (!row) {
        row = document.createElement('DIV');
        row.classList.add('img-row');
        row.style.display = "block";
    }
    // need to account for margins/padding et al
    for (var k=0; k<loadedImages.length; k++) {
        var liWidth = parseInt(loadedImages[k].style.width);
        var lmarg = parseInt(loadedImages[k].style.marginLeft);
        var rmarg = parseInt(loadedImages[k].style.marginRight);
        var totwd = liWidth + lmarg + rmarg;
        if (totwd + 8 > remainingWidth) { // 8 is an arbitrary 'safety' margin
            dndbox[0].appendChild(row);
            row = document.createElement('DIV');
            row.classList.add('img-row');
            row.style.display = "block";
            row.style.clear = "both";
            row.appendChild(loadedImages[k]);
            remainingWidth = dndWidth;
        } else {
            row.appendChild(loadedImages[k]);
            remainingWidth -= totwd;
        }
    }
    dndbox[0].appendChild(row);
    droppedImages = [];
    loadedImages = [];
}
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
    $progressBar.css('display', 'inline');
    $progressBar.val(0);
    if (isAdvancedUpload) {
        e.preventDefault();
        var uplds = imageUploads.length;
        if (uplds === 0) {
            alert("No files have been chosen or dragged in for upload");
                $form.removeClass('is-uploading');
            return;
        }
        var progPerUpld = 100/uplds;
        $('.nmeVal').each(function() {
            nameUploads.push($(this).val());
        });
        $('.desVal').each(function() {
            descUploads.push($(this).val());
        });
        // upload images one at a time; turn off 'is-uploading' when completed
        for (var u=0; u<uplds; u++) {
            if (imgSizes[u] > 1000000) {
                $('#resize').css('display', 'inline');
                resizeImage(imageUploads[u]).done(function() {
                    $('#resize').css('display', 'none');
                    upldCnt++;
                    if (upldCnt == uplds) {
                        $form.removeClass('is-uploading');
                        cleanup();
                    }
                });
            }
            postImg(
                imageUploads[u], nameUploads[u], descUploads[u], ehikeIndxNo, progPerUpld
            ).done(function() {
                upldCnt++;
                if (upldCnt == uplds) {
                    $form.removeClass('is-uploading');
                    cleanup();
                }
            });
        }
    } else {
      // ajax for legacy browsers
    }
  });

function resizeImage(img) {
    data = new FormData();
    data.append('img', img);
    return $.ajax({
        url: 'resize.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            alert("OK");
        },
        error: function(jqXHR, status, error) {
            var msg = "Error occurred during ajax:\nktesaUploader.js line 354\n" 
                + "Actual response: " + jqXHR.responseText + ";\n" + status +
                ": Error is: " + error ;
            alert(msg);
        }
    });
}
function postImg(ifile, nme, des, hikeno, proginc) {
    ajaxData = new FormData();
    ajaxData.append('img', ifile);
    var picname = JSON.stringify(nme);
    ajaxData.append('pnme', picname);
    var picdesc = JSON.stringify(des);
    ajaxData.append('pdes', picdesc);
    ajaxData.append('indx', hikeno);
    return $.ajax({
        url: 'test.php',
        type: 'POST',
        data: ajaxData,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            var bar = $progressBar.val();
            bar += proginc;
            $progressBar.val(bar);
        },
        error: function(jqXHR, status, error) {
            var msg = "Error occurred during ajax:\nktesaUploader.js line 381\n" 
                + "Actual response: " + jqXHR.responseText + ";\n" + status +
                ": Error is: " + error;
            alert(msg);
        }
    });
}
function cleanup() {
    droppedFiles = false;
    droppedImages = [];
    loadedImages = [];
    submittableImgs = [];
    imgSizes = [];
    imageUploads = [];
    nameUploads = [];
    descUploads = [];
    upldCnt = 0;
    $('img:not(#hikers, #tmap)').each(function() {
        var pos = $(this).offset();
        var ptop = pos.top;
        var plft = pos.left;
        var saved = document.createElement('P');
        saved.classList.add('uploaded');
        var stxt = document.createTextNode("UPLOADED");
        saved.appendChild(stxt);
        saved.style.top = (ptop + 12) + "px";
        saved.style.left = (plft + 12) + "px";
        $(this).before($(saved));
    });
}
