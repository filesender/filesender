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
     * Holder for named nodes
     */
    nodes: {},
    
    /**
     * Validators for form fields
     */
    validators: {
        email: /^[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-zA-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)$/i,
        filled: function() { return (this.replace(/(^\s+|\s+$)/, '') != ''); },
        int: /^[0-9]*$/,
        float: /^[0-9]*(\.[0-9]+)?$/,
        notzero: function() { return this && parseFloat(this) },
    },
    
    /**
     * Attach validator to form field
     */
    addValidator: function(what /*, tests ... */) {
        var input = $(this);
        if(!input.is(':input')) return;
        
        var validator = input.data('validator');
        
        if(!validator) validator = {
            input: input,
            tests: [],
            
            run: function() {
                var value = this.input.val();
                var errorselector = '.errorhint[for="' + this.input.attr('name') + '"]';
                
                for(var i=0; i<this.tests.length; i++) {
                    var test = this.tests[i];
                    var ok = true;
                    var err = null;
                    
                    if(typeof test == 'string') {
                        err = 'error_not_' + test;
                        test = filesender.ui.validators[test];
                    }
                    
                    if(test.test) { // Regexp
                        ok = test.test(value);
                    }else if(test.call) { // Function that throws or return error code
                        try {
                            err = test.call(this, value);
                            if(!err) ok = true;
                        } catch(e) {
                            err = e;
                            ok = false;
                        }
                    }
                    
                    if(!ok) {
                        this.input.addClass('error');
                        
                        if(err) {
                            if(typeof err == 'function') {
                                err.call(this);
                            }else if(!this.input.parent().find(errorselector + '[code="' + err + '"]').length) {
                                var msg = err.match(/\s/) ? err : lang.tr(err);
                                $('<span class="errorhint" />').attr({
                                    for: this.input.attr('name'),
                                    code: err
                                }).html(msg).insertAfter(this.input);
                            }
                        }
                        
                        return false;
                    }
                }
                
                this.input.removeClass('error');
                this.input.parent().find(errorselector).remove();
                
                return true;
            }
        };
        
        for(var i=1; i<arguments.length; i++) {
            var a = arguments[i];
            
            if(a.splice) { // Array
                for(var j=0; j<a.length; j++)
                    validator.tests.push(a[j]);
            }else validator.tests.push(a);
        }
        
        input.data('validator', validator);
    },
    
    /**
     * Validate whole form / single field
     */
    validate: function(what) {
        var type = what.tagName.toLowerCase();
        if(!type.match(/^(input|textarea|select|form)$/)) return true;
        
        if(type == 'form') { // Whole form validation
            var ok = true;
            $(what).find(':input').each(function() {
                ok &= filesender.ui.validate(this);
            });
            return ok;
        }
        
        // Element
        var input = $(what);
        var validator = input.data('validator');
        if(!validator) return true;
        return validator.run();
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
        
        var d = $('<div class="' + type + '" />').appendTo('body').attr({title: lang.tr(type + '_dialog').out()}).html(message);
        
        d.dialog({
            resizable: false,
            width:550,
            modal: true,
            buttons: {
                close: {
                    text: lang.tr('close'),
                    click: function () {
                        d.dialog('close');
                        d.remove();
                        if(onclose) onclose();
                    }
                }
            }
        });
        
        return d;
    },
    
    /**
     * Display a confirm box
     * 
     * @param string message
     * @param callable onok
     * @param callable oncancel
     */
    confirm: function(message, onok, oncancel) {
        if(typeof message != 'string') {
            if(message.out) {
                message = message.out();
            }else message = message.toString();
        }
        
        var d = $('<div />').appendTo('body').attr({title: lang.tr('confirm_dialog').out()}).html(message);
        
        d.dialog({
            resizable: false,
            width:550,
            modal: true,
            buttons: {
                ok: {
                    text: lang.tr('ok'),
                    click: function () {
                        d.dialog('close');
                        d.remove();
                        if(onok) onok();
                    }
                },
                cancel: {
                    text: lang.tr('cancel'),
                    click: function () {
                        d.dialog('close');
                        d.remove();
                        if(oncancel) oncancel();
                    }
                }
            }
        });
        
        return d;
    },
    
    /**
     * Redirect user to other page
     * 
     * @param string page
     * @param object args
     */
    goToPage: function(page, args) {
        var a = ['s=' + page];
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
     * Nicely displays an error
     * 
     * @param string code error code (to be translated)
     * @param object data values for translation placeholders
     */
    error: function(error) {
        var d = this.alert('error', lang.tr(error.message));
        
        if(error.info) {
            var i = $('<div class="details" />').appendTo(d);
            $.each(error.info, function(k, v) {
                if(isNaN(k)) v = k + ': ' + v;
                $('<div class="detail" />').text(v).appendTo(i);
            });
        }
        
        if(error.uid) {
            var r = $('<div class="report" />').appendTo(d);
            r.append(lang.tr('you_can_report_exception') + ' : ');
            $('<a />').attr({
                href: 'href="mailto:' + filesender.config.support_email + '?subject=Exception ' + error.uid
            }).text(lang.tr('report_exception')).appendTo(r);
        }
        
        return error.message;
    },
    
    rawError: function(text) {
        alert('Error : ' + (text.match(/^[a-z][a-z0-9_]+$/i) ? lang.tr(text) : text));
    },
    
    /**
     * Format size in bytes
     * 
     * @param int size in bytes
     * 
     * @return string
     */
    formatBytes: function formatBytes(bytes, precision) {
        if(!precision || isNaN(precision))
            precision = 1;
        
        var nomult = lang.tr('bytes_no_multiplier').out();
        if(nomult == '{bytes_no_multiplier}') nomult = 'Bytes';
        
        var wmult = lang.tr('bytes_with_multiplier').out();
        if(wmult == '{bytes_with_multiplier}') wmult = 'B';
        
        var multipliers = ['', 'k', 'M', 'G', 'T'];
        
        var bytes = Math.max(bytes, 0);
        var pow = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
        pow = Math.min(pow, multipliers.length - 1);
        
        bytes /= Math.pow(1024, pow);
        
        return bytes.toFixed(precision).replace(/\.0+$/g, '') + ' ' + multipliers[pow] + (pow ? wmult : nomult);
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
    }
};

$(function() {
    $('#topmenu_help').on('click', function() {
        $('#dialog-help').dialog({
            width: 700
        });
        return false;
    });
    
    $('#topmenu_about').on('click', function() {
        $('#dialog-about').dialog({
            width: 400
        });
        return false;
    });
});
