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
            
            node = $('<div class="file" />').attr({
                'data-name': files[i].name,
                'data-size': files[i].size
            }).appendTo(filesender.ui.nodes.files.list);
            
            $('<span class="info" />').text(info).attr({title: info}).appendTo(node);
            
            if(filesender.ui.nodes.required_files) {
                // Upload restart mode
                var req = filesender.ui.nodes.required_files.find('.file[data-name="' + files[i].name + '"][data-size="' + files[i].size + '"]');
                
                if(!req.length) {
                    filesender.ui.alert('error', lang.tr('unexpected_file'));
                    node.remove();
                    return null;
                }
                
                var file = req.data('file');
                var added_cid = req.attr('data-cid');
                file.cid = added_cid;
                file.blob = files[i];
                
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
                
                var added_cid = filesender.ui.transfer.addFile(files[i], function(error) {
                    var tt = 1;
                    if(error.details && error.details.filename) filesender.ui.files.invalidFiles.push(error.details.filename);
                    node.addClass('invalid');
                    node.addClass(error.message);
                    $('<span class="invalid fa fa-exclamation-circle fa-lg" />').prependTo(node.find('.info'))
                    $('<div class="invalid_reason" />').text(lang.tr(error.message)).appendTo(node);
                }, source_node);
                
                filesender.ui.nodes.files.clear.button('enable');
                
                if(added_cid === false) continue;
            }
                
            filesender.ui.evalUploadEnabled();
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
                    bar.closest('.file').addClass('done');
                }
            });
            
            $('<span class="fa fa-lg fa-check done_icon" />').appendTo(node);
            
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
        }
        
        filesender.ui.nodes.files.list.scrollTop(filesender.ui.nodes.files.list.prop('scrollHeight'));
        
        return node;
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
        
        if (filesender.config.upload_display_bits_per_sec)
            speed *= 8;
        
        filesender.ui.nodes.stats.uploaded.find('.value').html(uploaded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' bytes<br/>' + filesender.ui.formatBytes(uploaded) + ' /' + filesender.ui.formatBytes(size));
        
        if(this.status != 'paused')
            filesender.ui.nodes.stats.average_speed.find('.value').text(filesender.ui.formatSpeed(speed));
        
        var bar = filesender.ui.nodes.files.list.find('[data-cid="' + file.cid + '"] .progressbar');
        bar.progressbar('value', Math.floor(1000 * (file.fine_progress ? file.fine_progress : file.uploaded) / file.size)); 
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
                if(marker) marker.hide();
                
                return false;
            },
            minLength: filesender.config.autocomplete.min_characters
        });
    }
};

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
    
    if(filesender.ui.nodes.required_files) {
        if(ok) filesender.ui.nodes.required_files.hide();
        filesender.ui.nodes.buttons.restart.button(ok ? 'enable' : 'disable');
    } else {
        filesender.ui.nodes.buttons.start.button(ok ? 'enable' : 'disable');
    }
    
    return ok;
};

