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

// display bytes in readable format
// Used in upload
function readablizebytes(bytes)
 {
	if (bytes > 1024*1024*1024)
		bytesdisplay = (Math.round(bytes * 100/(1024*1024*1024))/100).toString() + " GB";
	else if (bytes > 1024*1024)
		bytesdisplay = (Math.round(bytes * 100/(1024*1024))/100).toString() + " MB";
	else if (bytes > 1024)
		bytesdisplay = (Math.round(bytes * 100/1024)/100).toString() + " KB";
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
	var validformat=/^\d{2}\-\d{2}\-\d{4}$/ //Basic check for format validity
	var today = new Date();
    var maxDate = new Date(today.getFullYear(), today.getMonth(), today.getDate()+parseInt(maximumDate));
	var returnval=false
	if (!validformat.test($("#datepicker").val())) 
	{
	$("#expiry_msg").show();
	return false;
	}
	var monthfield=$("#datepicker").val().split("-")[1]
	var dayfield=$("#datepicker").val().split("-")[0]
	var yearfield=$("#datepicker").val().split("-")[2]
	var dayobj = new Date(yearfield, monthfield-1, dayfield)
	if ((dayobj.getMonth()+1!=monthfield)||(dayobj.getDate()!=dayfield)||(dayobj.getFullYear()!=yearfield))
	{
	$("#expiry_msg").show();
	return false;
	}
	if(dayobj < today || dayobj > maxDate)
	{
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