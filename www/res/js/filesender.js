if(!('filesender' in window)) window.filesender = {};

/**
 * Supports (updated at end of script)
 */
window.filesender.supports = {
    localStorage: false,
    workers: false,
    digest: false,
};

/**
 * AJAX webservice client
 */
window.filesender.client = {
    // REST service base path
    base_path: null,
    
    // Send a request to the webservice
    call: function(method, resource, data, callback, options) {
        if(!this.base_path) {
            var path = window.location.pathname;
            path = path.split('/');
            path.pop();
            path = path.join('/');
            this.base_path = path + '/rest.php';
        }
        
        var args = {};
        if(options && options.args) for(var k in options.args) args[k] = options.args[k];
        args._ = (new Date()).getTime(); // Defeat cache
        var urlargs = [];
        for(var k in args) urlargs.push(k + '=' + args[k]);
        
        if(data) {
            var raw = options && ('rawdata' in options) && options.rawdata;
            
            if(!raw) data = JSON.stringify(data);
        }else data = undefined;
        
        var settings = {
            cache: false,
            contentType: 'application/json;charset=utf-8',
            context: window,
            data: data,
            processData: false,
            dataType: 'json',
            error: this.error,
            success: callback,
            type: method.toUpperCase(),
            url: this.base_path + resource + '?' + urlargs.join('&')
        };
        
        for(var k in options) settings[k] = options[k];
        
        jQuery.ajax(settings);
    },
    
    // Error handler
    error: function(xhr, status, error) {
        var msg = xhr.responseText.replace(/^\s+/, '').replace(/\s+$/, '');
        
        var m = msg.match(/^([a-z0-9_-]+)\s+\(([a-z0-9]+)\)$/i);
        if(m) {
            filesender.ui.error(m[1], {logid: m[2]});
            return;
        }
        
        if(msg.match(/^([a-z0-9_-]+)$/i)) {
            filesender.ui.error(msg);
            return;
        }
        
        filesender.ui.rawError(msg);
    },
    
    get: function(resource, callback, options) {
        this.call('get', resource, undefined, callback, options);
    },
    
    post: function(resource, data, callback, options) {
        this.call('post', resource, data, function(data, status, xhr) {
            callback.call(this, xhr.getResponseHeader('Location'), data);
        }, options);
    },
    
    put: function(resource, data, callback, options) {
        this.call('put', resource, data, callback, options);
    },
    
    delete: function(resource, callback, options) {
        this.call('delete', resource, undefined, callback, options);
    },
    
    /**
     * Get public info about the Filesender instance
     */
    getInfo: function(callback) {
        this.get('/info', callback);
    },
    
    /**
     * Start a transfer
     * 
     * @param array files array of file objects with name, size and sha1 properties
     * @param array recipients array of recipients addresses
     * @param string subject optionnal subject
     * @param string message optionnal message
     * @param string expires expiry date (yyyy-mm-dd or unix timestamp)
     * @param array options array of selected option identifiers
     * @param callable callback function to call with transfer path and transfer info once done
     */
    postTransfer: function(files, recipients, subject, message, expires, options, callback) {
        this.post('/transfer', {
            files: files,
            recipients: recipients,
            subject: subject,
            message: message,
            expires: expires,
            options: options
        }, callback);
    },
    
    /**
     * Post a file chunk
     * 
     * @param object file
     * @param blob chunk
     * @param callable callback
     */
    postChunk: function(file, blob, callback) {
        var opts = {
            contentType: 'application/octet-stream',
            rawdata: true
        };
        if(filesender.config.chunk_upload_security == 'key') opts.args = {key: file.uid};
        
        this.post('/file/' + file.id + '/chunk', blob, callback, opts);
    },
    
    /**
     * Put a file chunk
     * 
     * @param object file
     * @param blob chunk
     * @param int offset
     * @param callable callback
     */
    putChunk: function(file, blob, offset, callback) {
        var opts = {
            contentType: 'application/octet-stream',
            rawdata: true
        };
        if(filesender.config.chunk_upload_security == 'key') opts.args = {key: file.uid};
        
        this.put('/file/' + file.id + '/chunk/' + offset, blob, callback, opts);
    },
    
    /**
     * Signal file completion (along with checking data)
     * 
     * @param object file
     * @param object data check data
     * @param callable callback
     */
    fileComplete: function(file, data, callback) {
        var opts = {};
        if(filesender.config.chunk_upload_security == 'key') opts.args = {key: file.uid};
        
        this.put('/file/' + file.id + '/chunk/complete', data, callback, opts);
    },
    
    /**
     * Signal transfer completion (along with checking data)
     * 
     * @param object transfer
     * @param object data check data
     * @param callable callback
     */
    transferComplete: function(transfer, data, callback) {
        var opts = {};
        if(filesender.config.chunk_upload_security == 'key') opts.args = {key: transfer.files[0].uid};
        
        this.put('/transfer/' + transfer.id + '/complete', data, callback, opts);
    },
    
    /**
     * Delete a transfer
     * 
     * @param object transfer
     * @param callable callback
     */
    deleteTransfer: function(transfer, callback) {
        var id = transfer;
        var opts = {};
        
        if(typeof transfer == 'object') {
            id = transfer.id;
            if(filesender.config.chunk_upload_security == 'key') opts.args = {key: transfer.files[0].uid};
        }
        
        this.delete('/transfer/' + id, callback, opts);
    },
};

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
                var i = $(this);
                var v = i.data('validator');
                if(!v) continue;
                
            });
            return ok;
        }
        
        // Element
    },
    
    /**
     * Nicely displays an error
     * 
     * @param string code error code (to be translated)
     * @param object data values for translation placeholders
     */
    error: function(code, data) {
        var msg = 'ERROR : ' + code;
        if(data && data.logid) {
            msg += ' (' + data.logid + ')';
            delete data.logid;
        }
        
        console.log(data);
        
        alert(msg + ', see console for details');
        
        return code;
    },
    
    rawError: function(text) {
        alert('RAW ERROR : ' + text);
    },
};

