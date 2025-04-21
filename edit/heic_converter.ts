interface HeicPhoto {
    blob: File;
    toType: String;
}
interface EXIF_Tag {
    [key: string]: number | string | LatLngObject[] | undefined;
}
interface LatLngObject {
    numerator: number;
    denominator: number;
}
/**
 * 
 * REMOVE WHEN PLACING THIS FILE IN tsconfig.json
 */
interface ExifData {
    ehike: string;
    fname: string;
    lat: string;
    lng: string;
    date: string;
    mappable?: string;  // EXIF data exists for lat/lng? heic converter
    ino?: number;       // heic converter's assigned (unique) image number
}
// -------------
declare function heic2any(photo: HeicPhoto): Promise<Blob>;
declare function findEXIFinHEIC(exif_data: ArrayBuffer): EXIF_Tag;
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
const preview_height = 120; // image height of the preview
const upld_width = 640;     // standard z-size image width
const $anchor = $('#anchor') as JQuery<HTMLAnchorElement>; // prototype for links
const downloads = document.getElementById('dwnlds') as HTMLElement;
const previews = document.getElementById('previews') as HTMLElement;
var appMode = $('#appMode').text() as string;
var ehike_stats = [] as ExifData[]; // global array holding photo Exif data
var ehikeNo = $('#ehike').text();
var droppedFiles: boolean | FileList = false; 
// test browser's feature support
var isAdvancedUpload = 'FormData' in window && 'FileReader' in window;
var $div = $('#heic_upld');
var image: HTMLImageElement;
var reader: FileReader;
var base64data: string | ArrayBuffer | null;
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
    $div.on('drag dragstart dragend dragover dragenter dragleave drop', function(ev) {
        ev.preventDefault();
        ev.stopPropagation();
    })
    .on('dragover dragenter', function() {
        $div.addClass('is-dragover');
    })
    .on('dragleave dragend drop', function() {
        $div.removeClass('is-dragover');
    })
    .on('drop', function(e: JQuery.DropEvent) {
        $('#ldg').css('display', 'inline');
        let dfs = <DragEvent>e.originalEvent;
        let dxfr = <DataTransfer>dfs.dataTransfer;
        droppedFiles = dxfr.files;
        heic_cnt += droppedFiles.length;
        convertHeicToJpg(droppedFiles);
    });
} else {
    alert("Dropping of images not supported for this browser.");
}

/**
 * For input files using input browse function
 */
$('#file').on('change', function() {
    let file_input = <HTMLInputElement>this;
    var heics = <FileList>file_input.files
    heic_cnt += heics.length;
    if (heics.length === 0) {
        alert("No files selected");
        return false;
    } else {
        convertHeicToJpg(heics);
        return
    }
});

