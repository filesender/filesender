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
	
   // check if a voucher and load into form if it is
   $filestatus = "Available";
   $voucherUID = "";
   $senderemail = $useremail;
   
   // get initial upload uid
   $id = getGUID();
   // set id for progress bar upload
  // $id = md5(microtime() . rand());
   
   // check if this is a vooucher
  if($authvoucher->aVoucher())
  {
	// get voucher information 
	$voucherData =  $authvoucher->getVoucher();
	$voucherUID = $voucherData[0]["filevoucheruid"];
	$senderemail = $voucherData[0]["fileto"];
	// check if voucher is invalid (this should be an external function
	if($voucherData[0]["filestatus"] == "Voucher") {
	$filestatus = "Voucher";
	} else if($voucherData[0]["filestatus"] == "Voucher Cancelled" || $voucherData[0]["filestatus"] == "Closed")
	{
	require_once('../pages/vouchercancelled.php');
	return;
	}
}
	if (isset($_COOKIE['SimpleSAMLAuthToken'])) {
		$token = $_COOKIE['SimpleSAMLAuthToken'];
	} else {
		$token = "";
	}
	// set flash upload vairiables
	$flashVARS = "vid=".$voucherUID."&sid=".session_id()."&buttonBrowse="._BROWSE."&buttonUpload="._UPLOAD."&buttonCancel="._CANCEL."&siteURL=".$config["site_url"]."&token=".$token;
 ?>
<script type="text/javascript" src="lib/js/AC_OETags.js" language="javascript"></script>
<script type="text/javascript" src="js/upload.js"></script>
<script type="text/javascript">
	
	// 
	// all default settings
	var uploadid = '<?php echo $id ?>';
	var maximumDate= '<?php echo $config['default_daysvalid']?>';
	var aup = '<?php echo $config['AuP'] ?>';
	var bytesUploaded = 0;
	var bytesTotal = 0;
	var previousBytesLoaded = 0;
	var intervalTimer = 0;
	var vid='<?php if(isset($_REQUEST["vid"])){echo $_REQUEST["vid"];}; ?>';
 	//var fileupload[uploadid].status = "draft";
 	// start document ready 
	$(document).ready(function() { 
		
		// hide all upload objects
		$('#uploadstandard').hide();
		$('#uploadhtml5').hide();
		$('#uploadstandardspinner').hide();
		$('#progress_container').hide();
		$('#fileto_msg').hide();
		$('#expiry_msg').hide();
		$('#aup_msg').hide();
		$('#file_msg').hide();
		
		// hide aup if not required
		if(aup == '0') // check if AUP is required
		{
			$('#aup').hide();
		}
		//$('#uploadbutton').hide();
		// default error message dialogue
		$("#dialog-default").dialog({ autoOpen: false, height: 140, modal: true,title: "Error",		
		buttons: {
			Ok: function() {
				$("#dialog-default").html("");
				$( this ).dialog( "close" );
				}
			}
		})
		// set date picker
		$(function() {
			$("#datepicker" ).datepicker({ minDate: 1, maxDate: "+"+maximumDate+"D",altField: "#fileexpirydate", altFormat: "d-m-yy" });
			$("#datepicker" ).datepicker( "option", "dateFormat", "d/m/yy" );
			$("#datepicker").datepicker('setDate', new Date()+maximumDate);
		});	

		//Check if HTML5 is enable and use HTML uploader
		if(window.File && window.FileReader && window.FileList && window.Blob && window.FormData){
			// use HTML5 upload functions
			$("#uploadhtml5").show();
			} else {
			// use standard upload functions
			$("#uploadstandard").show();
		}
	// end document ready
	});

	function toggleTOG()
	{
	$('#tog').toggle();
	}
	
	// --------------------------
	// Common upload functions
	// --------------------------
	
	// update the progress bar
	function updatepb(bytesloaded,totalbytes)
	{
		var percentComplete = Math.round(bytesloaded * 100 / totalbytes);
		var bytesTransfered = '';
		if (bytesloaded > 1024*1024)
			bytesTransfered = (Math.round(bytesloaded * 100/(1024*1024))/100).toString() + 'MB';
		else if (bytesloaded > 1024)
			bytesTransfered = (Math.round(bytesloaded * 100/1024)/100).toString() + 'KB';
		else
			bytesTransfered = (Math.round(bytesloaded * 100)/100).toString() + 'Bytes';

			$('#progress_container').fadeIn(100);	//fade in progress bar	
			$('#progress_bar').width(percentComplete/100 *$('#progress_container').width());	//set width of progress bar based on the $status value (set at the top of this page)
			$('#progress_bar').html(percentComplete +"% ");
			$('#progress_completed').html(parseInt(percentComplete) + "%(" + bytesTransfered + ")" );	//display the % completed within the progress bar
		  
	}

	// get a dom element (just to reduce code)
	function obj(id) {
		return document.getElementById(id);
	}
	
	function validateforflash()
	{
	if(validateFormFlash())
	{
	getFlexApp('filesenderup').returnMsg("true")
	} else {
	getFlexApp('filesenderup').returnMsg("false")
	}
	}
	// --------------------------
	// Validation functions
	// --------------------------
	// HTML5 form Validation
	function validateForm()
	{
	// remove previouse vaildation messages
	$('#fileto_msg').hide();
	$('#expiry_msg').hide();
	$('#aup_msg').hide();
	$('#file_msg').hide();
	
	var validate = true;
	
	if(!validate_fileto() ){validate = false;};		// validate emails
	if(!validate_file() ){validate = false;};		// check if file selected
	if(aup == '1') // check if AUP is required
	{
	if(!validate_aup() ){validate = false;};		// check AUP is selected
	}
	if(!validate_expiry() ){validate = false;};		// check date
		
	return validate;
	}
	// FLASH form Validation
	function validateFormFlash()
	{
	// remove previouse vaildation messages
	$('#fileto_msg').hide();
	$('#expiry_msg').hide();
	$('#aup_msg').hide();
	$('#file_msg').hide();
	
	var validate = true;
	
	if(!validate_fileto() ){validate = false;};		// validate emails
	if(aup == '1') // check if AUP is required
	{
		if(!validate_aup() ){validate = false;};		// check AUP is selected
	}
	if(!validate_expiry() ){validate = false;};		// check date
		
	return validate;
	}

