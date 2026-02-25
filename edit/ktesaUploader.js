"use strict";
/**
 * @fileoverview This is a standalone utility that will preview selected
 * images (input select or drag-and-drop) on the page, having stored a
 * resized image on the server. They will be added to the editor photo gallery.
 *
 * @author Ken Cowles
 * @version 2.0 Typescripted
 * @version 2.1 Updated per heic_convert.ts
 * @version 3.0 Added upload for webp photos and deployed new exif reader
 */
// admin and server defined constants
const MAX_UPLOAD_SIZE = 20000000; // no longer required
const Z_WIDTH = 640;
// globals
var appMode = $('#appMode').text();
var ehikeIndxNo = $('#ehno').text(); // get the associated hike no
var droppedFiles = false;
var validated = [];
var FR_Images = []; // FileReader objects
var imgNo = 0; // unique id for each validated image
var meta = [];
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
        meta = []; // needs to be reset after each upload
        let dfs = e.originalEvent;
        let dxfr = dfs.dataTransfer;
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
    let file_input = this;
    previewImgs(file_input.files);
});
/**
 * This is the function which consecutively calls routines to:
 *  - validate the files as jpg/jpeg (and other tests);
 *  - load validated image files into file reader objects;
 *  - resize the images and store them on the site;
 *  - display the images on the page.
 */
const previewImgs = (flist) => {
    $('#ldg').css('display', 'inline');
    $('#preload').css('display', 'inline-block');
    meta = []; // needs to be reset after each upload
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
const render = (magic) => {
    if (magic.binaryFileType !== 'image/jpeg' && magic.binaryFileType !== 'image/webp') {
        return false;
    }
    else {
        return true;
    }
};
/**
 * This function accepts the hex string retrieved as the file's magic number
 */
const getMimetype = (signature) => {
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
        case '52494646':
            return 'image/webp';
        default:
            return 'Unknown filetype';
    }
};
/**
 * This function validates features about the input file:
 *    1. Filename length < 1024 bytes
 *    2. File extension is jpg, jpeg, or webp
 *    3. File size is less than current acceptable upload limit (no longer needed)
 *    4. File magic numbers agree on file mime type
 * All files passing the above test are pushed in to the 'validated' array of files
 */
