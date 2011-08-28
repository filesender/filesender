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
 * Vouchers Page
 * ---------------------------------
 * 
 */
 ?>
<script>
	var maximumDate= '<?php echo $config['default_daysvalid'] ?>';
	var selectedVoucher = "";
	$(function() {
		$("#fileto_msg").hide();
		$("#expiry_msg").hide();
		
		// stripe every second row in the tables
		$("#vouchertable tr:odd").addClass('altcolor');
		$("#datepicker" ).datepicker({ minDate: 1, maxDate: "+"+maximumDate+"D",altField: "#altdate", altFormat: "d-m-yy",currentText:maximumDate });
		$("#datepicker" ).datepicker( "option", "dateFormat", "dd-mm-yy" );
		$("#datepicker").datepicker("setDate", new Date()+maximumDate);
		$("#dialog-delete").dialog({ autoOpen: false, height: 140, modal: true,
		
		buttons: {
				<?php echo lang("_CANCEL") ?>: function() {
					$( this ).dialog( "close" );
				},
				<?php echo lang("_DELETE") ?>: function() { 
				deletevoucher();
				$( this ).dialog( "close" );
				}
		}
		});
	});

function validateForm()
	{
		$("#fileto_msg").hide();
		if(!validate_fileto()){return false;}
		if(!validate_expiry() ){return false;}
		document.forms['form1'].submit();//return true;
	}
	// Validate EXPIRY
function validate_expiry()
{

var validformat=/^\d{2}\-\d{2}\-\d{4}$/ //Basic check for format validity
	var returnval=false
	var today = new Date();
    var maxDate = new Date(today.getFullYear(), today.getMonth(), today.getDate()+parseInt(maximumDate));
    if (!validformat.test($("#datepicker").val())) 
	{
	$("#expiry_msg").show();
	return false;
	}
	var monthfield=$("#datepicker").val().split("-")[1]
	var dayfield=$("#datepicker").val().split("-")[0]
	var yearfield=$("#datepicker").val().split("-")[2]
	var dayobj = new Date(yearfield, monthfield-1, dayfield)
	if ((dayobj.getMonth()+1!=monthfield)||(dayobj.getDate()!=dayfield)||(dayobj.getFullYear()!=yearfield))
	{
	$("#expiry_msg").show();
	return false;
	}
	if(dayobj < today || dayobj > maxDate)
	{
		$("#expiry_msg").show();
		return false;	
	}
	if($("#datepicker").datepicker("getDate") == null)
	{
		$("#expiry_msg").show();
		return false;
	}
	$("#expiry_msg").hide();
	return true;
}

// Validate FILETO
function validate_fileto()
{
	// remove white spaces 
	
	var email = $("#fileto").val();
	email = email.split(" ").join("");
	$("#fileto").val(email);
	email = email.split(/,|;/);
	for (var i = 0; i < email.length; i++) {
		if (!echeck(email[i], 1, 0)) {
		$('#fileto_msg').show();
		return false;
		}
		}
	return true;	
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

	
	function echeck(str) {

		var at="@"
		var dot="."
		var lat=str.indexOf(at)
		var lstr=str.length
		var ldot=str.indexOf(dot)
		if (str.indexOf(at)==-1){
		//   alert("Invalid E-mail")
		   return false
		}

		if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
		//   alert("Invalid E-mail")
		   return false
		}

		if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
		//    alert("Invalid E-mail")
		    return false
		}

		 if (str.indexOf(at,(lat+1))!=-1){
		 //   alert("Invalid E-mail")
		    return false
		 }

		 if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		 //   alert("Invalid E-mail")
		    return false
		 }

		 if (str.indexOf(dot,(lat+2))==-1){
		 //   alert("Invalid E-mail")
		    return false
		 }
		
		 if (str.indexOf(" ")!=-1){
		 //   alert("Invalid E-mail")
		    return false
		 }

 		 return true					
	}
	</script>
<?php 

// add voucher
if(isset($_POST["fileto"]) && isset($_POST["altdate"]))
{
// insert voucher for each email
$emailto = str_replace(",",";",$_POST["fileto"]);
$emailArray = preg_split("/;/", $emailto);
foreach ($emailArray as $Email) { 
$functions->insertVoucher($Email,$_POST["altdate"]);
}
echo "<div id='message'>".lang("_VOUCHER_SENT")."</div>";
}
// del
if(isset($_REQUEST["a"]) && isset($_REQUEST["id"])) 
{
$myfileData = $functions->getVoucherData($_REQUEST['id']);
if($_REQUEST["a"] == "del" )
{
if($functions->deleteVoucher($myfileData[0]["fileid"]))
{
echo "<div id='message'>".lang("_VOUCHER_DELETED")."</div>";
}
}
}

// get file data
$filedata = $functions->getVouchers();
$json_o=json_decode($filedata,true);

?>
<form name="form1" method="post" action="index.php?s=vouchers"  onSubmit="return validateForm()">
    <div id="box">
  <?php echo '<div id="pageheading">'.lang("_VOUCHERS").'</div>'; ?>
    <table width="100%" border="0">
      <tr>
        <td colspan="2" class="formfieldheading"><?php echo html_entity_decode(lang("_SEND_NEW_VOUCHER")); ?></td>
      </tr>
      </table>
      </div>
      <div id="box">
       <table width="100%" border="0">
      <tr>
        <td class="formfieldheading mandatory" width="200"><?php echo lang("_SEND_VOUCHER_TO"); ?>:</td>
        <td>
        <input id="fileto" name="fileto" title="<?php echo lang("_EMAIL_SEPARATOR_MSG"); ?>"  type="text" size="45" /><br />
 		<div id="fileto_msg" class="validation_msg"><?php echo lang("_INVALID_MISSING_EMAIL"); ?></div>
 		</td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"><?php echo lang("_EXPIRY_DATE"); ?>:</td>
        <td><input id="datepicker" onchange="validate_expiry()" title="dd-mm-yyyy"></input> <div id="expiry_msg" class="validation_msg"><?php echo lang("_INVALID_EXPIRY_DATE"); ?></div></td>
      </tr>
      <tr>
        <td><input type="hidden" id="altdate" name="altdate" value="<?php echo date($config['datedisplayformat'],strtotime("+".$config['default_daysvalid']." day"));?>" /></td>
        <td><div id="bigbtn"><a href="#" onclick="validateForm()"><?php echo lang("_SEND_VOUCHER"); ?></a></div></td>
      </tr>
    </table>
     </div>
  </form>
  <div id="box">
  <table id="vouchertable" width="100%" border="0" cellspacing="1">
    <tr class="headerrow">
      <td><strong><?php echo lang("_TO"); ?></strong></td>
      <td><strong><?php echo lang("_CREATED"); ?></strong></td>
      <td><strong><?php echo lang("_EXPIRY"); ?></strong></td>
      <td></td>
    </tr>
    <?php 
foreach($json_o as $item) {
   echo "<tr><td>" .$item['fileto'] . "</td><td>" .date($config['datedisplayformat'],strtotime($item['filecreateddate'])) . "</td><td>" .date($config['datedisplayformat'],strtotime($item['fileexpirydate'])) . "</td><td><div  style='cursor:pointer;'><img src='images/shape_square_delete.png' onclick='confirmdelete(".'"' .$item['filevoucheruid'] . '"'. ")' border='0'></div></td></tr>"; //etc
}
?>
  </table>
</div>
<div id="dialog-delete" title="<?php echo lang("_DELETE_VOUCHER") ?>">
  <p><?php echo lang("_CONFIRM_DELETE_VOUCHER"); ?></p>
</div>