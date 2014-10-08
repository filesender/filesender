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
    var form = $('.admin_page #config_form');
    if(!form.length) return;
    
    // Remove default state if value changes
    form.find(':input').on('change', function() {
        var p = $(this).closest('.parameter');
        
        p.attr('data-is-default', '');
    });
    
    // Reset default status
    form.find('.make_default').on('click', function() {
        var p = $(this).closest('.parameter');
        
        p.attr('data-is-default', '1');
        
        var i = p.find(':input');
        
        if(i.is(':checkbox')) {
            i.removeAttr('checked');
            i.prop('checked', p.attr('data-default') == '1');
        } else i.val(p.attr('data-default'));
    });
    
    // Save overrides
    form.find('.save').button().on('click', function() {
        var overrides = {};
        
        form.find('.parameter').each(function() {
            var p = $(this);
            var key = p.attr('data-key');
            var i = p.find(':input');
            
            var value;
            
            if(p.attr('data-is-default') == '1') {
                value = null;
            } else if(i.is(':checkbox')) {
                value = i.is(':checked');
            } else value = i.val();
            
            overrides[key] = value;
        });
        
        filesender.client.overrideConfig(overrides, function() {
            filesender.ui.alert('success', lang.tr('config_overriden'));
        });
        
        return false;
    });
});
