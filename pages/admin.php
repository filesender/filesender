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
 * Admin Page
 * ---------------------------------
 * 
 */
 ?>
 <script>
	$(function() {
		$( "#tabs" ).tabs();
	});
	</script>

<?php 

// get file data

$json_o = $functions->adminFiles();
$json_log = $functions->adminLogs();
//$filedata = $filedata[0];
//$json_o=$filedata;
//$json_log=$logdata;
$drivespace = $functions->driveSpace();

?>
<?php echo '<div id="pageheading">'._ADMIN.'</div>'; ?> 
<div id="tabs">
	<ul>
    <?php
	// admin tab names
		echo '<li><a href="#tabs-1">'._GENERAL.'</a></li>';
		echo '<li><a href="#tabs-2">'._UPLOADS.'</a></li>';
		echo '<li><a href="#tabs-3">'._DOWNLOADS.'</a></li>';
		echo '<li><a href="#tabs-4">'._ERRORS.'</a></li>';
		echo '<li><a href="#tabs-5">'._FILES_AVAILABLE.'</a></li>';
		echo '<li><a href="#tabs-6">'._ACTIVE_VOUCHERS.'</a></li>';
		echo '<li><a href="#tabs-7">'._COMPLETE_LOG.'</a></li>';
		?>
	</ul>
  <div id="tabs-1">
	<?php echo  $functions->getStats(); ?><br />
	<div id="tablediv1">
	<table width="100%" border="0" cellpadding="4">
      <tr>
        <td>Drive</td>
        <td>Total</td>
        <td>Used</td>
        <td>Available</td>
        <td>Use %</td>
      </tr>
      <tr>
        <td>Files</td>
        <td><?php echo  formatBytes($drivespace["site_filestore_total"]) ?></td>
        <td><?php echo  formatBytes($drivespace["site_filestore_total"]-$drivespace["site_filestore_free"]) ?></td>
        <td><?php echo  formatBytes($drivespace["site_filestore_free"]) ?></td>
        <td></td>
      </tr>
      <tr>
        <td>Temp</td>
        <td><?php echo  formatBytes($drivespace["site_temp_filestore_total"]) ?></td>
        <td><?php echo  formatBytes($drivespace["site_temp_filestore_total"]-$drivespace["site_temp_filestore_free"]) ?></td>
        <td><?php echo  formatBytes($drivespace["site_temp_filestore_free"]) ?></td>
        <td></td>
      </tr>
    </table>
	</div>
	</div>
	<div id="tabs-2">
<div id="tablediv1">
<table width="100%" border="0" cellspacing="1" bgcolor="#FFFFFF">
<tr bgcolor="#eeeeee">
<?php 
echo '<td><strong>'._TO.'</strong></td>';
echo '<td><strong>'._FROM.'</strong></td>';
echo '<td><strong>'._FILE_NAME.'</strong></td>';
echo '<td><strong>'._SIZE.'</strong></td>';
echo '<td><strong>'._CREATED.'</strong></td>';
?>
</tr>
<?php 
foreach($json_log as $item) {
if($item['logtype'] == "Uploaded")
{
   echo "<tr  bgcolor='#eeeeee'><td>" .$item['logto'] . "</td><td>" .$item['logfrom'] . "</td><td>" .$item['logfilename']. "</td><td>" .formatBytes($item['logfilesize']). "</td><td>" .date("d/m/Y",strtotime($item['logdate'])) . "</td></tr>"; //etc
}
}

?>
</table>
</div>
	</div>
	<div id="tabs-3">
	
<div id="tablediv1">	
<table width="100%" border="0" cellspacing="1" bgcolor="#FFFFFF">
<tr bgcolor="#eeeeee">
<?php
echo '<td><strong>'._TO.'</strong></td>';
echo '<td><strong>'._FROM.'</strong></td>';
echo '<td><strong>'._FILE_NAME.'</strong></td>';
echo '<td><strong>'._SIZE.'</strong></td>';
echo '<td><strong>'._CREATED.'</strong></td>';
?>
</tr>
<?php 
foreach($json_log as $item) {
if($item['logtype'] == "Download")
{
   echo "<tr  bgcolor='#eeeeee'><td>" .$item['logto'] . "</td><td>" .$item['logfrom'] . "</td><td>" .$item['logfilename']. "</td><td>" .formatBytes($item['logfilesize']). "</td><td>" .date("d/m/Y",strtotime($item['logdate'])) . "</td></tr>"; //etc
}
}

?>
</table>

</div>

	</div>
	<div id="tabs-4">
	
	<div id="tablediv1">
	<table width="100%" border="0" cellspacing="1" bgcolor="#FFFFFF">
