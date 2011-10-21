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

.textmiddle {
        vertical-align:middle;
        padding-right: 10px;
        padding-top: 5px;
        padding-bottom: 5px;
}
</style>
</head>
<body scroll="no">

<div id="wrap">
	
  <div id="header">
  <img src="displayimage.php" width="800" height="60" border="0" alt="banner"/>
    <p class="style5 style1">Help</p>
  </div>

  <div id="content">
  <div style="padding:5px">
    <h4>Login</h4> 
    <ul>
    <li>If you don't see your institution in the list of Identity Providers (IdPs), or your institutional login fails, please contact your local IT support</li>
    </ul>

<h4>Uploads smaller than 2 Gigabytes (2GB) with Adobe Flash</h4>
<ul>
	<li>If you can watch YouTube videos this method should work for you</li>
	<li>You need a modern browser running version 10 (or higher) of <a target="_blank" href="http://www.adobe.com/software/flash/about/">Adobe Flash.</a></li>
	<li>FileSender will warn you should you try to upload a file that is too big for this method</li>
</ul>

<h4>Uploads of <i>any size</i> with HTML5</h4>
<ul>
        <li>If you see <img src="html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /> in FileSender's top right-hand corner this method works for you</li>
	<li>You need a very recent browser supporting HTML5, the latest version of the "language of the web".</li>
	<li>Currently Firefox4 (and higher) and Chrome on both Windows, Mac OSX and Linux are known to work.</li>
	<li>Your browser also needs to run version 10 (or higher) of <a target="_blank" href="http://www.adobe.com/software/flash/about/">Adobe Flash.</a></li>
</ul>

<h4>Downloads of any size</h4>
<ul>
        <li>You need a modern browser running version 10 (or higher) of <a target="_blank" href="http://www.adobe.com/software/flash/about/">Adobe Flash.</a></li>
</ul>


<h4>Limits of this FileSender installation</h4>
    <ul>
    <li><strong>
      Maximum recipient  addresses per email:</strong>  <?php echo $config["max_email_recipients"]?> multiple email addresses (separated by comma or semi-colon)</li>
    <li><strong>Maximum number of files per  upload:</strong> one - to upload several files at once, zip them into a  single archive first</li>
    <li><strong>Maximum file size per upload, with Adobe Flash only: </strong> <?php echo formatBytes($config["max_flash_upload_size"])?></li>
    <li><strong>Maximum file size per upload, with HTML5: </strong> <?php echo formatBytes($config["max_gears_upload_size"])?></li>
    <li>      <strong>Maximum  file / voucher expiry days: </strong><?php echo $config["default_daysvalid"]?> </li>
    </ul>
    <p>For more information please visit <a href="http://www.filesender.org/">www.filesender.org</a></p>
  </div>
  <hr />
  </div> <!-- #content -->

</div><!-- #wrap -->

</body>
</html>
