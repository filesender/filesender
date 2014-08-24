<div class="box">
    <h1>{tr:download_page}</h1>
    
    <?php
    
    if(!array_key_exists('token', $_REQUEST))
        throw new DownloadMissingTokenException();
    
    $token = $_REQUEST['token'];
    if(!Utilities::isValidUID($token))
        throw new DownloadBadTokenFormatException($token);
    
    $recipient = Recipient::fromToken($token); // Throws
    $transfer = $recipient->transfer;
    
    ?>
    
    <div class="disclamer">
        {tr:download_disclamer}
    </div>
    
    <div class="general box">
        <div class="from">{tr:from} : <?php echo htmlentities($transfer->user_email) ?></div>
        
        <div class="created">{tr:created} : <?php echo htmlentities(Utilities::formatDate($transfer->created)) ?></div>
        
        <div class="expires">{tr:expires} : <?php echo htmlentities(Utilities::formatDate($transfer->expires)) ?></div>
        
        <div class="expires">{tr:size} : <?php echo htmlentities(Utilities::formatBytes($transfer->size)) ?></div>
        
        <?php if($transfer->subject) { ?>
        <div class="subject">{tr:subject} : <?php echo htmlentities($transfer->subject) ?></div>
        <?php } ?>
        
        <?php if($transfer->message) { ?>
        <div class="message">
            {tr:message} :
            <p>
                <?php echo htmlentities($transfer->message) ?>
            </p>
        </div>
        <?php } ?>
    </div>
    
    <div class="files box" data-count="<?php echo count($transfer->files) ?>">
        <div class="select_all">
            <span class="fa fa-lg fa-mail-reply fa-rotate-270"></span>
            <span class="select clickable">
                <span class="fa fa-2x fa-square-o" title="{tr:select_all_for_archive_download}"></span>
                <span>{tr:select_all_for_archive_download}</span>
            </span>
        </div>
    <?php foreach($transfer->files as $file) { ?>
        <div class="file" data-id="<?php echo $file->id ?>">
            <span class="select clickable fa fa-2x fa-square-o" title="{tr:select_for_archive_download}"></span>
            <span class="name"><?php echo htmlentities($file->name) ?></span>
            <span class="size"><?php echo Utilities::formatBytes($file->size) ?></span>
            <a href="#" class="download" title="{tr:download_file}">
                <span class="fa fa-2x fa-download"></span>
                {tr:download}
            </a>
        </div>
    <?php } ?>
        <div class="archive">
            <div class="archive_message">{tr:archive_message}</div>
            
            <div class="mac_archive_message">
                {tr:mac_archive_message} : <a href="<?php echo Config::get('mac_unzip_link'); ?>"><?php echo Config::get('mac_unzip_name'); ?></a>.
            </div>
            
            <a href="#" class="archive_download" title="{tr:archive_download}">
                <span class="fa fa-2x fa-download"></span>
                {tr:archive_download}
            </a>
        </div>
    </div>
        
        
    <?php if (false && $fileData[0]['fileenabledownloadreceipts'] == 'true') { ?>
        <p><input type="checkbox" id="dlcomplete" style="width:20px; vertical-align: middle"/>{tr:_DOWNLOADER_RECEIPT}</p>
    <?php } ?>
</div>

<script type="text/javascript" src="{path:res/js/download_page.js}"></script>
