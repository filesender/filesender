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

function isChecked($checkbox) {
    if ($checkbox) {
        return 'checked';
    } else return '';
}

if(isset($_REQUEST['a']) && $_REQUEST['a'] == 'cancelled') {
    $statusMsg = lang('_UPLOAD_CANCEL_SUCCESS');
    $statusClass = 'green';
}
?>

<script type='text/javascript' src='js/multiupload.js'></script>
<script type='text/javascript'>
    var fileBoxSize = <?php echo Config::get('upload_box_default_size'); ?>;
// start document ready
$(function () {
    $('#dragfilestouploadcss').css('height', 14+(40* fileBoxSize));
    
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
                    //console.log(files);
                    addFiles(files);
                }
            }
        }
    );
    
    $('#uploadhtml5').show();
    
    autoCompleteEmails();
    fileSelected(); // For back button issues.
    
    $('#add_me_to_recipients').click(function() {
        $('#add_me_to_recipients').is(':checked') ? addEmailRecipientBox(userEmail) : removeEmailNameFromBox(userEmail);
        if ($('#fileto').val() != '') {
            validate_fileto();
        }
    });
    
    if(localStorage.getItem('uploadData')) {
        offerResumeUpload();
    }
    $('#fileto').keydown(function(e) {
        // enter is pressed
        if(e.keyCode == 13) {
            addEmailRecipientBox($('#fileto').val());
        }
    });
});
</script>

