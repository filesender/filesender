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
	$(function() {
		$( "#datepicker" ).datepicker({ minDate: 0, maxDate: "+"+maximumDate+"D",altField: "#altdate", altFormat: "d-m-yy",currentText:maximumDate });
		$( "#datepicker" ).datepicker( "option", "dateFormat", "d/m/yy" );
		

	});
	
   function checkform() {
  		var tempemail = document.getElementById('fileto').value;
		var email = tempemail.split(/,|;/);
		for (var i = 0; i < email.length; i++) {
		if (!echeck(email[i], 1, 0)) {
		//alert('one or more email addresses entered is invalid');
		return false;
		}
		}
		return true;
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
echo "<div id='message'>Voucher Added</div>";
}
// del
if(isset($_REQUEST["a"]) && isset($_REQUEST["id"])) 
{
$myfileData = $functions->getVoucherData($_REQUEST['id']);
if($_REQUEST["a"] == "del" )
{
if($functions->deleteVoucher($myfileData[0]["fileid"]))
{
echo "<div id='message'>Voucher Cancelled</div>";
}
}
}

// get file data
$filedata = $functions->getVouchers();
//$filedata = $filedata[0];
//echo $filedata;
$json_o=json_decode($filedata,true);

?>

<div id="box">
<?php echo '<div id="pageheading">'._VOUCHERS.'</div>'; ?>
  <form name="form1" method="post" action="index.php?s=vouchers"  onSubmit="return checkform()">
    <table width="100%" border="0">
      <tr>
        <td><?php echo _SEND_NEW_VOUCHER; ?></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><?php echo _TO; ?></td>
        <td><?php echo _EMAIL_SEPARATOR_MSG; ?><br />
          <input id="fileto" name="fileto" type="text" size="40" /></td>
      </tr>
      <tr>
        <td><?php echo _EXPIRY; ?></td>
        <td><div id="datepicker"></div></td>
      </tr>
      <tr>
        <td><input type="hidden" id="altdate" name="altdate" value="<?php echo date("d-m-Y",strtotime("+".$config['default_daysvalid']." day"));?>" /></td>
        <td><input type="submit" value="Send Voucher"/></td>
      </tr>
    </table>
  </form>
  </div>
  <div id="box">
  <table id="vouchertable" width="100%" border="0" cellspacing="1">
    <tr class="headerrow">
      <td><strong><?php echo _TO; ?></strong></td>
      <td><strong><?php echo _CREATED; ?></strong></td>
      <td><strong><?php echo _EXPIRY; ?></strong></td>
      <td></td>
    </tr>
    <?php 
foreach($json_o as $item) {
   echo "<tr><td>" .$item['fileto'] . "</td><td>" .date("d/m/Y",strtotime($item['filecreateddate'])) . "</td><td>" .date("d/m/Y",strtotime($item['fileexpirydate'])) . "</td><td><a href='index.php?s=vouchers&a=del&id=" .$item['filevoucheruid'] . "'><img src='images/shape_square_delete.png'></a></td></tr>"; //etc
}
?>
  </table>
</div>
<script language = "Javascript">
/**
 * DHTML email validation script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
 */

function echeck(str) {

		var at="@"
		var dot="."
		var lat=str.indexOf(at)
		var lstr=str.length
		var ldot=str.indexOf(dot)
		if (str.indexOf(at)==-1){
		   alert("Invalid E-mail")
		   return false
		}

		if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
		   alert("Invalid E-mail")
		   return false
		}

		if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
		    alert("Invalid E-mail")
		    return false
		}

		 if (str.indexOf(at,(lat+1))!=-1){
		    alert("Invalid E-mail")
		    return false
		 }

		 if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		    alert("Invalid E-mail")
		    return false
		 }

		 if (str.indexOf(dot,(lat+2))==-1){
		    alert("Invalid E-mail")
		    return false
		 }
		
		 if (str.indexOf(" ")!=-1){
		    alert("Invalid E-mail")
		    return false
		 }

 		 return true					
	}

function ValidateForm(){
	var emailID=document.frmSample.txtEmail
	
	if ((emailID.value==null)||(emailID.value=="")){
		alert("Please Enter your Email ID")
		emailID.focus()
		return false
	}
	if (echeck(emailID.value)==false){
		emailID.value=""
		emailID.focus()
		return false
	}
	return true
 }

// stripe every second row in the tables
$("#vouchertable tr:odd").addClass('altcolor');


</script> 
