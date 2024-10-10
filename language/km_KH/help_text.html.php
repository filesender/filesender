<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Login</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>You log in through one of the listed Identity Providers using your standard institutional account. If you do not see your institution in the list, or your login fails, please contact your local IT support</li>
</ul>

<h3>Your browser's features</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled" /> You can upload files of any size up to {size:cfg:max_transfer_size} per transfer.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload disabled" /> You can upload files of at most {size:cfg:max_legacy_file_size} each and up to {size:cfg:max_transfer_size} per transfer.</li>
</ul>

<h3>Uploads of <i>any size</i> with HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>You'll be able to use this method if the <img src="images/html5_installed.png" alt="HTML5 upload enabled" /> sign is displayed above</li>
    <li><i class="fa-li fa fa-caret-right"></i>To enable this functionnality simply use an up to date browser that supports HTML5, the latest version of the "language of the web".</li>
    <li><i class="fa-li fa fa-caret-right"></i>Up to date versions of Firefox and Chrome on Windows, Mac OS X and Linux are known to work.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        You can <strong>resume</strong> an interrupted or cancelled upload. To resume an upload, simply <strong>send the exact same files</strong> again !
        Make sure the files have the <strong>same names and sizes</strong> as before.
        When your upload starts, you should notice the progress bar jump to where the upload was halted, and continue from there.
    </li>
</ul>

<h3>Uploads up to {size:cfg:max_legacy_file_size} per file without HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>{cfg:site_name} will warn you should you try to upload a file that is too big for this method.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Resuming uploads is not supported with this method.</li>
</ul>

<h3>Downloads of any size</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Any modern browser will do just fine, nothing special is required for downloads</li>
</ul>

<h3>Configured service constraints</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximum number of recipients : </strong>{cfg:max_transfer_recipients} email addresses separated by a comma or semi-colon</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximum number of files per transfer : </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximum size per transfer : </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximum file size per file for non-HTML5 browsers : </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Transfer expiry days : </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Guest expiry days : </strong>{cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Technical details</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> uses the <a href="http://www.filesender.org/" target="_blank">FileSender software</a>.
        FileSender indicates whether or not the HTML5 upload method is supported for a particular browser.
        This depends mainly on the availability of advanced browser functionality, in particular the HTML5 FileAPI.
        Please use the <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> website to monitor implementation progress of the HTML5 FileAPI for all major browsers.
        In particular support for <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> and <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> needs to be light green (=supported) for a browser to support uploads larger than {size:cfg:max_legacy_file_size}.
        Please note that although Opera 12 is listed to support the HTML5 FileAPI, it currently does not support all that is needed to support use of the HTML5 upload method in FileSender.
    </li>
</ul>

<p>For more information please visit <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
