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
 * AJAX webservice client
 */
window.filesender.client = {
    // REST service base path
    base_path: null,
    
    // Security token
    security_token: null,
    
    // Webservice maintenance flag
    maintenance: false,
    
    // Pending requests
    pending_requests: [],
    
    // Handling authentication required
    authentication_required: false,

    // Handling specific exceptions thrown from server
    // should return true if it has handled the error
    specificErrorHandler: function(error) { return false; },

    getCSRFToken: function() {
        if (typeof CSRFP != "undefined") {
            return CSRFP._getAuthKey();
        }
        return "";
    },
    
    updateSecurityToken: function(source) {
        if(typeof source !== 'string') {
            if(!source.getResponseHeader)
                return;
            
            source = source.getResponseHeader('X-Filesender-Security-Token');
        }
        
        if(!source || source == this.security_token)
            return;
        
        filesender.ui.log('Security token update');
        this.security_token = source;
        $('body').attr({'data-security-token': source});
        
        if(filesender.terasender && filesender.terasender.security_token != source)
            filesender.terasender.security_token = source;
    },

    setupSSLOptions: function( settings ) {
    },
    
    // Send a request to the webservice
    call: function(method, resource, data, callback, options) {
        if(!this.base_path) {
            var path = window.location.href;
            path = path.split('/');
            path.pop();
            path = path.join('/');
            this.base_path = path + '/rest.php';
        }
        
        if(this.security_token === null)
            this.security_token = $('body').attr('data-security-token');
        
        if(!options) options = {};
        
        var args = {};
        if(options.args) for(var k in options.args) args[k] = options.args[k];
        var urlargs = [];
        for(var k in args) urlargs.push(k + '=' + args[k]);
        
        
        // API Key based authentication (i.e. CLI)
        if (this.api_key) {
            //... if there is an API key then REST CLI
            var timestamp = Math.floor(Date.now() / 1000);

            urlargs.push('remote_user' + '=' + this.from);
            urlargs.push('timestamp' + '=' + timestamp);

            var local_resource = resource.split(/\?|\&/);
            resource = local_resource.shift();

            local_resource.forEach(v => urlargs.push(v));

            urlargs.sort();
            var to_sign = method.toLowerCase()
                        +'&'
                        +this.base_path.replace('https://','',1).replace('http://','',1)
                        +resource
                        +'?'
                        +urlargs.join('&');

            const crypto = require('crypto');
            var hm = crypto.createHmac("sha1", this.api_key).update(to_sign);
            
            if(data) {
                var raw = options && ('rawdata' in options) && options.rawdata;

                if(!raw) {
                    //clean up the data
                    data.aup_checked = 1;

                    //Delete all `null` & `undefined` values (== operator vs ===)
                    Object.keys(data).forEach((key) => data[key] == null && delete data[key]);
                    data = JSON.stringify(data);
                    hm.update('&');
                    hm.update(data);
                } else {
                    hm.update('&');
                    value = '';
                    if( typeof data != 'object' ) {
                        value = data.buffer;
                        hm.update(value);
                    }
                    else {
                        value = window.filesender.crypto_common().convertArrayBufferViewtoString(data);
                        hm.update(data);
                    }
                }
            } else {
                data = undefined;
            }


            let signature = hm.digest().toString('hex');
            urlargs.push('signature' + '=' + signature);
            

        } else {

            if(data) {
                var raw = options && ('rawdata' in options) && options.rawdata;
                
                if(!raw) {
                    data = JSON.stringify(data);
                }
            }else data = undefined;
            
        }        

        
        if(urlargs.length) resource += (resource.match(/\?/) ? '&' : '?') + urlargs.join('&');
        
        var errorhandler = function(error) {
            filesender.ui.error(error);
        };
        if(options.error) {
            errorhandler = options.error;
            delete options.error;
        }
        
        var headers = {};
        if(options.headers) headers = options.headers;
        
        if(this.security_token /*&& (method != 'get')**/) headers['X-Filesender-Security-Token'] = this.security_token;

	if (method.toLowerCase() === 'delete' || method.toLowerCase() === 'put') {
	    // attach the token in request header
   	    headers['csrfptoken'] = filesender.client.getCSRFToken();
	}
        
        var settings = {
            cache: false,
            contentType: 'application/json;charset=utf-8',
            'accept-encoding': 'identity',
            context: window,
            data: data,
            processData: false,
            dataType: 'json',
            beforeSend: function(xhr) {
                for(var k in headers) xhr.setRequestHeader(k, headers[k]);
            },
            success: function(data, status, xhr) {
                filesender.client.updateSecurityToken(xhr); // Update security token if it was changed (do this before callback since callback may trigger another request)
                callback.apply(null, arguments);
            },
            complete: function(xhr) {
                filesender.client.updateSecurityToken(xhr); // Update security token if it was changed
            },
            type: method.toUpperCase(),
            url: this.base_path + resource
        };
        
        // Needs to be done after "var settings" because handler needs that settings variable exists
        settings.error = function(xhr, status, error) {
            var msg = xhr.responseText.replace(/^\s+/, '').replace(/\s+$/, '');
            
            if( // Ignore 40x, 50x and timeouts if undergoing maintenance
                (xhr.status >= 400 || status == 'timeout') &&
                filesender.client.maintenance
            ) return;
            
            try {
                var error = JSON.parse(msg);
                
                if(error.message == 'auth_user_not_allowed') // Should have been already reported by html ui
                    return;
                
                if(
                    (error.message == 'rest_authentication_required' || error.message == 'rest_xsrf_token_did_not_match') &&
                    (options.ignore_authentication_required || filesender.client.authentication_required)
                )
                    return;

                if(
                    (error.message == 'rest_authentication_required' || error.message == 'rest_xsrf_token_did_not_match') &&
                    (options.auth_prompt === undefined || options.auth_prompt)
                ) {
                    filesender.ui.confirmTitle(lang.tr('authentication_required'),
                                               lang.tr('authentication_required_explanation'),
                                               function() {
                                                   filesender.ui.redirect(filesender.config.logon_url);
                                               });
                    return;
                }


                if( error.message == 'rest_roundtrip_token_invalid')
                {
                    filesender.ui.alert('error',
                                        filesender.config.language.rest_roundtrip_token_invalid,
                                        function() {} );
                    return;
                }

                if( error.message == 'guest_reminder_rate_limit_reached')
                {
                    filesender.ui.alert('error',
                                        filesender.config.language.guest_reminder_rate_limit_reached,
                                        function() {} );
                    return;
                }

                if( error.message == 'user_hit_guest_rate_limit')
                {
                    filesender.ui.alert('error',
                                        filesender.config.language.user_hit_guest_rate_limit,
                                        function() {} );
                    return;
                }
                
                
                if(error.message == 'undergoing_maintenance') {
                    if(filesender.client.maintenance) return;
                    
                    filesender.ui.log('Webservice entered maintenance mode, keeping requests to run them when maintenance ends');
                    if(filesender.ui.transfer) filesender.ui.transfer.maintenance(true);
                    
                    filesender.client.maintenance = window.setInterval(function() {
                        filesender.client.get('/info', function(info) {
                            // Got data instead of "undergoing_maintenance" exception, maintenance is over, lets restart stacked requests
                            window.clearInterval(filesender.client.maintenance);
                            filesender.client.maintenance = false;
                            
                            filesender.ui.maintenance(false);
                            
                            filesender.ui.log('Webservice maintenance mode ended, running pending requests');
                            
                            if(filesender.ui.transfer) filesender.ui.transfer.maintenance(false);
                            for(var i=0; i<filesender.client.pending_requests.length; i++)
                                jQuery.ajax(filesender.client.pending_requests[i]);
                            
                            filesender.ui.log('Ran all pending requests from webservice maintenance period');
                        }, {maintenanceCheck: true});
                    }, 60 * 1000);
                    
                    filesender.client.pending_requests.push(settings);
                    
                    if(!$('#page.maintenance_page').length)
                        filesender.ui.maintenance(true);
                    
                    return;
                }

                if( filesender.client.specificErrorHandler &&
                    filesender.client.specificErrorHandler(error)) {
                    return;
                }

                if(error.message == 'user_hit_guest_limit') {
                    filesender.ui.alert('error',
                                        filesender.config.language.user_hit_guest_limit,
                                        function() {} );
                    return;
                }
                
                errorhandler(error);
            } catch(e) {
                filesender.ui.rawError(msg);
            }
        };
        
        for(var k in options) settings[k] = options[k];
        
        if(this.maintenance && !options.maintenanceCheck) {
            this.pending_requests.push(settings);
            return;
        }

        
        this.setupSSLOptions( settings );
        return jQuery.ajax(settings);
    },
    
    get: function(resource, callback, options) {
        return this.call('get', resource, undefined, callback, options);
    },
    
    post: function(resource, data, callback, options) {
        return this.call('post', resource, data, function(data, status, xhr) {
            callback.call(this, xhr.getResponseHeader('Location'), data);
        }, options);
    },
    
    put: function(resource, data, callback, options) {
        return this.call('put', resource, data, callback, options);
    },
    
    delete: function(resource, callback, options) {
        return this.call('delete', resource, undefined, callback, options);
    },
    
    /**
     * Get public info about the Filesender instance
     */
    getInfo: function(callback) {
        return this.get('/info', callback);
    },
    
    /**
     * Start a transfer
     * 
     * @param string from sender email
     * @param array files array of file objects with name, size and sha1 properties
     * @param array recipients array of recipients addresses
     * @param string subject optionnal subject
     * @param string message optionnal message
     * @param string expires expiry date (yyyy-mm-dd or unix timestamp)
     * @param array options array of selected option identifiers
     * @param callable callback function to call with transfer path and transfer info once done
     */
    postTransfer: function(transfer, callback, onerror) {
        var opts = {};
        if(onerror) opts.error = onerror;
            
        var files = [];
        for (var i = 0; i < transfer.files.length; i++) {
            //
            // Do not btoa an empty string, if there is nothing to send
            // then just send nothing.
            //
            var aead = '';
            if( transfer.files[i].aead ) {
                aead = btoa(transfer.files[i].aead);
            }
            files.push({
                name: transfer.files[i].name,
                size: transfer.files[i].size,
                mime_type: transfer.files[i].mime_type,
                cid: transfer.files[i].cid,
                iv: transfer.files[i].iv,
                aead: aead 
            });
        }

        return this.post(transfer.authenticatedEndpoint('/transfer'), {
            from: transfer.from,
            encryption: transfer.encryption,
            encryption_key_version: transfer.encryption_key_version,
            encryption_password_encoding: transfer.encryption_password_encoding,
            encryption_password_version:  transfer.encryption_password_version,
            encryption_password_hash_iterations: transfer.encryption_password_hash_iterations,
            encryption_client_entropy: transfer.encryption_client_entropy,
            files: files,
            recipients: transfer.recipients,
            subject: transfer.subject,
            message: transfer.message,
            lang: transfer.lang,
            expires: transfer.expires,
            aup_checked: transfer.aup_checked,
            options: transfer.options
        }, callback, opts);
    },
    
    /**
     * Get transfer info
     * 
     * @param int id
     * @param callable callback
     * @param callable onerror
     */
    getTransfer: function(id, callback, onerror, opts) {
        if(!opts) opts = {};
        if(onerror) opts.error = onerror;
        
        return this.get('/transfer/' + id, callback, opts);
    },
    
    /**
     * Post a file chunk
     * 
     * @param object file
     * @param blob chunk
     * @param callable callback
     */
    postChunk: function(file, blob, callback, onerror) {
        var opts = {
            contentType: 'application/octet-stream',
            rawdata: true,
            headers: {
                'X-Filesender-File-Size': file.size,
                'X-Filesender-Chunk-Offset': -1,
                'X-Filesender-Chunk-Size': blob.size
            }
        };
        
        if(onerror) opts.error = onerror;
        
        return this.post(file.transfer.authenticatedEndpoint('/file/' + file.id + '/chunk', file), blob, callback, opts);
    },
    
    /**
     * Put a file chunk
     * 
     * @param object file
     * @param blob chunk
     * @param int offset
     * @param callable progress
     * @param callable done
     * @param callable error
     */
    putChunk: function(file, blob, offset, progress, done, error, encrypted, encryption_details ) {
        var sz = blob.size;
        if( typeof blob == "string" ) {
            sz = blob.length;
        }
        var opts = {
            contentType: 'application/octet-stream',
            'accept-encoding': 'identity',
            rawdata: true,
            headers: {
                'X-Filesender-File-Size': file.size,
                'X-Filesender-Chunk-Offset': offset,
                'X-Filesender-Chunk-Size': sz,
                'X-Filesender-Encrypted': '1',
   	        'csrfptoken': filesender.client.getCSRFToken()
            },
            xhr: function() {
                var uxhr = $.ajaxSettings.xhr();
                
                if((typeof uxhr.upload != 'unknown') && uxhr.upload) uxhr.upload.addEventListener('progress', function(e) {
                    if(!e.lengthComputable) return;
                    if(progress) progress(e.loaded / e.total);
                }, false);
                
                return uxhr;
            }
        };

        var chunkid = Math.floor(offset / window.filesender.config.upload_chunk_size);
        var $this = this;
        if(encrypted){
            var cryptedBlob = null;
            var blobReader = window.filesender.crypto_blob_reader().createReader(blob, function(blob){
                // nothing todo here.. 
            });
            blobReader.blobSlice = blob;

            var origsz = blob.size;
            var response = new Response(blob);
            response.arrayBuffer().then(
                function(arrayBuffer){
                    arrayBuffer.size = origsz;
                    window.filesender.crypto_app().encryptBlob(
                        arrayBuffer,
                        chunkid,
                        encryption_details,
                        function(encrypted_blob) {
                            var result = $this.put(
                                file.transfer.authenticatedEndpoint(
                                    '/file/' + file.id + '/chunk/' + offset,
                                    file), encrypted_blob, done, opts);
                        }
                    );
                }
            );
        }else{
            var result = $this.put(file.transfer.authenticatedEndpoint('/file/' + file.id + '/chunk/' + offset, file), blob, done, opts);
        }
        
        if(error) opts.error = error;
    },
    
    /**
     * Signal file completion (along with checking data)
     * 
     * @param object file
     * @param object data check data
     * @param callable callback
     */
    fileComplete: function(file, data, callback, onerror) {
        var opts = {};
        if(onerror) opts.error = onerror;
        
        if(!data) data = {};
        data.complete = true;
        
        return this.put(file.transfer.authenticatedEndpoint('/file/' + file.id, file), {complete: true}, callback, opts);
    },
    
    /**
     * Signal transfer completion (along with checking data)
     * 
     * @param object transfer
     * @param object data check data
     * @param callable callback
     */
    transferComplete: function(transfer, data, callback, onerror) {
        var opts = {};
        if(onerror) opts.error = onerror;
        
        if(!data) data = {};
        data.complete = true;
        
        return this.put(transfer.authenticatedEndpoint('/transfer/' + transfer.id), data, callback, opts);
    },
    
    /**
     * Remind a transfer
     * 
     * @param mixed transfer id
     * @param callable callback
     */
    remindTransfer: function(id, callback) {
        return this.put('/transfer/' + id, {remind: true}, callback);
    },
    
    /**
     * Remind a recipient
     * 
     * @param mixed recipient id
     * @param callable callback
     */
    remindRecipient: function(id, callback) {
        return this.put('/recipient/' + id, {remind: true}, callback);
    },
    
    /**
     * Extend a transfer's expiry date
     * 
     * @param mixed transfer id
     * @param bool remind
     * @param callable callback
     */
    extendTransfer: function(id, remind, callback) {
        var data = {extend_expiry_date: true};
        if(remind) data.remind = true;
        
        return this.put('/transfer/' + id, data, callback);
    },

    extendObject: function(className, id, remind, callback) {
        var data = {extend_expiry_date: true};
        if(remind) data.remind = true;
        return this.put('/' + className + '/' + id, data, callback);
    },
    extendGuest: function( id, remind, callback ) {
        return this.extendObject('guest',id,remind, callback);
    },
    
    /**
     * Close a transfer
     * 
     * @param object transfer
     * @param callable callback
     */
    closeTransfer: function(transfer, callback, onerror) {
        var id = (typeof transfer == 'object') ? transfer.id : transfer;
        
        var opts = {};
        if(onerror) opts.error = onerror;
        
        return this.put('/transfer/' + id, {closed: true}, callback, opts);
    },

    /**
     * Decryption failed for a transfer, possibly by bad password
     * 
     * @param object transfer
     */
    decryptionFailedForTransfer: function(transfer) {
        var id = (typeof transfer == 'object') ? transfer.id : transfer;
        var opts = {};
        var callback = function() {} ;
        return this.put('/transfer/' + id, {decryptfailed: true}, callback, opts);
    },

    
    /**
     * Delete a transfer (admin / owner if in early statuses)
     * 
     * @param object transfer
     * @param bool nice should we notify owner/recipients about the deletion (close then delete or just delete)
     * @param callable callback
     */
    deleteTransfer: function(transfer, callback, onerror) {
        var id = transfer;
        var opts = {};
        if(onerror) opts.error = onerror;
        
        if(typeof transfer == 'object') {
            id = transfer.id;
            
        } else {
            transfer = {
                authenticatedEndpoint: function(res) {
                    return res;
                }
            };
        }
        
        return this.delete(transfer.authenticatedEndpoint('/transfer/' + id), callback, opts);
    },
    
    /**
     * Delete a file
     * 
     * @param object file
     * @param callable callback
     */
    deleteFile: function(file, callback, onerror) {
        var id = file;
        var opts = {};
        
        if(typeof file == 'object')
            id = file.id;
        
        if(onerror) opts.error = onerror;
        
        return this.delete('/file/' + id, callback, opts);
    },
    
    /**
     * Get recipient data
     * 
     * @param int recipient id
     * @param callable callback
     */
    getRecipient: function(id, callback) {
        return this.get('/recipient/' + id, callback);
    },
    
    /**
     * Add recipient to transfer
     * 
     * @param int transfer id
     * @param string email
     * @param callable callback
     */
    addRecipient: function(transfer_id, email, callback) {
        return this.post('/transfer/' + transfer_id + '/recipient', {recipient: email}, callback);
    },
    
    /**
     * Delete a recipient
     * 
     * @param object recipient
     * @param callable callback
     */
    deleteRecipient: function(recipient, callback, onerror) {
        var id = recipient;
        var opts = {};
        
        if(typeof recipient == 'object')
            id = recipient.id;
        
        if(onerror) opts.error = onerror;
        
        return this.delete('/recipient/' + id, callback, opts);
    },
    
    /**
     * Get a guest voucher
     * 
     * @param mixed guest voucher object or id
     * @param callable callback
     */
    getGuest: function(voucher, callback) {
        var id = voucher;
        
        if(typeof voucher == 'object')
            id = voucher.id;
        
        return this.get('/guest/' + id, callback);
    },
    
    /**
     * Create a guest voucher
     * 
     * @param string from sender email
     * @param string subject optionnal subject
     * @param string message optionnal message
     * @param string expires expiry date (yyyy-mm-dd or unix timestamp)
     * @param array options array of selected option identifiers
     * @param callable callback function to call with guest voucher path and guest voucher info once done
     */
    postGuest: function(from, recipient, subject, message, expires, options, callback) {
        return this.post('/guest', {
            from: from,
            recipient: recipient,
            subject: subject,
            message: message,
            expires: expires,
            options: options
        }, callback);
    },
    
    /**
     * Remind a guest
     * 
     * @param mixed guest id
     * @param callable callback
     */
    remindGuest: function(id, callback) {
        return this.put('/guest/' + id, {remind: true}, callback);
    },
    
    /**
     * Delete a guest voucher
     * 
     * @param mixed guest voucher object or id
     * @param callable callback
     */
    deleteGuest: function(voucher, callback) {
        var id = voucher;
        
        if(typeof voucher == 'object')
            id = voucher.id;
        
        return this.delete('/guest/' + id, callback);
    },
    
    /**
     * Override part of the config (if allowed)
     * 
     * @param object overrides
     * @param callable callback
     */
    overrideConfig: function(overrides, callback) {
        return this.put('/config', overrides, callback);
    },
    
    
    getFrequentRecipients: function(needle, callback) {
        return this.post('/user/@me',
                         {
                             property: 'frequent_recipients',
                             needle: needle
                         },
                         callback );
    },

    getOpenPGPPublicKey: function(emailaddr, callback) {
        return this.post('/user/@me',
                         {
                             property: 'openpgp_key',
                             email: emailaddr
                         },
                         callback );
    },

    testOpenPGPPublicKey: function(message,callback) {
        return this.post('/user/@me',
                         {
                             property: 'test_openpgp_key',
                             message: message
                         },
                         callback );
    },
    
    getTransferOption: function(id, option, token, callback) {
        return this.get('/transfer/' + id + '/options/' + option, callback, token ? {args: {token: token}} : undefined);
    },

    
    getTransferAuditlog: function(id, callback) {
        return this.get('/transfer/' + id + '/auditlog', callback);
    },
    
    getTransferAuditlogByEmail: function(id, filterid, callback) {
        var tailer = '';
        if( filterid ) {
            tailer = '/file/' + filterid;
        }
        return this.get('/transfer/' + id + '/auditlog/mail' + tailer, callback);
    },
    
    updateUserPreferences: function(preferences, callback) {
        return this.put('/user', preferences, callback);
    },
    updateUserIDPreferences: function(id, preferences, callback) {
        return this.put('/user/' + id + '/', preferences, callback);
    },
    serviceAUPAccept: function(version, callback) {
        var p = {};
        p['service_aup_version'] = version;
        
        var url = new URL(location);
        var tailer = '';
        if( url.searchParams.get("vid")) {
            tailer = '?vid=' + url.searchParams.get("vid");
        }
        return this.put('/principal'+tailer, p, callback );
    },
    
    getUserQuota: function(callback, onerror) {
        this.get('/user/@me/quota', callback, {ignore_authentication_required: true});
    },

    /**
     * Delete a user account
     * 
     * @param userid user to delete (can be '@me')
     * @param callable callback
     */
    deleteUserAccount: function(user, callback, onerror) {
        var id = user;
        var opts = {};
        
        if(typeof user == 'object')
            id = user.id;
        
        if(onerror) opts.error = onerror;
        
        return this.delete('/user/' + id, callback, opts);
    },

    
    createLocalDBAuthUser: function(username,password,callback,onerror) {
        var opts = {};
        if(onerror) opts.error = onerror;
        
        return this.post('/user/', {username:username, password:password}, callback, opts);
    },

    changeLocalAuthDBPassword: function(username,callback) {
        
        var prompt = window.filesender.ui.prompt('new password', function (pass) {
            filesender.client.createLocalDBAuthUser( username, pass, function() {
                filesender.ui.notify('success', lang.tr('password_updated'));
                if( callback ) {
                    callback(username);
                }
            });
            
        });
        // Add a field to the prompt
//        var input = $('<input type="text" class="wide" />').appendTo(prompt);
//        input.focus();
    },

    remindLocalAuthDBPassword: function(id, password, callback ) {
        return this.post('/user/' + id, {remind: true, username: id, password: password }, callback );
    },
    
    removeTransferOption: function(id, tropt, callback) {
        return this.put('/transfer/' + id, {'optionremove':true, option: tropt}, callback);
    },


    setUserSpecificExpireDaysForNewGuesst: function(id,callback) {

        var $this = this;
        var prompt = window.filesender.ui.prompt(
            lang.tr('set_user_guest_expiry_default_days'),
            function (obj) {
                var expires = $(this).find('input').val();

                var data = {guest_expiry_default_days: expires};
                return $this.put('/user/' + id, data, callback);
            });
        
        // Add a field to the prompt
        var input = $('<input type="text" class="wide" />').appendTo(prompt);
        $('<p>' + lang.tr('reset_per_user_guest_expire_setting') + '</p>').appendTo(prompt);
        input.focus();
    },

    sendVerificationCodeToYourEmailAddress: function(id, callback) {
        var m = window.location.search.match(/token=([0-9a-f-]+)/);
        var token = m[1];
        
        return this.put('/transfer/' + id + '/sendVerificationCodeToYourEmailAddress',
                        {sendVerificationCodeToYourEmailAddress: true, token: token},
                        callback);
    },

    checkVerificationCodeWithServer: function(id, pass, callback, ecb ) {
        var m = window.location.search.match(/token=([0-9a-f-]+)/);
        var token = m[1];
        return this.put('/transfer/' + id + '/checkVerificationCodeWithServer',
                        {checkVerificationCodeWithServer: pass, token: token},
                        callback, ecb );
    },

    token: null,
    page: null,

    verificationCodePassed: true,
    verificationCodeObjectThatTiggeredEvent: null,
    verificationCodePassedPopup: null,
    
    

    getTokenFromLocation: function() {
        // Get recipient token
        var m = window.location.search.match(/token=([0-9a-f-]+)/);
        this.token = m[1];
    },

    setToken: function( t ) {
        this.token = t;
    },
    
    setPage: function( v ) {
        this.page = v;
        var page = this.page;

        this.verificationCodePassed = true;
        this.verificationCodeObjectThatTiggeredEvent = null;
        this.verificationCodePassedPopup = null;
        if( window.filesender.config.download_verification_code_enabled ) {
            this.verificationCodePassed = false;
        }

        window.filesender.pbkdf2dialog.setup( true );


        if( window.filesender.config.download_verification_code_enabled ) {
            var transferid = $('.transfer').attr('data-id');
            var rid = $('.rid').attr('data-id');

            page.find('.verificationcodesendtoemail').button().on('click', function () {
                filesender.client.sendVerificationCodeToYourEmailAddress(
                    transferid,
                    function () {
                        window.filesender.ui.notify("info", lang.tr("email_sent"));
                    });
                return true;
            });
            page.find('.verificationcodesend').button().on('click', function () {
                var pass = $('#verificationcode').val();
                if (!pass.length) {
                    // nothing, could have just returned true here.
                } else {
                    try {
                        var options = {
                            error: function (e) {
                                if (e.message == 'rest_data_stale') {
                                    window.filesender.ui.alert("error", lang.tr("verification_code_is_too_old"));
                                    return;
                                }
                                filesender.ui.error(e);
                            }
                        };


                        filesender.client.checkVerificationCodeWithServer(
                            transferid, pass,
                            function (args) {
                                if (args.ok === true) {
                                    filesender.client.verificationCodePassed = true;
                                    $(".verify_email_to_download").dialog("close");

                                    var encrypted = filesender.client.verificationCodeObjectThatTiggeredEvent.closest('.file').attr('data-encrypted');
                                    var msg = "downloading";
                                    if (!encrypted) {
                                        window.filesender.ui.notify("info", lang.tr(msg));
                                    }
                                    filesender.client.verificationCodeObjectThatTiggeredEvent.click();
                                } else {
                                    window.filesender.ui.alert("error", lang.tr("verification_code_did_not_match"));
                                }
                            }
                            , options
                        );
                    } catch (exception) {
                    }
                }
                return true;
            });
        }


        var macos = navigator.platform.match(/Mac/);
        var linuxos = navigator.platform.match(/Linux/);
        if( !macos )
            $('.mac_archive_message').hide();

        // only worry the user with this banner if any files are encrypted
        // and they will not be able to download them.
        var transfer_is_encrypted = $('.transfer_is_encrypted').text()==1;
        if( transfer_is_encrypted && !filesender.supports.crypto )
            $('#encryption_description_not_supported').show();


        var button_zipdl = page.find('.archive_download_frame');
        var button_tardl = page.find('.archive_tar_download_frame');
        if( macos || linuxos ) {
            button_tardl.addClass('fs-button fs-button--success');
        } else {
            button_zipdl.addClass('fs-button fs-button--success');
        }
        
    },
    
    dl: function(ids, confirm, encrypted, progress, archive_format ) {
        if(typeof ids == 'string') ids = [ids];

        var page = this.page;

        // the dlcb handles starting the download for
        // all non encrypted downloads
        var dlcb = function(notify) {
            notify = notify ? '&notify_upon_completion=1' : '';
            return function() {
                if( archive_format ) {
                    console.log("Starting download using POST method...");
                    $('#dlarchivepostformat').attr( 'value', archive_format );
                    filesender.client.updateSelectedFilesForArchiveDownload();
                    $('#dlarchivepost').submit();
                } else {
                    filesender.ui.redirect(
                        filesender.config.base_path
                            + 'download.php?token=' + filesender.client.token
                            + '&archive_format=' + archive_format
                            + '&files_ids=' + ids.join(',') + notify);
                }
            };
        };
        if (!encrypted && confirm){
            filesender.ui.confirm(lang.tr('confirm_download_notify'), dlcb(true), dlcb(false), true);
        }else{
            if(encrypted){
                if(!filesender.supports.crypto ) {
                    filesender.ui.alert('error', lang.tr('file_encryption_description_disabled'));
                    return;
                }

                var crypto_app = window.filesender.crypto_app();

                if( window.filesender.config.use_streamsaver ) {
                    var streamsaverenabled = page.find('#streamsaverenabled').is(':checked');
                    crypto_app.disable_streamsaver = !streamsaverenabled;
                }
                console.log("download page has worked out if streamsaver should be disabled: " , crypto_app.disable_streamsaver );

                if( archive_format || ids.length > 1 ) {
                    //
                    // Stream encrypted files to browser and add the decrypted content
                    // into a zip64 file in the browser.
                    //
                    var onFileOpen = function( blobSink, fileid )
                    {
                        var progress = page.find("[data-id='" + fileid + "']").find('.downloadprogress');
                        progress.html("");
                        blobSink.progress = progress;

                        var overall = page.find(".archive_message");


                        var msg = lang.tr('encrypted_archive_download_overall_progress').r(
                            {
                                id: 0
                                , currentfilenumber:    blobSink.currentFileNumber+1
                                , totalfilestodownload: blobSink.totalFilesToDownload
                            }).out();
                        overall.html(msg);
                    };
                    var onFileClose = function( blobSink, fileid )
                    {
                        var progress = page.find("[data-id='" + fileid + "']").find('.downloadprogress');
                        progress.html(window.filesender.config.language.download_complete);

                    };
                    var onComplete = function( blobSink )
                    {
                        var overall = page.find(".archive_message");
                        overall.html(window.filesender.config.language.download_complete);
                    };

                    // generate zip in browser from decrypted files.
                    var selectedFiles = [];
                    var i = 0;
                    for(; i < ids.length; i++ ) {

                        var dataid  = "[data-id='" + ids[i] + "']";
                        var dataid0 = "[data-id='" + ids[0] + "']";
                        
                        var el                 = page.find(dataid);
                        var fileaead           = el.attr('data-fileaead');
                        var key_version        = el.attr('data-key-version');
                        var fileivcoded        = el.attr('data-fileiv');
                        var transferid         = $('.transfer').attr('data-id');
                        var chunk_size         = el.attr('data-chunk-size');
                        var crypted_chunk_size = el.attr('data-crypted-chunk-size');
                        
                        selectedFiles.push({
                            fileid:ids[i]
                            , filename           : el.attr('data-name')
                            , filesize           : el.attr('data-size')
                            , encrypted_filesize : el.attr('data-encrypted-size')
                            , mime               : el.attr('data-mime')
                            , key_version        : el.attr('data-key-version')
                            , salt               : el.attr('data-key-salt')
                            , password_version   : el.attr('data-password-version')
                            , password_encoding  : el.attr('data-password-encoding')
                            , password_hash_iterations : el.attr('data-password-hash-iterations')
                            , client_entropy     : el.attr('data-client-entropy')
                            , fileiv             : window.filesender.crypto_app().decodeCryptoFileIV(fileivcoded,key_version)
                            , fileaead           : fileaead.length?atob(fileaead):null
                            , transferid         : transferid
                        });

                        // clear any previous progress message
                        var progress = el.find('.downloadprogress');
                        progress.html("");
                    }
                    window.filesender.crypto_encrypted_archive_download = true;
                    crypto_app.decryptDownloadToZip( filesender.config.base_path
                                                     + 'download.php?token=' + filesender.client.token
                                                     + '&files_ids='
                                                     , transferid
                                                     , chunk_size
                                                     , crypted_chunk_size
                                                     , selectedFiles
                                                     , progress
                                                     , onFileOpen, onFileClose, onComplete
                                                   );


                }
                else
                {
                    // single file download
                    var dataid = "[data-id='" + ids[0] + "']";
                    var el = page.find(dataid);
                    var transferid               = $('.transfer').attr('data-id');
                    var chunk_size               = el.attr('data-chunk-size');
                    var crypted_chunk_size       = el.attr('data-crypted-chunk-size');
                    var filename                 = el.attr('data-name');
                    var filesize                 = el.attr('data-size');
                    var encrypted_filesize       = el.attr('data-encrypted-size');
                    var mime                     = el.attr('data-mime');
                    var key_version              = el.attr('data-key-version');
                    var salt                     = el.attr('data-key-salt');
                    var password_version         = el.attr('data-password-version');
                    var password_encoding        = el.attr('data-password-encoding');
                    var password_hash_iterations = el.attr('data-password-hash-iterations');
                    var client_entropy           = el.attr('data-client-entropy');
                    var fileiv                   = el.attr('data-fileiv');
                    var fileaead                 = el.attr('data-fileaead');
                    if( fileaead.length ) {
                        fileaead = atob(fileaead);
                    }

                    window.filesender.crypto_encrypted_archive_download = false;
                    crypto_app.decryptDownload( filesender.config.base_path
                                                + 'download.php?token=' + filesender.client.token
                                                + '&files_ids=' + ids.join(','),
                                                transferid, chunk_size, crypted_chunk_size,
                                                mime, filename, filesize, encrypted_filesize,
                                                key_version, salt,
                                                password_version, password_encoding,
                                                password_hash_iterations,
                                                client_entropy,
                                                window.filesender.crypto_app().decodeCryptoFileIV(fileiv,key_version),
                                                fileaead,
                                                progress );
                }
            }
            else
            {
                var notify = false;
                dlcb( notify ).call();
            }
        }
    },

    // Bind download buttons
    bindDownloadButton: function( n ) {
        var page = this.page;

        page.find( n ).button().on('click', function() {
            var id = $(this).closest('.file').attr('data-id');
            var encrypted = $(this).closest('.file').attr('data-encrypted');
            var progress = $(this).closest('.file').find('.downloadprogress');

            var transferid = $('.transfer').attr('data-id');

            filesender.client.verificationCodeObjectThatTiggeredEvent = $(this);
            if( !filesender.client.verificationCodePassed ) {
                filesender.client.verificationCodePassedPopup = filesender.ui.relocatePopup($(".verify_email_to_download"), { width: '30%' } );
            } else {
                filesender.client.getTransferOption(
                    transferid,
                    'enable_recipient_email_download_complete',
                    filesender.client.token,
                    function(dl_complete_enabled)
                    {
                        filesender.client.dl(id, dl_complete_enabled, encrypted, progress );
                    });
            }
            return false;
        });
    },

    bindDownloadArchive: function() {
        var page = this.page;
        var dlArchive = function( archive_format, button ) {
            var ids = [];
            page.find('.file[data-selected="1"]').each(function() {
                ids.push($(this).attr('data-id'));
            });

            if(!ids.length) { // No files selected, supose we want all of them
                page.find('.file').each(function() {
                    ids.push($(this).attr('data-id'));
                });
            }


            var transferid = $('.transfer').attr('data-id');
            var encrypted = $('.transfer_is_encrypted').text()==1;

            filesender.client.verificationCodeObjectThatTiggeredEvent = button;
            if( !filesender.client.verificationCodePassed ) {
                filesender.client.verificationCodePassedPopup = filesender.ui.relocatePopup($(".verify_email_to_download"), { width: '30%' } );
            } else {
                filesender.client.getTransferOption(transferid,
                                                    'enable_recipient_email_download_complete',
                                                    filesender.client.token,
                                                    function(dl_complete_enabled){
                    filesender.client.dl(ids, dl_complete_enabled, encrypted, null, archive_format );
                });
            }
            return false;
        };

        // Bind archive download button
        page.find('.archive .archive_download').on('click', function() {
            return dlArchive( 'zip', $(this) );
        });
        page.find('.archive .archive_tar_download').on('click', function() {
            return dlArchive( 'tar', $(this) );
        });
    },

    updateSelectedFilesForArchiveDownload: function()  {
        var page = this.page;
        var ids = [];
        page.find('.file[data-selected="1"]').each(function() {
            ids.push($(this).attr('data-id'));
        });
        var idlist = ids.join(',');
        $('.archivefileids').attr('value', idlist );
    },
    

    bindFileCheckButtons: function() {

        var page = this.page;

        // Bind file selectors
        page.find('.file input[type=checkbox]').on('change', function(e) {
            const el = $(this);
            const isChecked = e.target.checked;
            const f = el.closest('.file');
            f.attr('data-selected', isChecked ? '1' : '0');

            if (!isChecked) {
                const checkAll = $('#check-all');
                checkAll.prop("checked", false);
            }

            filesender.client.checkHideDownloadButtons();
            filesender.client.updateSelectedFileSize();

            e.stopPropagation();
        });

        // Bind global selector
        page.find('#check-all').on('change', function(e) {
            const isChecked = $('#check-all').is(":checked");
            const files = page.find('.file');
            files.attr('data-selected', isChecked ? '1' : '0');

            const checkBoxes = $('.file input[type=checkbox]');
            checkBoxes.prop("checked", isChecked);

            filesender.client.checkHideDownloadButtons();
            filesender.client.updateSelectedFileSize();

            e.stopPropagation();
        });
    },
    
    checkHideDownloadButtons: function() {
        var page = this.page;
        
        const ids = [];
        page.find('.file[data-selected="1"]').each(function() {
            ids.push($(this).attr('data-id'));
        });

        if(!ids.length) {
            $('.fs-download__actions').addClass('fs-download__actions--hide');
            $('.fs-download__zip64-info').addClass('fs-download__zip64-info--hide');
        } else {
            $('.fs-download__actions').removeClass('fs-download__actions--hide');
            $('.fs-download__zip64-info').removeClass('fs-download__zip64-info--hide');
        }
    },
    
    updateSelectedFileSize: function () {
        var page = this.page;

        let totalSize = 0;
        page.find('.file[data-selected="1"]').each(function() {
            totalSize = totalSize + parseInt($(this).attr('data-size'), 10);
        });

        const formattedTotalSize = filesender.client.formatBytes(totalSize);

        $('.fs-download__total-size span').text(formattedTotalSize);

        if (totalSize > 0) {
            $('.fs-download__total-size').addClass('fs-download__total-size--show');
        } else {
            $('.fs-download__total-size').removeClass('fs-download__total-size--show');
        }
    },

    formatBytes: function (bytes, decimals = 2) {
        if (!+bytes) return '0 Bytes'

        const k = 1024
        const dm = decimals < 0 ? 0 : decimals
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']

        const i = Math.floor(Math.log(bytes) / Math.log(k))

        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
    },    
};
