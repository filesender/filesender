<div class="core">
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


?>

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

        <div class="form-check form-switch custom-control custom-switch" data-option="options">
            <input id="streamsaverenabled" class="form-check-input" name="streamsaverenabled" type="checkbox" checked="checked" />
            <label for="streamsaverenabled" class="form-check-label">{tr:use_streamsaver_for_download}</label>
        </div>
    <?php } ?>
                            
    <div class="verify_email_to_download">
        <h2>{tr:verify_your_email_address_to_download}</h2>

        <table columns="2" border="1">
	    <col style="width:25%">
	    <col style="width:75%">            
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
    
    <table class="table borderless general" data-transfer-size="<?php echo $transfer->size ?>">
        <tbody>
            <?php if(!array_key_exists('hide_sender_email', $transfer->options) ||
                     !$transfer->options['hide_sender_email']) { ?>
                <tr><td align="right" class="from">{tr:from}</td><td colspan="5"><?php echo Template::sanitizeOutputEmail($transfer->user_email) ?></td></tr>
            <?php } ?>
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
                    <span class="select_all_text">{tr:select_all_for_archive_download}</span>
                </span>
            </div>
        <?php } ?>
        <table class="table borderless files">
            <tbody>

                <?php foreach($sortedFiles as $file) { ?>
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
                        data-transferid="<?php echo $transfer->id ?>"
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
                            <a rel="nofollow" href="<?php echo empty($downloadLinks[$file->id]) ? '#' : Utilities::sanitizeOutput($downloadLinks[$file->id]) ?>" class="btn btn-primary download" title="{tr:download_file}">
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
