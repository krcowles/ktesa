/**
 * The following globals are established for displaying images on the browser,
 * and displaying upload progress using the various means provided: an upload
 * bar giving overall progress, and upload 'dials' for each image.
 */
var ehikeIndxNo = $('#ehno').text(); // get the hike no for uploading 
// meters and progress bar
var $progressBar = $('#progbar');
var progPerUpld;
var $cmeter; // circular meter object
var cmtrIds = []; // each meter has a unique id required during submit
// objects during display/previewing of images on page
var validated = []; // files passing the filechecks function
var FR_Images = []; // FileReader objects to be loaded into DOM
var uploads = [];   // accumulated objects to be uploaded
var upldNo = 0;     // unique id for each image displayed
/**
 * While the following items can be changed, changing the image height (iheight)
 * will required adjustment of the 'rotation' class parameters in css.
 */
var iheight = 160; // image height on page
var nheight = 20;  // height of 'name' box
var dheight = 44;  // height of 'description' box
// where to place images
var dndbox = document.getElementsByClassName('box__dnd');
var droppedFiles = false; // if files are dropped, this holds the list
// image properties (all global)
var orient; // photo exif orientation data
var ajaxExif = []; // holds photos' exif data for storing in the db
var loadedImages = [];  // array of DOM nodes containing dropped/selected images
// track available space in current row for placement of next image
var row = false;
var frm = document.getElementsByClassName('box');
var dndWidth = frm[0].clientWidth;
var remainingWidth = dndWidth;
// upload arrays
var uloads = [];  // array of actual files to be uploaded
var desbox = [];  // array of image descriptions accompanying above files
var imgIds = [];  // array of image id's for uploads
var upldCnt = 0;
var nxtUpld = 0;
var $iflbl = $('label span'); // where the input file selection box text is held

// styling the 'Choose file...' input box and label text:
var inputs = document.querySelectorAll( '.inputfile' );

/**
 * The entire process begins with either a file select (from the "Choose..." input)
 * or by dragging and dropping one or more images onto the page. Either of these
 * actions will instantly result in a series of processes to properly display the
 * photo(s) on the page and establish the parameters needed for uploading. First,
 * the images ('File' objects in javascript, created either from the input file list,
 * or from the dataTransfer event in drag-n-drop) are "loaded" into javascript via
 * the 'ldImgs' function. When completed (deferred objects used), the function
 * 'ldNodes' is called. The ldImgs function creates FileReader objects used as input
 * to the ldNodes function. The ldNodes function establishes a progress dial for
 * each photo to be uploaded, rotates the image if needed, and otherwise prepares
 * the image(s) for display on the page. After ldNodes successfully completes (again,
 * using deferred objects), the 'dndPlace' function is invoked which situates the
 * uploaded images on the page. The upload process is separately described below.
 * Post upload functions can also be invoked: clear images, return to editor, etc.
 */
// preview any images selected by the "Choose..." button
$('#file').change(function() {
    previewImgs(this.files);
});
const previewImgs = (flist) => {
    $('#ldg').css('display', 'inline');
    $.when( filechecks(flist) ).then(function() {
        $.when( ldImgs(validated) ).then(function() {
            $.when( ldNodes(FR_Images) ).then(function() {
                dndPlace();
            });
        });
    });
}
// basic drag and drop...
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
        $.when( filechecks(droppedFiles) ).then(function() {
            $.when( ldImgs(validated) ).then(function() {
                $.when( ldNodes(FR_Images) ).then(function() {
                    dndPlace();
                });
            });
        });
    });
} else {
    alert("Dropping of images not supported for this browser.");
}

