<?php
$upload_options_handled = array();

$guest_can_only_send_to_creator = false;
$show_get_a_link_or_email_choice = true;

// CGI to used variables
$aupChecked = '';
if (Config::get('aup_default') || (isset($_SESSION['aup']) /*&& !$authvoucher->aVoucher()*/)) {
    $aupChecked = 'checked="checked"';
}

$crustMeterCount = 1;
if( Config::get('terasender_enabled'))
    $crustMeterCount = Config::get('terasender_worker_count');

$encryption_mandatory = Principal::isEncryptionMandatory();
$encryption_checkbox_checked = '';
$encryption_checkbox_classes = '';
$expire_time_is_editable = true;
$upload_directory_button_enabled = false;

if( !Config::get('disable_directory_upload')
    && Config::get('directory_upload_button_enabled')
    && (Browser::instance()->isChrome || Browser::instance()->isFirefox))
{
    $upload_directory_button_enabled = true;
}


if(Auth::isGuest()) {
    $guest = AuthGuest::getGuest();

    if($guest->getOption(GuestOptions::EMAIL_UPLOAD_PAGE_ACCESS)) {
        if(!$guest->last_activity || $guest->last_activity < strtotime('-1 hour')) {
            // Send mail to guest the owner of the voucher
            ApplicationMail::quickSend('guest_access_upload_page', $guest->owner, $guest);

            $guest->last_activity = time();
            $guest->save();
        }
    }
}

$need_recipients = true;
$allow_recipients = true;

foreach(Transfer::allOptions() as $name => $dfn)  {
    if($dfn['available']) continue;
    if(!$dfn['default']) continue;

    if($name == TransferOptions::ADD_ME_TO_RECIPIENTS)
        $need_recipients = false;

    if($name == TransferOptions::GET_A_LINK)
        $allow_recipients = false;

}

// If get a link is not available to the user then
// there is no real point showing the big choice to the
// user at the top of the page.
foreach(Transfer::allOptions() as $name => $dfn)  {
    if($dfn['available']) continue;
    if($name == TransferOptions::GET_A_LINK) {
        $show_get_a_link_or_email_choice = false;
    }
}

if(Auth::isGuest()) {
    $show_get_a_link_or_email_choice = false;
    $allow_recipients = true;

    if($guest->getOption(GuestOptions::CAN_ONLY_SEND_TO_ME)) {
        $guest_can_only_send_to_creator = true;
        $allow_recipients = false;
    }
}

/**
 * @param optionsToFilter is an array of options which we do not want to
 *                        show in the default panels on the left. This allows
 *                        some options to be displayed in other locations on the page.
 */
