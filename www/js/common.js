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
    var bytesDisplay;

	if (bytes > 1024*1024*1024) {
		bytesDisplay = (Math.round(bytes * 100/(1024*1024*1024))/100).toString() + ' GB';
    } else if (bytes > 1024*1024) {
		bytesDisplay = (Math.round(bytes * 100/(1024*1024))/100).toString() + ' MB';
    } else if (bytes > 1024) {
		bytesDisplay = (Math.round(bytes * 100/1024)/100).toString() + ' kB';
    } else {
		bytesDisplay = (Math.round(bytes * 100)/100).toString() + ' Bytes';
    }

	return bytesDisplay;
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
            if (!echeck(email[i])) {
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
	var selectedDate = $("#datepicker").val();
	try {
        $.datepicker.parseDate(datepickerDateFormat,selectedDate);
    } catch (e) {
        $("#expiry_msg").show();
		return false;
    }

	if($("#datepicker").datepicker("getDate") == null)
	{
        $("#expiry_msg").show();
		return false;
	}
	$("#expiry_msg").hide();
	return true;
}

//  validate single email	- upload, voucher and files
function echeck(str)
{
	var at='@';
	var dot='.';
	var lat=str.indexOf(at);
	var lstr=str.length;
	var ldot=str.indexOf(dot);
		
	if (str.indexOf(at) == -1 || str.indexOf(at) == 0 || str.indexOf(at) == lstr){
        return false;
    } else if (str.indexOf(dot) == -1 || str.indexOf(dot) == 0 || str.indexOf(dot) == lstr) {
        return false;
    } else if (str.indexOf(at,(lat+1))!=-1){
        return false;
    } else if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
        return false;
    } else if (str.indexOf(dot,(lat+2))==-1){
        return false;
    } else if (str.indexOf(" ") != -1){
        return false;
    } else return true;
}

	// display msg - upload, voucher and files
function fileMsg(msg)
{
    $("#file_msg").html(msg);
    $("#file_msg").show();
}

// validate ajax return code to make sure it is parsable JSON
function parseJSON(json)
{
    try {
        var data = JSON.parse(json);
        return data;
    } catch (e) {
        var msg = "Error accessing server. ";

        if(debug) {
            msg += "Fatal error (" + e + ")";
        }
        statusMessage(msg, 'red');
    }
}

// display erro state details if debug is on
function ajaxerror(readyState,status,responseText)
{
	var msg = "Error accessing server. ";

	if(debug) {
        msg += " Ready State:"+readyState + ", status:" + status + ",responseText" + responseText + "";
    }
    statusMessage(msg, 'red');
}

function hideMessages()
{
    $("#fileto_msg").hide();
    $("#expiry_msg").hide();
    $("#maxemails_msg").hide();
    $("#file_msg").hide();
    $("#aup_msg").hide();
}

function validate_aup()
{
    if ($("#aup").is(":checked")) {
        $('#aup').removeClass('errorglow');
        return true;
    } else {
        $('#aup').addClass('errorglow');
        return false;
    }
}

function statusMessage(msg, color)
{
    var statusMsg = $('#statusmessage');
    if(msg == null) {
        statusMsg.html('');
    }

    statusMsg.html(msg);
    statusMsg.attr('class', color);
}


var totalBytesLoaded = 0;

function updateProgressBar(bytesloaded, totalbytes, amountUploaded)
{
    var percentComplete = Math.round(bytesloaded * 100 / totalbytes);
    var bytesTransfered = '';
    var bytesRemaining;
    var uploadSpeed;
    var timeRemaining;

    // use time elapsed from start to calculate averages
    var now = new Date().getTime();
    var timeSinceStart = (now - startTime) / 1000;
    // Adds the amount of data uploaded this call to the total (for all files)

    if (amountUploaded != 0) {
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

