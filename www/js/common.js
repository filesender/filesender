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

// display bytes in readable format
// Used in upload
function readablizebytes(bytes)
 {
	if (bytes > 1024*1024*1024)
		bytesdisplay = (Math.round(bytes * 100/(1024*1024*1024))/100).toString() + " GB";
	else if (bytes > 1024*1024)
		bytesdisplay = (Math.round(bytes * 100/(1024*1024))/100).toString() + " MB";
	else if (bytes > 1024)
		bytesdisplay = (Math.round(bytes * 100/1024)/100).toString() + " kB";
	else
		bytesdisplay = (Math.round(bytes * 100)/100).toString() + " Bytes";
	return bytesdisplay;
}	
	
// -------------------
// validation
// -------------------

// Validate FILETO - upload, voucher and files
function validate_fileto()
{
    var isValid = true;

	$("#fileto_msg").hide();
	$("#maxemails_msg").hide();
	// remove white spaces 
	var email = $("#fileto").val();
	email = email.split(" ").join("");
    if(email != "") {
        // if not empty - check and remove trailing , added by autocomplete
        email = email.replace(/,$/, "");
        email = email.replace(/;$/, "");
        $("#fileto").val(email);
        email = email.split(/,|;/);

        if(email.length>maxEmailRecipients) {
            $("#maxemails_msg").show();
            isValid = false;

        }

        for (var i = 0; i < email.length; i++) {
            if (!echeck(email[i], 1, 0)) {
                $("#fileto_msg").show();
                isValid = false;
            }
        }
	} else {
        $('#fileto_msg').show();
        isValid = false;
    }

    if (isValid) {
        $('#fileto').removeClass('errorglow');
        return true;
    } else {
        $('#fileto').addClass('errorglow');
        return false;
    }
}
	
// Validate EXPIRY - upload, voucher and files
function validate_expiry()
{
	//var validformat=/^\d{2}\-\d{2}\-\d{4}$/ //Basic check for format validity
	var today = new Date();
    var maxDate = new Date(today.getFullYear(), today.getMonth(), today.getDate()+parseInt(maximumDate));
	var returnval=false
	
	//var monthfield=$("#datepicker").val().split("-")[1]
	//var dayfield=$("#datepicker").val().split("-")[0]
	//var yearfield=$("#datepicker").val().split("-")[2]
	//var dayobj = new Date(yearfield, monthfield-1, dayfield)
	var selectedDate = $("#datepicker").val();
	try {
           $.datepicker.parseDate(datepickerDateFormat,selectedDate);
        } catch (e) {
            $("#expiry_msg").show();
		return false;
        };

	//if(dayobj < today || dayobj > maxDate)
	//{
	//	$("#expiry_msg").show();
	//	return false;	
	//}
	if($("#datepicker").datepicker("getDate") == null)
	{
		$("#expiry_msg").show();
		return false;
	}
	$("#expiry_msg").hide();
	return true;
}

//  validate single email	- upload, voucher and files
function echeck(str) {

	var at="@"
	var dot="."
	var lat=str.indexOf(at)
	var lstr=str.length
	var ldot=str.indexOf(dot)
		
	if (str.indexOf(at)==-1){	return false; }
	if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){  return false;	}
	if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){ return false; }
	if (str.indexOf(at,(lat+1))!=-1){	return false;}
	if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){ return false; }
	if (str.indexOf(dot,(lat+2))==-1){ 	return false; }
	if (str.indexOf(" ")!=-1){			return false; }
	return true					
}

	// display msg - upload, voucher and files
function fileMsg(msg)
{
	$("#file_msg").html(msg);
	$("#file_msg").show();
}

// validate date format
// use: if (!isValidDate(myDateString, "DMY")) { alert("The date is not in the correct format."); }
function isValidDate(dateStr, format) {
   if (format == null) { format = "MDY"; }
   format = format.toUpperCase();
   if (format.length != 3) { format = "MDY"; }
   if ( (format.indexOf("M") == -1) || (format.indexOf("D") == -1) || (format.indexOf("Y") == -1) ) { format = "MDY"; }
   if (format.substring(0, 1) == "Y") { // If the year is first
      var reg1 = /^\d{2}(\-|\/|\.)\d{1,2}\1\d{1,2}$/
      var reg2 = /^\d{4}(\-|\/|\.)\d{1,2}\1\d{1,2}$/
   } else if (format.substring(1, 2) == "Y") { // If the year is second
      var reg1 = /^\d{1,2}(\-|\/|\.)\d{2}\1\d{1,2}$/
      var reg2 = /^\d{1,2}(\-|\/|\.)\d{4}\1\d{1,2}$/
   } else { // The year must be third
      var reg1 = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{2}$/
      var reg2 = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{4}$/
   }
   // If it doesn't conform to the right format (with either a 2 digit year or 4 digit year), fail
   if ( (reg1.test(dateStr) == false) && (reg2.test(dateStr) == false) ) { return false; }
   var parts = dateStr.split(RegExp.$1); // Split into 3 parts based on what the divider was
   // Check to see if the 3 parts end up making a valid date
   if (format.substring(0, 1) == "M") { var mm = parts[0]; } else 
      if (format.substring(1, 2) == "M") { var mm = parts[1]; } else { var mm = parts[2]; }
   if (format.substring(0, 1) == "D") { var dd = parts[0]; } else 
      if (format.substring(1, 2) == "D") { var dd = parts[1]; } else { var dd = parts[2]; }
   if (format.substring(0, 1) == "Y") { var yy = parts[0]; } else 
      if (format.substring(1, 2) == "Y") { var yy = parts[1]; } else { var yy = parts[2]; }
   if (parseFloat(yy) <= 50) { yy = (parseFloat(yy) + 2000).toString(); }
   if (parseFloat(yy) <= 99) { yy = (parseFloat(yy) + 1900).toString(); }
   var dt = new Date(parseFloat(yy), parseFloat(mm)-1, parseFloat(dd), 0, 0, 0, 0);
   if (parseFloat(dd) != dt.getDate()) { return false; }
   if (parseFloat(mm)-1 != dt.getMonth()) { return false; }
   return true;
}

