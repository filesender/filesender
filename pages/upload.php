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
require_once('../www/upload_flash_js.php');

//$flashVARS = "vid=".$voucherUID."&sid=".session_id()."&buttonBrowse=".lang("_BROWSE")."&buttonUpload=".lang("_SEND")."&buttonCancel=".lang("_CANCEL")."&siteURL=".$config["site_url"]."&token=".$token;
?>
<script type="text/javascript" src="js/swfobject.js"></script>
<script type="text/javascript" src="js/upload.js"></script>
<script type="text/javascript">	
    var flashvars = {};
    flashvars.vid = "<?php echo $voucherUID; ?>";
    flashvars.sid = "<?php echo session_id(); ?>";
    flashvars.buttonBrowse = "<?php echo lang('_BROWSE'); ?>";
    flashvars.buttonUpload = "<?php echo lang('_SEND') ?>";
    flashvars.buttonCancel = "<?php echo lang('_CANCEL') ?>";
    flashvars.siteURL = "<?php echo $config['site_url'] ?>";
    flashvars.token = "<?php echo $token; ?>";
    
    var params = {};
    params.wmode = 'transparent';
    params.allowScriptAccess = 'sameDomain';
    
    var attributes = {};
    attributes.id = 'filesenderup';
    attributes.name = 'filesenderup';
    
    swfobject.embedSWF('swf/filesenderup.swf', 'swfup', '300', '40', '6.0.65', 'swf/expressInstall.swf', flashvars, params, attributes); 
</script>

<script type="text/javascript">
    // all default settings
    var vid = '<?php echo isset($_REQUEST['vid']) ? htmlspecialchars($_REQUEST['vid']) : '' ?>';
    
    // start document ready
    $(function() {
        getDatePicker();
        
        // Display flash upload button
        $('#uploadstandard').show();
        
        addButtonText();
        autoCompleteEmails();
    });
</script>

