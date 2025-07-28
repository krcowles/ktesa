<?php
/**
 * This page holds all the modals required to complete the functionality of
 * the ktesaPanel.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
$gpxlist = scandir("../appGpxFiles");
array_splice($gpxlist, 0, 2); // eliminate . and ..
$rows = [];
foreach ($gpxlist as $gpx) {
    $row = "<tr><td class='fname'>{$gpx}</td>";
    $row .= "<td class='gcbox'><input type='checkbox' class='delfile'></td></tr>";
    array_push($rows, $row);                           
}
?>
<!-- Filter Hikes Modals (2) -->
<div id="bymiles" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter by Miles From Hike</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Select Hike:
                <div class="ui-widget">
                    <style type="text/css">
                        ul.ui-widget {
                            width: 320px;
                            clear: both;
                        }
                        ul[id^=ui-id] {
                            z-index: 9999;
                        }
                    </style>
                    <input id="startfromh" class="search modalsearch" type="text" 
                        placeholder="Search for Hike" />
                </div>
                <br />
                <div>
                    Miles from Hike:
                    <input id="misfromh" type="text" value="5"/>
                    <div class="spinicons">
                        <div class="uparw"></div>
                        <div class="separator">&nbsp;</div>
                        <div class="dwnarw"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="apply_miles" type="button"
                    class="btn btn-success">Apply Filter</button>
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div id="byloc" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter by Miles From Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php require "../edit/localeBox.html";?>
                <br />
                <div>
                    Miles from Locale Center:
                    <input id="misfroml" type="text" value="5"/>
                    <div class="spinicons">
                        <div class="uparw"></div>
                        <div class="separator">&nbsp;</div>
                        <div class="dwnarw"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="apply_loc" type="button"
                    class="btn btn-success">Apply Filter</button>
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- GPX Editor Modal -->
<div class="modal fade" id="ged" tabindex="-1"
    aria-labelledby="GPX File Editor" aria-hidden="true">
    <div class="modal-dialog">
        <form id="edform" action="../edit/gpxEditor.php" method="POST"
            enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Edit GPX File</h5>
                    <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input id="backurl" type="hidden" name="backurl" />
                    <label for="file2edit">
                        Select the GPX File to be edited: </label>
                    <input id="file2edit" type="file" name="file2edit" />
                    <br /><br />
                    <div id="gpxnote" style="font-style:italic;">
                        For GPX Files with more than one track, specify
                            which track to edit:&nbsp;&nbsp;&nbsp;
                        <input id="trackno" name="trackno" type="text"
                            value="1" size="2" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Close</button>
                    <button id="gotoedit" type="submit" class="btn btn-secondary">
                        Edit File
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Member benefits modal -->
<div id="membennies" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Membership Explained</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="ap" class="modal-body">
            <p>Membership is <em>free</em>. And as a member, you can create
                your own hike page, or edit an existing one. All you
                need is a gpx track file(s), photos taken during the
                hike, a good description, and external references, if
                any (books, weblinks, blogs, etc).</p>
            <p>Another benefit is that you can save 'favorites' and map
                them on a separate page (Explore->Show Favorites). Other
                member benefits are being added, such as auto-saving certain
                preferences, (e.g. preferred GPS formats), etc.
            </p>
            <p>Join now and start creating!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Login modal when lockout condition is encountered -->
<div class="modal fade" id="lockout" tabindex="-1"
    aria-labelledby="Lockout" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    You are locked out</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                You are currently locked out and can login again in 
                <span class="lomin"></span> minutes. You may continue
                to wait, or you may reset your password by selecting
                that option below.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Wait</button>
                <button id="force_reset" type="button" class="btn btn-success">
                    Reset my password</button>
            </div>
        </div>
    </div>
</div>
<!-- Change password Modal NOTE: re-appears in unifiedLogin.php since no panel -->
<div class="modal fade" id="cpw" tabindex="-1"
    aria-labelledby="ResetPassword" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    Reset Password</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            You will receive an email to reset/change your password<br />
            Enter email:<br /><br />
                <input id="rstmail" type="email" style="width:360px"
                    required placeholder="Enter your email" /><br /><br />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
                <button id="send" type="button" class="btn btn-success">
                    Send</button>
            </div>
        </div>
    </div>
</div>
<!-- Update Security Questions Modal -->
<div id="security" class="modal" tabindex="-1">
    <div class="modal-dialog" style="max-width:60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Answer 3 Security Questions</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="uques"></div>
            </div>
            <div class="modal-footer">
                <button id="resetans" type="button"
                    class="btn btn-secondary"> Reset Answers</button>
                <button id="closesec" type="button" 
                    class="btn btn-secondary">Apply</button>
            </div>
        </div>
    </div>
</div>
<!-- Latest Additions Modal -->
<div id="newpgs" class="modal" tabindex="-1">
    <div class="modal-dialog" style="max-width:40%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Most recent hikes</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div>
                    These are the latest hike page additions, in order of
                    most recent:
                    <div id="newest"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- GPX Files available for apps to download -->
<div id="appfiles" class="modal" tabindex="-1">
    <style type="text/css">
        #gpx-upload {display:none;}
        .custom-file-upload {border: 2px solid darkslategray;
            border-radius:6px; display: inline-block;
            padding: 6px 12px; cursor: pointer;}
        #gpxlist {width: 60%; height:180px;
            background-color: gainsboro;
            overflow: auto; border: 1px darkgray solid;}
        #available_gpx {margin-left:auto; margin-right:auto;
            border: 1px black solid; border-collapse:collapse;
            margin-top: 12px; background-color:#ffffdf;}
        .gcbox {border: 1px black solid; border-collapse:collapse;
            padding:2px !important;text-align:center !important;}
        .fname {border-collapse:collapse; border:1px black solid;
            padding: 2px 4px 2px 4px;}
        #sel_list {margin-left: 20px;}
        #nofiles {position:relative; top:12px; 
            left:16px; color:brown;}
    </style>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">GPX Files for Apps</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div>
                    <p>List of GPX files available to be downloaded to mobile apps,
                        such as 'GPX Viewer', 'GPX Tracker', etc. <span 
                        style="text-decoration:underline">Simply type this URL in
                        your app</span>, followed by the file to download:
                        <br />
                        <strong>https://nmhikes.com/appGpxFiles/&lt;file&gt;</strong>
                       
                    </p>
                    Alphabetical list: can be scrolled if needed
                    <div id="gpxlist">
                        <?php if (count($rows) === 0) : ?>
                        <span id="nofiles">There are no files available at
                            this time</span>
                        <?php else :?>
                        <table id="available_gpx">
                            <tbody>
                                <tr>
                                    <th class="fname"
                                        style="background-color:moccasin;">
                                        File Name</th>
                                    <th class="fname"
                                        style="background-color:moccasin;">
                                        Remove</th>
                                </tr>
                                    <?php foreach ($rows as $row) : ?>
                                        <?=$row?>
                                    <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                    <br />
                    <span><em>Add a GPX File to the List:</em></span>&nbsp;&nbsp;
                    <input id="gpx-upload" name="gpx-upload[]"
                        type="file" multiple/>
                    <label for="gpx-upload" class="custom-file-upload">
                        Browse
                    </label>
                    &nbsp;<span id="upldfile">No files selected</span><br />
                    <button id="getgpx" type="button" class="btn btn-success">
                        Upload file(s)
                    </button> 
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
