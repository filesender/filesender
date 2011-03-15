<script>
	var maximumDate= '<?php echo $config['default_daysvalid'] ?>';
	$(function() {
		$( "#datepicker" ).datepicker({ minDate: 0, maxDate: "+"+maximumDate+"D",altField: "#altdate", altFormat: "d-m-yy" });
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
<?php echo '<div id="pageheading">'._VOUCHERS.'</div>'; ?> 
<form name="form1" method="post" action="index.php?s=vouchers"  onSubmit="return checkform()">
<p><?php echo _SEND_NEW_VOUCHER; ?></p>
<p><?php echo _TO; ?>:
  <input id="fileto" name="fileto" type="text" size="40" /> 
  <?php echo _EMAIL_SEPARATOR_MSG; ?>
</p>
  <p><?php echo _EXPIRY; ?>:<div id="datepicker"></div><input type="hidden" id="altdate" name="altdate" value="<?php echo date("d-m-Y",strtotime("+".$config['default_daysvalid']." day"));?>">
</p>
<p></p>
  <p><input type="submit" value="Send Voucher"/></p>
</form>
<?php echo _VOUCHERS; ?>
<div id="tablediv">
<table width="100%" border="0" cellspacing="1">
<tr bgcolor="#eeeeee"><td><strong><?php echo _TO; ?></strong></td>
<td><strong><?php echo _CREATED; ?></strong></td>
<td><strong><?php echo _EXPIRY; ?></strong></td>
<td></td>
</tr>
<?php 
foreach($json_o as $item) {
   echo "<tr  bgcolor='#eeeeee'><td>" .$item['fileto'] . "</td><td>" .date("d/m/Y",strtotime($item['filecreateddate'])) . "</td><td>" .date("d/m/Y",strtotime($item['fileexpirydate'])) . "</td><td><a href='index.php?s=vouchers&a=del&id=" .$item['filevoucheruid'] . "'><img src='images/shape_square_delete.png'></a></td></tr>"; //etc
}
?>
</table>
</div>
<p>.</p>
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
</script>