/**
 * @fileoverview This is a standalone utility that will preview selected
 * images (input select or drag-and-drop) on the page, having stored a
 * resized image on the server. The user may select desired images to be
 * saved and used by the editor.
 * @author Tom Sandberg
 * @author Ken Cowles
 */

// admin and server defined constants
const MAX_UPLOAD_SIZE = 20000000; // no longer required
const Z_WIDTH = 640;
const Z_HEIGHT = 480;
const DISPLAY_HEIGHT = Z_HEIGHT/2;
// globals
var ehikeIndxNo = $('#ehno').text(); // get the associated hike no
var droppedFiles = false; 
var validated = [];
var FR_Images = []; // FileReader objects
var imgNo = 0;      // unique id for each validated image
var ajaxExif = [];  // holds photos exif data for storing in the db

/**
 * Whenever a page load completes, re-initialization of arrays and text
 * should take place
 * 
 * @return {null}
 */
const resetImageLoads = () => {
    $('#ldg').css('display', 'none');
    droppedFiles = false;
    validated = [];
    FR_Images = [];
    positionIcons();
    enableIcons();
    return;
};

/**
 * This routine places the icons appropriately in the left corner of the photo
 * 
 * @return {null}
 */
function positionIcons() {
    $('.dels').each(function() {
        let img = '#img' + this.id.substr(3);
        let imgpos = $(img).offset();
        let icon_left = imgpos.left + 12;
        let icon_top  = imgpos.top  + 12;
        $(this).css({
            top:  icon_top,
            left: icon_left,
        });
    });
    return;
}

/**
 * This function enables icons for display on mouseover, and also on click
 * for photo deletion (and its accompanying icon)
 * 
 * @return {null}
 */
function enableIcons() {
    $('.not-saved').each(function() {
        let icon = '#del' + this.id.substr(3);
        let $img = $(this);
        let iname = this.src;
        let data = {iname: iname};
        $(this).on('mouseover', function() {
            $(icon).css('display', 'block');
        });
        $(this).on('mouseout', function() {
            $(icon).css('display', 'none');
        });
        $(icon).off('click');
        $(icon).on('click', function(ev) {
            ev.preventDefault();
            ev.stopPropagation();
            $.ajax({
                url: 'deletePhoto.php',
                method: 'post',
                data: data,
                success: function() {
                    let x = 1;
                },
                error: function(jqXHR) {
                    var newDoc = document.open();
                    newDoc.write(jqXHR.responseText);
                    newDoc.close();
                }
            });
            $(icon).remove();
            $img.remove();
            positionIcons();
            return;
        });
    });
    return;
}

/**
 * The following code sets up the drag-and-drop area, and establishes
 * classes for CSS
 */
// test browser's feature support
var isAdvancedUpload = 'FormData' in window && 'FileReader' in window;
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
        $('#ldg').css('display', 'inline');
        droppedFiles = e.originalEvent.dataTransfer.files;
        $.when( filechecks(droppedFiles) ).then(function() {
            $.when( ldImgs(validated) ).then(function() {
                $.when( ldNodes(FR_Images) ).then(function() {
                    resetImageLoads();
                });
            });
        });
    });
} else {
    alert("Dropping of images not supported for this browser.");
}

$('#file').change(function() {
    previewImgs(this.files);
});

/**
 * This is the function which consecutively calls routines to:
 *  - validate the files as jpg/jpeg (and other tests);
 *  - load validated image files into file reader objects;
 *  - resize the images and store them on the site;
 *  - display the images on the page.
 *
 * @param {FileList} flist 
 * @return {null}
 */
const previewImgs = (flist) => {
    $('#ldg').css('display', 'inline');
    $.when( filechecks(flist) ).then(function() {
        $.when( ldImgs(validated) ).then(function() {
            $.when( ldNodes(FR_Images) ).then(function() {
                resetImageLoads();
            });
        });
    });
    return;
}

