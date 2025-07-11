// JavaScript Document

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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
    var verificationCodePassed = true;
    var verificationCodeObjectThatTiggeredEvent = null;
    var verificationCodePassedPopup = null;

    if( window.filesender.config.download_verification_code_enabled ) {
        verificationCodePassed = false;
    }
    
    window.filesender.pbkdf2dialog.setup( true );

    var updateSelectedFilesForArchiveDownload = function()  {
        var ids = [];
        page.find('.file[data-selected="1"]').each(function() {
            ids.push($(this).attr('data-id'));
        });
        var idlist = ids.join(',');
        $('.archivefileids').attr('value', idlist );
    }
    
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
                if( archive_format ) {
                    console.log("Starting download using POST method...");
                    $('#dlarchivepostformat').attr( 'value', archive_format );
                    updateSelectedFilesForArchiveDownload();
                    $('#dlarchivepost').submit();
                } else {
                    filesender.ui.redirect( filesender.config.base_path
                                            + 'download.php?token=' + token
                                            + '&archive_format=' + archive_format
                                            + '&files_ids=' + ids.join(',') + notify);
                }
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

                var crypto_app = window.filesender.crypto_app();

                if( window.filesender.config.use_streamsaver ) {
                    var streamsaverenabled = page.find('#streamsaverenabled').is(':checked');
                    crypto_app.disable_streamsaver = !streamsaverenabled;
                }
                console.log("download page has worked out if streamsaver should be disabled: " , crypto_app.disable_streamsaver );

                if( archive_format || ids.length > 1 ) {
                    //
                    // Stream encrypted files to browser and add the decrypted content
                    // into a zip64 file in the browser.
                    //
                    var onFileOpen = function( blobSink, fileid )
                    {
                        var progress = $($this).find("[data-id='" + fileid + "']").find('.downloadprogress');
                        progress.html("");
                        blobSink.progress = progress;

                        var overall = $($this).find(".archive_message");


                        var msg = lang.tr('encrypted_archive_download_overall_progress').r(
                            {
                                id: 0
                                , currentfilenumber:    blobSink.currentFileNumber+1
                                , totalfilestodownload: blobSink.totalFilesToDownload
                            }).out();
                        overall.html(msg);
                    };
                    var onFileClose = function( blobSink, fileid )
                    {
                        var progress = $($this).find("[data-id='" + fileid + "']").find('.downloadprogress');
                        progress.html(window.filesender.config.language.download_complete);
                        
                    };
                    var onComplete = function( blobSink )
                    {
                        var overall = $($this).find(".archive_message");
                        overall.html(window.filesender.config.language.download_complete);
                    };

                    // generate zip in browser from decrypted files.
                    var selectedFiles = [];
                    var i = 0;
                    for(; i < ids.length; i++ ) {

                        var fileaead = $($this).find("[data-id='" + ids[i] + "']").attr('data-fileaead');
                        var key_version = $($this).find("[data-id='" + ids[i] + "']").attr('data-key-version');
                        var fileivcoded = $($this).find("[data-id='" + ids[i] + "']").attr('data-fileiv');
                        var transferid = $('.transfer').attr('data-id');
                        var chunk_size         = $($this).find("[data-id='" + ids[0] + "']").attr('data-chunk-size');
                        var crypted_chunk_size = $($this).find("[data-id='" + ids[0] + "']").attr('data-crypted-chunk-size');
                        
                        selectedFiles.push({
                            fileid:ids[i]
                            , filename:$($this).find("[data-id='" + ids[i] + "']").attr('data-name')
                            , filesize:$($this).find("[data-id='" + ids[i] + "']").attr('data-size')
                            , encrypted_filesize:$($this).find("[data-id='" + ids[i] + "']").attr('data-encrypted-size')
                            , mime:$($this).find("[data-id='" + ids[i] + "']").attr('data-mime')
                            , key_version:$($this).find("[data-id='" + ids[i] + "']").attr('data-key-version')
                            , salt:$($this).find("[data-id='" + ids[i] + "']").attr('data-key-salt')
                            , password_version:$($this).find("[data-id='" + ids[i] + "']").attr('data-password-version')
                            , password_encoding:$($this).find("[data-id='" + ids[i] + "']").attr('data-password-encoding')
                            , password_hash_iterations:$($this).find("[data-id='" + ids[i] + "']").attr('data-password-hash-iterations')
                            , client_entropy:$($this).find("[data-id='" + ids[i] + "']").attr('data-client-entropy')
                            , fileiv:window.filesender.crypto_app().decodeCryptoFileIV(fileivcoded,key_version)
                            , fileaead:fileaead.length?atob(fileaead):null
                            , transferid:transferid
                        });

                        // clear any previous progress message
                        var progress = $($this).find("[data-id='" + ids[i] + "']").find('.downloadprogress');
                        progress.html("");
                    }
                    crypto_app.decryptDownloadToZip( filesender.config.base_path
                                                     + 'download.php?token=' + token
                                                     + '&files_ids='
                                                     , transferid
                                                     , chunk_size
                                                     , crypted_chunk_size
                                                     , selectedFiles
                                                     , progress
                                                     , onFileOpen, onFileClose, onComplete
                                                   );

                    
                }
                else
                {
                    // single file download
                    var transferid  = $('.transfer').attr('data-id');
                    var chunk_size         = $($this).find("[data-id='" + ids[0] + "']").attr('data-chunk-size');
                    var crypted_chunk_size = $($this).find("[data-id='" + ids[0] + "']").attr('data-crypted-chunk-size');
                    var filename    = $($this).find("[data-id='" + ids[0] + "']").attr('data-name');
                    var filesize    = $($this).find("[data-id='" + ids[0] + "']").attr('data-size');
                    var encrypted_filesize=$($this).find("[data-id='" + ids[0] + "']").attr('data-encrypted-size');
                    var mime        = $($this).find("[data-id='" + ids[0] + "']").attr('data-mime');
                    var key_version = $($this).find("[data-id='" + ids[0] + "']").attr('data-key-version');
                    var salt        = $($this).find("[data-id='" + ids[0] + "']").attr('data-key-salt');
                    var password_version  = $($this).find("[data-id='" + ids[0] + "']").attr('data-password-version');
                    var password_encoding = $($this).find("[data-id='" + ids[0] + "']").attr('data-password-encoding');
                    var password_hash_iterations = $($this).find("[data-id='" + ids[0] + "']").attr('data-password-hash-iterations');
                    var client_entropy = $($this).find("[data-id='" + ids[0] + "']").attr('data-client-entropy');
                    var fileiv   = $($this).find("[data-id='" + ids[0] + "']").attr('data-fileiv');
                    var fileaead = $($this).find("[data-id='" + ids[0] + "']").attr('data-fileaead');
                    if( fileaead.length ) {
                        fileaead = atob(fileaead);
                    }

                    window.filesender.crypto_encrypted_archive_download = false;
                    crypto_app.decryptDownload( filesender.config.base_path
                                                + 'download.php?token=' + token
                                                + '&files_ids=' + ids.join(','),
                                                transferid, chunk_size, crypted_chunk_size,
                                                mime, filename, filesize, encrypted_filesize,
                                                key_version, salt,
                                                password_version, password_encoding,
                                                password_hash_iterations,
                                                client_entropy,
                                                window.filesender.crypto_app().decodeCryptoFileIV(fileiv,key_version),
                                                fileaead,
                                                progress );
                }
            }
            else
            {
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

        verificationCodeObjectThatTiggeredEvent = $(this);
        if( !verificationCodePassed ) {
            verificationCodePassedPopup = filesender.ui.relocatePopup($(".verify_email_to_download"));
            return false;
        }

        
        filesender.client.getTransferOption(transferid, 'enable_recipient_email_download_complete', token, function(dl_complete_enabled){
            dl(id, dl_complete_enabled, encrypted, progress );
        });        
        return false;
    });
    
    var dlArchive = function( archive_format, button ) {
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
        var encrypted = $('.transfer_is_encrypted').text()==1;

        verificationCodeObjectThatTiggeredEvent = button;
        if( !verificationCodePassed ) {
            verificationCodePassedPopup = filesender.ui.relocatePopup($(".verify_email_to_download"));
            return false;
        }

        
        filesender.client.getTransferOption(transferid, 'enable_recipient_email_download_complete', token, function(dl_complete_enabled){
            dl(ids, dl_complete_enabled, encrypted, null, archive_format );
        });
        
        return false;
    };
    
    // Bind archive download button
    page.find('.archive .archive_download').button().on('click', function() {
        return dlArchive( 'zip', $(this) );
    });
    page.find('.archive .archive_tar_download').button().on('click', function() {
        return dlArchive( 'tar', $(this) );
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


    if( window.filesender.config.download_verification_code_enabled ) {

        var transferid = $('.transfer').attr('data-id');
        var rid        = $('.rid').attr('data-id');
        
        page.find('.verificationcodesendtoemail').button().on('click', function() {
            filesender.client.sendVerificationCodeToYourEmailAddress(
                transferid,
                function() {
                    window.filesender.ui.notify("info", lang.tr("email_sent"));
                });
            return true;
        });
        page.find('.verificationcodesend').button().on('click', function() {
            var pass = $('#verificationcode').val();
            if( !pass.length ) {
                return true;
            }
            try
            {
                var options = { error: function(e) {
                    if( e.message == 'rest_data_stale' ) {
                        window.filesender.ui.alert("error", lang.tr("verification_code_is_too_old"));
                        return;
                    }
                    filesender.ui.error(e);
                }};
                
                

                filesender.client.checkVerificationCodeWithServer(
                    transferid, pass,
                    function(args) {
                        if( args.ok === true ) {
                            verificationCodePassed = true;
                            $(".verify_email_to_download").dialog( "close" );
                            
                            var encrypted = verificationCodeObjectThatTiggeredEvent.closest('.file').attr('data-encrypted');
                            var msg = "downloading";
                            if( !encrypted ) {
                                window.filesender.ui.notify("info", lang.tr(msg));
                            }
                            verificationCodeObjectThatTiggeredEvent.click();
                        } else {
                            window.filesender.ui.alert("error", lang.tr("verification_code_did_not_match"));
                        }
                    }
                    , options
                );
            }
            catch( exception ) {
            }
            
            return true;
        });
    }
    
});
