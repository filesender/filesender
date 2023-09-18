<?php

$canDownload = true;

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

$have_av = !empty(Config::getArray('avprogram_list'));
function outputBool( $v )
{
    if( $v ) echo "1";
    else     echo "0";
}
function passErrToDesc( $p, $err )
{
    if( $p )   return "succeeded";
    if( $err ) return "error";
    return "failed";
}
function presentAVName( $v )
{
    $ret = Lang::tr("av_name_".$v);
    if( str_starts_with($ret,"{")) {
        $t = Lang::tr($v);
        if( $ret != $t ) {
            $ret = $t;
            if( str_starts_with($ret,"{")) {
                $ret = $v;
            }
        }
    }
    return $ret;
}

$rid = 0;
if( Utilities::isTrue(Config::get('download_verification_code_enabled'))) {
    if(array_key_exists('token', $_REQUEST)) {
        $token = $_REQUEST['token'];
        
        if(Utilities::isValidUID($token)) {
            
            try {
                // Getting recipient from the token
                $recipient = Recipient::fromToken($token); // Throws
                $rid = $recipient->id;
            } catch (RecipientNotFoundException $e) {
            }
        }
    }
}

$showdownloadlinks = Utilities::isTrue(Config::get('download_show_download_links'));

?>
<div class="box">
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

    $sortedFiles = $transfer->files;
    usort($sortedFiles, function( $a, $b ) { return strnatcmp( $a->name, $b->name ); });

    $downloadLinks = array();
    $archiveDownloadLink = '#';
    $archiveDownloadLinkFileIDs = '';
    
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
        ), 'download.php?' );
        $archiveDownloadLinkFileIDs = implode(',', $fileIds);
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


    
    if( $transfer->must_be_logged_in_to_download ) {
        $user = Auth::user();
        if( !$user ) {

            $loginToDownload = GUI::getLoginButton(Utilities::http_build_query(array('token' => $token, 's' => 'download')));
            echo '<div class="must_login_message">';
            echo Lang::tr('must_be_logged_in_to_download_first_person');
            echo '<br/>' . $loginToDownload;
            echo '</div>';
            
            $canDownload = false;
            $canDownloadAsTar = false;
            $canDownloadAsZip = false;
            // close page
            echo "</div>";
            return;
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
                            
    <div class="verify_email_to_download">
        <h2>{tr:verify_your_email_address_to_download}</h2>

        <table columns="2" border="1">
	    <col class="verify_email_to_download_col1">
	    <col class="verify_email_to_download_col2">
            <tr>
                <td>
                    <a href="#" class="verificationcodesendtoemail">
                        <span class="fa fa-paper-plane fa-lg"></span>&nbsp;{tr:send}
                    </a>
                </td>
                <td class="verify_labels2">{tr:send_verification_code_to_your_email_address}</td>
            </tr>
            <tr>
                <td colspan="2">
                    <p>{tr:then_enter_verification_code_below}</p>
                </td>
            </tr>
            <tr class="verificationcodesendpage">
                <td>
                    <a class="verificationcodesend verificationcodesendelement" href="#">
                        <span class="fa fa-unlock fa-lg"></span>&nbsp;{tr:verify}
                    </a>
                </td>
                <td class="verify_labels2">
                    <input id="verificationcode" class="verificationcode verify_labels verificationcodesendelement" name="verificationcode" type="text"/>
                </td>
            </tr>
        </table>
        
    </div>
    
    
    <div class="general box" data-transfer-size="<?php echo $transfer->size ?>">
        <?php if(!array_key_exists('hide_sender_email', $transfer->options) ||
                 !$transfer->options['hide_sender_email']) { ?>
        <div class="from">{tr:from} : <?php echo Template::sanitizeOutputEmail($transfer->user_email) ?></div>
        <?php } ?>
        
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
    <?php if($have_av) { ?>
        <div class="general2 box" data-transfer-size="<?php echo $transfer->size ?>">
            <div class="avdesc">{tr:av_results_description}
            <?php foreach($sortedFiles as $file) { ?>
                <div class="avfile" data-avid="<?php echo $file->id ?>" >
                    <span class="name avheader<?php outputBool($file->av_all_good)?> "><?php echo Utilities::sanitizeOutput($file->path) ?></span>
                    <?php if(!$file->have_avresults) { ?>
                        <span class="desc">{tr:no_av_scans_performed}</span>
                    <?php } else { ?>
                        <table>
                            <tr class="avresultheader">
                                <th>{tr:performed}</th>
                                <th>{tr:result}</th>
                                <th>{tr:avname}</th>
                            </tr>
                        <?php foreach($file->scan_results as $res) { $resultdesc = passErrToDesc($res->passes,$res->error); ?>
                            <tr class="avresult">
                                <td class="created"><?php echo Utilities::sanitizeOutput(Utilities::formatDate($res->created)) ?></td>
                                <td class="result avresult<?php echo $resultdesc ?>"><?php echo Lang::tr($resultdesc) ?></td>
                                <td class="app_name"><?php echo presentAVName($res->name) ?></td>
                            </tr>
                        <?php } ?>
                        </table>
                    <?php } ?>
                    
                </div>
            <?php } ?>
            </div>
        </div>
    <?php } ?>
    <div class="files box" data-count="<?php echo ($canDownloadArchive)?count($sortedFiles):'1' ?>">
        <?php if($canDownloadArchive) { ?>
        <div class="select_all">
            <span class="fa fa-lg fa-mail-reply fa-rotate-270"></span>
            <span class="select clickable">
                <span class="fa fa-2x fa-square-o toggle-select-all" title="{tr:select_all_for_archive_download}"></span>
                <span>{tr:select_all_for_archive_download}</span>
            </span>
        </div>
        <?php } ?>
    <?php foreach($sortedFiles as $file) { ?>
        <div class="file" data-id="<?php echo $file->id ?>"
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
             data-transferid="<?php echo $transfer->id ?>"
        >
            
            <?php if($canDownloadArchive) { ?>
                <span class="select clickable fa fa-2x fa-square-o" title="{tr:select_for_archive_download}"></span>
            <?php } ?>
            <span class="name"><?php echo Utilities::sanitizeOutput($file->path) ?></span>
            <span class="size"><?php echo Utilities::formatBytes($file->size) ?></span>
            <span class="download_decryption_disabled"><br/>{tr:file_encryption_disabled}</span>
            <a rel="nofollow" href="<?php echo empty($downloadLinks[$file->id]) ? '#' : Utilities::sanitizeOutput($downloadLinks[$file->id]) ?>" class="download" title="{tr:download_file}">
                <span class="fa fa-2x fa-download"></span>
                {tr:download}
            </a>
            <span class="downloadprogress"/>
            <?php if($showdownloadlinks) { ?>
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
            <?php } ?>
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
            <?php if($showdownloadlinks) { ?>
            <br>
            <span class="directlinkArchive">
            <?php
              if ($isEncrypted) {
                echo 'Direct Links are not avaliable for encrypted files';
              } else {
                echo 'Direct Link: '.Utilities::sanitizeOutput($archiveDownloadLink);
              }
            ?>
            </span>
            <br><br>
            <?php } ?>
            <?php if($canDownloadAsTar) { ?>
            <div class="archive_tar_download_frame">
            <a rel="nofollow" href="<?php echo Utilities::sanitizeOutput($archiveDownloadLink) ?>" class="archive_tar_download" title="{tr:archive_tar_download}">
                <span class="fa fa-2x fa-download"></span>
                {tr:archive_tar_download}
            </a>
            </div>            
            <?php if($showdownloadlinks) { ?>
            <br>
            <span class="directlinkArchive">
            <?php
              if ($isEncrypted) {
                echo 'Direct Links are not avaliable for encrypted files';
              } else {
                echo 'Direct Link: '.Utilities::sanitizeOutput($archiveDownloadLink);
              }
            ?>
            </span>
            <?php } ?>
            <?php } ?>

            <div class="archive_download_framex hidden">
                <form id="dlarchivepost" action="<?php echo Utilities::sanitizeOutput($archiveDownloadLink) ?>" method="post">
                    <input class="hidden archivefileids" name="files_ids" value="<?php echo $archiveDownloadLinkFileIDs; ?>" />
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
        <div class="transfer" data-id="<?php echo $transfer->id ?>"></div>
        <div class="rid" data-id="<?php echo $rid ?>"></div>
    </div>
</div>

    <div class="transfer_is_encrypted not_displayed">
        <?php echo $isEncrypted ? 1 : 0;  ?>
    </div>

<script type="text/javascript" src="{path:js/download_page.js}"></script>
