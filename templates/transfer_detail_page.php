<?php
$nosort = false;
if(!isset($trsort))  $nosort = true;

if(!isset($status)) $status = 'available';
if(!isset($mode)) $mode = 'user';
if(!isset($transfers) || !is_array($transfers)) $transfers = array();
if(!isset($limit)) $limit = 100000;
if(!isset($offset)) $offset = 0;
if(!isset($pagerprefix)) $pagerprefix = '';
if(!isset($trsort)) $trsort = TransferQueryOrder::create();
$show_guest = isset($show_guest) ? (bool)$show_guest : false;
$extend = (bool)Config::get('allow_transfer_expiry_date_extension');
$audit = (bool)Config::get('auditlog_lifetime') ? '1' : '';
$haveNext = 0;
$havePrev = 0;


$isAdmin = false;
$showAdminExtend = false;
if (Auth::isAuthenticated()) {
    if (Auth::isAdmin()) {

        $isAdmin = true;

        if(Config::get('allow_transfer_expiry_date_extension_admin')) {
            $showAdminExtend = true;
        }
    }
}

$cgiuid = "";
if (Auth::isAuthenticated()) {
    if (Auth::isAdmin()) {

        $uid = Utilities::arrayKeyOrDefault( $_GET, 'uid', 0, FILTER_VALIDATE_INT  );
        if( $uid ) {
            $cgiuid = "&uid=".$uid;
        }
    }
}

$cgiminmax = "";
$idmin = Utilities::arrayKeyOrDefault( $_GET, 'idmin', -1, FILTER_VALIDATE_INT  );
$idmax = Utilities::arrayKeyOrDefault( $_GET, 'idmax', -1, FILTER_VALIDATE_INT  );
if( $idmin >= 0 ) {
    $cgiminmax .= "&idmin=".$idmin;
}
if( $idmax >= 0 ) {
    $cgiminmax .= "&idmax=".$idmax;
}

if (!function_exists('clickableHeader')) {

    function clickableHeader($displayName,$trsortcol,$trsort,$nosort,$title = null) {

        if( $nosort ) {
            echo $displayName;
            return;
        }

        $qa = array(
            's' => Utilities::getGETparam('s','')
        , 'transfersort' => $trsort->clickableSortValue($trsortcol)
        , 'as' => Utilities::getGETparam('as','')
        );

        if (Auth::isAuthenticated()) {
            if (Auth::isAdmin()) {

                $uid = Utilities::arrayKeyOrDefault( $_GET, 'uid', 0, FILTER_VALIDATE_INT  );
                if( $uid ) {
                    $qa["uid"] = $uid;
                }
            }
        }
        $idmin = Utilities::arrayKeyOrDefault( $_GET, 'idmin', -1, FILTER_VALIDATE_INT  );
        $idmax = Utilities::arrayKeyOrDefault( $_GET, 'idmax', -1, FILTER_VALIDATE_INT  );
        if( $idmin >= 0 ) {
            $qa["idmin"] = $idmin;
        }
        if( $idmax >= 0 ) {
            $qa["idmax"] = $idmax;
        }

        $tr_url = Utilities::http_build_query($qa);
        echo '<a href="' . $tr_url . '" ';
        if( strlen($title)) {
            echo ' title="' . $title . '" ';
        }
        echo ' >';
        echo $displayName;
        echo ' ' . $trsort->screenArrowHTML($trsortcol);
        echo '</a>';
    }
}
?>

<?php foreach($transfers as $transfer) {
    echo $transfer->id;
}
?>













