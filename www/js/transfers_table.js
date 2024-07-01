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

    window.filesender.pbkdf2dialog.setup( true );


    // Transfer delete buttons
    $('.actions [data-action="delete"]').on('click', function(event) {
        var id = $(this).closest('[data-transfer]').attr('data-id');
        if(!id || isNaN(id)) return;
        event.stopPropagation();
        
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
    }).on('click', function(event) {
        event.stopPropagation();
        filesender.ui.extendExpires( $(this), 'transfer');
    });

    $('[data-expiry-extension][data-expiry-extension!="-1"] [data-action="extendexpires"]').each(function() {
        $(this).attr({
            title: lang.tr('extend_expiry_date').r({
                days: $(this).closest('[data-transfer]').attr('data-expiry-extension')
            })
        });
    }).on('click', function(event) {
        event.stopPropagation();
        filesender.ui.extendExpires( $(this), 'transfer');
    });



    // Add recipient buttons
    $('[data-recipients-enabled=""] [data-action="add_recipient"]').addClass('disabled');

    $('[data-recipients-enabled="1"] [data-action="add_recipient"]').on('click', function(event) {
        var id = $(this).closest('[data-transfer]').attr('data-id');
        if(!id || isNaN(id)) return;
        event.stopPropagation();

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

        })

        prompt.append('<p>' + lang.tr('email_separator_msg') + '</p>');
    });

    // Remind buttons
    $('[data-recipients-enabled=""] .actions [data-action="remind"]').addClass('disabled');

    $('[data-recipients-enabled="1"] .actions [data-action="remind"]').on('click', function(event) {
        var id = $(this).closest('[data-transfer]').attr('data-id');
        if(!id || isNaN(id)) return;
        event.stopPropagation();

        filesender.ui.confirm(lang.tr('confirm_remind_transfer'), function() {
            filesender.client.remindTransfer(id, function() {
                filesender.ui.notify('success', lang.tr('transfer_reminded'));
            });
        });
    });


    // Transfer options
    $('.transfer_options [data-action="remove"]').on('click', function(event) {
        var id = $(this).closest('[data-transfer]').attr('data-id');
        if(!id || isNaN(id)) return;
        event.stopPropagation();

        filesender.ui.confirm(lang.tr('confirm_remove_daily_stats_transfer'), function() {
            filesender.client.removeTransferOption(id, 'email_daily_statistics', function() {
                filesender.ui.notify('success', lang.tr('transfer_option_modified'));
            });
        });
    });


    // File download buttons when the files are encrypted
    $('.transfer-download').on('click', function (event) {

        if(!filesender.supports.crypto){
            return;
        }
        event.stopPropagation();

        var transferid = $(this).attr('data-transferid');
        var id = $(this).attr('data-id');
        var encrypted = $(this).attr('data-encrypted');
        var filename = $(this).attr('data-name');
        var filesize = $(this).attr('data-size');
        var encrypted_filesize = $(this).attr('data-encrypted-size');
        var mime = $(this).attr('data-mime');
        var key_version = $(this).attr('data-key-version');
        var salt = $(this).attr('data-key-salt');
        var password_version  = $(this).attr('data-password-version');
        var password_encoding = $(this).attr('data-password-encoding');
        var password_hash_iterations = $(this).attr('data-password-hash-iterations');
        var client_entropy = $(this).attr('data-client-entropy');
        var fileiv = $(this).attr('data-fileiv');
        var fileaead = $(this).attr('data-fileaead');
        if( fileaead.length ) {
            fileaead = atob(fileaead);
        }

        if (typeof id == 'string'){
            id = [id];
        }

        window.filesender.crypto_app().decryptDownload(
            filesender.config.base_path + 'download.php?files_ids=' + id.join(','),
            transferid,
            mime, filename,
            filesize, encrypted_filesize,
            key_version, salt,
            password_version, password_encoding,
            password_hash_iterations,
            client_entropy,
            window.filesender.crypto_app().decodeCryptoFileIV(fileiv,key_version),
            fileaead
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

    $('[data-transfer] .auditlog a').button().on('click', function(e) {
        e.stopPropagation();
        auditlogs($(this).closest('tr').attr('data-id'));
    });


    $('[data-action="auditlog"]').not('.transfer_details .file [data-action="auditlog"]').on('click', function(e) {
        e.stopPropagation();
        auditlogs($(this).closest('tr').attr('data-id'));
    });


    $('.fs-table__row--clickable').on('click', function() {
        const transfer_id = $(this).data("id");

        if (transfer_id) {
            window.location.href = `?s=transfer_detail&transfer_id=${transfer_id}`;
        }
    });

    // Do we have a quick open hash ?
    var anchor = window.location.hash.substr(1);
    var match = anchor.match(/^transfer_([0-9]+)$/);
    if(match) $('table.transfers .transfer[data-id="' + match[1] + '"] td.expand span.clickable').click();
});
