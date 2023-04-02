"use strict";
/**
 * @fileoverview This routine will collect .heic files selected by the user, extract
 * their Exif data, convert the photos to jpeg, and upload the resized images to the
 * server's pictures directory. In that process, the ETSV table will be updated with
 * the image fields and extracted Exif data. The routine will also display a preview
 * version of the image on the page along with a link which will allow the user to
 * delete the upload if so desired. The user may then, when satisfied, return to an
 * updated editor's tab2 where, the newly uploaded images will be displayed.
 *
 * @author Ken Cowles
 * @version 1.0 First release finalized
 */
var preview_height = 120; // image height of the preview
var upld_width = 640; // standard z-size image width
var $anchor = $('#anchor'); // prototype for links
var ehike_stats = []; // global array holding photo Exif data
var ehikeNo = $('#ehike').text();
var droppedFiles = false;
// test browser's feature support
var isAdvancedUpload = 'FormData' in window && 'FileReader' in window;
var $div = $('#heic_upld');
var image;
var reader;
var base64data;
var previews = document.getElementById('previews');
$('#preload').css({
    top: '360px',
    left: '360px',
    position: 'fixed'
});
var heic_cnt = 0;
var img_id = 0; // a unique id for image links
var conv_names = [];
var link_ids = [];
/**
 * For drag and drop files
 */
if (isAdvancedUpload) {
    $div.on('drag dragstart dragend dragover dragenter dragleave drop', function (ev) {
        ev.preventDefault();
        ev.stopPropagation();
    })
        .on('dragover dragenter', function () {
        $div.addClass('is-dragover');
    })
        .on('dragleave dragend drop', function () {
        $div.removeClass('is-dragover');
    })
        .on('drop', function (e) {
        $('#ldg').css('display', 'inline');
        var dfs = e.originalEvent;
        var dxfr = dfs.dataTransfer;
        droppedFiles = dxfr.files;
        heic_cnt += droppedFiles.length;
        convertHeicToJpg(droppedFiles);
    });
}
else {
    alert("Dropping of images not supported for this browser.");
}
/**
 * For input files using input browse function
 */
