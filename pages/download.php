<?php 

// get file data
 if (isset($_REQUEST['vid'])) {
 $vid = $_REQUEST['vid'];
$filedata = $functions->getVoucherData($vid);
$filedata = $filedata[0];

}

?>

<blockquote>
<?php echo '<div id="pageheading">'._DOWNLOAD.'</div>'; ?> 
  <div id="tablediv">
  <p>To:<?php echo $filedata["fileto"];?></p>
  <p>From:<?php echo $filedata["filefrom"];?></p>
  <p>Subject:<?php echo $filedata["filesubject"];?></p>
  <p>Message:<?php echo $filedata["filemessage"];?></p>
  <p>Filename:<?php echo $filedata["fileoriginalname"];?></p>
  <p>File Size:<?php echo formatBytes($filedata["filesize"]);?></p>
  <p>Expiry Date:<?php echo $filedata["fileexpirydate"];?></p>
  <p><a href="download.php?vid=<?php echo $filedata["filevoucheruid"];?>" target="_blank">Download Link   </a></p>
  <p>&nbsp;</p>
  </div>
  <p>.</p>
</blockquote>
