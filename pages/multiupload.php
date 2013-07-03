<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/* ---------------------------------
 * File Upload
 * ---------------------------------
 * 
 */

// --------------------------------------------------------
// file upload uses HTML5 and standard form based upload
// When using standard form based upload a iframe is used to send the form
// and an ajax call is used to check on the progress of the upload
// If the server is not able to return the information about the file upload then a default spinner is loaded
// --------------------------------------------------------

// check if a voucher and load into form if it is
$filestatus = 'Available';
$voucherUID = '';
$senderemail = $useremail;
$functions = Functions::getInstance();

// get initial upload uid
$id = getGUID();
// set id for progress bar upload
// $id = md5(microtime() . rand());

// check if this is a voucher
if ($authvoucher->aVoucher()) {
    // clear aup session
    //unset ($_SESSION['aup'], $var);

    // get voucher information
    $voucherData = $authvoucher->getVoucher();
    $voucherUID = $voucherData[0]['filevoucheruid'];
    $filetrackingcode = $voucherData[0]['fileauthuseruid'];
    logEntry('vid = ' . $voucherUID, 'E_ERROR');
    $senderemail = array($voucherData[0]['fileto']);
    // check if voucher is invalid (this should be an external function
    if ($voucherData[0]['filestatus'] == 'Voucher') {
        $filestatus = 'Voucher';
    } else {
        if ($voucherData[0]['filestatus'] == 'Voucher Cancelled' || $voucherData[0]['filestatus'] == 'Closed') {
            echo '<p>' . lang('_VOUCHER_CANCELLED') . '</p>';
            return;
        }
    }
}

if (isset($_COOKIE['SimpleSAMLAuthToken'])) {
    $token = urlencode($_COOKIE['SimpleSAMLAuthToken']);
} else {
    $token = '';
}

global $config;

?>

<script type='text/javascript' src='lib/js/AC_OETags.js' language='javascript'></script>
<script type='text/javascript' src='js/multiupload.js'></script>
<script type='text/javascript'>
//<![CDATA[
// all default settings
var uploadID = '<?php echo $id ?>';
var maximumDate = <?php echo (time()+($config['default_daysvalid']*86400))*1000 ?>;
var minimumDate = <?php echo (time()+86400)*1000 ?>;
var maxHTML5UploadSize = <?php echo $config['max_html5_upload_size']; ?>;
var maxEmailRecipients = <?php echo $config['max_email_recipients']; ?>;
var datepickerDateFormat = '<?php echo lang('_DP_dateFormat'); ?>';
var chunksize =  <?php echo $config['upload_chunk_size']; ?>;
var aup = '<?php echo $config['AuP'] ?>';
var bytesUploaded = 0;
var bytesTotal = 0;
var ext = '<?php echo $config['ban_extension']?>';
var bannedExtensions = ext.split(",");
var previousBytesLoaded = 0;
var intervalTimer = 0;

var errmsg_disk_space = '<?php echo lang('_DISK_SPACE_ERROR'); ?>';
var filedata = [];
var nameLang = '<?php echo lang('_FILE_NAME'); ?>';
var sizeLang = '<?php echo lang('_SIZE'); ?>';
var groupid = '<?php echo getOpenSSLKey(); ?>';

<?php if(!$authvoucher->aVoucher()) $userData = $authsaml->sAuth(); ?>
var trackingCode = '<?php echo $functions->getTrackingCode($userData['saml_uid_attribute']); ?>';
var vid = '<?php if (isset($_REQUEST['vid'])){ echo htmlspecialchars($_REQUEST['vid']);}; ?>';

