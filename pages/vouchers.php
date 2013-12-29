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
	
	$("#dialog-delete").dialog({ autoOpen: false, height: 180, modal: true,
	
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
     
	  // default auth error dialogue
		$("#dialog-autherror").dialog({ autoOpen: false, height: 240,width: 350, modal: true,title: "",		
		buttons: {
			'<?php echo lang("_OK") ?>': function() {
				location.reload(true);
				}
			}
		})  
		
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
        
        // end document ready
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
	 $("#voucherbutton").attr('onclick', '');
	 	
	var query = $("#form1").serializeArray(), json = {};
	for (i in query) {json[query[i].name] = query[i].value;} // create json from form1
	// post to fs_upload.php
	$.ajax({
	 type: "POST",
	 url: "fs_upload.php?type=insertVoucherAjax",
	 data: {myJson:  JSON.stringify(json)}
	 
		,success:function( data ) {
			if(data == "ErrorAuth")
		{
			$("#dialog-autherror").dialog("open");
			return;			
		}
		var data =  parseJSON(data);
		if(data.errors)
		{
		$.each(data.errors, function(i,result){
		if(result == "err_tomissing") { $("#fileto_msg").show();} // missing email data
		if(result == "err_expmissing") { $("#expiry_msg").show();} // missing expiry date
		if(result == "err_exoutofrange") { $("#expiry_msg").show();} // expiry date out of range
		if(result == "err_invalidemail") { $("#fileto_msg").show();} // 1 or more emails invalid
		if(result == "not_authenticated") { $("#_noauth").show();} // server returns not authenticated
		if(result == "err_token") {$("#dialog-tokenerror").dialog("open");} // token missing or error
		if(result == "") { $("#_noauth").show();} // server returns not authenticated
		if(result == "err_emailnotsent") {window.location.href="index.php?s=emailsenterror";} //
		})
		// re-enable button if client needs to change form details
		$("#voucherbutton").attr('onclick', 'validateForm()');
		return;
		}
		if(data.status && data.status == "complete") {  window.location.href="index.php?s=vouchers&a=complete";	}
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
			// check if user is authenticated and allowed to delete this voucher
			if( $isAuth && $userdata["saml_uid_attribute"] == $myfileData["fileauthuseruid"])
			{
				if($functions->deleteVoucher($myfileData["fileid"]))
				{
				echo "<div id='message'>".lang("_VOUCHER_DELETED")."</div>";
				} 
			} else {
				// log auth user tried to delete a voucher they do not have access to
				logEntry("Permission denied - attempt to delete voucher ".$myfileData["fileuid"],"E_ERROR");
				// notify - not deleted - you do not have permission	
				echo "<div id='message'>".lang("_PERMISSION_DENIED")."</div>";
			}
		}
	}
}
foreach ($errorArray as $message) 
		{
		if($message == "err_emailnotsent")
		{
			echo '<div id="message">'.lang("_ERROR_SENDING_EMAIL").'</div>';
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
        <input id="fileto" name="fileto" title="<?php echo lang("_EMAIL_SEPARATOR_MSG"); ?>" onfocus="$('#fileto_msg').hide();"  onblur="validate_fileto()" type="text" size="45"/><br />
 		<div id="fileto_msg" class="validation_msg" style="display:none"><?php echo lang("_INVALID_MISSING_EMAIL"); ?></div>
        <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo lang("_MAXEMAILS"); ?> <?php echo $config['max_email_recipients'] ?>.</div>
 		</td>
      </tr>
       <tr>
        <td class="mandatory" id="voucher_from"><?php echo lang("_FROM"); ?>:</td>
        <td colspan="2">
<?php
if ( count($useremail) > 1 ) {
        echo "<select name=\"filefrom\" id=\"filefrom\">\n";
        foreach($useremail as $email) {
                echo "<option>$email</option>\n";
        }
        echo "</select>\n";
} else {
        echo "<div id=\"visible_filefrom\">".$useremail[0]."</div>" ."<input name=\"filefrom\" type=\"hidden\" id=\"filefrom\" value=\"" . $useremail[0] . "\" />\n";
}
?>   </td>
        </tr>
	   <tr>
	     <td class="" id="voucher_subject"><?php echo lang("_SUBJECT"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
	     <td colspan="2"><input name="vouchersubject" type="text" id="vouchersubject" /></td>
	     </tr>
	   <tr>
	   <td class="" id="voucher_message"><?php echo lang("_MESSAGE"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
        <td colspan="2"><textarea name="vouchermessage" cols="57" rows="4" id="vouchermessage"></textarea></td>
      </tr>
      <tr>
        <td class="mandatory" id="vouchers_expirydate"><?php echo lang("_EXPIRY_DATE"); ?>:</td>
        <td><input id="datepicker" onchange="validate_expiry()" title="<?php echo lang('_DP_dateFormat'); ?>" />
        <div id="expiry_msg" class="validation_msg" style="display:none"><?php echo lang("_INVALID_EXPIRY_DATE"); ?></div>
        </td>
      </tr>
      <tr>
        <td align="right" valign="middle">
		<input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date($lang['datedisplayformat'],strtotime("+".$config['default_daysvalid']." day"));?>" />
		<input type="hidden" name="s-token" id="s-token" value="<?php echo (isset($_SESSION["s-token"])) ?  $_SESSION["s-token"] : "";?>" />
		</td>
        <td><div class="menu" id="voucherbutton" onclick="validateForm()"><a href="#" id="btn_sendvoucher" ><?php echo lang("_SEND_VOUCHER"); ?></a></div><div id="_noauth" class="validation_msg" style="display:none"><?php echo lang("_AUTH_ERROR"); ?></div></td>
      </tr>
    </table>
     </div>
</form>
  <div id="box_3" class="box">
  <table id="vouchertable" width="100%" border="0" cellspacing="1" style="table-layout:fixed;">
    <tr class="headerrow">
   	<td class='tblmcw1'></td>
    <td id="vouchers_header_from"><strong><?php echo lang("_FROM"); ?></strong></td>
    <td id="vouchers_header_to"><strong><?php echo lang("_TO"); ?></strong></td>
    <td class='tblmcw3' id="vouchers_header_subject"><strong><?php echo lang("_SUBJECT"); ?></strong></td>
    <td class='tblmcw3' id="vouchers_header_message"><strong><?php echo lang("_MESSAGE"); ?></strong></td>
    <td class='tblmcw3' id="vouchers_header_created"><strong><?php echo lang("_CREATED"); ?></strong></td>
    <td class='tblmcw3' id="vouchers_header_expiry"><strong><?php echo lang("_EXPIRY"); ?></strong></td>
    </tr>
    <?php
	$i = 0; 
	foreach($json_o as $item) {
		$i += 1; // counter for file id's
		echo "<tr>";
		echo "<td class='tblmcw1'><div  style='cursor:pointer;'><img id='btn_deletevoucher_".$i."' src='images/shape_square_delete.png' alt='' title='".lang("_DELETE")."' onclick='confirmdelete(".'"' .$item['filevoucheruid'] . '"'. ")' border='0' /></div></td>";
		echo "<td style='word-wrap:break-word;'>" .$item['filefrom'] . "</td><td style='word-wrap:break-word;'>" .$item['fileto'] . "</td><td class='HardBreak'>";
		if($item['filesubject'] != "")
		{
			echo "<img src='images/page_white_text_width.png' border='0' alt='' title='".utf8tohtml($item['filesubject'],TRUE). "' />";
		}
		echo "</td><td>";
		if($item['filemessage'] != "")
		{
			echo "<img src='images/page_white_text_width.png' border='0' alt='' title='".utf8tohtml($item['filemessage'],TRUE). "' />";
		}
		echo "</td><td>" .date($lang['datedisplayformat'],strtotime($item['filecreateddate'])) . "</td><td>" .date($lang['datedisplayformat'],strtotime($item['fileexpirydate'])) . "</td></tr>"; //etc
	}
?>
  </table>
 <?php
  if($i==0)
	{
		echo lang("_NO_VOUCHERS");
	}
?>
</div>
<div id="dialog-delete" style="display:none" title="<?php echo lang("_DELETE_VOUCHER") ?>">
<p><?php echo lang("_CONFIRM_DELETE_VOUCHER"); ?></p>
</div>
<div id="dialog-autherror" title="<?php echo lang($lang["_MESSAGE"]); ?>" style="display:none"><?php echo lang($lang["_AUTH_ERROR"]); ?></div>
