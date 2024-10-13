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

if(!('filesender' in window)) window.filesender = {};
window.filesender.pageLoaded = false;


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

filesender.ui.stage = 1;
filesender.ui.reuploading = false;

jQuery.fn.extend({
    disable: function(state) {
        if( state )
            this.addClass('disabled');
        else
            this.removeClass('disabled');
    }
});
jQuery.fn.extend({
    enable: function(state) {
        if( !state )
            this.addClass('disabled');
        else
            this.removeClass('disabled');
    }
});

function setFileProgress( progress, v, complete ) {
    var origv = v;
    var upload_progress = Math.floor(1000 * v);

    v = Math.floor( 100*v );

    if (upload_progress < 1000 || complete === true) {
        progress[1].style.background = `conic-gradient(var(--fs-success) ${v * 3.6}deg, var(--fs-border-color) 0deg)`;

        if( v >= 100 ) {
            progress.closest('.file').addClass('done');
        }
    }

    const lv = Math.floor(1000 * origv);
    const progressValue = progress[1].querySelector(".fs-progress-circle__value");
    progressValue.textContent = Math.trunc(lv/10);
}

function useWebNotifications()
{
    var ret = ('web_notification_when_upload_is_complete' in filesender.ui.nodes.options) ? filesender.ui.nodes.options.web_notification_when_upload_is_complete.is(':checked') : false;
    return ret;
}


function getOption( n )
{
    var ret = (n in filesender.ui.nodes.options) ? filesender.ui.nodes.options[n].is(':checked') : false;
    return ret;
}


function getGuestOption( n )
{
    var auth = $('body').attr('data-auth-type');
    if(auth == 'guest') {
        return filesender.ui.guest_options[n];
    }
    return false;
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
            var t = (new Date()).getTime();
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
            if( filesender.config.ui_use_datepicker_for_transfer_expire_time_selection ) {
                filesender.ui.setDateFromEpochData( filesender.ui.nodes.expires );
            }
        }
    });
    return this;
}

