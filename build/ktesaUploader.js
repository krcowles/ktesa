// close the parent (tab2 of editDB) so that when returning, the page is refreshed
window.opener.close();
var $cmeter; // circular meter object
// get the hike no:
var ehikeIndxNo = $('#ehno').text();
var FR_Images = []; // FileReader objects to be loaded into DOM
var uploads = [];   // accumulated objects to be uploaded
var upldNo = 0;
/**
 * While the following items can be changed, changing the image height (iheight)
 * will required adjustment of the 'rotation' class parameters in css.
 */
var iheight = 160; // image height on page
var nheight = 20;  // height of 'name' box
var dheight = 44;  // height of 'description' box
// where to place images
var dndbox = document.getElementsByClassName('box__dnd');
// specific to dropped files
var droppedFiles = false;
var droppedImages = []; // array of input files dropped by user
// image defs
var orient;  // photo exif orientation data: global required
var loadedImages = [];  // array of DOM nodes containing dropped/selected images
// track available space in current row for placement of next image
var row = false;
var frm = document.getElementsByClassName('box');
var dndWidth = frm[0].clientWidth;
var remainingWidth = dndWidth;
// upload arrays
var uloads = [];
var nmebox = [];
var desbox = [];
var upldCnt = 0;
var nxtUpld = 0;
var $iflbl = $('label span'); // where the input file selection box text is held

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

$('#clrimgs').on('click', function(ev) {
    ev.preventDefault();
    $('.img-row').remove();
    resets();
    remainingWidth = dndWidth;
    row = false;
    $iflbl.html("&nbsp;&nbsp;Choose one or more photos&hellip;");
});

