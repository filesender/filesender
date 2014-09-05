<div>
    <div style="padding:5px; text-align: left;">
        <h4>Login</h4> 
        <ul>
            <li>You log in through one of the listed Identity Providers using your standard institutional account. If you do not see your institution in the list, or your login fails, please contact your local IT support</li>
        </ul>
        
        <h4>Uploads of <i>any size</i> with HTML5</h4>
        <ul>
            <li>You\'ll be able to use this method if this sign is displayed: <img src="res/images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /></li>
            <li>To get the <img src="res/images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /> sign, simply use an up to date browser that supports HTML5, the latest version of the "language of the web".</li>
            <li>Up to date versions of Firefox and Chrome on Windows, Mac OS X and Linux are known to work.</li>
            <li>
                You can <b><i>resume</i></b> an interrupted or cancelled upload.
                To resume an upload, simply send the exact same file again !
                Make sure the file has the same name as before and <i><?php echo htmlspecialchars(Config::get('site_name')) ?></i> will recognise it.
                When your upload starts, you should notice the progress bar jump to where the upload was halted, and continue from there.
                <br /><br />
                If you <b><i>modified the file</i></b> between the first and second attempt, please rename the file first.
                This ensures a new, fresh upload is started and all your changes are properly transferred.
            </li>
        </ul>
        
        <h4>Downloads of any size</h4>
        <ul>
            <li>Any modern browser will do just fine.  Don't worry about Adobe Flash or HTML5 - these only matter for uploads; nothing special is required for downloads</li>
        </ul>
        
        <h4>Uploads smaller than 2 Gigabytes (2GB) with Adobe Flash</h4>
        <ul>
            <li>If you can watch YouTube videos this method should work for you</li>
            <li>You need a modern browser with version 10 (or higher) of the <a target="_blank" href="http://www.adobe.com/software/flash/about/">Adobe Flash</a> plugin.</li>
            <li>Using Adobe Flash you can upload file sizes of up to 2 Gigabytes (2GB).  <i><?php echo htmlspecialchars(Config::get('site_name')) ?></i> will warn you should you try to upload a file that is too big for this method</li>
            <li>Resuming uploads is not supported with this method</li>
        </ul>
        
        
        
        <h4>Configured service constraints</h4>
        <ul>
            <li><strong>Maximum recipient  addresses per email:</strong> Up to <?php echo Config::get('max_email_recipients') ?> email addresses separated by  a comma or semi-colon</li>
            <li><strong>Maximum number of files per  upload:</strong> one - to upload several files in one transaction, compress them into a  single archive first</li>
            <li><strong>Maximum file size per upload, with Adobe Flash only: </strong><?php echo Utilities::formatBytes(Config::get('max_legacy_upload_size')) ?></li>
            <li><strong>Maximum file size per upload, with HTML5: </strong><?php echo Utilities::formatBytes(Config::get('max_html5_upload_size')) ?></li>
            <li><strong>Maximum file / voucher expiry days: </strong><?php echo Config::get('default_daysvalid') ?></li>
        </ul>
        
        <h4>Technical details</h4>
        <ul>
            <li>
                <i><?php echo htmlspecialchars(Config::get('site_name')) ?></i> uses the <a href="http://www.filesender.org/" target="_blank">FileSender software</a>.
                FileSender indicates whether or not the HTML5 upload method is supported for a particular browser.
                This depends mainly on the availability of advanced browser functionality, in particular the HTML5 FileAPI.
                Please use the <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> website to monitor implementation progress of the HTML5 FileAPI for all major browsers.
                In particular support for <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> and <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> needs to be light green (=supported) for a browser to support uploads larger then 2GB.
                Please note that although Opera 12 is listed to support the HTML5 FileAPI, it currently does not support all that is needed to support use of the HTML5 upload method in FileSender.
            </li>
        </ul>
        
        <p>For more information please visit <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
    </div>
</div>