// Validate FILETO
function validate_fileto()
{
	$('#fileto_msg').hide();
	// remove white spaces 
	obj('fileto').value = obj('fileto').value.split(' ').join('');
	var tempemail = $('#fileto').val();
	var email = tempemail.split(/,|;/);
	for (var i = 0; i < email.length; i++) {
		if (!echeck(email[i], 1, 0)) {
		$('#fileto_msg').show();
		return false;
		}
		}
	return true;	
}

// Validate EXPIRY
function validate_expiry()
{
//	alert($('#fileexpirydate').datepicker("getDate"));
	if($('#datepicker').datepicker("getDate") == null)
	{
		$('#expiry_msg').show();
		return false;
	}
	$('#expiry_msg').hide();
	return true;
}

//Validate AUP
function validate_aup()
{
	if(	$('#aup').is(':checked'))
	{
		$('#aup_msg').hide();
		return true;
	} else {
		$('#aup_msg').show();
		return false;
	}
}

// Validate FILE (HTML5 only)
function validate_file()
{
	fileMsg("");
	if(!document.getElementById('fileToUpload').files[0])
	{
		fileMsg("Please select a file");
		return false;
	} else 
	{
		var file = document.getElementById('fileToUpload').files[0];
		// validate fiename 
		if (!validatefilename(file.name)){
		return false;
		}
		//validate file size
		if(file.size < 1)
		{
		fileMsg("File size cannot be 0. Please select another file.");	
		return false;
		}
		return true;
	}	
}
//  validate single email	
function echeck(str) {

		var at="@"
		var dot="."
		var lat=str.indexOf(at)
		var lstr=str.length
		var ldot=str.indexOf(dot)
		if (str.indexOf(at)==-1){
		  // alert("Invalid E-mail")
		   return false
		}

		if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
		   //alert("Invalid E-mail")
		   return false
		}

		if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
		   // alert("Invalid E-mail")
		    return false
		}

		 if (str.indexOf(at,(lat+1))!=-1){
		    //alert("Invalid E-mail")
		    return false
		 }

		 if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		    //alert("Invalid E-mail")
		    return false
		 }

		 if (str.indexOf(dot,(lat+2))==-1){
		    //alert("Invalid E-mail")
		    return false
		 }
		
		 if (str.indexOf(" ")!=-1){
		    //alert("Invalid E-mail")
		    return false
		 }

	 return true					
}
// flex file information check
function fileInfo(name,size)
{
if(size > 2000000000)
{
fileMsg("This file is larger than 2Gb. Please use a HTML5 enabled browser to upload larger files.");
} else if (validatefilename(name)) 
{

$("#fileInfoView").show();
$('#n').val(name);
$('#total').val(size);
$('#fileName').val(name);
$("#fileName").html('Name: ' + name);
$("#fileSize").html('Size: ' + readablizebytes(size));
getFlexApp('filesenderup').returnMsg("upload")
} 
}


