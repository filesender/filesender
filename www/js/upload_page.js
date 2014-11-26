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

// Manage files
filesender.ui.files = {
    invalidFiles: [],
    
    // File selection (browse / drop) handler
    add: function(files, source_node) {
        var node = null;
        for(var i=0; i<files.length; i++) {
            var info = files[i].name + ' : ' + filesender.ui.formatBytes(files[i].size);
            
            node = $('<div class="file" data-name="' + files[i].name + '" data-size="' + files[i].size + '" />').appendTo(filesender.ui.nodes.files.list);
            
            $('<span class="info" />').text(info).attr({title: info}).appendTo(node);
            
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
                    total_size -= parseInt(size);
                    filesender.ui.nodes.stats.number_of_files.show().find('.value').text(filesender.ui.transfer.files.length + '/' + filesender.config.max_transfer_files);
                    filesender.ui.nodes.stats.size.show().find('.value').text(filesender.ui.formatBytes(total_size) + '/' + filesender.ui.formatBytes(filesender.config.max_transfer_size));
                } else {
                    filesender.ui.files.invalidFiles.splice(iidx, 1);
                }
                
                filesender.ui.evalUploadEnabled();
            }).appendTo(node);
            
            var added_cid = filesender.ui.transfer.addFile(files[i], function(error) {
                var tt = 1;
                filesender.ui.files.invalidFiles.push(error.details.filename);
                node.addClass('invalid');
                node.addClass(error.message);
                $('<span class="invalid fa fa-exclamation-circle fa-lg" />').prependTo(node.find('.info'))
                node.attr({
                    title: lang.tr('invalid_file').replace({reason: lang.tr(error.message)})
                });
                node.find('.info').removeAttr('title');
            }, source_node);
            
            filesender.ui.nodes.files.clear.button('enable');
            filesender.ui.evalUploadEnabled();
            
            if(added_cid === false) continue;
            
            node.attr('data-cid', added_cid);
            
            var bar = $('<div class="progressbar" />').appendTo(node);
            $('<div class="progress-label" />').appendTo(bar);
            bar.progressbar({
                value: false,
                max: 1000,
                change: function() {
                    var bar = $(this);
                    var v = bar.progressbar('value');
                    bar.find('.progress-label').text((v / 10).toFixed(1) + '%');
                },
                complete: function() {
                    var bar = $(this);
                    bar.find('.progress-label').text(lang.tr('done'));
                }
            });
            
            var size = 0;
            for(var j=0; j<filesender.ui.transfer.files.length; j++)
                size += filesender.ui.transfer.files[j].size;
            
            filesender.ui.nodes.stats.number_of_files.show().find('.value').text(filesender.ui.transfer.files.length + '/' + filesender.config.max_transfer_files);
            filesender.ui.nodes.stats.size.show().find('.value').text(filesender.ui.formatBytes(size) + '/' + filesender.ui.formatBytes(filesender.config.max_transfer_size));
            
            node.attr('index', filesender.ui.transfer.files.length - 1);
        }
        
        return node;
    },
    
    // Update progress bar, run in transfer context
    progress: function(file, complete) {
        var size = 0;
        var uploaded = 0;
        for(var i=0; i<this.files.length; i++) {
            size += this.files[i].size;
            uploaded += this.files[i].uploaded;
        }
        
        var currentTime = (new Date()).getTime();
        if (this.pause_length > 0){
            var time = currentTime - this.pause_length - this.time;
        }else{
            var time = currentTime - this.time;            
        }
        
        var speed = uploaded / (time / 1000);
        
        if (filesender.config.upload_display_bits_per_sec)
            speed *= 8;
        
        filesender.ui.nodes.stats.uploaded.find('.value').text(filesender.ui.formatBytes(uploaded) + '/' + filesender.ui.formatBytes(size));
        
        if(this.status != 'paused')
            filesender.ui.nodes.stats.average_speed.find('.value').text(filesender.ui.formatSpeed(speed) + lang.tr('per_second'));
        
        var bar = filesender.ui.nodes.files.list.find('[data-cid="' + file.cid + '"] .progressbar');
        bar.progressbar('value', Math.round(1000 * file.uploaded / file.size));
    },
    
    // Clear the file box
    clear: function() {
        filesender.ui.transfer.files = [];
        
        filesender.ui.nodes.files.input.val('');
        
        filesender.ui.nodes.files.list.find('div').remove();
        
        filesender.ui.nodes.files.clear.button('disable');
        
        filesender.ui.evalUploadEnabled();
    },
};

