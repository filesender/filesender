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
if(Utilities::isTrue(Config::get('download_verification_code_enabled'))) {
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

$days_to_expire = $transfer->days_to_expire;

$showdownloadlinks = Utilities::isTrue(Config::get('download_show_download_links'));

?>

<div class="fs-download">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-download__title">
                    <h1><?php echo Template::sanitizeOutputEmail($transfer->user_email) ?> {tr:transferred_these_files}</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-sm-12 col-md-5 col-lg-4">
                <div class="fs-download__details mt-5">
                    <h4>{tr:transfer_details}</h4>
                    <div class="fs-info fs-info--aligned mt-5">
                        <strong>{tr:transfer_sent_on}</strong>
                        <span><?php echo Utilities::sanitizeOutput(Utilities::formatDate($transfer->created,true)) ?></span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>{tr:expiration_date}</strong>
                        <span><?php echo Utilities::sanitizeOutput(Utilities::formatDate($transfer->expires)) ?></span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>{tr:from}</strong>
                        <span><?php echo Template::sanitizeOutputEmail($transfer->user_email) ?></span>
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
                        <strong>{tr:transfer_size}</strong>
                        <span><?php echo Utilities::sanitizeOutput(Utilities::formatBytes($transfer->size)) ?></span>
                    </div>
                </div>
            </div>
            <div class="col col-sm-12 col-md-7 col-lg-8">
                <div class="fs-download__files">
                    <?php if($canDownloadArchive) { ?>
                        <div class="fs-download__check-all select_all">
                            <label class="fs-checkbox">
                                <label for="check-all" class="select_all_text">
                                    {tr:select_all_files}
                                </label>
                                <input id="check-all" type="checkbox">
                                <span class="fs-checkbox__mark toggle-select-all"></span>
                            </label>
                        </div>
                    <?php } ?>

                    <div class="fs-transfer__list">
                        <div class="fs-transfer__files" data-count="<?php echo ($canDownloadArchive)?count($sortedFiles):'1' ?>">
                            <table class="fs-table files">
                                <thead class="fs-transfer__files__thead">
                                    <tr>
                                        <th scope="col">Selected</th>
                                        <th scope="col">File information</th>
                                    </tr>
                                </thead>
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
                                        <td class="fs-table__check-action">
                                            <?php if($canDownloadArchive) { ?>
                                                <label class="fs-checkbox select" title="{tr:select_for_archive_download}">
                                                    <input id="check-<?php echo $file->id ?>" type="checkbox">
                                                    <span class="fs-checkbox__mark"></span>
                                                </label>
                                            <?php } ?>

                                        </td>
                                        <td>
                                            <div>
                                                <span class="name"><?php echo Utilities::sanitizeOutput($file->path) ?></span>
                                                <span class="size"><?php echo Utilities::formatBytes($file->size) ?></span>
                                                <span class="downloadprogress"></span>
                                                <span class="remove stage1">
                                                    <a rel="nofollow" href="<?php echo empty($downloadLinks[$file->id]) ? '#' : Utilities::sanitizeOutput($downloadLinks[$file->id]) ?>" class="fs-button fs-button--small fs-button--transparent fs-button--primary fs-button--no-text download" title="{tr:download_file}">
                                                        <i class="fi fi-download"></i>
                                                    </a>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                <?php } ?>

                                </tbody>
                            </table>

                            <div class="transfer" data-id="<?php echo $transfer->id ?>"></div>
                            <div class="rid" data-id="<?php echo $rid ?>"></div>
                        </div>
                    </div>

                    <div class="fieldcontainer" id="encryption_description_not_supported">
                        {tr:file_encryption_disabled}
                    </div>

                    <div class="fs-download__total-size">
                        <strong>{tr:size_of_selected_files}</strong>
                        <span>486mb</span>
                    </div>

                    <?php if($canDownloadArchive) { ?>
                        <div class="fs-download__actions archive">
                            <button type="button" class="fs-button archive_download_frame archive_download" title="{tr:archive_download}">
                                <span>{tr:archive_download}</span>
                            </button>
                            <?php if($canDownloadAsTar) { ?>
                                <button type="button" class="fs-button archive_tar_download_frame archive_tar_download" title="{tr:archive_tar_download}">
                                    <span>{tr:archive_tar_download}</span>
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

                    <div class="fs-download__zip64-info archive_message mac_archive_message">
                        <p>
                            {tr:mac_archive_message}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <?php if( Browser::instance()->allowStreamSaver ) { ?>

<div class="fs-download form-check form-switch custom-control custom-switch" data-option="options">
    <div class="container">
        <div class="row">
            <div class="col">
              <input id="streamsaverenabled" class="form-check-input" name="streamsaverenabled" type="checkbox" checked="checked" />
              <label for="streamsaverenabled" class="form-check-label">{tr:use_streamsaver_for_download}</label>
            </div>
        </div>
    </div>
</div>
    <?php } ?>


    <?php if($have_av) { ?>
<div class="fs-download general2 box" data-transfer-size="<?php echo $transfer->size ?>">
    <div class="container">
        <div class="row">
            <div class="col">
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
        </div>
    </div>
</div>
    <?php } ?>


<div class="fs-download verify_email_to_download">
    <div class="container">
        <div class="row">
            <div class="col">
                <h4>{tr:verify_your_email_address_to_download}</h4>
                <table columns="2" border="1">
                    <col class="width25">
                    <col class="width75">
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
        </div>
    </div>
</div>

<div class="transfer_is_encrypted not_displayed">
    <?php echo $isEncrypted ? 1 : 0;  ?>
</div>

<script type="text/javascript" src="{path:js/download_page.js}"></script>
