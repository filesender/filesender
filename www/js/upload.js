// JavaScript Document

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
 
// HTML5 Upload functions
// when cancelling an upload we need to wait till the chunk is complete before allowing the cancel to happen
// setting cancell upload to true will trigger the upload to stop before uploading the next chunk
// JavaScript Document


<!--
// -----------------------------------------------------------------------------
// Globals
// Major version of Flash required
var requiredMajorVersion = 10;
// Minor version of Flash required
var requiredMinorVersion = 0;
// Minor version of Flash required
var requiredRevision = 0;
// -----------------------------------------------------------------------------
// -->

//var bytesUploaded = 0;
//var bytesTotal = 0;
//var previousBytesLoaded = 0;
var intervalTimer = 0;
//var currentlocation = 0;
//var filename = "";
//var chunksize = 2000000;
var uploadURI = "fs_upload.php";
var fdata = []; // array of each file to be uploaded
var n = 0; // file int currently uploading

// a unique is created for each file that is uploaded.
// An object with the unique stores all relevant information about the file upload
	
	  
 	function fileSelected() {
		fdata[n] = Array;
		//	document.getElementById('MSG').innerHTML = "";
        var file = document.getElementById("fileToUpload").files[0];
        fdata[n].fileSize = file.size;
		fdata[n].bytesTotal = file.size;
		fdata[n].bytesUploaded = 0;
	    fdata[n].previousBytesLoaded = 0;
	    fdata[n].intervalTimer = 0;
		fdata[n].currentlocation = 0;
		fdata[n].filename = file.name;
		fdata[n].filetype = file.type;
		//fdata[n].filesize = 0;
		
	  	if(validate_file()) { 
			$("#uploadbutton").show(); 
			$("#fileInfoView").show();
			$("#fileName").html(nameLang + ': ' + fdata[n].filename);
			$("#fileSize").html(sizeLang + ': ' + readablizebytes(fdata[n].fileSize));
		} else { 
			$("#uploadbutton").hide();
			$("#fileInfoView").hide();
			$("#fileName").html("");
			$("#fileSize").html("");
		};
	}

	function startupload()
	{
		fdata[n].bytesUploaded = 0;
		
		// validate form data and return filesize or validation error
		// load form into json array
		var query = $("#form1").serializeArray(), json = {};
		for (i in query) { json[query[i].name] = query[i].value; } 
		// add file information fields
		json["fileoriginalname"] = fdata[n].filename;
		json["filesize"] = parseInt(fdata[n].fileSize);
		json["vid"] = vid;

		$.ajax({
  		type: "POST",
  		url: "fs_upload.php?type=validateupload&vid="+vid,
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
		if(result == "err_notauthenticated") { errorDialog(errmsg_notauthenticated);} // not authenticated
		if(result == "err_tomissing") { $("#fileto_msg").show();} // missing email data
		if(result == "err_expmissing") { $("#expiry_msg").show();} // missing expiry date
		if(result == "err_exoutofrange") { $("#expiry_msg").show();} // expiry date out of range
		if(result == "err_invalidemail") { $("#fileto_msg").show();} // 1 or more emails invalid
		if(result == "err_invalidfilename") { $("#file_msg").show();} // invalid filename
		if(result == "err_invalidextension") { $("#extension_msg").show();} //  invalid extension
		if(result == "err_nodiskspace") { errorDialog(errmsg_disk_space);}
		})
		}
		if(data.status && data.status == "complete")
		{
		$("#fileToUpload").hide();// hide Browse
		$("#selectfile").hide();// hide Browse message
		$("#uploadbutton").hide(); // hide upload
		$("#cancelbutton").show(); // show cancel
		// show upload progress dialog
		$("#dialog-uploadprogress").dialog("open");
		// no error so use result as current bytes uploaded for file resume 
		vid = data.vid;
		fdata[n].bytesUploaded = parseFloat(data.filesize);
		updatepb(fdata[n].bytesUploaded, fdata[n].fileSize);	
		uploadFile();
		}
		},error:function(xhr,err){
			// error function to display error message e.g.404 page not found
			ajaxerror(xhr.readyState,xhr.status,xhr.responseText);
		}
  		});
	}

