/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2022, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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
 * Client side notifiction handling
 */

if(!('filesender' in window)) window.filesender = {};


window.filesender.notification = {

    available: false,
    use: false,
    asked: false,
    n: [],


    /**
     * ask for notification permission
     */
    ask: function( force = false ) {
        this.available = false;

        // only try to ask once.
        if( this.asked &&
            (Notification.permission === 'denied' || Notification.permission === 'default'))
        {
            // this is to allow firefox to force ask if it failed due to not being in a 'click' when asking.
            if( !force ) 
                return false;
        }
        this.asked = true;
        
        function checkNotificationPromise() {
            try {
                Notification.requestPermission().then();
            } catch(e) {
                return false;
            }
            return true;
        }
        
        function handlePermission(permission) {
            if(Notification.permission === 'denied' || Notification.permission === 'default') {
                // remember that this might not be the notification object in this context
                window.filesender.notification.available = false;
            } else {
                window.filesender.notification.available = true;
            }
        }

        // Let's check if the browser supports notifications
        if (!('Notification' in window)) {
            console.log("This browser does not support notifications.");
        } else {
            if(checkNotificationPromise()) {
                Notification.requestPermission()
                    .then((permission) => {
                        handlePermission(permission);
                    })
            } else {
                Notification.requestPermission(function(permission) {
                    handlePermission(permission);
                });
            }
        }

        return this.available;
    },

    image_success: 'images/success.png',
    
    /**
     * send notification if enabled and requested
     *
     * @param {String} msg
     */
    notify: function( title, msg, image ) {
        if( !this.available ) {
            return;
        }

        if( !image ) {
            // maybe a default image?
        }
        var newn = new Notification( title, { body: msg, icon: image });
        this.n.push(newn);
        
    },

    /**
     * clear all the notifications
     */
    clear: function() {
        this.n.forEach(e => e.close());
        this.n = [];
    }
};


$(function() {
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            // remove notifications when the user switches to tab
            window.filesender.notification.clear();
        }
    });
});
