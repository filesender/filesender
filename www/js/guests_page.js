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

// Manage recipients
filesender.ui.recipients = {
    list: [],
    
    // Add recipient to list
    add: function(email, errorhandler) {
        if(!errorhandler) errorhandler = function(error) {
            filesender.ui.error(error);
        };
        
        if(email.match(/[,;\s]/)) { // Multiple values
            email = email.split(/[,;\s]/);
            var invalid = [];
            var too_much = null;
            for(var i=0; i<email.length; i++) {
                if(too_much) continue;
                
                var s = email[i].replace(/^\s+/g, '').replace(/\s+$/g, '');
                if(!s) continue;
                
                if(this.add(s, function(error) {
                    if(error.message == 'guest_too_many_recipients')
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
        
        if(!email.match(filesender.ui.validators.email))
            return email;
        
        for(var i=0; i<this.list.length; i++) {
            if(this.list[i] == email) {
                //filesender.ui.error({message: 'duplicate_recipient', details: {email: email}});
                //return email;
                return '';
            }
        }
        
        if(filesender.config.max_guest_recipients && this.list.length >= filesender.config.max_guest_recipients) {
            errorhandler({message: 'guest_too_many_recipients', details: {max: filesender.config.max_guest_recipients}});
            return '';
        }
        
        var node = $('<div class="recipient" />').attr('email', email).appendTo(filesender.ui.nodes.recipients.list);
        $('<span />').attr('title', email).text(email).appendTo(node);
        $('<span class="remove fa fa-minus-square" />').attr({
            title: lang.tr('click_to_delete_recipient')
        }).on('click', function() {
            filesender.ui.recipients.remove($(this).closest('.recipient').attr('email'));
            $(this).parent().remove();
        }).appendTo(node);
        
        this.list.push(email);
        
        filesender.ui.nodes.recipients.list.show();
        
        filesender.ui.evalSendEnabled();
        
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
        
        filesender.ui.nodes.recipients.list.find('[email="' + email + '"]').remove();
        
        for(var i=0; i<this.list.length; i++)
            if(this.list[i] == email) {
                this.list.splice(i, 1);
                break;
            }
        
        if(!filesender.ui.nodes.recipients.list.find('[email]').length)
            filesender.ui.nodes.recipients.list.hide();
        
        filesender.ui.evalSendEnabled();
    },
    
    // Clear the recipients list
    clear: function() {
        filesender.ui.nodes.recipients.input.val('');
        
        filesender.ui.nodes.recipients.list.find('div').remove();
        
        filesender.ui.evalSendEnabled();
    },
    
    // Enable autocomplete for frequent recipients on a field
    autocomplete: function(){
        if(!filesender.config.autocomplete.enabled) return;
        
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

filesender.ui.evalSendEnabled = function() {
    var ok = true;
    
    if(!filesender.ui.nodes.recipients.list.find('[email]').length) ok = false;
    
    filesender.ui.nodes.sendbutton.button(ok ? 'enable' : 'disable');
    
    return ok;
};

filesender.ui.send = function() {
    var options = {guest: {}, transfer: {}};
    
    var expires = filesender.ui.nodes.expires.datepicker('getDate').getTime() / 1000;
    
    var from = null;
    if(filesender.ui.nodes.from.length)
        from = filesender.ui.nodes.from.val();
    
    var subject = filesender.ui.nodes.subject.val();
    var message = filesender.ui.nodes.message.val();
    
    for(var c in filesender.ui.nodes.options) {
        for(var o in filesender.ui.nodes.options[c]) {
            var i = filesender.ui.nodes.options[c][o];
            var v = i.filter('[type="checkbox"]') ? i.is(':checked') : i.val();
            options[c][o] = v;
        }
    }

    var emails = filesender.ui.recipients.list;
    var sent = 0;
    for(var i=0; i<emails.length; i++) {
        filesender.client.postGuest(from, emails[i], subject, message, expires, options, function() {
            sent++;
            if(sent < emails.length) return;
            
            filesender.ui.notify('success', lang.tr('guest_vouchers_sent').r({sent: sent}), function() {
                filesender.ui.reload();
            });
        });
    }
};

$(function() {
    var send_voucher = $('#send_voucher');
    if(!send_voucher.length) return;
    
    // Register frequently used nodes
    filesender.ui.nodes = {
        recipients: {
            input: send_voucher.find('input[name="to"]'),
            list: send_voucher.find('.recipients'),
        },
        from: send_voucher.find('select[name="from"]'),
        subject: send_voucher.find('input[name="subject"]'),
        message: send_voucher.find('textarea[name="message"]'),
        expires: send_voucher.find('input[name="expires"]'),
        does_not_expire: send_voucher.find('input[name="does_not_expire"]'),
        get_a_link: send_voucher.find('input[name="get_a_link"]'),
        can_only_send_to_me: send_voucher.find('input[name="can_only_send_to_me"]'),
        options: {guest: {}, transfer: {}},
        sendbutton: send_voucher.find('.send'),
        message_can_not_contain_urls: send_voucher.find('textarea[name="message_can_not_contain_urls"]'),
    };
    send_voucher.find('.guest_options input').each(function() {
        var i = $(this);
        filesender.ui.nodes.options.guest[i.attr('name')] = i;
    });
    send_voucher.find('.transfer_options input').each(function() {
        var i = $(this);
        filesender.ui.nodes.options.transfer[i.attr('name')] = i;
    });
    
    filesender.ui.recipients.autocomplete();
    
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

    // validate message as it is typed
    window.filesender.ui.handleFlagInvalidOnRegexMatch(
        filesender.ui.nodes.message,
        $('#message_can_not_contain_urls'),
        filesender.config.message_can_not_contain_urls_regex );
    
    // Bind picker
    filesender.ui.nodes.expires.datepicker({
        minDate: filesender.config.min_guest_days_valid,
        maxDate: filesender.config.max_guest_days_valid
    });
    // set value from epoch time
    filesender.ui.setDateFromEpochData( filesender.ui.nodes.expires );
    filesender.ui.nodes.expires.on('change', function() {
        filesender.ui.nodes.expires.datepicker('setDate', $(this).val());
    });
    
    /**
     * It doesn't make sense to allow only send to me when the guest is getting a link
     * This is a function so it can be called on click and also right now to update the UI.
     */
    var get_a_link_updates = function() {
        var checked = filesender.ui.nodes.get_a_link.is(':checked');
        var onlyToMe = filesender.ui.nodes.can_only_send_to_me;
        if( checked ) {
            onlyToMe.prop('checked', false );
            onlyToMe.prop('disabled', true);
            filesender.ui.notify('info',lang.tr('turning_on_guests_get_a_link_disables_can_only_send_to_me'));
        } else {
            onlyToMe.prop('disabled', false);
        }
    }
    filesender.ui.nodes.get_a_link.on('click', function() {
        get_a_link_updates();
    });
    get_a_link_updates();


    if( filesender.ui.nodes.does_not_expire ) {
        filesender.ui.nodes.does_not_expire.on('click', function() {
            var checked = filesender.ui.nodes.does_not_expire.is(':checked');
            filesender.ui.nodes.expires.prop('disabled', checked);
        });
    }
    
    
    // Bind advanced options display toggle
    send_voucher.find('.toggle_advanced_options').on('click', function() {
        $(this).closest('.options_box').find('.advanced_options').slideToggle();
        return false;
    });
    
    // Bind buttons
    filesender.ui.nodes.sendbutton.on('click', function() {
        $(this).focus(); // Get out of email field / datepicker so inputs are updated
        $(this).blur();
        if($(this).filter('[aria-disabled="false"]')) {
            filesender.ui.send();
            filesender.ui.nodes.sendbutton.button('disable');
        }
        return false;
    }).button({disabled: true});
    
    // special fix for esc key on firefox stopping xhr
    $( "input" ).on( "keydown", function( e ) {
        // esc key
        if( e.which == 27 ) {
            e.stopImmediatePropagation();
        }
    });
});