$('#file').on('change', function () {
    var file_input = this;
    var heics = file_input.files;
    heic_cnt += heics.length;
    if (heics.length === 0) {
        alert("No files selected");
        return false;
    }
    else {
        convertHeicToJpg(heics);
        return;
    }
});
// The conversion, previews, and uploads
function convertHeicToJpg(input) {
    $('#preload').css('display', 'block'); // turn on the 'image loading' gif
    var _loop_1 = function (j) {
        file = input[j];
        fname = file.name;
        mime = file.type;
        console.log("Mime for " + fname + ": " + mime);
        if (!mime.startsWith("image/")) {
            alert("Non-image file encountered: " + fname);
            heic_cnt--;
            return "continue";
        }
        if (fname.length > 1024) {
            alert("Please rename this file such that the name is\n" +
                "less than 1024 characters (including file extension\n" +
                "This file will not be displayed...");
            heic_cnt--;
            return "continue";
        }
        // test the file extension - only heic files allowed at this time
        lastdot = fname.lastIndexOf('.');
        if (lastdot !== -1) {
            ext = fname.slice(lastdot + 1);
            newname = fname.slice(0, lastdot) + ".jpg";
            conv_names.push(newname);
            if (ext.toLowerCase() === 'heic') {
                /**
                 * The .heic EXIF data is extracted prior to the conversion,
                 * as the current conversion tools do not save it to the jpg file.
                 * Because of this, that data must be saved until needed, and
                 * is tied to the image number (img_id) for retrieval later.
                 * When the conversion is made, and before the jpg is uploaded,
                 * the EXIF data is located and added to the upload package.
                 */
                getHeicExifData(conv_names[j], file, img_id);
                preview_id = img_id++;
                link_ids.push(preview_id);
                blob = file;
                heic2any({
                    blob: blob,
                    toType: "image/jpeg"
                })
                    .then(function (resultBlob) {
                    if (!resultBlob.type.startsWith("image/")) {
                        alert("Not an image file");
                        heic_cnt--;
                        return false;
                    }
                    var cname = conv_names[j];
                    var linkid = link_ids[j];
                    // extract key EXIF data to prepare for upload by retrieving
                    // from the stored array of exif objects, 'ehike_stats'.
                    var found = false;
                    var thislat = '0';
                    var thislng = '0';
                    var thisdte = '';
                    var ismappable = '0';
                    for (var k = 0; k < ehike_stats.length; k++) {
                        if (ehike_stats[k]['ino'] === linkid) {
                            thislat = ehike_stats[k]['lat'];
                            thislng = ehike_stats[k]['lng'];
                            thisdte = ehike_stats[k]['date'];
                            ismappable = ehike_stats[k]['mappable'];
                            found = true;
                        }
                        if (found) {
                            break;
                        }
                    }
                    if (!found) {
                        alert("Could not find associated GPS data...");
                        heic_cnt--;
                        return false;
                    }
                    var img_wd = 0;
                    var img_ht = 0;
                    var scale = 0;
                    var prev_ht = 0;
                    var prev_wd = 0;
                    var canv_ht = 0;
                    var canv_wd = 0;
                    // prepare image data: get width/height from loaded image 
                    var url = URL.createObjectURL(resultBlob);
                    var source_img = document.createElement("img");
                    source_img.onload = function () {
                        img_wd = source_img.naturalWidth;
                        img_ht = source_img.naturalHeight;
                        scale = img_wd / img_ht;
                        if (scale !== 0 && scale > 1) { // horizontal photo
                            prev_ht = preview_height;
                            prev_wd = Math.floor(prev_ht * scale);
                            canv_wd = upld_width;
                            canv_ht = Math.floor(canv_wd / scale);
                        }
                        else { // vertical photo
                            prev_wd = preview_height;
                            prev_ht = Math.floor(prev_wd / scale);
                            canv_ht = upld_width;
                            canv_wd = Math.floor(canv_ht * scale);
                        }
                        placePreview(url, cname, prev_ht, prev_wd, linkid);
                        uploadImage(url, cname, ehikeNo, canv_ht, canv_wd, thislat, thislng, thisdte, ismappable, linkid);
                    };
                    source_img.src = url;
                    return;
                })["catch"](function (x) {
                    console.log(x.code);
                    console.log(x.message);
                });
            }
            else {
                alert("This does not appear to be an heic file");
                heic_cnt--;
                return "continue";
            }
        }
        else {
            alert("No file extension found");
            heic_cnt--;
            return "continue";
        }
    };
    var file, fname, mime, lastdot, ext, newname, preview_id, blob;
    for (var j = 0; j < input.length; j++) {
        _loop_1(j);
    }
    var ld_check = setInterval(function () {
        var $items = $('.converted').length;
        if ($items === heic_cnt) {
            $('#preload').css('display', 'none');
            alert("Items uploaded");
            clearInterval(ld_check);
        }
    }, 400);
    return;
}
/**
 * HEIC EXIF Data is extracted prior to jpg conversion, and occurs
 * quickly. For this reason, the key EXIF attributes for the image
 * are stored in a window object for retrieval by the uploader later.
 */
function getHeicExifData(filename, photo, imgno) {
    var reader = new FileReader();
    reader.onload = function () {
        var exif = reader.result;
        var tags = findEXIFinHEIC(exif);
        // tags to save for uploader:
        var lat = '0';
        var lng = '0';
        var date = '';
        var mappable = '1';
        // temp calculation vars
        var deg;
        var min;
        var sec;
        // get EXIF parms
        var exifdate = tags["DateTimeOriginal"];
        if (exifdate !== undefined) {
            date = tags["DateTimeOriginal"];
        }
        var latitudeComponents = tags["GPSLatitude"];
        var latitudeRef = tags["GPSLatitudeRef"];
        if (latitudeComponents === undefined || latitudeRef === undefined) {
            mappable = '0';
        }
        var longitudeComponents = tags["GPSLongitude"];
        var longitudeRef = tags["GPSLongitudeRef"];
        if (longitudeComponents === undefined || longitudeRef === undefined) {
            mappable = '0';
        }
        var uploader_data = { ehike: ehikeNo, fname: filename };
        // type to LatlngObject
        var latdat = latitudeComponents;
        deg = Math.floor(latdat[0]['numerator'] / latdat[0]['denominator']);
        min = latdat[1]['numerator'] / latdat[1]['denominator'];
        sec = (latdat[2]['numerator'] / latdat[2]['denominator']) / 60;
        min = (min + sec) / 60;
        // always < 1; gives leading 0...
        var fractional = min.toFixed(7).split(".");
        lat = deg + "." + fractional[1];
        lat = latitudeRef === 'N' ? lat : "-" + lat;
        uploader_data.lat = lat;
        var lngdat = longitudeComponents;
        deg = Math.floor(lngdat[0]['numerator'] / lngdat[0]['denominator']);
        min = lngdat[1]['numerator'] / lngdat[1]['denominator'];
        sec = (lngdat[2]['numerator'] / lngdat[2]['denominator']) / 60;
        min = (min + sec) / 60;
        fractional = min.toFixed(7).split(".");
        lng = deg + "." + fractional[1];
        lng = longitudeRef === 'W' ? "-" + lng : lng;
        uploader_data.lng = lng;
        uploader_data.date = date;
        uploader_data.mappable = mappable;
        uploader_data.ino = imgno;
        ehike_stats.push(uploader_data);
    };
    reader.readAsArrayBuffer(photo);
}
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
$('#tab2').on('click', function () {
    var editpg = 'editDB.php?tab=2&hikeNo=' + ehikeNo;
    window.open(editpg, "_self");
});
/**
 * This function will add a preview image to the page
 */
