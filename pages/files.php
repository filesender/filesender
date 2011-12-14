<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2011, AARNet, HEAnet, SURFnet, UNINETT
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
 * *	Neither the name of AARNet, HEAnet, SURFnet and UNINETT nor the
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
 * ---------------------------------
 * 
 */

if(isset($_REQUEST["a"]) && isset($_REQUEST["id"])) 
{
// validate id 
if(	ensureSaneFileUid($_REQUEST["id"])) {
$myfileData = $functions->getVoucherData($_REQUEST['id']);
//$myfileData = $myfileData[0];
if($_REQUEST["a"] == "del" )
{
if($functions->deleteFile($myfileData["fileid"]))
{
echo "<div id='message'>".lang("_FILE_DELETED")."</div>";
}
}

if($_REQUEST["a"] == "resend")
{
$sendmail->sendEmail($myfileData ,$config['fileuploadedemailbody']);
echo "<div id='message'>".lang("_MESSAGE_RESENT")."</div>";
}

if($_REQUEST["a"] == "added")
{

// display the add box
echo "<div id='message'>".lang("_EMAIL_SENT").".</div>";
}
} else {
echo "<div id='message'>".lang("_INVALID_FILEVOUCHERID")."</div>";	
}
}

$filedata = $functions->getUserFiles();
$json_o=json_decode($filedata,true);

?>
<script type="text/javascript">
	
	var selectedFile = ""; // file uid selected when deleteting
	// set default maximum date for date datepicker
	var maximumDate= '<?php echo $config['default_daysvalid']?>';
	var maxEmailRecipients = <?php echo $config['max_email_recipients'] ?>;
	var datepickerDateFormat = '<?php echo lang('_DP_dateFormat'); ?>';
	
	$(function() {
		// initialise datepicker
		$("#datepicker" ).datepicker({ minDate: 1, maxDate: "+"+maximumDate+"D",altField: "#fileexpirydate", altFormat: "d-m-yy" });
		$("#datepicker" ).datepicker( "option", "dateFormat", "<?php echo lang('_DP_dateFormat'); ?>" );
		$("#datepicker").datepicker('setDate', new Date()+maximumDate);
		
		// set datepicker language
		$.datepicker.setDefaults({
		closeText: '<?php echo lang("_DP_closeText"); ?>',
		prevText: '<?php echo lang("_DP_prevText"); ?>',
		nextText: '<?php echo lang("_DP_nextText"); ?>',
		currentText: '<?php echo lang("_DP_currentText"); ?>',
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
		$("#myfiles tr:odd").not(":first").addClass("altcolor");
		
		// delete modal dialog box
		$("#dialog-delete").dialog({ autoOpen: false, height: 180, modal: true,
			buttons: {
				'<?php echo lang("_CANCEL") ?>': function() {
				$( this ).dialog( "close" );
				},
				'<?php echo lang("_DELETE") ?>': function() { 
				deletefile();
				$( this ).dialog( "close" );
				}
			}
		});
		
		// add new recipient modal dialog box
		$("#dialog-addrecipient").dialog({ autoOpen: false, height: 410,width:650, modal: true,
			buttons: {
				'<?php echo lang("_CANCEL") ?>': function() {
					// clear form
					$("#filesubject").val("");
					$("#filemessage").val("");
					$( this ).dialog( "close" );
				},
				'<?php echo lang("_SEND") ?>': function() { 
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
				}).success(function( data ) {
				if(data == "") {
				alert("No response from server");
				return;	
				}
				var data =  JSON.parse(data);
				if(data.errors)
				{
				$.each(data.errors, function(i,result){
				if(result == "err_tomissing") { $("#fileto_msg").show();} // missing email data
				if(result == "err_expmissing") { $("#expiry_msg").show();} // missing expiry date
				if(result == "err_exoutofrange") { $("#expiry_msg").show();} // expiry date out of range
				if(result == "err_invalidemail") { $("#fileto_msg").show();} // 1 or more emails invalid
				})
				}
				if(data.status && data.status == "complete")
				{
				// done
				window.location.href="index.php?s=files&a=added";
				}
				});
				}
				}
			}
		});
		
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
		$("#filefrom").html(from);
		$("#filename").html(filename);
		$("#filesubject").val(decodeURIComponent(subject));
		$("#filemessage").html(decodeURIComponent(message));
		$("#filesize").html(readablizebytes(filesize));
		$("#dialog-addrecipient" ).dialog( "open" );
		
	}
	
</script>

