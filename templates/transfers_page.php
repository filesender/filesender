<div class="box">
    <h1>{tr:transfers_page}</h1>
    
    <?php Template::display('transfers_table', array('transfers' => Transfer::fromUser(Auth::user()))) ?>
</div>

<div id="dialog-addrecipient" style="display:none" title="<?php echo Lang::tr('_NEW_RECIPIENT'); ?>">
  <form id="form1" name="form1" enctype="multipart/form-data" method="post" action="#">
    <input type="hidden" name="a" value="add" />
    <input id="trackingCode" type="hidden" name="tc" value="" />
    <table  style="width: 600px; border: 0">
      <tr>
        <td class="formfieldheading mandatory tblmcw3" id="files_to"><?php echo Lang::tr('_TO'); ?>:</td>
        <td style="text-align: center">
          <div id="recipients_box" style="display: none"></div>
          <input name="fileto" title="<?php echo  Lang::tr('_EMAIL_SEPARATOR_MSG'); ?>" type="text" id="fileto" size="60" onblur="addEmailRecipientBox($('#fileto').val());" />
          <div id="fileto_msg" style="display: none" class="validation_msg"><?php echo Lang::tr('_INVALID_MISSING_EMAIL'); ?></div>
          <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo Lang::tr('_MAXEMAILS'); ?> <?php echo Config::get('max_email_recipients') ?>.</div>
        </td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory" id="files_from"><?php echo Lang::tr('_FROM'); ?>:</td>
        <td>
          <div id="filefrom"></div>
        </td>
      </tr>
      <tr>
        <td class="formfieldheading" id="files_subject"><?php echo Lang::tr('_SUBJECT'); ?>: (<?php echo Lang::tr("_OPTIONAL"); ?>)</td>
        <td><input name="filesubject" type="text" id="filesubject" size="60" /></td>
      </tr>
      <tr>
        <td class="formfieldheading" id="files_message"><?php echo Lang::tr('_MESSAGE'); ?>: (<?php echo Lang::tr("_OPTIONAL"); ?>)</td>
        <td><textarea name="filemessage" cols="57" rows="4" id="filemessage"></textarea></td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory" id="files_expiry">
          <?php echo Lang::tr('_EXPIRY_DATE'); ?>: <input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date(Lang::tr('date_format'), strtotime("+".Config::get('default_daysvalid')." day"));?>" />
        </td>
        <td>
          <input id="datepicker" name="datepicker" onchange="validate_expiry()" title="<?php echo Lang::tr('_DP_dateFormat'); ?>" />
          <div id="expiry_msg" class="validation_msg" style="display: none"><?php echo Lang::tr('_INVALID_EXPIRY_DATE'); ?></div>
        </td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"></td>
        <td>
          <div id="file_msg" class="validation_msg" style="display: none"><?php echo Lang::tr('_INVALID_FILE'); ?></div>
          <div id="extension_msg" class="validation_msg" style="display: none"><?php echo Lang::tr('_INVALID_FILE_EXT'); ?></div>
        </td>
      </tr>
    </table>
    <input name="filevoucheruid" type="hidden" id="filevoucheruid" /><br />
    <input type="hidden" name="s-token" id="s-token" value="<?php echo (isset($_SESSION["s-token"])) ?  $_SESSION["s-token"] : "";?>" />
  </form>
    
        
</div>
