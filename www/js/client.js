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
        
        if(urlargs.length) resource += (resource.match(/\?/) ? '&' : '?') + urlargs.join('&');
        
        if(data) {
            var raw = options && ('rawdata' in options) && options.rawdata;
            
            if(!raw) data = JSON.stringify(data);
        }else data = undefined;
        
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
        
        var settings = {
            cache: false,
            contentType: 'application/json;charset=utf-8',
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
                    filesender.client.authentication_required = filesender.ui.popup(
                        lang.tr('authentication_required'),
                        filesender.config.logon_url ? {
                            logon: function() {
                                filesender.ui.redirect(filesender.config.logon_url);
                            }
                        } : {
                            ok: function() {}
                        },
                        {noclose: true}
                    );
                    filesender.client.authentication_required.text(lang.tr('authentication_required_explanation'));
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
            files.push({
                name: transfer.files[i].name,
                size: transfer.files[i].size,
                mime_type: transfer.files[i].mime_type,
                cid: transfer.files[i].cid
            });
        }
        return this.post(transfer.authenticatedEndpoint('/transfer'), {
            from: transfer.from,
            encryption: transfer.encryption,
            encryption_key_version: transfer.encryption_key_version,
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
        var opts = {
            contentType: 'application/octet-stream',
            rawdata: true,
            headers: {
                'X-Filesender-File-Size': file.size,
                'X-Filesender-Chunk-Offset': offset,
                'X-Filesender-Chunk-Size': blob.size,
                'X-Filesender-Encrypted': '1'
            },
            xhr: function() {
                uxhr = $.ajaxSettings.xhr();
                
                if((typeof uxhr.upload != 'unknown') && uxhr.upload) uxhr.upload.addEventListener('progress', function(e) {
                    if(!e.lengthComputable) return;
                    if(progress) progress(e.loaded / e.total);
                }, false);
                
                return uxhr;
            }
        };
        
        var $this = this;
        if(encrypted){
            var cryptedBlob = null;
            blobReader = window.filesender.crypto_blob_reader().createReader(blob, function(blob){
                // nothing todo here.. 
            });
            blobReader.blobSlice = blob;

            blobReader.readArrayBuffer(function(arrayBuffer){
                window.filesender.crypto_app().encryptBlob(
                    arrayBuffer,
                    encryption_details,
                    function(encrypted_blob) {
                        var result = $this.put(
                            file.transfer.authenticatedEndpoint(
                                '/file/' + file.id + '/chunk/' + offset,
                                file), encrypted_blob, done, opts);
                    }
                );
            });
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
        return this.get('/user/@me/frequent_recipients', callback, needle ? {args: {'filterOp[email][contains]': needle}} : undefined);
    },
    
    getTransferOption: function(id, option, token, callback) {
        return this.get('/transfer/' + id + '/options/' + option, callback, token ? {args: {token: token}} : undefined);
    },
    
    getTransferAuditlog: function(id, callback) {
        return this.get('/transfer/' + id + '/auditlog', callback);
    },
    
    getTransferAuditlogByEmail: function(id, callback) {
        return this.get('/transfer/' + id + '/auditlog/mail', callback);
    },
    
    getLegacyUploadProgress: function(key, callback, error) {
        return this.get('/legacyuploadprogress/' + key, callback, {error: error});
    },
    
    updateUserPreferences: function(preferences, callback) {
        return this.put('/user', preferences, callback);
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
    
};
