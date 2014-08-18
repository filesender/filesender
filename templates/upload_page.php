<div class="box">
    <h1>{tr:upload_page}</h1>
    
    <form id="upload_form" enctype="multipart/form-data" method="post" action="{path:basic_upload.php}">
        <div class="box">
            <div class="files">
                <label for="files" class="mandatory">{tr:select_file} :</label>
                
                <input name="files" type="file" multiple />
            </div>
            
            <div class="files_dragdrop">
                <div class="instructions">{tr:drag_and_drop}</div>
                
                <div class="files"></div>
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
                
                <span class="stats"></span>
            </div>
        </div>
        
        <table class="two_columns">
            <tr>
                <td class="box">
                    <div class="fieldcontainer">
                        <?php $emails = Auth::user()->email ?>
                        
                        <label for="from" class="mandatory">{tr:from} :</label>
                        
                        <?php if (count($emails) > 1) { ?>
                        
                        <select name="from">
                            <?php foreach ($emails as $email) { ?>
                            <option><?php echo $email ?></option>
                            <?php } ?>
                        </select>
                        
                        <?php } else echo $emails[0] ?>
                    </div>
                    
                    <div class="fieldcontainer">
                        <label for="to" class="mandatory">{tr:to} :</label>
                        
                        <div class="recipients_box"></div>
                        
                        <input name="to" type="text" title="{tr:email_separator_msg}" value="" placeholder="{tr:enter_to_email}" />
                    </div>
                    
                    <div class="fieldcontainer">
                        <label for="subject">{tr:subject} ({tr:optional}) :</label>
                        
                        <input name="subject" type="text"/>
                    </div>
                    
                    <div class="fieldcontainer">
                        <label for="message">{tr:message} ({tr:optional}) : </label>
                        
                        <textarea name="message" cols="44" rows="4"></textarea>
                    </div>
                    
                    <div>
                        <input type="hidden" id="filevoucheruid" name="filevoucheruid" value="<?php echo $voucherUID; ?>"/>
                        <input type="hidden" name="vid" id="vid" value="<?php echo $voucherUID; ?>"/>
                        <input type="hidden" name="total" id="total" value=""/>
                        <input type="hidden" name="n" id="n" value=""/>
                        <input type="hidden" id="filestatus" name="filestatus" value="<?php echo $filestatus; ?>"/>
                        <input type="hidden" name="loadtype" id="loadtype" value="standard"/>
                        <input type="hidden" name="s-token" id="s-token" value="<?php echo (isset($_SESSION['s-token'])) ? $_SESSION['s-token'] : ''; ?>"/>
                    </div>
                </td>
                
                <td class="box">
                    <?php
                        $displayoption = function($name, $mode) {
                            $disp = Config::get($name.'_display');
                            if($disp != $mode) return;
                            
                            $checked = '';
                            if(Config::get($name.'_default')) $checked = 'checked="checked"';
                            
                            echo '<div class="fieldcontainer">';
                            echo '  <label for="'.$name.'">'.Lang::tr($name).'</label>';
                            echo '  <input name="'.$name.'" type="checkbox" '.$checked.' />';
                            echo '</div>';
                        };
                    ?>
                    
                    <div class="basic_options">
                        <div class="fieldcontainer">
                            <label for="datepicker" id="datepicker_label" class="mandatory">{tr:expiry_date}:</label>
                            
                            <input name="expires" type="text" title="{tr:dp_dateformat}" value="<?php echo Utilities::formatDate(Transfer::getMaxExpire()) ?>"/>
                        </div>
                        
                        <?php $displayoption('email_me_copies', 'always') ?>
                        <?php $displayoption('upload_complete_email', 'always') ?>
                        <?php $displayoption('inform_download_email', 'always') ?>
                        <?php $displayoption('email_me_daily_statistics', 'always') ?>
                        <?php $displayoption('download_confirmation_enabled', 'always') ?>
                        <?php $displayoption('add_me_to_recipients', 'always') ?>
                    </div>
                    
                    <?php if (true /* TODO $functions->advancedSettingsEnabled() */) { ?>
                    <div class="fieldcontainer">
                        <a class="toggle_advanced_options" href="#">{tr:advanced_settings}</a>
                    </div>
                    
                    <div class="advanced_options">
                        <?php $displayoption('email_me_copies', 'hidden') ?>
                        <?php $displayoption('upload_complete_email', 'hidden') ?>
                        <?php $displayoption('inform_download_email', 'hidden') ?>
                        <?php $displayoption('email_me_daily_statistics', 'hidden') ?>
                        <?php $displayoption('download_confirmation_enabled', 'hidden') ?>
                        <?php $displayoption('add_me_to_recipients', 'hidden') ?>
                        
                        <?php if (Config::get('terasender') && Config::get('terasenderadvanced')) { ?>
                        <div class="fieldcontainer">
                            <label for="chunksize">{tr:tera_chunksize}</label>
                            
                            <input id="chunksize" type="text" value="<?php echo Config::get('terasender_chunksize') ?>"/>
                            <br />
                        </div>
                        
                        <div class="fieldcontainer">
                            <label for="workerCount">{tr:tera_worker_count}</label>
                            
                            <input id="workerCount" type="text" value="<?php echo Config::get('terasender_workerCount') ?>"/>
                            <br />
                        </div>
                        
                        <div class="fieldcontainer">
                            <label for="jobsPerWorker">{tr:tera_jobs_per_workers}</label>
                            
                            <input id="jobsPerWorker" type="text" value="<?php echo Config::get('terasender_jobsPerWorker') ?>"/>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } /* End of advanced settings div. */ ?>
                </td>
            </tr>
        </table>
        
        <?php if (Config::get('AuP')) { ?>
        <div class="aup fieldcontainer box">
            <label for="aup" title="{tr:_SHOWHIDE}">
                {tr:accepttoc} [<span>{tr:showhide}</span>]
            </label>
            
            <?php
            $aupChecked = '';
            if (Config::get('AuP_default') || (isset($_SESSION['aup']) /*&& !$authvoucher->aVoucher()*/)) $aupChecked = 'checked="checked"';
            ?>
            <input name="aup" type="checkbox" <?php echo $aupChecked; ?> value="true"/>
            
            <div class="terms">{tr:aupterms}</div>
        </div>
        <?php } ?>
        
        <div class="upload_button">
            <a href="#" class="ui-button ui-state-default ui-corner-all">
                <span class="fa fa-cloud-upload fa-lg"></span> {tr:send}
            </a>
        </div>
    </form>
    
    <script type="text/javascript" src="{path:res/js/upload.js}"></script>
</div>
