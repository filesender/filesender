// JavaScript Document

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

$(function() {
    var page = $('.download_page');
    if(!page.length) return;
    
    // Bind file selectors
    page.find('.file .select').on('click', function() {
        var el = $(this);
        var f = el.closest('.file');
        
        var selected = f.attr('data-selected') == '1';
        selected = !selected;
        f.attr('data-selected', selected ? '1' : '0');
        
        el.toggleClass('fa-square-o', !selected).toggleClass('fa-check-square-o', selected);
    });
    
    // Bind global selector
    page.find('.toggle-select-all').on('click', function() {
        var el = $(this);
        
        var selected = el.hasClass('fa-check-square-o');
        selected = !selected;
        el.toggleClass('fa-square-o', !selected).toggleClass('fa-check-square-o', selected);
        
        var files = page.find('.file');
        files.attr('data-selected', selected ? '1' : '0');
        
        var selectors = page.find('.file .select');
        selectors.push(el.find('.fa'));
        selectors.toggleClass('fa-square-o', !selected).toggleClass('fa-check-square-o', selected);
    });
    
    
    // Get recipient token
    var m = window.location.search.match(/token=([0-9a-f-]+)/);
    var token = m[1];
    var $this = this;
    var dl = function(ids, confirm, encrypted, progress, archive_format ) {
        if(typeof ids == 'string') ids = [ids];
        
        // the dlcb handles starting the download for
        // all non encrypted downloads
        var dlcb = function(notify) {
            notify = notify ? '&notify_upon_completion=1' : '';
            return function() {
                filesender.ui.redirect( filesender.config.base_path
                                        + 'download.php?token=' + token
                                        + '&archive_format=' + archive_format
                                        + '&files_ids=' + ids.join(',') + notify);
            };
        };
        if (!encrypted && confirm){
            filesender.ui.confirm(lang.tr('confirm_download_notify'), dlcb(true), dlcb(false), true);
        }else{
            if(encrypted){
                if(!filesender.supports.crypto ) {
                    filesender.ui.alert('error', lang.tr('file_encryption_description_disabled'));
                    return;
                }
                
                var filename    = $($this).find("[data-id='" + ids[0] + "']").attr('data-name');
                var mime        = $($this).find("[data-id='" + ids[0] + "']").attr('data-mime');
                var key_version = $($this).find("[data-id='" + ids[0] + "']").attr('data-key-version');
                var salt        = $($this).find("[data-id='" + ids[0] + "']").attr('data-key-salt');
                var password_version  = $($this).find("[data-id='" + ids[0] + "']").attr('data-password-version');
                var password_encoding = $($this).find("[data-id='" + ids[0] + "']").attr('data-password-encoding');
                var password_hash_iterations = $($this).find("[data-id='" + ids[0] + "']").attr('data-password-hash-iterations');

                window.filesender.crypto_app().decryptDownload( filesender.config.base_path
                                                                + 'download.php?token=' + token
                                                                + '&files_ids=' + ids.join(','),
                                                                mime, filename, key_version, salt,
                                                                password_version, password_encoding,
                                                                password_hash_iterations,
                                                                progress);
            }else{
                var notify = false;
                dlcb( notify ).call();
            }
        }
    };
    
    // Bind download buttons
    page.find('.file .download').button().on('click', function() {
        var id = $(this).closest('.file').attr('data-id');
        var encrypted = $(this).closest('.file').attr('data-encrypted');
        var progress = $(this).closest('.file').find('.downloadprogress');
        var transferid = $('.transfer').attr('data-id');

        filesender.client.getTransferOption(transferid, 'enable_recipient_email_download_complete', token, function(dl_complete_enabled){
            dl(id, dl_complete_enabled, encrypted, progress);
        });
        
        return false;
    });

    var dlArchive = function( archive_format ) {
        var ids = [];
        page.find('.file[data-selected="1"]').each(function() {
            ids.push($(this).attr('data-id'));
        });
        
        if(!ids.length) { // No files selected, supose we want all of them
            page.find('.file').each(function() {
                ids.push($(this).attr('data-id'));
            });
        }
        
        
        var transferid = $('.transfer').attr('data-id');
        
        filesender.client.getTransferOption(transferid, 'enable_recipient_email_download_complete', token, function(dl_complete_enabled){
            dl(ids, dl_complete_enabled, null, null, archive_format );
        });
        
        return false;
    };
    
    // Bind archive download button
    page.find('.archive .archive_download').button().on('click', function() {
        return dlArchive( 'zip' );
    });
    page.find('.archive .archive_tar_download').button().on('click', function() {
        return dlArchive( 'tar' );
    });
    
    var macos = navigator.platform.match(/Mac/);
    var linuxos = navigator.platform.match(/Linux/);
    if( !macos )
        $('.mac_archive_message').hide();

    // only worry the user with this banner if any files are encrypted
    // and they will not be able to download them.
    var transfer_is_encrypted = $('.transfer_is_encrypted').text()==1;
    if( transfer_is_encrypted && !filesender.supports.crypto ) 
        $('.crypto_not_supported_message').show();

    page.find('.toggle-select-all').trigger('click');

    button_zipdl = page.find('.archive_download_frame');
    button_tardl = page.find('.archive_tar_download_frame');
    if( macos || linuxos ) {
        button_tardl.addClass('recommended');
    } else {
        button_zipdl.addClass('recommended');
    }
    
});
