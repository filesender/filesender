<?php

$cgiuid = "";
$section = 'invite';
$sections = array('invite','current','transfers');

if(array_key_exists('as', $_REQUEST))
    $section = $_REQUEST['as'];
if(!strlen($section)) {
    $section = 'invite';
}
if(!in_array($section, $sections)) {
    throw new GUIUnknownAdminSectionException($section);
}


// allow a part of the page to handle a guest option
// and avoid it being handled a second time by other code
$guest_options_handled = array();

$new_guests_can_only_send_to_creator = false;


Guest::forcedOptions();
foreach (Guest::allOptions() as $name => $dfn) {
    if( $name == 'can_only_send_to_me' ) {
        $new_guests_can_only_send_to_creator_default = $dfn['default'];
        if (in_array('available', $dfn) && !$dfn['available']) {
            if (in_array('default', $dfn)) {
                $new_guests_can_only_send_to_creator = $dfn['default'];
            }
        }
    }
}


//
// Note that this needs to be called inside a div with guest_options or transfer_options
//
$displayoption = function($name, $cfg, $transfer = false)
use ( $new_guests_can_only_send_to_creator,
    $new_guests_can_only_send_to_creator_default )
{
    // don't show the option for get_a_link if they can't use it.
    if($name == 'get_a_link' && $new_guests_can_only_send_to_creator ) {
        return;
    }
    // don't allow guests to get a link to their upload
    if($name == 'get_a_link' ) {
        return;
    }

    $default = $cfg['default'];
    if(Auth::isSP()) {
        if($transfer) {
            $default = Auth::user()->defaultTransferOptionState($name);
        } else {
            $default = Auth::user()->defaultGuestOptionState($name);
        }
    }
    if( $name == 'get_a_link' && $new_guests_can_only_send_to_creator_default ) {
        $default = false;
    }
    $checked = $default ? 'checked="checked"' : '';
    $extraDivAttrs = '';
    $hidden = '';
    if($transfer && in_array($name, array(TransferOptions::REDIRECT_URL_ON_COMPLETE))) {
        echo '<div class="fs-switch" data-option="'.$name.'" '. $extraDivAttrs .'>';
        echo '<input id="'.$name.'" name="'.$name.'" type="checkbox">';
        echo '<label for="'.$name.'">'.Lang::tr($name).'</label>';
        echo '</div>';
    } else {
        $lockClassLabel = '';
        $lockClass = '';
        if($name == 'get_a_link' || $name == 'can_only_send_to_me') {
            $lockClass = 'get_a_link_lock';
        }

        echo '<div class="fs-switch '.$lockClass.'" '. $extraDivAttrs .'>';
        echo '<input id="'.$name.'" name="'.$name.'" type="checkbox" '.$checked.' />';
        echo '<label for="'.$name.'" class="form-check-label '.$lockClassLabel.'">'.Lang::tr($name).'</label>';
        echo '</div>';
    }
};


?>

