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
 * My Downloads Page
 * ---------------------------------
 * 
 */


// get file data
if (isset($_REQUEST['vid'])) {
$vid = $_REQUEST['vid'];
$filedata = $functions->getVoucherData($vid);
}
?>
<script src="js/crypter/sjcl.js" type="text/javascript"></script>
<script src="js/crypter/FileSaver.js" type="text/javascript"></script>
<script src="js/crypter/FileStorage.js" type="text/javascript"></script>
<script src="js/crypter/shared.js" type="text/javascript"></script>
<script src="js/crypter/downloader.js" type="text/javascript"></script>

<script type="text/javascript">
//<![CDATA[
// Variables needed for crypto
var chunksize =  2000000;
var passwordprompt = "<?php echo lang("_ENCRYPT_PASSWDPROMPT"); ?>";
var encrypted = <?php echo $filedata["fileencryption"]?'true':'false';?>;
var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0; // At least Safari 3+: "[object HTMLElementConstructor]"
// End variables needed for crypto

$(document).ready(function() { 
	$("#message").hide();

	if ((!html5 || isSafari) && encrypted) {
		$("#download a").attr("onclick", "");
		$('#download').button("disable");
		$("#fileencryption").attr("class", "validation_msg");
		$("#fileencryption").css({"font-size": "12px"});
		$("#fileencryption").text('<?php echo lang("_YES")." (".lang("_ENCRYPT_DOWNLOAD_NOT_POSSIBLE").")" ?>');
	}

	// set dialog cancel upload
	$("#dialog-cancel").dialog({ autoOpen: false, height: 140, width: 350, modal: true,
	buttons: {
			'downloadconfirmyesBTN': function() {
			location.reload(true);

			},
			'downloadconfirmnoBTN': function() { 
			$( this ).dialog( "close" );
			}
	}
	});
	$('.ui-dialog-buttonpane button:contains(downloadconfirmnoBTN)').attr("id","btn_downloadconfirmno");            
	$('#btn_downloadconfirmno').html('<?php echo lang("_NO") ?>') 
	$('.ui-dialog-buttonpane button:contains(downloadconfirmyesBTN)').attr("id","btn_downloadconfirmyes");            
	$('#btn_downloadconfirmyes').html('<?php echo lang("_YES") ?>') 
	
	$("#dialog-downloadprogress").dialog({ 		
	    open: function() {
      //Hide closing "X" for this dialog only.
      $(this).parent().children().children("a.ui-dialog-titlebar-close").remove();
	},
	autoOpen: false, height: 180,width: 400, modal: true,title: "<?php echo lang("_DOWNLOAD_PROGRESS") ?>:",		
	buttons: {
		'downloadcancelBTN': function() {
			// are you sure?
			$("#dialog-cancel").dialog('open');
			$('.ui-dialog-buttonpane > button:last').focus();
			}	
		}
	})
	$('.ui-dialog-buttonpane button:contains(downloadcancelBTN)').attr("id","btn_downloadcancel");            
	$('#btn_downloadcancel').html('<?php echo lang("_CANCEL") ?>') 

	$("#dialog-password").dialog({
		autoOpen:false, height: "auto", width: 500, modal: true
	});

	// default error message dialogue
	$("#dialog-default").dialog({ autoOpen: false, height: 140, height: 200, modal: true,title: "",		
	buttons: {
		'<?php echo lang("_OK") ?>': function() {
			$("#dialog-default").html("");
			$( this ).dialog( "close" );
			}
		}
	});
});

function errorDialog(msg)
{
	$("#dialog-default").dialog("option", "title", "Error");
    $("#default-msg").html(msg);
    $("#dialog-default").dialog("open")
}


function startDownload() {
    if (encrypted) {
		if( ! html5 ) return false;

		$("#dialog-password").dialog({
			buttons: {
				'<?php echo lang("_OK"); ?>': function() { 
					$( this ).dialog( "close" ); 
					startDownloadWithPassword($("#decpassword").val());
				},
				'<?php echo lang("_CLOSE"); ?>' : function() {
					$( this ).dialog( "close" ); 
				}
			},
			open: function( event, ui ) { $("#decpassword").focus(); }
		});
	
		$("#dialog-password").dialog("open");
		
            return false; // False to prevent direct download
        }
    else {
        $("#message").show();
        return true; // True to start direct download
    }
}