<tr bgcolor="#eeeeee">
<?php 
echo '<td><strong>'._TO.'</strong></td>';
echo '<td><strong>'._FROM.'</strong></td>';
echo '<td><strong>'._FILE_NAME.'</strong></td>';
echo '<td><strong>'._SUBJECT.'</strong></td>';
echo '<td><strong>'._CREATED.'</strong></td>';
?>
</tr>
<?php 
foreach($json_log as $item) {
if($item['logtype'] == "Error")
{
   echo "<tr  bgcolor='#eeeeee'><td>" .$item['logto'] . "</td><td>" .$item['logfrom'] . "</td><td>" .$item['logfilename']. "</td><td>" .date("d/m/Y",strtotime($item['logdate'])) . "</td></tr>"; //etc
	echo "<tr><td colspan=4>".$item['logmessage']."</td></tr>";
}
}

?>
</table>
</div>

	</div>
	<div id="tabs-5">
	
	<div id="tablediv1">
		<table width="100%" border="0" cellspacing="1" bgcolor="#FFFFFF">
<tr bgcolor="#eeeeee">
<?php 
echo '<td><strong>'._TO.'</strong></td>';
echo '<td><strong>'._FROM.'</strong></td>';
echo '<td><strong>'._FILE_NAME.'</strong></td>';
echo '<td><strong>'._SIZE.'</strong></td>';
echo '<td><strong>'._SUBJECT.'</strong></td>';
echo '<td><strong>'._CREATED.'</strong></td>';
echo '<td><strong>'._EXPIRY.'</strong></td>';
?>
</tr>
<?php 
foreach($json_o as $item) {
if($item['filestatus'] == "Available")
{
   echo "<tr  bgcolor='#eeeeee'><td>" .$item['fileto'] . "</td><td>" .$item['filefrom'] . "</td><td>" .$item['fileoriginalname']. "</td><td>" .formatBytes($item['filesize']). "</td><td>".$item['filesubject']. "</td><td>" .date("d/m/Y",strtotime($item['filecreateddate'])) . "</td><td>" .date("d/m/Y",strtotime($item['fileexpirydate'])) . "</td></tr>"; //etc
}
}

?>
</table>
</div>
	</div>
	<div id="tabs-6">
	<div id="tablediv1">
			<table width="100%" border="0" cellspacing="1" bgcolor="#FFFFFF">
<tr bgcolor="#eeeeee">
<?php 
echo '<td><strong>'._TO.'</strong></td>';
echo '<td><strong>'._FROM.'</strong></td>';
echo '<td><strong>'._FILE_NAME.'</strong></td>';
echo '<td><strong>'._SIZE.'</strong></td>';
echo '<td><strong>'._SUBJECT.'</strong></td>';
echo '<td><strong>'._CREATED.'</strong></td>';
echo '<td><strong>'._EXPIRY.'</strong></td>';
?>
<?php 
foreach($json_o as $item) {
if($item['filestatus'] == "Voucher")
{
   echo "<tr  bgcolor='#eeeeee'><td>" .$item['fileto'] . "</td><td>" .$item['filefrom'] . "</td><td>" .$item['fileoriginalname']. "</td><td>" .formatBytes($item['filesize']). "</td><td>".$item['filesubject']. "</td><td>" .date("d/m/Y",strtotime($item['filecreateddate'])) . "</td><td>" .date("d/m/Y",strtotime($item['fileexpirydate'])) . "</td></tr>"; //etc
}
}

?>
</table>
</div>
	</div>
	<div id="tabs-7">

<div id="tablediv1">
	<table width="100%" border="0" cellspacing="1" bgcolor="#FFFFFF">
<tr bgcolor="#eeeeee">
<?php 
echo '<td><strong>'._TYPE.'</strong></td>';
echo '<td><strong>'._TO.'</strong></td>';
echo '<td><strong>'._FROM.'</strong></td>';
echo '<td><strong>'._FILE_NAME.'</strong></td>';
echo '<td><strong>'._CREATED.'</strong></td>';
?>
</tr>
<?php 
foreach($json_log as $item) {
   echo "<tr  bgcolor='#eeeeee'><td><b>" .$item['logtype'] . "</b></td><td>" .$item['logto'] . "</td><td>" .$item['logfrom'] . "</td><td>" .$item['logfilename']. "</td><td>" .date("d/m/Y",strtotime($item['logdate'])) . "</td></tr>"; //etc
	echo "<tr><td colspan=5>".$item['logmessage']."</td></tr>";
}

?>
</table>
</div>


	</div>
</div>

<p>.</p>
