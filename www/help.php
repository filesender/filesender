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
 * default help file
 * ---------------------------------
 * 
 */
require_once('../classes/_includes.php');
 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en"><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>FileSender:</title>
<link rel="stylesheet" type="text/css" href="css/default.css" />

<style type="text/css">
<!--
.style1 {
	color: #FFFFFF;
	font-weight: bold;
	padding-left:5px;
}
-->
</style>
</head>
<body scroll="no">

<div id="wrap">
	
  <div id="header">
  <img src="displayimage.php" width="800" height="60" border="0" alt="banner"/>
    <p class="style5 style1">Help</p>
  </div>
  <p>

  </p>
  <div id="content">
  <div style="padding:5px">
    <h4>Login</h4> 
    <ul>
    <li>If you don't see your institution in the list of Identity Providers (IdPs), or your institutional login fails, please contact your local IT support</li>
    </ul>

<h4>Required for sending a file</h4>

You'll be able to send (upload) files if one of these two situations applies to you:

<ul> 

<li><b>A]   your browser runs Adobe Flash.</b> <I>If you're able to watch
youtube videos, it's safe to say this upload method will work for you. Another
way of checking is by visiting this test page for Adobe Flash:
<a href="http://www.adobe.com/software/flash/about/"> http://www.adobe.com/software/flash/about/</a>.

This flash-based method suffices if the file you are uploading is smaller than
2 Gigabyte (2GB). FIleSender will warn you should you try to upload a file
that's too big for this method. If you need to upload something bigger than
2GB, you need method b)</li>

<li> B]  you have a very recent browser version, and it supports HTML5, the latest version of the "language of the web". In this
scenario, FileSender really comes into its own; larger-than-2Gb transfers become possible and upload suspend/resume
functionality will work.
It's a bit harder to say what browsers are "recent enough", but as of October 2011, an updated version of either Firefox4
(and higher) or Chrome is known to work on both Windows, Mac OSX and Linux. FileSender itself will also tell you when your
browser is "good enough", by placing a green "HTML5" tick on the right hand side of your main upload window.</li>

<h4>Limits of this FileSender installation</h4>
    <ul>
    <li><strong>
      Maximum recipient  addresses per email:</strong>  <?php echo $config["max_email_recipients"]?> multiple email addresses (separated by comma or semi-colon)</li>
    <li><strong>Maximum number of files per  upload:</strong> one - to upload several files at once, zip them into a  single archive first</li>
    <li><strong>Maximum file size per upload, without  HTML5 support: </strong> <?php echo formatBytes($config["max_flash_upload_size"])?></li>
    <li><strong>Maximum file size per upload, with HTML5 support: </strong> <?php echo formatBytes($config["max_gears_upload_size"])?></li>
    <li>      <strong>Maximum  file / voucher expiry days: </strong><?php echo $config["default_daysvalid"]?> </li>
    </ul>
    <p>For more information please visit <a href="http://www.filesender.org/">www.filesender.org</a></p>
  </div>
  <hr />
  </div> <!-- #content -->

</div><!-- #wrap -->

</body>
</html>
