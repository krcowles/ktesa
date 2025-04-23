"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
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
var downloads = document.getElementById('dwnlds');
var previews = document.getElementById('previews');
var appMode = $('#appMode').text();
var ehike_stats = []; // global array holding photo Exif data
var ehikeNo = $('#ehike').text();
var droppedFiles = false;
// test browser's feature support
var isAdvancedUpload = 'FormData' in window && 'FileReader' in window;
var $div = $('#heic_upld');
var image;
var reader;
var base64data;
$('#preload').css({
    top: '420px',
    left: '360px',
    position: 'fixed'
});
var heic_cnt = 0;
var img_id = 0; // a unique id for image links
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
// The conversion, previews, and uploads (async => do one at a time);
function convertHeicToJpg(input) {
    return __awaiter(this, void 0, void 0, function () {
        var j, file, fname, mime, lastdot, ext, newname, preview_id, msg;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    $('#preload').css('display', 'block'); // turn on the 'image loading' gif
                    j = 0;
                    _a.label = 1;
                case 1:
                    if (!(j < input.length)) return [3 /*break*/, 7];
                    file = input[j];
                    fname = file.name;
                    mime = file.type;
                    // test the file attributes - only heic files allowed at this time
                    console.log("Mime for " + fname + ": " + mime);
                    if (!mime.startsWith("image/")) {
                        alert("Non-image file encountered: " + fname);
                        heic_cnt--;
                        return [3 /*break*/, 6];
                    }
                    if (fname.length > 1024) {
                        alert("Please rename this file such that the name is\n" +
                            "less than 1024 characters (including file extension\n" +
                            "This file will not be displayed...");
                        heic_cnt--;
                        return [3 /*break*/, 6];
                    }
                    lastdot = fname.lastIndexOf('.');
                    if (!(lastdot !== -1)) return [3 /*break*/, 5];
                    ext = fname.slice(lastdot + 1);
                    if (!(ext.toLowerCase() === 'heic')) return [3 /*break*/, 3];
                    newname = fname.slice(0, lastdot) + ".jpg";
                    /**
                     * The .heic EXIF data is extracted prior to the conversion,
                     * as the current conversion tools do not save it to the jpg file.
                     * Because of this, EXIF data must be saved until needed, and
                     * is tied to the image number (img_id) for retrieval later.
                     * When the conversion is made, and before the jpg is uploaded,
                     * the EXIF data is located and added to the upload package.
                     */
                    getHeicExifData(newname, file, img_id);
                    preview_id = img_id++;
                    return [4 /*yield*/, heic2any({
                            blob: file,
                            toType: "image/jpeg",
                        }).then(function (resultBlob) {
                            if (!resultBlob.type.startsWith("image/")) {
                                alert("Not an image file");
                                heic_cnt--;
                                return 'Not image';
                            }
                            var cname = newname;
                            // extract key EXIF data to prepare for upload by retrieving
                            // from the stored array of exif objects, 'ehike_stats'.
                            var found = false;
                            var thislat = '0';
                            var thislng = '0';
                            var thisdte = '';
                            var ismappable = '0';
                            for (var k = 0; k < ehike_stats.length; k++) {
                                if (ehike_stats[k]['ino'] === preview_id) {
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
                                return 'No GPS Data';
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
                            var uploader = $.Deferred();
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
                                placePreview(url, cname, prev_ht, prev_wd, preview_id);
                                uploadImage(url, cname, ehikeNo, canv_ht, canv_wd, thislat, thislng, thisdte, ismappable, preview_id, uploader);
                            };
                            source_img.src = url;
                            var span = document.createElement("SPAN");
                            span.classList.add('dlinks');
                            var dwnld = $anchor.clone();
                            var dimage = dwnld[0];
                            dimage.id = 'dld' + preview_id;
                            dimage.href = url;
                            dimage.download = cname;
                            dimage.text = "Download " + cname;
                            dimage.style.display = 'inline-block';
                            span.appendChild(dimage);
                            downloads.appendChild(span);
                            return (uploader);
                        }).catch(function (x) {
                            console.log(x.code);
                            console.log(x.message);
                        })];
                case 2:
                    msg = _a.sent();
                    console.log(msg);
                    return [3 /*break*/, 4];
                case 3:
                    alert(fname + " does not have an heic extension ");
                    heic_cnt--;
                    return [3 /*break*/, 6];
                case 4: return [3 /*break*/, 6];
                case 5:
                    alert(fname + " does not have an heic extension");
                    heic_cnt--;
                    return [3 /*break*/, 6];
                case 6:
                    j++;
                    return [3 /*break*/, 1];
                case 7:
                    $('#preload').css('display', 'none');
                    return [2 /*return*/];
            }
        });
    });
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
        var upldr = $anchor.clone();
        var a = upldr[0];
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
function uploadImage(url, filename, hikeno, height, width, lat, lng, date, mappable, linkid, def) {
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
                var res_msg = 'Upload ' + linkid + ' done';
                def.resolve(res_msg);
            },
            error: function (_jqXHR, _textStatus, _errorThrown) {
                var msg = "heic_converter.js: attempting to save image " +
                    filename + " to saveImage.php";
                ajaxError(appMode, _jqXHR, _textStatus, msg);
            }
        });
    };
    canvas_img.src = url;
}
