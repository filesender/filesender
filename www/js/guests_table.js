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
  var table = guests = $('table.guests');
  if(!guests.length) return;

  // Expand / retract each transfer's details
  guests.find('tr.guest span.expand').on('click', function() {
      var el = $(this);
      var tr = el.closest('tr');

      tr.find('.short, .expand').hide();
      tr.find('.full').show('fast');
  });

  if(!table.is('[data-status="closed"]')) {
      // Setup action buttons
      guests.find('td.actions').each(function() {
          var td = $(this);

          // Delete button
          $('<span class="delete clickable fa fa-lg fa-trash-o" />').appendTo(td).attr({
              title: lang.tr('delete')
          }).on('click', function() {
              var id = $(this).closest('tr').attr('data-id');
              if(!id || isNaN(id)) return;

              filesender.ui.confirm(lang.tr('confirm_delete_guest'), function() {
                  filesender.client.deleteGuest(id, function() {
                      guests.find('[data-id="' + id + '"]').remove();
                      filesender.ui.notify('success', lang.tr('guest_deleted'));
                  });
              });
          });

          if(table.is('[data-mode="user"]')) {
              // Send reminder button
              $('<span class="remind clickable fa fa-lg fa-repeat" />').appendTo(td).attr({
                  title: lang.tr('send_reminder')
              }).on('click', function() {
                  var id = $(this).closest('tr').attr('data-id');
                  if(!id || isNaN(id)) return;

                  filesender.ui.confirm(lang.tr('confirm_remind_guest'), function() {
                      filesender.client.remindGuest(id, function() {
                          filesender.ui.notify('success', lang.tr('guest_reminded'));
                      });
                  });
              });

              // Send forward button
              $('<span class="forward clickable fa fa-lg fa-mail-forward" />').appendTo(td).attr({
                  title: lang.tr('forward')
              }).on('click', function() {
                  var id = $(this).closest('tr').attr('data-id');
                  if(!id || isNaN(id)) return;

                  var input = $('<input name="recipients" />').attr({
                       placeholder: lang.tr('enter_to_email')
                  });

                  var dialog = filesender.ui.prompt(lang.tr('forward_guest_voucher'), function() {
                      var value = input.val();
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
                  }).append('<label for="recipients">' + lang.tr('recipients') + '</label>').append(input);

                  input.on('keydown', function(e) {
                      if(e.keyCode != 13) return;

                      // enter is pressed
                      e.preventDefault();
                      e.stopPropagation();

                      dialog.dialog('option', 'buttons').ok.click();
                  });

                  input.focus();
              });
          }
      });
  }

  // Errors details
  guests.find('.guest[data-errors="1"] .to .errors').each(function() {
      $('<span class="details clickable fa fa-lg fa-info-circle" />').appendTo($(this)).attr({
          title: lang.tr('details')
      }).on('click', function() {
          var rcpt = $(this).closest('.guest');
          var id = rcpt.attr('data-id');
          if(!id || isNaN(id)) return;

          filesender.client.getGuest(id, function(guest) {
              var popup = filesender.ui.wideInfoPopup(lang.tr('recipient_errors'));

              for(var i=0; i<guest.errors.length; i++) {
                  var error = guest.errors[i];

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
});
