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

    // Transfer delete buttons
    $('.fs-transfer-detail [data-action="delete"]').on('click', function() {
        var id = $(this).closest('.fs-transfer-detail').attr('data-id');
        if(!id || isNaN(id)) return;

        console.log("BBB delete");
        if($(this).closest('table').is('[data-mode="user"][data-status="available"]')) {
            console.log("BBB delete2");
            var d = filesender.ui.chooseAction(['delete_transfer_nicely', 'delete_transfer_roughly'], function(choosen) {
                var done = function() {
                    $('[data-transfer][data-id="' + id + '"]').remove();
                    filesender.ui.notify('success', lang.tr('transfer_deleted'));
                };

//                console.log(" chosen ", choosen );
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
        } else if($(this).closest('table').is('[data-mode="admin"][data-status="uploading"]')) {
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
                    $('.fs-transfer-detail').attr("data-status", "closed");
                    filesender.ui.notify('success', lang.tr('transfer_closed'));
                    filesender.ui.updateUserQuotaBar();
                });
            });
        }
    });

    // File delete buttons
    $('.transfer_detail_page .file [data-action="delete"]').on('click', function() {
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

            var filterid = null;
            var filtered = false;

            if(filter) {
                var flt = $('<div class="filtered" />').text(lang.tr('filtered_transfer_log')).prependTo(popup);
                $('<a href="#" />').text(lang.tr('view_full_log')).appendTo(flt).on('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    $(this).closest('.wide_info').find('table tr').show('fast');
                    $(this).closest('.filtered').hide('fast');
                    filtered = false;
                    filterid = null;
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
                            filterid = filter[2];
                            if(v.id != filter[2]) filtered = true;
                        } else filtered = true;
                    }
                }
                if(filtered) tr.hide();

                $('<td class="date" />').text(log[i].date.formatted).appendTo(tr);

                var lid = 'report_event_' + log[i].event;

                var rpl = log[i];
                var ttlc = log[i].target.type.toLowerCase();
                rpl[ttlc] = log[i].target;
                if( rpl[ttlc]['name'] && !rpl[ttlc]['path'] ) {
                    rpl[ttlc]['path'] = rpl[ttlc]['name'];
                }

                $('<td />').html(lang.tr(lid).r(rpl).out()).appendTo(tr);

                $('<td />').text(log[i].author.ip).appendTo(tr);

            }

            var actions = $('<div class="actions" />').appendTo(popup);

            var send_by_email = $('<a href="#" class="btn btn-secondary" />').text(' ' + lang.tr('send_to_my_email')).appendTo(actions);
            $('<span class="fa fa-lg fa-envelope-o" />').prependTo(send_by_email);
            send_by_email.on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();

                filesender.client.getTransferAuditlogByEmail(transfer_id, filterid, function() {
                    filesender.ui.notify('success', lang.tr('email_sent'));
                });

                return false;
            });

            // Reset popup position as we may have added lengthy content
            filesender.ui.relocatePopup(popup);
        });
    };

    $('.fs-transfer-detail__files span[data-action="auditlog"]').on('click', function(e) {
        auditlogs($(this).closest('tr').attr('data-id'));
    });

    $('.fs-transfer-detail__actions button[data-action="auditlog"]').on('click', function(e) {
        auditlogs($(this).closest('.fs-transfer-detail').attr('data-id'));
    });

    $('.fs-transfer-detail__actions  button[data-action="extend"]').on('click', function() {
        filesender.ui.extendExpires( $(this), 'transfer');
    });    

    // Add recipient buttons
    $('[data-recipients-enabled=""] [data-action="add_recipient"]').addClass('disabled');

    $('[data-recipients-enabled="1"] [data-action="add_recipient"]').on('click', function() {
        var id = $(this).closest('.fs-transfer-detail').attr('data-id');
        if(!id || isNaN(id)) return;

        var recipients = [];
        $('.transfer_details[data-id="' + id + '"] .recipients .recipient').each(function() {
            recipients.push($(this).attr('data-email'));
        });

        var prompt = filesender.ui.promptEmail(lang.tr('enter_to_email'), function(input) {
            $('p.error', this).remove();

            var raw_emails = input.split(/[,;]/);

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
                console.log(errors);
                for(var i=0; i<errors.length; i++)
                    $('<p class="error message" />').text(errors[i].out()).appendTo(prompt);
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
    });

    // Remind buttons
    $('[data-recipients-enabled=""] [data-action="remind"]').addClass('disabled');

    $('[data-recipients-enabled="1"] [data-action="remind"]').on('click', function() {
        var id = $(this).closest('.fs-transfer-detail').attr('data-id');
        if(!id || isNaN(id)) return;

        filesender.ui.confirm(lang.tr('confirm_remind_transfer'), function() {
            filesender.client.remindTransfer(id, function() {
                filesender.ui.notify('success', lang.tr('transfer_reminded'));
            });
        });
    });

    // Copy download link
    const copyToClipboard = (value) => {
        navigator.clipboard.writeText(value).then((x) => {
            filesender.ui.notify('info', 'Copied to clipboard!');
        }).catch((e) => {
            console.error(e);
            filesender.ui.notify('error', 'Error copying to clipboard!');
        });
    }

    $('#copy-to-clipboard').on('click', function(e) {
        const element = this.parentElement.querySelector('span');
        if (element) {
            const value = element.textContent;
            copyToClipboard(value);
        }
    });

    if( filesender.config.make_download_links_clickable ) {
        $('.download_link').on('click', function() {
            filesender.ui.redirect($(this).text());
        });
    }
    
});
