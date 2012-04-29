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
 * Vouchers Page
 * ---------------------------------
 * 
 */
 ?>
<script type="text/javascript">
//<![CDATA[
var maximumDate = <?php echo (time()+($config['default_daysvalid']*86400))*1000 ?>;
var minimumDate = <?php echo (time()+86400)*1000 ?>;
var maxEmailRecipients = <?php echo $config['max_email_recipients'] ?>;
var datepickerDateFormat = '<?php echo lang('_DP_dateFormat'); ?>';
var selectedVoucher = "";
var nameLang = '<?php echo lang("_FILE_NAME"); ?>'
var sizeLang = '<?php echo lang("_SIZE"); ?>'

$(function() {
	//$("#fileto_msg").hide();
	$("#expiry_msg").hide();
	
	// stripe every second row in the tables
	$("#vouchertable tr:odd").addClass('altcolor');
	$("#datepicker" ).datepicker({ minDate: new Date(minimumDate), maxDate: new Date(maximumDate),altField: "#fileexpirydate", altFormat: "d-m-yy" });
	$("#datepicker" ).datepicker( "option", "dateFormat", "<?php echo lang('_DP_dateFormat'); ?>" );
	$("#datepicker").datepicker("setDate", new Date(maximumDate));
	
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
	
	$("#dialog-delete").dialog({ autoOpen: false, height: 160, modal: true,
	
	buttons: {
			'cancelBTN': function() {
				$( this ).dialog( "close" );
			},
			'deleteBTN': function() { 
			deletevoucher();
			$( this ).dialog( "close" );
			}
		}
	});
		$('.ui-dialog-buttonpane button:contains(cancelBTN)').attr("id","btn_cancel");            
		$('#btn_cancel').html('<?php echo lang("_NO") ?>')  
		$('.ui-dialog-buttonpane button:contains(deleteBTN)').attr("id","btn_delete");            
		$('#btn_delete').html('<?php echo lang("_YES") ?>')  
});

function hidemessages()
{
		$("#fileto_msg").hide();
		$("#expiry_msg").hide();
		$("#maxemails_msg").hide();	
}

function validateForm()
	{	
		hidemessages();
		if(!validate_fileto()){return false;}
		if(!validate_expiry() ){return false;}
		postVoucher();
	}
		
function deletevoucher()
	{
		window.location.href="index.php?s=vouchers&a=del&id=" + selectedVoucher;
	}

function confirmdelete(vid)
	{
		selectedVoucher = vid;
		$("#dialog-delete").dialog("open");
	}
	
function postVoucher()
{
	hidemessages();
	// post voucher data from form
		
	var query = $("#form1").serializeArray(), json = {};
	for (i in query) {json[query[i].name] = query[i].value;} // create json from form1
	// post to fs_upload.php
	$.ajax({
	 type: "POST",
	 url: "fs_upload.php?type=insertVoucherAjax",
	 data: {myJson:  JSON.stringify(json)}
		,success:function( msg ) {
		// complete or display validation messages 
		if(msg == "complete") { window.location.href="index.php?s=vouchers&a=complete"; } 
		if(msg == "err_tomissing") { $("#fileto_msg").show();} // missing email data
		if(msg == "err_expmissing") { $("#expiry_msg").show();} // missing expiry date
		if(msg == "err_exoutofrange") { $("#expiry_msg").show();} // expiry date out of range
		if(msg == "err_invalidemail") { $("#fileto_msg").show();} // 1 or more emails invalid
		if(msg == "not_authenticated") { $("#_noauth").show();} // server returns not authenticated
		if(msg == "") { $("#_noauth").show();} // server returns not authenticated
		},error:function(xhr,err){
			// error function to display error message e.g.404 page not found
			ajaxerror(xhr.readyState,xhr.status,xhr.responseText);
		}
	});
}
//]]>
</script>
<?php 