/**
 * This function verifies that the magic number does in fact indicate jpeg file
 * It is called by the filechecks() function.
 * 
 * @param {object} magic  The magic number object in the file
 * @return {boolean} True/False for jpeg file type
 */
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
/**
 * This function accepts the hex string retrieved as the file's magic number
 * 
 * @param {string} signature renders the binaryFileType from the magic number
 * @return {string} magic number's mime type
 */
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
/**
 * This function validates features about the input file:
 *    1. Filename length < 1024 bytes
 *    2. File extension is jpg or jpeg
 *    3. File size is less than current acceptable upload limit (no longer needed)
 *    4. File magic numbers agree on file mime type
 * All files passing the above test are pushed in to the 'validated' array of files
 * 
 * @param {FileList} candidates 
 * @return {array} An array of deferred objects (promises) pending resolution
 */
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
        if (file.size >= MAX_UPLOAD_SIZE) {
            alert("This file is too large for upload - please resize it to less than 20Mbytes");
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

/**
 * This function takes the 'validated' array of files and loads them into
 * FileReader objects. FileReader objects are pushed onto the FR_Images array
 * 
 * @param {FileList} imgs The FileList from input selection or drag-and-drop
 * @return {array} An array of deferred objects (promises) pending resoultion
 */
const ldImgs = (imgs) => {
    // Begin image loading
    var promises = [];
    for(var i=0; i<imgs.length; i++) {
        var reader = new FileReader();
        var deferred = new $.Deferred();
        promises.push(deferred);
        (function(d, ifile) {
            reader.onload = function (evt) {
                var result = evt.target.result;
                /**
                 * There's no way to predict the order the files will be loaded
                 */
                var imgObj = {indx: imgNo++, fname: ifile.name, size: ifile.size, data: result};
                FR_Images.push(imgObj);  // used for loading DOM, then rese
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

/**
 * This function will convert the EXIF array lat/lng into single values;
 * Invoked by the ldNodes() function
 * 
 * @param {array} exifArray Obtained from exifReader.js
 * @return {number} or {null}
 */
const extractLatLng = (exifArray) => {
    if (exifArray.length !== 3) {
        return null;
    }
    let coord = exifArray[0] + (exifArray[1] + exifArray[2]/60)/60;
    return coord;
}
/**
 * This function converts a dataURI from a canvas element to a Blob, 
 * which can then be appended to a FormData object for ajax.
 * 
 * @param {dataURI} dataURI 
 * @return {Blob} Input argument processed as Blob
 */
function dataURItoBlob(dataURI) {
    // convert base64/URLEncoded data component to raw binary data held in a string
    var byteString;
    if (dataURI.split(',')[0].indexOf('base64') >= 0) {
        byteString = atob(dataURI.split(',')[1]);
    } else {
        byteString = unescape(dataURI.split(',')[1]);
    }
    // separate out the mime component
    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
    // write the bytes of the string to a typed array
    var ia = new Uint8Array(byteString.length);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    var bb = new Blob([ia], {"type": mimeString});
    return bb;
}
/**
 * This function takes the FileReader objects and reduces their 
 * corresponding image sizes to a z-size image, temporarily stored
 * until 'saved' by the user. After resizing and storing, the image
 * is placed on the page.
 * 
 * @param {array} fr_objs FileReader objects to be resized
 * @return {null}
 */
const ldNodes = (fr_objs) => {
    var noOfImgs = fr_objs.length;
    var promises = [];
    var imgs = [];
    for (var j=0; j<noOfImgs; j++) {
        // create image node:
        imgs[j] = document.createElement('img');
        // identify the index in the FileReader object for this file
        var imgid = fr_objs[j]['indx'];
        var picname = fr_objs[j]['fname'];
        var def = new $.Deferred();
        promises.push(def);

        (function(def, itemno, imgname, data){
            imgs[j].onload = function(e) {
                var usable = true;
                EXIF.getData(this, function() {
                    /**
                     * Exif data is extracted in order to supply information
                     * to the database. If no lat/lng, user is notified, but
                     * image is uploaded. If no height or width data, user is
                     * notified that image is not usable and can not be uploaded.
                     */
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
                        if (plng > 0) plng = -1 * plng;
                    } else {
                        var plng = null;
                        mappable = false;
                    }
                     if (typeof EXIF.getTag(this, "DateTimeOriginal") !== 'undefined') {
                        var pdate = EXIF.getTag(this, "DateTimeOriginal");
                    } else {
                        var pdate = null;
                    }
                    exifdat = {ehike: ehikeIndxNo, imght: origHt, imgwd: origWd,
                        fname: imgname, lat: plat, lng: plng, date: pdate};
                    if (usable) {
                        if (!mappable) {
                            alert( imgname + " has no location data - it can be uploaded,\n" +
                                "but cannot be attached to the hike map");
                        }
                    } else {
                        alert(imgname + " is unusable and cannot be uploaded");
                        $('#ldg').css('display', 'none');
                        return;
                    }
                    ajaxExif[itemno] = exifdat;
                });
                if (usable) {
                    // create a DOM element in which to place the image
                    var img = document.createElement("img");
                    img.src = data;
                    var canvas = document.createElement("canvas");
                    var ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0);
                    // establish dimensions based on landscape/portrait
                    var width = img.width;
                    var height = img.height;
                    if (width > height) {
                            height *= Z_WIDTH / width;
                            width = Z_WIDTH;
                    } else {
                            width *= Z_WIDTH / height;
                            height = Z_WIDTH;
                    }
                    canvas.width = width;
                    canvas.height = height;
                    var ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0, width, height);
                    // the resized image:
                    var dataurl = canvas.toDataURL('image/jpeg', 0.6);
                    var blob = dataURItoBlob(dataurl);
                    var formDat = new FormData();
                    formDat.append("file", blob);
                    formDat.append("fname", imgname);
                    var store_def = new $.Deferred();
                    $.ajax({
                        url: 'zstore.php',
                        method: 'post',
                        data: formDat,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(picinfo) {
                            ajaxExif[itemno].imgwd = picinfo[0];
                            ajaxExif[itemno].imght = picinfo[1];
                            let upld_src = picinfo[4] + picinfo[2];
                            ajaxExif[itemno].thumb = picinfo[3];
                            let src_id = 'img' + itemno;
                            let pgimg = '<div class="image-container">' +
                                '<img id="' + src_id + '" class="not-saved" src="'
                                    + upld_src + '" height="' + DISPLAY_HEIGHT +
                                    '" alt="image to upload" /><img id="del' + itemno
                                    + '" class="dels" src="../images/deleteIcon.png" />';
                                '</div>';
                            $('#image-row').append(pgimg);
                            let tsv_def = new $.Deferred();
                            updateTSV(ajaxExif[itemno], tsv_def);
                            $.when(tsv_def).then(function() {
                                def.resolve();
                            });
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            var newDoc = document.open();
                            newDoc.write(jqXHR.responseText);
                            newDoc.close();
                            def.reject();
                        }
                    });
                }
            }
        }(def, imgid, picname, fr_objs[j]['data']));
        imgs[j].src = fr_objs[j]['data'];
    }
    return $.when.apply($, promises);            
}

/**
 * This function ajaxes the TSV data and stores it in the table
 * 
 * @param {object} tsvdat Object containing all TSV data needed
 * @param {Deferred} def  Triggers resolution of a deferred in caller
 * @return {null}
 */
function updateTSV(tsvdat, def) {
    $.ajax({
        url: 'saveImage.php',
        method: 'post', 
        data: tsvdat,
        success: function() {
            def.resolve();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
        }
    });
    return;
}

/**
 * Form submission simply returns to the editor
 */
$('#save').on('click', function(ev) {
    ev.preventDefault();
    var user = $('#eusr').text();
    var newed = "editDB.php?hikeNo=" + ehikeIndxNo + "&usr=" + user + "&tab=2";
        window.open(newed, "_self");
});
