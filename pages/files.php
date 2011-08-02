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
 * MyFiles Page
 * ---------------------------------
 * 
 */


if(isset($_REQUEST["a"]) && isset($_REQUEST["id"])) 
{
$myfileData = $functions->getVoucherData($_REQUEST['id']);
//if(isset($fileData["fileid"])
//{
//$fileID = $fileData["fileid"];
if($_REQUEST["a"] == "del" )
{
if($functions->deleteFile($myfileData[0]["fileid"]))
{
echo "<div id='message'>File Deleted</div>";
}
}

if($_REQUEST["a"] == "resend")
{
$sendmail->sendEmail($myfileData[0] ,$config['fileuploadedemailbody']);
echo "<div id='message'>File Resent</div>";
}

if($_REQUEST["a"] == "add")
{
// display the add box
}

//}
}
$filedata = $functions->getUserFiles();
//$filedata = $filedata[0];
//echo $filedata;
$json_o=json_decode($filedata,true);
?>
 <div id="box">
<?php echo '<div id="pageheading">'._MY_FILES.'</div>'; ?> 
<div id="tablediv">
<table id="myfiles" width="750" border="0" cellspacing="1" style="table-layout:fixed;">
<tr class="headerrow">
<td width="18">&nbsp;</td>
<td width="18">&nbsp;</td>
<td><strong><?php echo _TO; ?></strong></td>
<td><strong><?php echo _FROM; ?></strong></td>
<td><strong><?php echo _FILE_NAME; ?></strong></td>
<td width="60"><strong><?php echo _SIZE; ?></strong></td>
<td><strong><?php echo _SUBJECT; ?></strong></td>
<td width="16"><strong></strong></td>
<td width="80"><strong><?php echo _CREATED; ?></strong></td>
<td width="80"><strong><?php echo _EXPIRY; ?></strong></td>
<td width="18">&nbsp;</td>
</tr>
<?php 
if(sizeof($json_o) > 0)
{
foreach($json_o as $item) {
   echo "<tr><td valign='top'></td><td valign='top'><a href='index.php?s=files&a=resend&id=" .$item['filevoucheruid'] . "'><img src='images/email_go.png' title='Re-send Email'></a></td>";
   if($item['fileto'] == $attributes["email"])
   {
   echo "<td class='HardBreak' valign='top'>Me</td>";
   } else {
   echo "<td class='HardBreak'>" .$item['fileto'] . "</td>";
   }
    if($item['filefrom'] == $attributes["email"])
   {
   echo "<td class='HardBreak'>Me</td>";
   } else {
   echo "<td class='HardBreak'>" .$item['filefrom'] . "</td>";
   }
   echo "<td class='HardBreak'><a href='download.php?vid=". $item["filevoucheruid"]."' target='_blank'>" .$item['fileoriginalname']. "</a></td><td>" .formatBytes($item['filesize']). "</td><td>".$item['filesubject']. "</td><td>";
   if($item['filemessage'] != "")
   {
   echo "<img src='images/page_white_text_width.png' border='0' title='".$item['filemessage']. "'>";
   }
   echo "</td><td>" .date("d/m/Y",strtotime($item['filecreateddate'])) . "</td><td>" .date("d/m/Y",strtotime($item['fileexpirydate'])) . "</td><td  valign='top'  width='22'><div style='cursor:pointer;'><img onclick='confirmdelete(".'"' .$item['filevoucheruid'] . '")'. "' src='images/shape_square_delete.png' title='Delete' ></div></td></tr>"; //etc
   }
} else {
	echo "<tr><td colspan='7'>There are currently no files available</td></tr>";
}
?>
</table>
</div>
</div>
<div id="dialog-delete" title="Delete File">
  <p>Are you sure you want to delete this File?</p>
</div>
<script type="text/javascript">
var selectedFile = "";

	$(function() {
	
		$("#myfiles tr:odd").not(':first').addClass('altcolor');
		$("#dialog-delete").dialog({ autoOpen: false, height: 140, modal: true,
		
		buttons: {
				Cancel: function() {
					$( this ).dialog( "close" );
				},
				Delete: function() { 
				deletefile();
				$( this ).dialog( "close" );
				}
		}
		});
	});
	
	
	
	function deletefile()
		{
		window.location.href="index.php?s=files&a=del&id=" + selectedFile;
		}
	
	function confirmdelete(vid)
		{
			selectedFile = vid;
			$( "#dialog-delete" ).dialog( "open" );
		}
		
</script>