function uploadcomplete(name,size)
{
$("#form1").submit();
}

function uploaderror(name,size)
{
errorDialog("Error uploading your file"+name+":"+size);
}

// check browser type
function getFlexApp(appName)
{
  if (navigator.appName.indexOf ("Microsoft") !=-1)
  {
	  if(window[appName] == undefined)
	  {
    	return document[appName];
	  } else {
		return window[appName];  
	  }
  }
  else
  {
    return document[appName];
  }
}

function validatefilename(name)
{
   if (/^[^\\\/\:\*\?\"\<\>\|\.]+(\.[^\\\/\:\*\?\"\<\>\|\.]+)+$/.test(name)) 
   {
		return true; 
	} else {
		fileMsg("The name of the file you are uploading is invalid. Please rename your file and try again.");	
		return false;
	}
}

function validate() 
{
	// upload if validated
	if(validateForm())
	{
	startupload();
	}
}

function errorDialog(msg)
{
$('#dialog-default').html(msg);
$('#dialog-default').dialog('open')
}

function fileMsg(msg)
{
	$('#file_msg').html(msg);
	$('#file_msg').show();
}

function cancel()
{
	window.location.href=window.location.href;
}
    </script>

<div id="box"> <?php echo '<div id="pageheading">'._UPLOAD.'</div>'; ?>
  <form id="form1" enctype="multipart/form-data" method="POST" action="fs_uploadit5.php">
    <table width="500" border="0">
      <tr>
        <td width="200" class="formfieldheading mandatory"><?php echo _TO; ?>:</td>
        <td valign="middle"><input name="fileto" title="<?php echo _EMAIL_SEPARATOR_MSG; ?>" type="text" id="fileto" value="" size="40" onchange="validate_fileto()" />
          <div id="fileto_msg" style="display: none" class="validation_msg">Invalid or missing email</div></td>
        <td valign="middle">&nbsp;</td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"><?php echo _FROM; ?>:</td>
        <td><?php echo $senderemail ?>
          <input name="filefrom" type="hidden" id="filefrom" value="<?php echo $senderemail ?>" size="40" /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="formfieldheading"><?php echo _SUBJECT; ?>: (<?php echo _OPTIONAL; ?>)</td>
        <td><input name="filesubject" type="text" id="filesubject" size="40" /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="formfieldheading"><?php echo _MESSAGE; ?>: (<?php echo _OPTIONAL; ?>)</td>
        <td><textarea name="filemessage" cols="40" rows="4" id="filemessage"></textarea></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"><?php echo _EXPIRY; ?>:
          <input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date("d-m-Y",strtotime("+".$config['default_daysvalid']." day"));?>"/></td>
        <td><input id="datepicker" name="datepicker" onchange="validate_expiry()">
          </input>
          <div id="expiry_msg" class="validation_msg" style="display: none">Invalid expiry Date</div></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"><?php echo _SELECT_FILE; ?></td>
        <td><div id="uploadstandard"> 
            <script language="JavaScript" type="text/javascript">
<!--
// Version check for the Flash Player that has the ability to start Player Product Install (6.0r65)
var hasProductInstall = DetectFlashVer(6, 0, 65);

// Version check based upon the values defined in globals
var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);

if ( hasProductInstall && !hasRequestedVersion ) {
	// DO NOT MODIFY THE FOLLOWING FOUR LINES
	// Location visited after installation is complete if installation is required
	var MMPlayerType = (isIE == true) ? "ActiveX" : "PlugIn";
	var MMredirectURL = window.location;
    document.title = document.title.slice(0, 47) + " - Flash Player Installation";
    var MMdoctitle = document.title;

	AC_FL_RunContent(
		"src", "lib/swf/playerProductInstall",
		"FlashVars", "<?php echo $flashVARS ?>",
		"width", "500",
		"height", "50",
		"align", "middle",
		"id", "filesenderup",
		"quality", "high",
		"bgcolor", "#ffffff",
		"name", "filesenderup",
		"allowScriptAccess","sameDomain",
		"type", "application/x-shockwave-flash",
		"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
} else if (hasRequestedVersion) {
	// if we've detected an acceptable version
	// embed the Flash Content SWF when all tests are passed
	AC_FL_RunContent(
			"src", "swf/filesenderup",
			"FlashVars", "<?php echo $flashVARS ?>",
			"width", "500",
			"height", "50",
			"align", "middle",
			"id", "filesenderup",
			"quality", "high",
			"bgcolor", "#ffffff",
			"name", "filesenderup",
			"allowScriptAccess","sameDomain",
			"type", "application/x-shockwave-flash",
			"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
  } else {  // flash is too old or we can't detect the plugin
    var alternateContent = '<div id="header"><h1>Install Flash Player<h1></div><BR><div align="center">This application requires Flash Player.<BR><BR>'
  	+ 'To install Flash Player go to Adobe.com.<br> '
   	+ '<a href=http://www.adobe.com/go/getflash/>Get Flash</a></div>';
    document.write(alternateContent);  // insert non-flash content
  }
// -->
</script>
            <noscript>
            <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="500" height="50"
			codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
              <param name="movie" value="swf/filesenderup.swf" />
              <param name="quality" value="high" />
              <param name="bgcolor" value="#ffffff" />
              <param name="allowScriptAccess" value="sameDomain" />
              <embed src="swf/filesenderup.swf" quality="high" bgcolor="#869ca7"
				width="500" height="50" name="filesenderup" align="middle"
				play="true"
				loop="false"
				quality="high"
				allowScriptAccess="sameDomain"
				type="application/x-shockwave-flash"
				pluginspage="http://www.adobe.com/go/getflashplayer">
              </embed>
            </object>
            </noscript>
            <div id="uploadstandardspinner" style="padding-top:10px"><img src="images/ajax-loader-sm.gif" border=0 align="left" style="padding-right:6px"/><?php echo _UPLOADING_WAIT; ?></div>
            <BR />
          </div>
          <div id="uploadhtml5">
            <input type="file" name="fileToUpload" id="fileToUpload" onChange="fileSelected();"/>
            <input type="button" onClick="validate()" value="Upload" id="uploadbutton" name="uploadbutton"/>
            <input type="button" onClick="cancel()" value="Cancel" id="cancelbutton" name="cancelbutton" style="display:none"/>
            <div id="file_msg" class="validation_msg" style="display: none">Invalid File</div>
          </div></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td></td>
        <td><input type="hidden" id="filevoucheruid" name="filevoucheruid" value="<?php echo $voucherUID; ?>"/>
          <input type="hidden" name="vid" id="vid" value="<?php echo $voucherUID; ?>"/>
          <input type="hidden" name="total" id="total" value=""/>
          <input type="hidden" name="n" id="n" value=""/>
          <input type="hidden" id="filestatus" name="filestatus" value="<?php echo $filestatus; ?>"/>
          <input type="hidden" name="loadtype" id="loadtype" value="standard"/>
          <div class="row">
          <div id="fileInfoView">
            <div id="fileName" name="fileName"></div>
            <div id="fileSize" name="fileSize"></div>
            <div id="fileType" name="fileType"></div>
          </div>
          <div id="progress_container">
            <div id="progress_bar">
              <div id="progress_completed"></div>
              <br />
            </div>
          </div>
          <div id="transferSpeedInfo"></div>
          <div id="timeRemainingInfo"></div></td>
        <?php if ($config["AuP"]) {?>
      </tr>
      <tr>
        <td class="formfieldheading"></td>
        <td><input name="aup" type="checkbox" value="true" id="aup" onchange="validate_aup()" />
          <?php echo "I accept the terms and conditions of this service"; ?> [<a href="#" onclick="toggleTOG()">Show/Hide</a>]
          <div id="aup_msg" class="validation_msg" style="display: none">Please agree to the terms</div>
          <div id="tog" style="display:none"> <?php echo $config["AuP_terms"]; ?> </div></td>
        <td>&nbsp;</td>
      </tr>
      <?php } ?>
    </table>
  </form>
</div>
<div id="dialog-default" title=""> </div>