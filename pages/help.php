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
 * Help Page
 * ---------------------------------
 * 
 */
?>
<div id="box">
<?php
 echo '<div id="pageheading">'._HELP.'</div>'; 
 ?>


  <ul>
    <li>If you don't see your institution in the list of Identity Providers (IdPs), or your institutional login fails, please contact your local IT support</li>
  </ul>
  <h4>Requirements</h4>
  <ul>
    <li>A modern, current release of most popular browsers</li>
  </ul>
  <h4>Limits</h4>
  <ul>
    <li><strong> Maximum recipient  addresses per email:</strong> <?php echo $config["max_email_recipients"]?> multiple email addresses can be  separated by a comma</li>
    <li><strong>Maximum number of files per  upload:</strong> one - to upload several files at once, zip them into a  single archive first</li>
    <li><strong>Maximum file size per upload, without HTML 5: </strong> <?php echo formatBytes($config["max_flash_upload_size"])?></li>
    <li><strong>Maximum file size per upload, with HTML 5: </strong> <?php echo formatBytes($config["max_gears_upload_size"])?></li>
    <li> <strong>Maximum  file / voucher expiry days: </strong><?php echo $config["default_daysvalid"]?> </li>
  </ul>
  <p>For more information please visit <a href="http://www.filesender.org/">www.filesender.org</a></p>
</div>
