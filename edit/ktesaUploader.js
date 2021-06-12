"use strict";
/**
 * @fileoverview This is a standalone utility that will preview selected
 * images (input select or drag-and-drop) on the page, having stored a
 * resized image on the server. They will be added to the editor photo gallery.
 *
 * @author Ken Cowles
 * @version 2.0 Typescripted
 */
// admin and server defined constants
var MAX_UPLOAD_SIZE = 20000000; // no longer required
var Z_WIDTH = 640;
var Z_HEIGHT = 480;
var DISPLAY_HEIGHT = 220;
// globals
var ehikeIndxNo = $('#ehno').text(); // get the associated hike no
var droppedFiles = false;
var validated = [];
var FR_Images = []; // FileReader objects
var imgNo = 0; // unique id for each validated image
window.loaded_imgs = 0;
window.exifdat = { ehike: '0', fname: '', lat: '', lng: '', date: '' };
/**
 * After every upload of one or more images, the editor is refreshed
 * with the following data
 */
var newed = "editDB.php?hikeNo=" + ehikeIndxNo + "&tab=2";
/**
 * The following code sets up the drag-and-drop area, and establishes
 * classes for CSS
 */
// test browser's feature support
var isAdvancedUpload = 'FormData' in window && 'FileReader' in window;
var $form = $('#pupld');
if (isAdvancedUpload) {
    $form.on('drag dragstart dragend dragover dragenter dragleave drop', function (ev) {
        ev.preventDefault();
        ev.stopPropagation();
    })
        .on('dragover dragenter', function () {
        $form.addClass('is-dragover');
    })
        .on('dragleave dragend drop', function () {
        $form.removeClass('is-dragover');
    })
        .on('drop', function (e) {
        $('#ldg').css('display', 'inline');
        $('#preload').css('display', 'inline-block');
        var dfs = e.originalEvent;
        var dxfr = dfs.dataTransfer;
        droppedFiles = dxfr.files;
        $.when(filechecks(droppedFiles)).then(function () {
            $.when(ldImgs(validated)).then(function () {
                $.when(ldNodes(FR_Images)).then(function () {
                    window.open(newed, "_self");
                });
            });
        });
    });
}
else {
    alert("Dropping of images not supported for this browser.");
}
$('#file').on('change', function () {
    var file_input = this;
    previewImgs(file_input.files);
});
/**
 * This is the function which consecutively calls routines to:
 *  - validate the files as jpg/jpeg (and other tests);
 *  - load validated image files into file reader objects;
 *  - resize the images and store them on the site;
 *  - display the images on the page.
 */
var previewImgs = function (flist) {
    $('#ldg').css('display', 'inline');
    $('#preload').css('display', 'inline-block');
    $.when(filechecks(flist)).then(function () {
        $.when(ldImgs(validated)).then(function () {
            $.when(ldNodes(FR_Images)).then(function () {
                window.open(newed, "_self");
            });
        });
    });
    return;
};
/**
 * This function verifies that the magic number does in fact indicate jpeg file
 * It is called by the filechecks() function.
 */
var render = function (magic) {
    if (magic.binaryFileType !== 'image/jpeg') {
        return false;
        /*
        alert(magic.filename + "\nFiletype from file object: " + magic.filetype +
            "\nFiletype from binary: " + magic.binaryFileType + "\nHex: " +
            magic.hex);
        */
    }
    else {
        return true;
    }
};
/**
 * This function accepts the hex string retrieved as the file's magic number
 */
var getMimetype = function (signature) {
    switch (signature) {
        case '89504E47':
            return 'image/png';
        case '47494638':
            return 'image/gif';
        case '25504446':
            return 'application/pdf';
        case 'FFD8FFDB':
        case 'FFD8FFE0':
        case 'FFD8FFE1':
            return 'image/jpeg';
        case '504B0304':
            return 'application/zip';
        default:
            return 'Unknown filetype';
    }
};
/**
 * This function validates features about the input file:
 *    1. Filename length < 1024 bytes
 *    2. File extension is jpg or jpeg
 *    3. File size is less than current acceptable upload limit (no longer needed)
 *    4. File magic numbers agree on file mime type
 * All files passing the above test are pushed in to the 'validated' array of files
 */
