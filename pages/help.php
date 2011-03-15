<?php echo '<div id="pageheading">'._HELP.'</div>'; ?> 
    <ul>
    <li>If you don't see your institution in the list of Identity Providers (IdPs), or your institutional login fails, please contact your local IT support</li>
    </ul>

<h4>Requirements</h4>
<ul>
<li>A modern, current release of most popular browsers</li>
</ul>

<h4>Limits</h4>
    <ul>
    <li><strong>
      Maximum recipient  addresses per email:</strong>  <?php echo $config["max_email_recipients"]?> multiple email addresses can be  separated by a comma</li>
    <li><strong>Maximum number of files per  upload:</strong> one - to upload several files at once, zip them into a  single archive first</li>
    <li><strong>Maximum file size per upload, without  Gears: </strong> <?php echo formatBytes($config["max_flash_upload_size"])?></li>
    <li><strong>Maximum file size per upload, with Gears: </strong> <?php echo formatBytes($config["max_gears_upload_size"])?></li>
    <li>      <strong>Maximum  file / voucher expiry days: </strong><?php echo $config["default_daysvalid"]?> </li>
    </ul>
    <p>For more information please visit <a href="http://www.filesender.org/">www.filesender.org</a></p>

  <hr />