const filechecks = (candidates) => {
    var promises = [];
    for (let j = 0; j < candidates.length; j++) {
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
            var lc_ext = ext.toLowerCase();
            if (lc_ext === 'heic') {
                /**
                 * Need to convert to jpg, extract heic metadata
                 * and then import metadata to jpg
                 */
                alert(".heic files require conversion to .jpg before proceeding;\n" +
                    "Use converter button on this page for those files");
                continue;
            }
            if (lc_ext !== 'jpg' && lc_ext !== 'jpeg' && lc_ext !== 'webp') {
                alert('Type ".' + ext + '" (' + fname + ')' +
                    " is not supported at this time");
                continue;
            }
        }
        else {
            alert("Photo has malformed name (no extension)");
            continue;
        }
        if (file.size >= MAX_UPLOAD_SIZE) {
            alert("This file is too large for upload - please resize it to less than 20Mbytes");
            continue;
        }
        // check the internal magic numbers for type jpeg: IIFE
        (function (def, candidate) {
            filereader.onloadend = function (evt) {
                let event = evt.target;
                let load_result = event.result;
                if (event.readyState === FileReader.DONE) {
                    var uint = new Uint8Array(load_result);
                    let bytes = [];
                    uint.forEach((byte) => {
                        let thisbyte = byte.toString(16);
                        bytes.push(thisbyte);
                    });
                    var hex = bytes.join('').toUpperCase();
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
 * New approach with ExifReader: read any exifdata during ldImgs(), and store it
 * (along with image number) in an array for use in ldNodes()
 */
const extractMetaData = (tagData, ino) => {
    var use = true;
    var plat;
    var plng;
    var mappable = "1";
    var pdate;
    const gps_lat = tagData.gps?.Latitude;
    if (typeof gps_lat === 'undefined') {
        plat = '0';
        mappable = '0';
    }
    else {
        const tlat = tagData.gps?.Latitude;
        plat = tlat.toString();
    }
    const gps_lng = tagData.gps?.Longitude;
    if (typeof gps_lng === 'undefined') {
        plng = '0';
        mappable = '0';
    }
    else {
        const tlng = tagData.gps?.Longitude;
        plng = tlng.toString();
    }
    const dtime = tagData.exif?.DateTimeOriginal;
    if (typeof dtime === 'undefined') {
        pdate = '0';
    }
    else {
        const tdate = tagData.exif?.DateTimeOriginal;
        pdate = tdate.value[0];
    }
    const metaObj = { imgNo: ino, lat: plat, lng: plng, dtime: pdate,
        usable: use, mappable: mappable };
    meta.push(metaObj);
    return;
};
/**
 * This function takes the 'validated' array of files and loads them into
 * FileReader objects. FileReader objects are pushed onto the FR_Images array
 *
 */
const ldImgs = (imgs) => {
    // Begin image loading
    var promises = [];
    for (var i = 0; i < imgs.length; i++) {
        var reader = new FileReader();
        var deferred = $.Deferred();
        promises.push(deferred);
        (function (d, ifile) {
            reader.onload = async function (evt) {
                /**
                 * There's no way to predict the order the files will be loaded
                 */
                let event = evt.target;
                var result = event.result;
                // Some imgs have no usable image height/width EXIF tags, so:
                const newImg = document.createElement("img");
                newImg.src = result;
                var thisImg = imgNo; // before incrementing imgNo
                var imgObj = { indx: imgNo++, fname: ifile.name,
                    size: ifile.size, data: result };
                FR_Images.push(imgObj); // used for loading DOM, then reset
                var tags = await ExifReader.load(ifile, { expanded: true, includeUnknown: true });
                extractMetaData(tags, thisImg);
                d.resolve();
            };
            reader.onerror = function () {
                let item = reader.error;
                let msg = item.message;
                alert("Problem encountered: file cannot be displayed\n" + msg);
                d.resolve();
            };
        }(deferred, imgs[i]));
        reader.readAsDataURL(imgs[i]);
    }
    return $.when.apply($, promises); // return a variable set of promises
};
/**
 * This function converts a dataURI from a canvas element to a Blob,
 * which can then be appended to a FormData object for ajax.
 */
function canvasDataURItoBlob(dataURI) {
    // convert base64/URLEncoded data component to raw binary data held in a string
    var byteString;
    if (dataURI.split(',')[0].indexOf('base64') >= 0) {
        byteString = atob(dataURI.split(',')[1]);
    }
    else {
        byteString = decodeURIComponent(dataURI.split(',')[1]);
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
const ldNodes = (fr_objs) => {
    var noOfImgs = fr_objs.length;
    var promises = [];
    var imgs = [];
    window.loaded_imgs = 0; // track actual count, as .onload is asynchronous
    for (var j = 0; j < noOfImgs; j++) {
        // create image node:
        imgs[j] = document.createElement('img');
        var picname = fr_objs[j]['fname'];
        var pic_indx = fr_objs[j]['indx'];
        var def = $.Deferred();
        promises.push(def);
        (function (def, imgname, imgindx, data) {
            imgs[j].onload = function () {
                window.loaded_imgs++;
                const pic_data = meta[imgindx];
                if (pic_data.usable) {
                    window.exifdat = {
                        ehike: ehikeIndxNo, fname: imgname, lat: pic_data.lat,
                        lng: pic_data.lng, date: pic_data.dtime
                    };
                    // create a DOM element in which to place the image
                    var img = document.createElement("img");
                    img.src = data;
                    var canvas = document.createElement("canvas");
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
                    var dataurl = canvas.toDataURL('image/jpeg', 0.7);
                    var blob = canvasDataURItoBlob(dataurl); // local function
                    // prepare ajax data
                    var ajxwd = width.toString();
                    var ajxht = height.toString();
                    var formDat = new FormData();
                    formDat.append("file", blob);
                    formDat.append("ehike", window.exifdat.ehike);
                    formDat.append("fname", window.exifdat.fname);
                    formDat.append("imght", ajxht);
                    formDat.append("imgwd", ajxwd);
                    formDat.append("lat", window.exifdat.lat);
                    formDat.append("lng", window.exifdat.lng);
                    formDat.append("date", window.exifdat.date);
                    formDat.append("mappable", pic_data.mappable);
                    $.ajax({
                        url: 'saveImage.php',
                        method: 'post',
                        data: formDat,
                        processData: false,
                        contentType: false,
                        success: function (mapping) {
                            if (mapping === 'NO') {
                                alert(imgname + " has no location data: " +
                                    "it was uploaded, but cannot be attached " +
                                    "to the hike map; You can add location later " +
                                    "by clicking on the LOC checkbox");
                            }
                            def.resolve();
                        },
                        error: function (_jqXHR, _textStatus, _errorThrown) {
                            def.reject();
                            let msg = "ktesaUploader.js: attempting to save " +
                                imgname + " via saveImage.php";
                            ajaxError(appMode, _jqXHR, _textStatus, msg);
                        }
                    });
                }
                else {
                    alert(imgname + " is unusable and cannot be uploaded");
                    def.resolve();
                }
            };
        }(def, picname, pic_indx, fr_objs[j]['data']));
        imgs[j].src = fr_objs[j]['data'];
    }
    return $.when.apply($, promises);
};
