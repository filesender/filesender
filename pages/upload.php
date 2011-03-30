<script type="text/javascript" src="js/upload.js"></script>
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
   
   // set id for progress bar upload
   $id = md5(microtime() . rand());
   
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
	
	// set flash upload vairiables
	$flashVARS = "vid=".$voucherUID."&sid=".session_id()."&buttonBrowse="._BROWSE."&buttonUpload="._UPLOAD."&buttonCancel="._CANCEL."&siteURL=".$config["site_url"];
 ?>
<script type="text/javascript" src="js/fs_gears.js" ></script>
<script type="text/javascript" src="lib/js/AC_OETags.js" language="javascript"></script>
<script type="text/javascript">
	
	// all default settings
	var uploadid = '<?php echo $id ?>';
	var maximumDate= '<?php echo $config['default_daysvalid']?>';
	var bytesUploaded = 0;
	var bytesTotal = 0;
	var previousBytesLoaded = 0;
	var intervalTimer = 0;
 
 	// start document ready 
	$(document).ready(function() { 
		
		// hide all upload objects
		obj('uploadstandard').style.display = "none";
		obj('uploadhtml5').style.display = "none";
		obj('uploadstandardspinner').style.display = "none";
		obj('progress_container').style.display = "none";
		$("#dialog-toolarge").dialog({ autoOpen: false, height: 140, modal: true })
		$("#dialog-invalidfilename").dialog({ autoOpen: false, height: 140, modal: true })
		// set date picker
		$(function() {
			$( "#datepicker" ).datepicker({ minDate: 0, maxDate: "+"+maximumDate+"D",altField: "#fileexpirydate", altFormat: "d-m-yy" });
			$( "#datepicker" ).datepicker( "option", "dateFormat", "d/m/yy" );
		});	

		//Check if HTML5 is enable and use HTML uploader
		if(window.File && window.FileReader && window.FileList){
			if (window.FormData)
			{
				// can use HTML5 upload functions
				obj("uploadhtml5").style.display = 'block';
			} else {
				// use standard upload functions
				obj("uploadstandard").style.display = 'block';
			}
		} else {
			// use standard upload functions
		obj("uploadstandard").style.display = 'block';
		}
	// end document ready
	});

	function toggleTOG()
	{
	if (obj('TOG').style.display == "block")
	{
	obj('TOG').style.display = "none"
	} else {
	obj('TOG').style.display = "block"
	} 
	}

	function uploadcomplete(msg)
	{
	alert(msg);
	window.location="index.php?s=complete";

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
			$('#progress_bar').width(percentComplete +"% ");	//set width of progress bar based on the $status value (set at the top of this page)
			$('#progress_completed').html(parseInt(percentComplete) + "%(" + bytesTransfered + ")" );	//display the % completed within the progress bar
		  
	}

	// get a dom element (just to reduce code)
	function obj(id) {
		return document.getElementById(id);
	}
	// --------------------------
	// Validation functions
	// --------------------------
	function validateForm()
	{
	var tempemail = obj('fileto').value;
	var email = tempemail.split(/,|;/);
	for (var i = 0; i < email.length; i++) {
		if (!echeck(email[i], 1, 0)) {
		//alert('one or more email addresses entered is invalid');
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

function fileInfo(name,size)
{
if(size > 2000000000)
{
	$("#dialog-toolarge").dialog('open');

} else if (validatefilename(name) == false) 
{
 $("#dialog-invalidfilename").dialog('open')
} else if (!validateForm())
{
 alert("Invalid or missing email");
} else {
getFlexApp('filesender').returnMsg("upload")
}

obj('n').value= name;
obj('total').value = size;
}

function errormsg(msg)
{

}

function uploadcomplete(name,size,vid)
{
document.forms["form1"].submit();
}

function uploaderror(name,size)
{
alert("Error uploading your file");
}

// check browser type
function getFlexApp(appName)
{
  if (navigator.appName.indexOf ("Microsoft") !=-1)
  {
    return window[appName];
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
		return false;
	}
}
    </script>
<?php echo '<div id="pageheading">'._NEW_UPLOAD.'</div>'; ?>

<form id="form1" enctype="multipart/form-data" method="POST" action="fs_uploadit5.php">
  <div align="right">
    <p align="left"><?php echo _TO; ?>:
      <input name="fileto" type="text" id="fileto" size="40">
      <?php echo _EMAIL_SEPARATOR_MSG; ?></p>
    <p align="left"><?php echo _FROM; ?>: <?php echo $senderemail ?> 
  </div>
  <input name="filefrom" type="hidden" id="filefrom" value="<?php echo $senderemail ?>" size="40">
  </p>
  <p align="left"><?php echo _SUBJECT; ?>:
    <input name="filesubject" type="text" id="filesubject" size="40">
  </p>
  <p align="left"><?php echo _MESSAGE; ?>:
    <textarea name="filemessage" cols="40" rows="4" id="filemessage"></textarea>
  </p>
  <p align="left"><?php echo _EXPIRY; ?>:
  <div id="datepicker"></div>
  <input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date("d-m-Y",strtotime("+".$config['default_daysvalid']." day"));?>">
  </p>
  <p align="left">
    <input name="checkbox" type="checkbox" value="checkbox" checked="checked">
    <?php echo _TERMS_OF_AGREEMENT; ?>[<a href="#" onclick="toggleTOG()"><?php echo _SHOW_TERMS; ?></a>]</p>
  <div id="TOG" style="display:none">
    <hr />
    <?php echo $config["AuP_terms"]; ?>
    <hr />
  </div>
  <label for="fileToUpload"><?php echo _SELECT_FILE; ?></label>
  <br />
  <input type="hidden" id="filevoucheruid" value="<?php echo $voucherUID; ?>"/>
  <input type="hidden" name="vid" id="vid" value="<?php echo $voucherUID; ?>"/>
  <input type="hidden" name="total" id="total" value=""/>
  <input type="hidden" name="n" id="n" value=""/>
  <input type="hidden" id="filestatus" value="<?php echo $filestatus; ?>"/>
  <input type="hidden" name="loadtype" id="loadtype" value="standard"/>
  <div class="row">
  <div id="uploadstandard">
    <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="500" height="50"
			codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
      <param name="movie" value="swf/filesender.swf" />
      <param name="quality" value="high" />
      <param name='flashVars' value='<?php echo $flashVARS ?>'/>
      <param name="allowScriptAccess" value="sameDomain" />
      <embed src="swf/filesenderup.swf" quality="high" 
				width="500" height="50" name="filesender" align="middle"
                flashvars='<?php echo $flashVARS ?>'
				play="true"
				loop="false"
				quality="high"
				allowScriptAccess="sameDomain"
				type="application/x-shockwave-flash"
				pluginspage="http://www.adobe.com/go/getflashplayer">
      </embed>
    </object>
    <div id="uploadstandardspinner" style="padding-top:10px"><img src="images/ajax-loader-sm.gif" border=0 align="left" style="padding-right:6px"/><?php echo _UPLOADING_WAIT; ?></div>
    <BR />
  </div>
  <div id="uploadhtml5">
   <input type="file" name="fileToUpload" id="fileToUpload" onChange="fileSelected();"/>  
   <input type="button" onClick="startupload()" value="Upload" id="uploadbutton" />
    <div id="fileInfo">
      <div id="fileName"></div>
      <div id="fileSize"></div>
      <div id="fileType"></div>
    </div>
    <div class="row"></div>
  </div>
  <div id="progress_container">
    <div id="progress_bar">
      <div id="progress_completed"></div>
      <br />
    </div>
  </div>
  <div id="transferSpeedInfo"></div>
  <div id="timeRemainingInfo"></div>
</form>
<div id="dialog-toolarge" title="Error">
  <p>This file is larger than 2Gb. Please use a HTML5 enabled browser to upload larger files.</p>
</div>
<div id="dialog-invalidfilename" title="Error">
  <p>The name of the file you are uploading is invalid. Please rename your file and try again.</p>
</div>