<div id="box"> <?php echo '<div id="pageheading">'.lang("_MY_FILES").'</div>'; ?>
  <div id="tablediv">
    <table id="myfiles" width="750" border="0" cellspacing="1" style="table-layout:fixed;">
      <tr class="headerrow">
        <td width="18">&nbsp;</td>
        <td width="18">&nbsp;</td>
        <td><strong><?php echo lang("_TO"); ?></strong></td>
        <td><strong><?php echo lang("_FROM"); ?></strong></td>
        <td><strong><?php echo lang("_FILE_NAME"); ?></strong></td>
        <td width="60"><strong><?php echo lang("_SIZE"); ?></strong></td>
        <td><strong><?php echo lang("_SUBJECT") ; ?></strong></td>
        <td width="16"><strong></strong></td>
        <td width="80"><strong><?php echo lang("_CREATED"); ?></strong></td>
        <td width="80"><strong><?php echo lang("_EXPIRY"); ?></strong></td>
        <td width="18">&nbsp;</td>
      </tr>
      <?php 
if(sizeof($json_o) > 0)
{
foreach($json_o as $item) {
   echo '<tr><td valign="top"> <a href="index.php?s=files&a=resend&id=' .$item['filevoucheruid'] . '"><img src="images/email_go.png" title="'.lang("_RE_SEND_EMAIL").'"></a></td><td valign="top"><img src="images/email_add.png" title="'.lang("_NEW_RECIPIENT").'" onclick="openAddRecipient('."'".$item['filevoucheruid']."','".$item['fileoriginalname'] ."','".$item['filesize'] ."','".$item['filefrom']."','".rawurlencode($item['filesubject'])."','".rawurlencode($item['filemessage'])."'" .');"  style="cursor:pointer;"></td>';
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
   echo "<td class='HardBreak'><a href='download.php?vid=". $item["filevoucheruid"]."' target='_blank'>" .utf8tohtml($item['fileoriginalname'],TRUE). "</a></td>";
   echo "<td>" .formatBytes($item['filesize']). "</td><td  class='HardBreak'>".utf8tohtml($item['filesubject'],TRUE). "</td><td>";
   if($item['filemessage'] != "")
   {
   echo "<img src='images/page_white_text_width.png' border='0' title='".utf8tohtml($item['filemessage'],TRUE). "'>";
   }
   echo "</td><td>" .date($lang['datedisplayformat'],strtotime($item['filecreateddate'])) . "</td><td>" .date($lang['datedisplayformat'],strtotime($item['fileexpirydate'])) . "</td><td  valign='top'  width='22'><div style='cursor:pointer;'><img onclick='confirmdelete(".'"' .$item['filevoucheruid'] . '")'. "' src='images/shape_square_delete.png' title='".lang("_DELETE_FILE")."' ></div></td></tr>"; //etc
   }
} else {
	echo "<tr><td colspan='7'>".lang("_NO_FILES")."</td></tr>";
}
?>
    </table>
  </div>
</div>
<div id="dialog-delete" title="<?php echo  lang("_DELETE_FILE"); ?>">
<p><?php echo lang("_CONFIRM_DELETE_FILE");?></p>
</div>
<div id="dialog-addrecipient" title="<?php echo  lang("_NEW_RECIPIENT"); ?>">
  <form id="form1" name="form1" enctype="multipart/form-data" method="POST" action="">
    <table  width="600" border="0">
      <tr>
        <td width="100" class="formfieldheading mandatory"><?php echo  lang("_TO"); ?>:</td>
        <td width="400" valign="middle"><input name="fileto" title="<?php echo  lang("_EMAIL_SEPARATOR_MSG"); ?>" type="text" id="fileto" size="60" onchange="validate_fileto()" />
          <div id="fileto_msg" style="display: none" class="validation_msg"><?php echo lang("_INVALID_MISSING_EMAIL"); ?></div>
          <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo lang("_MAXEMAILS"); ?> <?php echo $config['max_email_recipients'] ?>.</div>
          </td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"><?php echo lang("_FROM"); ?>:</td>
        <td><div id="filefrom" name="filefrom"></div></td>
      </tr>
      <tr>
        <td class="formfieldheading"><?php echo lang("_SUBJECT"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
        <td><input name="filesubject" type="text" id="filesubject" size="60" /></td>
      </tr>
      <tr>
        <td class="formfieldheading"><?php echo lang("_MESSAGE"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
        <td><textarea name="filemessage" cols="57" rows="4" id="filemessage"></textarea></td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"><?php echo lang("_EXPIRY_DATE"); ?>:
          <input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date($config['datedisplayformat'],strtotime("+".$config['default_daysvalid']." day"));?>"/></td>
        <td><input id="datepicker" name="datepicker" onchange="validate_expiry()" title="<?php echo lang('_DP_dateFormat'); ?>">
          </input>
          <div id="expiry_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_EXPIRY_DATE"); ?></div></td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"><?php echo lang("_FILE_TO_BE_RESENT"); ?>:</td>
        <td><div id="filename" name="filename"></div></td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"><?php echo lang("_SIZE"); ?>:</td>
        <td><div id="filesize" name="filesize"></div></td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"></td>
        <td><div id="file_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_FILE"); ?></div></td>
      </tr>
    </table>
    <input name="filevoucheruid" type="hidden" id="filevoucheruid"/>
  </form>
</div>
