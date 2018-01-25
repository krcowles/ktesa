<?php
/**
 * This file contains function declarations designed to be used
 * by modules in the build directory.
 * 
 * @package Build_Functions
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
/**
 * This function validates the upload file and then moves the temporary
 * copy of the file stored in the server, to the site directory. In the
 * process, it calls the function uploadErr() if an error occurs during
 * upload, and dupFileName() if it finds that a file of the same name
 * already exists on the site. Other validation checks are performed.
 * NOTE: If the upload is a main site gpx file, makeTrackFile will be called.
 * 
 * @param string $name     <input type="file" name="$name" />
 * @param string $fileloc  The directory location for moving the upload
 * @param string $mimetype The mime type to validate on upload
 * 
 * @return array $filename, $msg
 */
function validateUpload($name, $fileloc, $mimetype)
{
    $msg = '';
    $filename = basename($_FILES[$name]['name']);
    if ($filename !== '') {
        $tmp_upload = $_FILES[$name]['tmp_name'];    
        $filetype = $_FILES[$name]['type'];
        $filestat = $_FILES[$name]['error'];
        if ($filestat !== UPLOAD_ERR_OK) {
            $badupld = "Failed to upload {$name}: " . uploadErr($filestat);
            die($badupld);
        }
        if (substr_count($filename, ".") !== 1) {
            $odd = "This file may be corrupted. Please correct the " .
                "file format and re-submit, or contact Site Master.";
            die($odd);
        }
        if ($mimetype !== 'nocheck') {
            if (preg_match($mimetype, $filetype) === 0) {
                $badmime = $filetype . ": should be '{$mimetype}'";
                die($badmime);
            }
        }
        $saveloc = $fileloc . $filename;
        if (file_exists($saveloc)) {
            $dupdata = dupFileName($filename);
            $filename = $dupdata[0];
            $saveloc = $fileloc . $filename;
            $msg .=  $dupdata[1] . "<br />";
        }
        if (!move_uploaded_file($tmp_upload, $saveloc)) {
            $nomove = "Could not save {$filename} to site: contact Site Master";
            die($nomove);
        } else {
            $msg .= "'{$filename}' Successfully uploaded to site";
        }
    } else {
        $filename = "No file specified";
    }
    return array($filename, $msg);
}
/**
 * This function supplies a message appropriate to the type of upload
 * error encountered.
 * 
 * @param integer $errdat The flag supplied by the upload error check
 * 
 * @return string 
 */
function uploadErr($errdat)
{
    if ($errdat === UPLOAD_ERR_INI_SIZE || $errdat === UPLOAD_ERR_FORM_SIZE) {
        return 'File is too large for upload';
    }
    if ($errdat === UPLOAD_ERR_PARTIAL) {
        return 'The file was only partially uploaded (no further information';
    }
    if ($errdat === UPLOAD_ERR_NO_FILE) {
        return 'The file was not uploaded (no further information';
    }
    if ($errdat === UPLOAD_ERR_CANT_WRITE) {
        return 'Failed to write file to disk';
    }
    if ($errdat === UPLOAD_ERR_EXTENSION) {
        return 'A PHP extension stopped the upload';
    }
}
/**
 * When it has been determined that a file already exists on the
 * site in the destination directory, the string _DUP is attached
 * and the file is uploaded. The user will be notified when this
 * occurs.
 * 
 * @param string $oldname The filename which currently exists on site
 * 
 * @return array The new filename and msg to user that a dup was found
 */
function dupFileName($oldname)
{
    $extpos = strrpos($oldname, ".");
    $fbase = substr($oldname, 0, $extpos) . '_DUP.';
    $extpos++;
    $extlgth = strlen($oldname) - $extpos;
    $fext = substr($oldname, $extpos, $extlgth);
    $newname = $fbase . $fext;
    $fout = 'NOTE: ' . $oldname . ' has been previously saved on the '.
        'server; A new file name was created: ' . $newname;
    return array($newname, $fout);
}
/**
 * This function will create a .json track file from the specified gpx.
 * 
 * @param string $gpxfile The file to be converted to a .json file
 * @param string $gpxpath The path to the gpx file
 * 
 * @return array $trkfile (new .json file), $msg (output message if desired)
 */
function makeTrackFile($gpxfile, $gpxpath) 
{
    $ext = strrpos($gpxfile, ".");
    $baseName = substr($gpxfile, 0, $ext);
    $gpxLoc = $gpxpath . $gpxfile;
    // Now create the .json track file
    $gpxdat = simplexml_load_file($gpxLoc);
    if ($gpxdat === false) {
        die(
            "buildFunctions.php: Could not load gpx file as simplexml; " .
            "Please contact Site Master"
        );
    }
    $trkfile = $baseName . ".json";
    $trkLoc = '../json/' . $trkfile;
    $json = true;
    include "../php/extractGpx.php"; // creates track file $jdat
    $trk = fopen($trkLoc, "w");
    $dwnld = fwrite($trk, $jdat);
    if ($dwnld === false) {
        $trkfail =  "buildFunctions.php: Failed to write out {$trkfile} " .
            "[length: " . strlen($jdat) . "]; Please contact Site Master";
        die($trkfail);
    } else {
        $msg = '<p>Track file created from GPX and saved</p>';
    }
    fclose($trk);
    return array($trkfile, $msg);
}
/**
 * This function is used to check the file extension of an upload and determine
 * the location on the server for it to reside, based on extension type.
 * The whitelist for extensions is currently: .gpx (.GPX), .kml, and .html
 * 
 * @param string $fname The file name with or with path info
 * 
 * @return array $uplType (file mime type) and $floc (site path for storage)
 */
function fileTypeAndLoc($fname)
{
    $usable = array('gpx', 'kml', 'html');
    // get lower case representation of file extension
    $dot = strpos($fname, ".") + 1;
    $extlgth = strlen($fname) - $dot;
    $ext = substr($fname, $dot, $extlgth);
    $fext = strtolower($ext);
    $checks = count($usable);
    // see if the extension is "usable" and assign it an appropriate location
    $mimeType = '';
    $ftypeError = '';
    for ($i=0; $i<$checks; $i++) {
        if ($fext === $usable[$i]) {
            if ($fext === 'html') {
                $mimeType = "/html/";
                $floc = '../maps/';
            } elseif ($fext === 'kml') {
                $mimeType = '/vnd.google-earth.kml+xml/';
                $floc = '../gpx/';
            } else {
                $mimeType = "/octet-stream/";
                $floc = '../gpx/';
            }
        }
    }
    if ($mimeType === '') {
        $ftypeError = "Unacceptable file extension";
    }
    return array($floc, $mimeType, $fext, $ftypeError);
}
?>
