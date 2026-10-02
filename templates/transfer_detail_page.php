<?php
if(!isset($mode)) $mode = 'user';
$show_guest = isset($show_guest) ? (bool)$show_guest : false;
$extend = (bool)Config::get('allow_transfer_expiry_date_extension');
$haveNext = 0;
$havePrev = 0;

$transfer_not_found = <<<HEREDOC
      <div class="fs-transfer-detail transfer_details">
        <div class="container">
            <div class="alert alert-danger show">
                <h4 class="alert-heading"><i class="bi-exclamation-octagon-fill"></i>
                {tr:encountered_exception}
                </h4>
                <div class="details">
                {tr:transfer_not_found}
                </div>
            </div>
        </div>
      </div>
HEREDOC;

$transfer_id  = Utilities::arrayKeyOrDefault($_GET, 'transfer_id',  0, FILTER_VALIDATE_INT  );
$isEncrypted = false;
$downloadsCount = 0;
$audit = (bool)Config::get('auditlog_lifetime') ? '1' : '';
$transfer = null;

if ($transfer_id) {
    try {
        $transfer = Transfer::fromId($transfer_id);
        $downloadsCount = count($transfer->downloads);
    } catch( Exception  $e ) {
        echo $transfer_not_found;
        return;
    }
}

$extend = (bool)Config::get('allow_transfer_expiry_date_extension');

$found = true;
$user = Auth::user();
$isEncrypted = isset($transfer->options['encryption']) && $transfer->options['encryption'] === true;

if( !Auth::isAuthenticated() || !$transfer ) {
    $found = false;
}
if( $found ) {
    if( !Auth::isAdmin()) {
        // non admin user can only view their own stuff
        if( $transfer->userid != $user->id ) {
            $found = false;
        }
        
    }
}

if( !$found ) {
    echo $transfer_not_found;
    return;
}

$canDownloadArchive = count($transfer->files) > 1 && $transfer->status == TransferStatuses::AVAILABLE;
$canDownloadAsTar = true;
$canDownloadAsZip = true;
if($isEncrypted) {
    // Streaming to a local decrypted archive requires StreamSaver feature
    $canDownloadArchive = false;
    // no stream to tar file support yet.
    $canDownloadAsTar = false;
    if( Browser::instance()->allowStreamSaver ) {
        $canDownloadArchive = true;
    }
}
$ui3_allow_file_selection = Utilities::isTrue(Config::get('ui3_allow_selecting_files_on_transfer_details_page'));
if( !$ui3_allow_file_selection ) {
    $canDownloadArchive = false;
}

      
$downloadLinks = array();
$archiveDownloadLink = '#';
$archiveDownloadLinkFileIDs = '';

if(empty($transfer->options['encryption'])) {

    $token = '';
    if(array_key_exists('token', $_REQUEST)) {
        $token = $_REQUEST['token'];
    }
    if(!Utilities::isValidUID($token)) {
        $token = '';
    }

    $fileIds = array();
    foreach($transfer->files as $file) {
        $linkParams = array('files_ids' => $file->id);
        if (!empty($token)) {
            $linkParams['token'] = $token;
        }
        $downloadLinks[$file->id] = Utilities::http_build_query($linkParams, 'download.php?');
        $fileIds[] = $file->id;
    }
    $archiveParams = array();
    if (!empty($token)) {
        $archiveParams['token'] = $token;
    }
    $archiveDownloadLink = Utilities::http_build_query($archiveParams, 'download.php?');
    $archiveDownloadLinkFileIDs = implode(',', $fileIds);

    // forget this here to limit scope.
    $token = '';
    $linkParams = array();
    $archiveParams = array();
}

$hasEncryptedMetadata = false;
if($isEncrypted) {
    $hasEncryptedMetadata = isset($transfer->options['encrypted_metadata']) && $transfer->options['encrypted_metadata'];
}

$formatFileSizeForDisplayQ = function( $filesz ) use ($hasEncryptedMetadata)
{
    if( $hasEncryptedMetadata ) {
        return "{tr:encrypted_metadata_file_size_hidden}";
    }
    return Template::Q(Utilities::formatBytes($filesz));
}

?>