<div id="box">
  <div id="pageheading"><?php echo lang('_UPLOAD') ?></div>
  <form id="form1" enctype="multipart/form-data" method="post" action="fs_uploadit.php">
    <table width="100%" border="0">
      <tr>
        <td width="130" class=" mandatory" id="upload_to"><?php echo lang('_TO') ; ?>:</td>
        <td colspan="2" valign="middle"><input name="fileto" title="<?php echo lang('_EMAIL_SEPARATOR_MSG') ; ?>" type="text" id="fileto" onblur="addEmailRecipientBox($('#fileto').val());"/>
          <div id="fileto_msg" style="display: none" class="validation_msg"><?php echo lang('_INVALID_MISSING_EMAIL'); ?></div>
          <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo lang('_MAXEMAILS'); ?> <?php echo $config['max_email_recipients'] ?>.</div>
          <div id="recipients_box" style="display: none"></div>
        </td>
        
        <td colspan="2" rowspan="4" align="center" valign="top"><table width="100%" border="0">
          <table>
            <tr>
              <td width="25"><img src="images/num_1.png" alt="1" width="25" height="25" hspace="6" border="0" align="left" /></td>
              <td align="left"><span class="forminstructions"><?php echo lang('_STEP1'); ?></span></td>
            </tr>
            
            <tr>
              <td><img src="images/num_2.png" alt="2" width="25" height="25" hspace="6" border="0" align="left" /></td>
              <td align="left"><span class="forminstructions"><?php echo lang('_STEP2'); ?></span></td>
            </tr>
            
            <tr>
              <td><img src="images/num_3.png" alt="3" width="25" height="25" hspace="6" border="0" align="left" /></td>
              <td align="left"><span class="forminstructions"><?php echo lang('_STEP3'); ?></span></td>
            </tr>
            
            <tr>
              <td><img src="images/num_4.png" alt="4" width="25" height="25" hspace="6" border="0" align="left" /></td>
              <td align="left"><span class="forminstructions"><?php echo lang('_STEP4'); ?></span></td>
            </tr>
            
            <tr>
              <td colspan="2" align="center">&nbsp;</td>
            </tr>
          </table>
        </td>
      </tr>
      
      <tr>
        <td class=" mandatory" id="upload_from"><?php echo lang('_FROM'); ?>:</td>
        <td colspan="2">
        <?php if ( count($senderemail) > 1 ) { ?>
          <select name="filefrom" id="filefrom">
          <?php foreach($senderemail as $email) { ?>
            <option><?php echo $email ?></option>
          <?php } ?>
          </select>
        <?php } else { ?>
          <div id="visible_filefrom"><?php echo $senderemail[0] ?></div>
          <input name="filefrom" type="hidden" id="filefrom" value="<?php echo $senderemail[0] ?>" />
        <?php } ?>
        </td>
      </tr>
      
      <tr>
        <td class="" id="upload_subject"><?php echo lang('_SUBJECT'); ?>: (<?php echo lang('_OPTIONAL'); ?>)</td>
        <td colspan="2"><input name="filesubject" type="text" id="filesubject" /></td>
      </tr>
      
      <tr>
        <td class="" id="upload_message"><?php echo lang('_MESSAGE'); ?>: (<?php echo lang('_OPTIONAL'); ?>)</td>
        <td colspan="2"><textarea name="filemessage" cols="57" rows="4" id="filemessage"></textarea></td>
      </tr>
      
      <tr>
        <td class=" mandatory" id="upload_expirydate"><?php echo lang('_EXPIRY_DATE'); ?>:
        <input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date(lang('datedisplayformat'),strtotime("+".$config['default_daysvalid']." day"));?>" /></td>
        
        <td colspan="2">
          <input id="datepicker" name="datepicker" title="<?php echo lang('_DP_dateFormat'); ?>" onchange="validate_expiry()" />
          <div id="expiry_msg" class="validation_msg" style="display: none"><?php echo lang('_INVALID_EXPIRY_DATE'); ?></div>
        </td>
        
        <td colspan="2" align="center" valign="top"></td>
      </tr>
      
      <tr>
        <td class=" mandatory"><div id="selectfile"><?php echo lang('_SELECT_FILE'); ?>:</div></td>
        <td colspan="2"><div id="uploadstandard" style="display:none">
          <div id="swfup">
            <p>
              <div id="errmessage" align="center">
                <br />
                This application requires Flash for uploading files.<br /><br />
                To install Flash Player go to <a href="http://www.adobe.com" target="_blank">www.adobe.com<a>.<br /> <br />
              </div>
            </p>
          </div>
          
          <div id="uploadstandardspinner" style="padding-top:10px;display:none"><img src="images/ajax-loader-sm.gif" alt="" border="0" align="left" style="padding-right:6px" /><?php echo lang("_UPLOADING_WAIT"); ?></div>
          <br />
          
          <div id="file_msg" class="validation_msg" style="display: none"><?php echo lang('_INVALID_FILE'); ?></div>
          <div id="extension_msg" class="validation_msg" style="display: none"><?php echo lang('_INVALID_FILE_EXT'); ?></div>
        </td>
        
        <td colspan="2" align="center" valign="top"><div id="html5text"></div></td>
      </tr>
      
      <tr id="fileInfoView" style="display:none">
        <td></td>
        
        <td colspan="2">
          <div>
            <div id="fileName"></div>
            <div id="fileSize"></div>
          </div>
        </td>
        
        <td colspan="2" align="center" valign="top">&nbsp;</td>
      </tr>
      
      <?php if ($config['AuP']) {?>
      <tr>
        <td class=""></td>
        <td><input name="aup" type="checkbox" id="aup" onchange="validate_aup()" <?php echo ($config['AuP_default'] ) ? 'checked="checked"' : ''; ?> <?php echo (isset($_SESSION['aup']) && !$authvoucher->aVoucher() ) ? 'checked="checked"' : ''; ?> value="true" /></td>
        
        <td>
          <div id="aup_label" onclick="$('#tog').slideToggle();return false;" style="cursor:pointer;"><?php echo lang('_ACCEPTTOC'); ?> [<font color="#666666"><?php echo lang('_SHOWHIDE'); ?></font>]</div>
          <div id="aup_msg" class="validation_msg" style="display: none"><?php echo lang('_AGREETOC'); ?></div>
          <div id="tog" style="display:none"> <?php echo lang('_AUPTERMS'); ?> </div>
        </td>
        
        <td colspan="2" align="center" valign="top">&nbsp;</td>
      </tr>
      <?php } ?>
      
      <tr>
        <td></td>
        
        <td colspan="2">
          <div class="menu" id="uploadbutton" style="display:none"><a href="#" onclick="validate()"><?php echo lang('_SEND'); ?></a></div>
        </td>
      </tr>
    </table>
    
    <input type="hidden" id="filevoucheruid" name="filevoucheruid" value="<?php echo $voucherUID; ?>" />
    <input type="hidden" name="vid" id="vid" value="<?php echo $voucherUID; ?>" />
    <input type="hidden" name="total" id="total" value="" />
    <input type="hidden" name="n" id="n" value="" />
    <input type="hidden" id="filestatus" name="filestatus" value="<?php echo $filestatus; ?>" />
    <input type="hidden" name="loadtype" id="loadtype" value="standard" />
    <input type="hidden" name="s-token" id="s-token" value="<?php echo (isset($_SESSION['s-token'])) ?  $_SESSION['s-token'] : '' ?>" />
  </form>
</div>
