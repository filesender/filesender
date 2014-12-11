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
                
                // Delete button
                $('<span class="delete clickable fa fa-lg fa-trash-o" />').appendTo(container).attr({
                    title: lang.tr('delete')
                }).on('click', function() {
                    var id = $(this).closest('tr').attr('data-id');
                    if(!id || isNaN(id)) return;
                    
                    if(table.is('[data-mode="admin"]')) {
                        var d = filesender.ui.chooseAction(['close_transfer', 'delete_transfer_nicely', 'delete_transfer_roughly'], function(choosen) {
                            var messages = {
                                close: 'transfer_closed',
                                delete_nicely: 'transfer_deleted',
                                delete: 'transfer_deleted'
                            };
                            var done = function() {
                                filesender.ui.alert('success', lang.tr(messages[choosen]), function() {
                                    table.find('[data-id="' + id + '"]').remove();
                                });
                            };
                            
                            switch(choosen) {
                                case 'close' : filesender.client.closeTransfer(id, done); break;
                                case 'delete_nicely' : filesender.client.closeTransfer(id, function() {
                                    filesender.client.deleteTransfer(id, done);
                                }); break;
                                case 'delete' : filesender.client.deleteTransfer(id, done); break;
                            }
                        });
                    } else {
                        filesender.ui.confirm(lang.tr('confirm_close_transfer'), function() {
                            filesender.client.closeTransfer(id, function() {
                                filesender.ui.alert('success', lang.tr('transfer_closed'), function() {
                                    table.find('[data-id="' + id + '"]').remove();
                                });
                            });
                        });
                    }
                });
                
                if(table.is('[data-mode="user"]')) {
                    // Add recipient(s)
                    $('<span class="add_recipient clickable fa fa-lg fa-envelope-o" />').appendTo(container).attr({
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
                            
                            alert(emails.join(', '));
                            
                            return true;
                        })
                        
                        prompt.append('<p>' + lang.tr('email_separator_msg') + '</p>');
                        prompt.append($('<input type="text" />'));
                    });
                    
                    // Send reminder button
                    $('<span class="remind clickable fa fa-lg fa-repeat" />').appendTo(container).attr({
                        title: lang.tr('send_reminder')
                    }).on('click', function() {
                        var id = $(this).closest('tr').attr('data-id');
                        if(!id || isNaN(id)) return;
                        
                        filesender.ui.confirm(lang.tr('confirm_remind_transfer'), function() {
                            // TODO
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
                            filesender.ui.alert('success', lang.tr('recipient_deleted'), function() {
                                rcpt.remove();
                                if(!transfer_details.find('.recipients .recipient').length) {
                                    transfer_details.prev('.transfer').remove();
                                    transfer_details.remove();
                                }
                            });
                        });
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
                            filesender.ui.alert('success', lang.tr('file_deleted'), function() {
                                file.remove();
                                if(!transfer_details.find('.files .file').length) {
                                    transfer_details.prev('.transfer').remove();
                                    transfer_details.remove();
                                }
                            });
                        });
                    });
                });
            });
        }
        
        // Add auditlogs triggers
        var auditlogs = function(transfer_id, filter) {
            filesender.client.getTransferAuditlog(transfer_id, function(log) {
                var popup = filesender.ui.wideInfoPopup('auditlog');
                
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
                    
                    var rpl = {author: log[i].author};
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
                        filesender.ui.alert('success', lang.tr('email_sent'));
                    });
                    
                    return false;
                }).button();
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
    });
});