// start document ready
$(function () {
    $('#clearallbtn').button('disable');
    // set date picker
    var datePicker = $('#datepicker');
    datePicker.datepicker({ minDate: new Date(minimumDate), maxDate: new Date(maximumDate), altField: '#fileexpirydate', altFormat: 'd-m-yy' });
    datePicker.datepicker('option', 'dateFormat', '<?php echo lang('_DP_dateFormat'); ?>');
    datePicker.datepicker('setDate', new Date(maximumDate));
    $('#ui-datepicker-div').css('display', 'none');

    // set datepicker language
    $.datepicker.setDefaults({
        closeText: '<?php echo lang('_DP_closeText'); ?>',
        prevText: '<?php echo lang('_DP_prevText'); ?>',
        nextText: '<?php echo lang('_DP_nextText'); ?>',
        currentText: '<?php echo lang('_DP_currentText'); ?>',
        monthNames: <?php echo lang('_DP_monthNames'); ?>,
        monthNamesShort: <?php echo lang('_DP_monthNamesShort'); ?>,
        dayNames: <?php echo lang('_DP_dayNames'); ?>,
        dayNamesShort: <?php echo lang('_DP_dayNamesShort'); ?>,
        dayNamesMin: <?php echo lang('_DP_dayNamesMin'); ?>,
        weekHeader: '<?php echo lang('_DP_weekHeader'); ?>',
        dateFormat: '<?php echo lang('_DP_dateFormat'); ?>',
        firstDay: <?php echo lang('_DP_firstDay'); ?>,
        isRTL: <?php echo lang('_DP_isRTL'); ?>,
        showMonthAfterYear: <?php echo lang('_DP_showMonthAfterYear'); ?>,
        yearSuffix: '<?php echo lang('_DP_yearSuffix'); ?>'
    });


    // upload area filesToUpload
    var body = $('body');
    body.on(
        'dragover',
        function (e) {
            e.preventDefault();
            e.stopPropagation();
        }
    );
    body.on(
        'dragenter',
        function (e) {
            e.preventDefault();
            e.stopPropagation();
        }
    );
    body.on(
        'drop',
        function (e) {
            if (e.originalEvent.dataTransfer) {
                if (e.originalEvent.dataTransfer.files.length) {

                    e.preventDefault();
                    e.stopPropagation();

                    var files = e.originalEvent.dataTransfer.files;
                    addFiles(files);
                }
            }
        }
    );

    // default error message dialog
    $('#dialog-support').dialog({ autoOpen: false, height: 400, width: 550, modal: true, title: '',
        buttons: {
            'supportBTN': function () {
                $(this).dialog('close');
            }
        }
    });

    var uiDialogButtonpane = $('.ui-dialog-buttonpane');

    uiDialogButtonpane.find('button:contains(supportBTN)').attr('id', 'btn_support');
    //$('.ui-dialog-buttonpane button:contains(supportBTN)').attr('id', 'btn_support');
    $('#btn_support').html('<?php echo lang('_OK') ?>');

    // default auth error dialog
    $('#dialog-autherror').dialog({ autoOpen: false, height: 240, width: 350, modal: true, title: '',
        buttons: {
            '<?php echo lang('_OK') ?>': function () {
                location.reload();
            }
        }
    });

    // default error message dialog
    $('#dialog-default').dialog({ autoOpen: false, height: 200, modal: true, title: 'Error',
        buttons: {
            '<?php echo lang('_OK') ?>': function () {
                $('#dialog-default').html('');
                $(this).dialog('close');
            }
        }
    });

    uiDialogButtonpane.find('button:contains(uploadcancelBTN)').attr('id', 'btn_uploadcancel');
    $('#btn_uploadcancel').html('<?php echo lang('_CANCEL') ?>');

    $('#uploadhtml5').show();

    // autocomplete
    var availableTags = [<?php  echo (isset($config["autocomplete"]) && $config["autocomplete"])?  $functions->uniqueemailsforautocomplete():  ""; ?>];

    function split( val ) {
        return val.split( /,\s*/ );
    }
    function extractLast( term ) {
        return split( term ).pop();
    }

    $( "#fileto" )
        // don't navigate away from the field on tab when selecting an item
        .bind( "keydown", function( event ) {
            if ( event.keyCode === $.ui.keyCode.TAB &&
                $( this ).data( "uiAutocomplete" ).menu.active ) {
                event.preventDefault();
            }
        })
        .autocomplete({
            minLength: 0,
            source: function( request, response ) {
                // delegate back to autocomplete, but extract the last term
                response( $.ui.autocomplete.filter(
                    availableTags, extractLast( request.term ) ) );
            },
            focus: function() {
                // prevent value inserted on focus
                return false;
            },
            select: function( event, ui ) {
                var terms = split( this.value );
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push( ui.item.value );
                // add placeholder to get the comma-and-space at the end
                terms.push( "" );
                this.value = terms;//.join( ", " );
                return false;
            }
        });
    // end autocomplete
});

// toggle AUP display
function toggleTOG() {
    var tog = $('#tog');
    if (tog.is(':visible')) {
        tog.hide();
    } else {
        tog.show();
    }
}