var filechecks = function (candidates) {
    var promises = [];
    for (var j = 0; j < candidates.length; j++) {
        var filereader = new FileReader();
        var deferred = $.Deferred();
        promises.push(deferred);
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
            var ext = fname.slice(lastdot + 1);
            if (ext.toLowerCase() !== 'jpg' && ext.toLowerCase() !== 'jpeg') {
                alert('Type ".' + ext + '" (' + fname + ')' +
                    " is not supported at this time");
                continue;
            }
        }
        if (file.size >= MAX_UPLOAD_SIZE) {
            alert("This file is too large for upload - please resize it to less than 20Mbytes");
            continue;
        }
        // check the internal magic numbers for type jpeg
        (function (def, candidate) {
            filereader.onloadend = function (evt) {
                var event = evt.target;
                var load_result = event.result;
                if (event.readyState === FileReader.DONE) {
                    var uint = new Uint8Array(load_result);
                    var bytes_1 = [];
                    uint.forEach(function (byte) {
                        var thisbyte = byte.toString(16);
                        bytes_1.push(thisbyte);
                    });
                    var hex = bytes_1.join('').toUpperCase();
                    var magic = {
                        filename: file.name,
                        filetype: file.type ? file.type : 'Unknown/Extension missing',
                        binaryFileType: getMimetype(hex),
                        hex: hex
                    };
                    if (render(magic)) {
                        validated.push(candidate);
                    }
                    else {
                        alert(file.name + " is corrupt and cannot be displayed");
                    }
                    def.resolve();
                }
            };
            var blob = file.slice(0, 4);
            filereader.readAsArrayBuffer(blob);
        }(deferred, file));
    }
    return $.when.apply($, promises); // return a variable set of promises
};
/**
 * This function takes the 'validated' array of files and loads them into
 * FileReader objects. FileReader objects are pushed onto the FR_Images array
 *
 */
var ldImgs = function (imgs) {
    // Begin image loading
    var promises = [];
    for (var i = 0; i < imgs.length; i++) {
        var reader = new FileReader();
        var deferred = $.Deferred();
        promises.push(deferred);
        (function (d, ifile) {
            reader.onload = function (evt) {
                var event = evt.target;
                var result = event.result;
                /**
                 * There's no way to predict the order the files will be loaded
                 */
                var imgObj = { indx: imgNo++, fname: ifile.name,
                    size: ifile.size, data: result };
                FR_Images.push(imgObj); // used for loading DOM, then rese
                d.resolve();
            };
            reader.onerror = function () {
                var item = reader.error;
                var msg = item.message;
                alert("Problem encountered: file cannot be displayed\n" + msg);
                d.resolve();
            };
        }(deferred, imgs[i]));
        reader.readAsDataURL(imgs[i]);
    }
    return $.when.apply($, promises); // return a variable set of promises
};
/**
 * This function will convert the EXIF array lat/lng into single values;
 * Invoked by the ldNodes() function: exif.js is a 3rd party lib, so using 'any[]'
 */
var extractLatLng = function (exifArray) {
    if (exifArray.length !== 3) {
        return null;
    }
    var coord = exifArray[0] + (exifArray[1] + exifArray[2] / 60) / 60;
    return coord;
};
/**
 * This function converts a dataURI from a canvas element to a Blob,
 * which can then be appended to a FormData object for ajax.
 */
function dataURItoBlob(dataURI) {
    // convert base64/URLEncoded data component to raw binary data held in a string
    var byteString;
    if (dataURI.split(',')[0].indexOf('base64') >= 0) {
        byteString = atob(dataURI.split(',')[1]);
    }
    else {
        byteString = unescape(dataURI.split(',')[1]);
    }
    // separate out the mime component
    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
    // write the bytes of the string to a typed array
    var ia = new Uint8Array(byteString.length);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    var bb = new Blob([ia], { "type": mimeString });
    return bb;
}
/**
 * This function takes the FileReader objects and reduces their
 * corresponding image sizes to a z-size image, temporarily stored
 * until 'saved' by the user. After resizing and storing, the image
 * is placed on the page.
 */
