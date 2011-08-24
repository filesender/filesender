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
   
   // get initial upload uid
   $id = getGUID();
   // set id for progress bar upload
  // $id = md5(microtime() . rand());
   
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
	if (isset($_COOKIE['SimpleSAMLAuthToken'])) {
		$token = $_COOKIE['SimpleSAMLAuthToken'];
	} else {
		$token = "";
	}
	// set flash upload vairiables
	$flashVARS = "vid=".$voucherUID."&sid=".session_id()."&buttonBrowse=".lang("_BROWSE")."&buttonUpload=".lang("_UPLOAD")."&buttonCancel=".lang("_CANCEL")."&siteURL=".$config["site_url"]."&token=".$token;
 ?>
<script type="text/javascript" src="lib/js/AC_OETags.js" language="javascript"></script>
<script type="text/javascript" src="js/upload.js"></script>
<script type="text/javascript">
	
	// 
	// all default settings
	var uploadid = '<?php echo $id ?>';
	var maximumDate= '<?php echo $config['default_daysvalid']?>';
	var maxHTML5uploadsize = <?php echo $config['max_gears_upload_size'] ?>;
	var aup = '<?php echo $config['AuP'] ?>';
	var bytesUploaded = 0;
	var bytesTotal = 0;
	var banextensions = '<?php echo $config['ban_extension']?>';
	var previousBytesLoaded = 0;
	var intervalTimer = 0;
	var vid='<?php if(isset($_REQUEST["vid"])){echo $_REQUEST["vid"];}; ?>';
 	//var fileupload[uploadid].status = "draft";
 	// start document ready 
	$(document).ready(function() { 
		
		// hide all upload objects
		$("#uploadstandard").hide();
		$("#uploadhtml5").hide();
		$("#uploadstandardspinner").hide();
		$("#progress_view").hide();
		$("#fileto_msg").hide();
		$("#expiry_msg").hide();
		$("#aup_msg").hide();
		$("#file_msg").hide();
		$("#uploadbutton").hide();
		
		// hide aup if not required
		if(aup == '0') // check if AUP is required
		{
			$("#aup").hide();
		}
		
		$("#dialog-cancel").dialog({ autoOpen: false, height: 140, width: 350, modal: true,
		
		buttons: {
				<?php echo lang("_OK") ?>: function() {
				//$( this ).dialog( "close" );
				//$("#dialog-uploadprogress").dialog('close');
				window.location = window.location;
				},
				<?php echo lang("_CANCEL") ?>: function() { 
				$( this ).dialog( "close" );
				}
		}
		});
		// default error message dialogue
		$("#dialog-default").dialog({ autoOpen: false, height: 140, modal: true,title: "Error",		
		buttons: {
			<?php echo lang("_OK") ?>: function() {
				$("#dialog-default").html("");
				$( this ).dialog( "close" );
				}
			}
		})
		// default error message dialogue
		$("#dialog-uploadprogress").dialog({ 
		
		    open: function() {
          //Hide closing "X" for this dialog only.
          $(this).parent().children().children("a.ui-dialog-titlebar-close").remove();
    	},
		autoOpen: false, height: 180,width: 400, modal: true,title: "<?php echo lang("_UPLOAD_PROGRESS") ?>:",		
		buttons: {
			<?php echo lang("_CANCEL") ?>: function() {
				// are you sure?
				$("#dialog-cancel").dialog('open');
				}	
			}
		})
		// set date picker
		$(function() {
			$("#datepicker" ).datepicker({ minDate: 1, maxDate: "+"+maximumDate+"D",altField: "#fileexpirydate", altFormat: "d-m-yy" });
			$("#datepicker" ).datepicker( "option", "dateFormat", "dd-mm-yy" );
			$("#datepicker").datepicker("setDate", new Date()+maximumDate);
		});	

		//Check if HTML5 is enable and use HTML uploader
		if(window.File && window.FileReader && window.FileList && window.Blob && window.FormData){
			// use HTML5 upload functions
			$("#uploadhtml5").show();
			} else {
			// use standard upload functions
			$("#uploadstandard").show();
		}
	// end document ready
	});

	// toggle AUP display
	function toggleTOG()
	{
	if( $("#tog").is(":visible") ) {
    	$("#tog").hide();
	} else {
	    $("#tog").show();
	}
	}
	
	// --------------------------
	// Common upload functions
	// --------------------------
	
	// update the progress bar
	function updatepb(bytesloaded,totalbytes)
	{
		$("#progress_bar").show();
		
		var percentComplete = Math.round(bytesloaded * 100 / totalbytes);
		var bytesTransfered = '';
		if (bytesloaded > 1024*1024)
			bytesTransfered = (Math.round(bytesloaded * 100/(1024*1024))/100).toString() + 'MB';
		else if (bytesloaded > 1024)
			bytesTransfered = (Math.round(bytesloaded * 100/1024)/100).toString() + 'KB';
		else
			bytesTransfered = (Math.round(bytesloaded * 100)/100).toString() + 'Bytes';

			$("#progress_view").fadeIn(100);	//fade in progress bar	
			$("#progress_bar").width(percentComplete/100 *$('#progress_container').width());	//set width of progress bar based on the $status value (set at the top of this page)
			$("#progress_bar").html(percentComplete +"% ");
			$("#progress_completed").html(parseInt(percentComplete) + "%(" + bytesTransfered + ")" );	//display the % completed within the progress bar
		  
	}

	// get a dom element (just to reduce code)
	//function obj(id) {
	//	return document.getElementById(id);
	//}
	
	function validateforflash(fname,fsize)
	{
	if(validateFormFlash())
	{
	// hide upload button
	$("#dialog-uploadprogress").dialog("option", "title", "<?php echo lang("_UPLOAD_PROGRESS") ?>: " +  fname + " (" +readablizebytes(fsize) + ")");
	$("#dialog-uploadprogress").dialog("open");	
	//lockformfields();
	getFlexApp("filesenderup").returnMsg("true")
	} else {
	getFlexApp("filesenderup").returnMsg("false")
	}
	}
	// --------------------------
	// Validation functions
	// --------------------------
	// HTML5 form Validation
	function validateForm()
	{
	// remove previouse vaildation messages
	$("#fileto_msg").hide();
	$("#expiry_msg").hide();
	$("#aup_msg").hide();
	$("#file_msg").hide();
	
	var validate = true;
	
	if(!validate_fileto() ){validate = false;};		// validate emails
	if(!validate_file() ){validate = false;};		// check if file selected
	if(aup == '1') // check if AUP is required
	{
	if(!validate_aup() ){validate = false;};		// check AUP is selected
	}
	if(!validate_expiry() ){validate = false;};		// check date
	
	return validate;
	}
	// FLASH form Validation
	function validateFormFlash()
	{
	// remove previouse vaildation messages
	$("#fileto_msg").hide();
	$("#expiry_msg").hide();
	$("#aup_msg").hide();
	$("#file_msg").hide();
	
	var validate = true;
	
	if(!validate_fileto() ){validate = false;};		// validate emails
	if(aup == '1') // check if AUP is required
	{
		if(!validate_aup() ){validate = false;};		// check AUP is selected
	}
	if(!validate_expiry() ){validate = false;};		// check date
	
	return validate;
	}

