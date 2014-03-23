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

$vid = "";
// get file data
if (isset($_REQUEST['vid'])) {
$vid = $_REQUEST['vid'];
$filedata = $functions->getVoucherData($vid);
}
?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function() { 
vid = 
$("#message").hide();
$("#errmessage").hide();
});
function startDownload()
{
    // hide messages
    $("#message").hide();
    $("#errmessage").hide();
    // check file exists and is available
    $.ajax({
	  type: "POST",
	  url: "fs_upload.php?type=validdownload&vid=<?php echo htmlspecialchars($vid,ENT_QUOTES); ?>"
	,success:function( data ) {
	
	var data =  parseJSON(data);
	// display error messages
	if(data.errors)
		{
		$.each(data.errors, function(i,result){
		if(result == "err_download") { $("#errmessage").show();} //
        if(result == "err_filesizeincorrect") { window.location.href="index.php?s=filesizeincorrect";} //	
		})
		} else {
            if(data.download && data.download == "available"){
            window.location.href="download.php?vid=<?php echo urlencode($filedata["filevoucheruid"]);?>";
	        $("#message").show();
            } else {
               $("#errormessage").show(); 
            }
         }
        }
    })
}
//]]>
</script>

<div id='message'><?php echo lang("_STARTED_DOWNLOADING") ?></div>
<div id='errmessage'><?php echo lang("_INVALID_VOUCHER") ?></div>
<div id="box"> <?php echo '<div id="pageheading">'.lang("_DOWNLOAD").'</div>'; ?>
  <div id="tablediv">
    <table width="100%">
      <tr>
        <td id="download_to"><?php echo lang("_TO"); ?>:</td>
        <td id="to"><?php echo htmlentities($filedata["fileto"]);?></td>
      </tr>
      <tr>
        <td id="download_from"><?php echo lang("_FROM"); ?>:</td>
        <td id="from"><?php echo htmlentities($filedata["filefrom"]);?></td>
      </tr>
      <tr>
        <td id="download_subject"><?php echo lang("_SUBJECT"); ?>:</td>
        <td id="subject"><?php echo utf8tohtml($filedata["filesubject"],TRUE);?></td>
      </tr>
      <tr>
        <td id="download_message"><?php echo lang("_MESSAGE"); ?>:</td>
        <td id="filemessage"><?php echo nl2br(utf8tohtml($filedata["filemessage"],TRUE));?></td>
      </tr>
      <tr>
        <td id="download_filename"><?php echo lang("_FILE_NAME"); ?>:</td>
        <td id="filename"><?php echo utf8tohtml($filedata["fileoriginalname"],TRUE);?></td>
      </tr>
      <tr>
        <td id="download_filesize"><?php echo lang("_FILE_SIZE"); ?>:</td>
        <td id="filesize"><?php echo formatBytes($filedata["filesize"]);?></td>
      </tr>
      <tr>
        <td id="download_expiry"><?php echo lang("_EXPIRY_DATE"); ?>:</td>
        <td id="expiry"><?php echo date($lang['datedisplayformat'],strtotime($filedata["fileexpirydate"]));?></td>
      </tr>
    </table>
  </div>
  <div class="menu" id="downloadbutton" >
    <p><a id="download" onclick="startDownload()"><?php echo lang("_START_DOWNLOAD"); ?></a></p>
  </div>
</div>
