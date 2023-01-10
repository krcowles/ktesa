<?php
/**
 * This page presents a sortable, filterable table of hikes
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
?>
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
<!-- Single Security Question Modal -->
<div id="twofa" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Security Question</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="ap" class="modal-body">
            <p id="the_question"></p>
            <input id="the_answer" type="text" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
                <button id="submit_answer" type="button"
                    class="btn btn-success">Apply</button>
            </div>
        </div>
    </div>
</div>
<!-- For 'Forgot password', 'Renew password' & 'Change password' Modal -->
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
                Enter your email below. You will receive an email link to 
                reset your password. It will also contain your username.<br /><br />
                <input id="rstmail" type="email" style="width:360px"
                    required placeholder="Enter your email" /><br /><br />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
                <button id="send">Send</button>
            </div>
        </div>
    </div>
</div>
<!-- Password Status Details Modal -->
<div id="show_pword_details" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Password Status</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table id="ptable">
                    <tbody>
                        <tr>
                            <td>Characters:</td>
                            <td id="total">0</td>
                            <td colspan="6"></td>
                        </tr>
                        <tr>
                            <td>Lower case:</td>
                            <td id="lc">0</td>
                            <td>;&nbsp;&nbsp;Upper case:</td>
                            <td id="uc">0</td>
                            <td>;&nbsp;&nbsp;Numbers:</td>
                            <td id="nm">0</td>
                            <td>;&nbsp;&nbsp;Special:</td>
                            <td id="sp">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Mobile Cookie Banner Modal -->
<div id="cooky" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Designate Your Choice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                Please accept or reject cookies. No data is stored when you
                reject, but you will have to login with each visit.
                </p>
                <table>
                    <tbody>
                        <tr><td>
                            <div class="form-check">
                                <input id="maccept" class="form-check-input"
                                    type="radio" value="nochoice" />
                                <label class="form-check-label" for="accept">
                                    Accept Cookies</label>
                            </div>
                        </td></tr>
                        <tr><td>
                            <div class="form-check">
                                <input id="mreject" class="form-check-input"
                                    type="radio" value="nochoice" />
                                <label class="form-check-label" for="reject">
                                    Reject Cookies</label>
                            </div>
                        </td></tr>
                    </tbody>
                </table>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Filter Hikes Modals -->
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
<!-- Sort Hikes Modals -->
<div id="sort_opts" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sort In Reverse Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Reverse Order
                Difficulty
                Last Hiked
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Info Modal [when ajax errors occur] -->
<div id="ajaxerr" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">An Error Has Occurred</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                We are sorry, but an error has occurred. The admin has been
                notified. We apologize for any inconvenience.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