function uploadFile() {
		
		// move to next chunk
		var file = document.getElementById("fileToUpload").files[0];
		var txferSize = chunksize;

		if(fdata[n].bytesUploaded > fdata[n].bytesTotal -1 )
		{
		var query = $("#form1").serializeArray(), json = {};
		$.ajax({
  		type: "POST",
  		url: "fs_upload.php?type=uploadcomplete&vid="+vid
		,success:function( data ) {
		var data =  parseJSON(data);
		if(data.errors)
		{
		$.each(data.errors, function(i,result){
		if(result == "err_cannotrenamefile") { window.location.href="index.php?s=uploaderror";} //	
		if(result == "err_emailnotsent") { window.location.href="index.php?s=emailsenterror";} //
		if(result == "err_filesizeincorrect") { window.location.href="index.php?s=filesizeincorrect";} //	
		})
		}
		if(data.status && data.status == "complete"){window.location.href="index.php?s=complete";}
		if(data.status && data.status == "completev"){window.location.href="index.php?s=completev";}
		}
		,error:function(xhr,err){
			// error function to display error message e.g.404 page not found
			ajaxerror(xhr.readyState,xhr.status,xhr.responseText);
		}
		});
		return;
		} 
			
		if(fdata[n].bytesUploaded + txferSize > fdata[n].fileSize)
		{
		txferSize = fdata[n].fileSize - fdata[n].bytesUploaded;
		}
		// check if firefox or Chrome slice supported 
		
		if(file && file.webkitSlice )
		{
			var blob = file.webkitSlice(fdata[n].bytesUploaded, txferSize+fdata[n].bytesUploaded);
		} else
		if(file && file.mozSlice )
		{
			var blob = file.mozSlice(fdata[n].bytesUploaded, txferSize+fdata[n].bytesUploaded);
		} else
		//if(file && file.slice )
		{
			var blob = file.slice(fdata[n].bytesUploaded, txferSize);
		}
	
	var boundary = "fileboundary"; //Boundary name
	var uri = (uploadURI + "?type=chunk&vid="+vid); //Path to script for handling the file sent
	var xhr = new XMLHttpRequest(); //Create the object to handle async requests
	xhr.onreadystatechange = processReqChange;
	xhr.upload.addEventListener("progress", uploadProgress, false);
	xhr.open("POST", uri, true); //Open a request to the web address set
	xhr.setRequestHeader("Content-Disposition"," attachment; name='fileToUpload'"); 
	xhr.setRequestHeader("Content-Type", "application/octet-stream");
	xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    //Set up the body of the POST data includes the name & file data.
	xhr.send(blob);

	function processReqChange(){
		 if (xhr.readyState == 4) {
	    	if (xhr.status == 200) {
				if(xhr.responseText == "ErrorAuth")
				{
					$("#dialog-autherror").dialog("open");
					return;			
				}
			fdata[n].bytesUploaded = parseFloat(xhr.responseText);
			updatepb(fdata[n].bytesUploaded,fdata[n].bytesTotal);	
			uploadFile();
			} else {
			errorDialog("There was a problem retrieving the data:\n" + req.statusText);
			}
		}else{
		}
}

return true;
}

function updateTransferSpeed() {
	var currentBytes = bytesUploaded+(chunksize*(chunk_id -1));
	var bytesDiff = currentBytes - chunksize*(chunk_id -1);//previousBytesLoaded;
    if (bytesDiff == 0) return;
    previousBytesLoaded = currentBytes;
    bytesDiff = bytesDiff * 2;
    var bytesRemaining = bytesTotal - previousBytesLoaded;
    var secondsRemaining = bytesRemaining / bytesDiff;
    var speed = "";
    if (bytesDiff > 1024 * 1024)
		speed = (Math.round(bytesDiff * 100/(1024*1024))/100).toString() + "MBps";
    	else if (bytesDiff > 1024)
    	speed =  (Math.round(bytesDiff * 100/1024)/100).toString() + "kBps";
     	else
        speed = bytesDiff.toString() + 'Bps';
       $("#transferSpeedInfo").html(speed);
}

function secondsToString(seconds) {        
        var h = Math.floor(seconds / 3600);
        var m = Math.floor(seconds % 3600 / 60);
        var s = Math.floor(seconds % 3600 % 60);
        return ((h > 0 ? h + ":" : "") + (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + ":" : "0:") + (s < 10 ? "0" : "") + s);
}

// update the progress bar
function updatepb(bytesloaded,totalbytes)
{
	$("#progress_bar").show();
	var percentComplete = Math.round(bytesloaded * 100 / totalbytes);
	var bytesTransfered = '';
	if (bytesloaded > 1024*1024)
		bytesTransfered = (Math.round(bytesloaded * 100/(1024*1024))/100).toString() + 'MB';
	else if (bytesloaded > 1024)
		bytesTransfered = (Math.round(bytesloaded * 100/1024)/100).toString() + 'kB';
	else
		bytesTransfered = (Math.round(bytesloaded * 100)/100).toString() + 'Bytes';
	
		$("#progress_bar").width(percentComplete/100 *$('#progress_container').width());	//set width of progress bar based on the $status value (set at the top of this page)
		$("#progress_bar").html(percentComplete +"% ");
		$("#progress_completed").html(parseInt(percentComplete) + "%(" + bytesTransfered + ")" );	//display the % completed within the progress bar
	  
}

function uploadProgress(evt) {
	}

function uploadFailed(evt) {
	clearInterval(intervalTimer);
	errorDialog("An error occurred while uploading the file.");  
}  
  
function uploadCanceled(evt) {
	clearInterval(intervalTimer);
	erorDialog("The upload has been canceled by the user or the browser dropped the connection.");  
	}  