// The conversion, previews, and uploads (async => do one at a time);
async function convertHeicToJpg(input: FileList) {
    $('#preload').css('display', 'block'); // turn on the 'image loading' gif
    for (let j=0; j<input.length; j++) {
        var file = input[j];
        var fname = file.name;
        var mime  = file.type;
        // test the file attributes - only heic files allowed at this time
        console.log("Mime for " + fname + ": " + mime);
        if (!mime.startsWith("image/")) {
            alert("Non-image file encountered: " + fname);
            heic_cnt--;
            continue;
        }
        if (fname.length > 1024) {
            alert("Please rename this file such that the name is\n" +
                "less than 1024 characters (including file extension\n" +
                "This file will not be displayed...");
                heic_cnt--;
            continue;
        }
        var lastdot = fname.lastIndexOf('.');
        if (lastdot !== -1) {
            var ext = fname.slice(lastdot+1);
            if (ext.toLowerCase() === 'heic') {
                // Proceed with heic conversion!
                var newname = fname.slice(0, lastdot) + ".jpg";
                /**
                 * The .heic EXIF data is extracted prior to the conversion,
                 * as the current conversion tools do not save it to the jpg file.
                 * Because of this, EXIF data must be saved until needed, and
                 * is tied to the image number (img_id) for retrieval later. 
                 * When the conversion is made, and before the jpg is uploaded,
                 * the EXIF data is located and added to the upload package.
                 */
                getHeicExifData(newname, file, img_id);
                var preview_id = img_id++;
                var msg = await heic2any({
                    blob: file,
                    toType: "image/jpeg",
                }).then(function(resultBlob: Blob) {
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
                    for (var k=0; k<ehike_stats.length; k++) {
                        if (ehike_stats[k]['ino'] === preview_id) {
                            thislat    = ehike_stats[k]['lat'] as string;
                            thislng    = ehike_stats[k]['lng'] as string;
                            thisdte    = ehike_stats[k]['date'] as string
                            ismappable = ehike_stats[k]['mappable'] as string;
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
                    var scale  = 0;
                    var prev_ht = 0;
                    var prev_wd = 0;
                    var canv_ht = 0;
                    var canv_wd = 0;
                    // prepare image data: get width/height from loaded image 
                    const url = URL.createObjectURL(resultBlob);
                    const source_img = document.createElement("img") as HTMLImageElement;
                    var uploader = $.Deferred();
                    source_img.onload = () => {
                        img_wd = source_img.naturalWidth;
                        img_ht = source_img.naturalHeight;
                        scale  = img_wd/img_ht;
                        if (scale !== 0 && scale > 1) { // horizontal photo
                            prev_ht = preview_height;
                            prev_wd = Math.floor(prev_ht * scale);
                            canv_wd = upld_width;
                            canv_ht = Math.floor(canv_wd/scale);
                        } else { // vertical photo
                            prev_wd = preview_height;
                            prev_ht = Math.floor(prev_wd/scale);
                            canv_ht = upld_width;
                            canv_wd = Math.floor(canv_ht * scale);
                        }
                        placePreview(url, cname, prev_ht, prev_wd, preview_id);
                        uploadImage(url, cname, ehikeNo, canv_ht, canv_wd, thislat,
                            thislng, thisdte, ismappable, preview_id, uploader);
                    }
                    source_img.src = url;
                    const span = document.createElement("SPAN");
                    span.classList.add('dlinks');
                    const dwnld = $anchor.clone();
                    const dimage = dwnld[0];
                    dimage.id = 'dld' + preview_id;
                    dimage.href = url;
                    dimage.download = cname
                    dimage.text = "Download " + cname;
                    dimage.style.display = 'inline-block';
                    span.appendChild(dimage);
                    downloads.appendChild(span);
                    return(uploader);
                }).catch (function (x) {
                    console.log(x.code);
                    console.log(x.message);
                });
                console.log(msg);
            } else {
                alert(fname + " does not have an heic extension ");
                heic_cnt--;
                continue;
            }
        } else {
            alert(fname + " does not have an heic extension");
            heic_cnt--;
            continue;
        }
    }
    $('#preload').css('display', 'none');
}
/**
 * HEIC EXIF Data is extracted prior to jpg conversion, and occurs
 * quickly. For this reason, the key EXIF attributes for the image
 * are stored in a window object for retrieval by the uploader later.
 */
function getHeicExifData(filename: string, photo: File, imgno: number) {
    let reader = new FileReader();

    reader.onload = function ()
    {
        var exif = reader.result as ArrayBuffer;
        var tags = findEXIFinHEIC(exif);
        // tags to save for uploader:
        var lat = '0';
        var lng = '0';
        var date = '';
        var mappable = '1';
        // temp calculation vars
        var deg: number;
        var min: number;
        var sec: number;
        // get EXIF parms
        var exifdate = tags["DateTimeOriginal"];
        if (exifdate !== undefined) {
            date = tags["DateTimeOriginal"] as string;
        }
        var latitudeComponents = tags["GPSLatitude"] as LatLngObject[] | undefined;
        var latitudeRef = tags["GPSLatitudeRef"];
        if (latitudeComponents === undefined || latitudeRef === undefined) {
            mappable = '0';
        }
        var longitudeComponents = tags["GPSLongitude"] as LatLngObject[] | undefined;
        var longitudeRef = tags["GPSLongitudeRef"];
        if (longitudeComponents === undefined || longitudeRef === undefined) {
            mappable = '0';
        }
        var uploader_data = {ehike: ehikeNo, fname: filename} as ExifData;        
        // type to LatlngObject
        var latdat = latitudeComponents as LatLngObject[];
        deg = Math.floor(latdat[0]['numerator'] / latdat[0]['denominator']);
        min = latdat[1]['numerator'] / latdat[1]['denominator'];
        sec = (latdat[2]['numerator'] / latdat[2]['denominator'])/60;
        min = (min + sec)/60;
        // always < 1; gives leading 0...
        var fractional = min.toFixed(7).split(".");
        lat = deg + "." + fractional[1];
        lat = latitudeRef === 'N' ? lat : "-" + lat;
        uploader_data.lat = lat;
        var lngdat = longitudeComponents as LatLngObject[];
        deg = Math.floor(lngdat[0]['numerator'] / lngdat[0]['denominator']);
        min = lngdat[1]['numerator'] / lngdat[1]['denominator'];
        sec = (lngdat[2]['numerator'] / lngdat[2]['denominator'])/60;
        min = (min + sec)/60;
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
function dataURItoBlob(dataURI: string) {
    // convert base64/URLEncoded data component to raw binary data held in a string
    var byteString;
    if (dataURI.split(',')[0].indexOf('base64') >= 0) {
        byteString = atob(dataURI.split(',')[1]);
    } else {
        byteString = decodeURIComponent(dataURI.split(',')[1]);
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
$('#tab2').on('click', function() {
    var editpg = 'editDB.php?tab=2&hikeNo=' + ehikeNo;
    window.open(editpg, "_self");
});
/**
 * This function will add a preview image to the page
 */
function placePreview(
    dataUrl: string, filename: string, height: number, width: number, linkid: number
) {
    const preview_img = document.createElement("img");
    preview_img.classList.add("converted");
    preview_img.height = height;
    preview_img.width = width;
    preview_img.onload = () => {
        //URL.revokeObjectURL(preview_img.src); 
        const picdiv = document.createElement('div');
        const info = document.createElement("span");
        info.classList.add('newnames');
        const upldr = $anchor.clone();
        const a = upldr[0];
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
function uploadImage(
    url: string, filename: string, hikeno: string, height: number, width: number,
    lat: string, lng: string, date: string, mappable: string, linkid: number,
    def: JQuery.Deferred<any, any, any>
) {
    const canvas_img = document.createElement("img") as HTMLImageElement;
    canvas_img.onload = () => {
        var canvas = document.createElement("canvas");
        canvas.width = width;
        canvas.height = height;
        var ajaxwd = width.toString();
        var ajaxht = height.toString();
        var ctx = <CanvasRenderingContext2D>canvas.getContext("2d");
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
            success: function(parms) {
                if (parms.indexOf('NO') !== -1) {
                    alert(filename + " has no location data - " +
                        "it was uploaded,\nbut cannot be attached " +
                        "to the hike map");
                }
                thumb = parms.replace("YES.", "");
                $(del_id).attr('href', thumb);
                $(del_id).on('click', function(ev) {
                    ev.preventDefault();
                    $.get('del_converted.php?thumb=' + thumb, function(result) {
                        if (result === 'DONE') {
                            alert("Uploaded photo deleted");
                        } else {
                            alert("Could not remove uploaded photo");
                        }    
                    });
                    $(this).parent().parent().remove();
                });
                var res_msg = 'Upload ' + linkid + ' done';
                def.resolve(res_msg);
            },
            error: function(_jqXHR, _textStatus, _errorThrown) {
                let msg = "heic_converter.js: attempting to save image " +
                    filename + " to saveImage.php";
                ajaxError(appMode, _jqXHR, _textStatus, msg);
            }
        });
    }
    canvas_img.src = url; 
}
