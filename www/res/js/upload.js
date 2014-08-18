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

// Manage files
filesender.ui.files = {
    list: [],
    
    // Test if file is duplicate
    isDuplicate: function(file) {
        for(var i=0; i<this.list.length; i++)
            if(
                (this.list[i].blob.name == file.name) &&
                (this.list[i].blob.size == file.size)
            ) return true;
        
        return false;
    },
    
    // File selection (browse / drop) handler
    add: function(files) {
        for(var i=0; i<files.length; i++) {
            if(this.isDuplicate(files[i])) continue;
            
            // TODO Sanity checks
            
            var node = $('<div />').text(files[i].name + ' (' + files[i].size + ')').appendTo($('#upload_form .files_dragdrop .files'));
            
            this.list.push({
                blob: files[i],
                node: node
            });
        }
        
        filesender.ui.nodes.files_clear.button('enable');
    },
    
    // Clear the file box
    clear: function() {
        for(var i=0; i<this.list.length; i++)
            this.list[i].node.remove();
        
        this.list = [];
        
        filesender.ui.nodes.files_clear.button('disable');
    },
};

// Manage recipients
filesender.ui.recipients = {
    list: [],
    
    // Test if recipient is duplicate
    exists: function(email) {
        for(var i=0; i<this.list.length; i++)
            if(this.list[i] == email) return true;
        
        return false;
    },
    
    // Add recipient to list
    add: function(email) {
        if(email.match(/[,;\s]/)) { // Multiple values
            email = email.split(/[,;\s]/);
            for(var i=0; i<email.length; i++) {
                var s = email[i].replace(/^\s+/g, '').replace(/\s+$/g, '');
                if(s) this.add(s);
            }
            return;
        }
        
        if(this.exists(email)) return;
        
        // TODO Sanity checks
        
        var div = $('<div />').attr('email', email).apendTo(filesender.ui.nodes.recipients.box);
        $('<span />').attr('title', email).appendTo(div);
        $('<i class="fa fa-minus-circle fa-lg fa-align-right" />').attr({
            title: lang.tr('click_to_delete_recipient')
        }).on('click', function() {
            filesender.ui.recipients.remove($(this).closest('div').attr('email'));
        }).appendTo(div);
        
        if(this.list.length)
            filesender.ui.nodes.recipients.box.show();
    },
    
    // Remove email from list
    remove: function(email) {
        if(email.match(/[,;\s]/)) { // Multiple values
            email = email.split(/[,;\s]/);
            for(var i=0; i<email.length; i++) {
                var s = email[i].replace(/^\s+/g, '').replace(/\s+$/g, '');
                if(s) this.remove(s);
            }
            return;
        }
        
        if(!this.exists(email)) return;
        
        filesender.ui.nodes.recipients.box.find('[email="' + email + '"]').remove();
        
        for(var i=0; i<this.list.length; i++)
            if(this.list[i] == email) this.list.splice(i, 1);
        
        if(this.list.length == 0)
            filesender.ui.nodes.recipients.box.hide();
    }
};

filesender.ui.startUpload = function() {
    
};

$(function() {
    var form = $('#upload_form');
    if(!form.length) return;
    
    // Register frequently used nodes
    filesender.ui.nodes = {
        files: {
            input: form.find(':file'),
            list: form.find('.files_dragdrop .files'),
            select: form.find('.files_actions .select_files'),
            clear: form.find('.files_actions .clear_all'),
        },
        recipients: {
            input: form.find('input[name="to"]'),
            box: form.find('.recipients_box'),
        },
        subject: form.find('input[name="subject"]'),
        message: form.find('input[name="message"]'),
        aup: form.find('input[name="aup"]'),
        expires: form.find('input[name="expires"]'),
        options: {}
    };
    form.find('.basic_options input, .advanced_options input').each(function() {
        var i = $(this);
        filesender.ui.nodes.options[i.attr('name')] = i;
    });

    // Bind file list clear button
    filesender.ui.nodes.files.clear.on('click', function() {
        if($(this).button('option', 'disabled')) return;
        
        filesender.ui.files.clear();
        return false;
    }).button().button('disable');
    
    // Bind file list select button
    filesender.ui.nodes.files.select.on('click', function() {
        filesender.ui.nodes.files.input.click();
        return false;
    }).button();
    
    // Bind file drag drop events
    $('body').on('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
    }).on('dragenter', function (e) {
        e.preventDefault();
        e.stopPropagation();
    }).on('drop', function (e) {
        if(!e.originalEvent.dataTransfer) return;
        if(!e.originalEvent.dataTransfer.files.length) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        filesender.ui.files.add(e.originalEvent.dataTransfer.files);
    });
    
    filesender.ui.nodes.recipients.input.on('keydown', function(e) {
        if(e.keyCode != 13) return;
        
        // enter is pressed
        e.preventDefault();
        e.stopPropagation();
        
        var i = $(this);
        filesender.ui.recipients.add(i.val());
        i.val('');
    }).on('blur', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var i = $(this);
        if(!i.val()) return;
        filesender.ui.recipients.add(i.val());
        i.val('');
    });
    
    // Bind file list select button
    filesender.ui.nodes.files.input.on('change', function() {
        // multiple files selected
        // loop through all files and show their values
        if (document.readyState != 'complete' && document.readyState != 'interactive') {
            return;
        }

        if(typeof this.files == 'undefined') return;
        
        filesender.upload_ui.files.add(this.files);
    });
    
    // Handle "back" browser action
    var files = filesender.ui.nodes.files.input[0].files;
    if(files && files.length) filesender.upload_ui.files.add(files);
    
    // Make options label toggle checkboxes
    form.find('.basic_options label, .advanced_options label').on('click', function() {
        var checkbox = $(this).closest('.fieldcontainer').find(':checkbox');
        checkbox.prop('checked', !checkbox.prop('checked'));
    }).css('cursor', 'pointer');
    
    // Bind advanced options display toggle
    form.find('.toggle_advanced_options').on('click', function() {
        $('.advanced_options').slideToggle();
        return false;
    });
    
    // Bind aup display toggle
    form.find('label[for="aup"]').on('click', function() {
        $(this).closest('.fieldcontainer').find('.terms').slideToggle();
        return false;
    });
    
    // Bind aup display toggle
    form.find('.upload_button a').on('click', function() {
        filesender.ui.startupload();
        return false;
    });
});
