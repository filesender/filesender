<div class="box">
    <h1>{tr:upload_page}</h1>
    
    <form id="upload_form" enctype="multipart/form-data" accept-charset="utf-8" method="post">
        <div class="box">
            <div class="files"></div>
            
            <div class="file_selector">
                <label for="files" class="mandatory">{tr:select_file} :</label>
                
                <input name="files" type="file" multiple />
                
                <?php echo LegacyUploadProgress::getTrackingInput() ?>
            </div>
            
            <div class="files_dragdrop">
                <div class="instructions">{tr:drag_and_drop}</div>
            </div>
            
            <div class="files_actions">
                <div>
                    <a class="clear_all" href="#">
                        {tr:clear_all}
                    </a>
                </div>
                
                <div>
                    <a class="select_files" href="#">
                        {tr:select_files}
                    </a>
                </div>
                
                <div class="stats">
                    <div class="number_of_files">{tr:number_of_files} : <span class="value"></span></div>
                    <div class="size">{tr:size} : <span class="value"></span></div>
                    <div class="uploaded">{tr:uploaded} : <span class="value"></span></div>
                    <div class="average_speed">{tr:average_speed} : <span class="value"></span></div>
                </div>
            </div>
        </div>
        
        <table class="two_columns">
            <tr>
                <td class="box">
                    <div class="fieldcontainer">
                        <?php $emails = Auth::user()->email_addresses ?>
                        
                        <label for="from" class="mandatory">{tr:from} :</label>
                        
                        <?php if (count($emails) > 1) { ?>
                        
                        <select name="from">
                            <?php foreach ($emails as $email) { ?>
                            <option><?php echo $email ?></option>
                            <?php } ?>
                        </select>
                        
                        <?php } else echo $emails[0] ?>
                    </div>
                    
                    <div class="fieldcontainer" data-related-to="message">
                        <label for="to" class="mandatory">{tr:to} :</label>
                        
                        <?php if(Auth::isGuest() && AuthGuest::getGuest()->hasOption(GuestOptions::CAN_ONLY_SEND_TO_ME)) { ?>
                        <?php echo AuthGuest::getGuest()->user_email ?>
                        <?php } else { ?>
                        <div class="recipients"></div>
                        
                        <input name="to" id="to" type="text" title="{tr:email_separator_msg}" value="" placeholder="{tr:enter_to_email}" />
                        <?php } ?>
                    </div>
                    
                    <div class="fieldcontainer" data-related-to="message">
                        <label for="subject">{tr:subject} ({tr:optional}) :</label>
                        
                        <input name="subject" type="text"/>
                    </div>
                    
                    <div class="fieldcontainer" data-related-to="message">
                        <label for="message">{tr:message} ({tr:optional}) : </label>
                        
                        <textarea name="message" rows="4"></textarea>
                    </div>
                    
                    <?php if(array_key_exists('get_a_link', Transfer::availableOptions())) { ?>
                    <div class="fieldcontainer" data-related-to="get_a_link">
                        <input name="get_a_link" type="checkbox" /> <label for="get_a_link">{tr:get_a_link}</label>
                    </div>
                    <?php } ?>
                    
                    <div>
                        <?php if(Auth::isGuest()) { ?>
                        <input type="hidden" name="guest_token" value="<?php echo AuthGuest::getGuest()->token ?>" />
                        <?php } ?>
                        
                    </div>
                </td>
                
                <td class="box">
                    <?php
                        $displayoption = function($name, $cfg) {
                            if($name == 'get_a_link') return;
                            
                            $checked = $cfg['default'] ? 'checked="checked"' : '';
                            
                            echo '<div class="fieldcontainer" data-option="'.$name.'">';
                            echo '  <input name="'.$name.'" type="checkbox" '.$checked.' />';
                            echo '  <label for="'.$name.'">'.Lang::tr($name).'</label>';
                            echo '</div>';
                        };
                    ?>
                    
                    <div class="basic_options">
                        <div class="fieldcontainer">
                            <label for="datepicker" id="datepicker_label" class="mandatory">{tr:expiry_date}:</label>
                            
                            <input name="expires" type="text" autocomplete="off" title="{tr:dp_dateformat}" value="<?php echo Utilities::formatDate(Transfer::getDefaultExpire()) ?>"/>
                        </div>
                        
                        <?php if(!Auth::isGuest()) foreach(Transfer::availableOptions(false) as $name => $cfg) $displayoption($name, $cfg) ?>
                    </div>
                    
                    <?php if((!Auth::isGuest() && count(Transfer::availableOptions(true))) || (Config::get('terasender_enabled') && Config::get('terasender_advanced'))) { ?>
                    <div class="fieldcontainer">
                        <a class="toggle_advanced_options" href="#">{tr:advanced_settings}</a>
                    </div>
                    
                    <div class="advanced_options">
                        <?php if(!Auth::isGuest()) foreach(Transfer::availableOptions(true) as $name => $cfg) $displayoption($name, $cfg) ?>
                        
                        <?php if (Config::get('terasender_enabled') && Config::get('terasender_advanced')) { ?>
                        <div class="fieldcontainer">
                            <label for="workerCount">{tr:terasender_worker_count}</label>
                            
                            <input id="terasender_worker_count" type="text" value="<?php echo Config::get('terasender_worker_count') ?>"/>
                            <br />
                        </div>
                        <?php } ?>
                    </div>
                    <?php } /* End of advanced settings div. */ ?>
                </td>
            </tr>
        </table>
        
        <?php if (Config::get('AuP')) { ?>
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
        </div>
    </form>
    
    <script type="text/javascript" src="{path:js/upload_page.js}"></script>
</div>