filesender.ui.startUpload = function() {
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
    this.transfer.encryption = filesender.ui.nodes.encryption.toggle.is(':checked'); 
    this.transfer.encryption_password = filesender.ui.nodes.encryption.password.val();
    this.transfer.disable_terasender = filesender.ui.nodes.disable_terasender.is(':checked');
    
    this.transfer.onprogress = filesender.ui.files.progress;
    
    this.transfer.oncomplete = function(time) {
        var redirect_url = filesender.ui.transfer.options.redirect_url_on_complete;
        if(redirect_url) {
            filesender.ui.redirect(redirect_url);
            
            window.setTimeout(function(f) {
                filesender.ui.redirect(redirect_url);
                filesender.ui.alert('success', lang.tr('done_uploading_redirect').replace({url: redirect_url}));
            }, 5000);
            
            return;
        }
        
        var close = function() {
            filesender.ui.goToPage(
                filesender.ui.transfer.guest_token ? 'home' : 'transfers',
                null,
                filesender.ui.transfer.guest_token ? null : 'transfer_' + filesender.ui.transfer.id
            );
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
            $(this).focus().select();
        });
    };
    
    var errorHandler = function(error) {
        filesender.ui.error(error,function(){
            filesender.ui.transfer.status = 'stopped';
            filesender.ui.reload();
        });
    };
    
    this.transfer.onerror = errorHandler;
    
    // Setup watchdog to look for stalled clients (only in html5 and terasender modes)
    if(filesender.supports.reader) {
        var transfer = this.transfer;
        transfer.resetWatchdog();
        window.setInterval(function() { // Check for stalled every minute
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
    
    filesender.ui.nodes.stats.number_of_files.hide();
    filesender.ui.nodes.stats.size.hide();
    filesender.ui.nodes.stats.uploaded.show();
    filesender.ui.nodes.stats.average_speed.show();
    
    filesender.ui.nodes.form.find(':input:not(.file input[type="file"])').prop('disabled', true);
    
    return this.transfer.start(errorHandler);
};

$(function() {
    var form = $('#upload_form');
    if(!form.length) return;
    
    // Transfer
    filesender.ui.transfer = new filesender.transfer();
    
    // Register frequently used nodes
    filesender.ui.nodes = {
        form: form,
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
        encryption: {
                toggle: form.find('input[name="encryption"]'),
                password: form.find('input[name="encryption_password"]'),
                show_hide: form.find('#encryption_show_password'),
                generate: form.find('#encryption_generate_password')
            },
        disable_terasender: form.find('input[name="disable_terasender"]'),
        message: form.find('textarea[name="message"]'),
        guest_token: form.find('input[type="hidden"][name="guest_token"]'),
        lang: form.find('input[name="lang"]'),
        aup: form.find('input[name="aup"]'),
        expires: form.find('input[name="expires"]'),
        options: {},
        buttons: {
            start: form.find('.buttons .start'),
            restart: form.find('.buttons .restart'),
            pause: form.find('.buttons .pause'),
            resume: form.find('.buttons .resume'),
            stop: form.find('.buttons .stop')
        },
        stats: {
            number_of_files: form.find('.files_actions .stats .number_of_files'),
            size: form.find('.files_actions .stats .size'),
            uploaded: form.find('.files_actions .stats .uploaded'),
            average_speed: form.find('.files_actions .stats .average_speed')
        },
        need_recipients: form.attr('data-need-recipients') == '1'
    };
    form.find('.basic_options [data-option] input, .advanced_options [data-option] input').each(function() {
        var i = $(this);
        filesender.ui.nodes.options[i.attr('name')] = i;
    });
    
    // Bind file list clear button
    filesender.ui.nodes.files.clear.on('click', function() {
        if($(this).button('option', 'disabled')) return;
        
        filesender.ui.files.clear();
        return false;
    }).button({disabled: true});
    
    // Bind file list select button
    filesender.ui.nodes.files.select.on('click', function() {
        filesender.ui.nodes.files.input.click();
        return false;
    }).button();
    
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
        
        filesender.ui.files.add(this.files);
        
        // Forget (cloned) selection for webkit
        this.value = null;
    });
    
    if(!filesender.supports.reader) filesender.ui.nodes.files.input.removeAttr('multiple');
    
    filesender.ui.recipients.autocomplete();
    
    // Handle "back" browser action
    if(filesender.supports.reader) {
        var files = filesender.ui.nodes.files.input[0].files;
        if(files && files.length) filesender.ui.files.add(files);
    }

    // validate message as it is typed
    window.filesender.ui.handleFlagInvalidOnRegexMatch(
        filesender.ui.nodes.message,
        $('#message_can_not_contain_urls'),
        filesender.config.message_can_not_contain_urls_regex );
    
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
        dateFormat: lang.tr('dp_date_format').out(),
        
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
    filesender.ui.nodes.expires.on('change', function() {
        filesender.ui.nodes.expires.datepicker('setDate', $(this).val());
    });
    
    // Make options label toggle checkboxes
    form.find('.basic_options label, .advanced_options label').on('click', function() {
        var checkbox = $(this).closest('.fieldcontainer').find(':checkbox');
        checkbox.prop('checked', !checkbox.prop('checked'));
        checkbox.change();
    }).css('cursor', 'pointer');
    
    // Bind advanced options display toggle
    form.find('.toggle_advanced_options').on('click', function() {
        $('.advanced_options').slideToggle();
        return false;
    });
    
    form.find('input[name="get_a_link"]').on('change', function() {
        var choice = $(this).is(':checked');
        form.find(
            '.fieldcontainer[data-related-to="message"], .recipients,' +
            ' .fieldcontainer[data-option="add_me_to_recipients"],' +
            ' .fieldcontainer[data-option="email_me_copies"],' +
            ' .fieldcontainer[data-option="enable_recipient_email_download_complete"]'
        ).toggle(!choice);
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
    
    // Bind encryption
    filesender.ui.nodes.encryption.toggle.on('change', function() {
        $('#encryption_password_container').slideToggle();
        $('#encryption_password_container_generate').slideToggle();
        $('#encryption_password_show_container').slideToggle();
        $('#encryption_description_container').slideToggle();
        return false;
    });
    filesender.ui.nodes.encryption.generate.on('click', function() {
        filesender.ui.nodes.encryption.password.val(Math.random().toString(36).substr(2, 14));
        filesender.ui.nodes.encryption.show_hide.prop('checked',true);
        filesender.ui.nodes.encryption.show_hide.trigger('change');
    });
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
        if(filesender.ui.transfer.status == 'new' && $(this).is('[aria-disabled="false"]')) {
            filesender.ui.startUpload();
            filesender.ui.nodes.buttons.start.addClass('not_displayed');
            if(filesender.supports.reader) filesender.ui.nodes.buttons.pause.removeClass('not_displayed');
            filesender.ui.nodes.buttons.stop.removeClass('not_displayed');
        }
        return false;
    }).button({disabled: true});
    
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
        filesender.ui.confirm(lang.tr('confirm_stop_upload'), function() {
            filesender.ui.transfer.stop(function() {
                filesender.ui.goToPage('upload');
            });
        });
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
    
    if(!filesender.supports.reader) {
        // Legacy uploader
        var selector = form.find('.file_selector').show();
        
        // Put notice
        form.addClass('legacy');
        
        $('<div class="info message" />').html(lang.tr('reader_not_supported').r({size: filesender.ui.formatBytes(filesender.config.max_legacy_file_size)}).out()).insertBefore(filesender.ui.nodes.files.list);
        
        // Remove unavailable features
        filesender.ui.nodes.files.select.remove();
        filesender.ui.nodes.files.dragdrop.remove();
        filesender.ui.nodes.buttons.pause.remove();
        
        // Adapt file selection
        filesender.ui.nodes.files.input.remove();
        $('<input name="file" type="file" />').appendTo(selector).on('change', function() {
            var sel = $(this)
            var file = sel.clone();
            
            // TODO check file size, reject if over filesender.config.max_legacy_file_size
            
            var node = filesender.ui.files.add(this.files, file.get(0));
            if(!node) return;
            
            file.appendTo(node);
            sel.val('');
        });
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
    } else if(failed) {
        var id = failed.id;
        if(filesender.config.chunk_upload_security == 'key') {
            id += '?key=' + failed.files[0].uid;
            
        } else if(!auth || auth == 'guest') {
            id = null; // Cancel
        }
        
        if(id) filesender.client.getTransfer(id, function() {
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
            
        }, function(error) {
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