// Validate FILETO
function validate_fileto()
{
	$("#fileto_msg").hide();
	// remove white spaces 
	var email = $("#fileto").val();
	email = email.split(" ").join("");
	$("#fileto").val(email);
	email = email.split(/,|;/);
	for (var i = 0; i < email.length; i++) {
		if (!echeck(email[i], 1, 0)) {
		$("#fileto_msg").show();
		return false;
		}
		}
	return true;	
}

// Validate EXPIRY
function validate_expiry()
{
	var validformat=/^\d{2}\-\d{2}\-\d{4}$/ //Basic check for format validity
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
	if($("#datepicker").datepicker("getDate") == null)
	{
		$("#expiry_msg").show();
		return false;
	}
	$("#expiry_msg").hide();
	return true;
}

//Validate AUP
function validate_aup()
{
	if(	$("#aup").is(":checked"))
	{
		$("#aup_msg").hide();
		return true;
	} else {
		$("#aup_msg").show();
		return false;
	}
}

// validate extension
function validateextension(filename)
{
	if(filename.split('.').pop().search(banextensions) == -1)
	{
	return true;
	} else {
	return false;
	}
}

// Validate FILE (HTML5 only)
function validate_file()
{
	fileMsg("");
	if(!document.getElementById("fileToUpload").files[0])
	{
		fileMsg("Please select a file");
		return false;
	} else 
	{
		var file = document.getElementById("fileToUpload").files[0];
		// validate fiename 
		if (!validatefilename(file.name)){
		return false;
		}
		//validate file size
		if(file.size < 1)
		{
		fileMsg("<?php echo lang("_INVALID_FILESIZE_ZERO") ?>");	
		return false;
		}
		if(file.size > maxHTML5uploadsize)
		{
		fileMsg("<?php echo lang("_INVALID_TOO_LARGE_1") ?> " + readablizebytes(maxHTML5uploadsize) + ". <?php echo lang("_SELECT_ANOTHER_FILE") ?> ");	
		return false;
		}
		var tmpExtension = file.name.split('.').pop();
		if(banextensions.search(tmpExtension) != -1)
		{
		fileMsg("<?php echo lang("_INVALID_FILE_EXT") ?>");	
		return false;
		}
		$("#dialog-uploadprogress").dialog("option", "title", "<?php echo lang("_UPLOAD_PROGRESS") ?>:  " +  file.name + " (" +readablizebytes(file.size) + ")");
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
// flex file information check
function fileInfo(name,size)
{
fileMsg("");
if(size > 2147483648)
{
fileMsg("<?php echo lang("_INVALID_2GB_USEHTML5") ?>");
// remove displayed file data
$("#fileInfoView").hide();
} else if (validatefilename(name)) 
{
	$("#fileInfoView").show();
	$("#n").val(name);
	$("#total").val(size);
	$("#fileName").val(name);
	$("#fileName").html("Name: " + name);
	$("#fileSize").html("Size: " + readablizebytes(size));
	getFlexApp("filesenderup").returnMsg("upload")
} else {
	$("#fileInfoView").hide();
}
}


function uploadcomplete(name,size)
{
//unlockformfields();
$("#form1").submit();
}

function uploaderror(name,size)
{
errorDialog("<?php echo lang("_ERROR_UPLOADING_FILE") ?> "+name+":"+size);
}

// check browser type
function getFlexApp(appName)
{
  if (navigator.appName.indexOf ("Microsoft") !=-1)
  {
	  if(window[appName] == undefined)
	  {
    	return document[appName];
	  } else {
		return window[appName];  
	  }
  }
  else
  {
    return document[appName];
  }
}

function validatefilename(name)
{
	var tmpExtension = name.split('.').pop();
		if(banextensions.search(tmpExtension) != -1)
		{
		fileMsg("<?php echo lang("_INVALID_FILE_EXT")." ".lang("_SELECT_ANOTHER_FILE") ?>");	
		return false;
		}
   if (/^[^\\\/\:\*\?\"\<\>\|\.]+(\.[^\\\/\:\*\?\"\<\>\|\.]+)+$/.test(name)) 
   {
		return true; 
	} else {
		fileMsg("<?php echo lang("_INVALID_FILE_NAME") ?>");	
		return false;
	}
}

function validate() 
{
	// upload if validated
	if(validateForm())
	{
	startupload();
	}
}

function errorDialog(msg)
{
$("#dialog-default").html(msg);
$("#dialog-default").dialog("open")
}

function fileMsg(msg)
{
	$("#file_msg").html(msg);
	$("#file_msg").show();
}
function keepMeAlive()
{
	$.ajax({
  		url: "keepalive.php" + '?x=' + escape(new Date()),
  		success: function(data) {
		}
		});	
}

    </script>

<div id="box"> <?php echo '<div id="pageheading">'.lang("_UPLOAD").'</div>'; ?>
  <form id="form1" enctype="multipart/form-data" method="POST" action="fs_uploadit5.php">
    <table width="100%" border="0">
      <tr>
        <td width="50" class="formfieldheading mandatory"><?php echo lang("_TO") ; ?>:</td>
        <td width="600" colspan="2" valign="middle"><input name="fileto" title="<?php echo lang("_EMAIL_SEPARATOR_MSG") ; ?>" type="text" id="fileto" size="60" onchange="validate_fileto()" />
        <div id="fileto_msg" style="display: none" class="validation_msg"><?php echo lang("_INVALID_MISSING_EMAIL"); ?></div>
        </td>
        <td valign="top"><img src="../www/images/num_1.png" alt="" width="25" height="25" hspace="6" border="0" align="left" /></td>
        <td width="250" valign="top"><span class="forminstructions"><?php echo lang("_STEP1"); ?></span></td>
       </tr>
      <tr>
        <td class="formfieldheading mandatory"><?php echo lang("_FROM"); ?>:</td>
        <td colspan="2"><?php echo $senderemail ?>
          <input name="filefrom" type="hidden" id="filefrom" value="<?php echo $senderemail ?>" size="40" />
          </td>
        <td valign="top"><img src="../www/images/num_2.png" width="25" height="25" hspace="6" border="0" align="left" /></td>
        <td valign="top"><span class="forminstructions"><?php echo lang("_STEP2"); ?></span></td>
        </tr>
      <tr>
        <td class="formfieldheading"><?php echo lang("_SUBJECT"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
        <td colspan="2"><input name="filesubject" type="text" id="filesubject" size="60" />
        </td>
        <td valign="top"><img src="../www/images/num_3.png" width="25" height="25" hspace="6" border="0" align="left" /></td>
        <td valign="top"><span class="forminstructions"><?php echo lang("_STEP3"); ?></span></td>
        </tr>
      <tr>
        <td class="formfieldheading"><?php echo lang("_MESSAGE"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
        <td colspan="2"><textarea name="filemessage" cols="45" rows="4" id="filemessage"></textarea></td>
        <td valign="top"><img src="../www/images/num_4.png" width="25" height="25" hspace="6" border="0" align="left" /></td>
        <td valign="top"><span class="forminstructions"><?php echo lang("_STEP4"); ?></span></td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"><?php echo lang("_EXPIRY_DATE"); ?>:
          <input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date($config['datedisplayformat'],strtotime("+".$config['default_daysvalid']." day"));?>"/></td>
        <td colspan="2"><input id="datepicker" name="datepicker" onchange="validate_expiry()">
          </input>
          <div id="expiry_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_EXPIRY_DATE"); ?></div>
          <div class="">(dd-mm-yyyy)</div></td>
        <td width="300" colspan="2" valign="top">&nbsp;</td>
      </tr>
      <?php if ($config["AuP"]) {?>
      <tr>
        <td class="formfieldheading"></td>
        <td><input name="aup" type="checkbox" id="aup" onchange="validate_aup()" value="true"/>
         </td>
        <td>
          <div id="aup_label" name="aup_label" onclick="toggleTOG()" style="cursor:pointer;"><?php echo lang("_ACCEPTTOC"); ?> [<font color="#666666"><?php echo lang("_SHOWHIDE"); ?></font>]</div>
          <div id="aup_msg" class="validation_msg" style="display: none"><?php echo lang("_AGREETOC"); ?></div>
          <div id="tog" name="tog" style="display:none"> <?php echo $config["AuP_terms"]; ?> </div>
        </td>
        <td width="300" colspan="2" valign="top">&nbsp;</td>
      </tr>
      <?php } ?>
      <tr>
        <td class="formfieldheading mandatory"><div id="selectfile" name="selectfile"><?php echo lang("_SELECT_FILE"); ?>:</div></td>
        <td colspan="2"><div id="uploadstandard"> 
            <script language="JavaScript" type="text/javascript">
<!--
// Version check for the Flash Player that has the ability to start Player Product Install (6.0r65)
var hasProductInstall = DetectFlashVer(6, 0, 65);

// Version check based upon the values defined in globals
var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);

if ( hasProductInstall && !hasRequestedVersion ) {
	// DO NOT MODIFY THE FOLLOWING FOUR LINES
	// Location visited after installation is complete if installation is required
	var MMPlayerType = (isIE == true) ? "ActiveX" : "PlugIn";
	var MMredirectURL = window.location;
    document.title = document.title.slice(0, 47) + " - Flash Player Installation";
    var MMdoctitle = document.title;

	AC_FL_RunContent(
		"src", "lib/swf/playerProductInstall",
		"FlashVars", "<?php echo $flashVARS ?>",
		"width", "300",
		"height", "30",
		"align", "middle",
		"id", "filesenderup",
		"quality", "high",
		"bgcolor", "#ffffff",
		"name", "filesenderup",
		"allowScriptAccess","sameDomain",
		"type", "application/x-shockwave-flash",
		"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
} else if (hasRequestedVersion) {
	// if we've detected an acceptable version
	// embed the Flash Content SWF when all tests are passed
	AC_FL_RunContent(
			"src", "swf/filesenderup",
			"FlashVars", "<?php echo $flashVARS ?>",
			"width", "300",
			"height", "30",
			"align", "middle",
			"id", "filesenderup",
			"quality", "high",
			"bgcolor", "#ffffff",
			"name", "filesenderup",
			"allowScriptAccess","sameDomain",
			"type", "application/x-shockwave-flash",
			"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
  } else {  // flash is too old or we can't detect the plugin
    var alternateContent = '<div id="header"><h1>Install Flash Player<h1></div><BR><div align="center">This application requires Flash Player.<BR><BR>'
  	+ 'To install Flash Player go to Adobe.com.<br> '
   	+ '<a href=http://www.adobe.com/go/getflash/>Get Flash</a></div>';
    document.write(alternateContent);  // insert non-flash content
  }
// -->
</script>
            <noscript>
            <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="500" height="50"
			codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
              <param name="movie" value="swf/filesenderup.swf" />
              <param name="quality" value="high" />
              <param name="bgcolor" value="#ffffff" />
              <param name="allowScriptAccess" value="sameDomain" />
              <embed src="swf/filesenderup.swf" quality="high" bgcolor="#869ca7"
				width="300" height="30" name="filesenderup" align="middle"
				play="true"
				loop="false"
				quality="high"
				allowScriptAccess="sameDomain"
				type="application/x-shockwave-flash"
				pluginspage="http://www.adobe.com/go/getflashplayer">
              </embed>
            </object>
            </noscript>
            <div id="uploadstandardspinner" style="padding-top:10px"><img src="images/ajax-loader-sm.gif" border=0 align="left" style="padding-right:6px"/><?php echo lang("_UPLOADING_WAIT"); ?></div>
            <BR />
          </div>
          <div id="uploadhtml5">
            <input type="file" name="fileToUpload" id="fileToUpload" onChange="fileSelected();"/>
            <input type="button" onClick="validate()" value="Upload" id="uploadbutton" name="uploadbutton"> 
          </div>
          <div id="file_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_FILE"); ?></div>
          </td>
        <td width="300" colspan="2" valign="top">&nbsp;</td>
      </tr>
      <tr>
        <td></td>
        <td colspan="2">
          <div id="fileInfoView">
            <div id="fileName" name="fileName"></div>
            <div id="fileSize" name="fileSize"></div>
          </div>
        </td>
        <td width="300" colspan="2" valign="top">&nbsp;</td>
      </tr>
         </table>
<input type="hidden" id="filevoucheruid" name="filevoucheruid" value="<?php echo $voucherUID; ?>"/>
		<input type="hidden" name="vid" id="vid" value="<?php echo $voucherUID; ?>"/>
		<input type="hidden" name="total" id="total" value=""/>
		<input type="hidden" name="n" id="n" value=""/>
		<input type="hidden" id="filestatus" name="filestatus" value="<?php echo $filestatus; ?>"/>
		<input type="hidden" name="loadtype" id="loadtype" value="standard"/>
  </form>
</div>
<div id="dialog-default" title=""> </div>
<div id="dialog-cancel" title="<?php echo lang("_CANCEL_UPLOAD"); ?>"><?php echo lang("_ARE_YOU_SURE"); ?></div>
<div id="dialog-uploadprogress" title="">
<img id="progress_image" name="progress_image" src="images/ajax-loader-sm.gif" width="16" height="16" alt="Uploading" align="left"/> 
	<div id="progress_container">
   		<div id="progress_bar">
		<div id="progress_completed"></div>
	</div>
	</div>
</div>
<img id="keepAliveIMG" name="keepAliveIMG" width="1" height="1" src="images/ka.png" />