<form id="form1" enctype="multipart/form-data" method="post" action="fs_uploadit.php">
  <div style="width:100%; border-spacing:6px">
    <div class="box" style="vertical-align: top;">
      <div id="fileInfoView">
        <div id="uploadhtml5" style="display:none">
          <input style="display:none; padding-right:6px;" type="file" name="fileToUpload" id="fileToUpload" onchange="fileSelected();" multiple/>
        </div>
        <div id="file_msg" class="validation_msg" style="display: none"><?php echo lang('_INVALID_FILE'); ?></div>
      </div>
      <div id="dragfilestouploadcss" style="overflow:auto;" class="box">
        <div id="filestoupload" style="display:table;width:100%; height:100%;">
          <div id="draganddropmsg" style="text-align:center;display:table-cell; vertical-align:middle;" class="heading"><?php echo lang('_DRAG_AND_DROP'); ?></div>
        </div>
      </div>
      <div style="width:100%; display: table" class="menu">
        <div style="display: table-cell; width: 33%">
          <a id="clearallbtn" href="#" onclick="clearFileBox()" style="cursor:pointer;width:45%;" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false" draggable="false">
            <?php echo lang('_CLEAR_ALL'); ?>
          </a>
        </div>
        <div style="display: table-cell; text-align:center; width: 34%">
          <a href="#" onclick="browse()" style="cursor:pointer;width:50%" class="btn btn-default btn-sm"  aria-disabled="false" draggable="false">
            <?php echo lang('_SELECT_FILES'); ?>
          </a>
        </div>
        <span id="uploadBoxStats" style="text-align: right; width: 33%; display: table-cell; font-size: 0.8em; vertical-align: middle"></span>
      </div>
    </div>
    
    <table id="columns" style="border-spacing: 0">
      <tr>
        <td class="box" style="height:100%; vertical-align:top;width:50%" id="col1">
          <div class="fieldcontainer" id="upload_from">
          <?php
          if (count($senderemail) > 1) {
              echo '<label for="filefrom" class="mandatory">' . lang('_FROM') . ':&nbsp;</label>';
              echo '<select name=\"filefrom\" id=\"filefrom\" style=\"width:98%;font-size:12px;font-family: Verdana, Geneva, sans-serif;\">\n';
              foreach ($senderemail as $email) echo '<option>' . $email . '</option>';
              echo '</select>';
          } else {
              echo '<span class="mandatory">' . lang('_FROM') . ':&nbsp;</span>';
              echo $senderemail[0] . "<input name=\"filefrom\" type=\"hidden\" id=\"filefrom\" value=\"$senderemail[0]\" />\n";
          }
          ?>
          </div>
          
          <div class="fieldcontainer">
            <label id="fileto_label" for="fileto" class="mandatory"><?php echo lang('_TO'); ?>:</label>
            <span id="recipients_box" style="display: none"></span>
            <input name="fileto" type="text" id="fileto" title="<?php echo lang('_EMAIL_SEPARATOR_MSG'); ?>" onblur="addEmailRecipientBox($('#fileto').val());" value="" placeholder="<?php echo lang('_ENTER_TO_EMAIL'); ?>" />
            <div id="fileto_msg" style="display: none" class="validation_msg field">
              <?php echo lang('_INVALID_MISSING_EMAIL'); ?>
            </div>
            <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo lang('_MAXEMAILS').Config::get('max_email_recipients'); ?></div>
          </div>
          
          <div class="fieldcontainer">
            <label for="filesubject"><?php echo lang('_SUBJECT') . ': (' . lang('_OPTIONAL'); ?>)</label>
            <input name="filesubject" type="text" id="filesubject"/>
          </div>
          
          <div class="fieldcontainer">
            <label for="filemessage"><?php echo lang('_MESSAGE') . ': (' . lang('_OPTIONAL'); ?>)</label>
            <textarea name="filemessage" cols="57" rows="3" id="filemessage" style="resize:vertical"></textarea>
          </div>
          
          <div>
            <input type="hidden" id="filevoucheruid" name="filevoucheruid" value="<?php echo $voucherUID; ?>"/>
            <input type="hidden" name="vid" id="vid" value="<?php echo $voucherUID; ?>"/>
            <input type="hidden" name="total" id="total" value=""/>
            <input type="hidden" name="n" id="n" value=""/>
            <input type="hidden" id="filestatus" name="filestatus" value="<?php echo $filestatus; ?>"/>
            <input type="hidden" name="loadtype" id="loadtype" value="standard"/>
            <input type="hidden" name="s-token" id="s-token" value="<?php echo (isset($_SESSION['s-token'])) ? $_SESSION['s-token'] : ''; ?>"/>
          </div>
        </td>
        
        <td style="width:5px"></td>
        <td class="box" style="vertical-align:top; height:100%;width:50%;" id="col2">
          <div id="options">
            <div class="fieldcontainer" id="upload_expirydate">
              <label for="datepicker" id="datepicker_label" class="mandatory"><?php echo lang('_EXPIRY_DATE'); ?>:</label>
              <input id="datepicker" style="width: 70%;" name="datepicker" title="<?php echo lang('_DP_dateFormat'); ?>" onchange="validate_expiry()"/>
              <div id="expiry_msg" class="validation_msg" style="display: none"><?php echo lang('_INVALID_EXPIRY_DATE'); ?></div>
            </div>
            
            <input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date(lang('datedisplayformat'), strtotime('+'.Config::get('default_daysvalid').' day')); ?>"/>
            
            <?php if (Config::get('email_me_copies_display') == 'always') { ?>
            <div class="fieldcontainer">
              <label for="rtnemail"><?php echo lang('_SEND_COPY_EMAILS'); ?></label>
              <input name="rtnemail" type="checkbox" id="rtnemail" style="float:left; width:20px;" <?php echo isChecked(Config::get('email_me_copies_default')); ?> />
            </div>
            <?php } ?>
            
            <?php if (Config::get('upload_complete_email_display') == 'always') { ?>
            <div class="fieldcontainer">
              <label for="email-upload-complete"><?php echo lang('_EMAIL_ME_COMPLETE') ?></label>
              <input type="checkbox" id="email-upload-complete" style="float:left; width:20px;" <?php echo isChecked(Config::get('upload_complete_email_default')); ?> />
            </div>
            <?php } ?>
            
            <?php if (Config::get('inform_download_email_display') == 'always') { ?>
            <div class="fieldcontainer">
              <label for="email-inform-download"><?php echo lang('_EMAIL_ME_ON_DOWNLOAD') ?></label>
              <input id="email-inform-download" type="checkbox" style="float:left; width:20px;" <?php echo isChecked(Config::get('inform_download_email_default')); ?> />
            </div>
            <?php } ?>
            
            <?php if (Config::get('email_me_daily_statistics_display') == 'always') { ?>
            <div class="fieldcontainer">
              <label for="email-inform-daily"><?php echo lang('_EMAIL_DAILY_STATS') ?></label>
              <input id="email-inform-daily" type="checkbox" style="float:left; width:20px;" <?php echo isChecked(Config::get('email_me_daily_statistics_default')); ?>/>
            </div>
            <?php } ?>
            
            <?php if (Config::get('download_confirmation_enabled_display') == 'always') { ?>
            <div class="fieldcontainer">
              <label for="email-enable-confirmation"><?php echo lang('_ENABLE_EMAIL_CONFIRMATION') ?></label>
              <input id="email-enable-confirmation" type="checkbox" style="float:left; width:20px;" <?php echo isChecked(Config::get('download_confirmation_enabled_default')); ?>/>
            </div>
            <?php } ?>
            
            <?php if (Config::get('add_me_to_recipients_display') == 'always') { ?>
            <div class="fieldcontainer">
              <label for="add_me_to_recipients"><?php echo lang('_ADD_ME_TO_RECIPIENTS') ?></label>
              <input id="add_me_to_recipients" type="checkbox" style="float:left; width:20px;" <?php echo isChecked(Config::get('add_me_to_recipients_default')); ?>/>
            </div>
            <?php } ?>
          </div>
          
          <?php if ($functions->advancedSettingsEnabled()) { ?>
          <div class="fieldcontainer">
            <a href="#" onclick="$('#advanced-settings').slideToggle()"><?php echo lang('_ADVANCED_SETTINGS'); ?></a>
          </div>
          
          <div id="advanced-settings" style="display: none;">
            <?php if (Config::get('email_me_copies_display') == 'hidden') { ?>
            <div class="fieldcontainer" >
              <label for="rtnemail"><?php echo lang('_SEND_COPY_EMAILS'); ?></label>
              <input name="rtnemail" type="checkbox" id="rtnemail" style="float:left; width:20px;" <?php echo isChecked(Config::get('email_me_copies_default')); ?>/>
            </div>
            <?php } ?>
            
            <?php if (Config::get('upload_complete_email_display') == 'hidden') { ?>
            <div class="fieldcontainer">
              <label for="email-upload-complete"><?php echo lang('_EMAIL_ME_COMPLETE') ?></label>
              <input type="checkbox" id="email-upload-complete" style="float:left; width:20px;" <?php echo isChecked(Config::get('upload_complete_email_default')); ?>/>
            </div>
            <?php } ?>
            
            <?php if (Config::get('inform_download_email_display') == 'hidden') { ?>
            <div class="fieldcontainer">
              <label for="email-inform-download"><?php echo lang('_EMAIL_ME_ON_DOWNLOAD') ?></label>
              <input id="email-inform-download" type="checkbox" style="float:left; width:20px;" <?php echo isChecked(Config::get('inform_download_email_default')); ?>/>
            </div>
            <?php } ?>
            
            <?php if (Config::get('email_me_daily_statistics_display') == 'hidden') { ?>
            <div class="fieldcontainer">
              <label for="email-inform-daily"><?php echo lang('_EMAIL_DAILY_STATS') ?></label>
              <input id="email-inform-daily" type="checkbox" style="float:left; width:20px;" <?php echo isChecked(Config::get('email_me_daily_statistics_default')); ?>/>
            </div>
            <?php } ?>
            
            <?php if (Config::get('download_confirmation_enabled_display') == 'hidden') { ?>
            <div class="fieldcontainer">
              <label for="email-enable-confirmation"><?php echo lang('_ENABLE_EMAIL_CONFIRMATION') ?></label>
              <input id="email-enable-confirmation" type="checkbox" style="float:left; width:20px;" <?php echo isChecked(Config::get('download_confirmation_enabled_default')); ?>/>
            </div>
            <?php } ?>
            
            <?php if (Config::get('add_me_to_recipients_display') == 'hidden') { ?>
            <div class="fieldcontainer">
              <label for="add_me_to_recipients"><?php echo lang('_ADD_ME_TO_RECIPIENTS') ?></label>
              <input id="add_me_to_recipients" type="checkbox" style="float:left; width:20px;" <?php echo isChecked(Config::get('add_me_to_recipients_default')); ?>/>
            </div>
            <?php } ?>
            
            <?php if (Config::get('terasender') && Config::get('terasenderadvanced')) { ?>
            <div class="fieldcontainer">
              <label for="chunksize"><?php echo lang('_TERA_CHUNKSIZE'); ?></label>
              <input id="chunksize" type="text" value="<?php echo Config::get('terasender_chunksize') ?>"/>
              <br />
            </div>
            
            <div class="fieldcontainer">
              <label for="workerCount"><?php echo lang('_TERA_WORKER_COUNT'); ?></label>
              <input id="workerCount" type="text" value="<?php echo Config::get('terasender_workerCount') ?>"/>
              <br />
            </div>
            
            <div class="fieldcontainer">
              <label for="jobsPerWorker"><?php echo lang('_TERA_JOBS_PER_WORKER'); ?></label>
              <input id="jobsPerWorker" type="text" value="<?php echo Config::get('terasender_jobsPerWorker') ?>"/>
            </div>
            
            <?php } else if (Config::get('terasender') && !Config::get('terasenderadvanced')) { ?>
            <input id="chunksize" type="hidden" value="<?php echo Config::get('terasender_chunksize') ?>" />
            <input id="workerCount" type="hidden" value="<?php echo Config::get('terasender_workerCount') ?>" />
            <input id="jobsPerWorker" type="hidden" value="<?php echo Config::get('terasender_jobsPerWorker') ?>" />
            <?php } ?>
          </div>
          <?php } /* End of advanced settings div. */ ?>
        </td>
      </tr>
    </table>
    
    <div style="clear: both"></div>
    
    <?php if (Config::get('AuP')) { ?>
    <div class="auppanel">
      <label id="aup_label" for="aup" style="cursor:pointer; margin-left: 10px" title="<?php echo lang('_SHOWHIDE'); ?>" onclick="$('#tog').slideToggle();return false;">
        <?php echo lang('_ACCEPTTOC'); ?> [<span style="color: #666666;"><?php echo lang("_SHOWHIDE"); ?></span>]
      </label>
      
      <?php
      $aupChecked = '';
      if (Config::get('AuP_default') || (isset($_SESSION['aup']) && !$authvoucher->aVoucher())) $aupChecked = 'checked="checked"';
      ?>
      <input style="float:left" name="aup" type="checkbox" id="aup" onchange="validate_aup();" <?php echo $aupChecked; ?> value="true"/>
      
      <div id="aup_msg" class="validation_msg" style="display: none"><?php echo lang('_AGREETOC'); ?></div>
      <div id="tog" style="display:none"> <?php echo lang('_AUPTERMS'); ?> </div>
    </div>
    <?php } ?>
    
    <div class="menu mainButton" id="uploadbutton" style="padding-top: 10px;">
      <a href="#" onclick="validate()" class="btn btn-default btn-sm">   <i class="fa fa-cloud-upload fa-lg"></i> <?php echo lang('_SEND'); ?></a>
    </div>
  </div>
</form>

<div id="dialog-cancel" style="display:none" title="<?php echo lang('_CANCEL_UPLOAD'); ?>">
    <?php echo lang('_ARE_YOU_SURE'); ?>
</div>

<div id="suspended-upload" style="display:none;" title="<?php echo lang('_SUSPENDED_UPLOAD'); ?>">
    We detected you have an interrupted upload, would you like to resume?
    <br /><br /><br /><br />
    Note: In order to resume with this upload, you must reselect the files you wish to continue uploading from your computer
</div>