var ldNodes = function (fr_objs) {
    var noOfImgs = fr_objs.length;
    var promises = [];
    var imgs = [];
    window.loaded_imgs = 0; // track actual count, as .onload is asynchronous
    for (var j = 0; j < noOfImgs; j++) {
        // create image node:
        imgs[j] = document.createElement('img');
        // identify the index in the FileReader object for this file
        //var imgid = fr_objs[j]['indx'];
        var picname = fr_objs[j]['fname'];
        var def = $.Deferred();
        promises.push(def);
        (function (def, imgname, data) {
            imgs[j].onload = function () {
                // establish init values solely to appease typescript...
                //var ajxexif: ExifData = {ehike: '0', fname: '', lat: '0', lng: '0', date: ''};
                var item = this;
                window.loaded_imgs++;
                var usable = true;
                var mappable = '1';
                EXIF.getData(item, function () {
                    /**
                     * Exif data is extracted in order to supply information
                     * to the database. If no lat/lng, user is notified, but
                     * image is uploaded. If no height or width data, user is
                     * notified that image is not usable and can not be uploaded.
                     *
                     * NOTE: If you try to pass a null via ajax, it will be seen
                     * as a STRING "null"! Hence, no lat/lng/date's = 0;
                     */
                    var exifht = typeof EXIF.getTag(item, 'PixelYDimension');
                    if (exifht === 'undefined') {
                        usable = false;
                    }
                    var exifwd = typeof EXIF.getTag(item, 'PixelXDimension');
                    if (exifwd === 'undefined') {
                        usable = false;
                    }
                    var plat;
                    var plng;
                    var pdate;
                    if (typeof EXIF.getTag(item, "GPSLatitude") !== 'undefined') {
                        plat = extractLatLng(EXIF.getTag(item, "GPSLatitude"));
                    }
                    else {
                        plat = '0';
                        mappable = '0';
                    }
                    if (typeof EXIF.getTag(item, "GPSLongitude") !== 'undefined') {
                        var exiflng = extractLatLng(EXIF.getTag(item, "GPSLongitude"));
                        if (exiflng > 0)
                            exiflng = -1 * exiflng;
                        plng = exiflng.toString();
                    }
                    else {
                        plng = '0';
                        mappable = '0';
                    }
                    if (typeof EXIF.getTag(item, "DateTimeOriginal") !== 'undefined') {
                        pdate = EXIF.getTag(item, "DateTimeOriginal");
                    }
                    else {
                        pdate = '0';
                    }
                    if (usable) {
                        var ajxlat = plat.toString();
                        var ajxlng = plng.toString();
                        window.exifdat = { ehike: ehikeIndxNo, fname: imgname,
                            lat: ajxlat, lng: ajxlng, date: pdate };
                    }
                });
                //exifdat = ajxexif;
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
                    }
                    else {
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
                    // prepare ajax data
                    var ajxwd = width.toString();
                    var ajxht = height.toString();
                    var formDat = new FormData();
                    formDat.append("file", blob);
                    formDat.append("fname", imgname);
                    formDat.append("ehike", window.exifdat.ehike);
                    formDat.append("fname", window.exifdat.fname);
                    formDat.append("imght", ajxht);
                    formDat.append("imgwd", ajxwd);
                    formDat.append("lat", window.exifdat.lat);
                    formDat.append("lng", window.exifdat.lng);
                    formDat.append("date", window.exifdat.date);
                    formDat.append("mappable", mappable);
                    $.ajax({
                        url: 'saveImage.php',
                        method: 'post',
                        data: formDat,
                        processData: false,
                        contentType: false,
                        success: function (mapping) {
                            if (mapping === 'NO') {
                                alert(imgname + " has no location data - " +
                                    "it was uploaded,\nbut cannot be attached " +
                                    "to the hike map");
                            }
                            def.resolve();
                        },
                        error: function (jqXHR) {
                            var newDoc = document.open();
                            newDoc.write(jqXHR.responseText);
                            newDoc.close();
                            def.reject();
                        }
                    });
                }
                else {
                    alert(imgname + " is unusable and cannot be uploaded");
                    def.resolve();
                }
            };
        }(def, picname, fr_objs[j]['data']));
        imgs[j].src = fr_objs[j]['data'];
    }
    return $.when.apply($, promises);
};