<div class="fs-transfer-detail transfer_details"
     id="transfer_<?php echo $transfer->id ?>"
     data-id="<?php echo $transfer->id ?>"
     data-status="<?php echo $transfer->status ?>"
     data-recipients-enabled="<?php echo $transfer->getOption(TransferOptions::GET_A_LINK) ? '' : '1' ?>"
     data-errors="<?php echo count($transfer->recipients_with_error) ? '1' : '' ?>"
     data-expiry-extension="<?php echo $transfer->expiry_date_extension ?>"
     data-key-version="<?php echo $transfer->key_version; ?>"
     data-key-salt="<?php echo $transfer->salt; ?>"
     data-password-version="<?php echo $transfer->password_version; ?>"
     data-password-encoding="<?php echo $transfer->password_encoding_string; ?>"
     data-password-hash-iterations="<?php echo $transfer->password_hash_iterations; ?>"
     data-client-entropy="<?php echo $transfer->client_entropy; ?>"
     data-transfer-encrypted="<?php                     echo Template::Q(isset($transfer->options['encryption'])?$transfer->options['encryption']:'false'); ?>"
     data-transfer-id="<?php                            echo Template::Q($transfer->id); ?>"
     data-transfer-have-encrypted-metadata="<?php       echo Template::Q($transfer->have_encrypted_metadata); ?>"
