<?php
if(!isset($mode)) $mode = 'user';
if(!isset($guests)) $guests = array();
if(!isset($guest)) $guest = null;
if(!isset($filtered)) $filtered = array();
if(!isset($guest_id)) $guest_id = 0;

$guest_id = Utilities::arrayKeyOrDefault($_GET, 'guest_id',  0, FILTER_VALIDATE_INT  );

$guest = null;
$found = 0;
if ($guest_id) {
    try {
        $guest = Guest::fromId($guest_id);
        $found = true;
    }
    catch( Exception $e ) {
    }
}

$user = Auth::user();
if( !Auth::isAuthenticated() || !$guest || $guest->userid != $user->id ) {
    $found = false;
}

?>


<?php // short out if the guest id is not valid  ?>
<?php if ( !$found ) { ?>
    <div class="fs-invitation-detail" data-id="<?php echo $guest_id; ?>">
        <div class="container">
            <div class="row">
                <h1>{tr:guest_not_found}</h1>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{path:js/invitation_detail_page.js}"></script>
<?php return; } ?>


<div class="fs-invitation-detail" data-id="<?php echo $guest_id; ?>">
    <div class="container">
        <div class="row">
            <div class="col">
                <a id='fs-back-link' class='fs-link fs-link--primary fs-link--no-hover fs-back-link'>
                    <i class='fi fi-chevron-left'></i>
                    <span>{tr:new_invitation_back}</span>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="fs-invitation-detail__header mt-5">
                    <h1>{tr:invitation_details}</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                <div class="fs-invitation-detail__recipients">
                    <h4>{tr:recipient}</h2>
                    <p>
                        {tr:your_invitation_was_sent_to}:
                    </p>
                    <div>
                        <div class="fs-badge">
                            <a class="fs-link fs-link--no-hover" href="mailto:<?php echo Template::sanitizeOutputEmail($guest->email) ?>"><?php echo Template::sanitizeOutputEmail($guest->email) ?></a>
                        </div>
                    </div>
                    <?php if($mode == 'user' && $guest->status == 'available') { ?>
                        <ul class="fs-list fs-list--inline">
                            <li>
                                <button type="button" class="fs-button remind">
                                    <i class="fa fa-repeat"></i>
                                    <span>{tr:send_a_reminder}</span>
                                </button>
                            </li>
                            <li>
                                <button type="button" class="fs-button forward">
                                    <i class="fa fa-envelope-o"></i>
                                    <span>{tr:resend_invitation}</span>
                                </button>
                            </li>
                        </ul>
                    <?php } ?>
                </div>
                <div class="fs-invitation-detail__information">
                    <div class="fs-info fs-info--aligned">
                        <strong>{tr:invitation_sent_on}</strong>
                        <span>
                            <?php echo Utilities::formatDate($guest->created) ?>
                        </span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>{tr:expiration_date}</strong>
                        <span>
                            <?php echo $guest->getOption(GuestOptions::DOES_NOT_EXPIRE) ? Lang::tr('never') : Utilities::formatDate($guest->expires) ?>
                        </span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>{tr:subject}</strong>
                        <span>
                            <?php
                                if ($guest->subject) {
                                    echo Template::replaceTainted($guest->subject);
                                } else {
                                    echo '-';
                                }
                            ?>
                        </span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>{tr:message}</strong>
                        <span>
                            <?php
                                if ($guest->subject) {
                                    echo Template::replaceTainted($guest->message);
                                } else {
                                    echo '-';
                                }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                <div class="fs-invitation-detail__guest-list">
                    <h4>{tr:invitation_guest_transfer_linked}</h2>

                    <?php
                        $transfers = Transfer::fromGuest($guest);
                        Template::display('transfers_table', array('transfers' => $transfers, 'show_guest' => true));
                    ?>
                </div>
            </div>
        </div>

        <?php if ($guest->options || $guest->transfer_options) { ?>
            <div class="row">
                <div class="col">
                    <div class="fs-invitation-detail__options">
                        <h4>{tr:invitation_selected_options}</h2>
                        <div class="row">
                            <?php if ($guest->options) { ?>
                                <div class="col col-sm-12 col-md-6 mt-3">
                                    <strong>{tr:advanced_invitation_options}</strong>

                                    <?php
                                    $optionshtml = "";
                                    if(count(array_filter($guest->options))) {
                                        foreach (array_keys(array_filter($guest->options)) as $o) {
                                            $optionshtml .= "<div class='fs-invitation-detail__check'>";
                                            $optionshtml .= "<i class='fi fi-valid'></i>";
                                            $optionshtml .= "<span>".Lang::tr($o)."</span>";
                                            $optionshtml .= "</div>";
                                        }
                                    }

                                    if($optionshtml != '') {
                                        echo $optionshtml;
                                    } else {
                                        echo Lang::tr('none') ;
                                    }
                                    ?>
                                </div>
                            <?php } ?>
                            <?php if ($guest->transfer_options) { ?>
                                <div class="col col-sm-12 col-md-6 mt-3">
                                    <strong>{tr:advanced_transfer_options}</strong>

                                    <?php
                                    $optionshtml = "";
                                    if(count(array_filter($guest->transfer_options))) {
                                        foreach (array_keys(array_filter($guest->transfer_options)) as $o) {
                                            if ($o == TransferOptions::STORAGE_CLOUD_S3_BUCKET) {
                                                // this option will never be shown to the user
                                            } else {

                                                $optionshtml .= "<div class='fs-invitation-detail__check'>";
                                                $optionshtml .= "<i class='fi fi-valid'></i>";
                                                $optionshtml .= "<span>".Lang::tr($o)."</span>";
                                                $optionshtml .= "</div>";
                                            }
                                        }
                                    }

                                    if($optionshtml != '') {
                                        echo $optionshtml;
                                    } else {
                                        echo Lang::tr('none') ;
                                    }
                                    ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php if ($guest->status == 'available') { ?>
            <div class="row">
                <div class="col-12">
                    <div class="fs-invitation-detail__actions">
                        <button type="button" class="fs-button fs-button--inverted delete">
                            <i class="fi fi-trash"></i>
                            <span>{tr:delete_invitation}</span>
                        </button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script type="text/javascript" src="{path:js/invitation_detail_page.js}"></script>
