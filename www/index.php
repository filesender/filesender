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


/*
 * loads javascript
 * js/gears_init.js  initialises google gears if gears is loaded
 * js/fs_gears.js   manages all gears related functions and google gears uploading
 * js/jquery-1.2.6.min.js  loaded in preparation for HTML 5 UI
 */
 
require_once('../classes/_includes.php');
$flexerrors = "true";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>FileSender:</title>
<link rel="stylesheet" type="text/css" href="css/default.css" />
<link rel="stylesheet" type="text/css" href="lib/history/history.css" />
<link rel="icon" href="favicon.ico"
	type="image/x-icon">

<link rel="shortcut icon" href="favicon.ico"
	type="image/x-icon">
<script type="text/javascript" src="js/gears_init.js" ></script>
<script type="text/javascript" src="js/fs_gears.js" ></script>
<script type="text/javascript" src="js/jquery-1.2.6.min.js" ></script>
	<meta name="robots" content="noindex, nofollow" />
	<script src="lib/js/AC_OETags.js" language="javascript"></script>

<!--  BEGIN Browser History required section -->
<script src="lib/history/history.js" language="javascript"></script>
<!--  END Browser History required section -->
<script type="text/javascript">

window.onload = function() {
//getFlexApp('filesender').returnStatus('error');
//  if (!window.google || !google.gears) {
//   addStatus('Gears is not installed', 'error');
      
//    return;
//  }
}
</script>
<style>
body { margin: 0px; }
.style5 {color: #FFFFFF}
</style>
<script language="JavaScript" type="text/javascript">
<!--
// -----------------------------------------------------------------------------
// Globals
// Major version of Flash required
var requiredMajorVersion = 10;
// Minor version of Flash required
var requiredMinorVersion = 0;
// Minor version of Flash required
var requiredRevision = 0;
// -----------------------------------------------------------------------------
// -->
</script>

</head>
<body>

<div id="wrap">
	
  <div id="header">
  <img src="displayimage.php" width="800" height="60" border=0/>
  </div>
  <script language="JavaScript" type="text/javascript">
<!--
// Version check for the Flash Player that has the ability to start Player Product Install (6.0r65)
var hasProductInstall = DetectFlashVer(6, 0, 65);

// Version check based upon the values defined in globals
var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);

if ( hasProductInstall && !hasRequestedVersion ) {
	// DO NOT MODIFY THE FOLLOWING FOUR LINES
	// Location visited after installation is complete if installation is required
	var MMPlayerType = (isIE == true) ? "ActiveX" : "PlugIn";
	var MMredirectURL = window.location;
    document.title = document.title.slice(0, 47) + " - Flash Player Installation";
    var MMdoctitle = document.title;

	AC_FL_RunContent(
		"src", "lib/swf/playerProductInstall",
		"FlashVars", "flexerrors=<?php echo $flexerrors ?>&MMredirectURL="+MMredirectURL+'&MMplayerType='+MMPlayerType+'&MMdoctitle='+MMdoctitle+"",
		"width", "800",
		"height", "561",
		"align", "middle",
		"id", "filesender",
		"quality", "high",
		"bgcolor", "#869ca7",
		"name", "filesender",
		"allowScriptAccess","sameDomain",
		"type", "application/x-shockwave-flash",
		"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
} else if (hasRequestedVersion) {
	// if we've detected an acceptable version
	// embed the Flash Content SWF when all tests are passed
	AC_FL_RunContent(
			"src", "swf/filesender",
			"FlashVars", "flexerrors=<?php echo $flexerrors ?>",
			"width", "800",
			"height", "561",
			"align", "middle",
			"id", "filesender",
			"quality", "high",
			"bgcolor", "#869ca7",
			"name", "filesender",
			"allowScriptAccess","sameDomain",
			"type", "application/x-shockwave-flash",
			"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
  } else {  // flash is too old or we can't detect the plugin
    var alternateContent = '<div id="header"><h1>Install Flash Player<h1></div><BR><div align="center">This application requires Flash Player.<BR><BR>'
  	+ 'To install Flash Player go to Adobe.com.<br> '
   	+ '<a href=http://www.adobe.com/go/getflash/>Get Flash</a></div>';
    document.write(alternateContent);  // insert non-flash content
  }
// -->
</script>
    <noscript>
      <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="800" height="561"
			codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
        <param name="movie" value="swf/filesender.swf?flexerrors=<?php echo $flexerrors ?>" />
        <param name="quality" value="high" />
        <param name="bgcolor" value="#869ca7" />
        <param name="allowScriptAccess" value="sameDomain" />
        <embed src="swf/filesender.swf?flexerrors=<?php echo $flexerrors ?>" quality="high" bgcolor="#869ca7"
				width="800" height="561" name="filesender" align="middle"
				play="true"
				loop="false"
				quality="high"
				allowScriptAccess="sameDomain"
				type="application/x-shockwave-flash"
				pluginspage="http://www.adobe.com/go/getflashplayer">
        </embed>
    </object>
    </noscript>
</div>
  <noscript>
</noscript>
  <p>
  </div>
</p>
  <div id="DoneLoading">
</div>
</body>
</html>
