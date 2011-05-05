// JavaScript Document

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

var bytesUploaded = 0;
var bytesTotal = 0;
var previousBytesLoaded = 0;
var intervalTimer = 0;
var currentlocation = 0;
var filename = "";
var chunksize = 2000000;
var uploadURI = "fs_upload.php";
var filesize = 0;
// a unique is created for each file that is uploaded.
// An object with the unique stores all relevant information about the file upload
	  
 	function fileSelected() {
	
		//	document.getElementById('MSG').innerHTML = "";
        var file = document.getElementById('fileToUpload').files[0];
        var fileSize = file.size;
        bytesTotal = fileSize;
		bytesUploaded = 0;
	    previousBytesLoaded = 0;
	    intervalTimer = 0;
		currentlocation = 0;
		filename = "";
		filesize = 0;
		
	  
	  if (validatefilename(file.name) == false) 
		{
		 $("#dialog-invalidfilename").dialog('open')
		} else if (!validateForm())
		{
		 alert("Invalid or missing email");

		} else {
		
        document.getElementById('fileInfo').style.display = 'block';
        document.getElementById('fileName').innerHTML = 'Name: ' + file.name;
        document.getElementById('fileSize').innerHTML = 'Size: ' + fileSize;
        document.getElementById('fileType').innerHTML = 'Type: ' + file.type;
		obj('n').value= file.name;
		obj('total').value = fileSize;
		
      }
	}



function startupload()
{
		
		// hide upload button
		var progressBar = obj('progress_bar');
      	progressBar.style.display = 'block';
      	progressBar.style.width = '0px';      
		$('#progress_completed').html("0%");
		
		// check if file is already on the server
		var file = document.getElementById('fileToUpload').files[0];
		var fileSize = file.size;
		var fileName = file.name;
				
		$.ajax({
  		url: uploadURI + '?n='+fileName+'&total='+fileSize+'&vid=&type=filesize',
  		success: function(data) {
		currentBytesUpload = parseFloat(data);
		uploadFile(currentBytesUpload);
  		}
		});	
	
		//intervalTimer = setInterval(updateTransferSpeed, 500);
}

function uploadFile(currentBytesUpload) {
		
		bytesUploaded = currentBytesUpload;
		
		// move to next chunk
		var file = document.getElementById('fileToUpload').files[0];
		var txferSize = chunksize;
		
	  	filename = file.name;
	  	filesize = file.size;

		if(bytesUploaded > bytesTotal -1 )
			{
			document.forms["form1"].submit();
			return;
			} 
			
		if(bytesUploaded + txferSize > filesize)
		{
		txferSize = filesize - bytesUploaded;
		}
		// check if firefox or Chrome slice supported 
		if(file && file.webkitSlice )
		{
			var blob = file.webkitSlice(bytesUploaded, txferSize+bytesUploaded);
		}
		if(file && file.mozSlice )
		{
			var blob = file.mozSlice(bytesUploaded, txferSize+bytesUploaded);
		}
		if(file && file.slice )
		{
			var blob = file.slice(bytesUploaded, txferSize);
		}
		
	var fileName = file.name; //Grab the file name
    var fileSize = file.size; //Grab the file size
    var fileType = file.type; //Grab the file type
    var reader = new FileReader(); //Create FileReader object to read the image data
    reader.readAsBinaryString(blob); //Start reading the blob out as binary data
    reader.onload = function() { //Execute this when the blob is successfully read
 
        var boundary = "fileboundary"; //Boundary name
        var uri = uploadURI + "?n="+fileName+"&total="+fileSize+"&type=chunk"; //Path to script for handling the file sent
 
        var xhr = new XMLHttpRequest(); //Create the object to handle async requests
 		xhr.onreadystatechange = processReqChange;
 		xhr.upload.addEventListener("progress", uploadProgress, false);
        xhr.open("POST", uri, true); //Open a request to the web address set
      	xhr.setRequestHeader("Content-Disposition"," attachment; name='fileToUpload'"); // n='" + fileName + "'; total='"+ fileSize +"'; type='chunk'"); //chunk_id = '"+chunk_id+"'; chunksize='"+chunksize+"'; totalchunks='"+totalchunks+"'");
		xhr.setRequestHeader("Content-Type", "application/octet-stream");
        //Set up the body of the POST data includes the name & file data.
        var bodySend =  "";
		bodySend = reader.result;

	    //Use sendAsBinary to send binary data. If you are sending text just use send.
		//document.getElementById('uploadResponse').innerHTML = "Uploading...";
		xhr.send(bodySend);
	    //xhr.sendAsBinary(bodySend);

function processReqChange(){
     if (xhr.readyState == 4) {
            if (xhr.status == 200) {
			bytesUploaded = parseFloat(xhr.responseText);
			updatepb(bytesUploaded,bytesTotal);	
			uploadFile(bytesUploaded);
			
           } else {
                alert("There was a problem retrieving the data:\n" + req.statusText);
                alert("Status Code = "+xhr.status);
            // 	alert("There was a problem retrieving the data:\n");
            //	alert("Failed : object = "+xhr);
            //  alert(xhr.responseXML);
            // 	alert("Failed : response = "+xhr.responseText);
            //	alert("Failed : status = "+xhr.statusText);
            }
        }else{
		
     }
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
          speed = (Math.round(bytesDiff * 100/(1024*1024))/100).toString() + 'MBps';
        else if (bytesDiff > 1024)
          speed =  (Math.round(bytesDiff * 100/1024)/100).toString() + 'KBps';
        else
          speed = bytesDiff.toString() + 'Bps';
        document.getElementById('transferSpeedInfo').innerHTML = speed;
        //document.getElementById('timeRemainingInfo').innerHTML = '| ' + secondsToString(secondsRemaining);        
      }

      function secondsToString(seconds) {        
        var h = Math.floor(seconds / 3600);
        var m = Math.floor(seconds % 3600 / 60);
        var s = Math.floor(seconds % 3600 % 60);
        return ((h > 0 ? h + ":" : "") + (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + ":" : "0:") + (s < 10 ? "0" : "") + s);
      }

      function uploadProgress(evt) {

		
      }

      function uploadFailed(evt) {
        clearInterval(intervalTimer);
        alert("An error occurred while uploading the file.");  
      }  
  
      function uploadCanceled(evt) {
        clearInterval(intervalTimer);
        alert("The upload has been canceled by the user or the browser dropped the connection.");  
      }  
	  