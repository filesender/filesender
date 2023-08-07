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


if(!('filesender' in window)) window.filesender = {};


/**
 * Dialog letting user know that PBKDF2 is being calculated
 */
window.filesender.pbkdf2dialog = {

    // delay in ms to show the dialog
    // this is set from config in setup()
    delay_to_show_dialog: 0,

    // dialog window
    dialog: null,

    // if the delay causes the show dialog method
    // to be run after the action is already complete don't
    // quickly flash the dialog at the user
    already_complete: false,

    // remember how long pbkdf2 took on this machine
    time_start: 0,
    time_end: 0,

    only_one_pbkdf2_process: true,


    // set in setup() from config.
    encryption_password_hash_iterations: 0,

    reset: function() {
        $this = this
        $this.already_complete = false;
    },
    usingGeneratedKey: function() {
        $this = this
        return $this.encryption_password_hash_iterations < 10;
    },
    setup: function( only_one_pbkdf2_process_v ) {
        $this = this;
        $this.only_one_pbkdf2_process = only_one_pbkdf2_process_v;

        $this.delay_to_show_dialog = window.filesender.config.crypto_pbkdf2_delay_to_show_dialog;
        $this.encryption_password_hash_iterations = filesender.config.encryption_password_hash_iterations_new_files;


        window.filesender.onPBKDF2Starting = function() {
            window.filesender.log("pbkdf2dialog onPBKDF2Starting()");
            $this.time_start = Date.now();
            expected_delay = window.localStorage.getItem('crypto_pbkdf2_delay_seconds');
            
            window.setTimeout(function() {
                if( !$this.already_complete ) {
                    if( window.filesender.config.crypto_pbkdf2_dialog_enabled ) {
                        var trans = 'crypto_pbkdf2_dialog_with_expected';
                        if( !expected_delay ) {
                            trans = 'crypto_pbkdf2_dialog';
                        }
                        // Generated keys do not need repeated hashing
                        // so they should be too quick for a dialog to be needed
                        // See end of https://github.com/filesender/filesender/pull/375#issuecomment-439160499
                        if( 'usingGeneratedKey' in $this && !($this.usingGeneratedKey())) {
                            $this.dialog = filesender.ui.alert(
                                "info", lang.tr(trans).r({seconds: expected_delay}).out());
                        }
                    }
                }
            }, $this.delay_to_show_dialog );
                              
        };
        

        window.filesender.onPBKDF2Ended = function() {
            
            window.filesender.log("ended() only_one_pbkdf2_process: " + $this.only_one_pbkdf2_process );
            if( $this.only_one_pbkdf2_process ) {
                $this.onPBKDF2Over();
            }
        };

        // Chain this out so the UI can still get it.
        var allEnded = window.filesender.onPBKDF2AllEnded;
        window.filesender.onPBKDF2AllEnded = function() {
            window.filesender.log("ending() only_one_pbkdf2_process: " + $this.only_one_pbkdf2_process );
            if( !($this.only_one_pbkdf2_process)) {
                $this.onPBKDF2Over();
            }
            
            window.filesender.log("pbkdf2dialog onPBKDF2AllEnded()");
            allEnded();
        };
    },

    /*
     * This will call onPBKDF2AllEnded() if it has not already been called.
     **/
    ensure_onPBKDF2AllEnded: function() {
        if( $this.already_complete && !$this.dialog ) {
            return;
        }
        window.filesender.onPBKDF2AllEnded();
    },

    onPBKDF2Over: function() {
        $this = this;
        $this.time_end = Date.now();
        window.filesender.log("pbkdf2dialog onPBKDF2Over()");
        $this.already_complete = true;
        if( $this.dialog && $this.dialog['0'].id!="" ) {
            $this.dialog.dialog('close');
            $this.dialog.remove();
            $this.dialog = null;
        }
        if( window.filesender.supports.localStorage ) {
            // dont record time for a generated key as it is different
            // than user supplied key.
            if( !($this.usingGeneratedKey())) {
                window.localStorage.setItem('crypto_pbkdf2_delay_seconds',
                                            Math.ceil(($this.time_end - $this.time_start) / 1000 ));
            }
        }
    },
    
};
