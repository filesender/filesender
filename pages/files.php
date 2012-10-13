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
 * MyFiles Page
 * Display details about a users files
 * Allow a user to re-send a file 
 * Allow a user to Forward a file	
 * ---------------------------------
 * 
 */

// Check for delete/resent/added actions and report back
if(isset($_REQUEST["a"]) && isset($_REQUEST["id"])) 
{
	// validate id 
	if(ensureSaneFileUid($_REQUEST["id"])) {
		$myfileData = $functions->getVoucherData($_REQUEST['id']);
		if($_REQUEST["a"] == "del")
		{
			// check if user is authenticated and allowed to delete this file
			if( $isAuth && $userdata["saml_uid_attribute"] == $myfileData["fileauthuseruid"])
			{
				if($functions->deleteFile($myfileData["fileid"]))
				{
					echo "<div id='message'>".lang("_FILE_DELETED")."</div>";
				}
			} else {
			// log auth user tried to delete a file they do not have access to
			logEntry("Permission denied - attempt to delete ".$myfileData["fileuid"],"E_ERROR");
			// notify - not deleted - you do not have permission	
			echo "<div id='message'>".lang("_PERMISSION_DENIED")."</div>";
			}
		}
		if($_REQUEST["a"] == "resend")
		{
			// check if user is authenticated and allowed to resend this file
			if( $isAuth && $userdata["saml_uid_attribute"] == $myfileData["fileauthuseruid"])
			{
				if($sendmail->sendEmail($myfileData ,$config['fileuploadedemailbody']))
				{
					echo "<div id='message'>".lang("_MESSAGE_RESENT")."</div>";
				}
			} else {
			// log auth user tried to resend email for a file they do not have access to
			logEntry("Permission denied - attempt to resend ".$myfileData["fileuid"],"E_ERROR");
			// notify - not resent - you do not have permission	
			echo "<div id='message'>".lang("_PERMISSION_DENIED")."</div>";		
			}
		}
	} else {
		echo "<div id='message'>".lang("_INVALID_FILEVOUCHERID")."</div>";	
	}
}
if(isset($_REQUEST["a"]) && $_REQUEST["a"] == "added")
{
	// display the add box
	echo "<div id='message'>".lang("_EMAIL_SENT").".</div>";
}
foreach ($errorArray as $message) 
		{
		if($message == "err_emailnotsent")
		{
			echo '<div id="message">'.lang("_ERROR_SENDING_EMAIL").'</div>';
		}
		}
// Get list of user files and display page
$filedata = $functions->getUserFiles();
$json_o=json_decode($filedata,true);

