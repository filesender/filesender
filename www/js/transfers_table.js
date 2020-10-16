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

class FabriqueTransferList {
  constructor() {
    this.setDefaultUI();
  }

  showTable() {
    $('.transfers').addClass('active');
  }

  setDefaultUI() {
    this.transferDetails = $('<div id="transferDetails"></div>').hide();

    $('body').append(this.transferDetails);

    $('.actions').remove();//this causes layout problems otherwise
    $('.transfer_id').hide();
    $('.expand').hide();
    $('.collapse').hide();
    $('.auditlog').hide();
    $('.actions').hide();
    $('.general').prev().addClass('detail_popup_title').hide();
    $('.auditlog').next().addClass('detail_fileslist_title');
    $('.subheader').hide();
    $('.options').parent().parent().hide();

    // Move pagination and hide initially
    this.previousPage = $('.pageprev');
    this.firstPage = $('.pageprev0');
    this.nextPage = $('.pagenext');
    this.paginationWrapper = this.previousPage.parent().detach();
    $('.nextColumn').append(this.paginationWrapper);

    // Change layout
    this.setTableTitles();
    this.addListenerToRows();
    this.changeFileColumnContents();
    this.changeEmailColumnContents();
    this.changeDownloadColumnContents();
    this.changeExpiryDateColumnContents();
    this.setBottomNavigation();

    // Set back click on everything except the detail popup div
    var self = this;
    $('#transferDetails').click(function(e){
        if( e.target !== this) return;
        self.hideTransferDetails();
    });

    window.setTimeout(this.showTable, 1500);
  }

  hideTransferDetails() {
    $('#transferDetails').hide();
    $('#transferDetails').removeClass('popup');
  }

  setTableTitles() {
      $('[data-status=available]').before(`<h2>${lang.tr('available_transfers')}</h2>`);
      $('[data-status=closed]').before(`<h2>${lang.tr('closed_transfers')}</h2>`);
  }

  changeEmailColumnContents() {
    $('.recipients').each(function(i){

        if(!$(this).is('td')) return; // so we dont get the ones in the popup with the same class

        const column = $(this);
        let emailString = '';
        let otherRecipientsCount = 0;

        // iterate over the files in the column
        if (column.children().length > 0) {
            const currentElement = column.children().first();

            if(currentElement.is('a')) {
                emailString += currentElement.text();

                const transferId = column.parent().attr('data-id');

                // Get the info from the collapsed field since that has all the other recipient info and the table only shows max 3
                const extraInfo = $('.transfer_details[data-id="' + transferId + '"]');
                otherRecipientsCount = extraInfo.find('.recipient').length - 1;
            }
            else if(currentElement.is('abbr')) {
                emailString = lang.tr('download_link');
            }

            let moreString = otherRecipientsCount ? (' + ' + otherRecipientsCount + ' ' + lang.tr('more')) : '';
            emailString += moreString;
            column.text(emailString);
        }
    });
  }

  changeFileColumnContents() {
      $('.files').each(function(i){

          if(!$(this).is('td')) return;

          const column = $(this);
          let fileString = '';
          let otherFileCount = 0;

          // iterate over the files in the column
          if (column.children().length > 0) {
            column.children().each(function(i) {
                const currentElement = $(this);
                if(currentElement.is('span')) {
                    if(i == 0) {
                        fileString += currentElement.text();
                    }
                    else {
                        otherFileCount += 1;
                    }
                }
            });

            let moreString = otherFileCount ? (' + ' + otherFileCount + ' ' + lang.tr('more')) : '';
            fileString += moreString;
            column.text(fileString);
          }
      });
  }

  changeDownloadColumnContents() {
    $('.downloads').each(function(i){
        if(!$(this).is('td')) return;
        const column = $(this);
        const dlCount = column.html().toString().trim().split(' ')[0];
        column.html(dlCount + ' ' + lang.tr('downloads'));
    });
  }

  changeExpiryDateColumnContents() {
    $('.expires').each(function(i){
        if(!$(this).is('td')) return;
        const column = $(this);
        const date = column.text();
        column.html(lang.tr('Expires') + ' ' + date);
    });
  }

  addListenerToRows() {
      const that = this;
      $('.transfer').each(function(i){
        $(this).click(function(){
            const id = $(this).attr('data-id');
            return that.toggleTransferDetails(id);
        });
      });
  }

  addRecipient(transferId, data) {
      var id = transferId;
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
  }

