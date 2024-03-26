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
    
    getLegacyUploadProgress: function(key, callback, error) {
        return this.get('/legacyuploadprogress/' + key, callback, {error: error});
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
    
};