?>
<script type="text/javascript">
//<![CDATA[
	var selectedFile = ""; // file uid selected when deleteting
	// set default maximum date for date datepicker
	var maximumDate = <?php echo (time()+($config['default_daysvalid']*86400))*1000 ?>;
	var minimumDate = <?php echo (time()+86400)*1000 ?>;
	var maxEmailRecipients = <?php echo $config['max_email_recipients'] ?>;
	var datepickerDateFormat = '<?php echo lang('_DP_dateFormat'); ?>';
	
	$(function() {
		// initialise datepicker
		$("#datepicker" ).datepicker({ minDate: new Date(minimumDate), maxDate: new Date(maximumDate),altField: "#fileexpirydate", altFormat: "d-m-yy" });
		$("#datepicker" ).datepicker( "option", "dateFormat", "<?php echo lang('_DP_dateFormat'); ?>" );
		$("#datepicker").datepicker("setDate", new Date(maximumDate));
		$('#ui-datepicker-div').css('display','none');
		// set datepicker language
		$.datepicker.setDefaults({
		closeText: '<?php echo lang("_DP_closeText"); ?>',
		prevText: '<?php echo lang("_DP_prevText"); ?>',
		nextText: '<?php echo lang("_DP_nextText"); ?>',
		currentText: "<?php echo lang("_DP_currentText"); ?>",
		monthNames: <?php echo lang("_DP_monthNames"); ?>,
		monthNamesShort: <?php echo lang("_DP_monthNamesShort"); ?>,
		dayNames: <?php echo lang("_DP_dayNames"); ?>,
		dayNamesShort: <?php echo lang("_DP_dayNamesShort"); ?>,
		dayNamesMin: <?php echo lang("_DP_dayNamesMin"); ?>,
		weekHeader: '<?php echo lang("_DP_weekHeader"); ?>',
		dateFormat: '<?php echo lang("_DP_dateFormat"); ?>',
		firstDay: <?php echo lang("_DP_firstDay"); ?>,
		isRTL: <?php echo lang("_DP_isRTL"); ?>,
		showMonthAfterYear: <?php echo lang("_DP_showMonthAfterYear"); ?>,
		yearSuffix: '<?php echo lang("_DP_yearSuffix"); ?>'});
	
		// stripe every second row in the tables
		$("#myfiles tr:odd").addClass("altcolor");
		
		// delete modal dialog box
		$("#dialog-delete").dialog({ autoOpen: false, height: 180, modal: true,
			buttons: {
				'cancelBTN': function() {
				$( this ).dialog( "close" );
				},
				'deleteBTN': function() { 
				deletefile();
				$( this ).dialog( "close" );
				}
			}
		});
		// resend email modal dialog box
		$("#dialog-resend").dialog({ autoOpen: false, height: 180, modal: true,
			buttons: {
				'cancelsendBTN': function() {
				$( this ).dialog( "close" );
				},
				'sendBTN': function() { 
				resend();
				$( this ).dialog( "close" );
				}
			}
		});
		
		// default auth error dialogue
		$("#dialog-autherror").dialog({ autoOpen: false, height: 240,width: 350, modal: true,title: "",		
		buttons: {
			'<?php echo lang("_OK") ?>': function() {
				location.reload(true);
				}
			}
		})
		
		$('.ui-dialog-buttonpane button:contains(cancelBTN)').attr("id","btn_cancel");            
		$('#btn_cancel').html('<?php echo lang("_NO") ?>');  
		$('.ui-dialog-buttonpane button:contains(deleteBTN)').attr("id","btn_delete");            
		$('#btn_delete').html('<?php echo lang("_YES") ?>');  
		$('.ui-dialog-buttonpane button:contains(cancelsendBTN)').attr("id","btn_cancelsend");            
		$('#btn_cancelsend').html('<?php echo lang("_NO") ?>');  
		$('.ui-dialog-buttonpane button:contains(sendBTN)').attr("id","btn_send");            
		$('#btn_send').html('<?php echo lang("_YES") ?>');  
		// add new recipient modal dialog box
		$("#dialog-addrecipient").dialog({ autoOpen: false, height: 410,width:650, modal: true,
			buttons: {
				'addrecipientcancelBTN': function() {
					// clear form
					$("#fileto").val("");
					$("#datepicker").datepicker("setDate", new Date(maximumDate));
					$("#filesubject").val("");
					$("#filemessage").val("");
					$( this ).dialog( "close" );
				},
				'addrecipientsendBTN': function() { 
				// calidate form before sending
				if(validateForm())
				{
				// post form1 as json
				var query = $("#form1").serializeArray(), json = {};
				for (i in query) { json[query[i].name] = query[i].value; } 
				
				$.ajax({
  				type: "POST",
				url: "fs_upload.php?type=addRecipient",
				data: {myJson:  JSON.stringify(json)}
				,success:function( data ) {
				if(data == "") {
				alert("No response from server");
				return;	
				}
				if(data == "ErrorAuth")
				{
					$("#dialog-autherror").dialog("open");
					return;			
				}
				var data =  parseJSON(data);
				if(data.errors)
				{
				$.each(data.errors, function(i,result){
				if(result == "err_token") { $("#dialog-tokenerror").dialog("open");} // token missing or error
				if(result == "err_tomissing") { $("#fileto_msg").show();} // missing email data
				if(result == "err_expmissing") { $("#expiry_msg").show();} // missing expiry date
				if(result == "err_exoutofrange") { $("#expiry_msg").show();} // expiry date out of range
				if(result == "err_invalidemail") { $("#fileto_msg").show();} // 1 or more emails invalid
				if(result == "err_emailnotsent") {window.location.href="index.php?s=emailsenterror";} //
				})
				} else {
				if(data.status && data.status == "complete")
				{
				// done
				window.location.href="index.php?s=files&a=added";
				}
				}
				},error:function(xhr,err){
				// error function to display error message e.g.404 page not found
				ajaxerror(xhr.readyState,xhr.status,xhr.responseText);
				}
				});
				}
				}
			}
		});
		
		$('.ui-dialog-buttonpane button:contains(addrecipientcancelBTN)').attr("id","btn_addrecipientcancel");            
		$('#btn_addrecipientcancel').html('<?php echo lang("_CANCEL") ?>')  
		$('.ui-dialog-buttonpane button:contains(addrecipientsendBTN)').attr("id","btn_addrecipientsend");            
		$('#btn_addrecipientsend').html('<?php echo lang("_SEND") ?>')  
		
	});
	
	// validate form beofre sending
	function validateForm()
	{
		// remove previouse vaildation messages
		$("#fileto_msg").hide();
		$("#expiry_msg").hide();
		var validate = true;
		if(!validate_fileto() ){validate = false;};		// validate emails
		if(!validate_expiry() ){validate = false;};		// check date
		return validate;
	}
	function resend(uid)
	{
		window.location.href="index.php?s=files&a=resend&id=" + selectedFile;
	}
	
	function confirmResend(vid)
		{
			// confirm deletion of selected file
			selectedFile = vid;
			$("#dialog-resend" ).dialog( "open" );
		}
	function deletefile()
		{
		// reload page to delete selected file
		// should add a tick box to delete multiple selected files	
		window.location.href="index.php?s=files&a=del&id=" + selectedFile;
		}
	
	function confirmdelete(vid)
		{
			// confirm deletion of selected file
			selectedFile = vid;
			$("#dialog-delete" ).dialog( "open" );
		}
		
	function openAddRecipient(vid,filename,filesize,from, subject, message)
	{
		// populate form and open add-recipient modal form
		$("#form1").attr("action", "index.php?s=files&a=add&id=" + vid );
		$("#filevoucheruid").val(vid);
		$("#filefrom").html(decodeURIComponent(from));
		$("#filename").html(decodeURIComponent(filename));
		$("#filesubject").val(decodeURIComponent(subject));
		$("#filemessage").val(decodeURIComponent(message));
		$("#filesize").html(readablizebytes(filesize));
		$("#fileto").val("");
		$("#datepicker").datepicker("setDate", new Date(maximumDate));
		// clear error messages
		$("#expiry_msg").hide();
		$("#file_msg").hide();
		$("#fileto_msg").hide();
		$("#maxemails_msg").hide();
		$("#dialog-addrecipient" ).dialog( "open" );
		
	}
