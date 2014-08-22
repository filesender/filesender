<div class="box">
    <h1>{tr:transfers_page}</h1>
    
    <table class="transfers">
        <tr>
            <th class="expand" title="{tr:expand_all}">
                <span class="clickable fa fa-plus-circle fa-lg"></span>
            </th>
            
            <th class="recipients">
                {tr:recipients}
            </th>
            
            <th class="size">
                {tr:size}
            </th>
            
            <th class="files">
                {tr:files}
            </th>
            
            <th class="downloads">
                {tr:downloads}
            </th>
            
            <th class="expires">
                {tr:expires}
            </th>
            
            <th class="actions">
                {tr:actions}
            </th>
        </tr>
        
        <?php foreach(Transfer::fromUser(Auth::user()) as $transfer) { ?>
        <tr class="transfer" data-id="<?php echo $transfer->id ?>">
            <td class="expand" title="{tr:show_details}">
                <span class="clickable fa fa-plus-circle fa-lg"></span>
            </td>
            
            <td class="recipients">
                <?php
                echo implode('<br />', array_map(function($recipient) {
                    return $recipient->email;
                }, array_slice($transfer->recipients, 0, 3)));
                
                if(count($transfer->recipients) > 3)
                    echo '<br />(<span class="clickable expand">'.Lang::tr('more', array('n' => count($transfer->recipients) - 3)).'</span>)';
                ?>
            </td>
            
            <td class="size">
                <?php echo Utilities::formatBytes($transfer->size) ?>
            </td>
            
            <td class="files">
                <?php
                echo implode('<br />', array_map(function($file) {
                    return $file->name;
                }, array_slice($transfer->files, 0, 3)));
                
                if(count($transfer->files) > 3)
                    echo '<br />(<span class="clickable expand">'.Lang::tr('more', array('n' => count($transfer->recipients) - 3)).'</span>)';
                ?>
            </td>
            
            <td class="downloads">
                TODO (<span class="clickable expand">{tr:see_all}</span>)
            </td>
            
            <td class="expires">
                <?php echo Utilities::formatDate($transfer->expires) ?>
            </td>
            
            <td class="actions"></td>
        </tr>
        
        <tr class="transfer_details" data-id="<?php echo $transfer->id ?>">
            <td colspan="5">
                <div class="general">
                    {tr:created} : <?php echo Utilities::formatDate($transfer->created) ?><br />
                    {tr:expires} : <?php echo Utilities::formatDate($transfer->expires) ?><br />
                    {tr:size} : <?php echo Utilities::formatBytes($transfer->size) ?><br />
                    {tr:with_identity} : <?php echo $transfer->user_email ?>
                </div>
                
                <div class="recipients">
                    <h2>{tr:recipients}</h2>
                    
                    <?php foreach($transfer->recipients as $recipient) { ?>
                        <div class="recipient" data-id="<?php echo $recipient->id ?>">
                            <?php echo $recipient->email ?> : <!--count($transfer->auditLogs('download', $recipient)--> 0 {tr:downloads}
                        </div>
                    <?php } ?>
                </div>
                
                <div class="files">
                    <h2>{tr:files}</h2>
                    
                    <?php foreach($transfer->files as $file) { ?>
                        <div class="file" data-id="<?php echo $file->id ?>">
                            <?php echo $file->name ?> (<?php echo Utilities::formatBytes($file->size) ?>) : <!--count($transfer->auditLogs('download', $file)--> 0 {tr:downloads}
                        </div>
                    <?php } ?>
                </div>
                
                <div class="auditlog">
                    <h2>{tr:auditlog}</h2>
                    TODO
                </div>
            </td>
        </tr>
        <?php } ?>
<?php
$i = 0;

if(false && sizeof($transactions) > 0) {
    foreach($transactions as $item) {
        $i += 1; // Counter for file IDs.

        $transactionContents = $functions->getTransactionDetails($item['filetrackingcode'], $item['fileauthuseruid']);
        $contentsTableData = array();
        $recipients = array();
        $totalNumDownloads = 0;
        $totalSize = 0;

        foreach ($transactionContents as $fileRow) {
            $fileName = $fileRow['fileoriginalname'];
            $fileDownloads = $fileRow['filenumdownloads'];
            $totalNumDownloads += $fileDownloads;

            if (isset($contentsTableData[$fileName])) {
                $contentsTableData[$fileName]['filenumdownloads'] += $fileDownloads;
            } else {
                $contentsTableData[$fileName] = array(
                    'filesize' => $fileRow['filesize'],
                    'filenumdownloads' => $fileDownloads
                );

                $totalSize += $fileRow['filesize'];
            }

            $rec = $fileRow['fileto'];

            if (isset($recipients[$rec])) {
                $recipients[$rec]['files'][$fileName] = $fileDownloads;
                $recipients[$rec]['downloads'] += $fileDownloads;
            } else {
                $recipients[$rec] = array(
                    'downloads' => $fileDownloads,
                    'gid' => $fileRow['filegroupid'],
                    'files' => array($fileName => $fileDownloads)
                );
            }
        }

        $recipientEmails = array_keys($recipients);
        $recipientsString = implode(', ', $recipientEmails);
        $numRecipients = sizeof($recipientEmails);
        $fileToString = $recipientEmails[0];

        if ($numRecipients > 1) {
            $fileToString .= ' ... (' . ($numRecipients - 1) . ')';
        }

        $rowClass = ($i % 2 != 0)? 'class="altcolor"' : ''; // Alternating rows.

        if (!isset($transactionContents[0])) continue;
        
        $hasMessage = $transactionContents[0]['filemessage'] != "";
        $hasSubject = $transactionContents[0]['filesubject'] != "";
        
        // Print the top level of the expandable row for the transaction.
        ?>
      <tr>
        <td class="dr7" colspan="10">
      </tr>
      
      <tr <?php echo $rowClass ?>>
        <td class="dr1 expct" onclick="toggleDisplayRow(<?php echo $i ?>)">
          <i id="showicon_'.$i.'" style="cursor:pointer;" class="fa fa-plus-circle fa-lg" title="<?php echo Lang::tr('_SHOW_ALL') ?>"></i>
        </td>
        <td colspan="4" class="dr2 HardBreak" title="<?php echo $recipientsString ?>"><?php echo $fileToString ?></td>
        <td class="dr2 HardBreak" style="text-align: center"><?php echo formatBytes($totalSize) ?></td>
        <td class="dr2 HardBreak" style="text-align: center"><?php echo $totalNumDownloads ?></td>
        <td class="dr2" style="text-align: center">
          <?php echo date(Lang::tr('datedisplayformat'),strtotime($transactionContents[0]['fileexpirydate'])) ?>
        </td>
        <td class="dr2" style="text-align: center"><?php echo utf8ToHtml($transactionContents[0]['filetrackingcode'], true) ?></td>
        <td class="dr8">
          <i style="cursor:pointer; color: #ff0000;" title="<?php echo Lang::tr('_DELETE_TRANSACTION') ?>" onclick="confirmDeleteTransaction('<?php echo $transactionContents[0]['filetrackingcode'] ?>', '<?php echo $item['fileauthuseruid'] ?>')" style="cursor:pointer;" class="fa fa-minus-circle fa-lg" title="<?php echo Lang::tr('_SHOW_ALL') ?>"></i>
        </td>
      </tr>
      
      <tr class="hidden" style="display:none" id="show_<?php echo $i ?>">
        <td class="dr4"></td>
        <td colspan="8">';
          <table style="width: 100%; padding: 0; border-collapse: collapse; border: 0;" class="rowdetails">
            <tr>
              <td colspan="2" class="dr9 headerrow"><?php echo Lang::tr('_DETAILS') ?></td>
            </tr>
            
            <tr class="rowdivider">
              <td class="dr4 sdheading tblmcw3 "><strong><?php echo Lang::tr('_CREATED') ?></strong></td>
              <td class="dr6 HardBreak"><?php echo date(Lang::tr('datedisplayformat'), strtotime($transactionContents[0]['filecreateddate'])) ?></td>
            </tr>
            
            <tr>
              <td class="dr4 sdheading"><strong><?php echo Lang::tr('_FROM') ?></strong></td>
              <td class="dr6 HardBreak"><?php echo $transactionContents[0]['filefrom'] ?></td>
            </tr>
            
            <tr class="rowdivider">
              <?php if ($hasMessage || $hasSubject) { ?>
              <td class="dr4 sdheading tblmcw3"><strong><?php echo Lang::tr('_TO') ?></strong></td>
              <td class="dr6 HardBreak"><?php echo $recipientsString ?></td>
              <?php } else { ?>
              <td class="dr11 sdheading tblmcw3"><strong><?php echo Lang::tr('_TO') ?></strong></td>
              <td class="dr13"><?php echo $recipientsString ?></td>
              <?php } ?>
            </tr>
            
            <?php if ($transactionContents[0]['filesubject'] != '') { ?>
            <tr>
              <?php if ($hasMessage) { ?>
              <td class="dr4 sdheading tblmcw3"><strong><?php echo Lang::tr('_SUBJECT') ?></strong></td>
              <td class="dr6 HardBreak"><?php echo utf8ToHtml($transactionContents[0]['filesubject'], true) ?></td>
              <?php } else { ?>
              <td class="dr11 sdheading tblmcw3"><strong><?php echo Lang::tr('_SUBJECT') ?></strong></td>
              <td class="dr13 HardBreak"><?php echo utf8ToHtml($transactionContents[0]['filesubject'], true) ?></td>
              <?php } ?>
            </tr>
            <?php } ?>
            
            <?php if ($transactionContents[0]['filemessage'] != '') { ?>
            <tr class="<?php echo $transactionContents[0]['filesubject'] != '' ? 'rowdivider' : '' ?>">
              <td class="dr11 sdheading"><strong><?php echo Lang::tr('_MESSAGE') ?></strong></td>
              <td class="dr13" >
                <pre class="HardBreak"><?php echo utf8ToHtml($transactionContents[0]['filemessage'], true) ?></pre>
              </td>
            </tr>
            <?php } ?>
          </table>
          <br />
          
          <table style="width: 100%; padding: 0; border-collapse: collapse; border: 0;">
            <tr>
              <td colspan="3" class="dr9 headerrow"><?php echo Lang::tr('_CONTENTS') ?></td>
            </tr>
            
            <tr>
              <td class="dr4 HardBreak" style="width: 66%; text-align: left"><strong><?php echo Lang::tr('_FILE_NAME') ?></strong></td>
              <td class="HardBreak" style="width: 17%; text-align: center"><strong><?php echo Lang::tr('_FILE_SIZE') ?></strong></td>
              <td class="dr6 HardBreak" style="width: 17%; text-align: center"><strong><?php echo Lang::tr('_DOWNLOADS') ?></strong></td>
            </tr>
            
            <?php
            $rowCount = 0;
            foreach ($contentsTableData as $fileName => $data) {
                // Print file name, file size, number of downloads.
                $fileName = utf8ToHtml($fileName, true);
                $fileSize = formatBytes($data['filesize']);
                echo ($rowCount % 2 == 0) ? '<tr class="rowdivider">' : '<tr>';
                
                if ($rowCount == sizeof($contentsTableData) - 1) {
                    // Different border styling for the last row of the table.
                    echo '<td class="dr11">' . $fileName . '</td>
                          <td class="dr12 HardBreak" style="text-align: center">' . $fileSize . '</td>
                          <td class="dr13 HardBreak" style="text-align: center">' . $data['filenumdownloads'] . '</td>';
                } else {
                    echo '<td class="dr4">' . $fileName . '</td>
                          <td class="HardBreak" style="text-align: center">' . $fileSize . '</td>
                          <td class="dr6 HardBreak" style="text-align: center">' . $data['filenumdownloads'] . '</td>';
                }
                
                echo '</tr>';
                $rowCount++;
            }
            ?>
          </table>
          <br />
          
          <table style="width: 100%; border-spacing: 0; border: 0">
            <tr>
              <td colspan="5" class="dr9 headerrow"><?php echo Lang::tr('_RECIPIENTS') ?></td>
            </tr>
            
            <tr>
              <td class="dr4 tblmcw1" onclick="expandRecipients('<?php echo $i ?>', '<?php echo sizeOf($recipientEmails) ?>')" style="cursor:pointer; width:5%;">
                <i id="showicon_<?php echo $i ?>_recipients" style="cursor:pointer;" class="fa fa-plus-circle fa-lg"></i>
              </td>
              <td class="HardBreak" style="text-align: left; width:61%;" ><strong><?php echo Lang::tr('_EMAIL') ?></strong></td>
              <td class="HardBreak tblmcw3" style="text-align: center; width:24%"><strong><?php echo Lang::tr('_DOWNLOADS') ?></strong></td>
              <td class="tbl1mcw1" style="cursor:pointer; width:5%;">&nbsp;</td>
              <td class="tbl1mcw1 dr6" style="cursor:pointer; width:5%;">&nbsp;</td>
            </tr>
            
            <?php
            $rowCount = 0;
            foreach ($recipients as $rec => $rowData) {
                // Display email address, number of downloads, buttons for expanding / re-sending / deleting.
                echo ($rowCount % 2 == 0) ? '<tr class="rowdivider">' : '<tr>';
                $id = $i . '_' . $rowCount . '_recipients';
                
                echo '<td  class="dr4 expct" style="width: 5%;" onclick="toggleDisplayRow(&quot;' . $id . '&quot;)">
                        <i id="showicon_'. $id . '" style="cursor:pointer;" class="fa fa-plus-circle fa-lg"></i>
                      </td>
                      <td class="HardBreak" style="text-align: left;">' . $rec . '</td>
                      <td class="HardBreak" style="text-align: center;">' . $rowData['downloads'] . '</td>
                      <td class="tblmcw1" style="cursor:pointer; width:5%;">
                        <i onclick="confirmResend(&quot;' . $rowData['gid'] . '&quot;)"  style="cursor:pointer;" class="fa fa-mail-forward fa-lg" alt="" title="' . Lang::tr("_RE_SEND_EMAIL") . '"></i>
                      </td>
                      <td class="tblmcw1 dr6" style="cursor:pointer; width:5%;">
                        <i style="cursor:pointer; color: #ff0000;" title="'. Lang::tr("_DELETE_TRANSACTION") . '" onclick="confirmDeleteRecipient(&quot;' . $rowData['gid'] . '&quot;)" style="cursor:pointer;" class="fa fa-minus-circle fa-lg" title="'. Lang::tr("_DELETE_RECIPIENT").'"></i>
                      </td>
                    </tr>';
                
                // Print sub-table (download statistics for each recipient).
                echo '<tr class="hidden" style="display:none" id="show_'. $id .'">
                        <td class="dr4"></td>
                        <td class="" colspan="3" style="">
                          <table style="width: 100%; border-collapse: collapse; border: 0;padding: 10px;">
                            <tr>
                              <td class="dr9 headerrow" colspan="2">' . Lang::tr('_DOWNLOAD_STATISTICS') . '</td>
                            </tr>';
                
                $row = 0;
                foreach ($rowData['files'] as $name => $downloads) {
                    // Display file name and number of downloads for each file.
                    echo ($row % 2 == 0) ? '<tr class="rowdivider">' : '<tr>';
                    echo '<td class="HardBreak dr4" >' . utf8ToHtml($name, true) . '</td>
                            <td class="HardBreak dr6" style="text-align: center">' . $downloads . '</td>
                          </tr>';
                    
                    $row++;
                }
                
                echo '<tr>
                        <td class="dr7" colspan="2"></td>
                      </tr>
                    </table>
                  </td>
                  <td class="dr6"></td>
                </tr>'; // End of recipient row.
                
                $rowCount++;
            }
            ?>
            
            <tr>
              <td colspan="5" style="text-align: center; border: 1px solid #999;">
                <a target="_blank" style="cursor:pointer;"
                  onclick="openAddRecipient('<?php echo $transactionContents[0]['fileauthuseruid'] ?>','<?php echo rawurlencode($transactionContents[0]['filefrom']) ?>','<?php echo rawurlencode($transactionContents[0]['filesubject']) ?>','<?php echo rawurlencode($transactionContents[0]['filemessage']) ?>','<?php echo $item['filetrackingcode'] ?>')"
                >
                  Click here to Add a new Recipient
                </a>
              </td>
            </tr>
          </table>
          <br />
          
        </td>
        
        <td class="dr6"></td>
      </tr><!-- End of hidden div -->
      <?php } ?>
      
      <tr>
        <td class="dr7" colspan="10">
      </tr>
    <?php } ?>
    </table>
    
    <?php //if($i == 0) echo Lang::tr('_NO_FILES') ?>
    
  </div>
</div>

<div id="dialog-addrecipient" style="display:none" title="<?php echo Lang::tr('_NEW_RECIPIENT'); ?>">
  <form id="form1" name="form1" enctype="multipart/form-data" method="post" action="#">
    <input type="hidden" name="a" value="add" />
    <input id="trackingCode" type="hidden" name="tc" value="" />
    <table  style="width: 600px; border: 0">
      <tr>
        <td class="formfieldheading mandatory tblmcw3" id="files_to"><?php echo Lang::tr('_TO'); ?>:</td>
        <td style="text-align: center">
          <div id="recipients_box" style="display: none"></div>
          <input name="fileto" title="<?php echo  Lang::tr('_EMAIL_SEPARATOR_MSG'); ?>" type="text" id="fileto" size="60" onblur="addEmailRecipientBox($('#fileto').val());" />
          <div id="fileto_msg" style="display: none" class="validation_msg"><?php echo Lang::tr('_INVALID_MISSING_EMAIL'); ?></div>
          <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo Lang::tr('_MAXEMAILS'); ?> <?php echo Config::get('max_email_recipients') ?>.</div>
        </td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory" id="files_from"><?php echo Lang::tr('_FROM'); ?>:</td>
        <td>
          <div id="filefrom"></div>
        </td>
      </tr>
      <tr>
        <td class="formfieldheading" id="files_subject"><?php echo Lang::tr('_SUBJECT'); ?>: (<?php echo Lang::tr("_OPTIONAL"); ?>)</td>
        <td><input name="filesubject" type="text" id="filesubject" size="60" /></td>
      </tr>
      <tr>
        <td class="formfieldheading" id="files_message"><?php echo Lang::tr('_MESSAGE'); ?>: (<?php echo Lang::tr("_OPTIONAL"); ?>)</td>
        <td><textarea name="filemessage" cols="57" rows="4" id="filemessage"></textarea></td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory" id="files_expiry">
          <?php echo Lang::tr('_EXPIRY_DATE'); ?>: <input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date(Lang::tr('datedisplayformat'), strtotime("+".Config::get('default_daysvalid')." day"));?>" />
        </td>
        <td>
          <input id="datepicker" name="datepicker" onchange="validate_expiry()" title="<?php echo Lang::tr('_DP_dateFormat'); ?>" />
          <div id="expiry_msg" class="validation_msg" style="display: none"><?php echo Lang::tr('_INVALID_EXPIRY_DATE'); ?></div>
        </td>
      </tr>
      <tr>
        <td class="formfieldheading mandatory"></td>
        <td>
          <div id="file_msg" class="validation_msg" style="display: none"><?php echo Lang::tr('_INVALID_FILE'); ?></div>
          <div id="extension_msg" class="validation_msg" style="display: none"><?php echo Lang::tr('_INVALID_FILE_EXT'); ?></div>
        </td>
      </tr>
    </table>
    <input name="filevoucheruid" type="hidden" id="filevoucheruid" /><br />
    <input type="hidden" name="s-token" id="s-token" value="<?php echo (isset($_SESSION["s-token"])) ?  $_SESSION["s-token"] : "";?>" />
  </form>
    
        
    <script type="text/javascript" src="{path:res/js/transfers.js}"></script>
</div>
