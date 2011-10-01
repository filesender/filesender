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

// when cancelling an upload we need to wait till the chunk is complete before allowing the cancel to happen
// setting cancell upload to true will trigger the upload to stop before uploading the next chunk
var cancelUploadStatus = "false";
var bytesUploaded = 0;
var bytesTotal = 0;
var previousBytesLoaded = 0;
var intervalTimer = 0;
var currentlocation = 0;
var filename = "";
var chunksize = 2000000;
var uploadURI = "fs_upload.php";
var filesize = 0;

function gearsActive(){
	//alert("gg");
	//if(window.File && window.FileReader && window.FileList && window.Blob && window.FormData){
	//getFlexApp('filesender').gearsActive("true");	
	//} else {
	//getFlexApp('filesender').gearsActive("false");	
	//}
//if (!window.google || !google.gears) {
//   getFlexApp('filesender').gearsActive("false");
//	}
//	else
 //   { 
 //  getFlexApp('filesender').gearsActive("true");
 // }
}

function gearsup(){
	$('#fileToUpload').click();
	//return browse();
}

function DoneLoading() {
if(jQuery.browser.mozilla) {
    var img = new Image();
    img.src = 'ff_icon.png';
}
}


/**
 * Display information to the client
 */
function addStatus(s,m){ 
	// return status to flex
	getFlexApp('filesender').returnStatus(s,m);
	return 1;
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
/**
 * Get the minimum of two results.
 */
function min(a,b){ return (a<b?a:b); }

// Gears specific upload settings
// will need to use config settings in next beta

var CHUNK_BYTES		= 2000000; 	// < 200MB Send file in chunks of 2MB (2000000) -50000000 works 50MB
var MAX_FILE_SIZE	= 1000000000000;	// Limit the total upload size
var UPLOAD_RETRIES	= 3;		// Number of retries
var mylist		= {}; 		// Array of file and properties
var fileName		= "";		// Index of mylist that is being processed


/**
 * Get the minimum of two results.
 */
function min(a,b){ return (a<b?a:b); }

/**
 * Open file browser window
 */
 function browsecomplete()
 {
	//var desktop = google.gears.factory.create('beta.desktop');
	mylist		= {};  // clear files list
	files = document.getElementById("fileToUpload").files;
	myList = files;
	//desktop.openFiles( function(files) {
		
		for ( var i = 0; i < files.length; i++ )
		{
			if ( mylist[files[i].name] ){ continue; } // Has the file by the same name already been selected?
			
			mylist[files[i].name] = {
				filename:	files[i].name, 
				uploaded:	0,
				length: 	files[i].size, 
				//blob:		files[i].blob, 
				bytesUploaded: 0,
				status:		(files[i].size>MAX_FILE_SIZE?"File too large":"Pending")};
			
			//addStatus( "Selected: " + files[i].name + " " + files[i].blob.length,"msg" );
			addStatus( files[i].size,"filesize");
			addStatus( files[i].name,"filename");
   
		}
		
		
		
		//$('#upload').html('<a href="#upload" onclick="return upload();">Upload</a>');
	//},
    //{ singleFile: true  }
    //  { singleFile: true }
	//);
	 
 }
 
function browse(){

document.getElementById("fileToUpload").click();	
}

function setResumeposition(resumePosition,fileNm)
{
	
	//resumePosition = (parseInt(rleft) * 10000000) + parseInt(rright);
	//mylist[fileNm].uploaded =  parseInt(resumePosition);//parseInt(resumePosition);
	bytesUploaded = parseInt(resumePosition);
	addStatus( resumePosition,"msg");
}


function uploadFile(voucheruid) {
		
		
		
		// move to next chunk
		var file = document.getElementById("fileToUpload").files[0];
		//bytesUploaded = mylist[file].uploaded;
		var txferSize = chunksize;
		//alert(bytesUploaded + ":"+txferSize + ":"+ filesize);
	  	filename = file.name;
	  	filesize = file.size;
		
		if(bytesUploaded > bytesTotal -1 )
			{
			//var filecontrol = document.getElementById("fileToUpload");
       		// Remove the new file control.
    		//filecontrol.parentNode.removeChild(filecontrol);
			//unlockformfields();
			// encodeURIComponent file name before sending
			//$("#fileName").val(encodeURIComponent(filename));
			//document.forms["form1"].submit();
			addStatus( "","complete");
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
		} else
		if(file && file.mozSlice )
		{
			var blob = file.mozSlice(bytesUploaded, txferSize+bytesUploaded);
		} else
		//if(file && file.slice )
		{
			var blob = file.slice(bytesUploaded, txferSize);
		}
		//alert("");
	var fileName = file.name; //Grab the file name
    var fileSize = file.size; //Grab the file size
    var fileType = file.type; //Grab the file type
    var reader = new FileReader(); //Create FileReader object to read the image data
    reader.readAsBinaryString(blob); //Start reading the blob out as binary data
    reader.onload = function() { //Execute this when the blob is successfully read
 
	var boundary = "fileboundary"; //Boundary name
	var uri = 'fs_gears_upload.php?n='+encodeURIComponent(fileName)+'&b='+encodeURIComponent(bytesUploaded)+'&vid='+voucheruid+'&total='+fileSize;
	//var uri = (uploadURI + "?n="+encodeURIComponent(fileName)+"&total="+fileSize+"&type=chunk&vid="+voucheruid); //Path to script for handling the file sent
	var xhr = new XMLHttpRequest(); //Create the object to handle async requests
	xhr.onreadystatechange = processReqChange;
	xhr.upload.addEventListener("progress", uploadProgress, false);
	xhr.open("POST", uri, true); //Open a request to the web address set
	xhr.setRequestHeader("Content-Disposition"," attachment; name='fileToUpload'"); 
	xhr.setRequestHeader("Content-Type", "application/octet-stream");
	xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    //Set up the body of the POST data includes the name & file data.
    var bodySend =  "";
	bodySend = reader.result;
	xhr.send(blob);
	//xhr.sendAsBinary(bodySend);

	function processReqChange(){
	    if (xhr.readyState == 4) {
	    	if (xhr.status == 200) {
			bytesUploaded = bytesUploaded + txferSize;//parseFloat(xhr.responseText);
			var prcnt = Math.ceil( ( bytesUploaded/bytesTotal ) * 100 );
			addStatus(prcnt,"percentage");
			//updatepb(bytesUploaded,bytesTotal);
			if (cancelUploadStatus == "true")
			{
				cancelUploadStatus = "";
				addStatus("upload Cancelled","cancelled");
				
			} else {
			uploadFile(voucheruid);
			}
			} else {
			errorDialog("There was a problem retrieving the data:\n" + req.statusText);
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
		speed = (Math.round(bytesDiff * 100/(1024*1024))/100).toString() + "MBps";
    	else if (bytesDiff > 1024)
    	speed =  (Math.round(bytesDiff * 100/1024)/100).toString() + "KBps";
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

function uploadProgress(evt) {
	}

function uploadFailed(evt) {
	clearInterval(intervalTimer);
	erorDialog("An error occurred while uploading the file.");  
}  
  
function uploadCanceled(evt) {
	clearInterval(intervalTimer);
	erorDialog("The upload has been canceled by the user or the browser dropped the connection.");  
	}  


function upload(voucheruid)
{
	
		var file = document.getElementById("fileToUpload").files[0];
        var fileSize = file.size;
        bytesTotal = fileSize;
		//bytesUploaded = 0;
	    previousBytesLoaded = 0;
	    intervalTimer = 0;
		currentlocation = 0;
		filename = "";
		filesize = 0;
		uploadFile(voucheruid);
}
// old
function _upload(voucheruid)
{
	var chunkLength, chunk;
	
	/**
	 * Loop through the files and upload the next file/chunk
	 */
	
	for ( file in mylist ) if ( ( mylist[file].uploaded < mylist[file].length && !mylist[file].error ) )
	{
		
		/**
		 * what is the current filename
		 */
		fileName = file;
		chunkLength = min( mylist[file].uploaded + CHUNK_BYTES, mylist[file].length);
		/**
		 * Get the next chunk to send.
		 */
		 //addStatus("","aaaaa");
		 chunk = mylist[file].blob.slice( mylist[file].uploaded, (chunkLength - mylist[file].uploaded) );
		
		/**
		 * Send Chunk
		 */
		
		sendChunk( mylist[file], chunk, mylist[file].uploaded, chunkLength, mylist[file].length,voucheruid );
		break;
	}
}

function cancelUpload ()
{
	cancelUploadStatus = "true";
}

function sendChunk ( entry, chunk, start, end, total,voucheruid )
{
	var req = google.gears.factory.create('beta.httprequest');
	var prcnt = Math.ceil( ( end/total ) * 100 );
	addStatus(prcnt,"percentage");
	/**
	 * Start Post
	 */
	req.open('POST', 'fs_gears_upload.php?n='+encodeURIComponent(fileName)+'&b='+encodeURIComponent(start)+'&vid='+voucheruid+'&total='+total );
	//req.open('POST', 'upload2.php?n='+encodeURIComponent(fileName)+'&b='+encodeURIComponent(start) );
	
	/**
	 * Assign Headers
	 */ 
	
	var h = { 'Content-Disposition'	: 'attachment; filename="' + fileName + '"', 
					'Content-Type' 	: 'application/octet-stream',
					'Content-Range'	: 'bytes ' + start + '-' + end + '/' + total };
	
	
	for( var x in h ) if (h.hasOwnProperty(x)) { 
	req.setRequestHeader( x, h[x] );
	addStatus(x + ":" + h[x],"msg");
	}
	
	/**
	 * Build Response function
	 */
	 
	req.onreadystatechange = function(){

			if (cancelUploadStatus == "true")
			{
				//var req = google.gears.factory.create('beta.httprequest');
				req.abort();
				mylist		= {};  // clear files list
				addStatus("upload Cancelled","cancelled");
				cancelUploadStatus = "false";
				return;
			}
			
		//addStatus(prcnt,"percentage");
		if(req && req.responseText == "Error"){
		req.abort();
		mylist		= {};  // clear files list
		addStatus("Error Uploading","error");
		}
		
		if(req && req.responseText == "ErrorAuth"){
		req.abort();
		mylist		= {};  // clear files list
		addStatus("Error Unable to Authenticate","errorauth");
		}
		
		if (req && req.readyState == 4 && addStatus( "Resp: (" + req.status + ")" ) && req.status == 200 ) {
			entry.uploaded = end;
			//addStatus( fileName + ( (end + 1) >= total ? " Finished" : ' Upload: so far ' + prcnt + '%' ),"msg" );
			if( (end + 1) >= total){
			addStatus( "","complete");
			}
			upload(voucheruid);
		}
	}

	/**
	 * Send Chunk
	 */
	req.send(chunk);

}
	/**
	 * return reference to the flash app to allow communication between gears and flash
	 */
	 
function gup( name )
{
	// returns URL string specified by name(vid)
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var results = regex.exec( window.location.href );
	var gearsStatus = false;
	//if (!window.google || !google.gears) {
  	//	gearsStatus = false;
	//}
	//else
    // { 
	//	gearsStatus = true;
  //}
  if(window.File && window.FileReader && window.FileList && window.Blob && window.FormData){
	gearsStatus = true;	
	} else {
	gearsStatus = false;	
	}
	if( results == null )
   getFlexApp('filesender').checkVoucher("",gearsStatus);
  else
    getFlexApp('filesender').checkVoucher(results[1],gearsStatus); 
}