function placePreview(dataUrl, filename, height, width, linkid) {
    var preview_img = document.createElement("img");
    preview_img.classList.add("converted");
    preview_img.height = height;
    preview_img.width = width;
    preview_img.onload = function () {
        //URL.revokeObjectURL(preview_img.src); 
        var picdiv = document.createElement('div');
        var info = document.createElement("span");
        info.classList.add('newnames');
        var dwnldr = $anchor.clone();
        var a = dwnldr[0];
        a.id = 'img' + linkid;
        a.href = "#"; // will be replaced by image thumb value after upload
        a.text = "Delete upload: " + filename;
        a.style.display = 'inline-block';
        info.appendChild(a);
        picdiv.appendChild(preview_img);
        picdiv.appendChild(info);
        previews.appendChild(picdiv);
    };
    preview_img.src = dataUrl;
}
/**
 * This function uploads the image to the server's pictures directory
 */
function uploadImage(url, filename, hikeno, height, width, lat, lng, date, mappable, linkid) {
    var canvas_img = document.createElement("img");
    canvas_img.onload = function () {
        var canvas = document.createElement("canvas");
        canvas.width = width;
        canvas.height = height;
        var ajaxwd = width.toString();
        var ajaxht = height.toString();
        var ctx = canvas.getContext("2d");
        ctx.drawImage(canvas_img, 0, 0, width, height);
        var dataurl = canvas.toDataURL('image/jpeg', 0.75);
        var blob = dataURItoBlob(dataurl); // local function
        var thumb = '';
        // Upload this image to the server's 'pictures' directory
        var formDat = new FormData();
        formDat.append("file", blob);
        formDat.append("ehike", hikeno);
        formDat.append("fname", filename);
        formDat.append("imght", ajaxht);
        formDat.append("imgwd", ajaxwd);
        formDat.append("lat", lat);
        formDat.append("lng", lng);
        formDat.append("date", date);
        formDat.append("mappable", mappable);
        formDat.append("page", 'converter');
        var del_id = '#img' + linkid;
        $.ajax({
            url: 'saveImage.php',
            method: 'post',
            data: formDat,
            processData: false,
            contentType: false,
            success: function (parms) {
                if (parms.indexOf('NO') !== -1) {
                    alert(filename + " has no location data - " +
                        "it was uploaded,\nbut cannot be attached " +
                        "to the hike map");
                }
                thumb = parms.replace("YES.", "");
                $(del_id).attr('href', thumb);
                $(del_id).on('click', function (ev) {
                    ev.preventDefault();
                    $.get('del_converted.php?thumb=' + thumb, function (result) {
                        if (result === 'DONE') {
                            alert("Uploaded photo deleted");
                        }
                        else {
                            alert("Could not remove uploaded photo");
                        }
                    });
                    $(this).parent().parent().remove();
                });
            },
            error: function (jqXHR) {
                var newDoc = document.open();
                newDoc.write(jqXHR.responseText);
                newDoc.close();
            }
        });
    };
    canvas_img.src = url;
}
