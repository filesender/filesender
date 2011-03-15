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
 * My Downloads Page
 * ---------------------------------
 * 
 */


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
