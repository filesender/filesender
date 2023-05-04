<?php

$guest_can_only_send_to_creator = false;
$encryption_mandatory = Principal::isEncryptionMandatory();
$encryption_checkbox_checked = '';
$encryption_checkbox_classes = '';
$expire_time_is_editable = true;

$files_actions_div_extra_class = "div3";
$upload_directory_button_enabled = false;
if( !Config::get('disable_directory_upload')
    && Config::get('directory_upload_button_enabled')
 && (Browser::instance()->isChrome || Browser::instance()->isFirefox))
{
    $upload_directory_button_enabled = true;
    $files_actions_div_extra_class = "div4";
}

$formClasses = "upload_form_regular";
if (Config::get('upload_display_per_file_stats')) {
   $formClasses = "upload_form_stats";
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

if(Auth::isGuest()) {
    if($guest->getOption(GuestOptions::CAN_ONLY_SEND_TO_ME)) {
        $guest_can_only_send_to_creator = true;
    }
}

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




?>

<div class="box">
    <form id="upload_form" class="<?php echo $formClasses; ?>" enctype="multipart/form-data" accept-charset="utf-8" method="post" autocomplete="off" data-need-recipients="<?php echo $need_recipients ? '1' : '' ?>">
        <div class="box">
            <div class="files" id="fileslist"></div>
            
            <div class="file_selector">
                <label for="files" class="mandatory">{tr:select_file} :</label>
                
                <input id="files" name="files" type="file" multiple />
                
                <?php echo LegacyUploadProgress::getTrackingInput() ?>
            </div>
            
            <div class="files_dragdrop">
                <div class="instructions">{tr:drag_and_drop}</div>
            </div>

            <div class="files_uploadlogtop" hidden="true">
                <div class="uploadlogbox">
                    <div class="uploadlogheader">{tr:upload_log_header}</div>
                    <table class="uploadlog">
                        <thead hidden="true">
                            <tr>
                                <th>{tr:date}</th>
                                <th>{tr:message}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="tpl">
                                <td class="date"></td>
                                <td class="message"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="files_actions">
                <div class="<?php echo $files_actions_div_extra_class ?>">
                    <a class="clear_all" href="#">
                        {tr:clear_all}
                    </a>
                </div>
                
                <div class="<?php echo $files_actions_div_extra_class ?>">
                    <a class="select_files" href="#">
                        {tr:select_files}
                    </a>
                </div>
                <?php if ($upload_directory_button_enabled) { ?>
                <div class="<?php echo $files_actions_div_extra_class ?>">
                    <input type="file" name="selectdir" id="selectdir" class="selectdir_hidden_input_element" webkitdirectory directory multiple mozdirectory />
                    <label for="selectdir" class="select_directory  ">{tr:select_directory}</label>
                </div>
                <?php } ?>
                
                <div class="stats <?php echo $files_actions_div_extra_class ?>">
                    <div class="number_of_files">{tr:number_of_files} : <span class="value"></span></div>
                    <div class="size">{tr:size} : <span class="value"></span></div>
                </div>
            </div>

            <div class="uploading_actions" hidden="true">
                <div class="msg">
                    <table class="resumetable">
                        <tr><td>
                            <div class="auto_resume_timer_top">
                                <div class="auto_resume_timer">
                                </div>
                            </div>
                        </td></tr>
                        <tr><td>
                            <div class="seconds_since_data_sent" id="seconds_since_data_sent"></div>
                        </td></tr>
                        <tr><td>
                            <div class="seconds_since_data_sent_info" id="seconds_since_data_sent_info"></div>
                        </td></tr>
                    </table>
                </div>
                <div class="stats">
                    <div class="uploaded">{tr:uploaded} : <span class="value"></span></div>
                    <div class="average_speed">{tr:average_speed} : <span class="value"></span></div>
                    <div class="estimated_completion">{tr:estimated_completion} : <span class="value"></span></div>
                </div>
            </div>
            
        </div>
        
        <table class="two_columns" id="upload_options_table">
            <tr>
                <td class="box">
                    <div class="fieldcontainer">
                        <?php $emails = Auth::isGuest() ? array(AuthGuest::getGuest()->email) : Auth::user()->email_addresses ?>
                        
                        <label for="from" class="mandatory">{tr:from} :</label>
                        
                        <?php if (count($emails) > 1) { ?>
                        
                        <select id="from" name="from">
                            <?php foreach ($emails as $email) { ?>
                            <option><?php echo Template::sanitizeOutputEmail($email) ?></option>
                            <?php } ?>
                        </select>
                        
                        <?php } else echo Template::sanitizeOutputEmail($emails[0]) ?>
                    </div>

                    <?php
                    /**
                     * @param optionsToFilter is an array of options which we do not want to
                     *                        show in the default panels on the left. This allows
                     *                        some options to be displayed in other locations on the page.
                     */
                    $displayoption = function( $name, $cfg, $disable = false, $forcedOption = false,
                                               $optionsToFilter = array('hide_sender_email'))
                    use ($guest_can_only_send_to_creator)
                    {
                            $text = in_array($name, array(TransferOptions::REDIRECT_URL_ON_COMPLETE));

                            if( in_array($name, $optionsToFilter)) {
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
                            
                            echo '<div class="fieldcontainer" data-option="'.$name.'" '. $extraDivAttrs .'>';
                            if($text) {
                                echo '    <label for="'.$name.'">'.Lang::tr($name).'</label>';
                                echo '    <input id="'.$name.'" name="'.$name.'" type="text" value="'.htmlspecialchars($default).'" '.$disabled.'>';
                                
                            } else {
                                echo '  <input id="'.$name.'" name="'.$name.'" type="checkbox" '.$checked.' '.$disabled.' />';
                                echo '  <label for="'.$name.'">'.Lang::tr($name).'</label>';
                            }
                            
                            if($name == TransferOptions::ENABLE_RECIPIENT_EMAIL_DOWNLOAD_COMPLETE)
                                echo '<div class="info message">'.Lang::tr('enable_recipient_email_download_complete_warning').'</div>';
                            if($name == TransferOptions::WEB_NOTIFICATION_WHEN_UPLOAD_IS_COMPLETE && Browser::instance()->isFirefox)
                                echo '<div class="info message"><a class="enable_web_notifications" href="#">'.Lang::tr('click_to_enable_web_notifications').'</a></div>';
                            
                            echo '</div>';
                        };
                    ?>
                    
                    <div class="left_options">
                    <?php
                    $ops = Transfer::availableOptions();
                    if( array_key_exists( 'hide_sender_email', $ops )) {
                        $displayoption('hide_sender_email', $ops['hide_sender_email'], Auth::isGuest(), false, array() );
                    }
                    ?>
                    </div>
                    
                    <?php if($allow_recipients) { ?>
                    <div class="fieldcontainer" data-related-to="message">
                        <label for="to" class="mandatory">{tr:to} :</label>
                        
                        <?php if(Auth::isGuest() && AuthGuest::getGuest()->getOption(GuestOptions::CAN_ONLY_SEND_TO_ME)) { ?>
                        <?php echo AuthGuest::getGuest()->user_email ?>
                        <?php } else { ?>
                        <div class="recipients"></div>
                        
                        <input name="to" id="to" type="email" multiple title="{tr:email_separator_msg}" value="" placeholder="{tr:enter_to_email}" />
                        <?php } ?>
                    </div>
                    
                    <div class="fieldcontainer" data-related-to="message">
                        <label for="subject">{tr:subject} ({tr:optional}) :</label>
                        
                        <input id="subject" name="subject" type="text"/>
                    </div>
                    
                    <div class="fieldcontainer" data-related-to="message">
                        <label for="message">{tr:message} ({tr:optional}) : </label>
                        <label class="invalid" id="message_can_not_contain_urls">{tr:message_can_not_contain_urls}</label>                        
                        <label class="invalid" id="password_can_not_be_part_of_message_warning">
                            {tr:password_can_not_be_part_of_message_warning}</label>                        
                        <label class="invalid" id="password_can_not_be_part_of_message_error">
                            {tr:password_can_not_be_part_of_message_error}</label>                        
                        <textarea id="message" name="message" rows="4"></textarea>
                    </div>
                    <?php } ?> <!-- closing if($allow_recipients) -->
                    
                    <?php if(Config::get('encryption_enabled')) { ?>
                        <div class="fieldcontainer <?php echo $encryption_checkbox_classes ?>" id="encrypt_checkbox" data-related-to="encryption">
                            <input id="encryption" name="encryption" type="checkbox" <?php echo $encryption_checkbox_checked ?> >
                            <label for="encryption" class="cursor" >{tr:file_encryption}</label>
                        </div>
                        <div class="fieldcontainer" id="encryption_password_container">  
                            <label for="encryption_password" class="cursor" >{tr:file_encryption_password} : </label>
                            <input class="encryption_password" id="encryption_password" name="encryption_password" type="password" autocomplete="new-password" readonly />
                        </div>
                        <div class="fieldcontainer passwordvalidation" id="encryption_password_container_too_short_message">
                            {tr:file_encryption_password_too_short}
                        </div>
                        <div class="fieldcontainer passwordvalidation" id="encryption_password_container_must_have_numbers_message">
                            {tr:file_encryption_password_must_have_numbers}
                        </div>
                        <div class="fieldcontainer passwordvalidation" id="encryption_password_container_must_have_upper_and_lower_case_message">
                            {tr:file_encryption_password_must_have_upper_and_lower_case}
                        </div>
                        <div class="fieldcontainer passwordvalidation" id="encryption_password_container_must_have_special_characters_message">
                            {tr:file_encryption_password_must_have_special_characters}
                        </div>
                        <div class="fieldcontainer passwordvalidation" id="encryption_password_container_can_have_text_only_min_password_length_message">
                            {tr:encryption_password_container_can_have_text_only_min_password_length_message}
                        </div>
                        
                        <div class="fieldcontainer" id="encryption_password_container_generate">
                            <input id="encryption_use_generated_password"  name="encryption_use_generated_password" type="checkbox" >  
                            <label for="encryption_use_generated_password" class="cursor" >{tr:file_encryption_generate_password}</label>
                            
                        </div>
                        <div class="fieldcontainer" id="encryption_password_container_generate_again">
                            <a href="#" id="encryption_generate_password" class="">
                                <span class="fa fa-refresh"></span>&nbsp;{tr:generate_a_different_password}
                            </a>
                        </div>
                        <div class="fieldcontainer" id="encryption_password_show_container">  
                            <input id="encryption_show_password" name="encryption_show_password" type="checkbox">  
                            <label class="cursor" for="encryption_show_password">{tr:file_encryption_show_password}</label>
                        </div>
                        <div class="fieldcontainer" id="encryption_description_container">
                            {tr:file_encryption_description}
                        </div>
                        <div class="fieldcontainer" id="encryption_description_disabled_container">
                            {tr:file_encryption_description_disabled}
                        </div>
                    <?php } ?>
                    
                    <div>
                        <?php if(Auth::isGuest()) { ?>
                        <input type="hidden" name="guest_token" value="<?php echo AuthGuest::getGuest()->token ?>" />
                        <input type="hidden" id="guest_options" value="<?php echo Utilities::sanitizeOutput(json_encode(AuthGuest::getGuest()->options)) ?>" />
                        <input type="hidden" id="guest_transfer_options" value="<?php echo Utilities::sanitizeOutput(json_encode(AuthGuest::getGuest()->transfer_options)) ?>" />
                        <?php } ?>
                        
                    </div>
                </td>
                <td class="box">

                    <div class="basic_options">
                        <div class="fieldcontainer">
                            <label for="expires" id="datepicker_label" class="mandatory">{tr:expiry_date}:</label>
                            
                            <input id="expires" name="expires" type="text" autocomplete="off" <?php if(!$expire_time_is_editable) echo " disabled "  ?>
                                   title="<?php echo Lang::trWithConfigOverride('dp_date_format_hint')->r(array('max' => Config::get('max_transfer_days_valid'))) ?>"
                                   data-epoch="<?php echo Transfer::getDefaultExpire() ?>"
                            />
                        </div>
                        
                        <?php
                            if(Config::get('transfer_recipients_lang_selector_enabled')) {
                                $opts = array();
                                $code = Lang::getBaseCode();
                                foreach(Lang::getAvailableLanguages() as $id => $dfn) {
                                    $selected = ($id == $code) ? 'selected="selected"' : '';
                                    $opts[] = '<option value="'.$id.'" '.$selected.'>'.Utilities::sanitizeOutput($dfn['name']).'</option>';
                                }
                                
                                echo '<div class="fieldcontainer">';
                                echo '  <label for="lang">{tr:recipients_notifications_language}:</label>';
                                echo '  <select id="lang" name="lang">'.implode('', $opts).'</select>';
                                echo '</div>';
                            }
                        ?>
                        
                        <?php foreach(Transfer::availableOptions(false) as $name => $cfg) $displayoption($name, $cfg, Auth::isGuest()) ?>
                    </div>

                    
                    <?php if(count(Transfer::availableOptions(true)) || (Config::get('terasender_enabled') && Config::get('terasender_advanced'))) { ?>
                    <div class="fieldcontainer">
                        <a class="toggle_advanced_options" href="#">{tr:advanced_settings}</a>
                    </div>
                    
                    <div class="advanced_options">
                        <?php foreach(Transfer::availableOptions(true) as $name => $cfg) $displayoption($name, $cfg, Auth::isGuest()) ?>
                        
                        <?php if (Config::get('terasender_enabled') && Config::get('terasender_advanced')) { ?>
                        <div class="fieldcontainer">
                            <label for="terasender_worker_count">{tr:terasender_worker_count}</label>
                            
                            <input id="terasender_worker_count" type="text" value="<?php echo Config::get('terasender_worker_count') ?>"/>
                            <br />
                        </div>
                        <?php } ?>
                        <?php if (Config::get('terasender_enabled') && Config::get('terasender_disableable')) $displayoption('disable_terasender', array('default'=>false), false); ?>


                    </div>
                    <?php } /* End of advanced settings div. */ ?>

                    <div class="hidden_options">
                        <?php foreach(Transfer::forcedOptions() as $name => $cfg) {
                            $allowed = (array)Config::get("transfer_options_not_available_to_export_to_client");
                            if( in_array($name,$allowed)) {
                                $displayoption($name, $cfg, Auth::isGuest(),true);
                            }
                        } ?>
                    </div>
                    
                </td>
            </tr>
        </table>
        
        <?php if (Config::get('aup_enabled')) { ?>
        <div class="aup fieldcontainer box">
            <label for="aup" title="{tr:showhide}">
                {tr:accepttoc} [<span>{tr:showhide}</span>]
            </label>
            
            <?php
            $aupChecked = '';
            if (Config::get('aup_default') || (isset($_SESSION['aup']) /*&& !$authvoucher->aVoucher()*/)) $aupChecked = 'checked="checked"';
            ?>
            <input name="aup" type="checkbox" <?php echo $aupChecked; ?> value="true"/>
            
            <div class="terms">{tr:aupterms}</div>
        </div>
        <?php } ?>
        
        <div class="buttons">
            <a href="#" class="start">
                <span class="fa fa-cloud-upload fa-lg"></span> {tr:send}
            </a>
            <a href="#" class="restart not_displayed">
                <span class="fa fa-cloud-upload fa-lg"></span> {tr:restart}
            </a>
            <a href="#" class="pause not_displayed">
                <span class="fa fa-pause fa-lg"></span> {tr:pause}
            </a>
            <a href="#" class="resume not_displayed">
                <span class="fa fa-play fa-lg"></span> {tr:resume}
            </a>
            <a href="#" class="stop not_displayed">
                <span class="fa fa-stop fa-lg"></span> {tr:stop}
            </a>
            <a href="#" class="reconnect not_displayed">
                <span class="fa fa-cloud-upload fa-lg"></span> {tr:reconnect_and_continue}
            </a>
        </div>
    </form>

    <?php if (Config::get('upload_graph_bulk_display')) { ?>
        <div id="graph" class="uploadbulkgraph"><div id="graphDiv"><canvas id="speedChart"></canvas></div></div>

        <script type="text/javascript" src="{path:lib/chart.js/chart.min.js}"></script>
        <script type="text/javascript" src="{path:js/graph.js}"></script>
    <?php } ?>
    
    <?php if (!Config::get('disable_directory_upload')) { ?>
       <script type="text/javascript" src="{path:js/dragdrop-dirtree.js}"></script>
    <?php } ?>
    <script type="text/javascript" src="{path:js/upload_page.js}"></script>
</div>