// functions for file checking (magic numbers)
const render = (magic) => {
    if (magic.binaryFileType !== 'image/jpeg') {
        return false;
        /*
        alert(magic.filename + "\nFiletype from file object: " + magic.filetype +
            "\nFiletype from binary: " + magic.binaryFileType + "\nHex: " +
            magic.hex);
        */
    } else {
        return true;
    }
}
const getMimetype = (signature) => {
    switch (signature) {
        case '89504E47':
            return 'image/png'
        case '47494638':
            return 'image/gif'
        case '25504446':
            return 'application/pdf'
        case 'FFD8FFDB':
        case 'FFD8FFE0':
        case 'FFD8FFE1':
            return 'image/jpeg'
        case '504B0304':
            return 'application/zip'
        default:
            return 'Unknown filetype'
    }
}
const filechecks = (candidates) => {
    var promises = [];
    for (let j=0; j<candidates.length; j++) {
        var file = candidates[j];
        var fname = file.name;
        if (fname.length > 1024) {
            alert("Please rename this file such that the name is\n" +
                "less than 1024 characters (including file extension\n" +
                "This file will not be displayed...");
            continue;
        }
        // test the file extension - only jpg files allowed at this time
        var lastdot = fname.lastIndexOf('.');
        if (lastdot !== -1) {
            var ext = fname.slice(lastdot+1);
            if (ext.toLowerCase() !== 'jpg' && ext.toLowerCase() !== 'jpeg') {
                alert('Type ".' + ext + '" (' + fname +')' +
                    " is not supported at this time");
                continue;
            }
        }
        if (file.size >= 8000000) {
            alert("This file is too large for upload - please resize it to less than 8Mbytes");
            continue;
        }
        // check the internal magic numbers for type jpeg
        var filereader = new FileReader();
        var magicdef = new $.Deferred();
        promises.push(magicdef);
        (function(def, candidate) {
            filereader.onloadend = function(evt) {
                if (evt.target.readyState === FileReader.DONE) {
                    var uint = new Uint8Array(evt.target.result)
                    let bytes = []
                    uint.forEach((byte) => {
                        bytes.push(byte.toString(16))
                    });
                    var hex = bytes.join('').toUpperCase()
                    var magic = {
                        filename: file.name,
                        filetype: file.type ? file.type : 'Unknown/Extension missing',
                        binaryFileType: getMimetype(hex),
                        hex: hex
                    };
                    if(render(magic)) {
                        validated.push(candidate);
                    } else {
                        alert(file.name + " is corrupt and cannot be displayed");
                    }
                    def.resolve();
                }
            }
            var blob = file.slice(0, 4);
            filereader.readAsArrayBuffer(blob);
        }(magicdef, file));
    }
    return $.when.apply($, promises); // return when promises fulfilled
}
// functions to load images, if successful place on DOM and display
const ldImgs = (imgs) => {
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
                var imgObj = {indx: upldNo, fname: ifile.name, size: ifile.size, data: result};
                FR_Images.push(imgObj);  // used for loading DOM, then reset
                var upldObj = {indx: upldNo++, ifile}; // ifile is already key-value pair
                uploads.push(upldObj);   // accumulated for form submit
                d.resolve();
            }
            reader.onerror = function() {
                alert("Problem encountered: file cannot be displayed\n" +
                    reader.error.message);
                d.resolve();
            }
        }(deferred, imgs[i]));
        reader.readAsDataURL(imgs[i]);
    }
    return $.when.apply($, promises); // return when promises fulfilled
}
const ldNodes = (fr_objs) => {
    var promises = [];
    var containers = []; // DOM nodes containing images & textareas
    var imgs = [];
    // IN ORDER OF fr_objs...
    for (var j=0; j<fr_objs.length; j++) {
        // create image node:
        imgs[j] = document.createElement('img');
        // identify the index in the FileReader object for this file
        var imgid = fr_objs[j]['indx'];
        var picname = fr_objs[j]['fname'];
        var def = new $.Deferred();
        promises.push(def);

        (function(def, itemno, imgname){
            imgs[j].onload = function() {
                var usable = true;
                EXIF.getData(this, function() {
                    let mappable = true;
                    let exifht = typeof EXIF.getTag(this, 'PixelYDimension');
                    if (exifht !== 'undefined') {
                        var origHt = EXIF.getTag(this, 'PixelYDimension');
                    } else {
                        var origHt = 0;
                        usable = false;
                    }
                    let exifwd = typeof EXIF.getTag(this, 'PixelXDimension');
                    if (exifwd !== 'undefined') {
                        var origWd = EXIF.getTag(this, 'PixelXDimension');
                    } else {
                        origWd = 0;
                        usable = false;
                    }
                    if (typeof EXIF.getTag(this, "GPSLatitude") !== 'undefined') {
                        var plat = extractLatLng(EXIF.getTag(this, "GPSLatitude"));
                    } else {
                        var plat = null;
                        mappable = false;
                    }
                    if (typeof EXIF.getTag(this, "GPSLongitude") !== 'undefined') {
                        var plng = extractLatLng(EXIF.getTag(this, "GPSLongitude"));
                    } else {
                        var plng = null;
                        mappable = false;
                    }
                     if (typeof EXIF.getTag(this, "DateTimeOriginal") !== 'undefined') {
                        var pdate = EXIF.getTag(this, "DateTimeOriginal");
                    } else {
                        var pdate = null;
                    }
                    if (typeof EXIF.getTag(this, "Orientation") !== 'undefined') {
                        orient = EXIF.getTag(this, "Orientation");
                    } else {
                        orient = '1'; // some legitimate images do not specify orientation
                    }
                    exifdat = {origHt: origHt, origWd: origWd, orient: orient,
                        fname: imgname, lat: plat, lng: plng, date: pdate};
                    // for every item in 'uploads', there is an upldNo id:
                    ajaxExif.push(exifdat);
                    if (usable) {
                        if (!mappable) {
                            alert( imgname + " has no location data - it can be uploaded,\n" +
                                "but cannot be attached to the hike map");
                        }
                    } else {
                        alert(imgname + " is unusable and cannot be uploaded");
                        // remove bad image instances in arrays
                        var deleteIndx;
                        uploads.forEach(function(upld_obj, i) {
                            if (upld_obj.indx == itemno) {
                                deleteIndx = i;
                            }
                        });
                        uploads.splice(deleteIndx, 1);
                        ajaxExif.pop();
                        upldNo--;
                    }
                });
                if(usable) {
                    // NOTE: img is not a DOM node: ht/wd do not require "px";
                    var ht = this.naturalHeight;
                    var wd = this.naturalWidth;
                    var ratio = wd/ht;
                    var ibox = document.createElement('DIV');
                    ibox.id = 'div' + itemno;
                    // create the div holding textarea boxes
                    var tbox = document.createElement('DIV');
                    // will hold text for description + circle meter
                    var des = document.createElement('TEXTAREA');
                    des.style.height = dheight + "px";
                    des.style.display = "block";
                    des.placeholder = "Picture description";
                    des.maxlength = 512;
                    //des.classList.add('desVal');
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
                    if (orient == '6' || orient == '8') {
                        // NOTE: image height/width parameters DO NOT CHANGE WHEN ROTATED
                        var scaledHeight = Math.floor(iheight/ratio);
                        this.width = iheight;  // this is constant
                        this.height = scaledHeight;  // this varies w/image aspect ratios
                        this.style.margin = "0px";
                        this.style.display = "block";
                        let offset = Math.round((iheight - scaledHeight)/2);
                        if (orient == '8') {
                            rotation = offset > 20 ? "arotate270" : "irotate270";
                            tboxoff  = offset > 20 ? "translate(0px, 70px)" :
                                "translate(0px, 40px)";
                        } else {
                            rotation = offset > 20 ? "arotate90" : "irotate90";
                            tboxoff  = offset > 20 ? "translate(0px, 70px)" :
                                "translate(0px, 40px)";
                        }
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
                         * items being rotated must be translated as if (y, x). If you
                         * attempt to use js transform for rotating then again for 
                         * translating the x/y origin, the second cmd overrides the first;
                         * therefore, a combined CSS class is used to do both. There
                         * are currently 2 camera styles, so classes exist for both.
                         */
                        this.classList.add(rotation);
                        // place the description (des) node and circle meter (circmtr) node
                        des.style.width = (scaledHeight - 4) + "px";
                        des.style.margin = "4px 0px 0px 2px";
                        tbox.style.width = scaledHeight + "px";
                        tbox.appendChild(des);
                        tbox.appendChild(circmtr);
                        tbox.style.margin = "0px 0px 0px 6px";



                        // items placed below the image act as if image is NOT rotated
                        tbox.style.transform = tboxoff;
                        // fix the image container
                        ibox.style.width = (scaledHeight + 16) + "px";
                        var accumht = iheight + (nheight + 4 + 12) + (dheight + 4) + "px";
                        ibox.style.height = accumht;
                    } else {
                        if (orient == '3') {
                            this.classList.add("rotate180");
                        }
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
                    this.id = "imgId" + itemno;
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
                }
                def.resolve();
            }
        }(def, imgid, picname));
        imgs[j].src = fr_objs[j]['data'];
    }
    return $.when.apply($, promises);            
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
    validated = [];
    FR_Images = [];     // browse button selections
    droppedFiles = [];  // dragged-n-dropped selections
    loadedImages = [];  // DOM nodes
}

/**
 * This code is actually what uploads, and tracks progress of each photo uploaded.
 * The uploads occur one at a time, so the function is recursive to an extent.
 * The uploading function is 'postImg'. This function relies on a standard
 * XHttpRequest in order to track upload progress and display it on the dial and
 * progress bar. When uploading for an image has completed, the code progresses
 * to the 'advanceUpload' function, which will mark the uploaded image as 'Uploaded'
 * and re-invoke the postImg file for the remaining photos. If the final photo
 * has been uploaded, the 'cleanup' fuction is invoked to complete the process
 * and re-initialize the variables needed for additional photos, if the user
 * so chooses. This function also displays the 'results' of the upload(s) in an
 * alert box.
 */
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
        $('#filecnt').text("0/" + uplds);
        progPerUpld = 100/uplds;
        $progressBar.val(0)
        // parameters to pass - associate upload (indx) with name/desc box
        for (var n=0; n<uploads.length; n++) {
            uloads[n] = uploads[n]['ifile'];  // file data (includes name, size)
            var indx = uploads[n]['indx'];
            desbox[n] = $('#desc' + indx).val();
            cmtrIds[n] = '#mtr' + indx;
            imgIds[n] = '#imgId' + indx;

        }
        // upload images one at a time; turn off 'is-uploading' when completed
        postImg(
            imgIds[nxtUpld], uloads[nxtUpld], desbox[nxtUpld], cmtrIds[nxtUpld],
                ehikeIndxNo, nxtUpld, uplds
        );
    } else {
      // ajax for legacy browsers e.g.
      // xhr = new XMLHttpRequest(); ...
    }
  });

