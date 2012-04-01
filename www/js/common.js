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
	$("#fileto_msg").hide();
	$("#maxemails_msg").hide();
	// remove white spaces 
	var email = $("#fileto").val();
	email = email.split(" ").join("");
	$("#fileto").val(email);
	email = email.split(/,|;/);
	if(email.length>maxEmailRecipients)
	{
		$("#maxemails_msg").show();
		return false;
	}
	for (var i = 0; i < email.length; i++) {
		if (!echeck(email[i], 1, 0)) {
		$("#fileto_msg").show();
		return false;
	}
	}
	return true;		
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