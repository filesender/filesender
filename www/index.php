<?php

/*
 *  FileSender www.filesender.org
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


/*
 * loads javascript
 * js/upload.js   manages all html5 related functions and uploading
 */

 if(session_id() == ""){
	// start new session and mark it as valid because the system is a trusted source
	session_start();
	$_SESSION['validSession'] = true;
} 

require_once('../classes/_includes.php');

$flexerrors = "true";
// load config
$authsaml = AuthSaml::getInstance();
$authvoucher = AuthVoucher::getInstance();
$functions = Functions::getInstance();
$CFG = config::getInstance();
$config = $CFG->loadConfig();
$sendmail = Mail::getInstance();
$log = Log::getInstance();

date_default_timezone_set($config['Default_TimeZone']);

$useremail = "";
if($authsaml->isAuth() ) { 
$userdata = $authsaml->sAuth();
$useremail = $userdata["email"];
} 

$s = "";
if(isset($_REQUEST["s"]))
{
$s = $_REQUEST["s"];
}
if(!$authvoucher->aVoucher() && !$authsaml->isAuth() && $s != "complete" && $s != "completev" )
{
$s = "logon";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo $config['site_name']; ?></title>
<link rel="icon" href="favicon.ico"	type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico"type="image/x-icon">
<link type="text/css" href="css/smoothness/jquery-ui-1.8.2.custom.css" rel="Stylesheet" />
<link rel="stylesheet" type="text/css" href="css/default.css" />
<script type="text/javascript" src="js/common.js" ></script>
<script type="text/javascript" src="js/jquery-1.7.min.js" ></script>
<script type="text/javascript" src="js/jquery-ui-1.8.1.custom.min.js"></script>
<script type="text/javascript">
$(function() {
	
	$( "a", ".menu" ).button();
	
	$("#dialog-help").dialog({ autoOpen: false, height: 400,width: 660, modal: true,
		buttons: {
			'helpBTN': function() {
				$( this ).dialog( "close" );
				}
			}
		});
		$('.ui-dialog-buttonpane button:contains(helpBTN)').attr("id","btn_closehelp");            
		$('#btn_closehelp').html('<?php echo lang("_CLOSE") ?>')  
		
		$("#dialog-about").dialog({ autoOpen: false,  height: 400,width: 400, modal: true,
			buttons: {
				'aboutBTN': function() {
					$( this ).dialog( "close" );
				}
			}
		});
		$('.ui-dialog-buttonpane button:contains(aboutBTN)').attr("id","btn_closeabout");            
		$('#btn_closeabout').html('<?php echo lang("_CLOSE") ?>')  
});
	
function openhelp()
	{
		$( "#dialog-help" ).dialog( "open" );
		$('.ui-dialog-buttonpane > button:last').focus();
	}
	
function openabout()
	{
		$( "#dialog-about" ).dialog( "open" );
		$('.ui-dialog-buttonpane > button:last').focus();
	}
</script>
   
<meta name="robots" content="noindex, nofollow" />
</head>
<body>
<div id="wrap">
  <div id="header">
    <div align="center">
      <p><img src="displayimage.php" width="800" height="60" border=0/></p>
      <noscript>
      <p class="style5">JavaScript is turned off in your web browser. <br />
        This application will not run without Javascript enabled in your web browser. </p>
      </noscript>
    </div>
  </div>
  <div id="topmenu">
  <div class="menu" id="menuleft">
      <?php 
  	// create menu
  	// disable all buttons if this is a voucher, even if the user is logged on
 	if (!$authvoucher->aVoucher()  &&  $s != "completev"){
	if($authsaml->isAuth() ) { echo '<a id="topmenu_newupload" href="index.php?s=upload">'.lang("_NEW_UPLOAD").'</a>'; }
	if($authsaml->isAuth() ) { echo '<a id="topmenu_vouchers" href="index.php?s=vouchers">'.lang("_VOUCHERS").'</a>'; }
	if($authsaml->isAuth() ) {echo '<a id="topmenu_myfiles" href="index.php?s=files">'.lang("_MY_FILES").'</a>'; }
	if($authsaml->authIsAdmin() ) { echo '<a id="topmenu_admin" href="index.php?s=admin">'.lang("_ADMIN").'</a>'; }
  }
  ?>
   <div class="menu" id="menuright">
  <?php
	if($config['helpURL'] == "") {
		echo '<a href="#" id="topmenu_help" onclick="openhelp()">'.lang("_HELP").'</a></li>';
	} else {
		echo '<a href="'.$config['helpURL'].'" target="_blank" id="topmenu_help">'.lang("_HELP").'</a></li>';
	}
	if($config['aboutURL'] == "") {
		echo '<a href="#" id="topmenu_about" onclick="openabout()">'.lang("_ABOUT").'</a></li>';
	} else {
		echo '<a href="'.$config['aboutURL'].'" target="_blank" id="topmenu_about">'.lang("_ABOUT").'</a></li>';	
	}
	if(!$authsaml->isAuth() && $s != "logon" ) { echo '<a href="'.$authsaml->logonURL().'" id="topmenu_logon">'.lang("_LOGON").'</a>';}
   	if($authsaml->isAuth() && !$authvoucher->aVoucher() &&  $s != "completev" ) { echo '<a href="'.$authsaml->logoffURL().'" id="topmenu_logoff">'.lang("_LOG_OFF").'</a>'; }
   // end menu
   ?>
   </div>
  </div>
  </div>
  <div id="content">
  <div id="scratch" class="scratch_msg">
	<?php
		if(array_key_exists("scratch", $_SESSION )) {
			echo($functions->getScratchMessage());
			//$functions->clearScratchMessages()
			session_unregister("scratch");
		}
	?>
	<?php    ?>
  </div>	
  <div id="userinformation">
    <?php 
// display user details if authenticated and not a voucher
echo "<div class='welcomeuser'>";
if(	$authvoucher->aVoucher() || $s == "completev") { 
echo lang("_WELCOMEGUEST");
} else if ($authsaml->isAuth() ){
$attributes = $authsaml->sAuth();
echo lang("_WELCOME")." ";
if($config["displayUserName"]) { echo $attributes["cn"];};
}
echo "</div>";

$versiondisplay = "";
if($config["site_showStats"])
{
	$versiondisplay .= $functions->getStats();
}
if($config["versionNumber"])
{
	$versiondisplay .= FileSender_Version::VERSION;
}
echo "<div class='versionnumber'>" .$versiondisplay."</div>";

?>
</div>
    <?php
if(	$authvoucher->aVoucher())
{
// check if it is Available or a Voucher for Uploading a New File
$voucherData = $authvoucher->getVoucher();

if($voucherData[0]["filestatus"] == "Voucher")
{ // load voucher upload
require_once('../pages/upload.php');
} else if($voucherData[0]["filestatus"] == "Available")
{ 
// allow download of voucher
require_once('../pages/download.php');
} else if($voucherData[0]["filestatus"] == "Closed")
{
require_once('../pages/vouchercancelled.php');
}
 else if($voucherData[0]["filestatus"] == "Voucher Cancelled")
{
require_once('../pages/vouchercancelled.php');
}
} else if($s == "upload") 
{
require_once('../pages/upload.php');
} else if($s == "vouchers" && !$authvoucher->aVoucher()) 
{
require_once('../pages/vouchers.php');
} else if($s == "files" && !$authvoucher->aVoucher()) 
{
require_once('../pages/files.php');
} else if($s == "logon") 
{
require_once('../pages/logon.php');
}	
else if($s == "admin" && !$authvoucher->aVoucher()) 
{
require_once('../pages/admin.php');
}
else if($s == "uploaderror") 
{
require_once('../pages/uploaderror.php');
}	
else if($s == "complete" || $s == "completev") 
{
require_once('../pages/uploadcomplete.php');
} else if ($s == "" && $authsaml->isAuth()){
require_once('../pages/upload.php');	
}else if ($s == "" ){
require_once('../pages/home.php');	
}
?>
  </div>
</div>
<div id="dialog-help" title="<?php echo lang("_HELP"); ?>">
 <?php echo lang("_HELP_TEXT"); ?>
</div>
<div id="dialog-about" title="<?php echo lang("_ABOUT"); ?>">
 <?php echo lang("_ABOUT_TEXT"); ?>
</div>
<div id="footer">Version <?php echo FileSender_Version::VERSION; ?></div>
<div id="DoneLoading"></div>
</body>
</html>