// validate ajax return code to make sure it is parsable JSON
function parseJSON(json)
{
	try {
          var data = JSON.parse(json);
		  return data;
        } catch (e) {
			var msg = "Error accessing server. ";
			if(debug) { msg += "Fatal error (" + e + ")";};
			$("#scratch").html('<div id="message">'+msg+'</>');	
     	};
}

// display erro state details if debug is on
function ajaxerror(readyState,status,responseText)
{
	var msg = "Error accessing server. ";
	if(debug) { msg += " Ready State:"+readyState + ", status:" + status + ",responseText" + responseText + "";};
	$("#scratch").html('<div id="message">'+msg+'</>');						   
}

function hideMessages() {
    $("#fileto_msg").hide();
    $("#expiry_msg").hide();
    $("#maxemails_msg").hide();
    $("#file_msg").hide();
    $("#aup_msg").hide();
}

function validate_aup() {
    if ($("#aup").is(":checked")) {
        $('#aup').removeClass('errorglow');
        return true;
    } else {
        $('#aup').addClass('errorglow');
        return false;
    }
}

function statusMessage(msg, color) {
    if(msg == null) { $('#statusmessage').html(''); }
        $('#statusmessage').html(msg);
        $('#statusmessage').attr('class', color);
}


var totalBytesLoaded = 0;

function updateProgressBar(bytesloaded, totalbytes, amountUploaded) {

    var percentComplete = Math.round(bytesloaded * 100 / totalbytes);
    var bytesTransfered = '';
    var bytesRemaining;
    var uploadSpeed;
    var timeRemaining;

    if (bytesloaded > 1024 * 1024) {
        bytesTransfered = (Math.round(bytesloaded * 100 / (1024 * 1024)) / 100).toString() + 'MB';
    } else if (bytesloaded > 1024) {
        bytesTransfered = (Math.round(bytesloaded * 100 / 1024) / 100).toString() + 'kB';
    } else {
        bytesTransfered = (Math.round(bytesloaded * 100) / 100).toString() + 'Bytes';
    }

    // use time elapsed from start to calculate averages
    var now = new Date().getTime();
    var timeSinceStart = (now - startTime) / 1000;
    // Adds the amount of data uploaded this call to the total (for all files)

    if (totalFileLengths != 0) {
        var progress_bar = '#progress_bar-' + n;
        var file_box = '#file_' + n;
        var progress_completed = '#progress_completed-' + n;
        $(progress_bar).width(percentComplete / 100 * $(file_box).width());	//set width of progress bar based on the $status value (set at the top of this page)
        percentComplete = Math.round(totalBytesLoaded * 100 / totalFileLengths);
        totalBytesLoaded += amountUploaded;
        uploadSpeed = (totalBytesLoaded / timeSinceStart) / 1024 / 1024;
        bytesRemaining = totalFileLengths - totalBytesLoaded;
        timeRemaining = (uploadSpeed == 0 ? 0 : ((bytesRemaining / 1024 / 1024) / uploadSpeed));
        $('#progress_string').html(percentComplete + '%');
        $('#progress_bar').width(percentComplete / 100 * $('#progress_container').width());
        $('#totalUploaded').html('Total uploaded: ' + readablizebytes(totalBytesLoaded) + '/' + readablizebytes(totalFileLengths));
        $('#averageUploadSpeed').html('Average upload Speed:' + uploadSpeed.toFixed(2) + 'MB/s');
        $('#timeRemaining').html('Approx time remaining: ' + secondsToString(timeRemaining));
    } else {
        uploadSpeed = (bytesloaded/timeSinceStart) / 1024 / 1024;
        bytesRemaining = totalbytes - bytesloaded;
        timeRemaining = (uploadSpeed == 0 ? 0 : ((bytesRemaining / 1024 / 1024) / uploadSpeed));
        $('#progress_string').html(percentComplete + '%');
        $('#progress_bar').width(percentComplete/100 * $('#progress_container').width());	//set width of progress bar based on the $status value (set at the top of this page)
        $('#totalUploaded').html('Total uploaded: ' + readablizebytes(bytesloaded) + '/' + readablizebytes(totalbytes));
        $('#averageUploadSpeed').html('Average upload Speed: ' + uploadSpeed.toFixed(2) * 8 + 'MBit/s');
        $('#timeRemaining').html('Approx time remaining: ' + secondsToString(timeRemaining));

    }

}

// Creates a HH:MM:SS string from seconds.
function secondsToString(seconds) {
    var h = Math.floor(seconds / 3600);
    var m = Math.floor(seconds % 3600 / 60);
    var s = Math.floor(seconds % 3600 % 60);
    return ((h > 0 ? h + ":" : "") + (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + ":" : "0:") + (s < 10 ? "0" : "") + s);
}

