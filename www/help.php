<?php

/*
 *  Filsender www.filesender.org
 *      
 *  Copyright (c) 2009-2010, Aarnet, HEAnet, UNINETT
 * 	All rights reserved.
 *
 * 	Redistribution and use in source and binary forms, with or without
 *	modification, are permitted provided that the following conditions are met:
 *	* 	Redistributions of source code must retain the above copyright
 *   		notice, this list of conditions and the following disclaimer.
 *   	* 	Redistributions in binary form must reproduce the above copyright
 *   		notice, this list of conditions and the following disclaimer in the
 *   		documentation and/or other materials provided with the distribution.
 *   	* 	Neither the name of Aarnet, HEAnet and UNINETT nor the
 *   		names of its contributors may be used to endorse or promote products
 *   		derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY Aarnet, HEAnet and UNINETT ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Aarnet, HEAnet or UNINETT BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
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
  <img src="displayimage.php" width="800" height="60" border=0/>
    <p class="style5 style1">Help</p>
  </div>
  <p>

  </p>
  <div align="left">
  <div style="padding:5px">
    <p><strong>Login issues:</strong> 
      If you don’t see your institution in the  list of Identity Providers (IdPs), or your institutional login fails,  please contact your local IT support.</p>
    <p><strong>
      Maximum recipient  addresses per email:</strong>  <?php echo $config["max_email_recipients"]?> multiple email addresses can be  separated by a comma.</p>
    <p>      <strong>Maximum number of files per  upload:</strong> one - to upload several files at once, zip them into a  single archive first</p>
    <p><strong>Maximum file size per upload, without  Gears: </strong> <?php echo formatBytes($config["max_flash_upload_size"])?></p>
    <p><strong>Maximum file size per upload, with Gears: </strong> <?php echo formatBytes($config["max_gears_upload_size"])?> (file uploads over 2 GB currently not possible on Mac OSX 10.6.x)</p>
    <p>      <strong>Maximum  file / voucher expiry days: </strong><?php echo $config["default_daysvalid"]?> </p>
  </div>
  </div>
  <hr />
</div>
<!-- #content -->

</div><!-- #wrap -->

</body>
</html>
