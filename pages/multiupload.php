<?php

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
	 // clear aup session
	//unset ($_SESSION['aup'], $var);

	// get voucher information 
	$voucherData =  $authvoucher->getVoucher();
	$voucherUID = $voucherData[0]["filevoucheruid"];
	$senderemail = $voucherData[0]["fileto"];
	// check if voucher is invalid (this should be an external function
	if($voucherData[0]["filestatus"] == "Voucher") {
	$filestatus = "Voucher";
	} else if($voucherData[0]["filestatus"] == "Voucher Cancelled" || $voucherData[0]["filestatus"] == "Closed")
	{
	?>

<p><?php echo lang("_VOUCHER_CANCELLED"); ?></p>
<?php
	return;
	}
}
	if (isset($_COOKIE['SimpleSAMLAuthToken'])) {
		$token = $_COOKIE['SimpleSAMLAuthToken'];
	} else {
		$token = "";
	}
	// set flash upload vairiables
	$flashVARS = "vid=".$voucherUID."&sid=".session_id()."&buttonBrowse=".lang("_BROWSE")."&buttonUpload=".lang("_SEND")."&buttonCancel=".lang("_CANCEL")."&siteURL=".$config["site_url"]."&token=".$token;
 ?>
<script type="text/javascript" src="lib/js/AC_OETags.js" language="javascript"></script> 
<script type="text/javascript" src="js/multiupload.js"></script> 
<script type="text/javascript">
//<![CDATA[
	// all default settings
	var uploadid = '<?php echo $id ?>';
	var maximumDate = <?php echo (time()+($config['default_daysvalid']*86400))*1000 ?>;
	var minimumDate = <?php echo (time()+86400)*1000 ?>;
	var maxHTML5uploadsize = <?php echo $config['max_html5_upload_size']; ?>;
	var maxFLASHuploadsize = <?php echo $config['max_flash_upload_size']; ?>;
	var maxEmailRecipients = <?php echo $config['max_email_recipients']; ?>;
	var datepickerDateFormat = '<?php echo lang('_DP_dateFormat'); ?>';
	var chunksize =  <?php echo $config['upload_chunk_size']; ?>;
	var aup = '<?php echo $config['AuP'] ?>';
	var bytesUploaded = 0;
	var bytesTotal = 0;
	var banextensions = '<?php echo $config['ban_extension']?>';
	var uploadprogress = '<?php echo lang($lang["_UPLOAD_PROGRESS"]); ?>';
	var previousBytesLoaded = 0;
	var intervalTimer = 0;
	var html5 = false;
	var errmsg_disk_space = "<?php echo lang($lang["_DISK_SPACE_ERROR"]); ?>";
	var filedata=new Array(); 
	
	var vid='<?php if(isset($_REQUEST["vid"])){echo htmlspecialchars($_REQUEST["vid"]);}; ?>';
	// check if html5 functions are available
	html5 = (window.File && window.FileReader && window.FileList && window.Blob && window.FormData) ? true : false;
 	// start document ready 
	$(function() { 

		// set date picker
		$("#datepicker" ).datepicker({ minDate: new Date(minimumDate), maxDate: new Date(maximumDate),altField: "#fileexpirydate", altFormat: "d-m-yy" });
		$("#datepicker" ).datepicker( "option", "dateFormat", "<?php echo lang("_DP_dateFormat"); ?>" );
		$("#datepicker").datepicker("setDate", new Date(maximumDate));
			
		// set datepicker language
		$.datepicker.setDefaults({
		closeText: '<?php echo lang("_DP_closeText"); ?>',
		prevText: '<?php echo lang("_DP_prevText"); ?>',
		nextText: '<?php echo lang("_DP_nextText"); ?>',
		currentText: "<?php echo lang("_DP_currentText"); ?>",
		monthNames: <?php echo lang("_DP_monthNames"); ?>,
		monthNamesShort: <?php echo lang("_DP_monthNamesShort"); ?>,
		dayNames: <?php echo lang("_DP_dayNames"); ?>,
		dayNamesShort: <?php echo lang("_DP_dayNamesShort"); ?>,
		dayNamesMin: <?php echo lang("_DP_dayNamesMin"); ?>,
		weekHeader: '<?php echo lang("_DP_weekHeader"); ?>',
		dateFormat: '<?php echo lang("_DP_dateFormat"); ?>',
		firstDay: <?php echo lang("_DP_firstDay"); ?>,
		isRTL: <?php echo lang("_DP_isRTL"); ?>,
		showMonthAfterYear: <?php echo lang("_DP_showMonthAfterYear"); ?>,
		yearSuffix: '<?php echo lang("_DP_yearSuffix"); ?>'});
		
		
		// upload area filestoupload
	$('body').on(
    'dragover',
    function(e) {
        e.preventDefault();
        e.stopPropagation();
    }
)
$('body').on(
    'dragenter',
    function(e) {
        e.preventDefault();
        e.stopPropagation();
    }
)
	$('body').on(
    'drop',
    function(e){
        if(e.originalEvent.dataTransfer){
            if(e.originalEvent.dataTransfer.files.length) {
                e.preventDefault();
                e.stopPropagation();
                /*UPLOAD FILES HERE*/
               var files = e.originalEvent.dataTransfer.files;
  				for (var i = 0, f; f = files[i]; i++) 
				{
					n = n + 1;
  				 	// --------------
					fdata[n] = Array(n);
        fdata[n].file = files[i];
        fdata[n].fileSize = fdata[n].file.size;
		fdata[n].bytesTotal = fdata[n].file.size;
		fdata[n].bytesUploaded = 0;
	    fdata[n].previousBytesLoaded = 0;
	    fdata[n].intervalTimer = 0;
		fdata[n].currentlocation = 0;
		fdata[n].filename = fdata[n].file.name;
		fdata[n].name = fdata[n].file.name;
		fdata[n].filetype = fdata[n].file.type;
		fdata[n].valid = false; // assume invalid until checked
		fdata[n].status = true; // to allow removal of file from upload list
		// validate file for upload
		// Show in list 'invalid' with reason 
		//fdata[n].filesize = 0;
		var validfile = "";
	  	if(validate_file(n)) { 
			fdata[n].valid = true;
		} else {
			validfile = '<img style="float:left;padding-right:6px;" src="images/information.png" border=0 title="This file is invalid and will not be uploaded"/>';
		}
			$("#uploadbutton").show(); 
			$("#fileInfoView").show();
			var progressString = '<div id="file_'+n+'" class="fileBox valid'+ fdata[n].valid+ '">' + validfile + ' ' + fdata[n].filename + ' : ' + readablizebytes(fdata[n].fileSize)+'<div class="delbtn" id="file_del_'+n+'" onclick="removeItem('+n+');"><img src="images/delete.png" width="16" height="16" border="0" align="absmiddle" style="cursor:pointer"/></div><div style="display:none" class="progress_container" id="progress_container-'+n+'"><div class="progress_bar"  id="progress_bar-'+n+'"></div></div>';
	
			$("#filestoupload").append(progressString);
			//$("#fileName").html('Name: ' + fdata[n].filename);
			//$("#fileSize").html('Size: ' + readablizebytes(fdata[n].fileSize));
		//} else { 
		 // display invalid file
			//$("#uploadbutton").hide();
			//$("#fileInfoView").hide();
			//$("#fileName").html("");
			//$("#fileSize").html("");
		//};
	
		//--------------
 				}
            }   
        }
    }
);
		// set dialog cancel upload
		$("#dialog-cancel").dialog({ autoOpen: false, height: 140, width: 350, modal: true,
		buttons: {
				'uploadconfirmyesBTN': function() {
				location.reload(true);

				},
				'uploadconfirmnoBTN': function() { 
				$( this ).dialog( "close" );
				}
		}
		});
		
		$('.ui-dialog-buttonpane button:contains(uploadconfirmnoBTN)').attr("id","btn_uploadconfirmno");            
		$('#btn_uploadconfirmno').html('<?php echo lang("_NO") ?>') 
		$('.ui-dialog-buttonpane button:contains(uploadconfirmyesBTN)').attr("id","btn_uploadconfirmyes");            
		$('#btn_uploadconfirmyes').html('<?php echo lang("_YES") ?>') 
		
			// default error message dialogue
		$("#dialog-support").dialog({ autoOpen: false, height: 400,width: 550, modal: true,title: "",		
		buttons: {
			'supportBTN': function() {
				$( this ).dialog( "close" );
				}
			}
		})
		
		$('.ui-dialog-buttonpane button:contains(supportBTN)').attr("id","btn_support");            
		$('#btn_support').html('<?php echo lang("_OK") ?>') 
		
		// default auth error dialogue
		$("#dialog-autherror").dialog({ autoOpen: false, height: 240,width: 350, modal: true,title: "",		
		buttons: {
			'<?php echo lang("_OK") ?>': function() {
				location.reload(true);
				}
			}
		})
		
		// default error message dialogue
		$("#dialog-default").dialog({ autoOpen: false, height: 140, height: 200, modal: true,title: "Error",		
		buttons: {
			'<?php echo lang("_OK") ?>': function() {
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
			'uploadcancelBTN': function() {
				// are you sure?
				$("#dialog-cancel").dialog('open');
				$('.ui-dialog-buttonpane > button:last').focus();
				}	
			}
		})
		
		
		$('.ui-dialog-buttonpane button:contains(uploadcancelBTN)').attr("id","btn_uploadcancel");            
		$('#btn_uploadcancel').html('<?php echo lang("_CANCEL") ?>') 
		
		function displayhtml5support()
		{
		$("#dialog-support").dialog("open");		
		}
		//Check if HTML5 is enable and use HTML uploader
		if(html5){
			// use HTML5 upload functions
			$("#html5image").attr("src","images/html5_installed.png");
			$("#html5image").attr("title","<?php echo lang("_HTML5Supported"); ?>");
			$("#html5text").html('<?php echo lang("_HTML5Supported"); ?>');
			$("#uploadhtml5").show();
			} else {
			$("#html5image").attr("src","images/html5_none.png");
			$("#html5image").attr("title","<?php echo lang("_HTML5NotSupported"); ?>");
			$("#html5text").html('<?php echo lang("_HTML5NotSupported"); ?>');
			$('#html5image').click(function() { displayhtml5support(); });
			$("#html5link").removeAttr("href");
			
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
	
	function hidemessages()
{
		$("#fileto_msg").hide();
		$("#expiry_msg").hide();
		$("#maxemails_msg").hide();	
		$("#file_msg").hide();
		$("#aup_msg").hide();
}
	// --------------------------
	// Validation functions
	// --------------------------
	function validateforflash(fname,fsize)
	{
	// remove previouse vaildation messages
	hidemessages();
	
	var validate = true;
	
	if(!validate_fileto() ){validate = false;};		// validate emails
	if(aup == '1') // check if AUP is required
	{
		if(!validate_aup() ){validate = false;};		// check AUP is selected
	}
	if(!validate_expiry() ){validate = false;};		// check date
	// vaildate with server
	if(validate) {	
		var query = $("#form1").serializeArray(), json = {};
		for (i in query) { json[query[i].name] = query[i].value; } 
		// add file information fields
		json["fileoriginalname"] = fname;
		json["filesize"] = parseInt(fsize);
		json["vid"] = vid;

		$.ajax({
  		type: "POST",
  		url: "fs_upload.php?type=validateupload&vid="+vid,
  		data: {myJson:  JSON.stringify(json)}
		}).success(function( data ) {
		if(data == "") {
		alert("No response from server");
		return;	
		}
		if(data == "ErrorAuth")
		{
			$("#dialog-autherror").dialog("open");
			return;			
		}
		var data =  JSON.parse(data);
		if(data.errors)
		{
		$.each(data.errors, function(i,result){
		if(result == "err_tomissing") { $("#fileto_msg").show();} // missing email data
		if(result == "err_expmissing") { $("#expiry_msg").show();} // missing expiry date
		if(result == "err_exoutofrange") { $("#expiry_msg").show();} // expiry date out of range
		if(result == "err_invalidemail") { $("#fileto_msg").show();} // 1 or more emails invalid
		if(result == "err_invalidfilename") { $("#file_msg").show();} //  invalid filename
		if(result == "err_nodiskspace") { errorDialog(errmsg_disk_space);}
		})
		}
		if(data.status && data.status == "complete")
		{
		$("#fileToUpload").hide();// hide Browse
		$("#selectfile").hide();// hide Browse message
		//$("#uploadbutton").hide(); // hide upload
		$("#cancelbutton").show(); // show cancel
		// show upload progress dialog
		$("#dialog-uploadprogress").dialog("open");
		// no error so use reuslt as current bytes uploaded for file resume 
		vid = data.vid;
		// hide upload button
		$("#dialog-uploadprogress").dialog("option", "title", "<?php echo lang("_UPLOAD_PROGRESS") ?>: " +  fname + " (" +readablizebytes(fsize) + ")");
		$("#dialog-uploadprogress").dialog("open");	
		getFlexApp("filesenderup").returnVoucher(vid)
		} else {
		getFlexApp("filesenderup").returnMsg(false)
		}
	
	})
	}
	}
	
	// Validate FILE (HTML5 only)
function validate_file(id)
{
	//alert(id);
	fileMsg("");
	//if(!document.getElementById("fileToUpload").files[0])
	//{
		// display message if a user enters all form details and selects upload without selecting a file
		// in theory this error should not appear as a browse button should not be visible without a file first being selected
	//	fileMsg("<?php echo lang("_SELECT_FILE") ?>");
	//	return false;
	//} else 
	//{
		var file = fdata[n];
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
	//}	
}

	// HTML5 form Validation
	function validateForm()
	{
	// remove previouse vaildation messages
	hidemessages();
	
	var validate = true;
	
	if(!validate_fileto() ){validate = false;};		// validate emails
	//if(!validate_file() ){validate = false;};		// check if file selected
	//if(aup == '1') // check if AUP is required
	//{
	if(aup == '1' && !validate_aup() ){validate = false;};		// check AUP is selected
	//}
	if(!validate_expiry() ){validate = false;};		// check date
	
	return validate;
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

// flex file information check
function fileInfo(name,size)
{
	//$("#uploadbutton").hide(); 
fileMsg("");
if(size < 1)
{
	getFlexApp("filesenderup").returnMsg("hideupload");
	$("#fileInfoView").hide();
	fileMsg("<?php echo lang("_INVALID_FILESIZE_ZERO") ?>");	
	return false;
}
if(size > maxFLASHuploadsize)
{
	fileMsg("<?php echo lang("_INVALID_TOO_LARGE_1") ?> " + readablizebytes(maxFLASHuploadsize) + ". <?php echo lang("_INVALID_SIZE_USEHTML5") ?> ");	
	$("#fileInfoView").hide();
	return false;
} 
			if (validatefilename(name)) 
		{
			$("#fileInfoView").show();
			$("#n").val(name);
			$("#total").val(size);
			$("#fileName").val(name);
			$("#fileName").html("Name: " + name);
			$("#fileSize").html("Size: " + readablizebytes(size));
			$("#uploadbutton").show(); 
		} else {
			$("#fileInfoView").hide();
			//$("#uploadbutton").hide(); 
		}
}


function uploadcomplete(name,size)
{
	$("#fileName").val(name);
	// ajax form data to fs_upload.php
	$.ajax({
	  type: "POST",
	  url: "fs_upload.php?type=uploadcomplete&vid="+vid//,
	  //data: {myJson:  JSON.stringify(json)}
	}).success(function( data ) {

	if(data == "err_cannotrenamefile")
		{
		window.location.href="index.php?s=uploaderror";
		} else if(data == "complete"){		
		window.location.href="index.php?s=complete";
		} else {
		window.location.href="index.php?s=completev";
		}
});
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
	if(html5) {		
	if(validateForm()) 
	{
		// validate client side
		// validate server side as well (check for drive space
		//Use this to allow uplods with faulty parameters (and comment out the previous one) if(true)
	
	n=0;
	startupload();
	}
	} else {
	getFlexApp("filesenderup").returnMsg("validatebeforeupload");
	}
	
}

function errorDialog(msg)
{
$("#dialog-default").html(msg);
$("#dialog-default").dialog("open")
}


function keepMeAlive()
{
	$.ajax({
  		url: "keepalive.php" + '?x=' + escape(new Date()),
  		success: function(data) {
		}
		});	
}
//]]>
    </script>
<div style="width:100%;height:20px;display:none"><a href="<?php echo $config['HTML5URL'] ?>"  target="_newtab" id="html5link" name="html5link"><img style="float:right;padding-left:6px;" src="images/html5_installed.png" alt="" name="html5image" width="75" height="18" border="0" id="html5image" title="" /></a>
  <div class="html5text" id="html5text"></div>
</div>
<form id="form1" enctype="multipart/form-data" method="post" action="fs_uploadit.php" >
  <table width="100%" border="0" cellspacing="6">
    <tr>
      <td class="box" rowspan="4" valign="top">
      <div id="fileInfoView">
           <div class="box">
              <div id="uploadhtml5" style="display:none">
                <div class="menu"><a href="#"  onclick="browse()" style="cursor:pointer;width:33%;">Select files</a></div>
                <input style="display:none; padding-right:6px;" type="file" name="fileToUpload" id="fileToUpload" onchange="fileSelected();" multiple/>
              </div>
              <div id="file_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_FILE"); ?></div>
              <div id="uploadstandard" style="display:none"> 
                <script language="JavaScript" type="text/javascript">
<!--
// Version check for the Flash Player that has the ability to start Player Product Install (6.0r65)
var hasProductInstall = DetectFlashVer(6, 0, 65);

// Version check based upon the values defined in globals
var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);
if(!html5) {
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
             'wmode',"transparent",
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
    var alternateContent = '<div id="errmessage" align="center"><br />This application requires Flash for uploading files.<br /><br />'
  	+ 'To install Flash Player go to www.adobe.com.<br /> <br /> '
   	+ '</div>';
	$("#content").html(alternateContent);
  }
}
// -->
</script>
                <div id="uploadstandardspinner" style="padding-top:10px;display:none"><img src="images/ajax-loader-sm.gif" alt="" border="0" align="left" style="padding-right:6px" /><?php echo lang("_UPLOADING_WAIT"); ?></div>
              </div>
              <br />
            </div>
          </div>	
            
  <div id="dragfilestouploadcss" style="height:400px" class="box">
          <div  id="filestoupload" style="width:100%; height:100%; overflow:auto;"><div style="text-align:center; vertical-align:middle" class="heading">drag & drop your files here</div> </div>
      </div></td>
      <td width="50%" height="100%" valign="top" class="box">
      
           <div class="box">
          <div class="fieldcontainer" id="upload_from">
            <div class="mandatory"><?php echo lang("_FROM"); ?>:</div> <div class="input"><?php echo $senderemail ?></div>
          </div>
          <div class="fieldcontainer">
            <div class="label mandatory"id="upload_to" ><?php echo lang("_TO") ; ?>:</div>
            <div class="field">
              <input name="fileto" type="text" id="fileto" title="<?php echo lang("_EMAIL_SEPARATOR_MSG") ; ?>" onchange="validate_fileto()" value="" placeholder="Enter recipient email"/>
              <div id="fileto_msg" style="display: none" class="validation_msg field"  class="">
              <?php echo lang("_INVALID_MISSING_EMAIL"); ?>
              <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo lang("_MAXEMAILS"); ?> <?php echo $config['max_email_recipients'] ?>.</div>
            </div>
          </div>
        </div>
        <div class="fieldcontainer" id="upload_subject">
          <div class="label"><?php echo lang("_SUBJECT"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</div>
          <div class="field" >
            <input name="filesubject" type="text" id="filesubject" />
          </div>
        </div>
        <div class="fieldcontainer" id="upload_message">
          <div class="label"><?php echo lang("_MESSAGE"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</div>
          <div class="field">
            <textarea name="filemessage" cols="57" rows="5" id="filemessage"></textarea>
          </div>
        </div>
        <input name="filefrom" type="hidden" id="filefrom" value="<?php echo $senderemail ?>" size="40" />
        <div>
          <input type="hidden" id="filevoucheruid" name="filevoucheruid" value="<?php echo $voucherUID; ?>" />
          <input type="hidden" name="vid" id="vid" value="<?php echo $voucherUID; ?>" />
          <input type="hidden" name="total" id="total" value="" />
          <input type="hidden" name="n" id="n" value="" />
          <input type="hidden" id="filestatus" name="filestatus" value="<?php echo $filestatus; ?>" />
          <input type="hidden" name="loadtype" id="loadtype" value="standard" />
          
        
        </div>
        
        
      </td>
    </tr>
    <tr>
      <td valign="top" height="100%" class="box">
      
        <div id="options">
          <div class="fieldcontainer" id="upload_expirydate">
            <div class="label mandatory"><?php echo lang("_EXPIRY_DATE"); ?>:</div>
            <div class="field">
              <input id="datepicker" name="datepicker" title="<?php echo lang('_DP_dateFormat'); ?>" onchange="validate_expiry()" />
              <div id="expiry_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_EXPIRY_DATE"); ?></div>
            </div>
          </div>
          <div class=" mandatory">
            <input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date($lang['datedisplayformat'],strtotime("+".$config['default_daysvalid']." day"));?>" />
          </div>
          <br/>
          Send copy of emails to me
          <input name="rtnemail" type="checkbox" id="rtnemail" style="float:left; width:20px;" />
        </div></td>
    </tr>
    <tr>
      <td class="box" height="20px">
        <?php if ($config["AuP"]) {?>
          <div class="auppanel">
            <input style="float:left" name="aup" type="checkbox" id="aup" onchange="validate_aup()" <?php echo ($config["AuP_default"] ) ? "checked" : ""; ?> <?php echo (isset($_SESSION["aup"]) && !$authvoucher->aVoucher() ) ? "checked" : ""; ?> value="true" />
            <div id="aup_label" title="<?php echo lang("_SHOWHIDE"); ?>" onclick="toggleTOG()" style="cursor:pointer;"><?php echo lang("_ACCEPTTOC"); ?></div>
            <div id="aup_msg" class="validation_msg" style="display: none"><?php echo lang("_AGREETOC"); ?></div>
            <div id="tog" style="display:none"> <?php echo lang("_AUPTERMS"); ?> </div>
          </div>
          <?php } ?>
          <div><div class="menu" id="uploadbutton" style="display:"><a href="#" onclick="validate()"><?php echo lang("_SEND"); ?></a></div></div></td>
    </tr>
  </table>
  <div class="colmask threecol" id="dragfilestoupload"> </div>
</form>
<div id="dialog-default" style="display:none" title=""> </div>
<div id="dialog-cancel" style="display:none" title="<?php echo lang("_CANCEL_UPLOAD"); ?>"><?php echo lang("_ARE_YOU_SURE"); ?></div>
<div id="dialog-uploadprogress" title="" style="display:none"> <img id="progress_image" name="progress_image" src="images/ajax-loader-sm.gif" width="16" height="16" alt="Uploading" align="left" />
  <div id="progress_container">
    <div id="progress_bar">
      <div id="progress_completed"></div>
    </div>
  </div>
</div>
<div id="dialog-support" title="" style="display:none">
  <?php require_once("$filesenderbase/pages/html5display.php"); ?>
</div>
<div id="dialog-autherror" title="<?php echo lang($lang["_MESSAGE"]); ?>" style="display:none"><?php echo lang($lang["_AUTH_ERROR"]); ?></div>
