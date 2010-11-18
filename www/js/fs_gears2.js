// JavaScript Document
/*
 *  Filsender www.filesender.org
 *      
 *  Copyright (c) 2009, Ricoshae Pty Ltd
 * 	All rights reserved.
 * 
 * 	Redistribution and use in source and binary forms, with or without
 *	modification, are permitted provided that the following conditions are met:
 *	* 	Redistributions of source code must retain the above copyright
 *   		notice, this list of conditions and the following disclaimer.
 *   	* 	Redistributions in binary form must reproduce the above copyright
 *   		notice, this list of conditions and the following disclaimer in the
 *   		documentation and/or other materials provided with the distribution.
 *   	* 	Neither the name of Ricoshae Pty Ltd nor the
 *   		names of its contributors may be used to endorse or promote products
 *   		derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY Ricoshae Pty Ltd ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Ricoshae Pty Ltd BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * Resources: Original code idea from
 * 		Brad Neuberg (http://codinginparadise.org)
 * 		Gears (http://gears.google.com)
 * 
 * 		Modified by @author Andrew Dodson
 * 		Modified by @author chris Richter
 */
 
function gearsActive(){
if (!window.google || !google.gears) {
   getFlexApp('filesender').gearsActive("false");
	}
	else
     { 
   getFlexApp('filesender').gearsActive("true");
  }
}

function gearsup(){
	return browse();
}
/**
 * Display information to the client
 */
function addStatus(s,m){ 
	// return status to flex
	getFlexApp('filesender').returnStatus(s,m);
	return 1;
}

function addChunk(s,m){ 
	// return status to flex
	getFlexApp('filesender').returnChunk(s,m);
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
 
var CHUNK_BYTES		= 2000000; 	// Send file in packets of 200KB (200000)
var MAX_FILE_SIZE	= 1000000000000;	// Limit the total up load size
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
 
function browse(){
	
	var desktop = google.gears.factory.create('beta.desktop');
	mylist		= {};  // clear files list
	
	desktop.openFiles( function(files) {
		
		for ( var i = 0; i < files.length; i++ )
		{
			if ( mylist[files[i].name] ){ continue; } // Has the file by the same name already been selected?
			
			mylist[files[i].name] = {
				filename:	files[i].name, 
				uploaded:	0,
				length: 	files[i].blob.length, 
				blob:		files[i].blob, 
				bytesUploaded: 0,
				status:		(files[i].blob.length>MAX_FILE_SIZE?"File too large":"Pending")};
			
			//addStatus( "Selected: " + files[i].name + " " + files[i].blob.length,"msg" );
			addStatus( files[i].blob.length,"filesize");
		    addStatus( files[i].name,"filename");
		}
		$('#upload').html('<a href="#upload" onclick="return upload();">Upload</a>');
	},
    { singleFile: true  }
    //  { singleFile: true }
	);
}

function setResumeposition(resumePosition,fileNm)
{
	
	//resumePosition = (parseInt(rleft) * 20000000) + parseInt(rright);
	mylist[fileNm].uploaded =  parseInt(resumePosition);//parseInt(resumePosition);
	addStatus( resumePosition,"msg");
}

function upload()
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
		 chunk = mylist[file].blob.slice( mylist[file].uploaded, (chunkLength - mylist[file].uploaded) );
		
		//addChunk(chunk,"achunk");
		/**
		 * Send Chunk
		 */
		// send chunk to flash to upload?
	//	var bytes = [];
		
//var nBytes = chunk.length;
//var nStep = 0;

//for (nStep = 0; nStep < nBytes; nStep += 1024) {
//  var nLen = nBytes - nStep;
//  if( nLen > 1024 ) nLen = 1024;
//  bytes = bytes.concat(chunk.getBytes(nStep, nLen));
//}

		
//		addChunk(bytes,"achunk");
		
		sendChunk( mylist[file], chunk, mylist[file].uploaded, chunkLength, mylist[file].length, fileName );
		break; 
	}
}

function cancelUpload ()
{
	var req = google.gears.factory.create('beta.httprequest');
	req.abort();
	mylist		= {};  // clear files list
	addStatus("upload Cancelled","msg");
}

function sendChunk ( entry, chunk, start, end, total, filename )
{
	var req = google.gears.factory.create('beta.httprequest');
	var prcnt = Math.ceil( ( end/total ) * 100 );
	addStatus(prcnt,"percentage");
	 
	/**   
	 * Build Response function
	 */
	  
	//var data = desktop.getDragData(event, 'application/x-gears-files');

    var boundary = '------multipartformboundary' + (new Date).getTime();
    var dashdash = '--';
    var crlf     = '\r\n';

    /* Build RFC2388 string. */
    var builder = google.gears.factory.create('beta.blobbuilder');

    builder.append(dashdash);
    builder.append(boundary);
    builder.append(crlf);

   // for (var i in data.files) {

   //     var file = data.files[i];

        /* Generate headers. */
        builder.append('Content-Disposition: form-data; name="user_file[]"');
        if (filename) {
            builder.append('; filename="' + filename + '"');
        }
        
        builder.append(crlf);

        builder.append('Content-Type: application/octet-stream');
        builder.append(crlf);
        builder.append(crlf); 

        /* Append binary data. */
        builder.append(chunk);
        builder.append(crlf);

        /* Write boundary. */
        builder.append(dashdash);
        builder.append(boundary);
        builder.append(crlf); 
   // }

    /* Mark end of the request. */
    builder.append(dashdash);
    builder.append(boundary);
    builder.append(dashdash);
    builder.append(crlf);     
    
   
    
    
	req.onreadystatechange = function(){
	
		
		if(req.responseText == "Error"){
		req.abort();
		mylist		= {};  // clear files list
		addStatus("Error Uploading","error");
		}
		if (req.readyState == 4 && addStatus( "Resp: (" + req.status + ")" ) && req.status == 200 ) {
			entry.uploaded = end;
			//addStatus( fileName + ( (end + 1) >= total ? " Finished" : ' Upload: so far ' + prcnt + '%' ),"msg" );
			if( (end + 1) >= total){
			addStatus( "","complete");
			}
			upload();
		}
	} 
	
	//req.open("POST", "fs_gears_upload2.php");
	req.open("POST", "fs_gears_upload2.php");
    req.setRequestHeader('content-type', 'multipart/form-data; boundary=' + boundary);
    req.send(builder.getAsBlob());

	/**
	 * Send Chunk
	 */
	//req.send(chunk);
}

	/**
	 * return reference to the flash app to allow communication between gears and flash
	 */
	 
function gup( name )
{
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
   getFlexApp('filesender').checkVoucher("");
  else
    getFlexApp('filesender').checkVoucher(results[1]); 
}