// Manage files
filesender.ui.files = {
    invalidFiles: [],
    duplicateFiles: [],
    
    // Sort error cases to the top
    sortErrorLinesToTop: function() {
        var $selector = $("#fileslistdirectparent");
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

    /*
        addFileTryToReadAByte: async function( cid ) {

            var idx = filesender.ui.transfer.fileCIDToIndex( cid );
            if( idx != -1 ) {
                var file = filesender.ui.transfer.files[idx];

                // try to read a byte
                if( file.size >= 1 ) {
                    var slicer = filesender.ui.makeBlobSlicer( file );

                    var newblob = file.blob[slicer](0,1);
                    try
                    {
                        const text = await newblob.text();
                    }
                    catch (e) {
                        console.log('unreadable_file cid ' + cid );

                        var table = filesender.ui.nodes.files.filestable;
                        var tr = table.find('[data-cid=' + file.cid + ']');
                        var emsg = 'unreadable_file';
                        tr.addClass('invalid');
                        tr.addClass(emsg);
                        tr.find('.error').text(lang.tr(emsg));
                    }
                }
            }
        },
    */

    updateTotalFileSizeAndCountStats: function() {
        filesender.ui.updateSizeInfo();

    },

    addFile: function(filepath, fileblob, isSingleOperation, source_node) {
        filesender.ui.hideDragAndDropUpload();

        var filesize = fileblob.size;

        var table = filesender.ui.nodes.files.filestable;
        var tr = table.find('.tpl').clone().removeClass('tpl').addClass('file');
        tr.attr('data-name', filepath);
        tr.attr('data-size', filesize);
        tr.find('.filename').text(filepath);
        tr.find('.filesize').text(filesender.ui.formatBytes(filesize));
        tr.prependTo(table.find('tbody'));

        if(filesender.ui.nodes.required_files) {
            // Upload restart mode
            var table = filesender.ui.nodes.files.filestable;
            var req = table.find('.required_file[data-name="' + filepath + '"][data-size="' + filesize + '"]');

            if(!req.length) {
                filesender.ui.alert('error', lang.tr('unexpected_file'));
                tr.remove();
                return null;
            }

            var file = req.data('file');
            var added_cid = req.attr('data-cid');
            file.cid = added_cid;
            file.blob = fileblob;

            filesender.ui.transfer.files.push(file);
            filesender.ui.files.updateTotalFileSizeAndCountStats();

            req.remove();
            tr.find('.remove').hide();
            filesender.ui.nodes.files.clear.button('disable');

            var req = table.find('.required_file');
            if(!req.length) {
                $('#please_readd_files_message').hide();
                $('.files_dragdrop').hide();
                $('.files_actions').hide();
            }

        } else {

            // Normal upload mode
            tr.find('.removebutton').on('click', function(e) {
                filesender.ui.removeFile(this);
            });


            var added_cid = filesender.ui.transfer.addFile(filepath, fileblob, function(error) {
                var tt = 1;
                if(error.message && error.message == 'duplicate_file' ) {
                    filesender.ui.files.duplicateFiles.push(error.details.filename);
                    tr.attr('data-cid-dup', added_cid);
                    tr.addClass('duplicate_file_entry');
                }
                
                if(error.details && error.details.filename) {
                    filesender.ui.files.invalidFiles.push(error.details.filename);
                }
                else if(error.message &&
                    error.message == 'empty_file' || error.message == 'unreadable_file' )
                {
                    filesender.ui.files.invalidFiles.push(filepath);
                }

                tr.addClass('invalid');
                tr.addClass(error.message);
                tr.find('.error').text(lang.tr(error.message));
            }, source_node);

            filesender.ui.nodes.files.clear.button('enable');

            filesender.ui.evalUploadEnabled();
            this.updateStatsAndClearAll();
            if(added_cid === false) return tr;
        }

        filesender.ui.evalUploadEnabled();
        tr.attr('data-cid', added_cid);

        if( isSingleOperation ) {
            filesender.ui.evalUploadEnabled();
        }

        if( filesender.config.test_for_unreadable_files ) {
            // IE11 has issues
            /*
                        window.setTimeout(
                            function() {
                                filesender.ui.files.addFileTryToReadAByte(added_cid);
                            }, 10
                        );
            */
        }

        if(filesender.ui.nodes.required_files) {
            if(file) {
            }
        } else {
            filesender.ui.updateSizeInfo();
        }

        tr.attr('index', filesender.ui.transfer.files.length - 1);

        if( isSingleOperation ) {
            filesender.ui.nodes.files.list.scrollTop(filesender.ui.nodes.files.list.prop('scrollHeight'));
        }
        // this.updateStatsAndClearAll();

        return tr;
    },

    /**
     * Set the files list, stats, clear all, and clear buttons enabled
     * and visibility based on the current files selection from the
     * user.
     */
    updateStatsAndClearAll: function() {

        if(!filesender.ui.nodes.files.list.find('.file').length) {
            // filesender.ui.nodes.files.list.hide();
        } else {
            filesender.ui.nodes.files.list.show();
        }

        if( filesender.ui.transfer.files.length || filesender.ui.files.invalidFiles.length ) {
            filesender.ui.nodes.clearandstats.show();
        } else {
            filesender.ui.nodes.clearandstats.hide();
        }

        if(filesender.ui.nodes.required_files) {
            filesender.ui.nodes.files.clear.hide();
        } else {
            filesender.ui.nodes.files.clear.button('enable');
        }
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
        for (var i = 0; i < filesender.ui.transfer.getFileCount(); i++) {
            var file = filesender.ui.transfer.files[i];
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
//        window.filesender.log("AAAAAAAAAAA update_crust_meter(top) status " +  filesender.ui.transfer.status );
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

        var v = -1;
        var anyOffending = false;
        var maxV = 0;
        for( var i=0; i < imax; i++ ) {
            v = -1;
            if( i < durations.length ) {
                v = durations[i];
            }
            var b = false;
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

        const uploadPercentage = Math.round((uploaded * 100) / size);

        $('.fs-transfer__upload-detail .fs-progress-bar__value').html(`${uploadPercentage}%`)
        $('.fs-transfer__upload-detail .fs-progress-bar__indicator').css('width', `${uploadPercentage}%`);

        if(this.status != 'paused')
            filesender.ui.nodes.stats.average_speed.find('.value').text(filesender.ui.formatSpeed(speed));

        const progress = filesender.ui.nodes.files.list.find('[data-cid="' + file.cid + '"] .fs-progress-circle');
        setFileProgress( progress, (file.fine_progress ? file.fine_progress : file.uploaded) / file.size, complete );
    },

    // Clear the file box
    clear: function() {
        filesender.ui.transfer.files = [];
        filesender.ui.files.invalidFiles = [];

        filesender.ui.nodes.files.input.val('');

        filesender.ui.nodes.files.list.find('.file').remove();

        filesender.ui.nodes.files.clear.button('disable');

        // filesender.ui.nodes.stats.size.hide().find('.value').text('');

        filesender.ui.nodes.stats.filecount.text('');
        filesender.ui.nodes.stats.sendingsize.text('');
        filesender.ui.evalUploadEnabled();
        this.updateStatsAndClearAll();

        filesender.ui.goToStage(1);
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

    usingGeneratedPassword: function() {
        var crypto = window.filesender.crypto_app();
        return filesender.ui.transfer.encryption_password_version == crypto.crypto_password_version_constants.v2019_generated_password_that_is_full_256bit;
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

        if( filesender.ui.files.usingGeneratedPassword()) {
            $('.passwordvalidation').each(function( index ) {
                $(this).hide();
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
        $('<span class="remove fa fa-close" />').attr({
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

filesender.ui.isUserGettingALink = function() {
    var gal = false;
    if($('.get_a_link_top_selector').length) {
        gal = $('.get_a_link_top_selector').is(':checked');
    }
    return gal;
}
filesender.ui.isUserAddMeToRecipients = function() {
    var addme = ('add_me_to_recipients' in filesender.ui.nodes.options) ? filesender.ui.nodes.options.add_me_to_recipients.is(':checked') : false;
    return addme;
}

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
    var uploadFileStageOk = true;
    var configStageOk = true;
    var gal   = filesender.ui.isUserGettingALink();
    var addme = filesender.ui.isUserAddMeToRecipients();

    // Check if there is no files with banned extension
    if (filesender.ui.files.invalidFiles.length > 0) {
        ok  = false;
        uploadFileStageOk = false;
    }
    if(!filesender.ui.transfer.getFileCount()) {
        ok = false;
        uploadFileStageOk = false;
    }

    if(
        filesender.ui.nodes.need_recipients &&
        !gal && !addme &&
        filesender.ui.nodes.recipients.list.length &&
        !filesender.ui.transfer.recipients.length
    ) {
        ok = false;
        configStageOk = false;
    }

    if(filesender.ui.nodes.aup.length) {
        if(!filesender.ui.nodes.aup.is(':checked')) {
            ok = false;
            configStageOk = false;
        }
    }

    if( filesender.ui.doesUploadMessageContainPassword()) {
        if( filesender.config.upload_page_password_can_not_be_part_of_message_handling == 'error' ) {
            ok = false;
            configStageOk = false;
        }
    }


    if(filesender.ui.nodes.encryption.toggle.is(':checked')) {
        var passok = filesender.ui.files.checkEncryptionPassword(filesender.ui.nodes.encryption.password,false );
        if( !passok ) {
            ok = false;
            configStageOk = false;
        }
        if( document.activeElement !== document.getElementById("message")) {
            filesender.ui.nodes.encryption.password.focus();
        }
    }

    if( filesender.ui.doesUploadMessageContainPassword()) {
        if( filesender.config.upload_page_password_can_not_be_part_of_message_handling == 'error' ) {
            ok = false;
        }
    }

    var invalid_nodes = filesender.ui.nodes.files.list.find('.invalid');
    if( invalid_nodes.length ) {
        ok = false;
        uploadFileStageOk = false;
    }

    if(filesender.ui.nodes.required_files) {
        if(ok) filesender.ui.nodes.required_files.hide();
        filesender.ui.nodes.buttons.restart.button(ok ? 'enable' : 'disable');
    } else {
        filesender.ui.nodes.buttons.start.button(ok ? 'enable' : 'disable');
    }


    // if (filesender.ui.stage == 2) {
    //     filesender.ui.nodes.stages.nextStep.prop('disabled', !uploadFileStageOk);
    // }

    if (filesender.ui.stage == 1) {
        filesender.ui.nodes.stages.confirm.prop('disabled', !configStageOk);
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

    window.filesender.ui.uploading = true;
    this.transfer.encryption = filesender.ui.nodes.encryption.toggle.is(':checked'); 
    this.transfer.encryption_password = filesender.ui.nodes.encryption.password.val();
    this.transfer.disable_terasender = filesender.ui.nodes.disable_terasender.is(':checked');


    var can_use_terasender = filesender.config.terasender_enabled;
    if( this.transfer.disable_terasender ) {
        can_use_terasender = false;
    }
    var v2018_importKey_deriveKey = window.filesender.crypto_app().crypto_key_version_constants.v2018_importKey_deriveKey;
    window.filesender.pbkdf2dialog.setup(!can_use_terasender);
    window.filesender.pbkdf2dialog.reset();

    if(!filesender.ui.nodes.required_files) {

        if( filesender.config.ui_use_datepicker_for_transfer_expire_time_selection ) {
            this.transfer.expires = filesender.ui.nodes.expires.datepicker('getDate').getTime() / 1000;
        } else {
            const expiresDays = $('#expires-select').find(":selected").val();
            const now = new Date();
            now.setHours(0, 0, 0, 0);
            const expiresDate = now.setDate(now.getDate() + parseInt(expiresDays, 10));
            this.transfer.expires = expiresDate / 1000;
        }

        if(filesender.ui.nodes.from.length)
            this.transfer.from = filesender.ui.nodes.from.val();

        this.transfer.subject = filesender.ui.nodes.subject.val();
        this.transfer.message = filesender.ui.nodes.message.val();
        if (filesender.ui.nodes.guest_token.length){
            this.transfer.guest_token = filesender.ui.nodes.guest_token.val();
        }

        if( filesender.ui.nodes.lang && filesender.ui.nodes.lang.attr('data-id')) {
            this.transfer.lang = filesender.ui.nodes.lang.attr('data-id');
        }

        if(filesender.ui.nodes.lang.length)
            this.transfer.lang = filesender.ui.nodes.lang.val();
        
        for(var o in filesender.ui.nodes.options) {
            var i = filesender.ui.nodes.options[o];
            var v = i.is('[type="checkbox"]') ? i.is(':checked') : i.val();
            this.transfer.options[o] = v;
        }
    }
    this.transfer.options['get_a_link'] = filesender.ui.isUserGettingALink();

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
            if( window.filesender.transfer.encryption && !window.filesender.pbkdf2dialog.already_complete ) {
                filesender.ui.uploading_again_started_at_time_touch();
                this.transfer.touchAllUploadStartedInWatchdog();
            }
            else
            {
                for (var i = 0; i < filesender.ui.transfer.getFileCount(); i++) {
                    var file = filesender.ui.transfer.files[i];
                    filesender.ui.files.update_crust_meter( file );
                }
            }

        }, 1000);
    }

    this.transfer.oncomplete = function(time) {

        filesender.ui.uploadLogPrependRAW( lang.tr('upload_completed'), 0 );
        window.filesender.ui.uploading = false;       
 
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

        if(filesender.ui.transfer.options.popup_on_complete){
           filesender.ui.confirmTitle(lang.tr('uploaded'),' <i class="fa fa-check" aria-hidden="true"></i> '+lang.tr('upload_completed'))
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

        // show the completed stage.
        filesender.ui.stage = 4;

        $('.fs-transfer__upload-link .fs-copy > span').html(filesender.ui.transfer.download_link);
        if( filesender.config.make_download_links_clickable ) {
            $('.fs-transfer__upload-link .fs-copy > span').on('click', function(e){
                filesender.ui.redirect(filesender.ui.transfer.download_link);
            });
        }
        
        $('#copy-to-clipboard').on('click', function(e){
            filesender.ui.copyToClipboard(filesender.ui.transfer.download_link);
        });

        var link = filesender.ui.createPageLink(
            filesender.ui.transfer.guest_token ? 'home' : 'transfers',
            null,
            filesender.ui.transfer.guest_token ? null : 'transfer_' + filesender.ui.transfer.id
        );
        filesender.ui.nodes.form.find('.mytransferslink').attr('href',link);

        filesender.ui.goToStage(3);
        filesender.ui.setFileList(2, 3);

        filesender.ui.updateSizeInfo();

        if (filesender.ui.transfer.recipients && filesender.ui.transfer.recipients.length > 0) {
            $('.fs-transfer__upload-recipients').addClass('fs-transfer__upload-recipients--show');

            const badgeList = $('.fs-transfer__upload-recipients .fs-badge-list');

            filesender.ui.transfer.recipients.forEach(recipient => {
                badgeList.append(`<div class="fs-badge">${recipient}</div>`);
            });
        } else {
            if(filesender.ui.transfer.download_link) {
                $('.fs-transfer__upload-link').addClass('fs-transfer__upload-link--show');
            }
        }

        const expireDays = new Date(filesender.ui.transfer.expires * 1000);
        const now = new Date();
        now.setHours(0, 0, 0, 0);
        const dateDiff = expireDays.getTime() - now.getTime();
        const daysToExpire = Math.ceil(dateDiff / (1000 * 3600 * 24));

        $('#expires-days').text(daysToExpire);

        $('#detail-link').attr('href', `?s=transfer_detail&transfer_id=${filesender.ui.transfer.id}`);

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
            if( window.filesender.transfer.encryption && !window.filesender.pbkdf2dialog.already_complete ) {
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

                var buttons = {
                    retry: {callback: retry},
                    stop:  {callback:  stop},
                    ignore: {callback: ignore}
                };
                if(transfer.isRestartSupported()) buttons['retry_later'] = {callback: later};

                var prompt = filesender.ui.popup(lang.tr('stalled_transfer'),
                    buttons,
                    {onclose: ignore});
                $('<p />').text(lang.tr('transfer_seems_to_be_stalled')).appendTo(prompt);
            }
        }, 80 * 1000);
    }

    var twc = $('#terasender_worker_count');
    if(twc.length) {
        twc = parseInt(twc.val());
        if(!isNaN(twc)) {
            if( twc > filesender.config.terasender_worker_max_count ) {
                // clamp to max value rather than ignore change
                twc = filesender.config.terasender_worker_max_count;
            }
            if( twc > 0 && twc <= filesender.config.terasender_worker_max_count) {
                filesender.config.terasender_worker_count = twc;
            }
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

    // filesender.ui.nodes.stats.size.hide();
    filesender.ui.nodes.stats.uploaded.show();
    filesender.ui.nodes.stats.average_speed.show();
    filesender.ui.nodes.stats.estimated_completion.show();

    // filesender.ui.nodes.form.find(':input:not(.file input[type="file"])').prop('disabled', true);

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


filesender.ui.handle_get_a_link_change = function() {
    var form = $('#upload_form');
    var gal = form.find('input[id="get_a_link"]');
    var choice = gal.is(':checked');
    form.find(
        '.fieldcontainer[data-related-to="message"], .recipients,' +
        ' .fieldcontainer[data-option="add_me_to_recipients"],' +
        ' .fieldcontainer[data-option="email_me_copies"],' +
        ' .fieldcontainer[data-related-to="emailfrom"],' +
        ' .custom-checkbox[data-option="add_me_to_recipients"],' +
        ' .fieldcontainer[data-option="enable_recipient_email_download_complete"],' +
        ' .fieldcontainer[data-related-to="verify_email_to_download"]'
    ).toggle(!choice);

    form.find(
        ' .fieldcontainer[data-option="hide_sender_email"]'
    ).toggle(!choice);

    filesender.ui.evalUploadEnabled();
}

// Add class to highlight droparea container
filesender.ui.activeDroparea = function () {
    $('.fs-transfer__droparea').addClass('fs-transfer__droparea--active');
}

// Remove the droparea container highlight class
filesender.ui.inactiveDroparea = function () {
    $('.fs-transfer__droparea').removeClass('fs-transfer__droparea--active');
}

filesender.ui.resetStage = function() {
    filesender.ui.clearActiveStages();
    filesender.ui.goToStage(1);
}

filesender.ui.clearActiveStages = function() {
    $(`.${filesender.ui.stageActiveClass}`).removeClass(filesender.ui.stageActiveClass);
}

filesender.ui.goToStage = function (stage) {
    filesender.ui.clearActiveStages();
    const stageElement = $(`[data-step="${stage}"`);
    stageElement.addClass(filesender.ui.stageActiveClass);
    filesender.ui.stage = stage;
    window.scrollTo({
        top: 0,
        left: 0,
        behavior: "auto",
    });
}

filesender.ui.removeFile = function (e) {
    var el   = $(e).parents('.file');
    var cid  = el.attr('data-cid');
    var name = el.attr('data-name');
    var iidx = -1;

    iidx = filesender.ui.files.duplicateFiles.indexOf(name);
    if (iidx != -1) {
        var dups = filesender.ui.nodes.files.list.find(".file[data-name='" + name + "']");
        dups.each( function() {
            var e = $(this);
            if( e.hasClass('duplicate_file_entry')) {
                e.remove();
                
            }
        });
        while( iidx != -1 ) {
            filesender.ui.files.duplicateFiles.splice(iidx, 1);
            iidx = filesender.ui.files.duplicateFiles.indexOf(name);
        }
        iidx = filesender.ui.files.invalidFiles.indexOf(name);
        while( iidx != -1 ) {
            filesender.ui.files.invalidFiles.splice(iidx, 1);
            iidx = filesender.ui.files.invalidFiles.indexOf(name);
        }
        filesender.ui.notify('success', lang.tr('files_removed_from_upload'));
        
    } else {
    
        if(cid) filesender.ui.transfer.removeFile(cid);
        el.remove();

    }

    if(!filesender.ui.nodes.files.list.find('.file').length) {
        // The last file was removed,
        // this may hide some UI elements like clear all
        filesender.ui.files.clear();
        filesender.ui.showDragAndDropUpload();
    }

    iidx = filesender.ui.files.invalidFiles.indexOf(name);
    if (iidx === -1) {
        // not an invalid file name
        filesender.ui.files.updateTotalFileSizeAndCountStats();
    } else {
        filesender.ui.files.invalidFiles.splice(iidx, 1);
    }

    filesender.ui.evalUploadEnabled();

    if(!filesender.ui.nodes.files.list.find('.file').length) {
        // The last file was removed,
        // this may hide some UI elements like clear all
        filesender.ui.files.clear();
    }

    if (filesender.ui.stage === 2) {
        filesender.ui.setFileList(2, 3);
    }

    if (filesender.ui.stage === 3) {
        filesender.ui.setFileList(3, 2);
    }
}

filesender.ui.setFileList = function (stageToClone, stageToApply) {
    const fileListToClone = $(`[data-step="${stageToClone}"]`).find('.fs-transfer__files table');
    const fileListToApply = $(`[data-step="${stageToApply}"]`).find('.fs-transfer__files');

    fileListToApply.html('');

    fileListToApply.html(fileListToClone.clone());

    fileListToApply.find('table tr .removebutton').on('click', function(e) {
        filesender.ui.removeFile(this);
    });

    if ($(`[data-step="${stageToClone}"]`).find('.fs-transfer__files table tbody').children().length === 1) {
        $(`[data-step="3"]`).find('.fs-transfer__files table').html('');
        filesender.ui.files.clear();
        filesender.ui.goToStage(1);
        filesender.ui.nodes.files.filestable = $(`[data-step="2"]`).find('.fs-transfer__files table');
    }
}

filesender.ui.deleteRemoveButton = function () {
    $(`[data-step="4"]`).find('.fs-transfer__files table tr .removebutton').each(function(){
        $(this).remove();
    });
}

filesender.ui.onChangeTransferType = function (transferType) {
    const TRANSFER_TYPES = {
        TRANSFER_LINK: 'transfer-link',
        TRANSFER_EMAIL: 'transfer-email'
    }
    if (transferType) {
        const emailField = $(`[data-transfer-type='${TRANSFER_TYPES.TRANSFER_EMAIL}']`);
        const addMeToRecipientsField = $(`#fs-transfer__add-me-to-recipients`);

        switch (transferType) {
            case TRANSFER_TYPES.TRANSFER_LINK:
                emailField.addClass('fs-input-group--hide');
                addMeToRecipientsField.addClass('fs-switch--hide');
                filesender.ui.getALink = true;
                filesender.ui.nodes.gal.checkbox.prop('checked', true);

                $('.recipients').html('');
                $('[data-option="add_me_to_recipients"], [data-option="email_me_copies"], [data-option="enable_recipient_email_download_complete"]').prop( "checked", false );
                $('[data-option="add_me_to_recipients"]').addClass('fs-switch--hide');
                filesender.ui.recipients.clear();

                break;
            case TRANSFER_TYPES.TRANSFER_EMAIL:
                emailField.removeClass('fs-input-group--hide');
                addMeToRecipientsField.removeClass('fs-switch--hide');
                $('[data-option="add_me_to_recipients"]').removeClass('fs-switch--hide');
                filesender.ui.getALink = false;
                filesender.ui.nodes.gal.checkbox.prop('checked', false);
                break;
            default:
                break;
        }

        if( filesender.ui.doesUploadMessageContainPassword()) {
            filesender.ui.nodes.message.focus();
        }
    }
};

filesender.ui.updateSizeInfo = function () {
    var size      = filesender.ui.transfer.getTotalSize();
    var filecount = filesender.ui.transfer.getFileCount();
    var sizetxt   = filesender.ui.formatBytes(size);

    var params = { filecount: filecount,
                   max_transfer_files: filesender.config.max_transfer_files,
                   max_transfer_size: filesender.config.max_transfer_size,
                   sizetxt: sizetxt,
                   size_human_readable: sizetxt,
                   size: size
                 };

    filesender.ui.nodes.stats.number_of_files.find('.value').text(lang.tr('files_transferred_display').r( params ));
    filesender.ui.nodes.stats.size.find('.value').text(lang.tr('size_transferred_display').r( params ));
    filesender.ui.nodes.stats.filecount.text(filecount);
    filesender.ui.nodes.stats.sendingsize.text(sizetxt);

    filesender.ui.nodes.text_desc_of_file_count_and_size.find('.value').text(lang.tr('text_desc_of_file_count_and_size').r({filecount: filecount, totalsize: sizetxt }).out());

};

filesender.ui.copyToClipboard = function(value) {
    navigator.clipboard.writeText(value).then((x) => {
        filesender.ui.notify('info', 'Copied to clipboard!');
    }).catch((e) => {
        console.error(e);
        filesender.ui.notify('error', 'Error copying to clipboard!');
    });
};

filesender.ui.hideDragAndDropUpload = function () {
    filesender.ui.nodes.files.dragdrop.hide();
    filesender.ui.nodes.files.list.show();
};

filesender.ui.showDragAndDropUpload = function () {
    filesender.ui.nodes.files.dragdrop.show();
    filesender.ui.nodes.files.list.hide();
};

$(function() {
    var form = $('#upload_form');
    if(!form.length) return;

    filesender.ui.errorOriginal = filesender.ui.error;

    var crypto = window.filesender.crypto_app();

    // Transfer
    filesender.ui.transfer = new filesender.transfer();

    // start out asking user for a password
    filesender.ui.transfer.encryption_password_version = crypto.crypto_password_version_constants.v2018_text_password;

    // Stage active class
    filesender.ui.stageActiveClass = 'fs-transfer__step--active';

    // initial value
    filesender.ui.guest_options = [];
    
    // Register frequently used nodes
    filesender.ui.nodes = {
        form: form,
        files: {
            input: form.find(':file'),
            files_input: form.find('#files:file'),
            list: form.find('.fs-transfer__list'),
            dragdrop: form.find('.fs-transfer__droparea'),
            uploadlogtop: form.find('.files_uploadlogtop'),
            uploadlog: form.find('.uploadlog'),
            select: form.find('.files_actions .select_files'),
            selectdir: form.find('.select_directory'),
            clear: form.find('.fs-transfer__clear-all'),
            filestable: form.find('.fs-transfer__files table'),
        },
        clearandstats: form.find('.clearandstats'),
        stage1: form.find('[data-step="1"'),
        stage2: form.find('[data-step="2"'),
        stage3: form.find('[data-step="3"'),
        stages: {
            // nextStep: form.find('#fs-transfer__next-step'),
            // previousStep: form.find('#fs-transfer__previous-step'),
            confirm: form.find('#fs-transfer__confirm'),
            cancel: form.find('#fs-transfer__cancel'),
        },
        gal: {
            gal: form.find('#galgal'),
            email: form.find('#galemail'),
            checkbox: form.find('input[id="get_a_link"]'),
            checkboxcontainer: form.find('.custom-control[data-option="get_a_link"]'),
        },
        get_a_link_or_email_choice: form.find('#get_a_link_or_email_choice'),
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
            generate: form.find('#encryption_generate_password'),
            use_generated: form.find('#encryption_use_generated_password'),
            generate_again: form.find('#encryption_password_container_generate_again')
        },
        uploading_actions_msg:form.find('.fs-transfer__upload-speed-info .msg'),
        auto_resume_timer:form.find('.fs-transfer__upload-speed-info .auto_resume_timer'),
        auto_resume_timer_top:form.find('.fs-transfer__upload-speed-info .auto_resume_timer_top'),
        seconds_since_data_sent:form.find('#seconds_since_data_sent'),
        seconds_since_data_sent_info:form.find('#seconds_since_data_sent_info'),
        disable_terasender: form.find('input[name="disable_terasender"]'),
        message: form.find('textarea[name="message"]'),
        message_contains_password_warning: form.find('#password_can_not_be_part_of_message_warning'),
        message_contains_password_error:   form.find('#password_can_not_be_part_of_message_error'),
        guest_token: form.find('input[type="hidden"][name="guest_token"]'),
        lang: form.find('#lang'),
        aup: form.find('input[name="aup"]'),
        aupshowhide: form.find('#aupshowhide'),
        expires: form.find('#expires'),
        options: {
            get_a_link: form.find('input[id="get_a_link"]'),
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
        uploading_actions: form.find('.fs-transfer__upload-speed-info'),
        text_desc_of_file_count_and_size: form.find('.text_desc_of_file_count_and_size'),
        stats: {
            number_of_files: form.find('.number_of_files'),
            size:            form.find('.size'),
            filecount:       form.find('.filecount'),
            sendingsize:     form.find('.sendingsize'),
            uploaded:        form.find('.fs-transfer__upload-size-info .stats .uploaded'),
            average_speed:   form.find('.fs-transfer__upload-speed-info .stats .average_speed'),
            estimated_completion: form.find('.fs-transfer__upload-speed-info .stats .estimated_completion')
        },
        need_recipients: form.attr('data-need-recipients') == '1'
    };

    filesender.ui.getALink = true;
    filesender.ui.nodes.gal.checkbox.prop('checked', true);

    $('.fs-transfer__transfer-fields').addClass('fs-transfer__transfer-fields--show');
    $('.fs-transfer__transfer-settings').addClass('fs-transfer__transfer-settings--show');

    form.find('.basic_options [data-option] input, .advanced_options [data-option] input').each(function() {
        var i = $(this);
        filesender.ui.nodes.options[i.attr('name')] = i;
    });
    form.find('.basic_options [data-option] input, .hidden_options [data-option] input').each(function() {
        var i = $(this);
        filesender.ui.nodes.options[i.attr('name')] = i;
    });
    form.find('.lifted_options [data-option] input').each(function() {
        var i = $(this);
        filesender.ui.nodes.options[i.attr('name')] = i;
    });

    form.find('.uploadoption').each(function() {
        var i = $(this);
        filesender.ui.nodes.options[i.attr('name')] = i;
    });

    filesender.ui.nodes.stats.estimated_completion_updater = filesender.ui.elements.nonBusyUpdater(
        filesender.ui.nodes.stats.estimated_completion.find('.value'),
        2000,
        lang.tr('initializing'));


    // Bind file list clear button
    filesender.ui.nodes.files.clear.on('click', function() {
        filesender.ui.files.clear();
        filesender.ui.goToStage(1);
        return false;
    });

    // Bind file list cancel button
    filesender.ui.nodes.stages.cancel.on('click', function() {
        filesender.ui.files.clear();
        filesender.ui.goToStage(1);
        return false;
    });

    filesender.ui.nodes.gal.checkboxcontainer.hide(); //check no remove
    form.find('.terms').hide();

    // filesender.ui.nodes.stages.nextStep.prop('disabled', true);
    filesender.ui.nodes.stages.confirm.prop('disabled', true);

    // handle browser back and forward buttons as best as we can
    window.onpopstate = function(event) {
        if( filesender.ui.lasthash == "" || filesender.ui.lasthash == "#stage1" ) {
            if( document.location.hash == "#stage2" ) {
                // filesender.ui.nodes.stages.nextStep.click();
            }
        }
        if( filesender.ui.lasthash == "#stage2" ) {
            if( !document.location.hash.length || document.location.hash == "#stage1" ) {
                // filesender.ui.nodes.stages.previousStep.click();
            }
        }
        if( filesender.ui.lasthash == "#uploading" && window.location.hash == "#uploading" ) {
            // ignore this case which is generated from the below reset.
        } else {
            if( !filesender.ui.reuploading ) {
                if( filesender.ui.lasthash == "#uploading" ) {
                    filesender.ui.nodes.buttons.stop.click();
                    window.location.hash = "#uploading";
                }
            }
        }
        filesender.ui.lasthash = document.location.hash;
    }

    // move to stage2
    filesender.ui.nodes.stages.confirm.on('click',function() {
        filesender.ui.goToStage(2);

        filesender.ui.setFileList(1, 2);
        filesender.ui.deleteRemoveButton();

        // best to use a selector because there are dynamic items in list
        form.find('.progressbar').show();

        filesender.ui.switchToUloadingPageConfiguration();
        filesender.ui.startUpload();
        filesender.ui.nodes.buttons.start.addClass('not_displayed');
        if(filesender.supports.reader) {
            filesender.ui.nodes.buttons.pause.removeClass('not_displayed');
            filesender.ui.nodes.buttons.reconnect_and_continue.removeClass('not_displayed');
        }
        filesender.ui.nodes.buttons.stop.removeClass('not_displayed');

        window.location.hash = "#uploading";

        return false;
    });

    $('#get_a_link').on('change',function() {
        if ($(this).is(":checked")) {
            // filesender.ui.nodes.gal.checkbox.prop('checked', true);
            filesender.ui.handle_get_a_link_change();
            form.find('.galmodelink').show();
            form.find('.galmodeemail').hide();
        }
        return false;
    });

    $('#transfer-email').on('change',function() {
        if ($(this).is(":checked")) {
            filesender.ui.nodes.gal.checkbox.prop('checked', false);
            filesender.ui.handle_get_a_link_change();
            form.find('.galmodelink').hide();
            form.find('.galmodeemail').show();
        }
        return false;
    });


    // Bind file list select button
    filesender.ui.nodes.files.select.on('click', function() {
        filesender.ui.nodes.files.files_input.click();
        return false;
    }).button();
    filesender.ui.nodes.files.selectdir.button();

    // Bind file drag drop events
    if(filesender.supports.reader) {
        $('html').on('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            filesender.ui.activeDroparea();
        }).on('dragenter', function (e) {
            e.preventDefault();
            e.stopPropagation();
            filesender.ui.activeDroparea();
        }).on('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            filesender.ui.inactiveDroparea();
        }).on('drop', function (e) {
            if(!e.originalEvent.dataTransfer) return;
            if(!e.originalEvent.dataTransfer.files.length) return;

            e.preventDefault();
            e.stopPropagation();

            filesender.ui.inactiveDroparea();

            var addtree_success = false;

            if (filesender.dragdrop &&
                typeof filesender.dragdrop.addTree === "function") {
                addtree_success = filesender.dragdrop.addTree(e.originalEvent.dataTransfer);
            }

            if (!addtree_success) {
                filesender.ui.files.addList(e.originalEvent.dataTransfer.files);
            }
        });
    }

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
            // as soon as they edit anything it can no longer be considered "generated".
            filesender.ui.transfer.encryption_password_version = crypto.crypto_password_version_constants.v2018_text_password;
            filesender.ui.transfer.encryption_password_encoding = 'none';
            if($('#encryption_password_show_container').is(":hidden")) {
                $('#encryption_password_show_container').show();
            }

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


    // Bind encryption password events
    var messageContainedPassword = false;
    if( filesender.config.upload_page_password_can_not_be_part_of_message_handling == 'warning'
        || filesender.config.upload_page_password_can_not_be_part_of_message_handling == 'error' )
    {
        filesender.ui.nodes.message.on(
            'keyup',
            function(e) {
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
        );
    }

    
    if( filesender.config.ui_use_datepicker_for_transfer_expire_time_selection ) {

        form.find('.expires-select-by-days').hide();
        form.find('.expires-select-by-picker').show();

            
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
    }
    
    // Custom collapse
    $('.fs-collapse__open').on('click', function() {
        $(this.parentElement).addClass('fs-collapse--open');
    });

    $('.fs-collapse__close').on('click', function() {
        $(this.parentElement).removeClass('fs-collapse--open');
    });

    form.find('.rlangdropitem').on('click', function() {
        form.find('#lang').html( this.innerHTML );
        form.find('#lang').attr('data-id', this.getAttribute('data-id'));
    });

    // Bind advanced options display toggle
    form.find('.toggle_advanced_options').on('click', function() {
        $('.advanced_options').slideToggle();
        return false;
    });

    form.find('.toggle_hidden_options').on('click', function() {
        $('.hidden_options').slideToggle();
        return false;
    });

    form.find('input[id="get_a_link"]').on('change', function() {
        var choice = $(this).is(':checked');
        form.find(
            '.fieldcontainer[data-related-to="message"], .recipients,' +
            ' .fieldcontainer[data-option="add_me_to_recipients"],' +
            ' .fieldcontainer[data-option="email_me_copies"],' +
            ' .fieldcontainer[data-related-to="emailfrom"],' +
            ' .fieldcontainer[data-related-to="verify_email_to_download"],' +
            ' .custom-checkbox[data-option="add_me_to_recipients"],' +
            ' .fieldcontainer[data-option="enable_recipient_email_download_complete"]'
        ).toggle(!choice);

        form.find(
            ' .fieldcontainer[data-option="hide_sender_email"]'
        ).toggle(!choice);

        form.find(
            ' .fieldcontainer[data-option="hide_sender_email"]'
        ).toggle(choice);

        filesender.ui.evalUploadEnabled();
    });

    form.find('input[name="transfer-type"]').on('change', function() {
        const value = $('input[name="transfer-type"]:checked' ).val();
        filesender.ui.onChangeTransferType(value);
    });

    form.find('input[name="add_me_to_recipients"]').on('change', function() {
        filesender.ui.evalUploadEnabled();
    });

    // Bind aup
    filesender.ui.nodes.aupshowhide.addClass('clickable').on('click', function() {
        $(this).closest('.aupbox').find('.terms').slideToggle();
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
        $('#encryption_password_container').show();
        $('#encryption_password_container_generate').show();
        $('#encryption_password_show_container').show();
        $('#encryption_description_container').show();
        $('#encgroup1').show();
        $('#encgroup2').show();
        $('#encgroup3').show();

        filesender.ui.transfer.encryption = filesender.ui.nodes.encryption.toggle.is(':checked');
        filesender.ui.files.checkEncryptionPassword(filesender.ui.nodes.encryption.password, true );


    }

    // Bind encryption
    filesender.ui.nodes.encryption.toggle.on('change', function(e) {
        const isChecked = filesender.ui.nodes.encryption.toggle.is(':checked');

        if (isChecked) {
            $('#encgroup1').show();
        } else {
            $('#encgroup1').slideToggle();
        }

        // $('#encgroup1').slideToggle();
        // $('#encgroup2').slideToggle();
        // $('#encgroup3').slideToggle();

        filesender.ui.transfer.encryption = isChecked;

        filesender.ui.files.checkEncryptionPassword(filesender.ui.nodes.encryption.password, true );

        for(var i=0; i<filesender.ui.transfer.getFileCount(); i++) {
            var file = filesender.ui.transfer.files[i];

            var node = filesender.ui.nodes.files.list.find('.file[data-name="' + file.name + '"][data-size="' + file.size + '"]');
            filesender.ui.transfer.checkFileAsStillValid(
                file,
                function(ok) {
                    node.removeClass('invalid');
                    node.find('.invalid').remove();
                    node.find('.invalid_reason').remove();
                    node.find('.error').text('');
                },
                function(error) {
                    var tt = 1;
                    if(error.details && error.details.filename) filesender.ui.files.invalidFiles.push(error.details.filename);
                    node.addClass('invalid');
                    node.find('.error').text(lang.tr(error.message));
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
        var password = encoded.value;
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

            var auth = $('body').attr('data-auth-type');
            if(auth == 'guest') {

                // confirm that the upload is only intended to the voucher issuer.
                if( getOption( 'add_me_to_recipients' )
                    && !getGuestOption( 'can_only_send_to_me' )
                    && !filesender.ui.transfer.recipients.length )
                {
                    filesender.ui.confirm(lang.tr('confirm_upload_add_to_recipients_with_no_explicit_address'),
                                          function() { // ok
                                              startUpload();
                                          },
                                          function() { // cancel
                                          });
                    
                    // dailog will start the upload if the user confirms the action
                    // so we fall through here.
                    return false;
                }
            }

            startUpload();
        }
        return false;
    }).button();

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
        }).button();
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
    $( "input" ).on( "keydown", function( e ) {
        // esc key
        if( e.which == 27 ) {
            e.stopImmediatePropagation();
        }
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

        $('<div class="info message" />').html(
            lang.tr('reader_not_supported').r({size: filesender.ui.formatBytes(filesender.config.max_legacy_file_size)}).out())
            .insertBefore(filesender.ui.nodes.files.list);

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

    form.find('.stopbutton').on('click', function(e) {
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
    });

    form.find('.pausebutton').on('click', function(e) {
        filesender.ui.cancelAutomaticResume();

        pause( true );
        filesender.ui.nodes.stats.average_speed.find('.value').text(lang.tr('paused'));
        filesender.ui.nodes.stats.estimated_completion.find('.value').text('');
        filesender.ui.setTimeSinceDataWasLastSentMessage(lang.tr('paused'));
        form.find('.resumebutton').prop("disabled",false);
        form.find('.pausebutton').prop("disabled",true);
        return false;
    });

    form.find('.resumebutton').on('click', function(e) {
        var force = filesender.ui.automatic_resume_retries > 0;
        resume( force, true );
        form.find('.pausebutton').prop("disabled", false);
        form.find('.resumebutton').prop("disabled", true);

        return false;
    });


    if(auth == 'guest') {
        var transfer_options = JSON.parse(form.find('input[id="guest_transfer_options"]').val());
        for(var option in filesender.ui.nodes.options) {
            if(option == 'undefined' || option == 'expires') continue;
            var i = filesender.ui.nodes.options[option];
            if(i.is('[type="checkbox"]')) {
                i.prop('checked', transfer_options[option]);
            } else {
                i.val(transfer_options[option]);
            }
        }

        $('input[name="transfer-type"]').trigger('change');
        $('#transfer-email').prop("checked", true);
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
                $('#please_readd_files_message').show();
                var table = filesender.ui.nodes.files.filestable;

                for(var i=0; i<required_files.length; i++) {

                    var tr = table.find('.tpl').clone().removeClass('tpl').addClass('file').addClass('invalid').addClass('required_file');
                    tr.attr('data-name', required_files[i].name);
                    tr.attr('data-size', required_files[i].size);
                    tr.attr('data-cid',  required_files[i].cid);
                    tr.data('file',      required_files[i]);
                    tr.find('.filename').text(required_files[i].name);
                    tr.find('.filesize').text(filesender.ui.formatBytes(required_files[i].size));
                    tr.find('.error').text(lang.tr('please_add_file_again'));
                    tr.find('.remove').hide();

                    tr.prependTo(table.find('tbody'));
                }
                $('.files_dragdrop').show();
                $('.files_actions').show();
                filesender.ui.transfer.status = 'stopped';

                filesender.ui.files.updateStatsAndClearAll();

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

                if( filesender.config.ui_use_datepicker_for_transfer_expire_time_selection ) {
                    filesender.ui.nodes.expires.datepicker('setDate', new Date(failed.expires * 1000));
                }
                
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

                // We do not show the stage2 page in this case as the options can
                // not be changed for the transfer once it is created.
                // filesender.ui.nodes.stages.nextStep.html( filesender.ui.nodes.stages.confirm.html() );
                filesender.ui.reuploading = true;

                filesender.ui.hideDragAndDropUpload();
                filesender.ui.goToStage(1);

                window.location.hash = "#uploading";
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

                var prompt = filesender.ui.popup( lang.tr('restart_failed_transfer'),
                    {load:   {callback: load, className: 'fs-button fs-button--inverted'},
                        forget: {callback: forget, className: 'fs-button fs-button--inverted'},
                        later:  {callback: later, className: 'fs-button fs-button--primary'}},
                    {onclose: later});
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

    window.setTimeout(
        function() {
            window.filesender.pageLoaded = true;
        }, 500 );
});

$('.instructions').on('click', function(){
    filesender.ui.nodes.files.files_input.click();
    return false;
});