function startDownloadWithPassword(password) {
	var source = 'download.php?vid=<?php echo urlencode($filedata["filevoucheruid"]);?>';
    var filename = '<?php echo utf8tohtml($filedata["fileoriginalname"],TRUE);?>';
    var filesize = '<?php echo $filedata["filesize"];?>';

            var downloader = new Downloader(chunksize, {
                progress: function(number) {
                    $("#progress_bar").width(number / 100 * $('#progress_container').width());
		    //set width of progress bar based on the $status value (set a
                    $("#progress_bar").html(number + "% ");
                    $("#progress_completed").html(parseInt(number) + "%");
		    //display the % completed within the progress bar
                    console.log("pbupdate" + number + "%");
                },
		onComplete: function( fileStorage ){
		$("#dialog-downloadprogress").dialog('close');

		// IE10 ONLY
		if( window.navigator.msSaveBlob ){
  	           window.navigator.msSaveBlob(fileStorage.getBlob(), filename );
		   return;
		}

		$('#download').attr('onclick', null );
		$('#download').attr('href', fileStorage.getURL());
		$('#download').attr('download', filename);
		$('#download').html('Click here to save your file');

                },
                onError: function(error) {
                    $("#dialog-downloadprogress").dialog('close');
                    errorDialog('<?php echo addslashes(lang("_ERROR_MESSAGE")); ?>'+"<br/>"+error);
		},onAbort: function(error) {
            $("#dialog-downloadprogress").dialog('close');
            errorDialog('<?php echo addslashes(lang("_ERROR_MESSAGE")); ?>'+"<br/>"+error);
		},onStatus: function(msg) {
            console.log("download status:"+msg);
		}
            });
                

	downloader.start(source, filesize, password);
  	$("#dialog-downloadprogress").dialog('open')
	$("#progress_bar").show();
}


//]]>
</script>
<div id="dialog-default" style="display:none" title=""><div id="default-msg"></div></div>
<div id="dialog-cancel" style="display:none" title="<?php echo lang("_CANCEL_DOWNLOAD"); ?>"><?php echo lang("_ARE_YOU_SURE"); ?></div>
<div id="dialog-downloadprogress" title="" style="display:none">
<img id="progress_image" name="progress_image" src="images/ajax-loader-sm.gif" width="16" height="16" alt="Uploading" align="left" /> 
	<div id="progress_container">
   		<div id="progress_bar" style="display:none">
		<div id="progress_completed"></div>
	</div>
	</div> 
</div>

<div id='message'><?php echo lang("_STARTED_DOWNLOADING") ?></div>
<div id="box">
<?php echo '<div id="pageheading">'.lang("_DOWNLOAD").'</div>'; ?> 
  <div id="tablediv">
  <table>
  <tr><td id="download_to"><?php echo lang("_TO"); ?>:</td><td id="to"><?php echo htmlentities($filedata["fileto"]);?></td></tr>
  <tr><td id="download_from"><?php echo lang("_FROM"); ?>:</td><td id="from"><?php echo htmlentities($filedata["filefrom"]);?></td></tr>
  <tr><td id="download_subject"><?php echo lang("_SUBJECT"); ?>:</td><td id="subject"><?php echo utf8tohtml($filedata["filesubject"],TRUE);?></td></tr>
  <tr><td id="download_message"><?php echo lang("_MESSAGE"); ?>:</td><td id="filemessage"><?php echo nl2br(utf8tohtml($filedata["filemessage"],TRUE));?></td></tr>
  <tr><td id="download_filename"><?php echo lang("_FILE_NAME"); ?>:</td><td id="filename"><?php echo utf8tohtml($filedata["fileoriginalname"],TRUE);?></td></tr>
  <tr><td id="download_filesize"><?php echo lang("_FILE_SIZE"); ?>:</td><td id="filesize"><?php echo formatBytes($filedata["filesize"]);?></td></tr>
  <tr><td id="download_expiry"><?php echo lang("_EXPIRY_DATE"); ?>:</td><td id="expiry"><?php echo date($lang['datedisplayformat'],strtotime($filedata["fileexpirydate"]));?></td></tr>
  <tr><td id="download_encryption"><?php echo lang("_ENCRYPTION"); ?>:</td><td id="fileencryption"><?php echo $filedata["fileencryption"]?lang("_YES")." (v".$filedata["fileencryption"].")":lang("_NO");?></td></tr>
  </table>
  </div>
  <div class="menu" id="downloadbutton" ><p><a id="download" href="download.php?vid=<?php echo urlencode($filedata["filevoucheruid"]);?>" onclick="return startDownload();"><?php echo lang("_START_DOWNLOAD"); ?></a></p></div>
</div>

<div id="dialog-password" title="<?php echo lang($lang["_DECRYPTION"]); ?>" style="display: none">
	<div><p><?php echo lang($lang["_DECRYPT_PASSWDPROMPT"]); ?><input type="text" name="decpassword" id="decpassword" size="24" autofocus />
	</p></div>
</div>
