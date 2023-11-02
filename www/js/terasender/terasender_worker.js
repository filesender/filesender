/**
 * Part of the Filesender software.
 * See http://filesender.org
*/




/**
 * We have to use a try/catch here so we can report failure
 * back to the master of this worker. Otherwise they will not
 * be notified that we have failed to start if there is a network
 * outage at startup.
 */
try {
    importScripts(
	'../../filesender-config.js.php',
	'../../js/crypter/crypto_common.js',
	'../../js/crypter/crypto_blob_reader.js',
	'../../js/crypter/crypto_app.js'
    );
}
catch( e ) {
    postMessage({
        command: 'error',
        worker_id: -1,
        data: {message: 'worker_failed_to_start' + e }
    });
}




function isIE11()
{
    if(navigator.userAgent.indexOf('MSIE')!==-1
       || navigator.appVersion.indexOf('Trident/') > -1)
    {
        return true;
    }
    return false;
}
function notIE11()
{
    return !isIE11();
}

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

    // csrf token
    csrfptoken: null,
    
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

    mode_values: {
        sender: 1,
        receiver: 2
    },
    mode: 1,
    isReceiver: function(job) {
        return this.mode == this.mode_values.receiver;
    },
    crypto_app: null,
    
    
    /**
     * Execute a given job
     * 
     * @param object job job to execute
     */
    executeJob: function(job) {

        if(job) {
            var worker = this;
            worker.mode = job.mode;
            
            if(job.mode == this.mode_values.receiver ) {

                this.job = job;
                var $this = this;
                var chunkid            = job.chunkid;
                var encryption_details = job.encryption_details;
                if( !this.crypto_app ) {
                    this.crypto_app = window.filesender.crypto_app();
                }

                this.log('ts.worker(1) Starting receiver job for chunkid:' + chunkid + " of " + encryption_details.chunkcount );
                
                // Decrypt the contents of the file
                var oReq = this.createXhr();
                
                oReq.open("GET", job.link, true);
                oReq.responseType = "arraybuffer";
                var chunksz     = 1 * this.crypto_app.crypto_chunk_size;
                var startoffset = 1 * (chunkid * chunksz);
                var endoffset   = 1 * (chunkid * chunksz + (1*this.crypto_app.upload_crypted_chunk_size)-1);
                var legacyChunkPadding = 0;
                oReq.setRequestHeader('X-FileSender-Encrypted-Archive-Download', job.crypto_encrypted_archive_download );
                
                //
                // There are some extra things to do for streaming legacy type files
                //
                if( encryption_details.key_version == this.crypto_app.crypto_key_version_constants.v2018_importKey_deriveKey ||
                    encryption_details.key_version == this.crypto_app.crypto_key_version_constants.v2017_digest_importKey )
                {
                    legacyChunkPadding = 1;
                }

                //
                // Handle last chunk details, some offsets might need to change slightly
                //
                if( chunkid == encryption_details.chunkcount ) {
                    var padding = (1*this.crypto_app.upload_crypted_chunk_size) - (1* this.crypto_app.crypto_chunk_size);
                    var blockPad = 32;

                    this.log("ts.worker executeJob(last chunk offset adjustment) "
                             + " legacyPadding " + legacyChunkPadding
                             + " ccs "  + this.crypto_app.crypto_chunk_size
                             + " uccs " + this.crypto_app.upload_crypted_chunk_size
                             + " soffset " + startoffset
                             + " soffsetcc " + (1 * (chunkid * this.crypto_app.upload_crypted_chunk_size))
                            );
                    this.log("ts.worker executeJob(last chunk offset adjustment) "
                             + " eoffset " + endoffset
                             + " fs " + encryption_details.filesize
                             + " efs " + encryption_details.encrypted_filesize
                            );
                    
                    endoffset = (1*encryption_details.filesize) + blockPad - 1;
                    if( encryption_details.key_version < 2 ) {
                        endoffset -= 4;
                    }
                    if( !chunkid ) {
                        endoffset = encryption_details.encrypted_filesize - 1;
                    }
                    if( chunkid > 0 && legacyChunkPadding ) {

                        var fs = (1*encryption_details.filesize);
                        fs = fs % chunksz;
                        if( fs == 0 ) {
                            fs = chunksz;
                        }
                        
                        endoffset = 1 * (chunkid * chunksz + fs + blockPad - (fs%16)) -1;
                        this.log("ts.worker executeJob(legacyPadding) new eoffset " + endoffset );
                        
                    }

                    this.log("ts.worker executeJob(adjustments done) "
                             + " eoffset " + endoffset
                             + " padding " + padding );

                    if( job.crypto_encrypted_archive_download_fileidlist ) {
                        oReq.setRequestHeader('X-FileSender-Encrypted-Archive-Contents', job.crypto_encrypted_archive_download_fileidlist );
                        job.crypto_encrypted_archive_download_fileidlist = '';
                    }
                }
                
                var brange = 'bytes=' + startoffset + '-' + endoffset;
                oReq.setRequestHeader('Range', brange);

                
                //Download progress
                oReq.addEventListener("progress", function(evt) {
                    $this.log( "ts.worker(progress) chunkid " + chunkid
                               + " loaded " + evt.loaded
                               + " of total " + evt.total );
                    worker.reportProgress(evt.loaded, evt.total);
                }, false );

                var transferError = function (error) {
                    this.error({message: error.message, details: {job: this.job}});
                };

                //
                // When bad things happen
                //
                oReq.addEventListener("error", function(evt) {
                    $this.log("worker error");
                    this.log("oReq error: " + evt.toString());
                    transferError(lang.tr('download_error').out());
                    return;
                });
                oReq.addEventListener("abort", function(evt) {
                    $this.log("worker abort");
                    transferError(lang.tr('download_error_abort').out());
                    return;
                });

                window.alert = function(msg) {
                    $this.error({message: msg, details: {job: $this.job}});
                }
                $this.crypto_app.alertcb = window.alert;

                //
                // Primary path
                // When we get the chunk data (or an XHR error)
                //
                oReq.onload = function (oEvent) {
                    
                    // check for a redirect containing and error and halt if so
                    if( $this.crypto_app.handleXHRError( oReq, job.link, 'file_encryption_wrong_password' )) {
                        return;
                    }

                    //
                    // call decryptBlob to handle this chunk and pass a "next"
                    // function to decryptBlob which will call us for the next chunk.
                    var arrayBuffer = new Uint8Array(oReq.response);
                    setTimeout(function(){

                        var sliced = window.filesender.crypto_blob_reader().sliceForDownloadBuffers(arrayBuffer);
                        var encryptedChunk = window.filesender.crypto_common().separateIvFromData(sliced[0]);

                        $this.job.encryptedChunk = encryptedChunk;
                        worker.uploadRequestChange(oReq);
                        
                    }, 20);
                };
                
                // start downloading this chunk
                oReq.send();
                return;
            }
        }
        
        if(job) {
            if('file' in job) { // Switch files
                this.job.file = job.file;
            }
            
            if('security_token' in job) {
                this.security_token = job.security_token;
            }
            if('csrfptoken' in job) {
                this.csrfptoken = job.csrfptoken;
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
        if( notIE11()) {
            xhr.timeout = window.filesender.config.terasender_worker_xhr_timeout;
        }
        
        var url = file.endpoint.replace('{offset}', this.job.chunk.start);
        xhr.open('PUT', url, true); // Open a request to the upload endpoint
        
        xhr.setRequestHeader('Content-Disposition', 'attachment; name="chunk"'); 
        xhr.setRequestHeader('Content-Type', 'application/octet-stream');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-Filesender-File-Size', file.size);
        xhr.setRequestHeader('X-Filesender-Chunk-Offset', this.job.chunk.start);
        xhr.setRequestHeader('X-Filesender-Chunk-Size', blob.size);
        xhr.setRequestHeader('X-Filesender-Security-Token', this.security_token);
        xhr.setRequestHeader('csrfptoken', this.csrfptoken);
        
        try {

	    if (!job.encryption) {
                xhr.send(blob);
	    } else {
		var cryptedBlob = null;
		var $this = this;
		blobReader = window.filesender.crypto_blob_reader().createReader(blob, function(blob){});
		blobReader.blobSlice = blob;
		blobReader.arrayBuffer().then( function (arrayBuffer)  {

		    window.filesender.crypto_app().encryptBlob(
                        arrayBuffer,
                        job.chunk.id,
                        job.encryption_details,
                        function (encrypted_blob) {
			    xhr.setRequestHeader('X-Filesender-Encrypted', '1');
			    xhr.send(encrypted_blob);
			},
                        function (e) { $this.error(e); } );
                });
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
        
        this.log('Job file:' + this.job.file.id + '[' + this.job.chunk.start + '...' + this.job.chunk.end + '] is ' + (100 * ratio).toFixed(1) + '% done ' + loaded + '/' + total);
        if (this.job.encryption) { 
            this.job.fine_progress = Math.floor(ratio * (this.job.chunk.end - this.job.chunk.start));
            this.job.progress = {
                loaded: loaded,
                total: total
            };
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
        
        if(status == 200 || (this.isReceiver() && status == 206)) { // All went well
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
                if(error.message == 'file_integrity_check_failed' ) {
                    this.log("Warning: Server sent back integrity_check_failed, so this worker will try to reupload the chunk");

                    var worker = this; // if in a method for example.
                    if( worker.send_attempts < window.filesender.config.terasender_worker_max_chunk_retries ) {
                        // try, try again
                        worker.send_attempts++;
                        worker.log('worker attempt ' + worker.send_attempts + ' to retry chunk upload at offset ' + worker.job.chunk.start);
                        worker.executeJob(worker.job);                    
                    }
                    else {
                        // Let the manager know something has really hit the fan
                        worker.sendCommand('jobFailed', worker.job);
                    }
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

window.filesender.onPBKDF2Starting = function() {
    terasender_worker.log("onPBKDF2Starting");
    terasender_worker.sendCommand("onPBKDF2Starting");
};
window.filesender.onPBKDF2Ended = function() {
    terasender_worker.log("onPBKDF2Ended");
    terasender_worker.sendCommand("onPBKDF2Ended");
};

/**
 * Register message handler
 */
self.onmessage = function(e) {
    terasender_worker.onMessage(e.data);
}
