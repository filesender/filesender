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
 ?>
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

	// --------------------------
	// standard upload functions
	// --------------------------
	 
	function startstandardupload() {
		if(!validateForm()) { return;};
		
		// display progress
		obj('progress_container').style.display = "block";
		var progressBar = obj('progress_bar');
        progressBar.style.display = 'block';
        progressBar.style.width = '0px';    
		
		// set initial progress at 0%
		$('#progress_completed').html("0%");
		
		uploadprogress();
	}
	
	// called continuously 
	function uploadprogress(){
		
		$.ajax({
  		url: 'info.php?ID='+uploadid,
  		dataType: 'json',
  		data: {},
  		success: function(data) {
  			if(data){
				if(data == 'false') { //
				// load spinner
				obj('uploadstandardspinner').style.display = "block";
				obj('progress_container').style.display = "none";
				return;
				}
    			if(data != null)
				{
				updatepb(data.bytes_uploaded,data.bytes_total);
				}
			}
			window.setTimeout("uploadprogress()",3000);
  			}
		});
	}

	// --------------------------
	// HTML5 upload functions
	// --------------------------

     function fileSelected() {
        var file = obj('fileToUpload').files[0];
        var fileSize = 0;
        if (file.size > 1024 * 1024)
          fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
        else
          fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';
        obj('fileInfo').style.display = 'block';
        obj('fileName').innerHTML = 'Name: ' + file.name;
        obj('fileSize').innerHTML = 'Size: ' + fileSize;
        obj('fileType').innerHTML = 'Type: ' + file.type;
		//obj('DownloadLink').innerHTML = "";
      }
      
      function uploadFile() {
	  
	  // validate first
	   if(!validateForm()) { return;};
	   
	    previousBytesLoaded = 0;
       // obj('uploadResponse').style.display = 'none';
        //obj('progressNumber').innerHTML = '';
        var progressBar = obj('progress_bar');
        progressBar.style.display = 'block';
        progressBar.style.width = '0px';        
    
		// set initial progress at 0%
		$('#progress_completed').html("0%");
        /* If you want to upload only a file along with arbitary data that
           is not in the form, use this */
       var fd = new FormData();
        fd.append("fileToUpload", obj('fileToUpload').files[0]);
	    fd.append("fileto", obj('fileto').value);
		fd.append("filefrom", obj('filefrom').value);
		fd.append("filesubject", obj('filesubject').value);
		fd.append("filemessage", obj('filemessage').value);
		fd.append("fileexpirydate", obj('fileexpirydate').value);
		fd.append("filesize", obj('fileToUpload').files[0].size);
		fd.append("filevoucheruid", obj('filevoucheruid').value);
		fd.append("filestatus", obj('filestatus').value);
        /* If you want to simply post the entire form, use this */
       // var fd = obj('form1').getFormData();
        
		obj('progress_container').style.display = "block";
		
        var xhr = new XMLHttpRequest();        
        xhr.upload.addEventListener("progress", uploadProgress, false);
        xhr.addEventListener("load", uploadComplete, false);
        xhr.addEventListener("error", uploadFailed, false);
        xhr.addEventListener("abort", uploadCanceled, false);
        xhr.open("POST", "fs_uploadit5.php");
        xhr.send(fd);

        intervalTimer = setInterval(updateTransferSpeed, 500);
      }

      function updateTransferSpeed() {
        var currentBytes = bytesUploaded;
        var bytesDiff = currentBytes - previousBytesLoaded;
        if (bytesDiff == 0) return;
        previousBytesLoaded = currentBytes;
        bytesDiff = bytesDiff * 2;
        var bytesRemaining = bytesTotal - previousBytesLoaded;
        var secondsRemaining = bytesRemaining / bytesDiff;

        var speed = "";
        if (bytesDiff > 1024 * 1024)
          speed = (Math.round(bytesDiff * 100/(1024*1024))/100).toString() + 'MBps';
        else if (bytesDiff > 1024)
          speed =  (Math.round(bytesDiff * 100/1024)/100).toString() + 'KBps';
        else
          speed = bytesDiff.toString() + 'Bps';
        obj('').innerHTML = speed;
        obj('timeRemainingInfo').innerHTML = '| ' + secondsToString(secondsRemaining);        
      }

      function secondsToString(seconds) {        
        var h = Math.floor(seconds / 3600);
        var m = Math.floor(seconds % 3600 / 60);
        var s = Math.floor(seconds % 3600 % 60);
        return ((h > 0 ? h + ":" : "") + (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + ":" : "0:") + (s < 10 ? "0" : "") + s);
      }

      function uploadProgress(evt) {
        if (evt.lengthComputable) {
        	bytesUploaded = evt.loaded;
        	bytesTotal = evt.total;
     		updatepb(bytesUploaded,bytesTotal);
       } else {
        	//obj('progress_bar').innerHTML = 'unable to compute';
        }  
      }


      function uploadComplete(evt) {
        clearInterval(intervalTimer);
   		// goto completed page 
		//------------------------
		document.location = "index.php?s=complete";
		//obj('DownloadLink').innerHTML = "<a href='download.php?name="+obj('fileToUpload').files[0].name + "'>Download File</a>";
      }  
  
      function uploadFailed(evt) {
        clearInterval(intervalTimer);
        alert("An error occurred while uploading the file.");  
      }  
  
      function uploadCanceled(evt) {
        clearInterval(intervalTimer);
        alert("The upload has been canceled by the user or the browser dropped the connection.");  
      }  
	  


	function toggleTOG()
	{
	if (obj('TOG').style.display == "block")
	{
	obj('TOG').style.display = "none"
	} else {
	obj('TOG').style.display = "block"
	} 
	}
	

	function completeupload()
	{
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
		  
		if (percentComplete == 100) {
			obj('progressInfo').style.display = 'none';
		}
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


    </script>
<?php echo '<div id="pageheading">'._NEW_UPLOAD.'</div>'; ?> 
<form id="form1" enctype="multipart/form-data" target="ifr" method="post" action="fs_uploadit5.php" onsubmit="startstandardupload()">
  <div align="right">
    <p align="left"><?php echo _TO; ?>:
      <input name="fileto" type="text" id="fileto" size="40">
      <input type="hidden" name="UPLOAD_IDENTIFIER" value="<?php echo $id;?>" />
      <?php echo _EMAIL_SEPARATOR_MSG; ?></p>
    <p align="left"><?php echo _FROM; ?>: <?php echo $senderemail ?> 
  </div>
  <input name="filefrom" type="hidden" id="filefrom" value="<?php echo $senderemail ?>" size="40">
  </p>
  <p align="left"><?php echo _SUBJECT; ?>:
    <input name="filesubject" type="text" id="filesubject" size="40">
  </p>
  <p align="left"><?php echo _MESSAGE; ?>:
    <input name="filemessage" type="text" id="filemessage" size="40" />
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
  <input type="hidden" id="filestatus" value="<?php echo $filestatus; ?>"/>
  <input type="hidden" name="loadtype" id="loadtype" value="standard"/>
  <input type="file" name="fileToUpload" id="fileToUpload" onChange="fileSelected();"/>
  <div class="row">
  <div id="uploadstandard">
    <iframe id="ifr" width="0px" height="0px" name="ifr" style="display:none"></iframe>
    <div id="uploadstandardspinner" style="padding-top:10px"><img src="images/ajax-loader-sm.gif" border=0 align="left" style="padding-right:6px"/><?php echo _UPLOADING_WAIT; ?></div>
    <BR />
    <input id="stdupload" type="submit" value="Upload" />
  </div>
  <div id="uploadhtml5">
    <input id="html5upload" type="button" onClick="uploadFile()" value="Upload" />
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
