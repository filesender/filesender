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
    var tables = $('table.transfers');
    if(!tables.length) return;
    
    tables.each(function() {
        var table = $(this);
        if(table.attr('data-processed')) return;
        table.attr('data-processed', '1');
        
        // Expand each transfer's details
        table.find('.transfer .expand span, .transfer span.expand').on('click', function() {
            var el = $(this);
            var tr = el.closest('tr');
            var details = table.find('.transfer_details[data-id="' + tr.attr('data-id') + '"]');
            
            tr.hide('fast');
            details.show('fast');
        });
        
        // Collapse each transfer's details
        table.find('.transfer_details .collapse span').on('click', function() {
            var el = $(this);
            var details = el.closest('tr');
            var tr = table.find('.transfer[data-id="' + details.attr('data-id') + '"]');
            
            details.hide('fast');
            tr.show('fast');
        });
        
        // Expand / retract all
        table.find('thead .expand span').on('click', function() {
            var el = $(this);
            var table = el.closest('table');
            
            var expanded = !el.hasClass('expanded');
            
            table.find('.transfer_details')[expanded ? 'show' : 'hide']('fast');
            table.find('.transfer')[expanded ? 'hide' : 'show']('fast');
            
            el.toggleClass('expanded', expanded).toggleClass('fa-plus-circle', !expanded).toggleClass('fa-minus-circle', expanded);
        });
        
        if(!table.is('[data-status="closed"]')) {
            // Setup action buttons
            table.find('tbody .actions').each(function() {
                var container = $(this);
                
                var id = container.closest('[data-id]').attr('data-id');
                
                var recipients_enabled = ($('.transfer[data-id="' + id + '"]').attr('data-recipients-enabled') == '1');
                
                // Delete button
                $('<span class="delete clickable fa fa-lg fa-trash-o" />').appendTo(container).attr({
                    title: lang.tr('delete')
                }).on('click', function() {
                    var id = $(this).closest('tr').attr('data-id');
                    if(!id || isNaN(id)) return;
                    
                    if(table.is('[data-mode="admin"]')) {
                        var d = filesender.ui.chooseAction(['delete_transfer_nicely', 'delete_transfer_roughly'], function(choosen) {
                            var messages = {
                                delete_nicely: 'transfer_deleted',
                                delete: 'transfer_deleted'
                            };
                            var done = function() {
                                table.find('[data-id="' + id + '"]').remove();
                                filesender.ui.notify('success', lang.tr(messages[choosen]));
                            };
                            
                            switch(choosen) {
                                case 'delete_nicely' : filesender.client.closeTransfer(id, function() {
                                    filesender.client.deleteTransfer(id, done);
                                }); break;
                                case 'delete' : filesender.client.deleteTransfer(id, done); break;
                            }
                        });
                    } else {
                        filesender.ui.confirm(lang.tr('confirm_close_transfer'), function() {
                            filesender.client.closeTransfer(id, function() {
                                table.find('[data-id="' + id + '"]').remove();
                                filesender.ui.notify('success', lang.tr('transfer_closed'));
                            });
                        });
                    }
                });
                
                if(table.is('[data-mode="user"]')) {
                    // Add recipient(s)
                    if(recipients_enabled) $('<span class="add_recipient clickable fa fa-lg fa-envelope-o" />').appendTo(container).attr({
                        title: lang.tr('add_recipient')
                    }).on('click', function() {
                        var id = $(this).closest('tr').attr('data-id');
                        if(!id || isNaN(id)) return;
                        
                        var recipients = [];
                        table.find('.transfer_details[data-id="' + id + '"] .recipients .recipient').each(function() {
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
                    
                    // Send reminder button
                    if(recipients_enabled) $('<span class="remind clickable fa fa-lg fa-repeat" />').appendTo(container).attr({
                        title: lang.tr('send_reminder')
                    }).on('click', function() {
                        var id = $(this).closest('tr').attr('data-id');
                        if(!id || isNaN(id)) return;
                        
                        filesender.ui.confirm(lang.tr('confirm_remind_transfer'), function() {
                            filesender.client.remindTransfer(id, function() {
                                filesender.ui.notify('success', lang.tr('transfer_reminded'));
                            });
                        });
                    });
                }
            });
            
            // Setup buttons in details
            table.find('.transfer_details .recipient').each(function() {
                $('<span class="delete clickable fa fa-lg fa-trash-o" />').appendTo($(this)).attr({
                    title: lang.tr('delete')
                }).on('click', function() {
                    var rcpt = $(this).closest('.recipient');
                    var id = rcpt.attr('data-id');
                    var transfer_details = rcpt.closest('.transfer_details');
                    if(!id || isNaN(id)) return;
                    
                    filesender.ui.confirm(lang.tr('confirm_delete_recipient'), function() {
                        filesender.client.deleteRecipient(id, function() {
                            rcpt.remove();
                            if(!transfer_details.find('.recipients .recipient').length) {
                                transfer_details.prev('.transfer').remove();
                                transfer_details.remove();
                            }
                            filesender.ui.notify('success', lang.tr('recipient_deleted'));
                        });
                    });
                });
            });
            
            table.find('.transfer_details .recipient .errors').each(function() {
                $('<span class="details clickable fa fa-lg fa-info-circle" />').appendTo($(this)).attr({
                    title: lang.tr('details')
                }).on('click', function() {
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
            });
            
            table.find('.transfer_details .file').each(function() {
                $('<span class="delete clickable fa fa-lg fa-trash-o" />').appendTo($(this)).attr({
                    title: lang.tr('delete')
                }).on('click', function() {
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
                        });
                    });
                });
            });
        }
        
        // Add auditlogs triggers
        var auditlogs = function(transfer_id, filter) {
            filesender.client.getTransferAuditlog(transfer_id, function(log) {
                var popup = filesender.ui.wideInfoPopup(lang.tr('auditlog'));
                
                if(!log || !log.length) {
                    $('<p />').text(lang.tr('no_auditlog')).appendTo(popup);
                    return;
                }
                
                var tbl = $('<table class="list" />').appendTo(popup);
                var th = $('<tr />').appendTo($('<thead />').appendTo(tbl));
                $('<th class="date" />').text(lang.tr('date')).appendTo(th);
                $('<th />').text(lang.tr('action')).appendTo(th);
                
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
        
        if(filesender.config.auditlog_lifetime !== null) {
            table.find('.transfer_details td').each(function() {
                var ld = $('<div class="auditlog" />').appendTo(this);
                $('<h2 />').text(lang.tr('auditlog')).appendTo(ld);
                $('<a href="#" />').text(lang.tr('open_auditlog')).appendTo(ld).on('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    auditlogs($(this).closest('tr').attr('data-id'));
                    return false;
                }).prepend('<span class="fa fa-lg fa-history" />').button();
            });
            
            table.find('.transfer_details .recipients .recipient').each(function() {
                $('<span class="clickable fa fa-lg fa-history" />').attr({
                    title: lang.tr('open_recipient_auditlog').out()
                }).appendTo(this).on('click', function() {
                    auditlogs($(this).closest('tr').attr('data-id'), 'author/recipient/' + $(this).closest('.recipient').attr('data-id'));
                });
            });
            
            table.find('.transfer_details .files .file').each(function() {
                $('<span class="clickable fa fa-lg fa-history" />').attr({
                    title: lang.tr('open_file_auditlog').out()
                }).appendTo(this).on('click', function() {
                    auditlogs($(this).closest('tr').attr('data-id'), 'target/file/' + $(this).closest('.file').attr('data-id'));
                });
            });
        }
        
        // Downloadlinks auto-selection
        table.find('.transfer_details .download_link input').on('focus', function() {
            $(this).select();
        });
    });
    
    // Do we have a quick open hash ?
    var anchor = window.location.hash.substr(1);
    var match = anchor.match(/^transfer_([0-9]+)$/);
    if(match) $('table.transfers .transfer[data-id="' + match[1] + '"] td.expand span.clickable').click();
});
