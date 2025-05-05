/**
 * Created by etienne on 5/2/18.
 */
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


window.filesender.logger = {
    // Log stash (memory)
    stash: [],

    /**
     * Log message
     *
     * @param {String} msg
     */
    log: function(msg) {
        if(!(typeof msg).match(/^(number|string)$/))
            msg = JSON.stringify(msg);

        var len = filesender.config ? filesender.config.clientlogs.stashsize : 10;

        this.stash.push(msg);
        this.stash = this.stash.slice(-1 * len);
        if(filesender.supports.localStorage) {
            window.localStorage.setItem('client_logs', JSON.stringify(this.stash))
        }
    },

    /**
     * Load from storage
     */
    load: function() {
        if(filesender.supports.localStorage) {
            var s = window.localStorage.getItem('client_logs');
            if(s) this.stash = JSON.parse(s);
            if(!this.stash) this.stash = [];
        }
    },

    clear: function() {
        this.stash = [];
        if(filesender.supports.localStorage) {
            window.localStorage.setItem('client_logs', JSON.stringify(this.stash))
        }
    },

    /**
     * Send logs to server
     *
     * @param {Function} callback
     */
    send: function(callback) {
        filesender.client.put('/clientlogs/@me', this.stash, function(data) {
            if(callback) callback(data);
        });
    },

    export: function() {
        var d = new Date();
        var obj = { type: "filesender-client-log",
                    generated: d.toLocaleString(),
                    log: this.stash
                  };
        var blob = new Blob([JSON.stringify(obj, null, '\t')], {'type':'text/plain'});
        saveAs(blob, 'filesender-clientlog.txt');
    }

};

filesender.logger.nopwatcher = {
    time_since_last_progress: 0,
    max_time: 180,
    msg: null,

    /**
     * Rests watchdog counter
     */
    reset: function() {
        this.time_since_last_progress = 0;

        if(this.msg) this.msg.hide();
    },

    /**
     * Report that noting happened over the last second
     */
    nop: function() {
        this.time_since_last_progress++;

        if(this.time_since_last_progress > this.max_time) {
            // More than 3min without any kind of progress

            if(this.msg) {
                this.msg.show();

            } else {
                const msgContainer = `<div class="container"><div class="row"><div id="fs-upload__msg_content" class="col-12"></div></div></div>`;
                $(msgContainer).appendTo('#upload_form');
                this.msg = $('<div class="nothing_happened_as_of_late_you_can_send_client_logs" />').appendTo('#fs-upload__msg_content');
                this.msg.append(lang.tr('nothing_happened_as_of_late_you_can_send_client_logs') + ' ');
                this.msg.append($('<button class="send_client_logs fs-button fs-button--primary" />').text(lang.tr('send_client_logs').out()).on('click', function () {
                    filesender.logger.send(function () {
                        filesender.ui.notify('success', lang.tr('client_logs_sent'));
                    });
                }));
            }
        }
    },

    /**
     * Start watchdog
     */
    start: function() {
        var nop = this;

        // Catch file progress updates, bit hackish, may need better plug-in solution
        var pgr = filesender.ui.files.progress;
        filesender.ui.files.progress = function(file, complete) {
            nop.reset();

            pgr.call(filesender.ui.transfer, file, complete);
        };

        // Watchdog
        window.setInterval(function() {
            if(filesender.ui.transfer && filesender.ui.transfer.status === 'running') {
                nop.nop();

            } else {
                // done / paused / stopped, reset counter
                nop.reset();
            }
        }, 1000);        }
};

filesender.logger.load();

// Wrap console utilities
var wrap = ['log', 'info' ,'warn', 'error'];
if(!window.console) window.console = {};

jQuery.each( wrap, function(i,val) {
    var f = (val in window.console) ? window.console.val : function() {};
    window.console.val = function(msg) {

        // Custom log
        filesender.logger.log(msg);

        // Forward to internal
        f(msg);
    };
});


/**
 * Replace the filesender log function with a forward to logger.log
 * to collect logging information in the clientlogs
 */
window.filesender.log = function( msg )
{
    filesender.logger.log(msg);
    console.log(msg);
}

// Capture js errors
window.addEventListener('error', function(e) {
    if( !e?.currentTarget?.location?.origin ) {
        return;
    }
    if( e?.currentTarget?.location?.origin == window.location.origin ) {
        filesender.logger.log('[' + (new Date()).toLocaleTimeString() + '] JS ERROR in ' + e.filename + '@' + e.lineno + ':' + e.colno + ' ' + e.message);
    }
});

// Setup "nothing happens" detection
$(function() {
    if(!filesender.ui.files) return; // not upload page

    // last progress update watcher
    filesender.logger.nopwatcher.start();
});
