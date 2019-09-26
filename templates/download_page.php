<div class="boxnoframe">
    <h1>{tr:download_page}</h1>
    
    <?php
    
    if(!array_key_exists('token', $_REQUEST))
        throw new TokenIsMissingException();
    
    $token = $_REQUEST['token'];
    if(!Utilities::isValidUID($token))
        throw new TokenHasBadFormatException($token);
    
    $recipient = Recipient::fromToken($token); // Throws
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
    if($isEncrypted) {
        // It is not possible to download archives of the encrypted files. 
        // since there is no unzip -> decrypt -> zip process in the current filesender 
        $canDownloadArchive = false;
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
    
    <div class="general box" data-transfer-size="<?php echo $transfer->size ?>">
        <div class="from">{tr:from} : <?php echo Template::sanitizeOutputEmail($transfer->user_email) ?></div>
        
        <div class="created">{tr:created} : <?php echo Utilities::sanitizeOutput(Utilities::formatDate($transfer->created)) ?></div>
        
        <div class="expires">{tr:expires} : <?php echo Utilities::sanitizeOutput(Utilities::formatDate($transfer->expires)) ?></div>
        
        <div class="size">{tr:size} : <?php echo Utilities::sanitizeOutput(Utilities::formatBytes($transfer->size)) ?></div>
        
        <?php if($transfer->subject) { ?>
            <div class="subject">{tr:subject} : <?php echo Utilities::sanitizeOutput($transfer->subject) ?></div>
        <?php } ?>
        
        <?php if($transfer->message) { ?>
            <div class="message">
                {tr:message} :
                <p>
                    <?php echo Utilities::sanitizeOutput($transfer->message) ?>
                </p>
            </div>
        <?php } ?>
    </div>
    <div class="files box" data-count="<?php echo ($isEncrypted)?'1':count($transfer->files) ?>">
        <div class="select_all">
            <span class="fa fa-lg fa-mail-reply fa-rotate-270"></span>
            <span class="select clickable">
                <span class="fa fa-2x fa-square-o toggle-select-all" title="{tr:select_all_for_archive_download}"></span>
                <span>{tr:select_all_for_archive_download}</span>
            </span>
        </div>
    <?php foreach($transfer->files as $file) { ?>
        <div class="file" data-id="<?php echo $file->id ?>"
             data-encrypted="<?php echo isset($transfer->options['encryption'])?$transfer->options['encryption']:'false'; ?>"
             data-mime="<?php echo $file->mime_type; ?>"
             data-name="<?php echo $file->path; ?>"
             data-key-version="<?php echo $transfer->key_version; ?>"
             data-key-salt="<?php echo $transfer->salt; ?>"
             data-password-version="<?php echo $transfer->password_version; ?>"
             data-password-encoding="<?php echo $transfer->password_encoding_string; ?>"
             data-password-hash-iterations="<?php echo $transfer->password_hash_iterations; ?>"
             data-client-entropy="<?php echo $transfer->client_entropy; ?>"
             data-fileiv="<?php echo $file->iv; ?>"
             data-fileaead="<?php echo $file->aead; ?>"
        >
            
            <span class="select clickable fa fa-2x fa-square-o" title="{tr:select_for_archive_download}"></span>
            <span class="name"><?php echo Utilities::sanitizeOutput($file->path) ?></span>
            <span class="size"><?php echo Utilities::formatBytes($file->size) ?></span>
            <span class="download_decryption_disabled"><br/>{tr:file_encryption_disabled}</span>
            <a rel="nofollow" href="<?php echo empty($downloadLinks[$file->id]) ? '#' : Utilities::sanitizeOutput($downloadLinks[$file->id]) ?>" class="download" title="{tr:download_file}">
                <span class="fa fa-2x fa-download"></span>
                {tr:download}
            </a>
            <span class="downloadprogress"></span>
        </div>
    <?php } ?>
        <?php if($canDownloadArchive) { ?>
            <div class="archive">
            <div class="archive_message">{tr:archive_message}</div>
            
            <div class="mac_archive_message">
                {tr:mac_archive_message}
            </div>
            
            <div class="archive_download_frame">
            <a rel="nofollow" href="<?php echo Utilities::sanitizeOutput($archiveDownloadLink) ?>" class="archive_download" title="{tr:archive_download}">
                <span class="fa fa-2x fa-download"></span>
                {tr:archive_download}
            </a>
            </div>
            <div class="archive_tar_download_frame">
            <a rel="nofollow" href="<?php echo Utilities::sanitizeOutput($archiveDownloadLink) ?>" class="archive_tar_download" title="{tr:archive_tar_download}">
                <span class="fa fa-2x fa-download"></span>
                {tr:archive_tar_download}
            </a>
            </div>
            <span class="downloadprogress"></span>
        </div>
    <?php } ?>    
        <div class="transfer" data-id="<?php echo $transfer->id ?>"></div>
    </div>
</div>

    <div class="transfer_is_encrypted not_displayed">
        <?php echo $isEncrypted ? 1 : 0;  ?>
    </div>

<script type="text/javascript" src="{path:js/download_page.js}"></script>