<div class="fs-new-invitation">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-new-invitation__header">
                    <a id='fs-back-link' class="fs-link fs-link--circle">
                        <i class='fa fa-angle-left'></i>
                    </a>
                    <h1>{tr:new_invitation}</h1>
                </div>
            </div>
        </div>

        <div class="fs-new-invitation__form">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-8 offset-md-2 col-lg-8 offset-lg-2">
                    <div class="fs-new-invitation__data">
                        <h2>
                            {tr:invitation_title}
                        </h2>
                        <form action="">
                            <div class="row">
                                <div id="send_voucher" class="col-12">
                                    <div>
                                        <div class="fs-input-group">
                                            <label for="to" class="mandatory">
                                                {tr:send_invitation_to}
                                            </label>

                                            <div>
                                                <input id="to" name="to" type="email"
                                                       class="fs-input"
                                                       multiple title="{tr:email_separator_msg}" value="" placeholder="{tr:enter_to_email}" />
                                            </div>
                                        </div>

                                        <div class="fs-new-invitation__recipients recipients"></div>
                                    </div>

                                    <div class="fs-input-group">
                                        <label for="subject">
                                            {tr:subject}
                                        </label>
                                        <input id="subject" name="subject" type="text" placeholder="{tr:subject}">
                                    </div>

                                    <div class="fs-input-group">
                                        <label for="message">
                                            {tr:message}
                                        </label>
                                        <textarea id="message" name="message" rows="4" placeholder="{tr:optional_message}"></textarea>
                                    </div>

                                    <label class="invalid" id="message_can_not_contain_urls" style="display:none;">{tr:message_can_not_contain_urls}</label>

                                    <h2>{tr:transfer_settings}</h2>

                                    <div class="guest_options options_box">
                                        <?php
                                        foreach(Guest::availableOptions(true) as $name => $cfg) {
                                            if( $name == 'does_not_expire' ) {
                                                $guest_options_handled[$name] = 1;
                                                $displayoption($name, $cfg, false);
                                            }
                                        }
                                        ?>
                                    </div>
 
                                    <div class="guest_options options_box">
                                    <?php
                                    foreach(Guest::availableOptions() as $name => $cfg) {
                                        if( $name == 'can_only_send_to_me' || $name == 'valid_only_one_time') {
                                            $displayoption($name, $cfg, false);
                                        }
                                    }
                                    ?>
                                    </div>

                                    <div class="fs-select guest-expires-select-by-days">
                                        <label for="expires-select">
                                            {tr:expires_after}
                                        </label>
                                        <select id="expires-select" name="expires-select">
                                            <option value="7" selected>7 {tr:days}</option>
                                            <option value="15">15 {tr:days}</option>
                                            <option value="30">30 {tr:days}</option>
                                            <option value="40">40 {tr:days}</option>
                                        </select>
                                    </div>

                                    <div class="fs-select guest_options options_box guest-expires-select-by-picker">
                                        <label for="expires" id="datepicker_label" class="mandatory">{tr:expiry_date}:</label>
                        
                                        <input id="expires" name="expires" type="text" autocomplete="off"
                                               title="<?php echo Lang::trWithConfigOverride('dp_date_format_hint')->r(array('max' => Config::get('max_guest_days_valid'))) ?>"
                                               data-epoch="<?php echo Transfer::getDefaultExpire() ?>"
                                        />
                                    </div>

                                    <div class="fs-collapse">
                                        <button type="button" class="fs-button fs-collapse__open">
                                            <i class="fa fa-chevron-down"></i>
                                            <span>{tr:show_advanced_settings}</span>
                                        </button>
                                        <button type="button" class="fs-button fs-collapse__close">
                                            <i class="fa fa-chevron-up"></i>
                                            <span>{tr:hide_advanced_settings}</span>
                                        </button>
                                        <div class="fs-collapse__content">
                                            <div class="fs-collapse__column">
                                                <strong>{tr:guest_transfer_settings}</strong>

                                                <div class="guest_options">
                                                    <?php foreach(Guest::availableOptions(false) as $name => $cfg) {
                                                        if( $name != 'can_only_send_to_me' && $name != 'valid_only_one_time') {
                                                            $displayoption($name, $cfg, false);
                                                        }
                                                    } ?>
                                                </div>

                                                <div class="guest_options">
                                                <?php if(count(Guest::availableOptions(true))) {
                                                    foreach(Guest::availableOptions(true) as $name => $cfg) {
                                                        if( !array_key_exists($name, $guest_options_handled)) {
                                                            if( $name != 'can_only_send_to_me' && $name != 'valid_only_one_time') {
                                                                $displayoption($name, $cfg, false);
                                                            }
                                                        }
                                                    }
                                                } ?>
                                                </div>
                                            </div>
                                            <div class="fs-collapse__column">
                                                <strong>{tr:guest_email_settings}</strong>

                                                <div class="transfer_options">
                                                    <?php foreach(Transfer::availableOptions(false) as $name => $cfg) {
                                                        if( $name != 'can_only_send_to_me' && $name != 'valid_only_one_time') {
                                                            $displayoption($name, $cfg, true);
                                                        }
                                                    } ?>
                                                </div>

                                                <div class="transfer_options">
                                                <?php if(count(Transfer::availableOptions(true))) {
                                                    foreach(Transfer::availableOptions(true) as $name => $cfg) {
                                                        if( $name != 'can_only_send_to_me' && $name != 'valid_only_one_time') {
                                                            $displayoption($name, $cfg, true);
                                                        }
                                                    }
                                                } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="fs-new-invitation__actions">
                                        <button type="button" class="fs-button send">
                                            <i class="fa fa-send"></i>
                                            <span>{tr:send_invitation}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="fs-new-invitation__success">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-8 offset-md-2 col-lg-8 offset-lg-2">
                    <div class="fs-new-invitation__success-info">
                        <h2>
                            {tr:invitation_sent}
                        </h2>
                        <p>
                            {tr:invitation_was_sent_to}
                        </p>
                        <br />
                        <div class="fs-new-invitation__recipients-result">
                            <div class="fs-badge-list">
                            </div>
                        </div>
                        <br />
                        <p>
                            {tr:invitation_expires_in} <span id="expires-days">7</span> {tr:days}.
                        </p>
                        <br />
                        <ul class="fs-list fs-list--inline">
<!--                            <li>-->
<!--                                <a href="" id="detail-link" class="fs-button">-->
<!--                                    <i class="fa fa-envelope-open"></i>-->
<!--                                    <span>{tr:see_invitation_details}</span>-->
<!--                                </a>-->
<!--                            </li>-->
                            <li>
                                <a href="?s=guests" class="fs-button">
                                    <i class="fa fa-envelope"></i>
                                    <span>{tr:go_to_invitations}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{path:js/new_invitation_page.js}"></script>

