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

if (isset($_REQUEST['gid']) && ensureSaneOpenSSLKey($_REQUEST['gid'])) {
    $fileData = $functions->getMultiFileData($_REQUEST['gid']);
?>

<script type="text/javascript">
    // From http://stackoverflow.com/a/11752084.
    var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;

    $(document).ready(function () {
        $('#message').hide();
        $('#errmessage').hide();
        $('#myfiles tr:odd').addClass('altcolor');

        if (!isMac) {
            $('#macmessage').hide();
        }

        $('.checkboxes').change(function() {
            // Show or hide the 'no files selected' message depending on current state.
            var numChecked = $('.checkboxes:checked').length;

            showOrHideErrorMessage();

            // Check or un-check the top checkbox depending on how many files are selected.
            if (numChecked == $('.checkboxes').length) {
                $('#selectall').prop('checked', 'checked');
            } else {
                $('#selectall').prop('checked', '');
            }

            showOrHideZipMessages();
        });
    });

    function showOrHideErrorMessage() {
        var numChecked = $('.checkboxes:checked').length;

        if (numChecked == 0) {
            $('#errmessage').show();
        } else {
            $('#errmessage').hide();
        }
    }

    function showOrHideZipMessages() {
        // Show link to Mac alternative unzip utility if more than one file is checked.
        var numChecked = $('.checkboxes:checked').length;

        if (numChecked > 1) {
            if (isMac) {
                $('#macmessage').show();
            }

            $('#zipmessage').show();
        } else {
            $('#macmessage').hide();
            $('#zipmessage').hide();
        }
    }

    function startDownload() {
        if ($('.checkboxes:checked').length > 0) {
            // At least one file is selected, start downloading.
            $('#errmessage').hide();
            $('#message').show();
            $('#fileform').submit();
        } else {
            // No files selected, show error message.
            $('#message').hide();
            $('#errmessage').show();
        }
    }
</script>

<div id='message'><?php echo lang('_STARTED_DOWNLOADING') ?></div>
<div id='errmessage'><?php echo lang('_NO_FILES_SELECTED') ?></div>
<div id="box" style="background:#fff">
    <?php echo '<div id="pageheading">' . lang('_DOWNLOAD') . '</div>' ?>
    <div id="fileinfo">
        <p id="tracking_code"><?php echo lang('_TRACKING_CODE') . ': ' . htmlentities($fileData[0]['filetrackingcode']); ?></p>

        <p id="download_from"><?php echo lang('_FROM') . ': ' . htmlentities($fileData[0]['filefrom']); ?></p>

        <p id="download_sent"><?php echo lang('_SENT_DATE') . ': ' . date(lang('datedisplayformat'), strtotime($fileData[0]['filecreateddate'])); ?></p>

        <p id="download_expiry"><?php echo lang('_EXPIRY_DATE') . ': ' . date(lang('datedisplayformat'), strtotime($fileData[0]['fileexpirydate'])); ?></p>

        <?php
        if (!empty($fileData[0]['filesubject'])) {
            echo '<p id="download_subject">' . lang('_SUBJECT') . ': ' . utf8tohtml($fileData[0]['filesubject'], true) . '</p>';
        }

        if (!empty($fileData[0]['filemessage'])) {
            echo '<p id="download_message">' . lang('_MESSAGE') . ': ' . nl2br(utf8tohtml($fileData[0]['filemessage'], true)) . '</p>';
        }
        ?>

        <form id="fileform" method="post" action="multidownload.php?gid=<?php echo urlencode($_REQUEST['gid'])?>">
            <table id="myfiles" style="table-layout: fixed; border: 0; width: 100%; border-spacing: 0;">
                <tr class="headerrow" >
                    <td class="tblmcw2"><input type="checkbox" checked="checked" style="margin-left: 0; margin-right: 0" id="selectall"
                                          onclick="$('.checkboxes').prop('checked', $('#selectall').prop('checked')); showOrHideErrorMessage(); showOrHideZipMessages();"/></td>
                    <td class="HardBreak" id="myfiles_header_filename" style="vertical-align: middle"><strong><?php echo lang('_FILE_NAME'); ?></strong></td>
                    <td class="HardBreak tblmcw3" id="myfiles_header_size" style="vertical-align: middle"><strong><?php echo lang('_SIZE'); ?></strong></td>
                </tr>
                <?php
                for ($i = 0; $i < sizeof($fileData); $i++) {
                    echo '<tr><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td></tr>';

                    echo
                        '<tr>' .
                        '<td style="text-align: center; vertical-align: middle" class="dr1"><input type="checkbox" checked="checked" class="checkboxes" name="' . $fileData[$i]['filevoucheruid'] . '" style="margin-left: 0; margin-right: 0; width: 11px; height: 11px;" /></td>' .
                        '<td class="dr2 HardBreak"><a id="link_downloadfile_' . $i .'" href="download.php?vid=' .$fileData[$i]['filevoucheruid'] . '">' . utf8tohtml($fileData[$i]['fileoriginalname'], true) . '</a></td>' .
                        '<td class="dr8 HardBreak">' . formatBytes($fileData[$i]['filesize']) . '</td>' .
                        '</tr>';
                }
                echo '<tr><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td></tr>';

                ?>
            </table>
            <input type="hidden" name="isformrequest" value="true" />
        </form>

        <div id="zipmessage">
            <p><?php echo lang('_ZIP_MESSAGE'); ?></p>
        </div>

        <div id="macmessage">
            <p><?php echo lang('_MAC_ZIP_MESSAGE'); ?><a href="<?php echo $config['mac_unzip_link']; ?>"><?php echo $config['mac_unzip_name']; ?></a>.</p>
        </div>
        
        <div class="menu" id="downloadbutton" >
            <p>
                <a id="download" href="" onclick="startDownload(); return false;">
                    <?php echo lang('_DOWNLOAD_SELECTED'); ?>
                </a>
            </p>
        </div>
    </div>
</div>

<?php } ?>