//]]>
</script>

<div id="box"> <?php echo '<div id="pageheading">'.lang("_MY_FILES").'</div>'; ?>
  <div id="tablediv">
    <table id="myfiles" width="750" border="0" cellspacing="1" style="table-layout:fixed;">
      <tr class="headerrow">
        <td width="18">&nbsp;</td>
        <td width="18">&nbsp;</td>
        <td class="HardBreak" id="myfiles_header_to"><strong><?php echo lang("_TO"); ?></strong></td>
        <td class="HardBreak" id="myfiles_header_from"><strong><?php echo lang("_FROM"); ?></strong></td>
        <td class="HardBreak" id="myfiles_header_filename"><strong><?php echo lang("_FILE_NAME"); ?></strong></td>
        <td class="HardBreak" id="myfiles_header_size"><strong><?php echo lang("_SIZE"); ?></strong></td>
        <td class="HardBreak" id="myfiles_header_subject"><strong><?php echo lang("_SUBJECT") ; ?></strong></td>
        <td class="HardBreak" id="myfiles_header_message"><strong><?php echo lang("_MESSAGE") ; ?></strong></td>
        <td class="HardBreak" id="myfiles_header_created"><strong><?php echo lang("_CREATED"); ?></strong></td>
        <td class="HardBreak" id="myfiles_header_expiry"><strong><?php echo lang("_EXPIRY"); ?></strong></td>
        <td width="18">&nbsp;</td>
      </tr>
      <?php 
