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

require_once('../includes/init.php');

header('Content-Type: text/javascript; charset=UTF-8');

$banned = Config::get('ban_extension');
$extension_whitelist_regex = Config::get('extension_whitelist_regex');

$amc = Config::get('autocomplete_min_characters');
function value_to_TF( $v )
{
    return json_encode( ($v ? true : false) );
}


?>
if (typeof window === 'undefined') {
	window = {};
}
if (!('filesender' in window)) window.filesender = {};

window.filesender.config = {
    log: true,
    
    site_name: '<?php echo Config::get('site_name') ?>',
    
    upload_chunk_size: <?php echo Config::get('upload_chunk_size') ?>,
    
    upload_display_bits_per_sec: <?php echo value_to_TF(Config::get('upload_display_bits_per_sec')) ?>,

    max_transfer_size: <?php echo Config::get('max_transfer_size') ?>,
    max_transfer_files: <?php echo Config::get('max_transfer_files') ?>,

    max_transfer_file_size: <?php echo Config::get('max_transfer_file_size') ?>,
    max_transfer_encrypted_file_size: <?php echo Config::get('max_transfer_encrypted_file_size') ?>,

    ban_extension: <?php echo is_string($banned) ? "'".$banned."'" : 'null' ?>,
    extension_whitelist_regex: <?php echo is_string($extension_whitelist_regex) ? "'".$extension_whitelist_regex."'" : 'null' ?>,
    
    max_transfer_recipients: <?php echo Config::get('max_transfer_recipients') ?>,
    max_guest_recipients: <?php echo Config::get('max_guest_recipients') ?>,
    
    max_transfer_days_valid: <?php echo Config::get('max_transfer_days_valid') ?>,
    default_transfer_days_valid: <?php echo Config::get('default_transfer_days_valid') ?>,
    max_guest_days_valid: <?php echo Config::get('max_guest_days_valid') ?>,
    default_guest_days_valid: <?php echo Config::get('default_guest_days_valid') ?>,
    
    chunk_upload_security: '<?php echo Config::get('chunk_upload_security') ?>',
    
    encryption_enabled: '<?php echo Config::get('encryption_enabled') ?>',
    encryption_min_password_length: '<?php echo Config::get('encryption_min_password_length') ?>',
    encryption_generated_password_length: '<?php echo Config::get('encryption_generated_password_length') ?>',
    upload_crypted_chunk_size: '<?php echo Config::get('upload_crypted_chunk_size') ?>',
    crypto_iv_len: '<?php echo Config::get('crypto_iv_len') ?>',
    crypto_crypt_name: '<?php echo Config::get('crypto_crypt_name') ?>',
    crypto_hash_name: '<?php echo Config::get('crypto_hash_name') ?>',

    terasender_enabled: <?php  echo value_to_TF(Config::get('terasender_enabled')) ?>,
    terasender_advanced: <?php echo value_to_TF(Config::get('terasender_advanced')) ?>,
    terasender_worker_count: <?php echo Config::get('terasender_worker_count') ?>,
    terasender_start_mode: '<?php echo Config::get('terasender_start_mode') ?>',
    terasender_worker_file: 'lib/terasender/terasender_worker.js?v=<?php echo Utilities::runningInstanceUID() ?>',
    terasender_upload_endpoint: '<?php echo Config::get('site_url') ?>rest.php/file/{file_id}/chunk/{offset}',
    terasender_worker_max_chunk_retries: <?php echo Config::get('terasender_worker_max_chunk_retries')  ?>,
    
    stalling_detection: <?php echo value_to_TF(Config::get('stalling_detection')); ?>,

    max_legacy_file_size: <?php echo Config::get('max_legacy_file_size') ?>,
    legacy_upload_endpoint: '<?php echo Config::get('site_url') ?>rest.php/file/{file_id}/whole',
    legacy_upload_progress_refresh_period: <?php echo Config::get('legacy_upload_progress_refresh_period') ?>,
    
    valid_filename_regex: '<?php $v = Config::get('valid_filename_regex'); $v = str_replace('\\', '\\\\', $v); echo $v; ?>',
    base_path: '<?php echo GUI::path() ?>',
    support_email: '<?php echo Config::get('support_email') ?>',
    autocomplete: {
        enabled:  <?php echo value_to_TF(Config::get('autocomplete')) ?>,
        min_characters: <?php echo Config::get('autocomplete_min_characters') ?>

    },
    message_can_not_contain_urls_regex: '<?php $v = Config::get('message_can_not_contain_urls_regex'); $v = str_replace('\\', '\\\\', $v); echo $v; ?>',

    auditlog_lifetime: <?php $lt = Config::get('auditlog_lifetime'); echo is_null($lt) ? 'null' : $lt ?>,
    
    logon_url: '<?php echo AuthSP::logonURL() ?>',

    upload_display_per_file_stats: '<?php echo Config::get('upload_display_per_file_stats') ?>',
    upload_force_transfer_resume_forget_if_encrypted: '<?php echo Config::get('upload_force_transfer_resume_forget_if_encrypted') ?>',
    upload_considered_too_slow_if_no_progress_for_seconds: '<?php echo Config::get('upload_considered_too_slow_if_no_progress_for_seconds') ?>',

    testing_terasender_worker_uploadRequestChange_function_name: '<?php echo Config::get('testing_terasender_worker_uploadRequestChange_function_name') ?>',


	language: {
		downloading : "<?php echo Lang::tr('downloading')->out(); ?>",
		decrypting : "<?php echo Lang::tr('decrypting')->out(); ?>",
		file_encryption_wrong_password : "<?php echo Lang::tr('file_encryption_wrong_password')->out(); ?>",
		file_encryption_enter_password : "<?php echo Lang::tr('file_encryption_enter_password')->out(); ?>",
		file_encryption_need_password : "<?php echo Lang::tr('file_encryption_need_password')->out(); ?>"
	},

};

<?php if(Config::get('force_legacy_mode')) { ?>

$(function() {
    filesender.supports = {
        localStorage: false,
        workers: false,
        digest: false
    };
    
    $('#dialog-help li[data-feature="html5"]').toggle(filesender.supports.reader);
    $('#dialog-help li[data-feature="nohtml5"]').toggle(!filesender.supports.reader);
});

<?php } ?>
