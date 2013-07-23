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

require_once('../www/upload_common_js.php');
require_once('../www/upload_html5_js.php');
?>

<script type='text/javascript' src='lib/js/AC_OETags.js'></script>
<script type='text/javascript' src='js/multiupload.js'></script>
<script type='text/javascript'>
// start document ready
$(function () {
    $('#clearallbtn').button('disable');

    getDatePicker();

   // Set up the drag-and-drop behaviour.
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

    var uiDialogButtonpane = $('.ui-dialog-buttonpane');
    uiDialogButtonpane.find('button:contains(supportBTN)').attr('id', 'btn_support');
    $('#btn_support').html('<?php echo lang('_OK') ?>');

    // Default auth error dialog.
    $('#dialog-autherror').dialog({ autoOpen: false, height: 240, width: 350, modal: true, title: '',
        buttons: {
            '<?php echo lang('_OK') ?>': function () {
                location.reload();
            }
        }
    });

    // Default error message dialog.
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

    autoCompleteEmails();
});
</script>

<form id="form1" enctype="multipart/form-data" method="post" action="fs_uploadit.php">
    <div style="width:100%; border-spacing:6px">
        <div class="box" style="vertical-align: top;">
            <div id="fileInfoView">
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
                    <a id="clearallbtn" href="#" onclick="clearFileBox()"
                       style="cursor:pointer;width:20%;"><?php echo lang('_CLEAR_ALL'); ?></a>

                    <span id="uploadBoxStats" style="float: right; padding-right: 20px;"></span>
                </div>
                <br/>
            </div>

            <div id="dragfilestouploadcss" style="height:200px; overflow:auto;" class="box">
                <div id="filestoupload" style="display:table;width:100%; height:100%;">
                    <div id="draganddropmsg" style="text-align:center;display:table-cell; vertical-align:middle;"
                         class="heading"><?php echo lang('_DRAG_AND_DROP'); ?></div>
                </div>
            </div>
            <br/>

            <div style="text-align:center;" class="menu">
                <a href="#" onclick="browse()"
                   style="cursor:pointer;width:33%;"><?php echo lang('_SELECT_FILES'); ?></a>
            </div>
            <br/>
        </div>

        <table id="columns" style="border-spacing: 0">
            <tr>
                <td class="box" style="height:100%; vertical-align:top;width:50%" id="col1">
                    <div class="fieldcontainer" id="upload_from">
                        <?php
                        if (count($senderemail) > 1) {
                            echo '<label for="filefrom" class="mandatory">' . lang('_FROM') . ':&nbsp;</label>';
                            echo '<select name=\"filefrom\" id=\"filefrom\" style=\"width:98%;font-size:12px;font-family: Verdana, Geneva, sans-serif;\">\n';
                            foreach ($senderemail as $email) {
                                echo '<option>' . $email . '</option>';
                            }
                            echo '</select>';
                        } else {
                            echo '<span class="mandatory">' . lang('_FROM') . ':&nbsp;</span>';
                            echo $senderemail[0] . "<input name=\"filefrom\" type=\"hidden\" id=\"filefrom\" value=\"$senderemail[0]\" />\n";
                        }
                        ?>
                    </div>

                    <div class="fieldcontainer">
                        <label for="fileto" class="mandatory"><?php echo lang("_TO"); ?>:</label>
                        <input name="fileto" type="text" id="fileto"
                               title="<?php echo lang("_EMAIL_SEPARATOR_MSG"); ?>" onchange="validate_fileto()"
                               value="" placeholder="<?php echo lang('_ENTER_TO_EMAIL'); ?>"/>

                        <div id="fileto_msg" style="display: none" class="validation_msg field">
                            <?php echo lang('_INVALID_MISSING_EMAIL'); ?>
                            <div id="maxemails_msg" style="display: none"
                                 class="validation_msg"><?php echo lang('_MAXEMAILS')
                                    . $config['max_email_recipients']; ?>
                            </div>
                        </div>
                    </div>

                    <div class="fieldcontainer">
                        <label for="filesubject"><?php echo lang('_SUBJECT') . ': (' . lang('_OPTIONAL'); ?>)</label>
                        <input name="filesubject" type="text" id="filesubject"/>
                    </div>

                    <div class="fieldcontainer">
                        <label for="filemessage"><?php echo lang('_MESSAGE') . ': (' . lang('_OPTIONAL'); ?>)</label>
                        <textarea name="filemessage" cols="57" rows="5" id="filemessage"
                                  style="resize:vertical"></textarea>
                    </div>

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
                <td style="width:5px"></td>
                <td class="box" style="vertical-align:top; height:100%;width:50%;" id="col2">
                    <div id="options">
                        <div class="fieldcontainer" id="upload_expirydate">
                            <label for="datepicker" class="mandatory"><?php echo lang("_EXPIRY_DATE"); ?>:</label>
                            <input id="datepicker" name="datepicker" title="<?php echo lang('_DP_dateFormat'); ?>"
                                   onchange="validate_expiry()"/>

                            <div id="expiry_msg" class="validation_msg"
                                 style="display: none"><?php echo lang('_INVALID_EXPIRY_DATE'); ?>
                            </div>
                        </div>
                        <input type="hidden" id="fileexpirydate" name="fileexpirydate"
                               value="<?php echo date(
                                   lang('datedisplayformat'),
                                   strtotime('+' . $config['default_daysvalid'] . ' day')
                               ); ?>"/>
                        <br/>
                        <label for="rtnemail"><?php echo lang("_SEND_COPY_EMAILS"); ?></label>
                        <input name="rtnemail" type="checkbox" id="rtnemail" style="float:left; width:20px;"/>
                    </div>

                    <div id="workers-advanced-settings" style="display: none;">
                        <div class="fieldcontainer">
                            <label for="chunksize"><?php echo lang('_TERA_CHUNKSIZE'); ?></label>
                            <input id="chunksize" type="text" value="<?php echo $config['terasender_chunksize'] ?>"/>
                            <br/>
                        </div>
                        <div class="fieldcontainer">
                            <label for="workerCount"><?php echo lang('_TERA_WORKER_COUNT'); ?></label>
                            <input id="workerCount" type="text"
                                   value="<?php echo $config['terasender_workerCount'] ?>"/>
                            <br/>
                        </div>
                        <div class="fieldcontainer">
                            <label for="jobsPerWorker"><?php echo lang('_TERA_JOBS_PER_WORKER'); ?></label>
                            <input id="jobsPerWorker" type="text"
                                   value="<?php echo $config['terasender_jobsPerWorker'] ?>"/>
                        </div>
                    </div>
                    <?php if ($config['terasender'] && $config['terasenderadvanced']) { ?>
                        <div class="fieldcontainer">
                            <a href="#" onclick="$('#workers-advanced-settings').slideToggle()">
                                <?php echo lang('_TERA_ADVANCED_SETTINGS'); ?>
                            </a>
                        </div>
                    <?php } ?>
                </td>
            </tr>
        </table>

        <div style="clear: both"></div>

        <?php if ($config['AuP']) { ?>
            <div class="auppanel">
                <label id="aup_label" for="aup" style="cursor:pointer;" title="<?php echo lang('_SHOWHIDE'); ?>"
                       onclick="$('#tog').slideToggle();return false;"><?php echo lang('_ACCEPTTOC'); ?></label>
                <?php
                $aupChecked = '';
                if ($config['AuP_default'] || (isset($_SESSION['aup']) && !$authvoucher->aVoucher())) {
                    $aupChecked = 'checked="checked"';
                }
                ?>

                <input style="float:left" name="aup" type="checkbox" id="aup"
                       onchange="validate_aup();" <?php echo $aupChecked; ?> value="true"/>

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
    <div class="colmask threecol" id="dragfilestoupload"></div>
</form>
<div id="dialog-default" style="display:none" title=""></div>
<div id="dialog-cancel" style="display:none"
     title="<?php echo lang('_CANCEL_UPLOAD'); ?>">
    <?php echo lang('_ARE_YOU_SURE'); ?>
</div>
