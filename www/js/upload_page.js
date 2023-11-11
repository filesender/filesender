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


// This is a duplicate, it should be moved to a common.js file
function isIE11()
{
    if(navigator.userAgent.indexOf('MSIE')!==-1
       || navigator.appVersion.indexOf('Trident/') > -1)
    {
        return true;
    }
    return false;
}
function isEdge()
{
    if(navigator.userAgent.indexOf(' Edge/')!==-1) {
        return true;
    }
    return false;
}
function use_webasm_pbkdf2_implementation()
{
    return isIE11() || isEdge();
}

function delayAndCallOnlyOnce(callback, ms) {
    var timer = 0;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
            callback.apply(context, args);
        }, ms || 0);
    };
}

function useWebNotifications()
{
    var ret = ('web_notification_when_upload_is_complete' in filesender.ui.nodes.options) ? filesender.ui.nodes.options.web_notification_when_upload_is_complete.is(':checked') : false;
    return ret;
}


/**
 * apply a 'bad' class to the obj if b==true
 * the useExplicitGoodClass can be set to true and a 'good' css class 
 * will be added to explicitly indicate success. Otherwise all good/bad
 * etc classes are removed when b==false.
 */
function applyBadClass( obj, b, useExplicitGoodClass ) {
    if( b ) {
        obj.removeClass('good');
        obj.removeClass('middle');
        obj.removeClass('slow');
        obj.removeClass('paused');
        obj.addClass('bad');
    } else {
        obj.removeClass('middle');
        obj.removeClass('slow');
        obj.removeClass('bad');
        obj.removeClass('paused');
        if( useExplicitGoodClass ) {
            obj.addClass('good');
        } else {
            obj.removeClass('good');
        }
    }
}

function pause( changeTextElements )
{
    filesender.ui.transfer.pause();
    filesender.ui.nodes.buttons.pause.addClass('not_displayed');
    filesender.ui.nodes.buttons.resume.removeClass('not_displayed');
    filesender.ui.nodes.buttons.resume.button('enable');
    if( changeTextElements ) {
        filesender.ui.nodes.seconds_since_data_sent_info.text('');
        filesender.ui.nodes.stats.average_speed.find('.value').text(lang.tr('paused'));
        filesender.ui.nodes.stats.estimated_completion.find('.value').text('');
        filesender.ui.setTimeSinceDataWasLastSentMessage(lang.tr('paused'));
    }
}

function resume( force, resetResumeCount )
{
    filesender.ui.nodes.buttons.pause.removeClass('not_displayed');
    filesender.ui.nodes.buttons.resume.addClass('not_displayed');
    filesender.ui.cancelAutomaticResume();
    filesender.ui.resumingUpload();
    filesender.ui.uploading_again_started_at_time_touch();

    if( resetResumeCount ) {
        // explicit action resets the automatic resume counters
        if( filesender.ui.automatic_resume_retries ) {
            filesender.ui.automatic_resume_retries = 0;
        }
    }
    if( force ) {
        window.filesender.pbkdf2dialog.reset();
        filesender.ui.transfer.retry( force );
    } else {
        filesender.ui.transfer.resume();
    }
    filesender.ui.resumeScheduled = false;
    filesender.ui.uploading_again_started_at_time_touch();
}

/**
 * Because the checkEncryptionPassword() uses toggle and also wants to read
 * the state of the message to see if it should call toggle there is a potential
 * issue of stale data. We must make slideToggleDelay less than checkEncryptionPassword_delay
 * to allow any toggle to have completed so that the next call to checkEncryptionPassword()
 * can read the state of the toggled element and not get confused.
 */
var checkEncryptionPassword_slideToggleDelay = 200;
var checkEncryptionPassword_delay = 300;

if(!('filesender' in window)) window.filesender = {};
if(!('ui'         in window.filesender)) window.filesender.ui = {};
if(!('elements'   in window.filesender.ui)) window.filesender.ui.elements = {};

/**
 * Update the UI element at uielement only once every delayMS time interval.
 * While the first delayMS interval is passing show the string initString.
 */
filesender.ui.elements.nonBusyUpdater = function( uielement, delayMS, initString ) {
    return {
        e: uielement,
        lastUpdate: null,
        update: function( v ) {
            var $this = this;
            t = (new Date()).getTime();
            if( $this.lastUpdate && ($this.lastUpdate + delayMS < t )) {
                $this.e.text( v );
            }
            if( !$this.lastUpdate ) {
                $this.e.text( initString );
            }
            if( !$this.lastUpdate || ($this.lastUpdate + delayMS < t )) {
                $this.lastUpdate = t;
            }
        }
    }
};


// prevent the element from having an empty string.
filesender.ui.elements.preventEmpty = function(el) {
    var originalValue = '';
    el.on( 'focus', function(e) { originalValue = e.target.value; } );
    el.on( 'blur',  function(e) {
        if( e.target.value == '' ) {
            filesender.ui.setDateFromEpochData( filesender.ui.nodes.expires );
        }
    });
    return this;
}