>
    <div class="container">
        <div class="row">
            <div class="col">
                <a id='fs-back-link' class='fs-link fs-link--primary fs-link--no-hover fs-back-link'>
                    <i class='fi fi-chevron-left'></i>
                    <span>{tr:transfer_details_back}</span>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="fs-transfer-detail__header mt-5">
                    <h1>{tr:transfer_details}</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-sm-12 col-md-6 col-lg-6">
                <div class="fs-transfer-detail__details">
                    <!-- <h4>{tr:transfer_name}</h4> -->
                    <?php if($transfer->status == TransferStatuses::FORWARDING) { ?>
                        <div class="fs-info fs-info--aligned">
                            <strong>{tr:forward_in_progress}</strong>
                        </div>
                    <?php } ?>
                    <div class="fs-info fs-info--aligned">
                        <strong>{tr:transfer_sent_on}</strong>
                        <span><?php echo Utilities::sanitizeOutput(Utilities::formatDate($transfer->created)) ?></span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>{tr:expiration_date}</strong>
                        <span><?php echo Utilities::sanitizeOutput(Utilities::formatDate($transfer->expires, true)) ?></span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>{tr:from}</strong>
                        <span><?php echo Template::replaceTainted($transfer->user_email) ?></span>
                    </div>
                    <?php if($transfer->subject) { ?>
                        <div class="fs-info fs-info--aligned">
                            <strong>{tr:subject}</strong>
                            <span><?php echo Template::replaceTainted($transfer->subject) ?></span>
                        </div>
                    <?php } ?>
                    <?php if($transfer->message) { ?>
                        <div class="fs-info fs-info--aligned">
                            <strong>{tr:message}</strong>
                            <span><?php echo Template::replaceTainted($transfer->message) ?></span>
                        </div>
                    <?php } ?>
                    <div class="fs-info fs-info--aligned">
                        <strong>{tr:encryption}</strong>
                        <span>
                            <?php if ($isEncrypted) {
                                echo Lang::tr('yes');
                            } else {
                                echo Lang::tr('no');
                            } ?>
                        </span>
                    </div>
                </div>

                <?php if(!$transfer->getOption(TransferOptions::GET_A_LINK)) { ?>
                    <div class="fs-transfer-detail__recipients">
                        <h4>{tr:recipients}</h4>

                        <div class="fs-transfer__upload-recipients fs-transfer__upload-recipients--show">
                            <span>{tr:your_transfer_was_sent}</span>
                            <div class="fs-transfer-detail__recipient-list recipients">
                                <?php foreach($transfer->recipients as $recipient) { ?>
                                    <div class="fs-badge-buttons recipient" data-id="<?php echo $recipient->id ?>" data-email="<?php echo Template::sanitizeOutputEmail($recipient->email) ?>" data-errors="<?php echo count($recipient->errors) ? '1' : '' ?>">
                                        <?php
                                        if(in_array($recipient->email, Auth::user()->email_addresses)) {
                                            echo '<abbr title="'.Template::sanitizeOutputEmail($recipient->email).'">'.Lang::tr('me').'</abbr>';
                                        } else {
                                            echo '<span>'.Template::sanitizeOutput($recipient->identity).'</span>';
                                        }
                                        ?>

                                        <span class="fs-badge-buttons-shell" >
                                            <span data-action="remind" class="fa fa-lg fa-repeat" title="{tr:send_reminder}"></span>
                                            <span data-action="delete" class="fi fi-trash fa-lg" title="{tr:delete}"></span>
                                            <span data-action="auditlog" class="fa fa-lg fa-history" title="{tr:open_recipient_auditlog}"></span>
                                        </span>

                                    </div>
                                <?php } ?>

                                <button type="button" class="fs-button fs-button--inverted mt-3" data-action="add_recipient" title="{tr:add_recipient}">
                                    <i class="fi fi-add"></i>
                                    <span>{tr:add_recipient}</span>
                                </button>

                                <?php if(!$transfer->getOption(TransferOptions::GET_A_LINK)) { ?>
                                    <button type="button" data-action="remind" class="fs-button fs-button--inverted mt-3">
                                        <i class="fi fi-reminder"></i>
                                        <span>{tr:send_reminder}</span>
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if($transfer->getOption(TransferOptions::GET_A_LINK)) { ?>
                    <div class="fs-transfer-detail__link">
                        <h4>{tr:download_link}</h4>
                        <div class="fs-copy">
                            <span class="download_link"><?php echo $transfer->first_recipient->download_link ?></span>

                            <button id="copy-to-clipboard" type='button'>
                              <i class='fi fi-copy'></i>
                          </button>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="col col-sm-12 col-md-6 col-lg-6">
                <div class="fs-transfer-detail__files">
                    <h4>{tr:transferred_files}</h4>
                    <?php if($canDownloadArchive) { ?>
                        <p class="fs-download__archive-hint">{tr:select_files_to_download}</p>

                        <div class="fs-download__check-all select_all">
                            <label class="fs-checkbox">
                                <label for="check-all" class="select_all_text">
                                    {tr:click_to_check_all}
                                </label>
                                <input id="check-all" type="checkbox">
                                <span class="fs-checkbox__mark toggle-select-all"></span>
                            </label>
                        </div>
                    <?php } ?>
                    
                    <div class="fs-transfer__list">
                        <div class="fs-transfer__files" data-count="<?php echo ($canDownloadArchive)?count($transfer->files):'1' ?>" >
                            <table class="fs-table files">
                                <tbody>
                                <?php foreach($transfer->files as $file) { ?>
                                    <tr class="file"
                                        data-action="download"
                                        data-id="<?php echo $file->id ?>"
                                        data-encrypted="<?php echo isset($transfer->options['encryption'])?$transfer->options['encryption']:'false'; ?>"
                                        data-mime="<?php echo Template::sanitizeOutput($file->mime_type); ?>"
                                        data-chunk-size="<?php               echo Template::Q($file->chunk_size); ?>"
                                        data-crypted-chunk-size="<?php       echo Template::Q($file->crypted_chunk_size); ?>"
                                        data-name="<?php echo Template::sanitizeOutput($file->path); ?>"
                                        data-size="<?php echo $file->size; ?>"
                                        data-encrypted-size="<?php echo $file->encrypted_size; ?>"
                                        data-key-version="<?php echo $transfer->key_version; ?>"
                                        data-key-salt="<?php echo $transfer->salt; ?>"
                                        data-password-version="<?php echo $transfer->password_version; ?>"
                                        data-password-encoding="<?php echo $transfer->password_encoding_string; ?>"
                                        data-password-hash-iterations="<?php echo $transfer->password_hash_iterations; ?>"
                                        data-client-entropy="<?php echo $transfer->client_entropy; ?>"
                                        data-fileiv="<?php echo $file->iv; ?>"
                                        data-fileaead="<?php echo $file->aead; ?>"
                                        data-transferid="<?php echo $transfer->id; ?>"
                                    >
                                            <?php if($canDownloadArchive) { ?>
                                                <td class="fs-table__check-action">
                                                    <label class="fs-checkbox select" title="{tr:select_for_archive_download}">
                                                        <input id="check-<?php echo Template::Q($file->id) ?>" type="checkbox">
                                                        <span class="fs-checkbox__mark"></span>
                                                    </label>
                                                </td>
                                            <?php } ?>
                                        
                                        <td>
                                            <div>
                                                <span class="name"><?php echo Utilities::sanitizeOutput($file->path) ?></span>
                                                <span class="size"><?php echo $formatFileSizeForDisplayQ($file->size) ?></span>

                                                <?php if(!$transfer->is_expired) { ?>

                                                    <?php if(isset($transfer->options['encryption']) && $transfer->options['encryption'] === true) { ?>
                                                        <span class="fs-button fs-button--small fs-button--transparent fs-button--primary fs-button--no-text download" title="{tr:download}"
                                                              data-action="download"
                                                              data-id="<?php echo $file->id ?>"
                                                              data-encrypted="<?php echo isset($transfer->options['encryption'])?$transfer->options['encryption']:'false'; ?>"
                                                              data-mime="<?php echo Template::sanitizeOutput($file->mime_type); ?>"
                                                              data-chunk-size="<?php               echo Template::Q($file->chunk_size); ?>"
                                                              data-crypted-chunk-size="<?php       echo Template::Q($file->crypted_chunk_size); ?>"                                       
                                                              data-name="<?php echo Template::sanitizeOutput($file->path); ?>"
                                                              data-size="<?php echo $file->size; ?>"
                                                              data-encrypted-size="<?php echo $file->encrypted_size; ?>"
                                                              data-key-version="<?php echo $transfer->key_version; ?>"
                                                              data-key-salt="<?php echo $transfer->salt; ?>"
                                                              data-password-version="<?php echo $transfer->password_version; ?>"
                                                              data-password-encoding="<?php echo $transfer->password_encoding_string; ?>"
                                                              data-password-hash-iterations="<?php echo $transfer->password_hash_iterations; ?>"
                                                              data-client-entropy="<?php echo $transfer->client_entropy; ?>"
                                                              data-fileiv="<?php echo $file->iv; ?>"
                                                              data-fileaead="<?php echo $file->aead; ?>"
                                                              data-transferid="<?php echo $transfer->id; ?>"
                                                        >
                                                            <i class="fi fi-download"></i>
                                                        </span>

                                                    <?php } else {?>
                                                        <a class="fs-button fs-button--small fs-button--transparent fs-button--primary fs-button--no-text download" title="{tr:download}" href="download.php?files_ids=<?php echo $file->id ?>">
                                                            <i class="fi fi-download"></i>
                                                        </a>
                                                    <?php } ?>
                                                <?php } ?>

                                                <?php if($audit) { ?>
                                                    <span data-action="auditlog" class="fs-button fs-button--small fs-button--transparent fs-button--primary fs-button--no-text" title="{tr:open_file_auditlog}">
                                                        <i class="fa fa-history"></i>
                                                    </span>
                                                <?php } ?>

                                                <span data-action="delete" class="fs-button fs-button--small fs-button--transparent fs-button--primary fs-button--no-text" title="{tr:delete}">
                                                    <i class="fi fi-close"></i>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                <?php } ?>

                                </tbody>
                            </table>

                            <div class="transfer" data-id="<?php echo $transfer->id ?>"></div>
                        </div>
                    </div>
                    <div class="fieldcontainer" id="encryption_description_not_supported">
                        {tr:file_encryption_disabled}
                    </div>
                    <div class="fs-transfer-detail__total-size fs-download__total-size">
                        <strong>{tr:ui2_total_size}</strong>
                        <span class="fs-info-transfer-size">
                            <?php echo $formatFileSizeForDisplayQ($transfer->size) ?>
                        </span>
                    </div>
                    <div class="fs-transfer-detail__total-size">
                        <strong>{tr:downloads}</strong>
                        <span>
                            <?php echo $downloadsCount ?>
                        </span>
                    </div>
                    <?php if($canDownloadArchive) { ?>
                        <div class="fs-download__actions archive">
                            <button type="button" class="fs-button archive_download_frame archive_download" title="{tr:archive_download}">
                                <i class="fa fa-download"></i>
                                <span>{tr:archive_download}</span>
                            </button>
                            <?php if($canDownloadAsTar) { ?>
                                <button type="button" class="fs-button archive_tar_download_frame archive_tar_download" title="{tr:archive_tar_download}">
                                    <i class="fa fa-download"></i>
                                    <span>{tr:archive_tar_download}</span>
                                </button>

                            <?php } ?>

                            <div class="archive_download_framex hidden">
                                <form id="dlarchivepost" action="<?php echo Template::Q($archiveDownloadLink) ?>" method="post">
                                    <input class="hidden archivefileids" name="files_ids" value="<?php echo Template::Q($archiveDownloadLinkFileIDs); ?>" />
                                    <input id="dlarchivepostformat" class="hidden " name="archive_format" value="zip" />
                                    <button type="submit"
                                            name="your_name" value="your_value"
                                            class="btn-link">DOWNLOAD
                                    </button>
                                </form>
                            </div>
                            <span class="downloadprogress"/>
                        </div>
                    <?php } ?>
                    
                </div>
            </div>
        </div>

        <?php
            $hiddenOptions = array(
                TransferOptions::GET_A_LINK,
                TransferOptions::STORAGE_CLOUD_S3_BUCKET,
                TransferOptions::FORWARD_SERVER_NAME,
            );
            $transferOptions = (array)$transfer->options;
            $optionNames = array_unique(array_merge(
                array_keys(Transfer::availableOptions()),
                array_keys(array_filter($transferOptions))
            ));
            $optionsHtml = array('general' => '', 'notification' => '');
            foreach ($optionNames as $o) {
                if (in_array($o, $hiddenOptions)) {
                    continue;
                }
                $active = !empty($transferOptions[$o]);
                $label = Lang::tr($o);
                if ($active && $o == TransferOptions::FORWARD_TO_ANOTHER_SERVER) {
                    $label .= ' : '.ForwardAnotherServer::getServerLabel($transferOptions[TransferOptions::FORWARD_SERVER_NAME]);
                }
                if ($active && $o == TransferOptions::REDIRECT_URL_ON_COMPLETE) {
                    $label .= ' : '.Template::sanitizeOutput($transferOptions[$o]);
                }
                $group = (strpos($o, 'email_') !== false || strpos($o, 'notification') !== false) ? 'notification' : 'general';
                $optionsHtml[$group] .= '<div class="fs-transfer-detail__check'.($active ? '' : ' fs-transfer-detail__check--inactive').'">';
                $optionsHtml[$group] .= '<i class="fi '.($active ? 'fi-valid' : 'fi-close').'"></i>';
                $optionsHtml[$group] .= '<span>'.$label.'</span>';
                $optionsHtml[$group] .= '</div>';
            }
        ?>
        <div class="row">
            <div class="col">
                <div class="fs-transfer-detail__options">
                    <h4>{tr:transfer_selected_options}</h4>
                    <div class="row">
                        <div class="col-12 col-lg-6 mt-4">
                            <strong>{tr:general_settings}</strong>
                            <?php echo $optionsHtml['general'] ? $optionsHtml['general'] : Lang::tr('none'); ?>
                        </div>
                        <div class="col-12 col-lg-6 mt-4">
                            <strong>{tr:notification_settings}</strong>
                            <?php echo $optionsHtml['notification'] ? $optionsHtml['notification'] : Lang::tr('none'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="fs-transfer-detail__actions" >
                    <?php if($audit) { ?>
                        <button type="button" data-action="auditlog" class="fs-button fs-button--inverted">
                            <i class="fi fi-list"></i>
                            <span>{tr:see_transfer_logs}</span>
                        </button>
                    <?php } ?>

                    <button type="button" data-action="delete" class="fs-button fs-button--inverted">
                        <i class="fi fi-trash"></i>
                        <span>{tr:delete_transfer}</span>
                    </button>

                    <?php if($extend) { ?>
                        <button type="button" data-action="extend" class="fs-button fs-button--inverted objectholder" data-id="<?php echo $transfer->id ?>" data-expiry-extension="<?php echo $transfer->expiry_date_extension ?>" >
                            <i class="fa fa-calendar-plus"></i>
                            <span>{tr:extend_expires}</span>
                        </button>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>

    
</div>

<div class="transfer_is_encrypted not_displayed">
    <?php echo $isEncrypted ? 1 : 0;  ?>
</div>
<div class="encrypted_metadata" id="encrypted_metadata"><?php echo Template::Q($transfer->encrypted_metadata) ?></div>

<script type="text/javascript" src="{path:js/transfer_detail_page.js}"></script>
