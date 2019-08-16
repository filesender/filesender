<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Welcome to FileSender</h3>

<p>
    FileSender is a web based application that allows authenticated
    users to securely and easily send arbitrarily large files to other
    users. Users without an account can be sent a guest upload voucher by an
    authenticated user. FileSender is developed to the requirements of
    the higher education and research community.
</p>

<h4>For guests...</h4>

<p>
    If you have been sent a guest voucher from this site you have been
    invited to upload files one or more times. The simplest way to do
    that is using the information contained in the invitation email.
    When uploading as guest, be sure to verify that any links in the
    guest invitation email are to a FileSender that is running on a
    research facility you trust. If you are not expecting a guest link
    from a facility you know then the email might not be legitimate.
</p>
<p>
    The user who invited you to this system might have done so
    allowing you to upload files and get a link to allow other folks
    to download those files. If you can not get a link then you will
    have to provide email address(es) of the people who you wish to
    invite to download the uploaded files. 
</p>

<h4>For authenticated users...</h4>

<p>
    If this installation of FileSender is on your research facility
    the login button on the top right of the page should let you login
    using standard institutional account. If you are unsure
    about what login credentials to use to access this FileSender then
    please contact your local IT support.
</p>

<p>
    As an authenticated user you should be able to upload files one or
    more times and either have FileSender email the recipients after
    your upload completes or provide you with a link to allow file
    download. You should also be able to invite other researchers to
    the system to upload one or more files as a guest. 
</p>

<h3>Possible Download Size Limitations</h3>
<p>
    Any modern browser will download files of any size from the site.
    Nothing special is required for downloads.
</p>

<h3>Possible Upload Size Limitations</h3>

<p>
    If your browser supports HTML5 then you should be able to upload
    files of any size up to {size:cfg:max_transfer_size}. Current versions of Firefox and Chrome on
    Windows, Mac OS and Linux are known to have HTML5 support.
</p>

<h3>Your browser's features</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled" /> You can upload files of any size up to {size:cfg:max_transfer_size} per transfer and you can resume uploads.</li>
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
    <li><i class="fa-li fa fa-caret-right"></i>FileSender will warn you should you try to upload a file that is too big for this method.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Resuming uploads is not supported with this method.</li>
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
        <strong>{cfg:site_name}</strong> uses the <a href="http://filesender.org/" target="_blank">FileSender software</a>.
        FileSender indicates whether or not the HTML5 upload method is supported for a particular browser.
        This depends mainly on the availability of advanced browser functionality, in particular the HTML5 FileAPI.
        Please use the <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> website to monitor implementation progress of the HTML5 FileAPI for all major browsers.
        In particular support for <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> and <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> needs to be light green (=supported) for a browser to support uploads larger than {size:cfg:max_legacy_file_size}.
        Please note that although Opera 12 is listed to support the HTML5 FileAPI, it currently does not support all that is needed to support use of the HTML5 upload method in FileSender.
    </li>
</ul>

<p>For more information please visit <a href="http://filesender.org/" target="_blank">filesender.org</a></p>
