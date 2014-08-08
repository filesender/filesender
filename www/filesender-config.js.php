<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * Propagates part of the config to javascript
 */

require_once('../classes/autoload.php');

date_default_timezone_set(Config::get('Default_TimeZone'));

header('Content-Type: text/javascript');

?>
if(!('filesender' in window)) window.filesender = {};

window.filesender.config = {
    upload_chunk_size: <?php echo Config::get('upload_chunk_size') ?>,
    
    max_flash_upload_size: <?php echo Config::get('max_flash_upload_size') ?>,
    
    max_html5_upload_size: <?php echo Config::get('max_html5_upload_size') ?>,
    max_html5_uploads: <?php echo Config::get('html5_max_uploads') ?>,
    
    ban_extension: '<?php echo Config::get('ban_extension') ?>',
    
    max_email_recipients: <?php echo Config::get('max_email_recipients') ?>,
    
    default_daysvalid: <?php echo Config::get('default_daysvalid') ?>,
    
    chunk_upload_security: '<?php echo Config::get('chunk_upload_security') ?>',
    
    terasender_enabled: <?php echo Config::get('terasender_enabled') ? 'true' : 'false' ?>,
    terasender_advanced: <?php echo Config::get('terasender_advanced') ? 'true' : 'false' ?>,
    terasender_chunk_size: <?php echo Config::get('terasender_chunk_size') ?>,
    terasender_worker_count: <?php echo Config::get('terasender_worker_count') ?>,
    terasender_start_mode: '<?php echo Config::get('terasender_start_mode') ?>',
    terasender_worker_file: 'js/terasender_worker.js', // Worker script file
    terasender_upload_endpoint: '<?php echo Config::get('site_url') ?>rest.php/file/{file_id}/chunk/{offset}<?php (Config::get('chunk_upload_security') == 'key') ? '?key={key}' : '' ?>',

};
