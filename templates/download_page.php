<div class="box">
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
    
    ?>
    
    <div class="disclamer">
        {tr:download_disclamer}
    </div>
    
    <div class="general box" data-transfer-size="<?php echo $transfer->size ?>">
        <div class="from">{tr:from} : <?php echo Utilities::sanitizeOutput($transfer->user_email) ?></div>
        
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
    <div class="files box" data-count="<?php echo (isset($transfer->options['encryption']) && $transfer->options['encryption'])?'1':count($transfer->files) ?>">
        <div class="select_all">
            <span class="fa fa-lg fa-mail-reply fa-rotate-270"></span>
            <span class="select clickable">
                <span class="fa fa-2x fa-square-o" title="{tr:select_all_for_archive_download}"></span>
                <span>{tr:select_all_for_archive_download}</span>
            </span>
        </div>
    <?php foreach($transfer->files as $file) { ?>
        <div class="file" data-id="<?php echo $file->id ?>"
             data-encrypted="<?php echo isset($transfer->options['encryption'])?$transfer->options['encryption']:'false'; ?>"
             data-mime="<?php echo $file->mime_type; ?>"
             data-name="<?php echo $file->name; ?>">
            
            <span class="select clickable fa fa-2x fa-square-o" title="{tr:select_for_archive_download}"></span>
            <span class="name"><?php echo Utilities::sanitizeOutput($file->name) ?></span>
            <span class="size"><?php echo Utilities::formatBytes($file->size) ?></span>
            <span class="download_decryption_disabled"><br/>{tr:file_encryption_disabled}</span>
            <a href="#" class="download" title="{tr:download_file}">
                <span class="fa fa-2x fa-download"></span>
                {tr:download}
            </a>
            <span class="downloadprogress"></span>
            <br>
            <span class="directlink">
    <?php
	if (isSet($transfer->options['encryption']) && $transfer->options['encryption']) {
		echo 'Direct Links are not avaliable for encrypted files';
	} else {
		echo 'Direct Link: '.Config::get('site_url').'download.php?token='.$token.'&files_ids='.$file->id;
	}
    ?>
	    </span>
        </div>
    <?php } ?>
    <?php if(!isset($transfer->options['encryption']) || $transfer->options['encryption'] === false) { ?>
    <?php // It is not possible to download archives of the encrypted files since there is no unzip -> decrypt -> zip process in the current filesender ?>
        <div class="archive">
            <div class="archive_message">{tr:archive_message}</div>
            
            <div class="mac_archive_message">
                {tr:mac_archive_message}
            </div>
            
            <a href="#" class="archive_download" title="{tr:archive_download}">
                <span class="fa fa-2x fa-download"></span>
                {tr:archive_download}
            </a>
            <span class="downloadprogress"></span>
        </div>
    <?php } ?>    
        <div class="transfer" data-id="<?php echo $transfer->id ?>"></div>
    </div>
</div>

<script type="text/javascript" src="{path:js/download_page.js}"></script>
