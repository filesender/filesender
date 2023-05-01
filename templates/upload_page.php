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



$files_actions_div_extra_class = "div3";
$upload_directory_button_enabled = false;
if( !Config::get('disable_directory_upload')
    && Config::get('directory_upload_button_enabled')
 && (Browser::instance()->isChrome || Browser::instance()->isFirefox))
{
    $upload_directory_button_enabled = true;
    $files_actions_div_extra_class = "div4";
}
$files_actions_div_extra_class = "";

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
    
    if($guest->getOption(GuestOptions::CAN_ONLY_SEND_TO_ME)) {
        $guest_can_only_send_to_creator = true;
    }
}



$displayoption = function($name, $cfg, $disable = false, $forcedOption = false,$relatedTo = '') use ($guest_can_only_send_to_creator) {
    $text = in_array($name, array(TransferOptions::REDIRECT_URL_ON_COMPLETE));

    
    
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
    
    if( $relatedTo != '' ) {
        echo '<div class="fieldcontainer" data-related-to="'.$relatedTo.'">';
    }
    echo '<div class="form-check form-switch custom-control custom-switch" data-option="'.$name.'" '. $extraDivAttrs .'>';

    if($text) {
        echo '    <label for="'.$name.'" class="form-check-label">'.Lang::tr($name).'</label>';
        echo '    <input id="'.$name.'" name="'.$name.'" class="form-check-input uploadoption" type="text" value="'.htmlspecialchars($default).'" '.$disabled.'>';
        
    } else {
        echo '  <input id="'.$name.'" name="'.$name.'" class="form-check-input uploadoption" type="checkbox" '.$checked.' '.$disabled.' />';
        echo '  <label for="'.$name.'" class="form-check-label">'.Lang::tr($name).'</label>';
    }
    
    if($name == TransferOptions::ENABLE_RECIPIENT_EMAIL_DOWNLOAD_COMPLETE)
        echo '<div class="info warning">'.Lang::tr('enable_recipient_email_download_complete_warning').'</div>';
    if($name == TransferOptions::WEB_NOTIFICATION_WHEN_UPLOAD_IS_COMPLETE && Browser::instance()->isFirefox)
        echo '<div class="info message"><a class="enable_web_notifications" href="#">'.Lang::tr('click_to_enable_web_notifications').'</a></div>';
    
    echo '</div>';
    if( $relatedTo != '' ) {
        echo '</div>';
    }
    
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



?>

<div class="core box">
    <form id="upload_form"
          class="<?php echo $formClasses; ?>"
          enctype="multipart/form-data"
          accept-charset="utf-8"
          method="post"
          autocomplete="off"
          data-need-recipients="<?php echo $need_recipients ? '1' : '' ?>">

        <div class="">

            <table width="100%" class="stage1 clearandstats">
                <tr>
                    <td colspan="2">
                        <div class="stats">
                            <div class="number_of_files">{tr:number_of_files} : <span class="value"></span></div>
                            <div class="size">{tr:size} : <span class="value"></span></div>
                        </div>
                    </td>
                    <td class="float-end">
                        <div class="end">
                            <button type="button" class="clear_all btn btn-secondary">
                                {tr:clear_all}
                            </button>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="stage1 stage3">
                <div class="info message" id="please_readd_files_message">{tr:need_to_readd_files}</div>
                <div class="files" id="fileslist">

                        <table class="filestable table">
                            <thead hidden="true">
                                <tr>
                                    <th>{tr:date}</th>
                                    <th>{tr:message}</th>
                                </tr>
                            </thead>
                            <tbody id="fileslistdirectparent">
                                <tr class="tpl"> <!-- becomes class="file" when cloned -->
                                    <td class="filename"></td>
                                    <td class="filesize"></td>
                                    
                                    <?php if (Config::get('upload_display_per_file_stats')) { ?>
                                    <td class="crustmeters stage3">
                                        <?php for( $i=0; $i < $crustMeterCount; $i++ ) { ?>
                                        <div class="crust crust<?php echo $i ?>"  >
                                            <a class="crust_meter" href="#">
                                                <div class="label crustage   uploadthread">   </div></a>
                                            <a class="crust_meter_bytes" href="#">
                                                <div class="label crustbytes uploadthread">   </div></a>
                                        </div>
                                        <?php } ?>
                                    </td>
                                    <?php } ?>
                                    <td class="error stage1"></td>
                                    <td class="progressbar w-25 stage3">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>                                        
                                    </td>
                                    <td class="remove stage1">
                                        <span class="remove removebutton fa fa-minus-square fa-lg" title="{tr:click_to_delete_file}" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    
                </div>

                <div class="file_selector">
                    <label for="files" class="mandatory">{tr:select_file} :</label>
                    
                    <input id="files" name="files" type="file" multiple />
                    
                    <?php echo LegacyUploadProgress::getTrackingInput() ?>
                </div>
                
                <div class="files_dragdrop">
                    <div class="instructions"><i class="fa fa-file"></i>&nbsp;{tr:ui2_select_files}
                    </div>
                </div>
            </div>

            <div class="stage4">
                <div class="text-center">
                    <h1 class="">{tr:done_uploading}</h1>
                    <a class="downloadlink" href="#"></a>
                    <br/>
                    <p></p>
                </div>

<?php if(!Auth::isGuest()) { ?>
                <div class="d-flex justify-content-center">
                    <a href="?s=transfers" class="btn btn-primary btn-lg btn-block mytransferslink" role="button">{tr:ui2_my_transfers}</a>
                </div>
                
<?php } ?>              
            </div>
            
            <div class="files_uploadlogtop stage3">
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

            <div class="files_actions stage1 row">
                    <div class="col-6 text-start">
                        <a class="select_files btn btn-secondary  " href="#">
                            {tr:select_files}
                        </a>
                    </div>
                    <div class="col-6 text-end">
                        <?php if ($upload_directory_button_enabled) { ?>
                            <div <?php echo $files_actions_div_extra_class ?>>
                                <input type="file" name="selectdir" id="selectdir" class="selectdir_hidden_input_element" webkitdirectory directory multiple mozdirectory />
                                <label for="selectdir" class="select_directory btn btn-secondary ">{tr:send_an_entire_directory}</label>
                            </div>
                        <?php } ?>
                    </div>
            </div>

            <div class="uploading_actions stage3">
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
        <!-- closed class="" still in form & core -->

	<div class="upload stage1 stage1options">

            <?php if(Config::get('encryption_enabled')) {  ?>
                <div class="row">
                    <div class="col">
                        <div class="form-check form-switch custom-control custom-switch" id="encrypt_checkbox" data-related-to="encryption">
                            <input id="encryption"
                                   name="encryption"
                                   class="form-check-input"
                                   type="checkbox"
                                   <?php echo $encryption_checkbox_checked ?>
                            />
                            <label for="encryption" class="form-check-label">{tr:file_encryption}</label>
                        </div>
                    </div>
                </div>
                <div id="encgroup1" class="row">
                    <div class="col">
                        <div class="fieldcontainer" id="encryption_password_container">  
                            <label for="encryption_password">{tr:file_encryption_password} : </label>
                            <input class="encryption_password"
                                   id="encryption_password"
                                   name="encryption_password"
                                   type="password"
                                   autocomplete="new-password" readonly
                            />
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
                        <div class="fieldcontainer" id="encryption_description_disabled_container">
                            {tr:file_encryption_description_disabled}
                        </div>                        
                    </div>
                </div>
                <div id="encgroup2" class="row">
                    <div class="col">
                        <div class="form-check form-switch custom-control custom-switch" id="encryption_password_container_generate">
                            <input id="encryption_use_generated_password"
                                   name="encryption_use_generated_password"
                                   class="form-check-input"
                                   type="checkbox"
                            />  
                            <label for="encryption_use_generated_password" class="form-check-label" >
                                {tr:file_encryption_generate_password}
                            </label>
                        </div>
                        <div class="fieldcontainer" id="encryption_password_container_generate_again">
                            <button type="button" class="btn btn-secondary" id="encryption_generate_password">
                                <span class="fa fa-refresh"></span>&nbsp;{tr:generate_a_different_password}
                            </button>
                        </div>
                    </div>
                </div>
                <div id="encgroup3" class="row">
                    <div class="col">
                        <div class="form-check form-switch custom-control custom-switch" id="encryption_password_show_container">  
                            <input id="encryption_show_password" name="encryption_show_password" class="form-check-input" type="checkbox">  
                            <label for="encryption_show_password" class="form-check-label">{tr:file_encryption_show_password}</label>
                        </div>
                    </div>
                </div>
            <?php } ?>
            
            
            <?php if (Config::get('aup_enabled')) { ?>
                <div class="row">
                    <div class="col">
                        <div class="aupbox form-check form-switch custom-control custom-switch">
                            <input id="aup" name="aup"
                                   type="checkbox"
                                   class="form-check-input"
                                   <?php echo $aupChecked; ?>  
                                   value="true" required />
                            <label for="aup" class="form-check-label" title="{tr:showhide}" >
                                {tr:accepttoc}
                            </label>
                            <div name="aupshowhide" id="aupshowhide" href="#">
                                [<span>{tr:showhide}</span>]</div>
                            
                            <div class="terms">{tr:aupterms}</div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            
            <?php if (Config::get('upload_graph_bulk_display')) { ?>
                <div class="row">
                    <div class="col-12">
                      <div id="graph" class="uploadbulkgraph"><div id="graphDiv"><canvas id="speedChart"></canvas></div></div>
                      <script type="text/javascript" src="{path:lib/chart.js/chart.min.js}"></script>
                      <script type="text/javascript" src="{path:js/graph.js}"></script>
                    </div>
        	</div>
            <?php } ?>

            <div class="d-flex justify-content-center">
                <a href="#" class="btn btn-primary btn-lg btn-block stage1continue" role="button">{tr:continue}&nbsp;<i class="fa fa-chevron-right"></i></a>
            </div>
        </div>
        
        <div class="nobox">

            <div class="stage2 mainbox rounded">
            <table class="upload stage2" width="100%" id="">
                <tr>
                    <td colspan="2">
                        <div class="text_desc_of_file_count_and_size" ><span class="value">x</span></div>
                    </td>
                </tr>
<?php if($show_get_a_link_or_email_choice) { ?>
                <tr id="get_a_link_or_email_choice">
                    <td colspan="2">
                        <h4>
                            <a               class="galmodelink  btn btn-primary">{tr:ui2_mode_link}</a>
                            <a id="galemail" class="galmodelink  btn btn-secondary" href="#">{tr:ui2_mode_mail}</a>
                            <a id="galgal"   class="galmodeemail btn btn-secondary" href="#">{tr:ui2_mode_link}</a>
                            <a               class="galmodeemail btn btn-primary">{tr:ui2_mode_mail}</a>
                        </h4>
                    </td>
                    <td class="">
                    </td>
                </tr>
<?php } ?>
            </table>
            <table class="upload stage2" width="100%" id="">
                <tr>
                <td class="nobox" colspan="3">
                    <div class="fieldcontainer" data-related-to="emailfrom">
                        
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
                    
                    <?php if($allow_recipients) { ?>
                    <div class="fieldcontainer" data-related-to="message">
                        <label for="to" class="mandatory">{tr:to} :</label>
                        
                        <?php if(Auth::isGuest() && AuthGuest::getGuest()->getOption(GuestOptions::CAN_ONLY_SEND_TO_ME)) { ?>
                        <?php echo AuthGuest::getGuest()->user_email ?>
                        <?php } else { ?>
                        <div class="recipients"></div>
                        
                        <input name="to" id="to" type="email"
                               class="form-control"
                               multiple title="{tr:email_separator_msg}"
                               value=""
                               placeholder="{tr:enter_to_email}" />
                        <?php } ?>
                    </div>

                    <?php
                    foreach(Transfer::availableOptions(false) as $name => $cfg) {
                        if( $name == 'add_me_to_recipients' ) {
                            $upload_options_handled[$name] = 1;
                            $forcedOption = false;
                            $displayoption($name, $cfg, Auth::isGuest(),$forcedOption,"message");
                        }
                    }
                    ?>

                    
                    <div class="fieldcontainer" data-related-to="message">
                        <label for="subject">{tr:subject} ({tr:optional}) :</label>
                        
                        <input id="subject" name="subject" class="form-control" type="text"/>
                    </div>
                    
                    <div class="fieldcontainer" data-related-to="message">
                        <label for="message">{tr:message} ({tr:optional}) : </label>
                        <label class="invalid" id="message_can_not_contain_urls">{tr:message_can_not_contain_urls}</label>
                        <label class="invalid" id="password_can_not_be_part_of_message_warning">
                            {tr:password_can_not_be_part_of_message_warning}</label>                        
                        <label class="invalid" id="password_can_not_be_part_of_message_error">
                            {tr:password_can_not_be_part_of_message_error}</label>                        
                        <textarea id="message" name="message" class="form-control" rows="4"></textarea>
                    </div>
                    <?php } ?> <!-- closing if($allow_recipients) -->
                    
                    <div>
                        <?php if(Auth::isGuest()) { ?>
                        <input type="hidden" name="guest_token" value="<?php echo AuthGuest::getGuest()->token ?>" />
                        <input type="hidden" id="guest_options" value="<?php echo Utilities::sanitizeOutput(json_encode(AuthGuest::getGuest()->options)) ?>" />
                        <input type="hidden" id="guest_transfer_options" value="<?php echo Utilities::sanitizeOutput(json_encode(AuthGuest::getGuest()->transfer_options)) ?>" />
                        <?php } ?>
                        
                    </div>
                </td>
                </tr>
            
                <tr>
                <td class="nobox" colspan="3">

                    <div class="basic_options">
                        <div class="fieldcontainer">
                            <label for="expires" id="datepicker_label" class="mandatory">{tr:expiry_date}:</label>
                            
                            <input id="expires" name="expires" type="text" autocomplete="off" <?php if(!$expire_time_is_editable) echo " disabled "  ?>
                                   title="<?php echo Lang::trWithConfigOverride('dp_date_format_hint')->r(array('max' => Config::get('max_transfer_days_valid'))) ?>"
                                   data-epoch="<?php echo Transfer::getDefaultExpire() ?>"
                            />
                        </div>
                        

                        <?php if(Config::get('transfer_recipients_lang_selector_enabled')) { ?>
                        <div class="nav-item dropdown">

                            <?php 
                            echo '  <label for="lang">{tr:recipients_notifications_language}:</label>';
                            $code = Lang::getCode();
                            foreach(Lang::getAvailableLanguages() as $id => $dfn) {
                                if($id == $code) {
                                    $specificid = $dfn['specific-id'];
                                    echo '<a class="nav-link dropdown-toggle" ';
                                    echo ' href="" ';
                                    echo ' id="lang" name="lang" ';
                                    echo ' data-bs-toggle="dropdown" ';
                                    echo ' data-id="'.$id.'" ';
                                    echo ' aria-haspopup="true" ';
                                    echo ' aria-expanded="false"> ';
                                    echo '  <span class="fi fi-'.$specificid.'"> </span> '.Utilities::sanitizeOutput($dfn['name']).'</a> ';
                                }
                            }
                            ?>
                            
                            <div class="dropdown-menu" aria-labelledby="lang" id="rlangdropdown">
                                <?php 
                                $code = Lang::getCode();
                                foreach(Lang::getAvailableLanguages() as $id => $dfn) {
                                    $specificid = $dfn['specific-id'];
                                    $selected = ($id == $code) ? 'selected="selected"' : '';
                                    echo '<a class="dropdown-item rlangdropitem" data-id="'.$id.'" >';
                                    echo '<span class="fi fi-'.$specificid.'"> </span> '.Utilities::sanitizeOutput($dfn['name']).'</a>';
                                    
                                }
                                ?>
                            </div>
                        </div>
                        <?php } ?>


                        

                        <?php
                        foreach(Transfer::availableOptions(false) as $name => $cfg) {
                            if( !array_key_exists($name,$upload_options_handled)) {
                                $displayoption($name, $cfg, Auth::isGuest());
                            }
                        }
                        ?>

                    </div>

                    
                    <?php if(count(Transfer::availableOptions(true)) || (Config::get('terasender_enabled') && Config::get('terasender_advanced'))) { ?>
                        <div class="accordion" id="advanced_options">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        {tr:advanced_settings}
                                    </button>
                                </h2>                            
                                <div class="card">
                                    <div id="collapseOne" class="collapse collapsed" aria-labelledby="headingOne" data-parent="#advanced_options">
                                        <div class="card-body">
                                            <?php
                                            foreach(Transfer::availableOptions(true) as $name => $cfg)  {
                                                if( !array_key_exists($name,$upload_options_handled)) {
                                                    $displayoption($name, $cfg, Auth::isGuest());
                                                }
                                            }
                                            ?>
                                            <?php if (Config::get('terasender_enabled') && Config::get('terasender_advanced')) { ?>
                                                <div class="fieldcontainer">
                                                    <label for="terasender_worker_count">{tr:terasender_worker_count}</label>
                                                    
                                                    <input id="terasender_worker_count"
                                                           class="form-control"
                                                           type="text"
                                                           value="<?php echo Config::get('terasender_worker_count') ?>"/>
                                                    <br />
                                                </div>
                                            <?php } ?>
                                            <?php if (Config::get('terasender_enabled') && Config::get('terasender_disableable')) $displayoption('disable_terasender', array('default'=>false), false); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

            <div class="d-flex justify-content-between">
                <a href="#" class="stage2back btn btn-primary btn-lg btn-block" role="button"><i class="fa fa-chevron-left"></i>&nbsp;{tr:back}</a>
                <a href="#" class="stage2continue btn btn-primary btn-lg btn-block" role="button">{tr:send}&nbsp;<i class="fa fa-chevron-right"></i></a></a>
            </div>
            
            </div>
            
        </div>
        
        <div class="buttons stage3">
            <a href="#" class="start  btn btn-primary ">
                <span class="fa fa-cloud-upload fa-lg"></span>&nbsp;{tr:send}
            </a>
            <a href="#" class="restart not_displayed  btn btn-secondary ">
                <span class="fa fa-cloud-upload fa-lg"></span>&nbsp;{tr:restart}
            </a>
            <a href="#" class="pause not_displayed btn btn-secondary ">
                <span class="fa fa-pause fa-lg"></span>&nbsp;{tr:pause}
            </a>
            <a href="#" class="resume not_displayed btn btn-secondary ">
                <span class="fa fa-play fa-lg"></span>&nbsp;{tr:resume}
            </a>
            <a href="#" class="stop not_displayed btn btn-secondary ">
                <span class="fa fa-stop fa-lg"></span>&nbsp;{tr:stop}
            </a>
            <a href="#" class="reconnect not_displayed btn btn-secondary ">
                <span class="fa fa-cloud-upload fa-lg"></span>&nbsp;{tr:reconnect_and_continue}
            </a>
        </div>
    </form>

    
    <?php if (!Config::get('disable_directory_upload')) { ?>
       <script type="text/javascript" src="{path:js/dragdrop-dirtree.js}"></script>
    <?php } ?>
    <script type="text/javascript" src="{path:js/upload_page.js}"></script>
</div>
