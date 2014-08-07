if(!('filesender' in window)) window.filesender = {};

/**
 * Supports (updated at end of script)
 */
window.filesender.supports = {
    localStorage: false,
    HTML5WebWorkers: false,
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
        this.post('/file/' + file.id + '/chunk', blob, callback, {
            args: {key: file.uid},
            contentType: 'application/octet-stream',
            rawdata: true
        });
    },
};

/**
 * UI methods
 */
window.filesender.ui = {
    /**
     * Nicely displays an error
     * 
     * @param string code error code (to be translated)
     * @param object data values for translation placeholders
     */
    error: function(code, data) {
        var msg = 'ERROR : ' + code;
        if(data.logid) {
            msg += ' (' + data.logid + ')';
            delete data.logid;
        }
        
        console.log(data);
        
        alert(msg + ', see console for details');
        
        return code;
    },
    
    rawError: function(text) {
        alert('RAW ERROR : ' + text);
    }
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
    
    this.file_index = 0;
    this.onprogress = null;
    this.oncomplete = null;
    
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
     * Add a file (html input or saved file) the the file list
     * 
     * @param object htmlfile HTML file object
     * 
     * @throws max_html5_uploads_exceeded
     * @throws banned_extension
     * @throws max_html5_upload_size_exceeded
     */
    this.addFile = function(file) {
        if('parentNode' in file) { // HTML file input
            file = file.files;
        }
        
        if('length' in file && 'type' in file[0]) { // FileList (blob)
            var blob = file[0];
            var file = {
                id: null,
                key: null,
                blob: blob,
                size: blob.size,
                uploaded: 0,
                name: blob.name,
                type: blob.type
            };
        }
        
        // Look for dup
        for(var i=0; i<this.files.length; i++) {
            if(this.files[i].name == file.name && this.files[i].size == file.size) {
                throw filesender.ui.error('duplicate_file', {name: file.name, size: file.size});
            }
        }
        
        if(this.files.length >= filesender.config.max_html5_uploads) {
            throw filesender.ui.error('max_html5_uploads_exceeded', {max: filesender.config.max_html5_uploads});
        }
        
        var extension = file.name.split('.').pop();
        var banned = new RegExp('^(' + filesender.config.ban_extension.replace(',', '|') + ')$', 'g');
        if(extension.match(banned)) {
            throw filesender.ui.error('banned_extension', {extension: extension, banned: filesender.config.ban_extension});
        }
        
        if(this.size + file.size > filesender.config.max_html5_upload_size) {
            throw filesender.ui.error('max_html5_upload_size_exceeded', {size: file.size, max: filesender.config.max_html5_upload_size});
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
    this.addRecipient = function(email) {
        if(this.recipients.length >= filesender.config.max_email_recipients) {
            throw filesender.ui.error('max_email_recipients_exceeded', {max: filesender.config.max_email_recipients});
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
        
        if(this.onprogress) this.onprogress(file, complete);
    };
    
    /**
     * Report transfer complete
     */
    this.reportComplete = function(file, complete) {
        if(filesender.config.log) {
            console.log('Transfer ' + this.id + ' (' + this.size + ' bytes) complete');
        }
        
        if(this.oncomplete) this.oncomplete();
    };
    
    /**
     * Start upload
     */
    this.start = function() {
        // Redo sanity checks
        
        if(this.files.length >= filesender.config.max_html5_uploads) {
            throw filesender.ui.error('max_html5_uploads_exceeded', {max: filesender.config.max_html5_uploads});
        }
        
        if(this.size > filesender.config.max_html5_upload_size) {
            throw filesender.ui.error('max_html5_upload_size_exceeded', {size: file.size, max: filesender.config.max_html5_upload_size});
        }
        
        // Prepare files
        var files_dfn = [];
        for(var i=0; i<this.files.length; i++) files_dfn.push({
            name: this.files[i].name,
            size: this.files[i].size
        });
        
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
                
                if(!transfer.files[i].id) throw filesender.ui.error('file_not_in_response', {file: transfer.files[i]});
            }
            
            // Start uploading chunks
            if(filesender.config.terasender_enabled) {
                
            }else{
                // Chunk by chunk upload
                transfer.uploadChunk();
            }
            
            //config.terasender_enabled
            //supports.HTML5WebWorkers
        });
    };
    
    /**
     * Chunk by chunk upload
     */
    this.uploadChunk = function() {
        var file = this.files[this.file_index];
        
        var blob = file.blob.slice(file.uploaded, file.uploaded + filesender.config.upload_chunk_size);
        
        file.uploaded += filesender.config.upload_chunk_size;
        
        var last = file.uploaded >= file.size;
        if(last) this.file_index++;
        
        var transfer = this;
        filesender.client.postChunk(file, blob, function() {
            if(!last || transfer.file_index < transfer.files.length) {
                if(last) { // File done
                    transfer.reportProgress(file, true);
                }else{
                    transfer.reportProgress(file);
                }
                
                transfer.uploadChunk();
            }else{
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

window.filesender.supports.HTML5WebWorkers = typeof(Worker) !== 'undefined';

window.filesender.supports.digest = typeof(FileReader) !== 'undefined';