// Manage recipients
filesender.ui.recipients = {
    // Add recipient to list
    add: function(email) {
        if(email.match(/[,;\s]/)) { // Multiple values
            email = email.split(/[,;\s]/);
            var invalid = [];
            for(var i=0; i<email.length; i++) {
                var s = email[i].replace(/^\s+/g, '').replace(/\s+$/g, '');
                if(!s) continue;
                if(!this.add(s))
                    invalid.push(s);
            }
            return invalid.join(', ');
        }
        
        var added = true;
        filesender.ui.transfer.addRecipient(email, function(error, data) {
            added = false;
        });
        if(!added) return email;
        
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
        
        var invalid = this.add(input.val());
        
        if(invalid) {
            input.val(invalid);
            input.addClass('invalid');
            if(!marker) {
                marker = $('<span class="invalid fa fa-exclamation-circle fa-lg" />').attr({
                    title: lang.tr('invalid_recipient')
                });
                input.data('error_marker', marker);
            }
            marker.insertBefore(input);
        }else{
            input.val('');
            input.removeClass('invalid');
            if(marker) marker.remove();
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
        $(filesender.ui.nodes.recipients.input).autocomplete({
            source: function (request, response) {
                filesender.client.getFrequentRecipients(request.term,
                    function (data) {
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
            },
            select: function (event, ui) {
                filesender.ui.recipients.add(ui.item.value);
                
                var marker = $(this).data('error_marker');
        
                $(this).val('');
                $(this).removeClass('invalid');
                if(marker) marker.remove();
                
                return false;
            },
            minLength: filesender.config.minimum_characters_for_autocomplete
        });
    }
};

filesender.ui.evalUploadEnabled = function() {
    var ok = true;
    
    // Check if there is no files with banned extension
    if (filesender.ui.files.invalidFiles.length > 0)ok  = false;
    
    if(!filesender.ui.transfer.files.length) ok = false;
    if(!filesender.ui.transfer.recipients.length) ok = false;
    
    if(filesender.ui.nodes.aup.length)
        if(!filesender.ui.nodes.aup.is(':checked')) ok = false;
    
    filesender.ui.nodes.buttons.start.button(ok ? 'enable' : 'disable');
    
    return ok;
};

filesender.ui.startUpload = function() {
    this.transfer.expires = filesender.ui.nodes.expires.datepicker('getDate').getTime() / 1000;
    
    if(filesender.ui.nodes.from.length)
        this.transfer.from = filesender.ui.nodes.from.val();
    
    this.transfer.subject = filesender.ui.nodes.subject.val();
    this.transfer.message = filesender.ui.nodes.message.val();
    if (filesender.ui.nodes.guest_token){
        this.transfer.guest_token = filesender.ui.nodes.guest_token.val();
    }
    
    for(var o in filesender.ui.nodes.options)
        if(filesender.ui.nodes.options[o].is(':checked'))
            this.transfer.options.push(o);
    
    
    this.transfer.onprogress = filesender.ui.files.progress;
    
    this.transfer.oncomplete = function(time) {
        filesender.ui.files.clear();
        filesender.ui.recipients.clear();
        filesender.ui.nodes.subject.val('');
        filesender.ui.nodes.message.val('');
        filesender.ui.nodes.expires.datepicker('setDate', (new Date()).getTime() + 24*3600*1000 * filesender.config.default_days_valid);
        
        filesender.ui.alert('success', lang.tr('done_uploading'), function() {
            filesender.ui.goToPage('transfers');
        });
        // TODO popup (view uploaded / upload other)
    };
    
    var errorHandler = function(error) {
        error = {message: 'upload_failed', details: error};
        filesender.ui.error(error,function(){
            //todo le code du reload
            filesender.ui.transfer.status = 'stopped';
            filesender.ui.reload();
        });
    };
    
    this.transfer.onerror = errorHandler;
    
    filesender.ui.nodes.files.list.find('.progressbar').show();
    
    filesender.ui.nodes.files.list.find('.file .remove').remove();
    
    filesender.ui.nodes.stats.number_of_files.hide();
    filesender.ui.nodes.stats.size.hide();
    filesender.ui.nodes.stats.uploaded.show();
    filesender.ui.nodes.stats.average_speed.show();
    
    // TODO lock fields
    this.transfer.start(errorHandler);
};

$(function() {
    var form = $('#upload_form');
    if(!form.length) return;
    
    // Transfer
    filesender.ui.transfer = new filesender.transfer();
    
    // Register frequently used nodes
    filesender.ui.nodes = {
        files: {
            input: form.find(':file'),
            list: form.find('.files'),
            dragdrop: form.find('.files_dragdrop'),
            select: form.find('.files_actions .select_files'),
            clear: form.find('.files_actions .clear_all'),
        },
        recipients: {
            input: form.find('input[name="to"]'),
            list: form.find('.recipients'),
        },
        from: form.find('select[name="from"]'),
        subject: form.find('input[name="subject"]'),
        message: form.find('textarea[name="message"]'),
        guest_token: form.find('input[type="hidden"][name="guest_token"]'),
        aup: form.find('input[name="aup"]'),
        expires: form.find('input[name="expires"]'),
        options: {},
        buttons: {
            start: form.find('.buttons .start'),
            pause: form.find('.buttons .pause'),
            resume: form.find('.buttons .resume'),
            stop: form.find('.buttons .stop')
        },
        stats: {
            number_of_files: form.find('.files_actions .stats .number_of_files'),
            size: form.find('.files_actions .stats .size'),
            uploaded: form.find('.files_actions .stats .uploaded'),
            average_speed: form.find('.files_actions .stats .average_speed')
        }
    };
    form.find('.basic_options input, .advanced_options input').each(function() {
        var i = $(this);
        filesender.ui.nodes.options[i.attr('name')] = i;
    });
    
    // Bind file list clear button
    filesender.ui.nodes.files.clear.on('click', function() {
        if($(this).button('option', 'disabled')) return;
        
        filesender.ui.files.clear();
        return false;
    }).button().button('disable');
    
    // Bind file list select button
    filesender.ui.nodes.files.select.on('click', function() {
        filesender.ui.nodes.files.input.click();
        return false;
    }).button();
    
    // Bind file drag drop events
    if(filesender.supports.reader) $('body').on('dragover', function (e) {
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
        
        filesender.ui.files.add(e.originalEvent.dataTransfer.files);
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
    
    // Bind file list select button
    filesender.ui.nodes.files.input.on('change', function() {
        // multiple files selected
        // loop through all files and show their values
        if (document.readyState != 'complete' && document.readyState != 'interactive') {
            return;
        }

        if(typeof this.files == 'undefined') return;
        
        filesender.ui.files.add(this.files, this);
    });
    
    filesender.ui.recipients.autocomplete();
    
    // Handle "back" browser action
    var files = filesender.ui.nodes.files.input[0].files;
    if(files && files.length) filesender.ui.files.add(files);
    
    // Setup date picker
    $.datepicker.setDefaults({
        closeText: lang.tr('DP_closeText').out(),
        prevText: lang.tr('DP_prevText').out(),
        nextText: lang.tr('DP_nextText').out(),
        currentText: lang.tr('DP_currentText').out(),
        
        monthNames: lang.tr('DP_monthNames').values(),
        monthNamesShort: lang.tr('DP_monthNamesShort').values(),
        dayNames: lang.tr('DP_dayNames').values(),
        dayNamesShort: lang.tr('DP_dayNamesShort').values(),
        dayNamesMin: lang.tr('DP_dayNamesMin').values(),
        
        weekHeader: lang.tr('DP_weekHeader').out(),
        dateFormat: lang.tr('DP_dateFormat').out(),
        
        firstDay: parseInt(lang.tr('DP_firstDay').out()),
        isRTL: lang.tr('DP_isRTL').out().match(/true/),
        showMonthAfterYear: lang.tr('DP_showMonthAfterYear').out().match(/true/),
        
        yearSuffix: lang.tr('DP_yearSuffix').out()
    });
    
    // Bind picker
    var mindate = new Date();
    var maxdate = new Date((new Date()).getTime() + 24*3600*1000 * filesender.config.default_days_valid);
    filesender.ui.nodes.expires.datepicker({
        minDate: mindate,
        maxDate: maxdate,
    });
    filesender.ui.nodes.expires.datepicker('setDate', maxdate);
    filesender.ui.nodes.expires.on('change', function() {
        filesender.ui.nodes.expires.datepicker('setDate', $(this).val());
        var d = filesender.ui.nodes.expires.datepicker('getDate').getTime() / 1000;
        
        var min = mindate.getTime() / 1000;
        min -= min % (24 * 3600);
        if(d < min) filesender.ui.nodes.expires.datepicker('setDate', mindate);
    });
    
    // Make options label toggle checkboxes
    form.find('.basic_options label, .advanced_options label').on('click', function() {
        var checkbox = $(this).closest('.fieldcontainer').find(':checkbox');
        checkbox.prop('checked', !checkbox.prop('checked'));
    }).css('cursor', 'pointer');
    
    // Bind advanced options display toggle
    form.find('.toggle_advanced_options').on('click', function() {
        $('.advanced_options').slideToggle();
        return false;
    });
    
    // Bind aup
    form.find('label[for="aup"]').addClass('clickable').on('click', function() {
        $(this).closest('.fieldcontainer').find('.terms').slideToggle();
        return false;
    });
    
    filesender.ui.nodes.aup.on('change', function() {
        filesender.ui.evalUploadEnabled();
    });
    
    // Bind buttons
    filesender.ui.nodes.buttons.start.on('click', function() {
        $(this).focus(); // Get out of email field / datepicker so inputs are updated
        $(this).blur();
        if(filesender.ui.transfer.status == 'new' && $(this).is('[aria-disabled="false"]')) {
            filesender.ui.startUpload();
            filesender.ui.nodes.buttons.start.addClass('not_displayed');
            if(filesender.supports.reader) filesender.ui.nodes.buttons.pause.removeClass('not_displayed');
            filesender.ui.nodes.buttons.stop.removeClass('not_displayed');
        }
        return false;
    }).button().button('disable');
    
    if(filesender.supports.reader) {
        filesender.ui.nodes.buttons.pause.on('click', function() {
            filesender.ui.transfer.pause();
            filesender.ui.nodes.buttons.pause.addClass('not_displayed');
            filesender.ui.nodes.buttons.resume.removeClass('not_displayed');
            filesender.ui.nodes.stats.average_speed.find('.value').text(lang.tr('paused'));
            return false;
        }).button();
        
        filesender.ui.nodes.buttons.resume.on('click', function() {
            filesender.ui.transfer.resume();
            filesender.ui.nodes.buttons.pause.removeClass('not_displayed');
            filesender.ui.nodes.buttons.resume.addClass('not_displayed');
            return false;
        }).button();
    }
    
    filesender.ui.nodes.buttons.stop.on('click', function() {
        filesender.ui.confirm(lang.tr('confirm_stop_upload'), function() {
            filesender.ui.transfer.stop(function() {
                filesender.ui.goToPage('upload');
            });
        });
        return false;
    }).button();
    
    
    // special fix for esc key on firefox stopping xhr
    window.addEventListener('keydown', function(e) {
        (e.keyCode == 27 && e.preventDefault())
    });
    
    // Set message to display if the user changes pages / close tab / close browser
    window.onbeforeunload = function() {
        if(!filesender.ui.transfer.status.match(/^(new|done|stopped)$/))
            return lang.tr('confirm_leave_upload_page'); // Ask for leaving confirmation
    };
    
    // Legacy uploader
    if(filesender.supports.reader) return;
    var selector = form.find('.file_selector').show();
    
    // Put notice
    form.addClass('legacy');
    
    $('<div class="info message" />').text(lang.tr('reader_not_supported')).insertBefore(filesender.ui.nodes.files.list);
    
    // Remove unavailable features
    filesender.ui.nodes.files.select.remove();
    filesender.ui.nodes.files.dragdrop.remove();
    filesender.ui.nodes.buttons.pause.remove();
    
    // Adapt file selection
    filesender.ui.nodes.files.input.remove();
    $('<input name="file" type="file" />').appendTo(selector).on('change', function() {
        var sel = $(this)
        var file = sel.clone();
        
        // TODO check file size, reject if over filesender.config.max_legacy_upload_size
        
        var node = filesender.ui.files.add(this.files, file.get(0));
        if(!node) return;
        
        file.appendTo(node);
        sel.val('');
    });
});