// Manage files
filesender.ui.files = {
    invalidFiles: [],

    // Sort error cases to the top
    sortErrorLinesToTop: function() {
        var $selector = $("#fileslist");
        var $element = $selector.children(".file");
        
        $element.sort(function(a, b) {
            var ainv = a.className.includes('invalid');
            var binv = b.className.includes('invalid');
            // no difference for files in same group
            if( (ainv && binv) || (!ainv && !binv )) {
                return 0;
            }
            if( ainv ) return -1;
            if( binv ) return  1;
            return 0;
        });
        
        $element.detach().appendTo($selector);
    },
    
    // File selection (browse / drop) handler
    addList: function(files, source_node) {
        var node = null;
        for(var i=0; i<files.length; i++) {
            var file_name = files[i].name;
            if(typeof files[i].webkitRelativePath === "string"
               && files[i].webkitRelativePath != "" )
            {
                file_name = files[i].webkitRelativePath;
            }
            
            var latest_node = filesender.ui.files.addFile(file_name, files[i], false, source_node);
            if (latest_node) {
                node = latest_node;
            }
        }
        
        filesender.ui.evalUploadEnabled();
        filesender.ui.nodes.files.list.scrollTop(filesender.ui.nodes.files.list.prop('scrollHeight'));
        
        this.sortErrorLinesToTop();
        return node;
    },
    
    addFile: function(filepath, fileblob, isSingleOperation, source_node) {
        var filesize = fileblob.size;
        var node = null;
            var info = filepath + ' : ' + filesender.ui.formatBytes(filesize);
            node = $('<div class="file" />').attr({
                'data-name': filepath,
                'data-size': filesize
            }).appendTo(filesender.ui.nodes.files.list);
            
            $('<span class="info" />').text(info).attr({title: info}).appendTo(node);
            
            if(filesender.ui.nodes.required_files) {
                // Upload restart mode
                var req = filesender.ui.nodes.required_files.find('.file[data-name="' + filepath + '"][data-size="' + filesize + '"]');
                
                if(!req.length) {
                    filesender.ui.alert('error', lang.tr('unexpected_file'));
                    node.remove();
                    return null;
                }
                
                var file = req.data('file');
                var added_cid = req.attr('data-cid');
                file.cid = added_cid;
                file.blob = fileblob;
                
                filesender.ui.transfer.files.push(file);
                
                req.remove();
                
            } else {
                // Normal upload mode
                $('<span class="remove fa fa-minus-square fa-lg" />').attr({
                    title: lang.tr('click_to_delete_file')
                }).on('click', function() {
                    var el = $(this).parent();
                    var cid = el.attr('data-cid');
                    var name = el.attr('data-name');
                    
                    var total_size = 0;
                    for(var j=0; j<filesender.ui.transfer.files.length; j++)
                        total_size += filesender.ui.transfer.files[j].size;
                    
                    if(cid) filesender.ui.transfer.removeFile(cid);
                    
                    $(this).parent().remove();
                    
                    if(!filesender.ui.nodes.files.list.find('div').length)
                        filesender.ui.nodes.files.clear.button('disable');
                    
                    var iidx = filesender.ui.files.invalidFiles.indexOf(name);
                    if (iidx === -1){
                        var size = 0;
                        for(var j=0; j<filesender.ui.transfer.files.length; j++)
                            size += filesender.ui.transfer.files[j].size;
                        filesender.ui.nodes.stats.number_of_files.show().find('.value').text(filesender.ui.transfer.files.length + '/' + filesender.config.max_transfer_files);
                        filesender.ui.nodes.stats.size.show().find('.value').text(filesender.ui.formatBytes(size) + '/' + filesender.ui.formatBytes(filesender.config.max_transfer_size));
                        
                    } else {
                        filesender.ui.files.invalidFiles.splice(iidx, 1);
                    }
                    
                    filesender.ui.evalUploadEnabled();
                }).appendTo(node);
                
                var added_cid = filesender.ui.transfer.addFile(filepath, fileblob, function(error) {
                    var tt = 1;
                    if(error.details && error.details.filename) filesender.ui.files.invalidFiles.push(error.details.filename);
                    node.addClass('invalid');
                    node.addClass(error.message);
                    $('<span class="invalid fa fa-exclamation-circle fa-lg" />').prependTo(node.find('.info'))
                    var invalidreason = lang.tr(error.message);
                    if(error.message == 'invalid_file_name') {
                        invalidreason += ' ' + lang.tr('starting_at') + ' ' + error.details.badEnding;
                    }
                    $('<div class="invalid_reason" />').text( invalidreason ).appendTo(node);
                }, source_node);
                
                filesender.ui.nodes.files.clear.button('enable');
                
                if(added_cid === false) return node;
            }

            if( isSingleOperation ) {
                filesender.ui.evalUploadEnabled();
            }
            node.attr('data-cid', added_cid);

            var bar = $('<div class="progressbar" />').appendTo(node);
            $('<div class="progress-label" />').appendTo(bar);
            bar.progressbar({
                value: false,
                max: 1000,
                change: function() {
                    var bar = $(this);
                    var v = bar.progressbar('value');
                    if (v === false) {
                        // stop progress showing 0.0% when the value is false
                        bar.find('.progress-label').text('');
                    } else {
                        bar.find('.progress-label').text((v / 10).toFixed(1) + '%');
                    }
                },
                complete: function() {
                    var bar = $(this);
                    bar.closest('.file').addClass('done');
                }
            });
            
            $('<span class="fa fa-lg fa-check done_icon" />').appendTo(node);

            if (filesender.config.upload_display_per_file_stats) {
                var p = $('<span class="workercrust"/>');
                p.appendTo(node);

                var makeCrust = function( p, idx ) {
                    var crust_meter = $('<div class="crust crust' + idx + '">'
                                        + '  <a class="crust_meter" href="#">'
                                        + '  <div class="label crustage   uploadthread">   </div></a>'
                                        + '  <a class="crust_meter_bytes" href="#">'
                                        + '  <div class="label crustbytes uploadthread">   </div></a>'
                                        + '</div>');
                    crust_meter.appendTo(p);
                    crust_meter.button({disabled: true});

                    return crust_meter;
                };

                if( filesender.config.terasender_enabled ) {
                    for( idx=0; idx < filesender.config.terasender_worker_count; idx++ ) {
                        var crust_meter = makeCrust( p, idx );
                    }
                }
                else {
                    var crust_meter = makeCrust( p, 0 );
                }

            }
            
            if(filesender.ui.nodes.required_files) {
                if(file) {
                    bar.show().progressbar('value', Math.floor(1000 * file.uploaded / file.size));
                }
                
            } else {
                var size = 0;
                for(var j=0; j<filesender.ui.transfer.files.length; j++)
                    size += filesender.ui.transfer.files[j].size;
                
                filesender.ui.nodes.stats.number_of_files.show().find('.value').text(filesender.ui.transfer.files.length + '/' + filesender.config.max_transfer_files);
                filesender.ui.nodes.stats.size.show().find('.value').text(filesender.ui.formatBytes(size) + '/' + filesender.ui.formatBytes(filesender.config.max_transfer_size));
            }
            
            node.attr('index', filesender.ui.transfer.files.length - 1);
        
        if( isSingleOperation ) {
            filesender.ui.nodes.files.list.scrollTop(filesender.ui.nodes.files.list.prop('scrollHeight'));
        }
        
        return node;
    },
    
    update_crust_meter_for_worker: function(file,idx,v,b) {

        var vd = -1;
        
        var crust_indicator = filesender.ui.nodes.files.list.find('[data-cid="' + file.cid + '"] .crust' + idx);
        if( crust_indicator ) {
            vd = v / 1000;
            if( v == -1 ) {
                crust_indicator.find('.crustage').text( "" );
            } else {
                crust_indicator.find('.crustage').text( vd.toFixed(2) );
            }

            crust_indicator.find('.crustbytes').text( '' );
            applyBadClass( crust_indicator, b, true );

            return vd;
        }
    },

    clear_crust_meter_all: function() {
        for (var i = 0; i < filesender.ui.transfer.files.length; i++) {
            file = filesender.ui.transfer.files[i];
            filesender.ui.files.clear_crust_meter( file );
        }
    },
    
    clear_crust_meter: function( file ) {
        var imax = 1;
        if( filesender.config.terasender_enabled ) {
            imax = filesender.config.terasender_worker_count;
        }

        for( var i = 0; i < imax; i++ ) {
            var crust_indicator = filesender.ui.nodes.files.list.find('[data-cid="' + file.cid + '"] .crust' + i);
            if( crust_indicator ) {
                crust_indicator.find('.crustage').text( '' );
                crust_indicator.find('.crustbytes').text( '' );
                crust_indicator.removeClass('middle');
                crust_indicator.removeClass('slow');
                crust_indicator.removeClass('bad');
                crust_indicator.removeClass('paused');
                crust_indicator.addClass('good');
            }
        }
    },

    pause_crust_meter: function( file ) {
        var imax = 1;
        if( filesender.config.terasender_enabled ) {
            imax = filesender.config.terasender_worker_count;
        }

        for( var i = 0; i < imax; i++ ) {
            var crust_indicator = filesender.ui.nodes.files.list.find('[data-cid="' + file.cid + '"] .crust' + i);
            if( crust_indicator ) {
                crust_indicator.find('.crustage').text( '' );
                crust_indicator.find('.crustbytes').text( '' );
                crust_indicator.removeClass('middle');
                crust_indicator.removeClass('slow');
                crust_indicator.removeClass('bad');
                crust_indicator.removeClass('good');
                crust_indicator.addClass('paused');
            }
        }
    },
    
    update_crust_meter: function( file ) {
//        window.filesender.log("update_crust_meter(top) status " +  filesender.ui.transfer.status );
        if (!filesender.config.upload_display_per_file_stats) {
            return;
        }
        
        if (filesender.ui.transfer.status != 'running') {
            this.clear_crust_meter( file );
            if (filesender.ui.transfer.status == 'paused') {
                this.pause_crust_meter( file );
            }
            return;
        }

        var durations = filesender.ui.transfer.getMostRecentChunkDurations( file );
        var bytes     = filesender.ui.transfer.getMostRecentChunkFineBytes( file );
        var offending = filesender.ui.transfer.getIsWorkerOffending( file );
        if( durations.length != bytes.length || bytes.length != offending.length ) {
            filesender.ui.log('WARNING worker tracking stats are wrong' );
            return;
        }
        if( durations.length < 1 ) {
            filesender.ui.log('WARNING worker tracking stats are missing' );
            return;
        }
        var imax = durations.length;
        if( filesender.config.terasender_enabled && imax != filesender.config.terasender_worker_count ) {
            filesender.ui.log('WARNING ts worker tracking stats are too few' );
            return;
        }

        var anyOffending = false;
        var maxV = 0;
        for( i=0; i < imax; i++ ) {
            v = -1;
            if( i < durations.length ) {
                v = durations[i];
            }
            b = false;
            if( i < offending.length ) {
                b = offending[i];
            }
            filesender.ui.files.update_crust_meter_for_worker( file, i, v, b );

            // calculate stats across all workers
            if( v > -1 ) {
                maxV = Math.max( v, maxV );
            }
            anyOffending = anyOffending || b;
        }
        
        filesender.ui.setMilliSecondsSinceDataWasLastSent( maxV, anyOffending );
    },
    
    // Update progress bar, run in transfer context
    progress: function(file, complete) {
        var size = 0;
        var uploaded = 0;
        for(var i=0; i<this.files.length; i++) {
            size += this.files[i].size;
            uploaded += this.files[i].fine_progress ? this.files[i].fine_progress : this.files[i].uploaded;
        }
        
        var currentTime = (new Date()).getTime();
        if (this.pause_length > 0){
            var time = currentTime - this.pause_length - this.time;
        }else{
            var time = currentTime - this.time;            
        }
        
        var speed = uploaded / (time / 1000);

        var remaining = size - uploaded;
        var eta = 0;
        if( remaining && speed ) {
            eta = remaining / speed;
        }
        filesender.ui.nodes.stats.estimated_completion_updater.update( filesender.ui.formatETA(eta));

        
        if (filesender.config.upload_display_bits_per_sec)
            speed *= 8;
        
        filesender.ui.nodes.stats.uploaded.find('.value').html(uploaded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' bytes<br/>' + filesender.ui.formatBytes(uploaded) + ' /' + filesender.ui.formatBytes(size));
        
        if(this.status != 'paused')
            filesender.ui.nodes.stats.average_speed.find('.value').text(filesender.ui.formatSpeed(speed));
        
        var bar = filesender.ui.nodes.files.list.find('[data-cid="' + file.cid + '"] .progressbar');
        upload_progress = Math.floor(1000 * (file.fine_progress ? file.fine_progress : file.uploaded) / file.size);
        // check to see if upload_progress is 100% or complete is true
        // so that we don't mark it as complete before the
        // upload is validated
        if (upload_progress < 1000 || complete === true) {
            bar.progressbar('value', upload_progress);
        } else {
            // Go stripey for validation
            bar.progressbar('value', false);
        }
    },
    
    // Clear the file box
    clear: function() {
        filesender.ui.transfer.files = [];
        
        filesender.ui.nodes.files.input.val('');
        
        filesender.ui.nodes.files.list.find('div').remove();
        
        filesender.ui.nodes.files.clear.button('disable');
        
        filesender.ui.nodes.stats.number_of_files.hide().find('.value').text('');
        filesender.ui.nodes.stats.size.hide().find('.value').text('');
        
        filesender.ui.evalUploadEnabled();
    },

    updatePasswordMustHaveMessage: function(slideMessage,invalid,msg) {

        if( slideMessage ) {
            
            if(invalid) {
                if( msg.css('display')=='none') {
                    msg.slideToggle( checkEncryptionPassword_slideToggleDelay );
                }
            }else{
                if( msg.css('display')!='none') {
                    msg.slideToggle( checkEncryptionPassword_slideToggleDelay );
                }
            }
        }
        
    },
    
    checkEncryptionPassword: function(input,slideMessage) {
        input = $(input);
        var crypto = window.filesender.crypto_app();
        var pass = input.val();
        var invalid = false;
        var msg = null;

        var use_encryption = filesender.ui.nodes.encryption.toggle.is(':checked');
        if( !use_encryption ) {
            $('.passwordvalidation').each(function( index ) {
                $((this)).hide();
            });
            return true;
        }
        
        
        if( filesender.ui.transfer.encryption_password_version == 
            crypto.crypto_password_version_constants.v2018_text_password )
        {
            var minLength = filesender.config.encryption_min_password_length;
            if( minLength > 0 ) {
                var testInvalid = false;
                if( !pass || pass.length < minLength ) {
                    testInvalid = true;
                    msg = $('#encryption_password_container_too_short_message');
                    invalid = true;
                }
                filesender.ui.files.updatePasswordMustHaveMessage(
                    slideMessage, testInvalid,
                    $('#encryption_password_container_too_short_message'));
            }
        }

        var v = filesender.ui.nodes.encryption.use_generated.is(':checked');
        if( v ) {
            $('.passwordvalidation').each(function( index ) {
                $((this)).hide();
            });
            return true;
        }
  
        //
        // Very long text passwords might be allowed by sys admin.
        //
        var phrasePass = false;
        if( filesender.config.encryption_password_text_only_min_password_length > 0 ) {
            var phraseLen = filesender.config.encryption_password_text_only_min_password_length;
            if( pass ) {
                var passNonRepeating = !(/^(.)\1+$/.test(pass));
                
                if( passNonRepeating && pass.length >= phraseLen ) {
                    phrasePass = true;

                    filesender.ui.files.updatePasswordMustHaveMessage(
                        slideMessage, false,
                        $('#encryption_password_container_can_have_text_only_min_password_length_message'));
                    
                    testInvalid = false;
                    filesender.ui.files.updatePasswordMustHaveMessage(
                        slideMessage, testInvalid,
                        $('#encryption_password_container_must_have_upper_and_lower_case_message'));
                    filesender.ui.files.updatePasswordMustHaveMessage(
                        slideMessage, testInvalid,
                        $('#encryption_password_container_must_have_numbers_message'));
                    filesender.ui.files.updatePasswordMustHaveMessage(
                        slideMessage, testInvalid,
                        $('#encryption_password_container_must_have_special_characters_message'));
                } else {
                    filesender.ui.files.updatePasswordMustHaveMessage(
                        slideMessage, true,
                        $('#encryption_password_container_can_have_text_only_min_password_length_message'));
                }
            } else {
                filesender.ui.files.updatePasswordMustHaveMessage(
                    slideMessage, true,
                    $('#encryption_password_container_can_have_text_only_min_password_length_message'));
            }
        }

        if( !phrasePass ) {
            
            if( filesender.config.encryption_password_must_have_upper_and_lower_case ) {
                var testInvalid = false;
                if(!pass.match(/[A-Z]/g) || !pass.match(/[a-z]/g)) {
                    testInvalid = true;
                    invalid = true;
                }
                filesender.ui.files.updatePasswordMustHaveMessage(
                    slideMessage, testInvalid,
                    $('#encryption_password_container_must_have_upper_and_lower_case_message'));
                
            }
            if( filesender.config.encryption_password_must_have_numbers ) {
                var testInvalid = false;
                if(!pass.match(/[0-9]/g)) {
                    testInvalid = true;
                    invalid = true;
                }
                filesender.ui.files.updatePasswordMustHaveMessage(
                    slideMessage, testInvalid,
                    $('#encryption_password_container_must_have_numbers_message'));
            }
            if( filesender.config.encryption_password_must_have_special_characters ) {
                var testInvalid = false;
                if(!pass.match(/[@#!$%^&*()\[\]<>?\/\\]/g)) {
                    testInvalid = true;
                    invalid = true;
                }
                filesender.ui.files.updatePasswordMustHaveMessage(
                    slideMessage, testInvalid,
                    $('#encryption_password_container_must_have_special_characters_message'));
            }
        }
        
        if( slideMessage ) {
            
            if(invalid) {
                input.addClass('invalid');
            }else{
                input.removeClass('invalid');
            }
        }
        return !invalid;
    },
};

// Manage recipients
filesender.ui.recipients = {
    // Add recipient to list
    add: function(email, errorhandler) {
        if(!errorhandler) errorhandler = function(error) {
            filesender.ui.error(error);
        };
        
        var too_much = null;
        if(email.match(/[,;\s]/)) { // Multiple values
            email = email.split(/[,;\s]/);
            var invalid = [];
            for(var i=0; i<email.length; i++) {
                if(too_much) continue;
                
                var s = email[i].replace(/^\s+/g, '').replace(/\s+$/g, '');
                if(!s) continue;
                
                if(this.add(s, function(error) {
                    if(error.message == 'transfer_too_many_recipients')
                        too_much = error;
                }))
                    invalid.push(s);
            }
            
            if(too_much) {
                filesender.ui.error(too_much);
                return '';
            }
            
            return invalid.join(', ');
        }
        
        var added = true;
        filesender.ui.transfer.addRecipient(email, function(error) {
            if(error.message == 'transfer_too_many_recipients')
                too_much = error;
            added = false;
        });
        
        if(too_much) {
            errorhandler(too_much);
            return '';
        }
        
        if(!added) return email;
        
        if(filesender.ui.nodes.recipients.list.find('.recipient[email="' + email + '"]').length) return ''; // Ignore duplicates
        
        var node = $('<div class="recipient" />').attr('email', email).appendTo(filesender.ui.nodes.recipients.list);
        $('<span />').attr('title', email).text(email).appendTo(node);
        $('<span class="remove fa fa-minus-square" />').attr({
            title: lang.tr('click_to_delete_recipient')
        }).on('click', function() {
            var email = $(this).parent().attr('email');
            if(email)
                filesender.ui.transfer.removeRecipient(email);
            
            $(this).parent().remove();
            
            filesender.ui.evalUploadEnabled();
        }).appendTo(node);
        
        filesender.ui.nodes.recipients.list.show();
        
        filesender.ui.evalUploadEnabled();
        
        return '';
    },
    
    // Add recipients from input
    addFromInput: function(input) {
        input = $(input);
        
        var marker = input.data('error_marker');
        if(!marker) {
            marker = $('<span class="invalid fa fa-exclamation-circle fa-lg" />').attr({
                title: lang.tr('invalid_recipient')
            }).hide().insertBefore(input);
            input.data('error_marker', marker);
        }
        
        var invalid = input.val() ? this.add(input.val()) : null;
        
        if(invalid) {
            input.val(invalid);
            input.addClass('invalid');
            marker.show();
        }else{
            input.val('');
            input.removeClass('invalid');
            marker.hide();
        }
    },
    
    // Remove email from list
    remove: function(email) {
        if(email.match(/[,;\s]/)) { // Multiple values
            email = email.split(/[,;\s]/);
            for(var i=0; i<email.length; i++) {
                var s = email[i].replace(/^\s+/g, '').replace(/\s+$/g, '');
                if(s) this.remove(s);
            }
            return;
        }
        
        filesender.ui.transfer.removeRecipient(email);
        
        filesender.ui.nodes.recipients.list.find('[email="' + email + '"]').remove();
        
        if(!filesender.ui.nodes.recipients.list.find('[email]').length)
            filesender.ui.nodes.recipients.list.hide();
        
        filesender.ui.evalUploadEnabled();
    },
    
    // Clear the recipients list
    clear: function() {
        filesender.ui.transfer.recipients = [];
        
        filesender.ui.nodes.recipients.input.val('');
        
        filesender.ui.nodes.recipients.list.find('div').remove();
        
        filesender.ui.evalUploadEnabled();
    },
    
    // Enable autocomplete for frequent recipients on a field
    autocomplete: function(){
        if(!filesender.config.autocomplete.enabled) return;
        
        if(filesender.ui.nodes.guest_token.length) return;
        
        $(filesender.ui.nodes.recipients.input).autocomplete({
            source: function (request, response) {
                if(!filesender.config.internal_use_only_running_on_ci) {
                    filesender.client.getFrequentRecipients(request.term,
                        function (loc,data) {
                            response($.map(data, function (item) { 
                                if (filesender.ui.nodes.recipients.list.find('[email="'+item+'"]').length == 0){
                                    return { 
                                        label: item,
                                        value: item
                                    };
                                }else{
                                    return undefined;
                                }
                            })) 
                        }
                    );
                }
            },
            select: function (event, ui) {
                filesender.ui.recipients.add(ui.item.value);
                
                var marker = $(this).data('error_marker');
        
                $(this).val('');
                $(this).removeClass('invalid');
                if(marker) marker.hide();
                
                return false;
            },
            minLength: filesender.config.autocomplete.min_characters
        });
    }
};

filesender.ui.doesUploadMessageContainPassword = function() {
    if(filesender.ui.nodes.encryption.toggle.is(':checked')) {
        var p = filesender.ui.nodes.encryption.password.val();
        var m = filesender.ui.nodes.message.val();
        if( p && m ) {
            if( m.includes(p)) {
                return true;
            }
        }
    }
    return false;
}

filesender.ui.evalUploadEnabled = function() {
    var ok = true;
    
    if(filesender.ui.nodes.required_files) {
        if(filesender.ui.nodes.required_files.find('.file').length) ok = false;
        
    } else {
        // Check if there is no files with banned extension
        if (filesender.ui.files.invalidFiles.length > 0) ok  = false;
        
        if(!filesender.ui.transfer.files.length) ok = false;
        
        var gal = ('get_a_link' in filesender.ui.nodes.options) ? filesender.ui.nodes.options.get_a_link.is(':checked') : false;
        
        var addme = ('add_me_to_recipients' in filesender.ui.nodes.options) ? filesender.ui.nodes.options.add_me_to_recipients.is(':checked') : false;
        
        if(
            filesender.ui.nodes.need_recipients &&
            !gal && !addme &&
            filesender.ui.nodes.recipients.list.length &&
            !filesender.ui.transfer.recipients.length
        ) ok = false;
    }
    
    if(filesender.ui.nodes.aup.length)
        if(!filesender.ui.nodes.aup.is(':checked')) ok = false;

    if(filesender.ui.nodes.encryption.toggle.is(':checked')) {
        ok = ok && filesender.ui.files.checkEncryptionPassword(filesender.ui.nodes.encryption.password,false );
    }

    if( filesender.ui.doesUploadMessageContainPassword()) {
        if( filesender.config.upload_page_password_can_not_be_part_of_message_handling == 'error' ) {
            ok = false;
        }
    }

    var invalid_nodes = filesender.ui.nodes.files.list.find('.invalid');
    if( invalid_nodes.length ) {
        ok = false;
    }
    
    if(filesender.ui.nodes.required_files) {
        if(ok) filesender.ui.nodes.required_files.hide();
        filesender.ui.nodes.buttons.restart.button(ok ? 'enable' : 'disable');
    } else {
        filesender.ui.nodes.buttons.start.button(ok ? 'enable' : 'disable');
    }
    
    return ok;
};

filesender.ui.automatic_resume_retries = 0;
filesender.ui.automatic_resume_timer = 0;

// the timet that an upload was restarted or 0
filesender.ui.uploading_again_started_at_time = 0;
// touch uploading_again_started_at_time
filesender.ui.uploading_again_started_at_time_touch = function() {
    var t = (new Date()).getTime();
    filesender.ui.uploading_again_started_at_time = t;
}

/**
 * Handy for functions this returns the number of seconds since 
 * filesender.ui.uploading_again_started_at_time
 * to allow functions to only timeout after some grace time.
 */
filesender.ui.uploading_again_started_at_time_diff_to_now = function() {
    var t = (new Date()).getTime();
    return (t - filesender.ui.uploading_again_started_at_time)/1000;
}
/**
 * Call here if you are resuming an upload so that the auto resume timeouts
 * can be reset so a slow start of an upload does not trigger an auto
 * resume too.
 */
filesender.ui.resumingUpload = function() {
    filesender.ui.cancelAutomaticResume();
    filesender.ui.uploading_again_started_at_time_touch();
}
filesender.ui.resumeScheduled = false;
filesender.ui.scheduleAutomaticResume = function(msg) {
    
    filesender.ui.resumeScheduled = true;
    filesender.ui.uploadLogPrepend(msg);
    pause( false );

    filesender.ui.automatic_resume_timer =
        window.setTimeout(
            function() {
                filesender.ui.automatic_resume_timer = 0;
                window.clearInterval(filesender.ui.automatic_resume_timer_countdown);
                filesender.ui.automatic_resume_timer_countdown = 0;

                window.filesender.log('scheduleAutomaticResume(calling retry) ' + msg );
                resume( true, false );
            },
            filesender.config.automatic_resume_delay_to_resume * 1000
        );
    filesender.ui.automatic_resume_timer_seconds = filesender.config.automatic_resume_delay_to_resume+1;
    var countDownFunc = function() {
        if (filesender.ui.automatic_resume_timer_seconds > 0) {
            filesender.ui.automatic_resume_timer_seconds--;
        }
        filesender.ui.nodes.auto_resume_timer.text(
            lang.tr('auto_resume_timer_seconds')
                .r({seconds: filesender.ui.automatic_resume_timer_seconds}).out());
        
    }
    countDownFunc();
    filesender.ui.automatic_resume_timer_countdown = window.setInterval( countDownFunc, 1000 );
    filesender.ui.nodes.auto_resume_timer_top.show();
    
}

/**
 * Report and possibly resume the upload
 */
filesender.ui.retryingErrorHandler = function(error,callback) {

    var msg = lang.tr(error.message).out().trim();
    msg += '. ';
    msg += lang.tr('retry_attempt_x')
                .r({x: filesender.ui.automatic_resume_retries}).out();
    if(error.details) {
        msg += ' details: ';
        $.each(error.details, function(k, v) {
            if(isNaN(k)) v = lang.tr(k) + ': ' + v;
            msg += " " + v;
        });
    }

    if (filesender.ui.resumeScheduled === false) {
        filesender.ui.automatic_resume_retries++;
        if( filesender.ui.automatic_resume_retries > filesender.config.automatic_resume_number_of_retries ) {
            window.filesender.log("The user has run out of automatic retries so we are going to report this as a fatal error");
            pause( true );
            filesender.ui.errorOriginal( error, callback );
            return;
        }

        filesender.ui.scheduleAutomaticResume( msg );
    }
}

filesender.ui.cancelAutomaticResume = function() {
    
    window.clearInterval(filesender.ui.automatic_resume_timer_countdown);
    filesender.ui.automatic_resume_timer_countdown = 0;
    window.clearTimeout(filesender.ui.automatic_resume_timer);
    filesender.ui.automatic_resume_timer = 0;
    
    filesender.ui.nodes.auto_resume_timer_top.hide();
    
}



filesender.ui.startUpload = function() {

    this.transfer.encryption = filesender.ui.nodes.encryption.toggle.is(':checked'); 
    this.transfer.encryption_password = filesender.ui.nodes.encryption.password.val();
    this.transfer.disable_terasender = filesender.ui.nodes.disable_terasender.is(':checked');
    
    var can_use_terasender = filesender.config.terasender_enabled;
    if( this.transfer.disable_terasender ) {
        can_use_terasender = false;
    }
    var v2018_importKey_deriveKey = window.filesender.crypto_app().crypto_key_version_constants.v2018_importKey_deriveKey;
    if(this.transfer.encryption
       && filesender.config.encryption_key_version_new_files == v2018_importKey_deriveKey
       && use_webasm_pbkdf2_implementation()) {
        can_use_terasender = false;
        filesender.config.terasender_enabled = can_use_terasender;
    }
    window.filesender.pbkdf2dialog.setup(!can_use_terasender);
    window.filesender.pbkdf2dialog.reset();

    if(!filesender.ui.nodes.required_files) {
        this.transfer.expires = filesender.ui.nodes.expires.datepicker('getDate').getTime() / 1000;
        
        if(filesender.ui.nodes.from.length)
            this.transfer.from = filesender.ui.nodes.from.val();
        
        this.transfer.subject = filesender.ui.nodes.subject.val();
        this.transfer.message = filesender.ui.nodes.message.val();
        if (filesender.ui.nodes.guest_token.length){
            this.transfer.guest_token = filesender.ui.nodes.guest_token.val();
        }
        
        if(filesender.ui.nodes.lang.length)
            this.transfer.lang = filesender.ui.nodes.lang.val();
        
        for(var o in filesender.ui.nodes.options) {
            var i = filesender.ui.nodes.options[o];
            var v = i.is('[type="checkbox"]') ? i.is(':checked') : i.val();
            this.transfer.options[o] = v;
        }
    }
    var crypto = window.filesender.crypto_app();
    this.transfer.encryption_key_version = filesender.config.encryption_key_version_new_files;
    this.transfer.encryption_password_hash_iterations = filesender.config.encryption_password_hash_iterations_new_files;
    if( filesender.ui.transfer.encryption_password_version
        == crypto.crypto_password_version_constants.v2019_generated_password_that_is_full_256bit )
    {
        // as the password is 256 bits of entropy hashing is not needed
        this.transfer.encryption_password_hash_iterations = 1;
    }

    // tell the PBKDF2 dialog handler about his setting
    window.filesender.pbkdf2dialog.encryption_password_hash_iterations = this.transfer.encryption_password_hash_iterations;


    this.transfer.onprogress = filesender.ui.files.progress;

    // if the server wants the aup to be checked then we pass that information
    // back to the server. If the user has disabled this part of the form then
    // the server can throw an error.
    this.transfer.aup_checked = false;
    if(filesender.ui.nodes.aup.length)
        this.transfer.aup_checked = filesender.ui.nodes.aup.is(':checked');

    if( filesender.config.upload_display_per_file_stats ) {
        window.setInterval(function() {
            if( !window.filesender.pbkdf2dialog.already_complete ) {
                filesender.ui.uploading_again_started_at_time_touch();
                transfer.touchAllUploadStartedInWatchdog();
            }
            else
            {
                for (var i = 0; i < filesender.ui.transfer.files.length; i++) {
                    file = filesender.ui.transfer.files[i];
                    filesender.ui.files.update_crust_meter( file );
                }
            }
        }, 1000);
    }
    
    this.transfer.oncomplete = function(time) {

        filesender.ui.files.clear_crust_meter_all();
        window.filesender.pbkdf2dialog.ensure_onPBKDF2AllEnded();

        var usp = new URLSearchParams(window.location.search);
        var reditectargs = [];
        if( usp.has('vid')) {
            reditectargs['vid'] = usp.get('vid');
        }
        var redirect_url = filesender.ui.transfer.options.redirect_url_on_complete;
        
        if(redirect_url) {
            filesender.ui.redirect(redirect_url,reditectargs);
            
            window.setTimeout(function(f) {
                filesender.ui.redirect(redirect_url,reditectargs);
                filesender.ui.alert('success', lang.tr('done_uploading_redirect').replace({url: redirect_url}));
            }, 5000);
                    
            return;
        }
        
        var close = function() {
            window.filesender.notification.clear();
            if( filesender.ui.transfer.guest_token ) {
                filesender.ui.goToPage( 'home', null, null );
            } else {
                filesender.ui.goToPage( 'transfers', reditectargs,
                                        'transfer_' + filesender.ui.transfer.id );
            }
        };

        var p = filesender.ui.alert('success', lang.tr('done_uploading'), close);
        
        var t = null;
        if(filesender.ui.transfer.download_link) {
            var dl = $('<div class="download_link" />').text(lang.tr('download_link') + ' :').appendTo(p);
            t = $('<textarea class="wide" readonly="readonly" />').appendTo(dl);
            t.val(filesender.ui.transfer.download_link).focus().select();
        }
        
        if(filesender.ui.transfer.guest_token) {
            $('<p />').appendTo(p).html(lang.tr('done_uploading_guest').out());
        }
        
        if(t) t.on('click', function() {
            window.filesender.notification.clear();
            $(this).focus().select();
        });

        if( useWebNotifications()) {
            window.filesender.notification.notify(lang.tr('web_notification_upload_complete_title'),
                                                  lang.tr('web_notification_upload_complete'),
                                                  window.filesender.notification.image_success);
        }
        
    };
    
    var errorHandler = function(error) {
        filesender.ui.error(error,function(){
            filesender.ui.transfer.status = 'stopped';
            filesender.ui.reload();
        });
    };

    if( filesender.config.automatic_resume_number_of_retries ) {
        errorHandler = filesender.ui.retryingErrorHandler;
    }
    this.transfer.onerror = errorHandler;

    
    // Setup watchdog to look for stalled clients (only in html5 and terasender modes)
    if(filesender.supports.reader) {
        var transfer = this.transfer;
        transfer.resetWatchdog();
        window.setInterval(function() { // Check for stalled every minute

            // wait for pbkdf2 to be complete before we start tracking.
            if( !window.filesender.pbkdf2dialog.already_complete ) {
                transfer.resetWatchdog();
            }
            else
            {
            
                var stalled = transfer.getStalledProcesses();
                if(!stalled || filesender.ui.reporting_stall) return;
                
                if(transfer.retry()) {// Automatic retry
                    filesender.ui.log('upload seems stalled, automatic retry');
                    return;
                }
                
                filesender.ui.reporting_stall = true;
                filesender.ui.log('upload seems stalled and max number of automatic retries exceeded, asking user about what to do');
                
                var retry = function() {
                    transfer.resetWatchdog();
                    filesender.ui.reporting_stall = false;
                    transfer.retry(true); // Manual retry
                };
                
                var stop = function() {
                    transfer.stop(function() {
                        filesender.ui.goToPage('upload');
                        filesender.ui.reporting_stall = false;
                    });
                };
                
                var later = function() {
                    filesender.ui.goToPage('upload');
                    filesender.ui.reporting_stall = false;
                };
                
                var ignore = function() {
                    transfer.resetWatchdog(); // Forget watchdog data
                    filesender.ui.reporting_stall = false;
                };
                
                var buttons = {'retry': retry, 'stop': stop, 'ignore': ignore};
                if(transfer.isRestartSupported()) buttons['retry_later'] = later;
                
                var prompt = filesender.ui.popup(lang.tr('stalled_transfer'), buttons, {onclose: ignore});
                $('<p />').text(lang.tr('transfer_seems_to_be_stalled')).appendTo(prompt);
            }
        }, 60 * 1000);
    }
    
    var twc = $('#terasender_worker_count');
    if(twc.length) {
        twc = parseInt(twc.val());
        if(!isNaN(twc) && twc > 0 && twc <= 30) {
		if (this.transfer.encryption)
			twc = Math.max(Math.round(twc/2),3);
		filesender.config.terasender_worker_count = twc;
	}
    }
    
    filesender.ui.nodes.files.list.find('.file').addClass('uploading');
    filesender.ui.nodes.files.list.find('.file .remove').hide();
    filesender.ui.nodes.recipients.list.find('.recipient .remove').hide();
    filesender.ui.nodes.files.list.find('.file .progressbar').show();

    filesender.ui.nodes.files_actions.hide();
    filesender.ui.nodes.uploading_actions.show();
    filesender.ui.nodes.uploading_actions_msg.show();
    filesender.ui.nodes.auto_resume_timer.text(
        lang.tr('auto_resume_timer_seconds')
            .r({seconds: 0}).out());
    
    filesender.ui.nodes.upload_options_table.hide();

    filesender.ui.nodes.stats.number_of_files.hide();
    filesender.ui.nodes.stats.size.hide();
    filesender.ui.nodes.stats.uploaded.show();
    filesender.ui.nodes.stats.average_speed.show();
    filesender.ui.nodes.stats.estimated_completion.show();

    filesender.ui.nodes.form.find(':input:not(.file input[type="file"])').prop('disabled', true);

    // Report and possibly resume the upload
    if( filesender.config.automatic_resume_number_of_retries ) {
        filesender.ui.error = filesender.ui.retryingErrorHandler;
    }
    
    return this.transfer.start(errorHandler);
};

filesender.ui.uploadLogPrependLastMessageWasOK = true;
filesender.ui.uploadLogPrependRAW = function( msg, dt ) {
    var l = filesender.ui.nodes.files.uploadlog.find('.tpl').clone().removeClass('tpl').addClass('log');
    var d = new Date();
    l.find('.date').text( d.toLocaleString() );
    l.find('.message').text(msg);
    l.prependTo(filesender.ui.nodes.files.uploadlog.find('tbody'));
}
filesender.ui.uploadLogPrepend = function( msg, dt ) {
    filesender.ui.uploadLogPrependLastMessageWasOK = false;
    filesender.ui.uploadLogPrependRAW( msg, dt );
}
filesender.ui.uploadLogAddOKIfNotLast = function() {
    if( !filesender.ui.uploadLogPrependLastMessageWasOK ) {
        filesender.ui.uploadLogPrependLastMessageWasOK = true;
        filesender.ui.uploadLogPrependRAW( lang.tr('upload_progressing_again'), 0 );
    }
}
filesender.ui.uploadLogStarting = function() {
    filesender.ui.uploadLogPrependLastMessageWasOK = true;
    filesender.ui.uploadLogPrependRAW( lang.tr('upload_started'), 0 );
}


filesender.ui.switchToUloadingPageConfiguration = function() {

    filesender.ui.nodes.files_actions.hide();
    filesender.ui.nodes.uploading_actions.show();
    filesender.ui.nodes.files.dragdrop.hide();

    if( filesender.config.automatic_resume_number_of_retries ) {
        filesender.ui.nodes.files.uploadlogtop.show();
        filesender.ui.uploadLogStarting();
    }    
}

// v is ms since last update
filesender.ui.setMilliSecondsSinceDataWasLastSent = function(v,anyOffending) {

    var automatic_resume_timeout = filesender.config.upload_considered_too_slow_if_no_progress_for_seconds;
    
    // we probably shouldn't get here if IE11 anyway
    if( isIE11() ) {
        return;
    }
    // if the option is disabled then there is nothing to do
    if( 0==automatic_resume_timeout ) {
        return;
    }

    // if we are here then we show the info text again
    filesender.ui.nodes.seconds_since_data_sent_info.text(
        lang.tr('automatic_resume_will_happen_if_delay_reaches_x_seconds').r({seconds: automatic_resume_timeout}).out());
    
    if( anyOffending &&
        ( filesender.ui.resumeScheduled ||
          filesender.ui.uploading_again_started_at_time_diff_to_now() < automatic_resume_timeout ))
    {
        applyBadClass( filesender.ui.nodes.seconds_since_data_sent, false, false );
        filesender.ui.nodes.seconds_since_data_sent.text(lang.tr('waiting_for_upload_to_stabilize'));
    }
    else
    {
        var labelv = (v / 1000).toFixed(1);
        if( !anyOffending ) {
            // this needs more work to avoid false positives
            // filesender.ui.uploadLogAddOKIfNotLast();
        }
        
        filesender.ui.nodes.seconds_since_data_sent.text(
            lang.tr('seconds_since_data_was_last_sent').r({seconds: labelv}).out());
        
        applyBadClass( filesender.ui.nodes.seconds_since_data_sent, anyOffending, false );
        if( anyOffending ) {
            filesender.ui.scheduleAutomaticResume(lang.tr('too_long_since_any_data_was_last_sent'));
        }
    }
}
filesender.ui.setTimeSinceDataWasLastSentMessage = function(msg) {

    filesender.ui.nodes.seconds_since_data_sent.text(msg);
    
}

$(function() {
    var form = $('#upload_form');
    if(!form.length) return;

    filesender.ui.errorOriginal = filesender.ui.error;

    var crypto = window.filesender.crypto_app();

    
    // Transfer
    filesender.ui.transfer = new filesender.transfer();

    // start out asking user for a password
    filesender.ui.transfer.encryption_password_version = crypto.crypto_password_version_constants.v2018_text_password;
    
    // Register frequently used nodes
    filesender.ui.nodes = {
        form: form,
        files: {
            input: form.find(':file'),
            files_input: form.find('#files:file'),
            list: form.find('.files'),
            dragdrop: form.find('.files_dragdrop'),
            uploadlogtop: form.find('.files_uploadlogtop'),
            uploadlog: form.find('.uploadlog'),
            select: form.find('.files_actions .select_files'),
            selectdir: form.find('.files_actions .select_directory'),
            clear: form.find('.files_actions .clear_all'),
        },
        recipients: {
            input: form.find('input[name="to"]'),
            list: form.find('.recipients'),
        },
        from: form.find('select[name="from"]'),
        subject: form.find('input[name="subject"]'),
        encryption: {
            toggle: form.find('input[name="encryption"]'),
            password: form.find('input[name="encryption_password"]'),
            show_hide: form.find('#encryption_show_password'),
            generate:      form.find('#encryption_generate_password'),
            use_generated: form.find('#encryption_use_generated_password'),
            generate_again: form.find('#encryption_password_container_generate_again')
        },
        uploading_actions_msg:form.find('.uploading_actions .msg'),
        auto_resume_timer:form.find('.uploading_actions .auto_resume_timer'),
        auto_resume_timer_top:form.find('.uploading_actions .auto_resume_timer_top'),
        seconds_since_data_sent:form.find('#seconds_since_data_sent'),
        seconds_since_data_sent_info:form.find('#seconds_since_data_sent_info'),
        disable_terasender: form.find('input[name="disable_terasender"]'),
        message: form.find('textarea[name="message"]'),
        message_contains_password_warning: form.find('#password_can_not_be_part_of_message_warning'),
        message_contains_password_error:   form.find('#password_can_not_be_part_of_message_error'),
        guest_token: form.find('input[type="hidden"][name="guest_token"]'),
        lang: form.find('input[name="lang"]'),
        aup: form.find('input[name="aup"]'),
        expires: form.find('input[name="expires"]'),
        options: {
            hide_sender_email: form.find('input[name="hide_sender_email"]')
        },
        buttons: {
            start: form.find('.buttons .start'),
            restart: form.find('.buttons .restart'),
            pause: form.find('.buttons .pause'),
            resume: form.find('.buttons .resume'),
            stop: form.find('.buttons .stop'),
            reconnect_and_continue: form.find('.buttons .reconnect')
        },
        upload_options_table: form.find('#upload_options_table'),
        files_actions: form.find('.files_actions'),
        uploading_actions: form.find('.uploading_actions'),
        stats: {
            number_of_files: form.find('.files_actions .stats .number_of_files'),
            size: form.find('.files_actions .stats .size'),
            uploaded: form.find('.uploading_actions .stats .uploaded'),
            average_speed: form.find('.uploading_actions .stats .average_speed'),
            estimated_completion: form.find('.uploading_actions .stats .estimated_completion')
        },
        need_recipients: form.attr('data-need-recipients') == '1'
    };
    form.find('.basic_options [data-option] input, .advanced_options [data-option] input').each(function() {
        var i = $(this);
        filesender.ui.nodes.options[i.attr('name')] = i;
    });
    form.find('.basic_options [data-option] input, .hidden_options [data-option] input').each(function() {
        var i = $(this);
        filesender.ui.nodes.options[i.attr('name')] = i;
    });
    
    filesender.ui.nodes.stats.estimated_completion_updater = filesender.ui.elements.nonBusyUpdater(
        filesender.ui.nodes.stats.estimated_completion.find('.value'),
        2000,
        lang.tr('initializing'));

    
    // Bind file list clear button
    filesender.ui.nodes.files.clear.on('click', function() {
        if($(this).button('option', 'disabled')) return;
        
        filesender.ui.files.clear();
        return false;
    }).button({disabled: true});
    
    // Bind file list select button
    filesender.ui.nodes.files.select.on('click', function() {
        filesender.ui.nodes.files.files_input.click();
        return false;
    }).button();
    filesender.ui.nodes.files.selectdir.button();
    
    // Bind file drag drop events
    if(filesender.supports.reader) $('html').on('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
    }).on('dragenter', function (e) {
        e.preventDefault();
        e.stopPropagation();
    }).on('drop', function (e) {
        if(!e.originalEvent.dataTransfer) return;
        if(!e.originalEvent.dataTransfer.files.length) return;
        
        e.preventDefault();
        e.stopPropagation();

        addtree_success = false;
        
        if (filesender.dragdrop &&
            typeof filesender.dragdrop.addTree === "function") {
          addtree_success = filesender.dragdrop.addTree(e.originalEvent.dataTransfer);
        }

        if (!addtree_success) {
            filesender.ui.files.addList(e.originalEvent.dataTransfer.files);
        }
      });
    
    // Bind recipients events
    filesender.ui.nodes.recipients.input.on('keydown', function(e) {
        if(e.keyCode != 13) return;
        
        // enter is pressed
        e.preventDefault();
        e.stopPropagation();
        
        filesender.ui.recipients.addFromInput($(this));
    }).on('blur', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        filesender.ui.recipients.addFromInput($(this));
    });


    // Bind encryption password events
    filesender.ui.nodes.encryption.password.on(
        'keyup',
        delayAndCallOnlyOnce(function(e) {
            filesender.ui.files.checkEncryptionPassword($(this),true);
            filesender.ui.evalUploadEnabled();
        }, checkEncryptionPassword_delay )
    );
    filesender.ui.nodes.encryption.password.on('click', function() {
        var crypto = window.filesender.crypto_app();
        if( filesender.ui.transfer.encryption_password_version
            == crypto.crypto_password_version_constants.v2019_generated_password_that_is_full_256bit )
        {
            $(this).select();
        }
    });
    

    // Disable readonly (some browsers ignore the autocomplete...)
    filesender.ui.nodes.encryption.password.attr('readonly', false);
    
    // Bind file list select button
    filesender.ui.nodes.files.input.on('change', function() {
        // multiple files selected
        // loop through all files and show their values
        if (document.readyState != 'complete' && document.readyState != 'interactive') {
            return;
        }

        if(typeof this.files == 'undefined') return;
        
        filesender.ui.files.addList(this.files);
        
        // Forget (cloned) selection for webkit
        this.value = null;
    });
    
    if(!filesender.supports.reader) filesender.ui.nodes.files.input.removeAttr('multiple');
    
    filesender.ui.recipients.autocomplete();
    
    // Handle "back" browser action
    if(filesender.supports.reader) {
        var files = filesender.ui.nodes.files.input[0].files;
        if(files && files.length) filesender.ui.files.addList(files);
    }

    // validate message as it is typed
    window.filesender.ui.handleFlagInvalidOnRegexMatch(
        filesender.ui.nodes.message,
        $('#message_can_not_contain_urls'),
        filesender.config.message_can_not_contain_urls_regex );

    // Bind encryption password events
    var messageContainedPassword = false;
    if( filesender.config.upload_page_password_can_not_be_part_of_message_handling == 'warning'
        || filesender.config.upload_page_password_can_not_be_part_of_message_handling == 'error' )
    {
        var checkThatPasswordIsNotInMessage = function(e) {
            if( filesender.ui.doesUploadMessageContainPassword()) {
                if( filesender.config.upload_page_password_can_not_be_part_of_message_handling == 'warning' ) {
                    filesender.ui.nodes.message_contains_password_warning.show();
                }
                if( filesender.config.upload_page_password_can_not_be_part_of_message_handling == 'error' ) {
                    filesender.ui.nodes.message_contains_password_error.show();
                }
                filesender.ui.evalUploadEnabled();
                messageContainedPassword = true;
            } else if( messageContainedPassword ) {
                messageContainedPassword = false;
                filesender.ui.nodes.message_contains_password_warning.hide();
                filesender.ui.nodes.message_contains_password_error.hide();
                filesender.ui.evalUploadEnabled();
            }
        }

        filesender.ui.nodes.message.on(             'keyup', checkThatPasswordIsNotInMessage );
        filesender.ui.nodes.encryption.password.on( 'keyup', checkThatPasswordIsNotInMessage );        
    }
    
    
    // Setup date picker
    $.datepicker.setDefaults({
        closeText: lang.tr('dp_close_text').out(),
        prevText: lang.tr('dp_prev_text').out(),
        nextText: lang.tr('dp_next_text').out(),
        currentText: lang.tr('dp_current_text').out(),
        
        monthNames: lang.tr('dp_month_names').values(),
        monthNamesShort: lang.tr('dp_month_names_short').values(),
        dayNames: lang.tr('dp_day_names').values(),
        dayNamesShort: lang.tr('dp_day_names_short').values(),
        dayNamesMin: lang.tr('dp_day_names_min').values(),
        
        weekHeader: lang.tr('dp_week_header').out(),
        dateFormat: lang.trWithConfigOverride('dp_date_format').out(),
        
        firstDay: parseInt(lang.tr('dp_first_day').out()),
        isRTL: lang.tr('dp_is_rtl').out().match(/true/),
        showMonthAfterYear: lang.tr('dp_show_month_after_year').out().match(/true/),
        
        yearSuffix: lang.tr('dp_year_suffix').out()
    });
    // Bind picker
    filesender.ui.nodes.expires.datepicker({
        minDate: 1,
        maxDate: filesender.config.max_transfer_days_valid
    });
    // set value from epoch time
    filesender.ui.setDateFromEpochData( filesender.ui.nodes.expires );
    filesender.ui.nodes.expires.on('change', function() {
        filesender.ui.nodes.expires.datepicker('setDate', $(this).val());
    });

    // prevent the datepicker from having an empty string.
    filesender.ui.nodes.expires.preventEmpty = filesender.ui.elements.preventEmpty(
        filesender.ui.nodes.expires);
    

    
    // Bind advanced options display toggle
    form.find('.toggle_advanced_options').on('click', function() {
        $('.advanced_options').slideToggle();
        return false;
    });
    form.find('.toggle_hidden_options').on('click', function() {
        $('.hidden_options').slideToggle();
        return false;
    });
    
    form.find('input[name="get_a_link"]').on('change', function() {
        var choice = $(this).is(':checked');
        form.find(
            '.fieldcontainer[data-related-to="message"], .recipients,' +
            ' .fieldcontainer[data-option="add_me_to_recipients"],' +
            ' .fieldcontainer[data-option="email_me_copies"],' +
            ' .fieldcontainer[data-option="verify_email_to_download"],' +
            ' .fieldcontainer[data-option="enable_recipient_email_download_complete"]'
        ).toggle(!choice);
        form.find(
            ' .fieldcontainer[data-option="hide_sender_email"]'
        ).toggle(choice);
        filesender.ui.evalUploadEnabled();
    });
    
    form.find('input[name="add_me_to_recipients"]').on('change', function() {
        filesender.ui.evalUploadEnabled();
    });
    
    // Bind aup
    form.find('label[for="aup"]').addClass('clickable').on('click', function() {
        $(this).closest('.fieldcontainer').find('.terms').slideToggle();
        return false;
    });
    
    filesender.ui.nodes.aup.on('change', function() {
        filesender.ui.evalUploadEnabled();
    });


    if('web_notification_when_upload_is_complete' in filesender.ui.nodes.options) {
        filesender.ui.nodes.options.web_notification_when_upload_is_complete.on('change', function() {
            var v = filesender.ui.nodes.options.web_notification_when_upload_is_complete.is(':checked');
            if(v) {
                window.filesender.notification.ask();
            }
        });
        if(filesender.ui.nodes.options.web_notification_when_upload_is_complete.is(':checked')) {
            window.filesender.notification.ask();
        }
        form.find('.enable_web_notifications').on('click', function() {
            window.filesender.notification.ask( true );
        });
    }
    
    if(filesender.ui.nodes.encryption.toggle.is(':checked')) {
        $('#encryption_password_container').slideToggle();
        $('#encryption_password_container_generate').slideToggle();
        $('#encryption_password_show_container').slideToggle();
        $('#encryption_description_container').slideToggle();
        filesender.ui.transfer.encryption = filesender.ui.nodes.encryption.toggle.is(':checked');
        filesender.ui.files.checkEncryptionPassword(filesender.ui.nodes.encryption.password, true );
    }

    // Bind encryption
    filesender.ui.nodes.encryption.toggle.on('change', function() {
        $('#encryption_password_container').slideToggle();
        $('#encryption_password_container_generate').slideToggle();
        $('#encryption_password_show_container').slideToggle();
        $('#encryption_description_container').slideToggle();
        filesender.ui.transfer.encryption = filesender.ui.nodes.encryption.toggle.is(':checked');
        
        filesender.ui.files.checkEncryptionPassword(filesender.ui.nodes.encryption.password, true );
        
        for(var i=0; i<filesender.ui.transfer.files.length; i++) {
            var file = filesender.ui.transfer.files[i];
            
            var node = filesender.ui.nodes.files.list.find('.file[data-name="' + file.name + '"][data-size="' + file.size + '"]');            
            filesender.ui.transfer.checkFileAsStillValid(
                file,
                function(ok) {
                    node.removeClass('invalid');
                    node.find('.invalid').remove();
                    node.find('.invalid_reason').remove();
                },
                function(error) {
                    var tt = 1;
                    if(error.details && error.details.filename) filesender.ui.files.invalidFiles.push(error.details.filename);
                    node.addClass('invalid');
                    node.addClass(error.message);
                    $('<span class="invalid fa fa-exclamation-circle fa-lg" />').prependTo(node.find('.info'))
                    $('<div class="invalid_reason" />').text(lang.tr(error.message)).appendTo(node);
                });
        }
        filesender.ui.evalUploadEnabled();
        
        return false;
    });

    filesender.ui.nodes.encryption.use_generated.on('change', function() {
        var v = filesender.ui.nodes.encryption.use_generated.is(':checked');
        if( v ) {
            filesender.ui.nodes.encryption.generate_again.show();
            
            filesender.ui.nodes.encryption.password.attr('readonly', true);
            filesender.ui.nodes.encryption.generate.click();
        } else {
            filesender.ui.nodes.encryption.generate_again.hide();
            $('#encryption_password_show_container').show();
            filesender.ui.nodes.encryption.password.attr('readonly', false);
            
            // plain text passwords have a specific version
            // and encoding which may be used by key generation
            // so we must reset that here if the user starts modifying the password.
            filesender.ui.transfer.encryption_password_version = crypto.crypto_password_version_constants.v2018_text_password;
            filesender.ui.transfer.encryption_password_encoding = 'none';            
            filesender.ui.files.checkEncryptionPassword(filesender.ui.nodes.encryption.password, true );
        }
    });

    
    filesender.ui.nodes.encryption.generate.on('click', function() {
        var crypto = window.filesender.crypto_app();
        var encoded = crypto.generateRandomPassword();
        password = encoded.value;
        filesender.ui.nodes.encryption.password.val(password);
        filesender.ui.transfer.encryption_password_encoding = encoded.encoding;
        filesender.ui.transfer.encryption_password_version  = encoded.version;
        filesender.ui.nodes.encryption.show_hide.prop('checked',true);
        filesender.ui.nodes.encryption.show_hide.trigger('change');
        $('#encryption_password_show_container').hide();
        filesender.ui.files.checkEncryptionPassword(filesender.ui.nodes.encryption.password, true );
        filesender.ui.evalUploadEnabled();
    });

    if( filesender.config.encryption_mandatory_with_generated_password ) {
        setTimeout( function() {
            filesender.ui.nodes.encryption.use_generated.click();
            filesender.ui.nodes.encryption.password.attr('type','text');
            filesender.ui.nodes.encryption.use_generated.attr('disabled', 'disabled' )
        }, 0 );
    }
    

    
    filesender.ui.nodes.encryption.show_hide.on('change', function() {
        if (filesender.ui.nodes.encryption.show_hide.is(':checked')) {
            filesender.ui.nodes.encryption.password.attr('type','text');
        } else {
            filesender.ui.nodes.encryption.password.attr('type','password');
        }
        filesender.ui.nodes.encryption.password.stop().effect('highlight',{},500);
        return false;
    });

    
    
    // Bind buttons
    filesender.ui.nodes.buttons.start.on('click', function() {
        $(this).focus(); // Get out of email field / datepicker so inputs are updated
        $(this).blur();
        
        if(filesender.ui.transfer.status == 'new' && $(this).filter('[aria-disabled="false"]')) {

            filesender.ui.switchToUloadingPageConfiguration();
            filesender.ui.startUpload();
            filesender.ui.nodes.buttons.start.addClass('not_displayed');
            if(filesender.supports.reader) {
                filesender.ui.nodes.buttons.pause.removeClass('not_displayed');
                filesender.ui.nodes.buttons.reconnect_and_continue.removeClass('not_displayed');
            }
            filesender.ui.nodes.buttons.stop.removeClass('not_displayed');
        }
        return false;
    }).button({disabled: true});
    
    if(filesender.supports.reader) {
        filesender.ui.nodes.buttons.pause.on('click', function() {
            filesender.ui.cancelAutomaticResume();

            pause( true );
            filesender.ui.nodes.stats.average_speed.find('.value').text(lang.tr('paused'));
            filesender.ui.nodes.stats.estimated_completion.find('.value').text('');
            filesender.ui.setTimeSinceDataWasLastSentMessage(lang.tr('paused'));
            return false;
        }).button();
        
        filesender.ui.nodes.buttons.resume.on('click', function() {

            var force = filesender.ui.automatic_resume_retries > 0;
            resume( force, true );
            
            return false;
        }).button();
    }
    
    if(filesender.ui.transfer.isRestartSupported()) {
        filesender.ui.nodes.buttons.restart.on('click', function() {
            if(filesender.ui.startUpload()) {
                filesender.ui.nodes.buttons.restart.addClass('not_displayed');
                if(filesender.supports.reader) filesender.ui.nodes.buttons.pause.removeClass('not_displayed');
                filesender.ui.nodes.buttons.stop.removeClass('not_displayed');
            }
            return false;
        }).button({disabled: true});
    }


    
    filesender.ui.nodes.buttons.stop.on('click', function() {
        if(filesender.supports.reader) {
            pause( true );
        }
        filesender.ui.confirm(lang.tr('confirm_stop_upload'),
                              function() { // ok
                                  filesender.ui.transfer.stop(function() {
                                      filesender.ui.goToPage('upload');
                                  });
                              },
                              function() { // cancel
                                  filesender.ui.transfer.resume();
                                  filesender.ui.nodes.buttons.pause.removeClass('not_displayed');
                                  filesender.ui.nodes.buttons.resume.addClass('not_displayed');
                              });
        return false;
    }).button();
    

    filesender.ui.nodes.buttons.reconnect_and_continue.on('click', function() {
        filesender.ui.uploadLogPrepend(lang.tr('user_requested_reconnect_and_continue_upload'));
        resume( true, true );
        return false;
        
    }).button();
        
    
    // MUST BE AFTER BUTTONS SETUP otherwise event propagation ends up
    // trying to change button state but button is still not initialized ...
    if(form.find('input[name="get_a_link"]').is(':checked'))
        form.find('input[name="get_a_link"]').trigger('change');
    
    // special fix for esc key on firefox stopping xhr
    window.addEventListener('keydown', function(e) {
        (e.keyCode == 27 && e.preventDefault())
    });
    
    // Set message to display if the user changes pages / close tab / close browser
    window.onbeforeunload = function() {
        if(!filesender.ui.transfer.status.match(/^(new|done|stopped)$/))
            return lang.tr('confirm_leave_upload_page'); // Ask for leaving confirmation
    };

    filesender.ui.nodes.auto_resume_timer_top.hide();
    
    if(!filesender.supports.reader) {
        // Legacy uploader
        var selector = form.find('.file_selector').show();
        
        // Put notice
        form.addClass('legacy');
        
        $('<div class="info message" />').html(lang.tr('reader_not_supported').r({size: filesender.ui.formatBytes(filesender.config.max_legacy_file_size)}).out()).insertBefore(filesender.ui.nodes.files.list);
        
        // Remove unavailable features
        filesender.ui.nodes.files.select.remove();
        filesender.ui.nodes.files.selectdir.remove();
        filesender.ui.nodes.files.dragdrop.remove();
        filesender.ui.nodes.buttons.pause.remove();
        
        // Adapt file selection
        filesender.ui.nodes.files.input.remove();
        $('<input name="file" type="file" />').appendTo(selector).on('change', function() {
            var sel = $(this)
            var file = sel.clone();
            
            // TODO check file size, reject if over filesender.config.max_legacy_file_size
            
            var node = filesender.ui.files.addList(this.files, file.get(0));
            if(!node) return;
            
            file.appendTo(node);
            sel.val('');
        });
    }

    // This has to be set before setup() is called
    // as pbkdf2dialog will chain to the active function
    window.filesender.onPBKDF2AllEnded = function() {
        filesender.ui.uploadLogPrepend(lang.tr('upload_all_terasender_workers_completed_pbkdf2'));
    }
    
    
    // Check if there is a failed transfer in tracker and if it still exists
    var failed = filesender.ui.transfer.isThereFailedInRestartTracker();
    var auth = $('body').attr('data-auth-type');
    
    if(auth == 'guest') {
        var transfer_options = JSON.parse(form.find('input[id="guest_transfer_options"]').val());
        for(option in filesender.ui.nodes.options) {
            if(option == 'undefined' || option == 'expires') continue;
            var i = filesender.ui.nodes.options[option];
            if(i.is('[type="checkbox"]')) {
                i.prop('checked', transfer_options[option]);
            } else {
                i.val(transfer_options[option]);
            }
        }
        form.find('input[name="get_a_link"]').trigger('change');
        
    } else if(failed) {
        var id = failed.id;
        if(filesender.config.chunk_upload_security == 'key') {
            id += '?key=' + failed.files[0].uid;
            
        } else if(!auth || auth == 'guest') {
            id = null; // Cancel
        }

        
        if(id) filesender.client.getTransfer(id, function(xdata) {
            // Transfer still exists on server, lets ask the user what to do with it
            
            var load = function() {
                var required_files = filesender.ui.transfer.loadFailedFromRestartTracker(failed.id);
                
                // Prefill files list
                filesender.ui.nodes.required_files = $('<div class="required_files" />').insertBefore(filesender.ui.nodes.files.list);
                $('<div class="info message" />').text(lang.tr('need_to_readd_files')).appendTo(filesender.ui.nodes.required_files);
                
                for(var i=0; i<required_files.length; i++) {
                    var info = required_files[i].name + ' : ' + filesender.ui.formatBytes(required_files[i].size);
                    var node = $('<div class="file required" />').attr({
                        'data-name': required_files[i].name,
                        'data-size': required_files[i].size,
                        'data-cid': required_files[i].cid
                    }).appendTo(filesender.ui.nodes.required_files);
                    $('<span class="info" />').text(info).attr({title: info}).appendTo(node);
                    node.data('file', required_files[i]);
                }
                
                // Following field settings are just cosmetic
                filesender.ui.nodes.recipients.list.show();
                for(var i=0; i<failed.recipients.length; i++) {
                    var node = $('<div class="recipient" />').attr('email', failed.recipients[i]).appendTo(filesender.ui.nodes.recipients.list);
                    $('<span />').attr('title', failed.recipients[i]).text(failed.recipients[i]).appendTo(node);
                }
                
                filesender.ui.nodes.from.val(failed.from);
                
                filesender.ui.nodes.subject.val(failed.subject);
                filesender.ui.nodes.message.val(failed.message);
                
                filesender.ui.nodes.aup.prop('checked', true);
                
                filesender.ui.nodes.expires.datepicker('setDate', new Date(failed.expires * 1000));
                
                for(var o in failed.options) {
                    var i = filesender.ui.nodes.options[o];
                    if(i.is('[type="checkbox"]')) {
                        i.prop('checked', failed.options[o]);
                    } else {
                        i.val(failed.options[o]);
                    }
                }
                
                filesender.ui.nodes.form.find(':input').prop('disabled', true);
                $('#terasender_worker_count').prop('disabled', false);
                filesender.ui.nodes.files.input.prop('disabled', false);
                
                // Setup restart button
                filesender.ui.nodes.buttons.start.addClass('not_displayed');
                filesender.ui.nodes.buttons.restart.removeClass('not_displayed');
            };
            
            var forget = function() {
                filesender.ui.transfer.removeFromRestartTracker(failed.id);
            };
            
            var later = function() {};


            // maybe we want to force filesender to forget the transfer
            var force_forget = false;

            if( filesender.config.upload_force_transfer_resume_forget_if_encrypted
                && xdata.options.encryption )
            {
                force_forget = true;
            }
            
            if( force_forget ) {
                forget();
            } else {
            
            var prompt = filesender.ui.popup(lang.tr('restart_failed_transfer'), {'load': load, 'forget': forget, 'later': later}, {onclose: later});
            $('<p />').text(lang.tr('failed_transfer_found')).appendTo(prompt);
            var tctn = $('<div class="failed_transfer" />').appendTo(prompt);
            
            $('<div class="size" />').text(lang.tr('size') + ' : ' + filesender.ui.formatBytes(failed.size)).appendTo(tctn);
            
            var fctn = $('<ul />').appendTo($('<div class="files" />').appendTo(tctn));
            for(var i=0; i<failed.files.length; i++) {
                var finfo = failed.files[i].name + ' (' + filesender.ui.formatBytes(failed.files[i].size) + ')';
                finfo += ', ' + (100 * failed.files[i].uploaded / failed.files[i].size).toFixed(0) + '% ' + lang.tr('done');
                $('<li />').text(finfo).appendTo(fctn);
            }

            
            $('<div class="recipients" />').text(lang.tr('recipients') + ' : ' + failed.recipients.join(', ')).appendTo(tctn);
            
            if(failed.subject)
                $('<div class="subject" />').text(lang.tr('subject') + ' : ' + failed.subject).appendTo(tctn);
            
            if(failed.message)
                $('<div class="message" />').text(lang.tr('message') + ' : ' + failed.message).appendTo(tctn);
            }            
        }, function(error) {
            window.filesender.log('getTransfer() msg: ' + error.message);
            if(error.message == 'transfer_not_found') {
                // Transfer does not exist anymore on server side, remove from tracker
                filesender.ui.transfer.removeFromRestartTracker(failed.id);
                
            } else if(error.message == 'rest_authentication_required' && auth) {
                // Transfer ended up being in a weird state, remove from tracker
                filesender.ui.transfer.removeFromRestartTracker(failed.id);
                
            } else {
                filesender.ui.error(error);
            }
        }, {auth_prompt: false});
    }

    
});

$('.instructions').on('click', function(){
    filesender.ui.nodes.files.files_input.click();
    return false;
});
