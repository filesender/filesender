
<div class="box">
    <h1>{tr:upload_page}</h1>
    
    <form id="upload_form" enctype="multipart/form-data" method="post" action="{path:basic_upload.php}">
        <div class="box">
            <div class="file_selector">
                <label for="files" class="mandatory">{tr:select_file} :</label>
                
                <input name="files" type="file" multiple />
            </div>
            
            <div class="files"></div>
            
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
                        
                        <div class="recipients"></div>
                        
                        <input name="to" id="to" type="text" title="{tr:email_separator_msg}" value="" placeholder="{tr:enter_to_email}" />
                    </div>
                    
                    <div class="fieldcontainer">
                        <label for="subject">{tr:subject} ({tr:optional}) :</label>
                        
                        <input name="subject" type="text"/>
                    </div>
                    
                    <div class="fieldcontainer">
                        <label for="message">{tr:message} ({tr:optional}) : </label>
                        
                        <textarea name="message" rows="4"></textarea>
                    </div>
                    
                    <div>
                        <input type="hidden" name="s-token" id="s-token" value="<?php echo (isset($_SESSION['s-token'])) ? $_SESSION['s-token'] : ''; ?>"/>
                    </div>
                </td>
                
                <td class="box">
                    <?php
                        $displayoption = function($name, $cfg) {
                            $checked = $cfg['default'] ? 'checked="checked"' : '';
                            
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
                        
                        <?php foreach(Transfer::availableOptions(false) as $name => $cfg) $displayoption($name, $cfg) ?>
                    </div>
                    
                    <?php if(count(Transfer::availableOptions(true)) || (Config::get('terasender_enabled') && Config::get('terasender_advanced'))) { ?>
                    <div class="fieldcontainer">
                        <a class="toggle_advanced_options" href="#">{tr:advanced_settings}</a>
                    </div>
                    
                    <div class="advanced_options">
                        <?php foreach(Transfer::availableOptions(true) as $name => $cfg) $displayoption($name, $cfg) ?>
                        
                        <?php if (Config::get('terasender_enabled') && Config::get('terasender_advanced')) { ?>
                        <div class="fieldcontainer">
                            <label for="chunksize">{tr:tera_chunksize}</label>
                            
                            <input id="chunksize" type="text" value="<?php echo Config::get('terasender_chunk_size') ?>"/>
                            <br />
                        </div>
                        
                        <div class="fieldcontainer">
                            <label for="workerCount">{tr:tera_worker_count}</label>
                            
                            <input id="workerCount" type="text" value="<?php echo Config::get('terasender_worker_count') ?>"/>
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
            <a href="#" class="pause not_displayed">
                <span class="fa fa-pause fa-lg"></span> {tr:pause}
            </a>
            <a href="#" class="restart not_displayed">
                <span class="fa fa-play fa-lg"></span> {tr:restart}
            </a>
            <a href="#" class="stop not_displayed">
                <span class="fa fa-stop fa-lg"></span> {tr:stop}
            </a>
        </div>
    </form>
    
    <script type="text/javascript" src="{path:res/js/upload_page.js}"></script>
</div>