// general purpose functions w/deferred objects
function ldImgs(imgs) {
    var promises = [];
    for(var i=0; i<imgs.length; i++) {
        var reader = new FileReader();
        var deferred = new $.Deferred();
        promises.push(deferred);

        (function(d, ifile) {
            reader.onload = function (evt) {
                var result = evt.target.result;
                /**
                 * There's no way to predict the order the files will
                 * actually be loaded, so the array index in 'uploads' is used
                 * to identify the item and associate it with name/desc boxes
                 * in the DOM. 'upldNo' is the array index, which will be
                 * unique, even for duplicate filenames.
                 */
                var nme = ifile.name;
                var fsize = ifile.size;
                var imgObj = {indx: upldNo, fname: nme, size: fsize, data: result};
                FR_Images.push(imgObj);  // used for loading DOM, then reset
                var upldObj = {indx: upldNo++, ifile};
                uploads.push(upldObj);   // accumulated for form submit
                d.resolve();
            }
        }(deferred, imgs[i]));

        reader.readAsDataURL(imgs[i]);
    }
    return $.when.apply($, promises); // apply 'promises' array to jQuery
}
function ldNodes(fr_objs) {
    var promises = [];
    var containers = []; // DOM nodes containing images & textareas
    var imgs = [];
    for (var j=0; j<fr_objs.length; j++) {
        // create image node:
        imgs[j] = document.createElement('img');
        // identify the index in the uploads array for this file
        var imgid = fr_objs[j]['indx'];
        var def = new $.Deferred();
        promises.push(def);

        (function(def, itemno){
            imgs[j].onload = function() {
                // NOTE: img is not a DOM node: ht/wd do not require "px";
                var ht = this.naturalHeight;
                var wd = this.naturalWidth;
                var ratio = wd/ht;
                var ibox = document.createElement('DIV');
                ibox.id = 'div' + itemno;
                // create the div holding textarea boxes
                var tbox = document.createElement('DIV');
                tbox.classList.add('txtdata');

                // textarea for picture 'description'
                var des = document.createElement('TEXTAREA');
                des.style.height = dheight + "px";
                des.style.display = "block";
                des.placeholder = "Picture description";
                des.classList.add('desVal');
                des.id = 'desc' + itemno;

                // circular progress meter
                var xmlns = "http://www.w3.org/2000/svg";
                var circmtr = document.createElement('DIV');
                circmtr.style.textAlign = "center";
                circmtr.style.margin = "4px 0px 0px 0px";
                var svg = document.createElementNS (xmlns, "svg");
                svg.setAttribute("viewBox", "0 0 32 32");
                svg.setAttribute("width", '32');
                svg.setAttribute("height", '32');
                svg.setAttribute('style', 'transform:rotate(-90deg)')
                // background circle
                var circle = document.createElementNS(xmlns, 'circle');
                circle.setAttribute('cx', '16');
                circle.setAttribute('cy', '16');
                circle.setAttribute('r', '14');
                circle.setAttribute('fill', 'none');
                circle.setAttribute('stroke', '#fcbcb5');
                circle.setAttribute('stroke-width', '4');
                // foreground progress circle
                var prog = document.createElementNS(xmlns, 'circle');
                prog.id = "mtr" + itemno;
                prog.setAttribute('cx', '16');
                prog.setAttribute('cy', '16');
                prog.setAttribute('r', '14');
                prog.setAttribute('fill', 'none');
                prog.setAttribute('stroke', 'brown'); // #f73722
                prog.setAttribute('stroke-width', '4');
                prog.setAttribute('stroke-dasharray', '87.964');
                prog.setAttribute('stroke-dashoffset', '87.964');
                svg.appendChild(circle);
                svg.appendChild(prog);
                circmtr.appendChild(svg);

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
                    //nme.style.width = (scaledHeight - 4) + "px"; // 4 for TA borders
                    //nme.style.margin = "6px 0px 6px 2px";
                    des.style.width = (scaledHeight - 4) + "px";
                    des.style.margin = "4px 0px 0px 2px";
                    tbox.style.width = scaledHeight + "px";
                    tbox.appendChild(des);
                    tbox.appendChild(circmtr);
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
                    //nme.style.width = (scaledWidth - 4) + "px"; // subtract TA borders
                    //nme.style.margin = "6px 0px 6px 6px";
                    des.style.width = (scaledWidth - 4) + "px";
                    des.style.margin = "4px 0px 0px 6px";
                    tbox.style.width = scaledWidth + "px";
                    tbox.appendChild(des);
                    tbox.appendChild(circmtr);
                    /**
                     * Each textarea has 2px of border, top & bottom, ie 4px total
                     * There is a margin (6px) on the top & bottom of the name textarea,
                     * for 12px total, no margin on the description ta:
                     */
                    var accumht = iheight + (nheight + 4 + 12) + dheight + "px"; 
                    ibox.style.height = accumht;
                    ibox.style.width = (scaledWidth + 12) + "px";
                }
                this.alt = "image" + itemno;
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
        }(def, imgid));
        imgs[j].src = fr_objs[j]['data'];
    }
    return $.when.apply($, promises);            
}
function sizeCheck() {
    var promises = [];
    for (var j=0; j<FR_Images.length; j++) {
        var rdef = new $.Deferred();
        promises.push(rdef);
        // set up resolver:
        if (FR_Images[j]['size'] < 8000000) { // limit found in php log error
            rdef.resolve();
        } else {
            /**
             * From StackOVerflow:
             * https://stackoverflow.com/questions/42092640/
             *      javascript-how-to-reduce-image-to-specific-file-size
             * "As far as i'm concerned, there is no way to reduce the filesize
             * of an image on the client side using javascript."
             * 
             * NOTE: using ajax to send the file to php where it CAN be 
             * reduced won't work, since the whole point was to reduce the
             * filesize PRIOR to using ajax during uploads, hence exceeding
             * transfer limit specified in the PHP error log:
             *      ... exceeds the limit of 8388608 bytes ...
             * The HTTP spec does not specify a limit, so it may be server
             * dependent.
             */  
            alert("File " + FR_Images[j]['fname'] + " too big to upload: " +
                "[ " + FR_Images[j]['size'] + " ]\n" +
                "You must reduce it first to under 8MB, then reload");
            rdef.reject();
        }
    }
    return $.when.apply($, promises); // apply 'promises' array to jQuery
}

