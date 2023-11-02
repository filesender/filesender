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

    // Send reminder button
    $('.remind').on('click', function() {
        var id = $('.fs-invitation-detail').attr('data-id');
        if(!id || isNaN(id)) return;

        filesender.ui.confirm(lang.tr('confirm_remind_guest'), function() {
            filesender.client.remindGuest(id, function() {
                filesender.ui.notify('success', lang.tr('guest_reminded'));
            });
        });
    });

    // Send forward button
    $('.forward').on('click', function() {
        var id = $('.fs-invitation-detail').attr('data-id');
        if(!id || isNaN(id)) return;

        var input = $('<input name="recipients" />').attr({
            placeholder: lang.tr('enter_to_email')
        });

        var dialog = filesender.ui.promptEmail(lang.tr('forward_guest_voucher'), function(value) {
            var emails = value.match(/[,;\s]/) ? value.split(/[,;\s]/) : [value];

            var h = {};
            var invalid = false;
            for(var i=0; i<emails.length; i++) {
                if(typeof h[emails[i]] != 'undefined') continue; // Duplicate

                h[emails[i]] = true;

                if(!emails[i].match(filesender.ui.validators.email))
                    invalid = true;
            }

            emails = [];
            for(var e in h) emails.push(e);
            if(!emails.length) invalid = true;

            input.val(emails.join(', '));

            var marker = input.data('error_marker');

            if(invalid) {
                input.addClass('invalid');
                if(!marker) {
                    marker = $('<span class="invalid fa fa-exclamation-circle fa-lg" />').attr({
                        title: lang.tr('invalid_recipient')
                    });
                    input.data('error_marker', marker);
                }
                marker.insertBefore(input);
            }else{
                input.removeClass('invalid');
                if(marker) marker.remove();
            }

            if(invalid) return false;

            filesender.client.getGuest(id, function(gv) {
                var forwarded = {from: gv.user_email, subject: gv.subject, message: gv.message, expires: gv.expires.raw, options: gv.options};

                var sent = 0;
                for(var i=0; i<emails.length; i++) {
                    filesender.client.postGuest(gv.user_email, emails[i], gv.subject, gv.message, gv.expires.raw, gv.options, function() {
                        sent++;
                        if(sent < emails.length) return;

                        filesender.ui.notify('success', lang.tr('guest_vouchers_sent').r({sent: sent}), function() {
                            filesender.ui.reload();
                        });
                    });
                }
            });

            return true;
        });

        input.on('keydown', function(e) {
            if(e.keyCode != 13) return;

            // enter is pressed
            e.preventDefault();
            e.stopPropagation();

            dialog.dialog('option', 'buttons').ok.click();
        });

        input.focus();
    });


    // Delete button
    $('.delete').on('click', function() {
        var id = $('.fs-invitation-detail').attr('data-id');
        if(!id || isNaN(id)) return;

        filesender.ui.confirm(lang.tr('confirm_delete_guest'), function() {
            filesender.client.deleteGuest(id, function() {
                filesender.ui.notify('success', lang.tr('guest_deleted'));
                window.location.href = '?s=guests';
            });
        });
    });
});
