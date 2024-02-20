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
        
        if(filesender.config.log) console.log('[' + (new Date()).toLocaleTimeString() + '] ' + message);
        if(filesender.logger) {
            filesender.logger.log(message);
        }
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
        
        var d = $("<div  />").appendTo('body').attr({title: title});
        
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
                text: lang.tr(lid).out().replace(/<[^>]*>/g, ''),
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
        
        if(options.noclose)
            options.closeOnEscape = false;
        
        d.dialog(options);
        
        if(onclose) d.on('dialogclose', function() {
            if(!$(this).data('closed_by_button_click')) onclose.call(this);
            $(this).data('closed_by_button_click', false);
        });
        
        if(options.noclose)
            d.closest('.ui-dialog').find('.ui-dialog-titlebar-close').remove();
        
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
            {width: $('#wrap').width(), minHeight: 'auto', onclose: onclose }
        ).addClass('wide_info');
    },
    
    /**
     * Relocate a dialog
     */
    relocatePopup: function(popup) {
        popup.dialog({
            position: {
                my: 'center',
                at: 'center'
            }
        });
    },
    
    /**
     * Display a notification
     * 
     * @param string type "info", "success" or "error"
     * @param string message
     * @param callable ondisapear
     */
    notify: function(type, message, ondisapear) {
        if(typeof message != 'string') {
            if(message.out) {
                message = message.out();
            }else if(!message.jquery) {
                message = message.toString();
            }
        }
        
        var ctn = $('#notifications');
        if(!ctn.length) ctn = $('<div id="notifications" />').appendTo('body');
        
        var n = $('<div class="notification ' + type + '" />').html(message).appendTo(ctn);
        
        window.setTimeout(function() {
            n.fadeOut(1500, 'linear', ondisapear);
        }, 3000);
        
        return n;
    },
    
    /**
     * Display/remove maintenance popup
     * 
     * @param bool state
     */
    maintenance: function(state) {
        if(!state && this.maintenance_popup) {
            this.maintenance_popup.remove();
            this.maintenance_popup = null;
        }
        
        if(state && !this.maintenance_popup) {
            this.maintenance_popup = this.popup(lang.tr('undergoing_maintenance'), {}, {noclose: true});
            this.maintenance_popup.text(lang.tr('maintenance_autoresume'));
        }
    },
    
    /**
     * Redirect user to other page
     * 
     * @param string page
     * @param object args
     */
    goToPage: function(page, args, anchor, keep_args) {
        var current_args = {};
        var q = window.location.search.substr(1).split('&');
        for(var i=0; i<q.length; i++) {
            var a = q[i].split('=');
            current_args[a[0]] = a.slice(1).join('=');
        }
        
        if(typeof page == 'string')
            current_args.s = page;
        
        if(!keep_args) // Only keep page
            current_args = {s: current_args.s};
        
        if(args) for(var k in args) current_args[k] = args[k];
        
        args = [];
        for(var k in current_args) args.push(k + '=' + current_args[k]);
        
        this.redirect(filesender.config.base_path + '?' + args.join('&') + (anchor ? '#' + anchor : ''));
    },
    
    /**
     * Redirect user to url
     * 
     * @param string url
     * @param object args optional cgi key=value settings to send as args to the server
     */
    redirect: function(url,args) {
        if(args) {
            var current_args = {};
            for(var k in args) {
                current_args[k] = args[k];
            }
            args = [];
            for(var k in current_args) {
                args.push(k + '=' + current_args[k]);
            }
            url = url + '&' + args.join('&');
        }
        
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
        this.log(error);

        var msg = lang.tr(error.message);
        if( error.messageTranslated ) {
            msg = error.messageTranslated;
        }
        var d = this.alert('error', msg, callback);
        
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

        r.append('<br /><br />' + lang.tr('you_can_send_client_logs') + ' ');
        r.append($('<button class="send_client_logs" />').text(lang.tr('send_client_logs').out()).on('click', function() {
            filesender.logger.send(function() {
                filesender.ui.notify('success', lang.tr('client_logs_sent'));
            });
        }));
        
        return error.message;
    },
    rawError: function (text) {
        console.log(text);
        var doc = new DOMParser().parseFromString(text, 'text/html');
        if (doc.getElementsByClassName('exception')) { //if this is from our template, pull out the exception only.
                text = doc.getElementsByClassName('exception')[0].textContent || text;
        } else { //strip html as alert cant process that.
                text = doc.body.textContent || text;
        }
	text = (text.match(/^[a-z][a-z0-9_]+$/i) ? lang.tr(text) : text).trim();
	if (text!='') {
	        alert('Error : ' + text);
	}
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
        return filesender.ui.formatBinarySize(bytes, precision) + lang.tr('size_unit');
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
        var unit = lang.tr('speed_unit_' + (filesender.config.upload_display_bits_per_sec ? 'bits' : 'bytes'));
        return filesender.ui.formatBinarySize(bytes, precision) + unit;
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
     * Format a number of seconds in the future to a readable string
     * 
     * @param int v
     * 
     * @return string
     */
    formatETA : function (v) {
        if( v==-1 ) {
            return lang.tr('no_estimate');
        }
        if( !v ) {
            return lang.tr('soon');
        }
        if( v > 3600 ) {
            v = v / 3600; // epoch_hours
            return (v).toFixed(1) + ' ' + lang.tr('epoch_hours');
        }
        if( v > 5*60 ) {
            v = v / 60;
            return (v).toFixed(0) + ' ' + lang.tr('epoch_minutes');
        }
        if( v < 5 ) {
            return lang.tr('soon');
        }
        return ''+ (v).toFixed(0) + ' ' + lang.tr('epoch_seconds');
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
        email: /^[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z]{2,})$/i,
        filled: function() { return (this.replace(/(^\s+|\s+$)/, '') != ''); },
        int: /^[0-9]*$/,
        float: /^[0-9]*(\.[0-9]+)?$/,
        notzero: function() { return this && parseFloat(this) },
    },
    
    /**
     * Update user quota bar
     */
    updateUserQuotaBar: function() {
        var auth = $('body').attr('data-auth-type');
        
        if(!auth || auth == 'guest') return;
        
        if(!filesender.config.quota && filesender.config.quota !== undefined) return;
        
        filesender.client.getUserQuota(function(quota) {
            filesender.config.quota = quota; // Propagate info
            
            if(!quota) return;
            
            var bar = $('.user_quota');
            if(!bar.length) {
                bar = $('<div class="progressbar quota user_quota" />').prependTo('#page .box:eq(0)');
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
                
                $(document).trigger({type: 'filesender.quotabar.setup', quota: quota, bar: bar});
            }
            
            bar.progressbar('value', Math.floor(1000 * quota.used / quota.total));
            
            var info = lang.tr('quota_usage').r(quota);
            
            bar.find('.progress-label').text(info);
            
            bar.attr({title: lang.tr('user_quota')});
        });
    },

    /**
     * add a handler to show the error message invalidlabelobj when the text
     * in inputtextareaobj matches the regular expression rexstr
     * 
     * @param inputtextareaobj input to attach check on
     */
    handleFlagInvalidOnRegexMatch: function(inputtextareaobj,invalidlabelobj,rexstr) {

        if( rexstr.length ) {
            banned = new RegExp(rexstr, 'g');
        
            inputtextareaobj.on('keyup', function(e) {
                var v = $(this).val();
                if (v.match(banned)) {
                    $(this).addClass('invalid');
                    invalidlabelobj.show();
                }else{
                    $(this).removeClass('invalid');
                    invalidlabelobj.hide();
                }
            });
        }
    
    },

    /**
     * Set a jQuery widget date to the unix epoch time (in seconds) 
     * value contained in the data-epoch attribute
     */
    setDateFromEpochData: function( w ) {
        w.datepicker('setDate', new Date(w.attr('data-epoch') * 1000 ));
    },


    extendExpires: function(self,className)
    {
        if(self.hasClass('disabled')) return;
        
        var t = self.closest('.objectholder');
        var id = t.attr('data-id');
        if(!id || isNaN(id)) return;
        
        var duration = parseInt(t.attr('data-expiry-extension'));

        var extend = function(remind) {
            filesender.client.extendObject(className,id, remind, function(t) {
                $('.objectholder[data-id="' + id + '"]').attr('data-expiry-extension', t.expiry_date_extension);
                if( !t.expiry_date_extension ) {
                    self.addClass('disabled');
                }
                $('.objectholder[data-id="' + id + '"] [data-rel="expires"]').text(t.expires.formatted);
                
                if(!t.expiry_date_extension) {
                    $('.objectholder[data-id="' + id + '"] [data-action="extend"]').addClass('disabled').attr({
                        title: lang.tr('expiry_extension_count_exceeded')
                    });
                    
                } else {
                    $('.objectholder[data-id="' + id + '"] [data-action="extend"]').attr({
                        title: lang.tr('extend_expiry_date').r({
                            days: self.closest('.objectholder').attr('data-expiry-extension')
                        })
                    });
                }
                
                filesender.ui.notify('success', lang.tr(remind ? 'extended_reminded' : 'extended').r({expires: t.expires.formatted}));
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
    },
    
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
        filesender.ui.goToPage(true, {lang: $(this).val()}, null, true);
    });
    
    filesender.ui.updateUserQuotaBar();

    if (!filesender.supports.crypto) {
        // Disable the upload fields
        $("#encryption").attr("disabled", "disabled");
        $("#encryption_description_container_disabled").show();

        // Disable the transfer buttons
        $('#encryption_description_not_supported').show();
        $('.transfer-download').css({'color': 'rgba(173, 173, 173, 1)', 'cursor': 'default'});

        // Disable the download buttons
        $(".files.box .file[data-encrypted='1']").css({'height': '3.5em'});
        $(".files.box .file[data-encrypted='1'] .download").hide();
        $(".download_decryption_disabled").show();
    }
});