function postImg(imgid, ifile, des, mtrid, hikeno, uldno, imgcnt) {
    // First, upload the image
    var uldefer = new $.Deferred();
    var dbdat = ajaxExif[uldno]; // exif data obtained in ldNodes()
    var picdesc = JSON.stringify(des);
    dbdat.indxNo = hikeno;
    dbdat.descstr = picdesc;
    ajaxData = new FormData();
    ajaxData.append('file', ifile);
    ajaxData.append('orient', dbdat.orient);
    ajaxData.append('origHt', dbdat.origHt);
    ajaxData.append('origWd', dbdat.origWd);
    $cmeter = $(mtrid);
    var xhr = new XMLHttpRequest();
    xhr.upload.addEventListener("progress", function(evt) {
        var percent = 1 - evt.loaded/evt.total;
        if (percent > .1) {
            var prog = percent * 99.99;
            $cmeter[0].setAttribute('stroke-dashoffset', prog);
        }
    }, false);
    // proceed with post
    xhr.open("POST", 'usrPhotos.php');
    xhr.send(ajaxData);
    xhr.onload = function() {
        var serverResponse = this.response;
        if (this.status !== 200) {  // e.g. 404, 500, etc.
            var newDoc = document.open();
		    newDoc.write(serverResponse);
		    newDoc.close();
            uldefer.reject();
            return;
        } else {
            $cmeter[0].setAttribute('stroke-dashoffset', '0');
            if (serverResponse.indexOf('Error:') !== -1) {
                alert("Image not uploaded:\n" + serverResponse);
                advanceUpload(imgid, hikeno, imgcnt, false);
                uldefer.reject();
            } else {
                dbdat.thumb = serverResponse;
                uldefer.resolve();
                advanceUpload(imgid, hikeno, imgcnt, true);
            }
            return;
        }
    }
    xhr.onerror = function() {
        // in developer mode, Whoops takes over and the following doesn't execute
        alert("The request failed for item " + nxtUpld);
        uldefer.fail();
        advanceUpload(imgid, hikeno, imgcnt, false);
        return;
    }
    $.when( uldefer ).then(function() { // only happens on xhr success
        $.ajax({
            url: 'savePhotoDat.php',
            method: "POST",
            data: dbdat,
            error: function(jqXHR, textStatus, errorThrown) {
                var newDoc = document.open();
                newDoc.write(jqXHR.responseText);
		        newDoc.close();
            }
            // success function not needed: advanceUpload already called
        });
    });
}
function advanceUpload(imageId, hikeno, imgCnt, success) {
    // mark this img with success or failure to upload
    if (!success) {
        $(imageId).addClass('notUploaded');
    }
    nxtUpld++;
    var newcnt = nxtUpld + "/" + imgCnt;
    $('#filecnt').text(newcnt);
    $progressBar.val(progPerUpld * nxtUpld);
    if (nxtUpld == imgCnt) {
        $form.removeClass('is-uploading');
        cleanup();
    } else {
        postImg(
            imgIds[nxtUpld], uloads[nxtUpld], desbox[nxtUpld], cmtrIds[nxtUpld], 
            hikeno, nxtUpld, imgCnt
        )
    }
}
function cleanup() {
    resets();
    $('img:not(#hikers, #tmap)').each(function() {
        if (!$(this).hasClass('uploaded') && !$(this).hasClass('notUploaded')) {
            var pos = $(this).offset();
            var saved = document.createElement('p');
            saved.classList.add('uploaded');
            var txt = document.createTextNode("UPLOADED");
            saved.appendChild(txt);
            saved.style.top = (pos.top + 12) + "px";
            saved.style.left = (pos.left + 12) + "px";
            $(this).before($(saved));
        } else if ($(this).hasClass('notUploaded')) {
            var pos = $(this).offset();
            var notsaved = document.createElement('p');
            notsaved.classList.add('uploadFail');
            var txt = document.createTextNode("NOT UPLOADED");
            notsaved.appendChild(txt);
            notsaved.style.top = (pos.top + 12)  + "px";
            notsaved.style.left = (pos.left) + 12 + "px";
            $(this).before($(notsaved));
        }
    });
    uloads = [];
    desbox = [];
}
function resets() {
    droppedFiles = false;
    uploads = [];
    upldCnt = 0;
    nxtUpld = 0;
}

/**
 * Miscellaneous functions:
 */

// The user may elect to 'clear out' existing photos, whether uploaded or not
$('#clrimgs').on('click', function(ev) {
    ev.preventDefault();
    $('.img-row').remove();
    resets();
    upldNo = 0;  // this is the only place this var should be reset
    remainingWidth = dndWidth;
    row = false;
    $iflbl.html("&nbsp;&nbsp;Choose one or more photos&hellip;");
});

// This button allows the user to return to the photo editor, with any new photos
$('#ret').on('click', function(ev) {
    ev.preventDefault();
    var user = $('#eusr').text();
    var newed = "editDB.php?hikeNo=" + ehikeIndxNo + "&usr=" + user + "&tab=2";
    window.open(newed, "_self");
});

// Convert EXIF array value into single value (for lat, lng)
const extractLatLng = (exifArray) => {
    if (exifArray.length !== 3) {
        return null;
    }
    let coord = exifArray[0] + (exifArray[1] + exifArray[2]/60)/60;
    return coord;
}