// hides every error message on the form
function hidemessages() {
    $('#fileto_msg').hide();
    $('#expiry_msg').hide();
    $('#maxemails_msg').hide();
    $('#file_msg').hide();
    $('#aup_msg').hide();
}

// --------------------------
// Validation functions
// --------------------------

// Validate FILE (with embedded calls to check filename and file-extension)
function validate_file(id) {
    fileMsg('');

    if (!fileData[id]) {
        // display message if a user enters all form details and selects upload without selecting a file
        // in theory this error should not appear as a browse button should not be visible without a file first being selected
        fileMsg('<?php echo lang('_SELECT_FILE') ?>');
        return false;
    } else {
        var file = fileData[id];

        if (!validateFileName(file.name)) {
            return false;
        } else if (file.size < 1) {
            fileMsg('<?php echo lang('_INVALID_FILESIZE_ZERO') ?>');
            return false;
        } else if (file.size > maxHTML5UploadSize) {
            fileMsg('<?php echo lang('_INVALID_TOO_LARGE_1') ?> ' + readablizebytes(maxHTML5UploadSize) + '. <?php echo lang('_SELECT_ANOTHER_FILE') ?> ');
            return false;
        }
    }
    return true;
}

// HTML5 form Validation
function validateForm() {
    // remove messages from any previous attempt
    hidemessages();
    return (validate_fileto() && validate_file(n) && validate_expiry() && validateAUP());
}

function constrainNumWebWorkers() {
    <?php
        $limit = 'undefined';
        if (isset($config['webWorkersLimit'])) {
            $limit = $config['webWorkersLimit'];
        }
    ?>

    var maxLimitWebWorkers = <?php echo $limit; ?>;
    var workerCount = $('#workerCount');
    if (maxLimitWebWorkers != 'undefined' && parseInt(workerCount.val()) > maxLimitWebWorkers) {
        workerCount.val(maxLimitWebWorkers);
    }
}

function validateAUP() {
    // Checks if the AUP is required in config and if users have checked the box
    if ($('#aup').is(':checked') && aup == '1') {
        $('#aup_msg').hide();
        return true;
    } else {
        $('#aup_msg').show();
        return false;
    }
}

function validateExtension(fileName) {
    // Loops through the list of banned extensions and returns false if there is a match
    for (var i = 0, len = bannedExtensions.length; i < len; ++i) {
        if (fileName.split('.').pop() == bannedExtensions[i]) {
            return false;
        }
    }
    return true;
}

function checkFilesSelected() {
    return document.getElementById('fileToUpload').files[0] != null || document.getElementById('file_0') != null;
}

function uploadError(name, size) {
    errorDialog('<?php echo lang('_ERROR_UPLOADING_FILE') ?> ' + name + ':' + size);
}