$displayoption = function( $name, $cfg, $disable = false, $forcedOption = false, $optionsToFilter = array('hide_sender_email')) use ($guest_can_only_send_to_creator) {
    $text = in_array($name, array(TransferOptions::REDIRECT_URL_ON_COMPLETE));

    if( in_array($name, $optionsToFilter)) {
        return;
    }
    if( $name == "get_a_link" ) {
        return;
    }

    $default = $cfg['default'];
    if( !$forcedOption ) {
        if(Auth::isSP() && !$text)
            $default = Auth::user()->defaultTransferOptionState($name);
    }

    $checked = $default ? 'checked="checked"' : '';
    $disabled = $disable ? 'disabled="disabled"' : '';
    $extraDivAttrs = '';
    if(Auth::isGuest() && $disable) {
        if( Config::get('guest_upload_page_hide_unchangable_options')) {
            $extraDivAttrs .= ' hidden="true" ';
        }
    }

    // if they are a guest and can only send to the user
    // who sent the guest voucher to them then don't even
    // show the get a link option.
    if(Auth::isGuest() && $name == 'get_a_link') {
        if($name == 'get_a_link' && $guest_can_only_send_to_creator ) {
            return;
        }
    }

    echo '<div data-option="'.$name.'" '. $extraDivAttrs .'>';

    if($text) {
        echo '<div class="fs-input-group">';
        echo '    <label for="'.$name.'">'.Lang::tr($name).'</label>';
        echo '    <input id="'.$name.'" name="'.$name.'" type="text" value="'.htmlspecialchars($default).'" '.$disabled.'>';
        echo '</div>';

    } else {
        echo '<div id="fs-transfer__add-me-to-recipients" class="fs-switch">';
        echo '  <input id="'.$name.'" name="'.$name.'" type="checkbox" '.$checked.' '.$disabled.' />';
        echo '  <label for="'.$name.'">'.Lang::tr($name).'</label>';
        echo '</div>';
    }

    if($name == TransferOptions::ENABLE_RECIPIENT_EMAIL_DOWNLOAD_COMPLETE)
        echo '<div class="info message">'.Lang::tr('enable_recipient_email_download_complete_warning').'</div>';
    if($name == TransferOptions::WEB_NOTIFICATION_WHEN_UPLOAD_IS_COMPLETE && Browser::instance()->isFirefox)
        echo '<div class="info message"><a class="enable_web_notifications" href="#">'.Lang::tr('click_to_enable_web_notifications').'</a></div>';

    echo '</div>';
};


if( $encryption_mandatory ) {
    $encryption_checkbox_checked = ' checked="checked"  disabled="disabled" ';
    $encryption_checkbox_classes = '';
}

if(Auth::isGuest()) {
    $guest = AuthGuest::getGuest();
    if( $guest->guest_upload_expire_read_only ) {
        $expire_time_is_editable = false;
    }
}

$userHasGALPreference = false;
if( !Auth::isGuest()) {
    $user = Auth::User();
    if( $user->save_transfer_preferences ) {
        $ops = (array)$user->transfer_preferences;
        if( array_key_exists( 'get_a_link', $ops )) {
            $userHasGALPreference = $ops["get_a_link"];
        }
    }
}


$possibleExpireDays = array( 7, 15, 30, 40 );
$expireDays = array_filter(array( 7, 15, 30, 40 ), function($k) {
    return $k < Config::get('max_transfer_days_valid');
});


?>

<?php if( Config::get('read_only_mode')) { ?>
    <div class="box">
        {tr:read_only_mode}
    </div>
<?php
    return;
}
?>