/**
 * Transfer pseudoclass
 */
window.filesender.transfer = function() {
    this.id = null;
    
    this.size = 0;
    this.files = [];
    this.recipients = [];
    this.subject = null;
    this.message = null;
    this.expires = null;
    this.options = [];
    
    this.time = 0;
    this.file_index = 0;
    this.status = 'running';
    this.onprogress = null;
    this.oncomplete = null;
    this.onerror = null;
    
    this.saveTemp = function() {
        if(!filesender.supports.localStorage) return;
        localStorage.setItem('transfer_' + this.id, JSON.stringify({
            size: this.size,
            files: this.files,
            recipients: this.recipients,
            subject: this.subject,
            message: this.message,
            expires: this.expires,
            options: this.options,
        }));
    };
    
    /**
     * Add a file to the file list
     * 
     * @param object file HTML input / FileList / File
     * 
     * @throws no_file_given
     * @throws max_html5_uploads_exceeded
     * @throws banned_extension
     * @throws max_html5_upload_size_exceeded
     */
    this.addFile = function(file, errorhandler) {
        if(!errorhandler) errorhandler = filesender.ui.error;
        
        if(!file)
            return errorhandler('no_file_given');
        
        if('parentNode' in file) // HTML file input
            file = file.files;
        
        if('length' in file) { // FileList
            if(!file.length)
                return errorhandler('no_file_given');
            
            for(var i=0; i<file.length; i++)
                this.addFile(file[i]);
            
            return;
        }
        
        if(!('type' in file))
            return errorhandler('no_file_given');
        
        var blob = file;
        var file = {
            id: null,
            key: null,
            blob: blob,
            size: blob.size,
            uploaded: 0,
            name: blob.name,
            type: blob.type
        };
        
        // Look for dup
        for(var i=0; i<this.files.length; i++) {
            if(this.files[i].name == file.name && this.files[i].size == file.size) {
                return errorhandler('duplicate_file', {name: file.name, size: file.size});
            }
        }
        
        if(this.files.length >= filesender.config.max_html5_uploads) {
            return errorhandler('max_html5_uploads_exceeded', {max: filesender.config.max_html5_uploads});
        }
        
        if(!/^[^\\\/:;\*\?\"<>|]+(\.[^\\\/:;\*\?\"<>|]+)*$/.test(file.name)) {
            return errorhandler('invalid_file_name', {max: filesender.config.max_html5_uploads});
        }
        
        var extension = file.name.split('.').pop();
        var banned = new RegExp('^(' + filesender.config.ban_extension.replace(',', '|') + ')$', 'g');
        if(extension.match(banned)) {
            return errorhandler('banned_extension', {extension: extension, banned: filesender.config.ban_extension});
        }
        
        if(this.size + file.size > filesender.config.max_html5_upload_size) {
            return errorhandler('max_html5_upload_size_exceeded', {size: file.size, max: filesender.config.max_html5_upload_size});
        }
        
        this.size += file.size;
        
        this.files.push(file);
    };
    
    /**
     * Add a recipient
     * 
     * @param string email address
     * 
     * @throws 
     */
    this.addRecipient = function(email, errorhandler) {
        if(!errorhandler) errorhandler = filesender.ui.error;
        
        if(this.recipients.length >= filesender.config.max_email_recipients) {
            return errorhandler('max_email_recipients_exceeded', {max: filesender.config.max_email_recipients});
        }
        
        this.recipients.push(email);
    };
    
    /**
     * Report progress
     * 
     * @param object file
     * @param bool complete is file done
     */
    this.reportProgress = function(file, complete) {
        if(filesender.config.log) {
            if(complete) {
                console.log('File ' + file.name + ' (' + file.size + ' bytes) uploaded');
            }else{
                console.log('Uploading ' + file.name + ' (' + file.size + ' bytes) : ' + (100 * file.uploaded / file.size).toFixed(2) + '%');
            }
        }
        
        if(complete) {
            var transfer = this;
            filesender.client.fileComplete(file, undefined, function(data) {
                if(transfer.onprogress) transfer.onprogress(file, true);
            });
        }else if(this.onprogress) {
            this.onprogress(file, complete);
        }
    };
    
    /**
     * Report transfer complete
     */
    this.reportComplete = function() {
        var time = (new Date()).getTime() - this.time; // ms
        
        if(filesender.config.log) {
            console.log('Transfer ' + this.id + ' (' + this.size + ' bytes) complete, took ' + (time / 1000) + 's');
        }
        
        var transfer = this;
        filesender.client.transferComplete(this, undefined, function(data) {
            if(transfer.oncomplete) transfer.oncomplete(time);
        });
    };
    
    /**
     * Report transfer error
     */
    this.reportError = function(code, details) {
        if(filesender.config.log) {
            console.log('Transfer ' + this.id + ' (' + this.size + ' bytes) failed');
        }
        
        if(this.onerror) {
            this.onerror(code, details);
        }else{
            filesender.ui.error(code, details);
        }
    };
    
    /**
     * Start upload
     */
    this.start = function(errorhandler) {
        if(!errorhandler) errorhandler = filesender.ui.error;
        
        // Redo sanity checks
        
        if(this.files.length >= filesender.config.max_html5_uploads) {
            return errorhandler('max_html5_uploads_exceeded', {max: filesender.config.max_html5_uploads});
        }
        
        if(this.size > filesender.config.max_html5_upload_size) {
            return errorhandler('max_html5_upload_size_exceeded', {size: file.size, max: filesender.config.max_html5_upload_size});
        }
        
        // Prepare files
        var files_dfn = [];
        for(var i=0; i<this.files.length; i++) files_dfn.push({
            name: this.files[i].name,
            size: this.files[i].size
        });
        
        this.time = (new Date()).getTime();
        
        var transfer = this;
        filesender.client.postTransfer(files_dfn, this.recipients, this.subject, this.message, this.expires, this.options, function(path, data) {
            transfer.id = data.id;
            
            for(var i=0; i<transfer.files.length; i++) {
                for(var j=0; j<data.files.length; j++) {
                    if(
                        (data.files[j].name == transfer.files[i].name) &&
                        (data.files[j].size == transfer.files[i].size)
                    ) {
                        transfer.files[i].id = data.files[j].id;
                        transfer.files[i].uid = data.files[j].uid;
                    }
                }
                
                if(!transfer.files[i].id) return errorhandler('file_not_in_response', {file: transfer.files[i]});
            }
            
            // Start uploading chunks
            if(filesender.config.terasender_enabled && filesender.supports.workers) {
                filesender.terasender.start(transfer);
            }else{
                // Chunk by chunk upload
                transfer.uploadChunk();
            }
        });
    };
    
    /**
     * Pause upload
     */
    this.pause = function() {
        if(this.status != 'running') return;
        
        this.status = 'paused';
        
        if(filesender.config.terasender_enabled && filesender.supports.workers)
            filesender.terasender.pause();
    };
    
    /**
     * Restart upload
     */
    this.restart = function() {
        if(this.status != 'paused') return;
        
        this.status = 'running';
        
        if(filesender.config.terasender_enabled && filesender.supports.workers)
            filesender.terasender.restart();
    };
    
    /**
     * Stop upload
     */
    this.stop = function(callback) {
        this.status = 'stopped';
        
        if(filesender.config.terasender_enabled && filesender.supports.workers)
            filesender.terasender.stop();
        
        window.setTimeout(function() { // Small delay to let workers stop
            filesender.client.deleteTransfer(this, callback);
        }, 1000);
    };
    
    /**
     * Chunk by chunk upload
     */
    this.uploadChunk = function() {
        if(this.status == 'stopped') return;
        
        var transfer = this;
        if(this.status == 'paused') {
            window.setTimeout(function() {
                transfer.uploadChunk();
            }, 500);
            return;
        }
        
        var file = this.files[this.file_index];
        
        var slicer = file.blob.slice ? 'slice' : (file.blob.mozSlice ? 'mozSlice' : (file.blob.webkitSlice ? 'webkitSlice' : 'slice'));
        
        var offset = file.uploaded;
        var blob = file.blob[slicer](offset, offset + filesender.config.upload_chunk_size);
        
        file.uploaded += filesender.config.upload_chunk_size;
        
        var last = file.uploaded >= file.size;
        if(last) this.file_index++;
        
        filesender.client.putChunk(file, blob, offset, function() {
            if(!last || transfer.file_index < transfer.files.length) {
                if(last) { // File done
                    transfer.reportProgress(file, true);
                }else{
                    transfer.reportProgress(file);
                }
                
                transfer.uploadChunk();
            }else{
                transfer.status = 'done';
                transfer.reportComplete();
            }
        });
    };
};

/**
 * Pending transfer check
 * 
 * @param function callback will be given another callback which must be called with decision ("resume", "ignore", "clear")
 */
window.filesender.checkPendingTransfer = function(callback) {
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
};

window.filesender.supports.localStorage = typeof(localStorage) !== 'undefined';

window.filesender.supports.workers = typeof(Worker) !== 'undefined';

window.filesender.supports.digest = typeof(FileReader) !== 'undefined';
