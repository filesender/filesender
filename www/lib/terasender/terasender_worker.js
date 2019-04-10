/**
 * Part of the Filesender software.
 * See http://filesender.org
 */

importScripts(
	'../../filesender-config.js.php',
	'../../js/crypter/crypto_common.js',
	'../../js/crypter/crypto_blob_reader.js',
	'../../js/crypter/crypto_app.js'
);


var terasender_worker = {
    /**
     * Worker properties
     */
    id: null, // Worker id
    
    job: { // Current job
        file: null, // File data (id, key, size, blob)
        chunk: null, // Chunk coordinates (start, end)
        fine_progress: 0
    },
    
    /**
     * Security token (XSRF protection)
     */
    security_token: null,
    
    /**
     * Maintenance flag / timer
     */
    maintenance: null,

    /**
     * Number of retries that have happened for this chunk
     */
    send_attempts: 0,
    
    /**
     * Start the worker
     */
    start: function() {
        this.requestJob();
    },
    
    /**
     * Request job from driver
     */
    requestJob: function() {
        this.log('Requesting job');
        this.sendCommand('requestJob');
    },
    
    /**
     * Execute a given job
     * 
     * @param object job job to execute
     */
    executeJob: function(job) {
        if(job) {
            if('file' in job) { // Switch files
                this.job.file = job.file;
            }
            
            if('security_token' in job) {
                this.security_token = job.security_token;
            }
            
            this.job.chunk = job.chunk;

            if('encryption' in job) {
                this.job.encryption = job.encryption;
            }
        }
        
        if(!this.job.file) {
            this.error({message: 'file_missing'});
            return;
        }
        
        if(!this.job.chunk.start)
            this.job.chunk.start = 0;
        
        if(
            !this.job.chunk.end ||
            isNaN(this.job.chunk.end) ||
            isNaN(this.job.chunk.start) ||
            (this.job.chunk.end <= this.job.chunk.start)
        ) {
            this.error({message: 'bad_chunk_boundaries'});
            return;
        }
        
        var file = this.job.file;
        
        this.log('Starting job file:' + file.id + '[' + this.job.chunk.start + '...' + this.job.chunk.end + ']');
        
        var slicer = file.blob.slice ? 'slice' : (file.blob.mozSlice ? 'mozSlice' : (file.blob.webkitSlice ? 'webkitSlice' : 'slice'));
        
        var blob = file.blob[slicer](this.job.chunk.start, this.job.chunk.end);

        var xhr = this.createXhr();

        var worker = this;
        
        if((typeof xhr.upload != 'unknown') && xhr.upload) xhr.upload.onprogress = function(e) { //IE11 seems to skip this only in workers
            if(!e.lengthComputable) return;
            worker.reportProgress(e.loaded, e.total);
        };
        
        xhr.onreadystatechange = function() {
            worker.uploadRequestChange(xhr);
        };
        
        xhr.ontimeout = function() {
            worker.timeout();
        };
        xhr.timeout = window.filesender.config.terasender_worker_xhr_timeout;
        
        var url = file.endpoint.replace('{offset}', this.job.chunk.start);
        xhr.open('PUT', url, true); // Open a request to the upload endpoint
        
        xhr.setRequestHeader('Content-Disposition', 'attachment; name="chunk"'); 
        xhr.setRequestHeader('Content-Type', 'application/octet-stream');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-Filesender-File-Size', file.size);
        xhr.setRequestHeader('X-Filesender-Chunk-Offset', this.job.chunk.start);
        xhr.setRequestHeader('X-Filesender-Chunk-Size', blob.size);
        xhr.setRequestHeader('X-Filesender-Security-Token', this.security_token);
        
        try {
            
		if (job.encryption) { //MD
			var cryptedBlob = null;
			var $this = this;
			blobReader = window.filesender.crypto_blob_reader().createReader(blob, function(blob){});
			blobReader.blobSlice = blob;
			blobReader.readArrayBuffer(function(arrayBuffer){
			    window.filesender.crypto_app().encryptBlob(
                                arrayBuffer,
                                job.encryption_details,
                                function (encrypted_blob) {
				    xhr.setRequestHeader('X-Filesender-Encrypted', '1');
				    xhr.send(encrypted_blob);
				});
			});
		} else {
			xhr.send(blob);
		}
        } catch(err) {
            this.error({message: 'source_file_not_available', details: {job: this.job}});
        }
    },
    
    /**
     * Xhr factory
     */
    createXhr: function() {
        try { return new XMLHttpRequest(); } catch(e) {}
        try { return new ActiveXObject('Msxml2.XMLHTTP'); } catch (e) {}
        return null;
    },
    
    /**
     * Report progress of current job
     */
    reportProgress: function(loaded,total) {
        var now = (new Date()).getTime();
        var ratio = loaded/total;
        if (ratio < 0.95 && this.progress_reported && this.progress_reported > (now - 300))
            return; // No need to report progress more than 1 time per 300ms (especially if fine_progress)
        
        this.progress_reported = now;
        
        this.log('Job file:' + this.job.file.id + '[' + this.job.chunk.start + '...' + this.job.chunk.end + '] is ' + (100 * ratio).toFixed(1) + '% done');
        if (this.job.encryption) { 
            this.job.fine_progress = Math.floor(ratio * (this.job.chunk.end - this.job.chunk.start));
        } else {
            this.job.fine_progress = loaded;
        }
        this.sendCommand('jobProgress', this.job);
    },
    
    /**
     * Report that current job is complete
     */
    reportDone: function() {
        this.log('Executed job file:' + this.job.file.id + '[' + this.job.chunk.start + '...' + this.job.chunk.end + ']');
        this.sendCommand('jobExecuted', this.job);
    },
    
    /**
     * Report that security token changed
     */
    reportSecurityTokenChange: function(new_security_token) {
        this.log('Security token changed, propagating');
        this.sendCommand('securityTokenChanged', new_security_token);
    },

    testing_uploadRequestChange_xhr_fail_on_third_finally_succeed: function(xhr) {
        var worker = this;
        var upload_chunk_size = window.filesender.config.upload_chunk_size;
        var ret = xhr.status;
        
        this.log('testing_xhr_fail_on_third( called )');
        
        if( xhr.status == 200
            && this.send_attempts < (window.filesender.config.terasender_worker_max_chunk_retries-1)
            && worker.job
            && worker.job.chunk
            && worker.job.chunk.start==(3*upload_chunk_size))
        {
            ret = 0;
            this.log('force the status to zero for testing! XXX this.send_attempts ' + this.send_attempts);
        }
        return ret;
    },

    
    
    /**
     * Upload xhr onreadystatechange callback
     * 
     * @param object xhr
     */
    uploadRequestChange: function(xhr){
        if(xhr.readyState != 4) return; // Not a progress update

        var status = xhr.status;

        // call testing mutilation function if set
        {
            var fname = window.filesender.config.testing_terasender_worker_uploadRequestChange_function_name;
            if( fname.length && fname.startsWith('testing_uploadRequestChange_')) {
                status = this[fname](xhr);
            }
        }
        
        // Did security token change ?
        var new_security_token = xhr.getResponseHeader('X-Filesender-Security-Token');
        if(new_security_token && new_security_token != this.security_token) {
            this.security_token = new_security_token;
            this.reportSecurityTokenChange(new_security_token);
        }
        
        // Ignore 40x and 50x if undergoing maintenance
        if(status >= 400 && this.maintenance) {
            var worker = this;
            this.maintenance = setTimeout(function() {
                worker.executeJob();
            }, 60 * 1000);
            return;
        }

        var worker = this;
        
        if(status == 200) { // All went well
            if(this.maintenance) {
                this.log('Webservice maintenance mode ended, pending chunk has been uploaded');
                clearTimeout(this.maintenance);
                this.maintenance = null;
                this.sendCommand('maintenance', false);
            }
            
            this.reportDone();
        }else if(status == 0) { // Request cancelled (browser refresh or such)

            this.send_attempts++;
            
            setTimeout(function() {
                if( worker.send_attempts < window.filesender.config.terasender_worker_max_chunk_retries ) {
                    // try, try again
                    worker.log('worker attempt ' + worker.send_attempts + ' to retry chunk upload at offset ' + worker.job.chunk.start);
                    worker.executeJob(worker.job);
                }
                else {
                    // Let the manager know something has really hit the fan
                    worker.sendCommand('jobFailed', worker.job);
                }
            }, 1000);

            // We have scheduled upload halt
            // or another attempt already
            return;
            
        }else{ // We have an error
            var msg = xhr.responseText.replace(/^\s+/, '').replace(/\s+$/, '');
            
            try {
                var error = JSON.parse(msg);
                
                if(error.message == 'undergoing_maintenance') {
                    if(!this.maintenance) {
                        this.log('Webservice entered maintenance mode, keeping chunk to upload it when maintenance ends');
                        this.sendCommand('maintenance', true);
                    }
                    
                    var worker = this;
                    this.maintenance = setTimeout(function() {
                        worker.executeJob();
                    }, 60 * 1000);
                    
                    return;
                }
                
                if(!error.details) error.details = {};
                error.details.job = this.job;
                this.error(error);
            } catch(e) {
                this.error({message: msg, details: {job: this.job}});
            }
        }
    },
    
    /**
     * Timeout callback
     */
    timeout: function() {
        if(this.maintenance) { // If under maintenance retrigger job after a while
            var worker = this;
            this.maintenance = setTimeout(function() {
                worker.executeJob();
            }, 60 * 1000);
        }
        
        this.error({message: 'chunk_upload_timeout', details: {job: this.job}});
    },
    
    /**
     * Log to driver
     * 
     * @param string message
     */
    log: function(message) {
        this.sendCommand('log', message);
    },
    
    /**
     * Report error to driver
     * 
     * @param string code error code
     * @param string details
     */
    error: function(error) {
        this.sendCommand('error', error);
    },
    
    /**
     * Post message to driver
     * 
     * @param string command
     * @param object data
     */
    sendCommand: function(command, data) {
        postMessage({
            command: command,
            worker_id: this.id,
            data: data
        });
    },
    
    /**
     * Handle messages from driver
     * 
     * @param object message
     */
    onMessage: function(message) {
        var command = message.command;
        var data = message.data;
        
        if(!command) return;
        
        switch(command) {
            case 'start' :
                this.id = data;
                this.requestJob();
                break;
            
            case 'executeJob' :
                // setting this has to be here rather than in executeJob()
                // because the retry handling code also calls executeJob()
                this.send_attempts = 0;
                this.executeJob(data);
                break;
            
            case 'comeBackLater' :
                var worker = this;
                setTimeout(function() {
                    worker.requestJob();
                }, 500);
                break;
            
            case 'done' :
                this.log('closing');
                close();
                break;
        }
    },
};

/**
 * Register message handler
 */
self.onmessage = function(e) {
    terasender_worker.onMessage(e.data);
}