$i = 0;	  
if(sizeof($json_o) > 0)
{
foreach($json_o as $item) {
	$i += 1; // counter for file id's
	$onClick = "'" .$item['filevoucheruid'] ."'";
   echo '<tr><td valign="top"> <div id="btn_resendemail_'.$i.'"><img src="images/email_go.png" alt="" title="'.lang("_RE_SEND_EMAIL").'"  style="cursor:pointer;"  onclick="confirmResend('.$onClick.')" /></div></td><td valign="top"><img id="btn_addrecipient_'.$i.'" src="images/email_add.png" alt="" title="'.lang("_NEW_RECIPIENT").'" onclick="openAddRecipient('."'".$item['filevoucheruid']."','".rawurlencode(utf8tohtml($item['fileoriginalname'],true)) ."','".$item['filesize'] ."','".rawurlencode($item['filefrom'])."','".rawurlencode($item['filesubject'])."','".rawurlencode($item['filemessage'])."'" .');"  style="cursor:pointer;" /></td>';
   if($item['fileto'] == $attributes["email"])
   {
   echo "<td class='HardBreak' valign='top'>".lang("_ME")."</td>";
   } else {
   echo "<td class='HardBreak'>" .$item['fileto'] . "</td>";
   }
    if($item['filefrom'] == $attributes["email"])
   {
   echo "<td class='HardBreak'>".lang("_ME")."</td>";
   } else {
   echo "<td class='HardBreak'>" .$item['filefrom'] . "</td>";
   }
   echo "<td class='HardBreak'><a id='link_downloadfile_".$i."' href='download.php?vid=". $item["filevoucheruid"]."' target='_blank'>" .utf8tohtml($item['fileoriginalname'],TRUE). "</a></td>";
   echo "<td>" .formatBytes($item['filesize']). "</td><td  class='HardBreak'>".utf8tohtml($item['filesubject'],TRUE). "</td><td>";
   if($item['filemessage'] != "")
   {
   echo "<img src='images/page_white_text_width.png' border='0' alt='' title='".utf8tohtml($item['filemessage'],TRUE). "' />";
   }
   echo "</td><td>" .date($lang['datedisplayformat'],strtotime($item['filecreateddate'])) . "</td><td>" .date($lang['datedisplayformat'],strtotime($item['fileexpirydate'])) . "</td><td  valign='top'  width='22'><div style='cursor:pointer;'><img id='btn_deletevoucher_".$i."' onclick='confirmdelete(".'"' .$item['filevoucheruid'] . '")'. "' src='images/shape_square_delete.png' alt='' title='".lang("_DELETE_FILE")."' /></div></td></tr>"; //etc
   }
}
?>
</table>
<?php
  if($i==0)
	{
		echo lang("_NO_FILES");
	}
?>
  </div>
</div>
<div id="dialog-delete" title="<?php echo  lang("_DELETE_FILE"); ?>">
<p><?php echo lang("_CONFIRM_DELETE_FILE");?></p>
</div>
<div id="dialog-resend" title="<?php echo  lang("_RE_SEND_EMAIL"); ?>">
<p><?php echo lang("_CONFIRM_RESEND_EMAIL");?></p>
</div>
<div id="dialog-addrecipient" style="display:none" title="<?php echo  lang("_NEW_RECIPIENT"); ?>">
  <form id="form1" name="form1" enctype="multipart/form-data" method="post" action="">
    <table  width="600" border="0">
      <tr>
        <td width="100" class="formfieldheading mandatory" id="files_to"><?php echo  lang("_TO"); ?>:</td>
        <td width="400" valign="middle"><input name="fileto" title="<?php echo  lang("_EMAIL_SEPARATOR_MSG"); ?>" type="text" id="fileto" size="60" onchange="validate_fileto()" />
          <div id="fileto_msg" style="display: none" class="validation_msg"><?php echo lang("_INVALID_MISSING_EMAIL"); ?></div>
          <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo lang("_MAXEMAILS"); ?> <?php echo $config['max_email_recipients'] ?>.</div>
          </td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory" id="files_from"><?php echo lang("_FROM"); ?>:</td>
        <td><div id="filefrom"></div></td>
      </tr>
      <tr>
        <td class="formfieldheading" id="files_subject"><?php echo lang("_SUBJECT"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
        <td><input name="filesubject" type="text" id="filesubject" size="60" /></td>
      </tr>
      <tr>
        <td class="formfieldheading" id="files_message"><?php echo lang("_MESSAGE"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
        <td><textarea name="filemessage" cols="57" rows="4" id="filemessage"></textarea></td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory" id="files_expiry"><?php echo lang("_EXPIRY_DATE"); ?>:
          <input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date($lang['datedisplayformat'],strtotime("+".$config['default_daysvalid']." day"));?>" /></td>
        <td><input id="datepicker" name="datepicker" onchange="validate_expiry()" title="<?php echo lang('_DP_dateFormat'); ?>" />
          <div id="expiry_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_EXPIRY_DATE"); ?></div></td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory" id="files_to_be_resent"><?php echo lang("_FILE_TO_BE_RESENT"); ?>:</td>
        <td><div id="filename"></div></td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory" id="files_size"><?php echo lang("_SIZE"); ?>:</td>
        <td><div id="filesize"></div></td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"></td>
        <td><div id="file_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_FILE"); ?></div><div id="extension_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_FILE_EXT"); ?></div></td>
      </tr>
    </table>
    <input name="filevoucheruid" type="hidden" id="filevoucheruid" /><br />
	<input type="hidden" name="s-token" id="s-token" value="<?php echo (isset($_SESSION["s-token"])) ?  $_SESSION["s-token"] : "";?>" />
  	</form>
</div>
<div id="dialog-autherror" title="<?php echo lang($lang["_MESSAGE"]); ?>" style="display:none"><?php echo lang($lang["_AUTH_ERROR"]); ?></div>
