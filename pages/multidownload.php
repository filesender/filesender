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
 * Multiple Downloads page
 * ---------------------------------
 *
 */


if (isset($_REQUEST['gid'])) {
    $fileData = $functions->getMultiFileData($_REQUEST['gid']);
?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#message").hide();
        $("#myfiles tr:odd").addClass("altcolor");
    });

    function startDownload() {
        $("#message").show();
        $("#fileform").submit();
    }
</script>

<div id='message'><?php echo lang("_STARTED_DOWNLOADING") ?></div>
<div id="box" style="background:#fff">
    <?php echo '<div id="pageheading">' . lang("_DOWNLOAD") . '</div>' ?>
    <div id="fileinfo">
        <p id="download_from"><?php echo lang("_FROM") . ": " . htmlentities($fileData[0]["filefrom"]); ?></p>

        <p id="download_sent"><?php echo lang("_SENT_DATE") . ": " . date(lang('datedisplayformat'), strtotime($fileData[0]["filecreateddate"])); ?></p>

        <p id="download_expiry"><?php echo lang("_EXPIRY_DATE") . ": " . date(lang('datedisplayformat'), strtotime($fileData[0]["fileexpirydate"])); ?></p>

        <?php
        if (!empty($fileData[0]["filesubject"])) {
            echo '<p id="download_subject">' . lang("_SUBJECT") . ": " . utf8tohtml($fileData[0]["filesubject"], TRUE) . '</p>';
        }

        if (!empty($fileData[0]["filemessage"])) {
            echo '<p id="download_message">' . lang("_MESSAGE") . ": " . nl2br(utf8tohtml($fileData[0]["filemessage"], TRUE)) . '</p>';
        }
        ?>

        <form id="fileform" method="post" action="multidownload.php?gid=<?php echo urlencode($_REQUEST['gid'])?>">
            <table id="myfiles" width="100%" border="0" cellspacing="0" cellpadding="4" style="table-layout:fixed;">
                <tr class="headerrow" >
                    <td class="tblmcw2"><input type="checkbox" style="margin-left: 0; margin-right: 0" name="selectall"
                                          id="selectall"
                                          onclick="$('.checkboxes').prop('checked', $('#selectall').prop('checked'))"/></td>
                    <td class="HardBreak" id="myfiles_header_filename" style="vertical-align: middle"><strong><?php echo lang("_FILE_NAME"); ?></strong></td>
                    <td class="HardBreak tblmcw3" id="myfiles_header_size" style="vertical-align: middle"><strong><?php echo lang("_SIZE"); ?></strong></td>
                </tr>
                <?php
                for ($i = 0; $i < sizeof($fileData); $i++) {
                    echo '<tr><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td></tr>';

                    echo
                        '<tr>' .
                        '<td style="text-align: center; vertical-align: middle" class="dr1"><input type="checkbox" class="checkboxes" name="' . $fileData[$i]['filevoucheruid'] . '" style="margin-left: 0; margin-right: 0; width: 11px; height: 11px;" /></td>' .
                        '<td class="dr2 HardBreak"><a id="link_downloadfile_' . $i .'" href="download.php?vid=' .$fileData[$i]['filevoucheruid'] . '">' . utf8tohtml($fileData[$i]['fileoriginalname'], TRUE) . '</a></td>' .
                        '<td class="dr8 HardBreak">' . formatBytes($fileData[$i]['filesize']) . '</td>' .
                        '</tr>';
                }
                echo '<tr><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td></tr>';

                ?>
            </table>
            <input type="hidden" name="isformrequest" value="true" />
        </form>

        <div class="menu" id="downloadbutton" >
            <p>
                <a id="download" href="" onclick="startDownload(); return false;">
                    <?php echo lang("_START_DOWNLOAD"); ?>
                </a>
            </p>
        </div>
    </div>
</div>

<?php } ?>