<div class="fs-transfer-detail">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-transfer-detail__header">
                    <a href='javascript:history.back()' class='fs-link fs-link--circle'>
                        <i class='fa fa-angle-left'></i>
                    </a>
                    <h1>Transfer details</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-sm-12 col-md-6 col-lg-6">
                <div class="fs-transfer-detail__details">
                    <h2>Transfer info</h2>
                    <div class="fs-info fs-info--aligned">
                        <strong>Transfer sent on:</strong>
                        <span>01/01/2023</span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>Expiration date:</strong>
                        <span>08/01/2023</span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>Subject:</strong>
                        <span>Subject</span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>Message:</strong>
                        <span>Hi, here are the files you asked for. You're welcome, have fun.</span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>Language:</strong>
                        <span>English</span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>Encryption:</strong>
                        <span>Off</span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>Downloads:</strong>
                        <span>2</span>
                    </div>
                </div>
            </div>
            <div class="col col-sm-12 col-md-6 col-lg-6">
                <div class="fs-transfer-detail__files">
                    <h2>Transferred files</h2>
                    <div class="fs-transfer__list">
                        <div class="fs-transfer__files">
                            <table class="fs-table">
                                <tbody>
                                <tr>
                                    <td>
                                        <div>
                                            <span class="filename">RNP2023-052900.zip</span>
                                            <span class="filesize">578.2 kB</span>
                                            <span class="remove stage1">
                                                    <button type="button" class="fs-button fs-button--small fs-button--transparent fs-button--danger fs-button--no-text removebutton" alt="Remover arquivo">
                                                        <i class="fa fa-close"></i>
                                                    </button>
                                                </span>
                                            <span class="remove stage1">
                                                    <button type="button" class="fs-button fs-button--small fs-button--transparent fs-button--info fs-button--no-text removebutton" alt="Remover arquivo">
                                                        <i class="fa fa-download"></i>
                                                    </button>
                                                </span>
                                        </div>
                                    </td>
                                </tr>
                                </tbod
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="fs-transfer-detail__total-size">
                        <strong>Size of selected files</strong>
                        <span>486mb</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="fs-transfer-detail__recipients">
                    <h2>Recipients</h2>
                    <div class="fs-transfer__upload-recipients fs-transfer__upload-recipients--show">
                        <span>
                            Sua transferência foi enviada para os seguintes endereços de e-mail
                        </span>
                        <div class="fs-badge-list">
                            <div class="fs-badge">wrg.wrg@gmail.com</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col col-sm-12 col-md-8">
                <div class="fs-transfer-detail__link">
                    <h2>Download link</h2>
                    <div class="fs-copy">
                        <span>https://localhost/filesender/?s=download&amp;token=f7923fbd-eb16-43f3-aa45-f5639e52d3d5</span>

                        <button id="copy-to-clipboard" type="button" class="fs-button">
                            <i class="fa fa-copy"></i>
                            Copiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="fs-transfer-detail__options">
                    <h2>Selected options for this transfer</h2>
                    <div class="row">
                        <div class="col col-sm-12 col-md-12 col-lg-6">
                            <h3>Selected transfer options</h3>
                            <div class="fs-transfer-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-1">
                                        redirect after upload &nbsp;&nbsp;&nbsp; <small>redirecting link: <a href="">https://company.link/success-page</a></small>
                                    </label>
                                    <input id="check-1" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-transfer-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-2">
                                        include me as a recipient
                                    </label>
                                    <input id="check-2" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-transfer-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-3">
                                        slow internet upload
                                    </label>
                                    <input id="check-3" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-transfer-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-32">
                                        recipient must login to download
                                    </label>
                                    <input id="check-32" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col col-sm-12 col-md-12 col-lg-6">
                            <h3>Selected notification options</h3>
                            <div class="fs-transfer-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-4">
                                        email me when upload is done
                                    </label>
                                    <input id="check-4" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-transfer-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-5">
                                        email me upon downloads
                                    </label>
                                    <input id="check-5" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-transfer-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-6">
                                        email me when transfer is expired
                                    </label>
                                    <input id="check-6" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-transfer-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-62">
                                        email me daily statistics
                                    </label>
                                    <input id="check-62" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-transfer-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-63">
                                        email recipient when download is complete
                                    </label>
                                    <input id="check-63" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{path:js/transfer_detail_page.js}"></script>
