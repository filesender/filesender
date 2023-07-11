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

$now = time();
$datediff = $transfer->expires - $now;
$days_to_expire = round($datediff / (60 * 60 * 24));

?>

<div class="fs-download">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-download__title">
                    <h1><?php echo Template::sanitizeOutputEmail($transfer->user_email) ?> {tr:transferred_these_files}</h1>
                    <p>{tr:transfer_expires_in} <?php echo $days_to_expire ?> {tr:days}</p>
                </div>
            </div>
        </div>

        <hr />

        <div class="row">
            <div class="col col-sm-12 col-md-5 col-lg-4">
                <div class="fs-download__details">
                    <h2>{tr:transfer_details}</h2>
                    <div class="fs-download__info">
                        <strong>{tr:transfer_sent_on}:</strong>
                        <span><?php echo Utilities::sanitizeOutput(Utilities::formatDate($transfer->created)) ?></span>
                    </div>
                    <div class="fs-download__info">
                        <strong>{tr:from}:</strong>
                        <span><?php echo Template::sanitizeOutputEmail($transfer->user_email) ?></span>
                    </div>
                    <?php if($transfer->subject) { ?>
                        <div class="fs-download__info">
                            <strong>{tr:subject}:</strong>
                            <span><?php echo Utilities::sanitizeOutput($transfer->subject) ?></span>
                        </div>
                    <?php } ?>
                    <div class="fs-download__info">
                        <strong>{tr:expiration_date}:</strong>
                        <span><?php echo Utilities::sanitizeOutput(Utilities::formatDate($transfer->expires)) ?></span>
                    </div>
                    <?php if($transfer->message) { ?>
                        <div class="fs-download__info">
                            <strong>{tr:message}:</strong>
                            <span><?php echo Utilities::sanitizeOutput($transfer->message) ?></span>
                        </div>
                    <?php } ?>
                    <div class="fs-download__info">
                        <strong>{tr:transfer_size}:</strong>
                        <span><?php echo Utilities::sanitizeOutput(Utilities::formatBytes($transfer->size)) ?></span>
                    </div>
                </div>
            </div>
            <div class="col col-sm-12 col-md-7 col-lg-8">
                <div class="fs-download__files">
                    <h2>{tr:download_files}</h2>
                    <p>{tr:select_files_to_download}</p>

                    <?php if($canDownloadArchive) { ?>
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
                        <div class="fs-transfer__files" data-count="<?php echo ($canDownloadArchive)?count($transfer->files):'1' ?>">
                            <table class="fs-table files">
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
                                                <span><p class="download_decryption_disabled">{tr:file_encryption_disabled}</p></span>
                                                <span class="downloadprogress"></span>
                                                <span class="remove stage1">
                                                    <a rel="nofollow" href="<?php echo empty($downloadLinks[$file->id]) ? '#' : Utilities::sanitizeOutput($downloadLinks[$file->id]) ?>" class="fs-button fs-button--small fs-button--transparent fs-button--info fs-button--no-text download" title="{tr:download_file}">
                                                        <i class="fa fa-download"></i>
                                                    </a>
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
                    <div class="fs-download__total-size">
                        <strong>{tr:size_of_selected_files}</strong>
                        <span>486mb</span>
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






























<!--    <div class="crypto_not_supported_message">-->
<!--         {tr:file_encryption_disabled}-->
<!--    </div>-->
<!---->
<!--    --><?php //if( Browser::instance()->allowStreamSaver ) { ?>
<!---->
<!--        <div class="form-check form-switch custom-control custom-switch" data-option="options">-->
<!--            <input id="streamsaverenabled" class="form-check-input" name="streamsaverenabled" type="checkbox" checked="checked" />-->
<!--            <label for="streamsaverenabled" class="form-check-label">{tr:use_streamsaver_for_download}</label>-->
<!--        </div>-->
<!--    --><?php //} ?>
<!---->
<!---->
<!--    --><?php //if($have_av) { ?>
<!--        <div class="general2 box" data-transfer-size="--><?php //echo $transfer->size ?><!--">-->
<!--            <div class="avdesc">{tr:av_results_description}-->
<!--            --><?php //foreach($transfer->files as $file) { ?>
<!--                <div class="avfile" data-avid="--><?php //echo $file->id ?><!--" >-->
<!--                    <span class="name avheader--><?php //outputBool($file->av_all_good)?><!-- ">--><?php //echo Utilities::sanitizeOutput($file->path) ?><!--</span>-->
<!--                    --><?php //if(!$file->have_avresults) { ?>
<!--                        <span class="desc">{tr:no_av_scans_performed}</span>-->
<!--                    --><?php //} else { ?>
<!--                        <table>-->
<!--                            <tr class="avresultheader">-->
<!--                                <th>{tr:performed}</th>-->
<!--                                <th>{tr:result}</th>-->
<!--                                <th>{tr:avname}</th>-->
<!--                            </tr>-->
<!--                        --><?php //foreach($file->scan_results as $res) { $resultdesc = passErrToDesc($res->passes,$res->error); ?>
<!--                            <tr class="avresult">-->
<!--                                <td class="created">--><?php //echo Utilities::sanitizeOutput(Utilities::formatDate($res->created)) ?><!--</td>-->
<!--                                <td class="result avresult--><?php //echo $resultdesc ?><!--">--><?php //echo Lang::tr($resultdesc) ?><!--</td>-->
<!--                                <td class="app_name">--><?php //echo presentAVName($res->name) ?><!--</td>-->
<!--                            </tr>-->
<!--                        --><?php //} ?>
<!--                        </table>-->
<!--                    --><?php //} ?>
<!---->
<!--                </div>-->
<!--            --><?php //} ?>
<!--            </div>-->
<!--        </div>-->
<!--    --><?php //} ?>

    <div class="transfer_is_encrypted not_displayed">
        <?php echo $isEncrypted ? 1 : 0;  ?>
    </div>

<script type="text/javascript" src="{path:js/download_page.js}"></script>