  deleteTransfer(transferId, data) {
    var id = transferId;
    if(!id || isNaN(id)) return;

    self = this;

    if($('.transfers list').filter('[data-mode="admin"][data-status="available"]')) {
        var d = filesender.ui.chooseAction(['delete_transfer_nicely', 'delete_transfer_roughly'], function(choosen) {
            var done = function() {
                $('[data-transfer][data-id="' + id + '"]').remove();
                self.hideTransferDetails();
                filesender.ui.notify("success", lang.tr('ui2_transfer_deleted'));
                filesender.ui.reload();
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
  }

  extendTransfer(transferId, data) {

    var t = $('#popup_wrapper').find('.transfer_details');

    var id = t.attr('data-id');
    if(!id || isNaN(id)) {
        filesender.ui.notify("error", lang.tr('ui2_transfer_extend_error'));
        return;
    }

    var duration = parseInt(t.attr('data-expiry-extension'));
    if(!duration || duration <= 0) {
        filesender.ui.notify("error", lang.tr('ui2_transfer_extend_exhausted'));
        return;
    }

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
  }

  buildAuditlog(transferId) {
    // Re add log
    var auditlog = $('#popup_wrapper').find('.auditlog')

    $('#popup_wrapper').find('.general').first().append(auditlog.detach());
    auditlog.children().first().hide();
    auditlog.show();

    var title = auditlog.children().eq(1);
    title.addClass('seeTransferLogs');
    title.removeClass('ui-corner-all ui-widget ui-button');
    title.on('click', function(e){
        var logToggleDiv = $(`<div class="transferLogsDiv">
                                <div class="detail_fileslist_title title-popup-transferlog"><h2>${lang.tr('Transfer logs')}</h2></span>
                                <table id="transferLogTable">
                                </table>
                              </div>`).hide();

        $('#popup_wrapper').find('.general').first().append(logToggleDiv);

        filesender.client.getTransferAuditlog(transferId, function(log) {
            // Reset so it doesnt have entries from another transfer
            $('#transferLogTable').html('');
            log.forEach(function(entry){
                $('#transferLogTable').append(`<tr class="transferLogTableRow">
                                                    <td class="date">${ entry.date.formatted }</td>
                                                    <td class="author">${ entry.author.identity }</td>
                                                    <td class="author">${ lang.tr(entry.event) }</td>
                                                </tr>`)
            });
            logToggleDiv.show();
        });
    });
  }

  toggleTransferDetails(transferId) {
    var self = this;

    // Create popup, a lot of logic is dependent on the data attributes of the surrounding TR, so we copy that too
    const data = document.querySelector('.transfer_details[data-id="' + transferId + '"]').innerHTML;
    const tableRowData = $('.transfer_details[data-id="' + transferId + '"]').clone().empty();


    this.transferDetails.append('<div id="popup_wrapper"/>');
    $('#popup_wrapper').html(data);
    $('#popup_wrapper').append(tableRowData);

    // Add "add recipient" button, but only if it was an emailed transfer
    if($('#popup_wrapper').find('.recipient').length > 0) {
        var addRecipientButton = $(`<span id="addRecipient">+ ${lang.tr('add recipient')}</span>`);
        $('#popup_wrapper').find('.recipients').first().append(addRecipientButton);
        addRecipientButton.on('click', function(e) { self.addRecipient(transferId, data) });
    }

    // Create headings
    $('#popup_wrapper').find('.general').first().prepend(`<div class="detail_fileslist_title title-popup-details"><h2>${lang.tr('details')}</h2></span>`);
    $('#popup_wrapper').find('.general').first().append('<div class="detail_fileslist_title title-popup-options"></span>');

    // Add delete button
    var deleteTransferButton = $(`<div><span id="deleteTransfer">${lang.tr('delete_transfer')}</span></div>`);
    $('#popup_wrapper').find('.general').first().append(deleteTransferButton);
    deleteTransferButton.on('click', function(e) { self.deleteTransfer(transferId, data) });

    // Add extend button
    var extendTransferButton = $(`<div><span id="extendTransfer">${lang.tr('extend_transfer')}</span></div>`);
    $('#popup_wrapper').find('.general').first().append(extendTransferButton);
    extendTransferButton.on('click', function(e) { self.extendTransfer(transferId, data) });

    // Build contents of transfer log
    this.buildAuditlog(transferId);

    // Download count and delete button in filelist
    $('.file').each(function(e) {
        var current = $(this);
        var elements = current.text().split(':')

        if (elements.length > 1) {
            current.html('<span class="popupList_fileName">' + elements[0] + '</span> <span class="popupList_downloadCount">' + elements[1] + '</span>' + '<span id="deleteTransferFile"></span>');
        }

        current.find('#deleteTransferFile').on('click', function(e){
            var file = $(this).closest('.file');
            var id = file.attr('data-id');
            if(!id || isNaN(id)) return;

            filesender.ui.confirm(lang.tr('confirm_delete_file'), function() {
                filesender.client.deleteFile(id, function() {
                    file.remove();
                    if(!current.find('.files .file').length) {
                        filesender.ui.notify("success", lang.tr('ui2_all_files_deleted'));
                        self.hideTransferDetails();
                        filesender.ui.reload();
                    }
                    filesender.ui.updateUserQuotaBar();
                });
            });
        });
    });

    // Edit recipient list to also show
    $('.recipient').each(function(e) {
        var current = $(this);
        var elements = current.text().split(':')

        if (elements.length > 1) {
            current.html('<span class="popupList_fileName">' + elements[0] + '</span> <span class="popupList_downloadCount">' + elements[1] + '</span>' + '<span id="remindRecipient">send reminder</span> <span id="deleteRecipient"></span>');
        }

        current.find('#remindRecipient').on('click', function(e){
            var rcpt = $(this).closest('.recipient');
            var id = rcpt.attr('data-id');
            if(!id || isNaN(id)) return;

            filesender.ui.confirm(lang.tr('confirm_remind_recipient'), function() {
                filesender.client.remindRecipient(id, function() {
                    filesender.ui.notify('success', lang.tr('recipient_reminded'));
                });
            });
        });

        current.find('#deleteRecipient').on('click', function(e){
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
                        self.hideTransferDetails();
                        filesender.ui.notify("success", lang.tr('ui2_all_recipients_deleted'));
                        filesender.ui.reload();
                    }
                    filesender.ui.notify('success', lang.tr('recipient_deleted'));
                });
            });
        });

    });