// preview any images selected by the "Choose..." button
$('#file').change(function() {
    previewImgs(this.files);
});
function previewImgs(flist) {
    $('#ldg').css('display', 'inline');
    $.when( ldImgs(flist) ).then(function() {
        $.when( ldNodes(FR_Images) ).then(function() {
            $.when( sizeCheck() ). then(function() {
                dndPlace();
            });
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
    FR_Images = [];     // browse button selections
    droppedFiles = [];  // dragged-n-dropped selections
    loadedImages = [];  // DOM nodes
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
        $.when( ldImgs(droppedFiles) ).then(function() {
            $.when( ldNodes(FR_Images) ).then(function() {
                $.when( sizeCheck() ). then(function() {
                    dndPlace();
                });
            });
        });
    });
} else {
    alert("Dropping of images not supported for this browser.");
}

// form submittal
$form.on('submit', function(e) {
    if ($form.hasClass('is-uploading')) return false;
    $form.addClass('is-uploading');
    if (isAdvancedUpload) {
        e.preventDefault();
        var uplds = uploads.length;
        if (uplds === 0) {
            alert("No files have been chosen or dragged in for upload");
                $form.removeClass('is-uploading');
            return;
        }
        // parameters to pass - associate upload (indx) with name/desc box
        for (var n=0; n<uploads.length; n++) {
            uloads[n] = uploads[n]['ifile'];  // file data (includes name, size)
            var indx = uploads[n]['indx'];
            //nmebox[n] = $('#name' + indx).val();
            desbox[n] = $('#desc' + indx).val();
        }
        // upload images one at a time; turn off 'is-uploading' when completed
        sequentialUploader(
            uloads[nxtUpld], nmebox[nxtUpld], desbox[nxtUpld], 
            ehikeIndxNo, uplds
        );
    } else {
      // ajax for legacy browsers e.g.
      // xhr = new XMLHttpRequest(); ...
    }
  });

function sequentialUploader(imgfile, picname, picdesc, hikeno, noOfImgs) {
    postImg(imgfile, picdesc, hikeno).then(function() {
        nxtUpld++;
        if (nxtUpld == noOfImgs) {
            $form.removeClass('is-uploading');
            cleanup();
        } else {
            sequentialUploader(
                uloads[nxtUpld], nmebox[nxtUpld], desbox[nxtUpld], 
                hikeno, noOfImgs
            )
        }
    });
}
function postImg(ifile, des, hikeno) {
    var def = new $.Deferred();
    ajaxData = new FormData();
    ajaxData.append('file', ifile);
    var picdesc = JSON.stringify(des);
    ajaxData.append('descstr', picdesc);
    ajaxData.append('indx', hikeno);
    $cmeter = $('#mtr' + nxtUpld);
    var bytes = 0;
    var completion;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", 'usrPhotos.php');
    xhr.onprogress = function() {
        if (xhr.responseText.indexOf('X') !== -1) {
            def.reject();
        }
        bytes++;
        completion = (1 - bytes/5) * 87.964;
        $cmeter[0].setAttribute('stroke-dashoffset', completion);
    };
    xhr.send(ajaxData);
    xhr.onload = function() {
        if (this.status !== 200) {
            alert("The server returned error " + this.status);
            def.reject();
        } else {
            $cmeter[0].setAttribute('stroke-dashoffset', '0');
            def.resolve();
        }
    }
    xhr.onerror = function() {
        alert("The request failed for item " + nxtUpld);
        def.reject();
    }
    return def.promise();
}
function cleanup() {
    resets();
    $('img:not(#hikers, #tmap)').each(function() {
        if (!$(this).hasClass('uploaded')) {
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
        }
    });
    $.ajax({
        method: 'GET',
        url: 'statusReader.php',
        dataType: 'text',
        success: function(data) {
            alert(data);
        },
        error: function(jqXHR, errmsg, httpErr) {
            var msg = "Error encountered in retrieving the status text file: " +
                "\nLine 463 in ktesaUploader.js; \nerror found: " +
                errmsg + " " + httpErr;
        }
    });
    uloads = [];
    nmebox = [];
    desbox = [];
}
function resets() {
    droppedFiles = false;
    uploads = [];
    droppedImages = [];
    upldNo = 0;
    upldCnt = 0;
    nxtUpld = 0;
}
$('#ret').on('click', function(ev) {
    ev.preventDefault();
    var user = $('#eusr').text();
    var newed = "editDB.php?hno=" + ehikeIndxNo + "&usr=" + user + "&tab=2";
    window.open(newed, "_self");
});
