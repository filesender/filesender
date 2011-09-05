<?php

$lang["_HELP_TEXT"] = '<div align="left" style="padding:5px">
    <p> If you dont see your institution in the list of Identity Providers (IdPs), or your institutional login fails, please contact your local IT support </p>
    <p> <strong>Requirements</strong><br>
      A modern, current release of most popular browsers </p>
    <p> <strong>Limits</strong><br>
      <strong> Maximum recipient  addresses per email:</strong> '. $config["max_email_recipients"].' multiple email addresses can be  separated by a comma<br>
      <strong>Maximum number of files per  upload:</strong> one - to upload several files at once, zip them into a  single archive first<br>
      <strong>Maximum file size per upload, without HTML 5: </strong>'. formatBytes($config["max_flash_upload_size"]) .'<br>
      <strong>Maximum file size per upload, with HTML 5: </strong>' .formatBytes($config["max_gears_upload_size"]).'<br>
      <strong>Maximum  file / voucher expiry days: </strong>'. $config["default_daysvalid"].'<br>
    </p>
    <p>For more information please visit <a href="http://www.filesender.org/">www.filesender.org</a></p>
  </div>';

$lang["_ABOUT_TEXT"] = ' <div align="left" style="padding:5px">'. htmlentities($config['site_name']) .' is an installation of FileSender (<a rel="nofollow" href="http://www.filesender.org/">www.filesender.org</a>), which is developed to the requirements of the higher education and research community.</div>';

$lang["_AUPTERMS"] = "AuP Terms and conditions...";

?>