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
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS'
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

/**
 * Client side language handling
 */

if(!('filesender' in window)) window.filesender = {};


/**
 * UI methods
 */
window.filesender.ui = {
    /**
     * Log to console if enabled
     * 
     * @param mixed message
     */
    log: function(message) {
        if(filesender.config.log) console.log(message);
    },
    
    /**
     * Holder for named nodes
     */
    nodes: {},
    
    /**
     * Max popup width
     */
    popup_width: 550,
    
    
    /**
     * Open a popup
     * 
     * @param mixed title
     * @param object buttons lang id to action
     * 
     * @return Dialog
     */
    popup: function(title, buttons, options) {
        if(typeof title != 'string') {
            if(title.out) {
                title = title.out();
            }else if(!title.jquery) {
                title = title.toString();
            }
        }
        
        var d = $('<div />').appendTo('body').attr({title: title});
        
        var handle = function(lid) {
            return function() {
                var close = buttons[lid] ? buttons[lid].call(d) : true;
                if(typeof close != 'undefined' && !close) return;
                d.data('closed_by_button_click', true);
                d.dialog('close');
                d.remove();
            };
        };
        var btndef = [];
        for(var lid in buttons) {
            btndef.push({
                text: lang.tr(lid).out(),
                click: handle(lid)
            });
        }
        
        if(!options) options = {};
        var baseopts = {
            resizable: false,
            width: Math.min(this.popup_width, $('#wrap').width()),
            modal: true
        };
        for(var k in baseopts)
            if(typeof options[k] == 'undefined')
                options[k] = baseopts[k];
        
        options.buttons = btndef;
        
        var onclose = options.onclose ? options.onclose : null;
        if(onclose) delete options.onclose;
        
        d.dialog(options);
        
        if(onclose) d.on('dialogclose', function() {
            if(!$(this).data('closed_by_button_click')) onclose.call(this);
            $(this).data('closed_by_button_click', false);
        });
        
        return d;
    },
    
    /**
     * Display a nice alert like dialog
     * 
     * @param string type "info", "success" or "error"
     * @param string message
     * @param callable onclose
     */
    alert: function(type, message, onclose) {
        if(typeof message != 'string') {
            if(message.out) {
                message = message.out();
            }else if(!message.jquery) {
                message = message.toString();
            }
        }
        
        var d = this.popup(lang.tr(type + '_dialog'), {close: onclose}, {onclose: onclose});
        d.addClass(type).html(message);
        return d;
    },
    
    /**
     * Display a confirm box
     * 
     * @param string message
     * @param callable onok
     * @param callable oncancel
     */
    confirm: function(message, onok, oncancel, yesno) {
        if(typeof message != 'string') {
            if(message.out) {
                message = message.out();
            }else message = message.toString();
        }
        
        var d = this.popup(lang.tr('confirm_dialog'), yesno ? {yes: onok, no: oncancel} : {ok: onok, cancel: oncancel}, {onclose: oncancel});
        d.html(message);
        return d;
    },
    
    /**
     * Display a prompt box
     * 
     * @param callable onok
     * @param callable oncancel
     */
    prompt: function(title, onok, oncancel) {
        return this.popup(title, {ok: onok, cancel: oncancel}, {onclose: oncancel});
    },
    
    /**
     * Display an action selection box
     * 
     * @param array actions (lang ids)
     * @param callable onaction
     * @param callable oncancel
     * 
     * @return node
     */
    chooseAction: function(actions, onaction, oncancel) {
        var d = this.popup(lang.tr('what_to_do'), {
            ok: function() {
                return onaction($(this).find('.actions input[name="action"]:checked').val());
            },
            cancel: oncancel
        }, {onclose: oncancel});
        
        var list = $('<div class="actions" />').appendTo(d);
        for(var i=0; i<actions.length; i++) {
            var action = $('<div class="action" />').appendTo(list);
            var input = $('<input type="radio" name="action" />').attr({value: actions[i]}).appendTo(action);
            $('<label for="action" />').text(lang.tr(actions[i]).out()).appendTo(action);
            action.on('click', function() {
                var input = $(this).find('input[name="action"]');
                input.val([input.attr('value')]);
            });
        }
        list.find('input[name="action"]').val([actions[0]]);
        
        return d;
    },
    
    /**
     * Display a wide info popup
     * 
     * @param string title lang id
     * @param callable onclose
     */
    wideInfoPopup: function(title, onclose) {
        return this.popup(
            title,
            {close: onclose},
            {width: $('#wrap').width(), height: 0.8 * $(window).height(), onclose: onclose}
        ).addClass('wide_info');
    },
    
    /**
     * Redirect user to other page
     * 
     * @param string page
     * @param object args
     */
    goToPage: function(page, args) {
        if(typeof page != 'string') {
            var q = window.location.search.substr(1).split('&');
            for(var i=0; i<q.length; i++)
                if(q[i].substr(0, 2) == 's=')
                    page = q[i].substr(2);
        }
        
        var a = [];
        if(typeof page == 'string') a.push('s=' + page);
        if(args) for(var k in args) a.push(k + '=' + args[k]);
        
        this.redirect(filesender.config.base_path + '?' + a.join('&'));
    },
    
    /**
     * Redirect user to url
     * 
     * @param string url
     */
    redirect: function(url) {
        window.location.href = url;
    },
    
    /**
     * Reload page
     * 
     * @param string url
     */
    reload: function() {
        window.location.reload();
    },
    
    /**
     * Nicely displays an error
     * 
     * @param string code error code (to be translated)
     * @param object data values for translation placeholders
     */
    error: function(error,callback) {
        this.log('[error] ' + error.message);
        
        var d = this.alert('error', lang.tr(error.message),callback);
        
        if(error.details) {
            var i = $('<div class="details" />').appendTo(d);
            $.each(error.details, function(k, v) {
                if(isNaN(k)) v = lang.tr(k) + ': ' + v;
                $('<div class="detail" />').text(v).appendTo(i);
            });
        }
        
        var r = $('<div class="report" />').appendTo(d);
        if(filesender.config.support_email) {
            r.append(lang.tr('you_can_report_exception_by_email') + ' : ');
            $('<a />').attr({
                href: 'href="mailto:' + filesender.config.support_email + '?subject=Exception ' + (error.uid ? error.uid : '')
            }).text(lang.tr('report_exception')).appendTo(r);
        } else if(error.uid) {
            r.append(lang.tr('you_can_report_exception') + ' : "' + error.uid + '"');
        }
        
        return error.message;
    },
    
    rawError: function(text) {
        alert('Error : ' + (text.match(/^[a-z][a-z0-9_]+$/i) ? lang.tr(text) : text));
    },
    
    
    
    
    
    /**
     * Format size in bytes
     * 
     * @param int size 
     * @param int precision
     * 
     * @return string
     */
    formatBytes: function (bytes, precision) {
        return filesender.ui.formatBinarySize(bytes,precision)+lang.tr('size_unit');
    },
    
    
    /**
     * Format speed 
     * 
     * @param int size 
     * @param int precision
     * 
     * @return string
     */
    formatSpeed : function (bytes,precision) {
        return filesender.ui.formatBinarySize(bytes,precision)+lang.tr('speed_unit');
    },
    
    
     /**
     * Format binary size 
     * 
     * @param int size 
     * @param int precision
     * 
     * @return string
     */
    formatBinarySize : function (bytes, precision) {
        if(!precision || isNaN(precision))
            precision = 1;
        
        var multipliers = ['', 'k', 'M', 'G', 'T'];
        
        var bytes = Math.max(bytes, 0);
        var pow = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
        pow = Math.min(pow, multipliers.length - 1);
        
        bytes /= Math.pow(1024, pow);
        
        return bytes.toFixed(precision).replace(/\.0+$/g, '') + ' ' + multipliers[pow];
    },
    
    /**
     * Pending transfer check
     * 
     * @param function callback will be given another callback which must be called with decision ("resume", "ignore", "clear")
     */
    checkPendingTransfer: function(callback) {
        if(!this.supports.localStorage) return;
        
        var pending = localStorage.getItem('transfer');
        if(!pending) return;
        
        pending = JSON.parse(pending);
        
        callback(function(choice) {
            if(choice == 'resume') { // Resume pending transfer
                
            }else if(choice == 'clear') { // Forget pending transfer
                localStorage.removeItem('transfer');
            }
        });
    },
    
    /**
     * Validators for form fields
     */
    validators: {
        email: /^[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-zA-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)$/i,
        filled: function() { return (this.replace(/(^\s+|\s+$)/, '') != ''); },
        int: /^[0-9]*$/,
        float: /^[0-9]*(\.[0-9]+)?$/,
        notzero: function() { return this && parseFloat(this) },
    }
};

$(function() {
    $('#topmenu_help[href="#"]').on('click', function() {
        $('#dialog-help').dialog({
            width: 700
        });
        return false;
    });
    
    $('#dialog-help li[data-feature="html5"]').toggle(filesender.supports.reader);
    $('#dialog-help li[data-feature="nohtml5"]').toggle(!filesender.supports.reader);
    
    $('#topmenu_about[href="#"]').on('click', function() {
        $('#dialog-about').dialog({
            width: 400
        });
        return false;
    });
    
    $('#btn_logon').button();
    
    $('#language_selector').on('change', function() {
        filesender.ui.goToPage(true, {lang: $(this).val()})
    });
    
    filesender.client.getUserQuota(function(quota) {
        if(!quota) return;
        
        filesender.config.quota = quota; // Propagate info
        
        var bar = $('<div class="progressbar quota user_quota" />').prependTo('#page .box:eq(0)');
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
        
        bar.progressbar('value', Math.round(1000 * quota.used / quota.total));
        bar.attr({title: lang.tr('user_quota') + ' : ' + lang.tr('quota_usage').r({total: quota.total, used: quota.used, available: quota.available})});
    });
});
