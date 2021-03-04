<div class="core">
    <h1>{tr:download_page}</h1>
    
    <?php
    
    if(!array_key_exists('token', $_REQUEST))
        throw new TokenIsMissingException();
    
    $token = $_REQUEST['token'];
    if(!Utilities::isValidUID($token))
        throw new TokenHasBadFormatException($token);

    try {
        $recipient = Recipient::fromToken($token); // Throws
    } catch (RecipientNotFoundException $e) {
        throw new TransferPresumedExpiredException();
    }
    $transfer = $recipient->transfer;
    
    if($transfer->isExpired()) throw new TransferExpiredException($transfer);
    
    if($transfer->status != TransferStatuses::AVAILABLE) throw new TransferNotAvailableException($transfer);

    $downloadLinks = array();
    $archiveDownloadLink = '#';
    if(empty($transfer->options['encryption'])) {
        $fileIds = array();
        foreach($transfer->files as $file) {
            $downloadLinks[$file->id] = Utilities::http_build_query(array(
                'token' => $token,
                'files_ids' => $file->id,
            ), 'download.php?' );
            $fileIds[] = $file->id;
        }
        $archiveDownloadLink = Utilities::http_build_query(array(
            'token' => $token,
            'files_ids' => implode(',', $fileIds),
        ), 'download.php?' );
    }

    $isEncrypted = isset($transfer->options['encryption']) && $transfer->options['encryption'];
    $canDownloadArchive = count($transfer->files) > 1;
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
    ?>
    
    <div class="disclamer">
        <?php if(!$isEncrypted) { ?>
            {tr:download_disclamer}
            {tr:download_disclamer_nocrypto_message}
        <?php } ?>
        <?php if($isEncrypted) { ?>
            {tr:download_disclamer_crypto_message}
        <?php } ?>
        <?php if($canDownloadArchive) { ?>
            {tr:download_disclamer_archive}
        <?php } ?>
    </div>
    <div class="crypto_not_supported_message">
         {tr:file_encryption_disabled}
    </div>

    <?php if( Browser::instance()->allowStreamSaver ) { ?>

        <div class="fieldcontainer" data-option="options">
            <input id="streamsaverenabled" name="streamsaverenabled" type="checkbox" checked="checked" />
            <label for="streamsaverenabled">{tr:use_streamsaver_for_download}</label>
        </div>
    <?php } ?>
                            
    
    <table class="table borderless general" data-transfer-size="<?php echo $transfer->size ?>">
        <tbody>
            <tr><td align="right" class="from">{tr:from}</td><td colspan="5"><?php echo Template::sanitizeOutputEmail($transfer->user_email) ?></td></tr>
            <tr>
                <td align="right" class="created">{tr:created}</td><td><?php echo Utilities::sanitizeOutput(Utilities::formatDate($transfer->created)) ?></td>
                <td align="right" class="expires">{tr:expires}</td><td><?php echo Utilities::sanitizeOutput(Utilities::formatDate($transfer->expires)) ?></td>
                <td align="right" class="size">{tr:size}</td><td><?php echo Utilities::sanitizeOutput(Utilities::formatBytes($transfer->size)) ?></td>
            </tr>

            <?php if($transfer->subject) { ?>
                <tr><td align="right" class="subject">{tr:subject}</td><td><?php echo Utilities::sanitizeOutput($transfer->subject) ?></td></tr>
            <?php } ?>
        
            <?php if($transfer->message) { ?>
                <tr><td align="right" class="message">{tr:message}</td><td><p><?php echo Utilities::sanitizeOutput($transfer->message) ?></p></td></tr>
            <?php } ?>
        </tbody>
    </table>

    
    <div class="files box" data-count="<?php echo ($canDownloadArchive)?count($transfer->files):'1' ?>">
        <?php if($canDownloadArchive) { ?>
            <div class="select_all">
                <span class="fa fa-lg fa-mail-reply fa-rotate-270"></span>
                <span class="select clickable">
                    <span class="fa fa-2x fa-square-o toggle-select-all" title="{tr:select_all_for_archive_download}"></span>
                    <span>{tr:select_all_for_archive_download}</span>
                </span>
            </div>
        <?php } ?>
        <table class="table borderless files">
            <tbody>

                <?php foreach($transfer->files as $file) { ?>
                    <tr class="file" data-id="<?php echo $file->id ?>"
                        data-encrypted="<?php echo isset($transfer->options['encryption'])?$transfer->options['encryption']:'false'; ?>"
                        data-mime="<?php echo $file->mime_type; ?>"
                        data-name="<?php echo $file->path; ?>"
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
                    >
                        <td>
                            <?php if($canDownloadArchive) { ?>
                                <span class="select clickable fa fa-2x fa-square-o" title="{tr:select_for_archive_download}"></span>
                            <?php } ?>
                        </td>
                        <td class="name"><?php echo Utilities::sanitizeOutput($file->path) ?></td>
                        <td class="size"><?php echo Utilities::formatBytes($file->size) ?></td>
                        <td><p class="download_decryption_disabled">{tr:file_encryption_disabled}</p></td>
                        <td class="downloadprogress" ></td>
                        <td>
                            <a rel="nofollow" href="<?php echo empty($downloadLinks[$file->id]) ? '#' : Utilities::sanitizeOutput($downloadLinks[$file->id]) ?>" class="download" title="{tr:download_file}">
                                <span class="fa fa-2x fa-download"></span>
                                {tr:download}
                            </a>
                        </td>
                    </tr>

                <?php } ?>
                
            </tbody>
        </table>
                
        
        <?php if($canDownloadArchive) { ?>
            <div class="archive">
            <div class="archive_message">{tr:archive_message}</div>
            
            <div class="mac_archive_message">
                {tr:mac_archive_message}
            </div>

            <button type="button" class="clear_all btn btn-secondary archive_download_frame archive_download" title="{tr:archive_download}">
                <span class="fa fa-2x fa-download"></span>
                {tr:archive_download}
            </button>
            <?php if($canDownloadAsTar) { ?>
                <button type="button" class="clear_all btn btn-secondary archive_tar_download_frame archive_tar_download" title="{tr:archive_tar_download}">
                    <span class="fa fa-2x fa-download"></span>
                    {tr:archive_tar_download}
                </button>
                
            <?php } ?>    
            <span class="downloadprogress"/>
        </div>
    <?php } ?>    
        <div class="transfer" data-id="<?php echo $transfer->id ?>"></div>
    </div>
</div>



    <div class="transfer_is_encrypted not_displayed">
        <?php echo $isEncrypted ? 1 : 0;  ?>
    </div>

<script type="text/javascript" src="{path:js/download_page.js}"></script>
