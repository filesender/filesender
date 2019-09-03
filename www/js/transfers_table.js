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
    if(window.transfers_table) return;
    window.transfers_table = true;
    
    // Expand each transfer's details
    $('.transfer .expand span, .transfer span.expand').on('click', function() {
        var el = $(this);
        var tr = el.closest('tr');
        var details = el.closest('table').find('.transfer_details[data-id="' + tr.attr('data-id') + '"]');
        
        tr.hide('fast');
        details.show('fast');
    });
    
    // Collapse each transfer's details
    $('.transfer_details .collapse span').on('click', function() {
        var el = $(this);
        var details = el.closest('tr');
        var tr = el.closest('table').find('.transfer[data-id="' + details.attr('data-id') + '"]');
        
        details.hide('fast');
        tr.show('fast');
    });
    
    // Expand / retract all
    $('thead .expand span').on('click', function() {
        var el = $(this);
        var table = el.closest('table');
        
        var expanded = !el.hasClass('expanded');
        
        table.find('.transfer_details')[expanded ? 'show' : 'hide']('fast');
        table.find('.transfer')[expanded ? 'hide' : 'show']('fast');
        
        el.toggleClass('expanded', expanded).toggleClass('fa-plus-circle', !expanded).toggleClass('fa-minus-circle', expanded);
    });
    
    // Clone attributes for easier access
    $('.transfer_details').each(function() {
        var id = $(this).attr('data-id');
        if(!id || isNaN(id)) return;
        
        var t = $('.transfer[data-id="' + id + '"]');
        
        $(this).attr({
            'data-transfer': '',
            'data-recipients-enabled': t.attr('data-recipients-enabled'),
            'data-errors': t.attr('data-errors'),
            'data-expiry-extension': t.attr('data-expiry-extension'),
        });
        
        t.attr({'data-transfer': ''});
    });
    
    // Transfer delete buttons
    $('.actions [data-action="delete"]').on('click', function() {
        var id = $(this).closest('[data-transfer]').attr('data-id');
        if(!id || isNaN(id)) return;
        
        if($(this).closest('table').filter('[data-mode="admin"][data-status="available"]')) {
            var d = filesender.ui.chooseAction(['delete_transfer_nicely', 'delete_transfer_roughly'], function(choosen) {
                var done = function() {
                    $('[data-transfer][data-id="' + id + '"]').remove();
                    filesender.ui.notify('success', lang.tr('transfer_deleted'));
                };
                
                switch(choosen) {
                    case 'delete_transfer_nicely' :
                        filesender.client.closeTransfer(id, function() {
                            filesender.client.deleteTransfer(id, done);
                        });
                        break;
                    
                    case 'delete_transfer_roughly' :
                        filesender.client.deleteTransfer(id, done);
                        break;
                }
            });
        } else if($(this).closest('table').filter('[data-mode="admin"][data-status="uploading"]')) {
            filesender.ui.confirm(lang.tr('stop_transfer_upload'), function() {
                filesender.client.deleteTransfer(id, function() {
                    $('[data-transfer][data-id="' + id + '"]').remove();
                    filesender.ui.notify('success', lang.tr('transfer_upload_stopped'));
                });
            });
        } else {
            filesender.ui.confirm(lang.tr('confirm_close_transfer'), function() {
                filesender.client.closeTransfer(id, function() {
                    $('[data-transfer][data-id="' + id + '"]').remove();
                    filesender.ui.notify('success', lang.tr('transfer_closed'));
                    filesender.ui.updateUserQuotaBar();
                });
            });
        }
    });
    
    // Extend buttons
    $('[data-expiry-extension="0"] [data-action="extend"]').addClass('disabled').attr({title: lang.tr('transfer_expiry_extension_count_exceeded')});
    
    $('[data-expiry-extension][data-expiry-extension!="0"] [data-action="extend"]').each(function() {
        $(this).attr({
            title: lang.tr('extend_expiry_date').r({
                days: $(this).closest('[data-transfer]').attr('data-expiry-extension')
            })
        });
    }).on('click', function() {
        if($(this).hasClass('disabled')) return;
        
        var t = $(this).closest('[data-transfer]');
        
        var id = t.attr('data-id');
        if(!id || isNaN(id)) return;
        
        var duration = parseInt(t.attr('data-expiry-extension'));
        
        var extend = function(remind) {
            filesender.client.extendTransfer(id, remind, function(t) {
                $('[data-transfer][data-id="' + id + '"]').attr('data-expiry-extension', t.expiry_date_extension);
                
                $('[data-transfer][data-id="' + id + '"] [data-rel="expires"]').text(t.expires.formatted);
                
                if(!t.expiry_date_extension) {
                    $('[data-transfer][data-id="' + id + '"] [data-action="extend"]').addClass('disabled').attr({
                        title: lang.tr('transfer_expiry_extension_count_exceeded')
                    });
                    
                } else {
                    $('[data-transfer][data-id="' + id + '"] [data-action="extend"]').attr({
                        title: lang.tr('extend_expiry_date').r({
                            days: $(this).closest('[data-transfer]').attr('data-expiry-extension')
                        })
                    });
                }
                
                filesender.ui.notify('success', lang.tr(remind ? 'transfer_extended_reminded' : 'transfer_extended').r({expires: t.expires.formatted}));
            });
        };
        
        var buttons = {};
        
        buttons.extend = function() {
            extend(false);
        };
        
        if(t.attr('data-recipients-enabled')) buttons.extend_and_remind = function() {
            extend(true);
        };
        
        buttons.cancel = false;
        
        filesender.ui.popup(lang.tr('confirm_dialog'), buttons).html(lang.tr('confirm_extend_expiry').r({days: duration}).out());
    });
    
    // Add recipient buttons
    $('[data-recipients-enabled=""] [data-action="add_recipient"]').addClass('disabled');
    
    $('[data-recipients-enabled="1"] [data-action="add_recipient"]').on('click', function() {
        var id = $(this).closest('[data-transfer]').attr('data-id');
        if(!id || isNaN(id)) return;
        
        var recipients = [];
        $('.transfer_details[data-id="' + id + '"] .recipients .recipient').each(function() {
            recipients.push($(this).attr('data-email'));
        });
        
        var prompt = filesender.ui.prompt(lang.tr('enter_to_email'), function() {
            var input = $(this).find('input');
            $('p.error', this).remove();
            
            var raw_emails = input.val().split(/[,;]/);
            
            var emails = [];
            var errors = [];
            
            for(var i=0; i<raw_emails.length; i++) {
                var email = raw_emails[i].replace(/^\s+/, '').replace(/\s+$/, '');
                if(!email) continue;
                
                if(!email.match(filesender.ui.validators.email)) {
                    errors.push(lang.tr('invalid_recipient').r({email: email}));
                    continue;
                }
                
                for(var j=0; j<recipients.length; j++) {
                    if(recipients[j] == email) {
                        errors.push(lang.tr('duplicate_recipient').r({email: email}));
                        continue;
                    }
                }
                
                for(var j=0; j<emails.length; j++) {
                    if(emails[j] == email) {
                        errors.push(lang.tr('duplicate_recipient').r({email: email}));
                        continue;
                    }
                }
                
                emails.push(email);
            }
            
            if(recipients.length + emails.length >= filesender.config.max_email_recipients)
                errors.push(lang.tr('max_email_recipients_exceeded').r({max: filesender.config.max_email_recipients}));
            
            if(errors.length) {
                for(var i=0; i<errors.length; i++)
                    $('<p class="error message" />').text(errors[i].out()).appendTo(this);
                return false;
            }
            
            var done = 0;
            for(var i=0; i<emails.length; i++) {
                filesender.client.addRecipient(id, emails[i], function() {
                    done++;
                    
                    if(done < emails.length) return;
                    
                    filesender.ui.notify('success', lang.tr('recipient_added'), function() {
                        filesender.ui.reload();
                    });
                });
            }
            
            return true;
        })
        
        prompt.append('<p>' + lang.tr('email_separator_msg') + '</p>');
        var input = $('<input type="text" class="wide" />').appendTo(prompt);
        input.focus();
    });
    
    // Remind buttons
    $('[data-recipients-enabled=""] .actions [data-action="remind"]').addClass('disabled');
    
    $('[data-recipients-enabled="1"] .actions [data-action="remind"]').on('click', function() {
        var id = $(this).closest('[data-transfer]').attr('data-id');
        if(!id || isNaN(id)) return;
        
        filesender.ui.confirm(lang.tr('confirm_remind_transfer'), function() {
            filesender.client.remindTransfer(id, function() {
                filesender.ui.notify('success', lang.tr('transfer_reminded'));
            });
        });
    });
    
    // Recipient remind buttons
    $('.transfer_details .recipient [data-action="remind"]').on('click', function() {
        var rcpt = $(this).closest('.recipient');
        var id = rcpt.attr('data-id');
        if(!id || isNaN(id)) return;
        
        filesender.ui.confirm(lang.tr('confirm_remind_recipient'), function() {
            filesender.client.remindRecipient(id, function() {
                filesender.ui.notify('success', lang.tr('recipient_reminded'));
            });
        });
    });
    
    // Recipient delete buttons
    $('.transfer_details .recipient [data-action="delete"]').on('click', function() {
        var rcpt = $(this).closest('.recipient');
        var id = rcpt.attr('data-id');
        var transfer = rcpt.closest('.transfer_details');
        if(!id || isNaN(id)) return;
        
        filesender.ui.confirm(lang.tr('confirm_delete_recipient'), function() {
            filesender.client.deleteRecipient(id, function() {
                rcpt.remove();
                if(!transfer.find('.recipients .recipient').length) {
                    transfer.prev('.transfer').remove();
                    transfer.remove();
                }
                filesender.ui.notify('success', lang.tr('recipient_deleted'));
            });
        });
    });
    
    // Recipient error display
    $('.transfer_details .recipient .errors [data-action="details"]').on('click', function() {
        var rcpt = $(this).closest('.recipient');
        var id = rcpt.attr('data-id');
        if(!id || isNaN(id)) return;
        
        filesender.client.getRecipient(id, function(recipient) {
            var popup = filesender.ui.wideInfoPopup(lang.tr('recipient_errors'));
            
            for(var i=0; i<recipient.errors.length; i++) {
                var error = recipient.errors[i];
                
                var node = $('<div class="error" />').appendTo(popup);
                
                var type = $('<div class="type" />').appendTo(node);
                $('<span class="name" />').appendTo(type).text(lang.tr('error_type') + ' : ');
                $('<span class="value" />').appendTo(type).text(lang.tr('recipient_error_' + error.type));
                
                var date = $('<div class="date" />').appendTo(node);
                $('<span class="name" />').appendTo(date).text(lang.tr('error_date') + ' : ');
                $('<span class="value" />').appendTo(date).text(error.date.formatted);
                
                var details = $('<div class="details" />').appendTo(node);
                $('<span class="name" />').appendTo(details).text(lang.tr('error_details') + ' : ');
                $('<pre class="value" />').appendTo(details).text(error.details);
            }
            
            // Reset popup position as we may have added lengthy content
            filesender.ui.relocatePopup(popup);
        });
    });
    
    // File delete buttons
    $('.transfer_details .file [data-action="delete"]').on('click', function() {
        var file = $(this).closest('.file');
        var id = file.attr('data-id');
        var transfer_details = file.closest('.transfer_details');
        if(!id || isNaN(id)) return;
        
        filesender.ui.confirm(lang.tr('confirm_delete_file'), function() {
            filesender.client.deleteFile(id, function() {
                file.remove();
                if(!transfer_details.find('.files .file').length) {
                    transfer_details.prev('.transfer').remove();
                    transfer_details.remove();
                }
                filesender.ui.notify('success', lang.tr('file_deleted'));
                filesender.ui.updateUserQuotaBar();
            });
        });
    });
    
    // File download buttons when the files are encrypted
    $('.transfer-download').on('click', function () {
        
        if(!filesender.supports.crypto){
            return;
        }
        
        var id = $(this).attr('data-id');
        var encrypted = $(this).attr('data-encrypted');
        var filename = $(this).attr('data-name');
        var mime = $(this).attr('data-mime');
        var key_version = $(this).attr('data-key-version');
        var salt = $(this).attr('data-key-salt');
        var password_version  = $(this).attr('data-password-version');
        var password_encoding = $(this).attr('data-password-encoding');
        var password_hash_iterations = $(this).attr('data-password-hash-iterations');

        if (typeof id == 'string'){
            id = [id];
        }
        window.filesender.crypto_app().decryptDownload(
            filesender.config.base_path + 'download.php?files_ids=' + id.join(','),
            mime, filename, key_version, salt,
            password_version, password_encoding,
            password_hash_iterations
        );

        return false;
    });

    // Add auditlogs triggers
    var auditlogs = function(transfer_id, filter) {
        filesender.client.getTransferAuditlog(transfer_id, function(log) {
            var popup = filesender.ui.wideInfoPopup(lang.tr('auditlog'));
            popup.css('overflow','hidden');
            
            if(!log || !log.length) {
                $('<p />').text(lang.tr('no_auditlog')).appendTo(popup);
                return;
            }
            
            var tbl = $('<table class="list" />').appendTo(popup);
            var th = $('<tr />').appendTo($('<thead />').appendTo(tbl));
            $('<th class="date" />').text(lang.tr('date')).appendTo(th);
            $('<th />').text(lang.tr('action')).appendTo(th);
            $('<th />').text(lang.tr('ip')).appendTo(th);
            
            if(filter) {
                filter = filter.split('/');
                if(filter.length != 3) filter = null;
            }
            
            if(filter) {
                var flt = $('<div class="filtered" />').text(lang.tr('filtered_transfer_log')).prependTo(popup);
                $('<a href="#" />').text(lang.tr('view_full_log')).appendTo(flt).on('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    $(this).closest('.wide_info').find('table tr').show('fast');
                    $(this).closest('.filtered').hide('fast');
                    return false;
                });
            }
            
            var tb = $('<tbody />').appendTo(tbl);
            for(var i=0; i<log.length; i++) {
                var tr = $('<tr />').appendTo(tb);
                
                var filtered = false;
                if(filter) {
                    var v = log[i][filter[0]];
                    if(v && v.type) {
                        if(v.type.toLowerCase() == filter[1]) {
                            if(v.id != filter[2]) filtered = true;
                        } else filtered = true;
                    }
                }
                if(filtered) tr.hide();
                
                $('<td class="date" />').text(log[i].date.formatted).appendTo(tr);
                
                var lid = 'report_event_' + log[i].event;
                
                var rpl = log[i];
                rpl[log[i].target.type.toLowerCase()] = log[i].target;
                
                $('<td />').html(lang.tr(lid).r(rpl).out()).appendTo(tr);
                
                $('<td />').text(log[i].author.ip).appendTo(tr);
                
            }
            
            var actions = $('<div class="actions" />').appendTo(popup);
            
            var send_by_email = $('<a href="#" />').text(lang.tr('send_to_my_email')).appendTo(actions);
            $('<span class="fa fa-lg fa-envelope-o" />').prependTo(send_by_email);
            send_by_email.on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                
                filesender.client.getTransferAuditlogByEmail(transfer_id, function() {
                    filesender.ui.notify('success', lang.tr('email_sent'));
                });
                
                return false;
            }).button();
            
            // Reset popup position as we may have added lengthy content
            filesender.ui.relocatePopup(popup);
        });
    };
    
    $('[data-transfer] .auditlog a').button().on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        auditlogs($(this).closest('tr').attr('data-id'));
        return false;
    });
    
    $('[data-action="auditlog"]').on('click', function(e) {
        auditlogs($(this).closest('tr').attr('data-id'));
    });
    
    $('.transfer_details .recipient [data-action="auditlog"]').on('click', function() {
        auditlogs($(this).closest('tr').attr('data-id'), 'author/recipient/' + $(this).closest('.recipient').attr('data-id'));
    });
    
    $('.transfer_details .file [data-action="auditlog"]').on('click', function() {
        auditlogs($(this).closest('tr').attr('data-id'), 'target/file/' + $(this).closest('.file').attr('data-id'));
    });
    
    // Downloadlinks auto-selection
    $('.transfer_details .download_link input').on('focus', function() {
        $(this).select();
    });
    
    // Do we have a quick open hash ?
    var anchor = window.location.hash.substr(1);
    var match = anchor.match(/^transfer_([0-9]+)$/);
    if(match) $('table.transfers .transfer[data-id="' + match[1] + '"] td.expand span.clickable').click();
});
