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
        
        if(!email.match(filesender.ui.validators.email))
            return email;
        
        for(var i=0; i<this.list.length; i++)
            if(this.list[i] == email) {
                filesender.ui.error({message: 'duplicate_recipient', details: {email: email}});
                return email;
            }
        
        if(this.list.length >= filesender.config.max_email_recipients) {
            filesender.ui.error({message: 'max_email_recipients_exceeded', details: {max: filesender.config.max_email_recipients}});
            return email;
        }
        
        var node = $('<div class="recipient" />').attr('email', email).appendTo(filesender.ui.nodes.recipients.list);
        $('<span />').attr('title', email).text(email).appendTo(node);
        $('<span class="remove fa fa-minus-square" />').attr({
            title: lang.tr('click_to_delete_recipient')
        }).on('click', function() {
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
};

filesender.ui.evalSendEnabled = function() {
    var ok = true;
    
    if(!filesender.ui.nodes.recipients.list.find('[email]').length) ok = false;
    
    filesender.ui.nodes.sendbutton.button(ok ? 'enable' : 'disable');
    
    return ok;
};

filesender.ui.send = function() {
    var options = [];
    
    var expires = filesender.ui.nodes.expires.datepicker('getDate').getTime() / 1000;
    
    var from = null;
    if(filesender.ui.nodes.from.length)
        from = filesender.ui.nodes.from.val();
    
    var subject = filesender.ui.nodes.subject.val();
    var message = filesender.ui.nodes.message.val();
    
    for(var o in filesender.ui.nodes.options)
        if(filesender.ui.nodes.options[o].is(':checked'))
            options.push(o);
    
    var emails = filesender.ui.recipients.list;
    var sent = 0;
    for(var i=0; i<emails.length; i++) {
        filesender.client.postGuestVoucher(from, emails[i], subject, message, expires, options, function() {
            sent++;
            if(sent < emails.length) return;
            
            filesender.ui.alert('success', lang.tr('guest_vouchers_sent').r({sent: sent}), function() {
                filesender.ui.reload();
            });
        });
    }
};

$(function() {
    var page = $('.guests_page');
    if(!page.length) return;
    
    // Transfer
    filesender.ui.transfer = new filesender.transfer();
    
    // Register frequently used nodes
    filesender.ui.nodes = {
        recipients: {
            input: page.find('.send_voucher input[name="to"]'),
            list: page.find('.send_voucher .recipients'),
        },
        from: page.find('.send_voucher select[name="from"]'),
        subject: page.find('.send_voucher input[name="subject"]'),
        message: page.find('.send_voucher textarea[name="message"]'),
        expires: page.find('.send_voucher input[name="expires"]'),
        options: {},
        sendbutton: page.find('.send_voucher .send'),
    };
    page.find('.options input').each(function() {
        var i = $(this);
        filesender.ui.nodes.options[i.attr('name')] = i;
    });

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
    
    // Bind picker
    var maxdate = new Date((new Date()).getTime() + 24*3600*1000 * filesender.config.default_daysvalid);
    filesender.ui.nodes.expires.datepicker({
        minDate: new Date(),
        maxDate: maxdate,
    });
    filesender.ui.nodes.expires.datepicker('setDate', maxdate);
    
    // Make options label toggle checkboxes
    page.find('.options label').on('click', function() {
        var checkbox = $(this).closest('.fieldcontainer').find(':checkbox');
        checkbox.prop('checked', !checkbox.prop('checked'));
    }).css('cursor', 'pointer');
    
    // Bind buttons
    filesender.ui.nodes.sendbutton.on('click', function() {
        if($(this).is('[aria-disabled="false"]')) {
            filesender.ui.send();
            filesender.ui.nodes.sendbutton.button('disable');
        }
        return false;
    }).button().button('disable');
    
    // special fix for esc key on firefox stopping xhr
    window.addEventListener('keydown', function(e) {
        (e.keyCode == 27 && e.preventDefault())
    });
});
