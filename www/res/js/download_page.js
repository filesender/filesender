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
    var page = $('.download_page');
    if(!page.length) return;
    
    // Bind file selectors
    page.find('.file .select').on('click', function() {
        var el = $(this);
        var f = el.closest('.file');
        
        var selected = f.attr('data-selected') == '1';
        selected = !selected;
        f.attr('data-selected', selected ? '1' : '0');
        
        el.toggleClass('fa-square-o', !selected).toggleClass('fa-check-square-o', selected);
    });
    
    // Bind global selector
    page.find('.select_all .select').on('click', function() {
        var el = $(this);
        
        var selected = el.find('.fa').hasClass('fa-check-square-o');
        selected = !selected;
        
        var files = page.find('.file');
        files.attr('data-selected', selected ? '1' : '0');
        
        var selectors = page.find('.file .select');
        selectors.push(el.find('.fa'));
        selectors.toggleClass('fa-square-o', !selected).toggleClass('fa-check-square-o', selected);
    });
    
    // Get recipient token
    var m = window.location.search.match(/token=([0-9a-f-]+)/);
    var token = m[1];
    
    var dl = function(ids) {
        if(typeof ids == 'string') ids = [ids];
        
        var dlcb = function(notify) {
            notify = notify ? '&notify_upon_completion=1' : '';
            return function() {
                filesender.ui.redirect(filesender.config.base_path + 'download.php?token=' + token + '&files_ids=' + ids.join(',') + notify);
            };
        };
        
        filesender.ui.confirm('confirm_download_notify', dlcb(true), dlcb(false), true);
    };
    
    // Bind download buttons
    page.find('.file .download').button().on('click', function() {
        var id = $(this).closest('.file').attr('data-id');
        
        dl(id);
        
        return false;
    });
    
    // Bind archive download button
    page.find('.archive .archive_download').button().on('click', function() {
        var ids = [];
        page.find('.file[data-selected="1"]').each(function() {
            ids.push($(this).attr('data-id'));
        });
        
        if(!ids.length) { // No files selected, supose we want all of them
            page.find('.file').each(function() {
                ids.push($(this).attr('data-id'));
            });
        }
        
        dl(ids);
        
        return false;
    });
});
