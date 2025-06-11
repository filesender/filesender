// JavaScript Document

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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
    var q = $('#page.admin_page .statistics_section .host_quota');
    if(!q.length) return;
    
    var quota = {
        total: parseInt(q.attr('data-total')),
        used: parseInt(q.attr('data-used')),
        available: parseInt(q.attr('data-available'))
    };
    
    var bar = $('<div class="progressbar quota" />').insertAfter(q);
    $('<div class="progress-label" />').appendTo(bar);
    bar.progressbar({
        value: false,
        max: 1000,
        change: function() {
            var bar = $(this);
            var v = bar.progressbar('value');
            
            var classes = [];
            
            var pct = parseInt(v / 10);
            
            var tens = parseInt(pct / 10);
            if(tens) classes.push('quota_' + tens + '0');
            
            if(pct % 10 >= 5) classes.push('quota_plus_5');
            
            bar.find('.progress-label').text((v / 10).toFixed(1) + '%');
            bar.addClass(classes.join(' '));
        },
        complete: function() {
            var bar = $(this);
            bar.find('.progress-label').text(lang.tr('full'));
        }
    });
    
    bar.progressbar('value', Math.floor(1000 * quota.used / quota.total));
    
    var info = lang.tr('quota_usage').r(quota);
    bar.find('.progress-label').text(info);
    
    q.remove();
});