if(isset($_REQUEST["a"]))
{
	// add voucher
	if($_REQUEST["a"] == "complete")
	{	
	echo "<div id='message'>".lang("_VOUCHER_SENT")."</div>";
	}
	// del
	if(isset($_REQUEST["a"]) && isset($_REQUEST["id"])) 
	{
	$myfileData = $functions->getVoucherData($_REQUEST['id']);
		if($_REQUEST["a"] == "del" )
		{
			if($functions->deleteVoucher($myfileData["fileid"]))
			{
			echo "<div id='message'>".lang("_VOUCHER_DELETED")."</div>";
			} else {
			
			}
		}
	}
}
// get file data
$filedata = $functions->getVouchers();
$json_o=json_decode($filedata,true);

?>
<form name="form1" id="form1" method="post" action="">
    <div id="box_1" class="box">
  <?php echo '<div id="pageheading">'.lang("_VOUCHERS").'</div>'; ?>
    <table width="100%" border="0">
      <tr>
        <td colspan="2" id="invite_text"><?php echo lang("_SEND_NEW_VOUCHER"); ?></td>
      </tr>
      </table>
  </div>
      <div id="box_2" class="box">
       <table width="100%" border="0">
      <tr>
        <td class="mandatory" id="vouchers_to" width="130"><?php echo lang("_SEND_VOUCHER_TO"); ?>:</td>
        <td>
        <input id="fileto" name="fileto" title="<?php echo lang("_EMAIL_SEPARATOR_MSG"); ?>"  type="text" size="45" onchange="validate_fileto()" /><br />
 		<div id="fileto_msg" class="validation_msg" style="display:none"><?php echo lang("_INVALID_MISSING_EMAIL"); ?></div>
        <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo lang("_MAXEMAILS"); ?> <?php echo $config['max_email_recipients'] ?>.</div>
 		</td>
      </tr>
      <tr>
        <td class="mandatory" id="vouchers_expirydate"><?php echo lang("_EXPIRY_DATE"); ?>:</td>
        <td><input id="datepicker" onchange="validate_expiry()" title="<?php echo lang('_DP_dateFormat'); ?>"></input> 
        <div id="expiry_msg" class="validation_msg" style="display:none"><?php echo lang("_INVALID_EXPIRY_DATE"); ?></div>
        </td>
      </tr>
      <tr>
        <td align="right" valign="middle"><input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date($lang['datedisplayformat'],strtotime("+".$config['default_daysvalid']." day"));?>" />
        <input type="hidden" name="filestatus" id="filestatus" value="voucher" /></td>
        <td><div class="menu" id="voucherbutton" onclick="validateForm()"><a href="#" id="btn_sendvoucher" ><?php echo lang("_SEND_VOUCHER"); ?></a></div><div id="_noauth" class="validation_msg" style="display:none"><?php echo lang("_AUTH_ERROR"); ?></div></td>
      </tr>
    </table>
     </div>
</form>
  <div id="box_3" class="box">
  <table id="vouchertable" width="100%" border="0" cellspacing="1">
    <tr class="headerrow">
      <td id="vouchers_header_to"><strong><?php echo lang("_TO"); ?></strong></td>
      <td id="vouchers_header_created"><strong><?php echo lang("_CREATED"); ?></strong></td>
      <td id="vouchers_header_expiry"><strong><?php echo lang("_EXPIRY"); ?></strong></td>
      <td></td>
    </tr>
    <?php
	$i = 0; 
	foreach($json_o as $item) {
		$i += 1; // counter for file id's
		echo "<tr><td>" .$item['fileto'] . "</td><td>" .date($lang['datedisplayformat'],strtotime($item['filecreateddate'])) . "</td><td>" .date($lang['datedisplayformat'],strtotime($item['fileexpirydate'])) . "</td><td><div  style='cursor:pointer;'><img id='btn_deletevoucher_".$i."' src='images/shape_square_delete.png' alt='' title='".lang("_DELETE")."' onclick='confirmdelete(".'"' .$item['filevoucheruid'] . '"'. ")' border='0' /></div></td></tr>"; //etc
	}
?>
  </table>
</div>
<div id="dialog-delete" style="display:none" title="<?php echo lang("_DELETE_VOUCHER") ?>">
<p><?php echo lang("_CONFIRM_DELETE_VOUCHER"); ?></p>
</div>
