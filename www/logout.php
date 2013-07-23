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
 * logout from SAML
 * ---------------------------------
 * 
 */

// set cache to default - nocache
session_cache_limiter('nocache'); 
 
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
<title><?php echo htmlspecialchars($config['site_name']); ?></title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link type="text/css" href="css/smoothness/jquery-ui-1.10.2.custom.min.css" rel="Stylesheet" />
<link rel="stylesheet" type="text/css" href="css/default.css?<?php echo FileSender_Version::VERSION; ?>" />
<script type="text/javascript" src="js/common.js" ></script>
<script type="text/javascript" src="js/jquery-1.9.1.min.js" ></script>
<script type="text/javascript" src="js/jquery-ui-1.10.2.custom.min.js"></script>
<script type="text/javascript">
$(function() {
$( "a", ".menu" ).button();
})
</script>

</head>
<body>

<div id="wrap">
	
  <div id="header">
    <div style="text-align: center">
       <img src="displayimage.php" width="800" height="60" style="border: 0" alt="banner" />
    </div>
  </div>
  <div id="content">
   <div id="box">
    <div style="text-align: center">
	<p><?php echo lang("_LOGOUT_COMPLETE") ?></p>
	<div class="menu" style="text-align: center"><p><a id="btn_logon" href="<?php echo $authsaml->logonURL();?>" ><?php echo lang("_LOGON") ?></a></p></div>
    </div>
   </div>
  </div><!-- #content -->

</div><!-- #wrap -->

</body>
</html>
