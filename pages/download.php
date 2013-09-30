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
 * My Downloads Page
 * ---------------------------------
 * 
 */


// get file data
if (isset($_REQUEST['vid'])) {
    $vid = $_REQUEST['vid'];
    $fileData = $functions->getVoucherData($vid);
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#message").hide();
    });

    function startDownload() {
        statusMessage('<?php echo lang("_STARTED_DOWNLOADING"); ?>', 'green');
    }

</script>
<div id="box" style="background:#fff">
    <?php echo '<div id="pageheading">' . lang("_DOWNLOAD") . '</div>'; ?>
    <div id="fileinfo">
        <p id="download_filename"><?php echo lang('_FILE_NAME') . ': ' . utf8ToHtml($fileData['fileoriginalname'], true); ?></p>

        <p id="download_filesize"><?php echo lang('_FILE_SIZE') . ': ' . formatBytes($fileData['filesize'], true); ?></p>

        <p id="tracking_code"><?php echo lang('_TRACKING_CODE') . ': ' . htmlentities($fileData['filetrackingcode']); ?></p>

        <p id="download_from"><?php echo lang('_FROM') . ': ' . htmlentities($fileData['filefrom']); ?></p>

        <p id="download_sent"><?php echo lang('_SENT_DATE') . ': ' . date(lang('datedisplayformat'), strtotime($fileData['filecreateddate'])); ?></p>

        <p id="download_expiry"><?php echo lang('_EXPIRY_DATE') . ': ' . date(lang('datedisplayformat'), strtotime($fileData['fileexpirydate'])); ?></p>

        <?php
        if (!empty($fileData[0]['filesubject'])) {
            echo '<p id="download_subject">' . lang('_SUBJECT') . ': ' . utf8ToHtml($fileData['filesubject'], true) . '</p>';
        }

        if (!empty($fileData[0]['filemessage'])) {
            echo '<p id="download_message">' . lang('_MESSAGE') . ': ' . nl2br(utf8ToHtml($fileData['filemessage'], true)) . '</p>';
        }
        ?>
    </div>

    <div class="menu" id="downloadbutton"><p><a id="download"href="download.php?vid=<?php echo urlencode($fileData["filevoucheruid"]); ?>"
            onclick="startDownload()"><?php echo lang("_START_DOWNLOAD"); ?></a></p>
    </div>
</div>