function validateFileName(name) {
    if (!validateExtension(name)) {
        fileMsg('<?php echo lang('_INVALID_FILE_EXT').' '.lang('_SELECT_ANOTHER_FILE') ?>');
        return false;
    }
    if (/^[^\\\/:;\*\?\"<>|]+(\.[^\\\/:;\*\?\"<>|]+)*$/.test(name)) {
        return true;
    } else {
        fileMsg('<?php echo lang('_INVALID_FILE_NAME') ?>');
        return false;
    }
}

function validate() {
    // upload if validated
    if (validateForm()) {
        constrainNumWebWorkers(); // make sure selected web workers isn't over the config limit
        n = 0;
        openProgressBar();
        aggregateStartTime = new Date().getTime();// Calling before first upload stops progress bar opening for every download (when attempting to close it)
        startUpload();

    }
}

function errorDialog(msg) {
    var dialogDefault = $('#dialog-default');
    dialogDefault.html(msg);
    dialogDefault.dialog('open');
}


function keepMeAlive() {
    $.ajax({
        // TODO: maybe encodeURI(new Date()) is a better alternative
        url: 'keepalive.php' + '?x=' + escape(new Date()),
        success: function (data) {
        }
    });
}

// special fix for esc key on firefox stopping xhr
window.addEventListener('keydown', function (e) {
    (e.keyCode == 27 && e.preventDefault())
});
//]]>
</script>

<form id="form1" enctype="multipart/form-data" method="post" action="fs_uploadit.php">
    <table border="0" style="width:100%; border-spacing:6px">
        <tr>
            <td class="box" rowspan="4" style="vertical-align: top;">
                <div id="fileInfoView" >
                    <div class="box" style="border: none">
                        <div id="uploadhtml5" style="display:none">

                            <input style="display:none; padding-right:6px;" type="file" name="fileToUpload"
                                   id="fileToUpload" onchange="fileSelected();" multiple/>
                        </div>
                        <div id="file_msg" class="validation_msg"
                             style="display: none"><?php echo lang("_INVALID_FILE"); ?>
                        </div>
                    </div>
                    <div style="text-align:left;" class="menu">
                        <a id="clearallbtn" href="#" onclick="clearFileBox()" style="cursor:pointer;width:20%;"><?php echo lang('_CLEAR_ALL'); ?></a>
                    </div>
                    <br/>
                </div>

                <div id="dragfilestouploadcss" style="height:400px; overflow:auto;" class="box">
                    <div id="filestoupload" style="display:table;width:100%; height:100%;">
                        <div id="draganddropmsg" style="text-align:center;display:table-cell; vertical-align:middle;" class="heading"><?php echo lang('_DRAG_AND_DROP'); ?></div>
                    </div>
                </div>
                <br/>

                <div style="text-align:center;" class="menu">
                    <a href="#" onclick="browse()" style="cursor:pointer;width:33%;"><?php echo lang('_SELECT_FILES'); ?></a>
                </div>
                <br/>
            </td>
            <td class="box" style="width:50%; height:100%; vertical-align:top;">
                <div class="box">
                    <div class="fieldcontainer" id="upload_from">
                        <div class="label mandatory"><?php echo lang('_FROM'); ?>:&nbsp;</div>
                        <?php
                            if (count($senderemail) > 1) {
                                echo '<select name=\"filefrom\" id=\"filefrom\" style=\"width:98%;font-size:12px;font-family: Verdana, Geneva, sans-serif;\">\n';
                                foreach ($senderemail as $email) {
                                    echo '<option>$email</option>\n';
                                }
                                echo '</select>\n';
                            } else {
                                echo $senderemail[0]
                                    . "<input name=\"filefrom\" type=\"hidden\" id=\"filefrom\" value=\""
                                    . $senderemail[0]
                                    . "\" />\n";
                            }
                        ?>
                    </div>

                    <label for="fileto" class="mandatory"><?php echo lang("_TO"); ?>:</label>
                    <input name="fileto" type="text" id="fileto"
                               title="<?php echo lang("_EMAIL_SEPARATOR_MSG"); ?>" onchange="validate_fileto()"
                               value="" placeholder="<?php echo lang('_ENTER_TO_EMAIL'); ?>" />

                    <div id="fileto_msg" style="display: none" class="validation_msg field" class="">
                        <?php echo lang('_INVALID_MISSING_EMAIL'); ?>
                        <div id="maxemails_msg" style="display: none"
                             class="validation_msg"><?php echo lang('_MAXEMAILS') . $config['max_email_recipients']; ?>
                        </div>
                    </div>

                    <label for="filesubject"><?php echo lang('_SUBJECT') . ': (' . lang('_OPTIONAL'); ?>)</label>
                    <input name="filesubject" type="text" id="filesubject"/>

                    <label for="filemessage"><?php echo lang('_MESSAGE') . ': (' . lang('_OPTIONAL'); ?>)</label>
                    <textarea name="filemessage" cols="57" rows="5" id="filemessage"></textarea>

                    <input name="filefrom" type="hidden" id="filefrom" value="<?php echo $senderemail[0]; ?>" size="40"/>

                    <div>
                        <input type="hidden" id="filevoucheruid" name="filevoucheruid"
                               value="<?php echo $voucherUID; ?>"/>
                        <input type="hidden" name="vid" id="vid" value="<?php echo $voucherUID; ?>"/>
                        <input type="hidden" name="total" id="total" value=""/>
                        <input type="hidden" name="n" id="n" value=""/>
                        <input type="hidden" id="filestatus" name="filestatus" value="<?php echo $filestatus; ?>"/>
                        <input type="hidden" name="loadtype" id="loadtype" value="standard"/>
                        <input type="hidden" name="s-token" id="s-token"
                               value="<?php echo (isset($_SESSION['s-token'])) ? $_SESSION['s-token'] : ''; ?>"/>
                    </div>
            </td>
        </tr>
        <tr>
            <td class="box" style="vertical-align:top; height:100%;">
                <div id="options">
                    <div class="fieldcontainer" id="upload_expirydate">
                        <label for="datepicker"><?php echo lang("_EXPIRY_DATE"); ?>:</label>
                        <input id="datepicker" name="datepicker" title="<?php echo lang('_DP_dateFormat'); ?>"
                               onchange="validate_expiry()"/>

                        <div id="expiry_msg" class="validation_msg"
                             style="display: none"><?php echo lang('_INVALID_EXPIRY_DATE'); ?>
                        </div>
                    </div>
                    <div class=" mandatory">
                        <input type="hidden" id="fileexpirydate" name="fileexpirydate"
                               value="<?php echo date(
                                   lang('datedisplayformat'),
                                   strtotime('+' . $config['default_daysvalid'] . ' day')
                               ); ?>"/>
                    </div>
                    <br/>
                    <label for="rtnemail"><?php echo lang("_SEND_COPY_EMAILS"); ?></label>
                    <input name="rtnemail" type="checkbox" id="rtnemail" style="float:left; width:20px;"/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="box" style="height:20px">
                <?php if ($config['AuP']) { ?>
                    <div class="auppanel">
                        <label id="aup_label" for="aup"style="cursor:pointer;" title="<?php echo lang('_SHOWHIDE'); ?>"
                               onclick="toggleTOG();return false;"><?php echo lang('_ACCEPTTOC'); ?></label>
                        <input style="float:left" name="aup" type="checkbox" id="aup"
                               onchange="validateAUP();"
                            <?php echo ($config['AuP_default']) ? 'checked' : ''; ?>
                            <?php echo (isset($_SESSION['aup']) && !$authvoucher->aVoucher()) ? 'checked' : ''; ?>
                               value="true"/>
                        <div id="aup_msg" class="validation_msg"
                             style="display: none"><?php echo lang('_AGREETOC'); ?>
                        </div>
                        <div id="tog" style="display:none"> <?php echo lang('_AUPTERMS'); ?> </div>
                    </div>
                <?php } ?>
                <div>
                    <div class="menu" id="uploadbutton" style="display:;text-align: center;">
                        <a href="#" onclick="validate()"><?php echo lang('_SEND'); ?></a>
                    </div>
                </div>

                <div id="workers-advanced-settings" style="display: none;" class="box">
                    <label for="chunksize"><?php echo lang('_TERA_CHUNKSIZE'); ?></label>
                    <input id="chunksize" type="text" value="<?php echo $config['terasender_chunksize'] ?>"><br/>
                    <label for="workerCount"><?php echo lang('_TERA_WORKER_COUNT'); ?></label>
                    <input id="workerCount" type="text" value="<?php echo $config['terasender_workerCount'] ?>"><br/>
                    <label for="jobsPerWorker"><?php echo lang('_TERA_JOBS_PER_WORKER'); ?></label>
                    <input id="jobsPerWorker" type="text"value="<?php echo $config['terasender_jobsPerWorker'] ?>">
                </div>
                <?php if ($config['terasender'] && $config['terasenderadvanced']) { ?>
                    <div>
                        <a href="#" onclick="$('#workers-advanced-settings').slideToggle()"><?php echo lang('_TERA_ADVANCED_SETTINGS'); ?></a>
                    </div>
                <?php } ?>
            </td>
        </tr>
    </table>
    <div class="colmask threecol" id="dragfilestoupload"></div>
</form>
<div id="dialog-default" style="display:none" title=""></div>
<div id="dialog-cancel" style="display:none"
     title="<?php echo lang('_CANCEL_UPLOAD'); ?>">
     <?php echo lang('_ARE_YOU_SURE'); ?>
</div>

<div id="dialog-support" title="" style="display:none">
    <?php require_once("$filesenderbase/pages/html5display.php"); ?>
</div>

<div id="dialog-autherror" title="<?php echo lang('_MESSAGE'); ?>"
     style="display:none"><?php echo lang('_AUTH_ERROR'); ?>
</div>

<!--Aggregate progress bar contents-->
<div id="aggregate_dialog_contents" style="display: none;">
    <div id="aggregate_progress" class="fileBox">
        <span class="filebox_string" id="aggregate_string" style="text-align: center"></span>
        <div class="progress_bar" id="aggregate_bar"></div>
    </div>
    <p id="totalUploaded"></p>
    <p id="averageUploadSpeed"></p>
    <p id="timeRemaining"></p>
</div>

<!-- Upload Cancel -->
<div id="dialog-confirm" title="Are you Sure?" style="display: none">
    <p>All files will be deleted</p> <!-- TODO: need a lang for this -->
</div>
