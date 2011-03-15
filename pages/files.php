<?php 
// add,del,resend

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
<?php echo '<div id="pageheading">'._MY_FILES.'</div>'; ?> 
<div id="tablediv">
<table width="100%" border="0" cellspacing="1">
<tr bgcolor="#eeeeee">
  <td bgcolor="#FFFFFF">&nbsp;</td>
  <td bgcolor="#FFFFFF"><strong><?php echo _TO; ?></strong></td>
<td bgcolor="#FFFFFF"><strong><?php echo _FILE_NAME; ?></strong></td>
<td bgcolor="#FFFFFF"><strong><?php echo _SIZE; ?></strong></td>
<td bgcolor="#FFFFFF"><strong><?php echo _SUBJECT; ?></strong></td>
<td bgcolor="#FFFFFF"><strong><?php echo _CREATED; ?></strong></td>
<td bgcolor="#FFFFFF"><strong><?php echo _EXPIRY; ?></strong></td>
<td bgcolor="#FFFFFF">&nbsp;</td>
</tr>
<?php 
foreach($json_o as $item) {
   echo "<tr  bgcolor='#eeeeee'><td><a href='index.php?s=files&a=resend&id=" .$item['filevoucheruid'] . "'><img src='images/email_go.png'></a></td><td>" .$item['fileto'] . "</td><td><a href='download.php?vid=". $item["filevoucheruid"]."' target='_blank'>" .$item['fileoriginalname']. "</a></td><td>" .formatBytes($item['filesize']). "</td><td>".$item['filesubject']. "</td><td>" .date("d/m/Y",strtotime($item['filecreateddate'])) . "</td><td>" .date("d/m/Y",strtotime($item['fileexpirydate'])) . "</td><td><a href='index.php?s=files&a=del&id=" .$item['filevoucheruid'] . "'><img src='images/shape_square_delete.png'></a></td></tr>"; //etc
}


?>
</table>
</div>
<p>.</p>