    // Download link opens in new window
    this.transferDetails.find('.download_link').first().children().first().children().first().attr('target', '_blank');

    // Show popup now that we've set the layout elements
    this.transferDetails.show();
    this.transferDetails.addClass('popup');
  }

  setBottomNavigation(transferCount, transferCountInactive) {
      const navs = $('.pager_bottom_nav');

      const transferListActive = $(navs[0]).children().first();

      if (!transferListActive.length) return;

      if (transferListActive.html().toString().includes('No more records')) {
        transferListActive.html(`<span id="nav_no_more" class="nav_option">${lang.tr('ui2_no_active_transfers')}</span>`);
      }
      else {
        transferListActive.html(`<span id="nav_active_show_more" class="nav_option">${lang.tr('ui2_show_more')}</span> <span id="nav_active_show_all" class="nav_option">${lang.tr('ui2_show_all')}</span>`)
      }

      // Set onclick
      $('#nav_active_show_more').click(function(){
        var paramSplit = window.location.href.split('openlimit=');
        var previousCount = paramSplit.length > 1 ? parseInt(paramSplit.pop()) : 10;
        window.location = '/?s=transfers&openlimit=' + (previousCount += 10);
      });

      $('#nav_active_show_all').click(function(){
        window.location = '/?s=transfers&openlimit=999999';
      });

      const transferListInactive = $(navs[1]).children().first();

      if (!transferListInactive.length) return;
      if (transferListInactive.html().toString().includes('No more records')) {
        transferListInactive.html(`<span id="nav_no_more" class="nav_option">${lang.tr('ui2_no_inactive_transfers')}</span>`);
      }
      else {
        transferListInactive.html(`<span id="nav_inactive_show_more" class="nav_option">${lang.tr('Show more')}</span> <span id="nav_inactive_show_all" class="nav_option">${lang.tr('Show all')}</span>`);
      }

      // Set onclick
      $('#nav_inactive_show_more').click(function(){
        var paramSplit = window.location.href.split('closedlimit=');
        var previousCount = paramSplit.length > 1 ? parseInt(paramSplit.pop()) : 10;
        window.location = '/?s=transfers&closedlimit=' + (previousCount += 10);
      });

      $('#nav_inactive_show_all').click(function(){
        window.location = '/?s=transfers&closedlimit=999999';
      });
  }
}

$(function() {
  if(window.transfers_table) return;
  window.transfers_table = true;
  var FabriqueTL = new FabriqueTransferList();

  // Expand each transfer's details
//   $('.transfer .expand span, .transfer span.expand').on('click', function() {
//       var el = $(this);
//       var tr = el.closest('tr');
//       console.log(tr.attr('data-id'));
//       var details = el.closest('table').find('.transfer_details[data-id="' + tr.attr('data-id') + '"]');
//       FabriqueTL.toggleTransferDetails(tr.attr('data-id'));

//       // tr.hide('fast');
//       // details.show('fast');
//   });

  // Collapse each transfer's details
//   $('.transfer_details .collapse span').on('click', function() {
//       var el = $(this);
//       var details = el.closest('tr');
//       var tr = el.closest('table').find('.transfer[data-id="' + details.attr('data-id') + '"]');

//       details.hide('fast');
//       tr.show('fast');
//   });

  // Expand / retract all
//   $('thead .expand span').on('click', function() {
//       var el = $(this);
//       var table = el.closest('table');

//       var expanded = !el.hasClass('expanded');

//       table.find('.transfer_details')[expanded ? 'show' : 'hide']('fast');
//       table.find('.transfer')[expanded ? 'hide' : 'show']('fast');

//       el.toggleClass('expanded', expanded).toggleClass('fa-plus-circle', !expanded).toggleClass('fa-minus-circle', expanded);
//   });

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

      if (typeof id == 'string'){
          id = [id];
      }
      window.filesender.crypto_app().decryptDownload(
          filesender.config.base_path + 'download.php?files_ids=' + id.join(','),
          mime, filename, key_version, salt );

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
