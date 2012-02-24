<?php

$lang["_HELP_TEXT"] = '

<div>

<div align="left" style="padding:5px">

<h4>Login</h4> 
<ul>
    <li>If you do not see your institution in the list of Identity Providers (IdPs), or your institutional login fails, please contact your local IT support</li>
</ul>

<h4>Uploads smaller than 2 Gigabytes (2GB) with Adobe Flash</h4>
<ul>
	<li>If you can watch YouTube videos this method should work for you</li>
	<li>You need a modern browser running version 10 (or higher) of <a target="_blank" href="http://www.adobe.com/software/flash/about/">Adobe Flash.</a></li>
	<li>FileSender will warn you should you try to upload a file that is too big for this method</li>
</ul>

<h4>Uploads of <i>any size</i> with HTML5</h4>
<ul>
        <li>If you see <img src="images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /> in FileSender\'s top right-hand corner this method works for you</li>
	<li>You need a very recent browser supporting HTML5, the latest version of the "language of the web".</li>
	<li>Currently Firefox4 (and higher) and Chrome on both Windows, Mac OSX and Linux are known to work.</li>
	<li>Please use the <a href="http://caniuse.com/#feat=fileapi" target="_blank">"When can I use..."</A> website to monitor implementation progress of the HTML5 FileAPI for all major browsers.  In particular support for <a href="http://caniuse.com/#feat=filereader" target="_blank">FileReader API</A> and <A href="http://caniuse.com/#feat=bloburls" target="_blank">Blob URLs</A> needs to be light green (=supported) for a browser to support uploads larger then 2GB </li>
</ul>

<h4>Downloads of any size</h4>
<ul>
        <li>You need a modern browser, Adobe Flash or HTML5 are <b>not</b> required for downloads</li>
</ul>


<h4>Limits of this FileSender installation</h4>
<ul>
    <li><strong>
      Maximum recipient  addresses per email: </strong>'. $config["max_email_recipients"].' multiple email addresses (separated by comma or semi-colon)</li>
    <li><strong>Maximum number of files per  upload:</strong> one - to upload several files at once, zip them into a  single archive first</li>
    <li><strong>Maximum file size per upload, with Adobe Flash only: </strong>'. formatBytes($config["max_flash_upload_size"]).' </li>
    <li><strong>Maximum file size per upload, with HTML5: </strong>'. formatBytes($config["max_html5_upload_size"]).'</li>
    <li><strong>Maximum file / voucher expiry days: </strong>'. $config["default_daysvalid"].' </li>
</ul>
<p>For more information please visit <a href="http://www.filesender.org/">www.filesender.org</a></p>
</div>
</div>';

$lang["_ABOUT_TEXT"] = ' <div align="left" style="padding:5px">'. htmlentities($config['site_name']) .' is an installation of FileSender (<a rel="nofollow" href="http://www.filesender.org/">www.filesender.org</a>), which is developed to the requirements of the higher education and research community.</div>';

$lang["_AUPTERMS"] = "AuP Terms and conditions...";

?>
