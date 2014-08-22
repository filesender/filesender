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
    var transfers = $('table.transfers');
    if(!transfers.length) return;
    
    // Expand / retract each transfer's details
    transfers.find('tr.transfer td.expand span, tr.transfer span.expand').on('click', function() {
        var el = $(this);
        var tr = el.closest('tr');
        var details = tr.next('tr.transfer_details[data-id="' + tr.attr('data-id') + '"]');
        
        var expanded = el.toggleClass('expanded').hasClass('expanded');
        
        tr.find('td:not(.expand, .actions)').css('visibility', expanded ? 'hidden' : 'visible');
        details[expanded ? 'show' : 'hide']('fast');
        
        el.filter('.fa').toggleClass('fa-plus-circle', !expanded).toggleClass('fa-minus-circle', expanded);
        
        if(el.is('span.expand'))
            $('body').scrollTo($('tr.transfer_details .' + el.closest('td').className).position().top);
    });
    
    // Expand / retract all
    transfers.find('tr th.expand span').on('click', function() {
        var el = $(this);
        var table = el.closest('table');
        var details = table.find('.transfer_details');
        var expands = table.find('tr.transfer td.expand span');
        expands.push(this);
        
        var expanded = el.toggleClass('expanded').hasClass('expanded');
        
        table.find('.transfer td:not(.expand, .actions)').css('visibility', expanded ? 'hidden' : 'visible');
        details[expanded ? 'show' : 'hide']('fast');
        
        expands.toggleClass('fa-plus-circle', !expanded).toggleClass('fa-minus-circle', expanded);
    });
    
    // Setup action buttons
    transfers.find('td.actions').each(function() {
        var td = $(this);
        
        // Delete button
        $('<span class="delete clickable fa fa-lg fa-trash-o" />').appendTo(td).attr({
            title: lang.tr('delete')
        }).on('click', function() {
            var id = $(this).closest('tr').attr('data-id');
            if(!id || isNaN(id)) return;
            
            filesender.ui.confirm(lang.tr('confirm_delete_transfer'), function() {
                filesender.client.deleteTransfer(id, function() {
                    filesender.ui.alert('success', lang.tr('transfer_deleted'), function() {
                        transfers.find('[data-id="' + id + '"]').remove();
                    });
                });
            });
        });
        
        // Send reminder button
        $('<span class="remind clickable fa fa-lg fa-repeat" />').appendTo(td).attr({
            title: lang.tr('send_reminder')
        }).on('click', function() {
            var id = $(this).closest('tr').attr('data-id');
            if(!id || isNaN(id)) return;
            
            filesender.ui.confirm(lang.tr('confirm_remind_transfer'), function() {
                // TODO
            });
        });
    });
    
    // Setup buttons in details
    transfers.find('.transfer_details .recipient').each(function() {
        $('<span class="delete clickable fa fa-lg fa-trash-o" />').appendTo($(this)).attr({
            title: lang.tr('delete')
        }).on('click', function() {
            var id = $(this).closest('.recipient').attr('data-id');
            if(!id || isNaN(id)) return;
            
            filesender.ui.confirm(lang.tr('confirm_delete_recipient'), function() {
                // TODO
            });
        });
    });
    
    transfers.find('.transfer_details .file').each(function() {
        $('<span class="delete clickable fa fa-lg fa-trash-o" />').appendTo($(this)).attr({
            title: lang.tr('delete')
        }).on('click', function() {
            var id = $(this).closest('.file').attr('data-id');
            if(!id || isNaN(id)) return;
            
            filesender.ui.confirm(lang.tr('confirm_delete_file'), function() {
                // TODO
            });
        });
    });
});
