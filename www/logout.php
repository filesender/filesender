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
 * logout from SAML
 * ---------------------------------
 * 
 */
require_once('../classes/_includes.php');
$authsaml = AuthSaml::getInstance();
// force all session variable 
session_start();
session_unset();
session_destroy();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>FileSender:</title>
<link rel="stylesheet" type="text/css" href="css/default.css" />
<link rel="shortcut icon" href="favicon.ico"type="image/x-icon">
<link type="text/css" href="css/smoothness/jquery-ui-1.8.2.custom.css" rel="Stylesheet" />
<script type="text/javascript" src="js/common.js" ></script>
<script type="text/javascript" src="js/jquery-1.5.2.min.js" ></script>
<script type="text/javascript" src="js/jquery-ui-1.8.1.custom.min.js"></script>
<script>
$(function() {
$( "a", ".menu" ).button();
})
</script>
<style type="text/css">
body { margin: 0px; overflow:hidden }
.style5 {
	color: #FFFFFF;
	font-weight: bold;
}
</style>


</head>
<body scroll="no">

<div id="wrap">
	
  <div id="header">
   <img src="displayimage.php" width="800" height="60" border="0" alt="banner"/>
    <p class="style5 style1"></p>
  </div>
  <p>

  </p>
  <div id="content">
    <div align="center">
	<p><?php echo lang("_LOGOUT_COMPLETE") ?></p>
	<p><div class="menu" align="center"><a href="<?php echo $authsaml->logonURL();?>" ><?php echo lang("_LOGON") ?></a></div></p>
    </div>
    <hr />
  </div><!-- #content -->

</div><!-- #wrap -->

</body>
</html>