<div class="container">
    <form id="upload_form"
          class="fs-form"
          enctype="multipart/form-data"
          accept-charset="utf-8"
          method="post"
          autocomplete="off"
          data-need-recipients="<?php echo $need_recipients ? '1' : '' ?>"
          data-user-has-gal-preference="<?php echo $userHasGALPreference ? '1' : '0' ?>"
    >

        <div class="fs-transfer">
            <h1>
                {tr:transfer_files}
            </h1>

            <div class="fs-transfer__step fs-transfer__step--active" data-step="1">
                <div class="row">
                    <div class="col-12">
                        <div class="fs-transfer__droparea">
                            <input id="files" class="fs-transfer__input" type="file" name="file" multiple />
                            <label for="files">
                                <strong>{tr:start_selection_files}</strong>
                                <span class="fs-button">
                                <i class="fa fa-plus"></i>
                                {tr:drag_drop_select}
                            </span>
                            </label>

                            <?php if ($upload_directory_button_enabled) { ?>
                                <div class="fs-transfer__directory">
                                    <span>
                                        {tr:or}
                                    </span>
                                    <label for="selectdir">
                                        <span class="fs-button">
                                            <i class="fa fa-folder"></i>
                                            {tr:send_an_entire_directory}
                                        </span>
                                    </label>
                                    <input type="file" name="selectdir" id="selectdir" class="fs-transfer__input" webkitdirectory directory multiple mozdirectory />
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!--            <div class="row">-->
                <!--                <div class="col-12">-->
                <!--                    --><?php //if (Config::get('upload_graph_bulk_display')) { ?>
                <!--                        <div class="row">-->
                <!--                            <div class="col-12">-->
                <!--                                <div id="graph" class="uploadbulkgraph"><div id="graphDiv"><canvas id="speedChart"></canvas></div></div>-->
                <!--                                <script type="text/javascript" src="{path:lib/chart.js/chart.min.js}"></script>-->
                <!--                                <script type="text/javascript" src="{path:js/graph.js}"></script>-->
                <!--                            </div>-->
                <!--                        </div>-->
                <!--                    --><?php //} ?>
                <!--                </div>-->
                <!--            </div>-->
            </div>

            <div class="fs-transfer__step" data-step="2">
                <div class="row">
                    <div class="col-12">
                        <div class="fs-transfer__list">
                            <h6>
                                {tr:selected_files}
                            </h6>
                            <div class="fs-transfer__files">
                                <table class="fs-table">
                                    <thead hidden="true">
                                    <tr>
                                        <th>{tr:date}</th>
                                        <th>{tr:message}</th>
                                    </tr>
                                    </thead>
                                    <tbody id="fileslistdirectparent">
                                    <tr class="tpl"> <!-- becomes class="file" when cloned -->
                                        <td>
                                            <div>
                                                <span class="filename"></span>
                                                <span class="filesize"></span>
                                                <span class="remove stage1">
                                                <button type="button" class="fs-button fs-button--small fs-button--transparent fs-button--danger fs-button--no-text removebutton" alt="{tr:click_to_delete_file}">
                                                    <i class="fa fa-close"></i>
                                                </button>
                                            </span>
                                            </div>
                                            <div>
                                                <span class="error"></span>
                                            </div>
                                            <div>
                                                <?php if (Config::get('upload_display_per_file_stats')) { ?>
                                                    <span class="crustmeters stage3">
                                                    <?php for( $i=0; $i < $crustMeterCount; $i++ ) { ?>
                                                        <div class="crust crust<?php echo $i ?>"  >
                                                            <a class="crust_meter" href="#">
                                                                <div class="label crustage uploadthread"></div>
                                                            </a>
                                                            <a class="crust_meter_bytes" href="#">
                                                                <div class="label crustbytes uploadthread"></div>
                                                            </a>
                                                        </div>
                                                    <?php } ?>
                                                </span>
                                                <?php } ?>
                                            </div>
                                            <div>
                                            <span class="progressbar">
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </span>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="fs-transfer__add-buttons">
                                <label for="files">
                                    <span class="fs-button">
                                        <i class="fa fa-plus"></i>
                                        {tr:add_files}
                                    </span>
                                </label>
                                <?php if ($upload_directory_button_enabled) { ?>
                                    <label for="selectdir">
                                        <span class="fs-button">
                                            <i class="fa fa-folder"></i>
                                            {tr:add_directory}
                                        </span>
                                    </label>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <hr />

                <div class="row">
                    <div class="col-12">
                        <div class="fs-transfer__actions">
                            <div class="fs-transfer__left">
                                <button type="button" class="fs-button fs-button--danger fs-transfer__clear-all">
                                    <i class="fa fa-trash"></i>
                                    {tr:clear_all}
                                </button>
                            </div>
                            <div class="fs-transfer__right">
                                <button type="button" id="fs-transfer__next-step" class="fs-button fs-button--info fs-button--icon-right fs-transfer__next">
                                    {tr:next}
                                    <i class="fa fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fs-transfer__step" data-step="3">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                        <div class="fs-transfer__list fs-transfer__list--full">
                            <h6>
                                {tr:selected_files}
                            </h6>
                            <div class="fs-transfer__files">
                                <table></table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                        <div class="fs-transfer__options">
                            <div class="fs-transfer__send-options">
                                <div class="row">
                                    <div class="col-12">
                                        <h5>
                                            {tr:choose_files}
                                        </h5>

                                        <?php if($show_get_a_link_or_email_choice) { ?>

                                            <div class="fs-radio-group">
                                                <input type="radio" id="get_a_link" name="transfer-type" value="transfer-link" class="get_a_link_top_selector">

                                                <label for="get_a_link" class="fs-radio">
                                                    <div class="fs-radio__option">
                                                        <span class="fs-radio__circle"></span>
                                                        <span class="fs-radio__text">
                                                            {tr:a_transfer_link}
                                                         </span>
                                                    </div>
                                                    <span class="fs-radio__info">
                                                        - {tr:a_transfer_link_tip}
                                                    </span>
                                                </label>
                                            </div>
                                        <?php } ?>

                                        <div class="fs-radio-group">
                                            <input type="radio" id="transfer-email" name="transfer-type" value="transfer-email">

                                            <label for="transfer-email" class="fs-radio">
                                                <div class="fs-radio__option">
                                                    <span class="fs-radio__circle"></span>
                                                    <span class="fs-radio__text">
                                                        {tr:an_email}
                                                    </span>
                                                </div>
                                                <span class="fs-radio__info">
                                                    - {tr:an_email_tip}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="fs-transfer__transfer-fields <?php if(!$show_get_a_link_or_email_choice) { echo 'fs-transfer__transfer-fields--show'; } ?>">
                                <hr />

                                <div class="row">
                                    <div class="col-12">
                                        <div data-related-to="emailfrom">

                                            <?php $emails = Auth::isGuest() ? array(AuthGuest::getGuest()->email) : Auth::user()->email_addresses ?>

                                            <?php if (count($emails) > 1) { ?>
                                                <div class="fs-select">
                                                    <label for="form">
                                                        {tr:from}
                                                    </label>
                                                    <select id="from" name="from">
                                                        <?php foreach ($emails as $email) { ?>
                                                            <option><?php echo Template::sanitizeOutputEmail($email) ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            <?php } else { ?>
                                                <div class="fs-input-group fs-input-group--hide" data-transfer-type="transfer-email">
                                                    <label for="from">
                                                        {tr:from}
                                                    </label>

                                                    <input name="to" id="to" type="email"
                                                           title="{tr:from}"
                                                           value=""
                                                           placeholder="<?php echo Template::sanitizeOutputEmail($emails[0]) ?>" disabled />

                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <?php
                                            $ops = Transfer::availableOptions();
                                            if( array_key_exists( 'hide_sender_email', $ops )) {
                                                $displayoption('hide_sender_email', $ops['hide_sender_email'], Auth::isGuest(), false, array() );
                                            }
                                        ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <?php if($allow_recipients) { ?>
                                            <div data-related-to="message">
                                                <?php if(Auth::isGuest() && AuthGuest::getGuest()->getOption(GuestOptions::CAN_ONLY_SEND_TO_ME)) { ?>
                                                    <div class="fs-input-group fs-input-group--hide" data-transfer-type="transfer-email">
                                                        <label for="to">
                                                            {tr:send_transfer_to}
                                                        </label>

                                                        <?php echo AuthGuest::getGuest()->user_email ?>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="fs-input-group fs-input-group--hide" data-transfer-type="transfer-email">
                                                        <label for="to">
                                                            {tr:send_transfer_to}
                                                        </label>

                                                        <div>
                                                            <input name="to" id="to" type="email"
                                                                   multiple title="{tr:email_separator_msg}"
                                                                   value=""
                                                                   placeholder="{tr:enter_to_email}" />
                                                        </div>
                                                    </div>

                                                    <div class="fs-transfer__recipients recipients"></div>
                                                <?php } ?>
                                            </div>

                                            <div data-related-to="message">
                                                <div class="fs-input-group">
                                                    <label for="subject">
                                                        {tr:subject}
                                                    </label>
                                                    <input name="subject" id="subject" type="text"
                                                           title="{tr:subject}"
                                                           value=""
                                                           placeholder="{tr:enter_to_subject}" />
                                                </div>

                                                <label class="invalid" id="message_can_not_contain_urls">{tr:message_can_not_contain_urls}</label>
                                                <label class="invalid" id="password_can_not_be_part_of_message_warning">
                                                    {tr:password_can_not_be_part_of_message_warning}
                                                </label>
                                                <label class="invalid" id="password_can_not_be_part_of_message_error">
                                                    {tr:password_can_not_be_part_of_message_error}
                                                </label>
                                            </div>

                                            <div data-related-to="message">
                                                <div class="fs-input-group">
                                                    <label for="message">
                                                        {tr:message}
                                                    </label>
                                                    <textarea id="message" name="message" rows="3" placeholder="{tr:optional_message}"></textarea>
                                                </div>

                                                <label class="invalid" id="message_can_not_contain_urls">{tr:message_can_not_contain_urls}</label>
                                                <label class="invalid" id="password_can_not_be_part_of_message_warning">
                                                    {tr:password_can_not_be_part_of_message_warning}
                                                </label>
                                                <label class="invalid" id="password_can_not_be_part_of_message_error">
                                                    {tr:password_can_not_be_part_of_message_error}
                                                </label>
                                            </div>
                                        <?php } ?> <!-- closing if($allow_recipients) -->
                                        <?php if(Auth::isGuest()) { ?>
                                            <div>
                                                <input type="hidden" name="guest_token" value="<?php echo AuthGuest::getGuest()->token ?>" />
                                                <input type="hidden" id="guest_options" value="<?php echo Utilities::sanitizeOutput(json_encode(AuthGuest::getGuest()->options)) ?>" />
                                                <input type="hidden" id="guest_transfer_options" value="<?php echo Utilities::sanitizeOutput(json_encode(AuthGuest::getGuest()->transfer_options)) ?>" />
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 lifted_options">
                                        <div data-related-to="message">
                                            <?php
                                            foreach(Transfer::availableOptions(false) as $name => $cfg) {
                                                if( $name == 'add_me_to_recipients' ) {
                                                    $upload_options_handled[$name] = 1;
                                                    $forcedOption = false;
                                                    $displayoption($name, $cfg, Auth::isGuest(), $forcedOption,array("message"));
                                                }
                                            }
                                            foreach(Transfer::availableOptions(false) as $name => $cfg) {
                                                if( !array_key_exists($name,$upload_options_handled)) {
                                                    $displayoption($name, $cfg, Auth::isGuest());
                                                }
                                            }
                                            
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="fs-transfer__transfer-settings <?php if(!$show_get_a_link_or_email_choice) { echo 'fs-transfer__transfer-settings--show'; } ?>">
                                <hr />



                                <strong>Transfer settings</strong>

                                <?php if(Config::get('encryption_enabled')) {  ?>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="fs-switch" data-related-to="encryption">
                                                <input id="encryption" name="encryption" type="checkbox" <?php echo $encryption_checkbox_checked ?> />
                                                <label for="encryption">
                                                    {tr:encrypt_files_with_password}
                                                </label>
                                            </div>

                                            <div id="encgroup1" class="fs-transfer__password">
                                                <div class="fs-transfer__password-top" id="encryption_password_container">
                                                    <div class="row">
                                                        <div class="col-12 col-sm-12 col-md-7">
                                                            <div class="fs-input-group">
                                                                <input type="text" id="encryption_password" name="encryption_password" placeholder="{tr:enter_your_password}">
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-sm-12 col-md-5">
                                                            <div class="fs-transfer__generate-password">
                                                                <span>{tr:or}</span>
                                                                <a id="encryption_generate_password" href="javascript:void(0);" class="fs-link">{tr:generate_password}</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="fieldcontainer" id="encryption_password_show_container">  
                                                    <input id="encryption_show_password" name="encryption_show_password" type="checkbox" checked="1" >  
                                                    <label class="cursor" for="encryption_show_password">{tr:file_encryption_show_password}</label>
                                                </div>
                                                
                                                <div class="fs-transfer__password-bottom">
                                                    <small>{tr:password_share_tip}</small>
                                                </div>

                                                <div class="fieldcontainer passwordvalidation" id="encryption_password_container_too_short_message">
                                                    <small>{tr:file_encryption_password_too_short}</small>
                                                </div>
                                                <div class="fieldcontainer passwordvalidation" id="encryption_password_container_must_have_numbers_message">
                                                    <small>{tr:file_encryption_password_must_have_numbers}</small>
                                                </div>
                                                <div class="fieldcontainer passwordvalidation" id="encryption_password_container_must_have_upper_and_lower_case_message">
                                                    <small>{tr:file_encryption_password_must_have_upper_and_lower_case}</small>
                                                </div>
                                                <div class="fieldcontainer passwordvalidation" id="encryption_password_container_must_have_special_characters_message">
                                                    <small>{tr:file_encryption_password_must_have_special_characters}</small>
                                                </div>
                                                <div class="fieldcontainer passwordvalidation" id="encryption_password_container_can_have_text_only_min_password_length_message">
                                                    <small>{tr:encryption_password_container_can_have_text_only_min_password_length_message}</small>
                                                </div>
                                                <div class="fieldcontainer" id="encryption_description_disabled_container">
                                                    <small>{tr:file_encryption_description_disabled}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>


                                <div class="row">
                                    <div class="col-12">
                                        <div class="fs-select">
                                            <label for="expires-select">
                                                {tr:expires_after}
                                            </label>
                                            <select id="expires-select" name="expires-select">
                                                <?php foreach( $expireDays as $v ) { ?>
                                                    <option value="<?php echo $v ?>" selected><?php echo $v ?> {tr:days}</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="fs-collapse">
                                            <button type="button" class="fs-button fs-collapse__open">
                                                <i class="fa fa-chevron-down"></i>
                                                <span>
                                                    {tr:show_advanced_settings}
                                                </span>
                                            </button>
                                            <button type="button" class="fs-button fs-collapse__close">
                                                <i class="fa fa-chevron-up"></i>
                                                <span>
                                                    {tr:hide_advanced_settings}
                                                </span>
                                            </button>
                                            <div class="fs-collapse__content">
                                                <div class="row">
                                                    <div class="col-12 basic_options">
                                                        <strong>
                                                            {tr:advanced_upload_settings}
                                                        </strong>

                                                        <?php
                                                            foreach(Transfer::availableOptions(false) as $name => $cfg) {
                                                                if( !array_key_exists($name,$upload_options_handled)) {
                                                                    $displayoption($name, $cfg, Auth::isGuest());
                                                                }
                                                            }

                                                            foreach(Transfer::availableOptions(true) as $name => $cfg)  {
                                                                if( !array_key_exists($name,$upload_options_handled)) {
                                                                    $displayoption($name, $cfg, Auth::isGuest());
                                                                }
                                                            }
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php if(count(Transfer::availableOptions(true)) || (Config::get('terasender_enabled') && Config::get('terasender_advanced'))) { ?>
                                                    <div class="row">
                                                        <div class="col-12 advanced_options">
                                                            <strong>
                                                                {tr:terasender_settings}
                                                            </strong>
                                                            <?php if (Config::get('terasender_enabled') && Config::get('terasender_advanced')) { ?>
                                                                <div class="fs-input-group fs-input-group--vertical">
                                                                    <label for="terasender_worker_count">
                                                                        {tr:terasender_worker_count}
                                                                    </label>

                                                                    <input id="terasender_worker_count" name="terasender_worker_count" type="text" value="<?php echo Config::get('terasender_worker_count') ?>"/>
                                                                </div>
                                                            <?php } ?>
                                                            <?php if (Config::get('terasender_enabled') && Config::get('terasender_disableable')) {
                                                                $displayoption('disable_terasender', array('default'=>false), false);
                                                            }?>
                                                        </div>
                                                    </div>
                                                <?php } /* End of advanced settings div. */ ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="hidden_options">
                                    <?php foreach(Transfer::forcedOptions() as $name => $cfg) {
                                        $allowed = (array)Config::get("transfer_options_not_available_to_export_to_client");
                                        if( in_array($name,$allowed)) {
                                            $displayoption($name, $cfg, Auth::isGuest(),true);
                                        }
                                    } ?>
                                </div>

                                <?php if (Config::get('aup_enabled')) { ?>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="fs-switch">
                                                <input id="aup" name="aup" type="checkbox" <?php echo $aupChecked; ?> value="true" required />
                                                <label for="aup">
                                                    {tr:accepttoc}
                                                </label>
                                            </div>

                                            <div class="aupbox">
                                                <div name="aupshowhide" id="aupshowhide" class="fs-link">
                                                    {tr:show_hide_terms}
                                                </div>

                                                <div class="terms">
                                                    {tr:aupterms}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <hr />

                <div class="row">
                    <div class="col-12">
                        <div class="fs-transfer__actions">
                            <div class="fs-transfer__left">
                                <button type="button" id="fs-transfer__previous-step" class="fs-button fs-button--info">
                                    <i class="fa fa-arrow-left"></i>
                                    {tr:previous}
                                </button>
                                <button type="button" id="fs-transfer__cancel" class="fs-button fs-button--danger">
                                    <i class="fa fa-ban"></i>
                                    {tr:cancel}
                                </button>
                            </div>
                            <div class="fs-transfer__right">
                                <button type="button" id="fs-transfer__confirm" class="fs-button fs-button--info fs-button--icon-right">
                                    {tr:confirm}
                                    <i class="fa fa-check"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fs-transfer__step" data-step="4">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                        <div class="fs-transfer__list fs-transfer__list--full">
                            <h6>
                                {tr:selected_files}
                            </h6>
                            <div class="fs-transfer__files">
                                <table class="fs-table"></table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                        <div class="fs-transfer__upload-detail fs-transfer__upload-uploading">
                            <h5>

                            </h5>
                            <div class="fs-progress-bar">
                                <strong class="fs-progress-bar__value">0%</strong>
                                <span class="fs-progress-bar__progress">
                                <span class="fs-progress-bar__indicator"></span>
                            </span>
                            </div>
                            <div class="fs-transfer__upload-size-info">
                                <div class="stats">
                                    <div class="uploaded">
                                        <span class="value">
                                            0 MB/0 MB
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="fs-transfer__upload-speed-info">
                                <div class="stats">
                                    <table class="fs-table">
                                        <tbody>
                                        <tr class="fs-transfer__speed-info average_speed">
                                            <td>{tr:average_speed}</td>
                                            <td id="fs-transfer__average-speed" class="value">17.4 MB / sec</td>
                                        </tr>
                                        <tr class="fs-transfer__number-of-files number_of_files">
                                            <td>{tr:number_of_files}</td>
                                            <td id="fs-transfer__total-files" class="value">0 {tr:files_lowercase}</td>
                                        </tr>
                                        <tr class="fs-transfer__estimated-info estimated_completion">
                                            <td>
                                                <strong>
                                                    {tr:estimated_completion}
                                                </strong>
                                            </td>
                                            <td>
                                                <strong id="fs-transfer__estimated-time" class="value">
                                                    {tr:in} 13 {tr:minutes}
                                                </strong>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if(Config::get('upload_show_play_pause')) { ?>
                                <div class="buttons">
                                    <button type="button" id="fs-transfer__pause" class="fs-button fs-button--info fs-button--icon-right pausebutton">
                                        {tr:pause}
                                        <i class="fa fa-pause"></i>
                                    </button>
                                    <button type="button" id="fs-transfer__resume" class="fs-button fs-button--info fs-button--icon-right resumebutton" disabled="1">
                                        {tr:resume}
                                        <i class="fa fa-play"></i>
                                    </button>
                                    <button type="button" id="fs-transfer__stop" class="fs-button fs-button--info fs-button--icon-right stopbutton">
                                        {tr:stop}
                                        <i class="fa fa-stop"></i>
                                    </button>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fs-transfer__step" data-step="5">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                        <div class="fs-transfer__list fs-transfer__list--full">
                            <h6>
                                {tr:selected_files}
                            </h6>
                            <div class="fs-transfer__files">
                                <table class="fs-table"></table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                        <div class="fs-transfer__upload-detail fs-transfer__upload-finished">
                            <h5>
                                {tr:transfer_completed}
                            </h5>
                            <div class="fs-progress-bar">
                                <strong class="fs-progress-bar__value">100%</strong>
                                <span class="fs-progress-bar__progress">
                                <span class="fs-progress-bar__indicator" style="width: 100%"></span>
                            </span>
                            </div>
                            <div class="fs-transfer__upload-speed-info">
                                <div class="stats">
                                    <table class="fs-table">
                                        <tbody>
                                        <tr class="fs-transfer__total-info size">
                                            <td>{tr:ui2_total_size}</td>
                                            <td id="fs-transfer__total-size" class="value">0 MB</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="fs-transfer__upload-link">
                                <span>
                                    {tr:your_download_link}
                                </span>
                                <div class="fs-copy">
                                    <span></span>

                                    <button id="copy-to-clipboard" type="button" class="fs-button">
                                        <i class="fa fa-copy"></i>
                                        {tr:copy}
                                    </button>
                                </div>
                            </div>
                            <div class="fs-transfer__upload-recipients">
                                <span>
                                    {tr:your_transfer_was_sent}
                                </span>
                                <div class="fs-badge-list">
                                </div>
                            </div>
                            <div class="fs-transfer__upload-expires">
                                <span>
                                    {tr:expires_in} <span id="expires-days">7</span> {tr:days}.
                                </span>
                            </div>
                            <div class="fs-transfer__upload-custom-name">
                                <label for="transfer-name">
                                    {tr:add_transfer_custom_name}
                                </label>
                                <div class="fs-input-inline">
                                    <input type="text" id="transfer-name" name="transfer-name" placeholder="{tr:enter_transfer_name}">
                                    <button type="button" class="fs-button">
                                        <i class="fa fa-save"></i>
                                        {tr:save}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fs-transfer__upload-actions">
                    <a id="detail-link" href=""type="button" class="fs-button fs-button--info" role="button">
                        <i class="fa fa-file-text-o"></i>
                        {tr:transfer_details}
                    </a>
                    <a href="?s=transfers" class="fs-button fs-button--info" role="button">
                        <i class="fa fa-exchange"></i>
                        {tr:all_my_transfers}
                    </a>
                </div>
            </div>
        </div>

        <div class="upload stage1 stage1options mt-2">
            <?php if (Config::get('upload_graph_bulk_display')) { ?>
                <div class="row">
                    <div class="col-12">
                        <div id="graph" class="uploadbulkgraph"><div id="graphDiv"><canvas id="speedChart"></canvas></div></div>
                        <script type="text/javascript" src="{path:lib/chart.js/chart.min.js}"></script>
                        <script type="text/javascript" src="{path:js/graph.js}"></script>
                    </div>
                </div>
            <?php } ?>

        </div>
    </form>

    <?php if (!Config::get('disable_directory_upload')) { ?>
        <script type="text/javascript" src="{path:js/dragdrop-dirtree.js}"></script>
    <?php } ?>
    <script type="text/javascript" src="{path:js/upload_page.js}"></script>
</div>
