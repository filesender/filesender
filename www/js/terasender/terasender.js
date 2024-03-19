/**
 * Part of the Filesender software.
 * See http://filesender.org
 */

if(!('filesender' in window)) window.filesender = {};

var noWorkersHaveStarted = function( ts ) {
    ts.error({message: 'no_workers_have_started'});
    ts.stop();
};


window.filesender.terasender = {
    /**
     * Status
     */
    status: '',

    /**
     * For PBKDF2 key derivation we want to track a state machine
     * that starts with nokey, moves to generating, and ends with generated.
     * This allows the firing of only one onPBKDF2Ended event and a single
     * onPBKDF2AllEnded event.
     */
    crypto_pbkdf2_states: {
        pbkdf2_nokey: 0,
        pbkdf2_generating: 1,
        pbkdf2_generated: 2,
        pbkdf2_all_generated: 3
    },
    crypto_pbkdf2_status: 0,

    // Number of workers that have completed key generation
    crypto_pbkdf2_workers_that_have_generated: 0,

    // Number of workers that will generate keys
    // will be filesender.config.terasender_worker_count
    // or less if total chunks to upload is less than terasender_worker_count.
    crypto_pbkdf2_workers_that_will_generate: -1,
    
    /**
     * Job allocation lock
     */
    jobAllocationLocked: false,
    
    /**
     * Workers stack
     */
    workers: [],

    /**
     * timer id to check if a worker has started up
     */
    workers_start_monitor_id: 0,
    
    /**
     * Current transfer
     */
    transfer: null,
    
    /**
     * Security token
     */
    security_token: null,

    /**
     * upload/download mode
     */
    mode_values: {
        sender:   1,
        receiver: 2
    },
    mode: 1, // default to a sender
    isSender: function() {
        return this.mode == this.mode_values.sender;
    },
    isReceiver: function() {
        return this.mode == this.mode_values.receiver;
    },

    /**
     * This can be set to an object with callbacks for errors,
     * progress, a copy of the sink to write to etc when
     * mode==receiver.
     *
     */ 
    receiver: null,
    crypto_app: null,

    /**
     * To stop the server sending emails for each file in the encrypted download.
     */
    crypto_encrypted_archive_download_fileidlist: '',
//    crypto_encrypted_archive_download: false,
    
    /**
     * Send command to worker
     * 
     * @param worker workerinterface
     * @param string command
     * @param mixed data
     */
    sendCommand: function(workerinterface, command, data) {
        workerinterface.postMessage({
            command: command,
            data: data
        });
    },
    
    /**
     * Allocate a job to worker
     */
    allocateJob: function(worker) {
        var file = null;
        
        var files = this.transfer.files;

        this.log('allocateJob(top) mode:' + this.mode );
        
        if( this.isReceiver()) {

            var encryption_details = this.receiver.encryption_details;
            var chunkid = this.receiver.chunkid;
            this.receiver.chunkid++;
            this.log('allocateJob(recv) chunkid:' + chunkid );
            
            if( chunkid > encryption_details.chunkcount ) {
                this.log("allocateJob(recv) we have downloaded all the chunks!");
                return null;
            }
            
            var job = {
                mode: this.mode,
                chunkid: chunkid,
                chunk: {
                    id: chunkid,
                    start: 0,
                    end: 1
                },
                file: {
                    id: -1
                },
                encryption:         this.transfer.encryption,
                encryption_details: this.receiver.encryption_details,
                roundtriptoken:     this.transfer.roundtriptoken,
                link:               this.receiver.link,
                security_token:     this.security_token,
                csrfptoken:         filesender.client.getCSRFToken(),
                crypto_encrypted_archive_download: window.filesender.crypto_encrypted_archive_download,
                crypto_encrypted_archive_download_fileidlist: this.crypto_encrypted_archive_download_fileidlist
            };

            this.log('allocateJob(recv) chunk:' + chunkid + ' giving job to worker');
            
            worker.file_id = 1;
            worker.fine_progress = 0;
            worker.offset = 0;

            return job;
        }
        
        if(worker.file_id !== null) { // Worker already has file
            for(var i=0; i<files.length && !file; i++)
                if(files[i].id == worker.file_id) file = files[i];
            
            if(file && (file.uploaded >= file.size)) // Still chunk awaiting upload for this file ?
                file = null;
        }
        
        if(!file) { // Look for file with remaining chunks
            var mode = filesender.config.terasender_start_mode;
            
            if(mode == 'multiple') { // Look for file with the least worker count
                var files_workers = {};
                for(var i=0; i<this.workers.length; i++) {
                    if(!this.workers[i].file_id) continue;
                    
                    if(!files_workers['file_' + this.workers[i].file_id])
                        files_workers['file_' + this.workers[i].file_id] = 0;
                    
                    files_workers['file_' + this.workers[i].file_id]++;
                }
                
                var candidates = [];
                for(var i=0; i<files.length; i++) {
                    if(files[i].uploaded >= files[i].size) continue;
                    
                    var wcnt = files_workers['file_' + files[i].id];
                    if(!wcnt) wcnt = 0;
                    
                    candidates.push({file: files[i], wcnt: wcnt});
                }
                
                candidates.sort(function(a, b) {
                    return a.wcnt - b.wcnt;
                });
                
                var winner = candidates.shift();
                if(winner) file = winner.file;
            }
            
            if(!file) // or mode is "single"
                for(var i=0; i<files.length && !file; i++)
                    if(files[i].uploaded < files[i].size) file = files[i];
        }
        
        if(!file) return null; // Nothing to do
        
        if(!file.endpoint) file.endpoint = this.transfer.authenticatedEndpoint(filesender.config.terasender_upload_endpoint.replace('{file_id}', file.id), file);

        var chunkid = Math.floor(file.uploaded / filesender.config.upload_chunk_size);
        var encryption_details = this.transfer.getEncryptionMetadata( file );
        
	if (typeof file.fine_progress_done === 'undefined') file.fine_progress_done=file.uploaded; //missing from file
        var job = {
            mode: this.mode,
            chunk: {
                id: chunkid,
                start: file.uploaded,
                end: Math.min(file.uploaded + filesender.config.upload_chunk_size, file.size) //MD last chunk was too big
            },
	    encryption: this.transfer.encryption,
	    encryption_details: encryption_details,
            roundtriptoken: this.transfer.roundtriptoken,
            file: {
                id: file.id,
                name: file.name,
                size: file.size,
                blob: file.blob,
                endpoint: file.endpoint,
                iv: file.iv,
                aead: file.aead
            },
            security_token: this.security_token,
            csrfptoken: filesender.client.getCSRFToken()
        };
        
        file.fine_progress = 0;
        file.uploaded = file.uploaded + filesender.config.upload_chunk_size;
        
        worker.file_id = file.id;
        worker.fine_progress = 0;
        worker.offset = file.uploaded;

        if(file.uploaded >= file.size) file.uploaded = file.size ? file.size : 1; // Protect against empty files creating loops
        
        return job;
    },
    
    /**
     * Give job to requesting worker
     * 
     * @param object request job request
     * @param object workerinterface interface to post response to
     */
    giveJob: function(worker_id, workerinterface) {
        if(this.jobAllocationLocked || (this.status == 'paused')) {
            this.log('Asking worker:' + worker_id + ' to come back later' +
                     ' status ' + this.status + ' jobAllocationLocked ' + this.jobAllocationLocked );
            this.sendCommand(workerinterface, 'comeBackLater');
            return false;
        }
        
        this.log('Giving job to worker:' + worker_id);
        
        this.jobAllocationLocked = true;
        
        var worker = this.workers[worker_id];
        
        // Get job from pool
        var job = this.allocateJob(worker);
        
        if(job) {
            this.log('Found job file:' + (job.file ? job.file.id : worker.file_id) + '[' + job.chunk.start + '...' + job.chunk.end + ']');
            workerinterface.status = 'uploading';
            this.sendCommand(workerinterface, 'executeJob', job);
        }else{
            this.log('No jobs remaining, terminating');
            
            workerinterface.status = 'done';
            workerinterface.terminate(); //sending done seems to crash FF v52+ on windows and FF on mac?
        }
        
        this.jobAllocationLocked = false;
        
        return job;
    },
    
    /**
     * Log to ui
     * 
     * @param string message
     * @param string origin "driver" (default) or "worker"
     */
    log: function(message, origin) {
        filesender.ui.log('[terasender ' + (origin ? origin : 'driver') + '] ' + message);
    },
    
    /**
     * Handle errors
     * 
     * @param string code
     * @param mixed details
     * @param string origin "driver" (default) or "worker"
     */
    error: function(error, origin) {
        if(filesender.config.log) {
            window.filesender.log('[terasender ' + (origin ? origin : 'driver') + ' error] ' + error.message + (error.details ? ', details follow :' : ''));
            if(error.details) window.filesender.log(error.details); // Whatever type it is ...
        }
        

        if( this.receiver && this.receiver.onError ) {
            this.receiver.onError( error );
        }

        var doc = new DOMParser().parseFromString(error.message, 'text/html');
        if (doc.getElementsByClassName('exception')) { //if this is from our template, pull out the exception only.
            error.message = 'exception';
        } else {
            error.message = doc.body.textContent || error.message;
        }

        error.messageTranslated = error.message;
        error.message = 'terasender_' + error.message;
        
        // Trigger global error
        if(this.transfer) {
            this.transfer.reportError(error);
        }else{
            filesender.ui.error(error);
        }
    },

    ensureBareWorkerID: function(wid) {
        if( wid.includes('worker:')) {
            wid = wid.substr( 'worker:'.length );
        }
        return wid;
    },
    
    
    
    
    /**
     * Evaluate progress and report to transfer
     * 
     * @param worker_id worker that reported the progress
     * @param object data progress data
     */
    evalProgress: function(worker_id, job, ratio) {

        if( this.isReceiver()) {
            var t = this;
            var encryption_details = t.receiver.encryption_details;
            
            this.log('evalProgress(recv) job chunkid ' + job.chunkid );
            this.log('evalProgress(recv) job cc      ' + encryption_details.chunkcount );
            this.log('evalProgress(recv) job   ec ' + job.encryptedChunk );
            if( job.encryptedChunk ) {
                this.log('evalProgress(recv) job   iv.len ' + job.encryptedChunk.iv.length );
                this.log('evalProgress(recv) job data.len ' + job.encryptedChunk.data.length );
            }
            
            if(ratio >= 1) {
                // completion message
                this.receiver.onChunkSuccess( job );
            
                if( job.chunkid >= encryption_details.chunkcount ) {
                    this.log("evalProgress(recv) we have downloaded all the chunks!");
                    this.status = 'done';
                }
            } else {
                // progress message
                this.crypto_app = window.filesender.crypto_app();
                this.log('evalProgress(recv) progress loaded ' + job.progress.loaded );
                this.log('evalProgress(recv) progress total  ' + job.progress.total );
                this.log('evalProgress(recv) progress esz    ' + encryption_details.encrypted_filesize );
                this.log('evalProgress(recv) ca    ' + this.crypto_app );
                this.log('evalProgress(recv) ccs1  ' + this.crypto_app.crypto_chunk_size );
                this.workers[worker_id].progress = { loaded:job.progress.loaded, total:job.progress.total };
                var chunkid     = this.receiver.chunkid;
                var chunksz     = 1 * this.crypto_app.crypto_chunk_size;
                var startoffset = 1 * (chunkid * chunksz);
                var totalrecv = startoffset;
                var loaded = job.progress.loaded;

                var percentComplete = Math.round(loaded / (1*this.crypto_app.upload_crypted_chunk_size) *10000) / 100;
                var percentOfFileComplete = 100*(((chunkid-1)*this.crypto_app.crypto_chunk_size + loaded) / encryption_details.encrypted_filesize );

                if( this.receiver.onProgress ) {
                    this.receiver.onProgress( this, chunkid, totalrecv, percentComplete, percentOfFileComplete );
                }
            }
            return;
        }
        
        var file = null;
        for(var i=0; i<this.transfer.files.length; i++)
            if(this.transfer.files[i].id == job.file.id) {
                file = this.transfer.files[i];
                break;
            }
        
        if(!file) {
            this.error({message: 'unknown_file', details: {id: job.file.id}});
            this.stop();
            return;
        }

        var workers_on_same_file = false;
        var min_offset = file.uploaded;
        var fine_progress = 0;

        var uploading_count = 0;
        for(var i=0; i<this.workers.length; i++) {
            if(this.workers[i].status.match(/^(uploading)$/)) 
                uploading_count++;
        }
        
        for(var i=0; i<this.workers.length; i++) {
            if(!this.workers[i].status.match(/^(running|uploading)$/)) continue;
            
            if(this.workers[i].id == worker_id) {
                if(ratio >= 1) {
                    this.workers[i].status = 'running';
                    if (job.fine_progress==0) { //IE 11 doesnt report fine_progress, so lets make it up
                        job.fine_progress = job.chunk.end - job.chunk.start;
                    }
                    file.fine_progress_done += job.fine_progress;
                    fine_progress -= job.fine_progress;
		}
                this.workers[i].fine_progress = job.fine_progress;
            }
            
            if(this.workers[i].file_id == job.file.id) {
                min_offset = Math.min(min_offset, this.workers[i].offset);
                fine_progress += this.workers[i].fine_progress;
            }
                
            if(this.workers[i].id == worker_id) continue;
            
            if(this.workers[i].file_id == job.file.id)
                workers_on_same_file = true;
        }
        
        file.min_uploaded_offset = Math.max(0, min_offset - filesender.config.upload_chunk_size);
        
        var done = (file.fine_progress_done >= file.size) && !workers_on_same_file;
        
        file.fine_progress = done ? file.size : (fine_progress + file.fine_progress_done);
        
        var t = this;
        var complete = done ? function() {
            var chunks_pending = false;
            for(var i=0; i<t.transfer.files.length; i++)
                if(t.transfer.files[i].uploaded < t.transfer.files[i].size) {
                    chunks_pending = true;
                    break;
                }
            
            var workers_uploading = false;
            for(var i=0; i<t.workers.length; i++)
                if(t.workers[i].status == 'uploading') {
                    workers_uploading = true;
                    break;
                }

            if(chunks_pending || workers_uploading) return;
            
            //
            // If the final chunks of the final two files are
            // uploading it is possible that we can get here for the
            // second last file and that would present a race
            // condition between this reportComplete() whih itself
            // calls transferComplete() which is not the case until we
            // are finishing the last chunk of the last file.
            //
            // NOTE that we can not simply place the uploading_count
            // test above and set 'var complete = false' because
            // reportProgress() uses the callable complete as a
            // boolean to indicate if we are on the last chunk of the
            // file, which we are, but maybe not the last file as
            // well.
            //
            if( uploading_count > 1 ) return;
          
            // Notify all done
            t.transfer.reportComplete();
            t.status = 'done';
        } : false;
        
        this.transfer.reportProgress(file, complete);
    },

    /**
     * Look through all the workers for the file that job is working
     * on and set the transfer.files starting upload offsets so that
     * chunks that are currently in flight will always be resent on 
     * a resume. 
     * 
     * Note that the worker that has failed 'job' might not
     * have the lowest file offset of the currently running workers.
     * So you can not simply use job.chunk.start as the minimal offset.
     */
    setMinUploadedOffsetFromActiveWorkers: function( job ) {

        var files = this.transfer.files;
        var min_offset = -1;
        for(var i=0; i<files.length; i++) {
            if(files[i].id == job.file.id) {
                min_offset = files[i].uploaded;
            }
        }
                
        for(var i=0; i<this.workers.length; i++) {
            if(!this.workers[i].status.match(/^(running|uploading)$/)) continue;
            
            if(this.workers[i].file_id == job.file.id) {
                min_offset = Math.min(min_offset, this.workers[i].offset);
            }
        }

        var min_uploaded = Math.max(0, min_offset - filesender.config.upload_chunk_size);
                
        for(var i=0; i<files.length; i++) {
            if(files[i].id == job.file.id) {
                this.transfer.files[i].min_uploaded_offset = min_uploaded;
                this.transfer.files[i].uploaded = files[i].min_uploaded_offset;
            }
        }
    },
    
    /**
     * Handle messages from workers
     * 
     * @param object message
     * @param workerinterface workerinterface
     */
    onMessage: function(message, workerinterface) {
        var command = message.command;
        var worker_id = message.worker_id;
        var data = message.data;
        
        if(!command) return;
        
        switch(command) {
            case 'jobProgress' :
                this.log('Worker job progressed', 'worker:' + worker_id);
                this.transfer.recordUploadProgressInWatchdog('worker:' + worker_id,data.fine_progress);
                this.evalProgress(worker_id, data);
                window.filesender.pbkdf2dialog.ensure_onPBKDF2AllEnded();
                break;
                
            case 'jobExecuted' :
                this.log('Worker executed job', 'worker:' + worker_id);
                this.transfer.recordUploadedInWatchdog('worker:' + worker_id);
                this.evalProgress(worker_id, data, 1);
                // No break here as we give new job asap
                
            case 'requestJob' :
                if( this.workers_start_monitor_id ) {
                    window.clearTimeout( this.workers_start_monitor_id );
                    this.workers_start_monitor_id = 0;
                }
                var gave = this.giveJob(worker_id, workerinterface);
                if(gave) this.transfer.recordUploadStartedInWatchdog('worker:' + worker_id, gave.file);
                break;

            // This happens after the worker has already
            // tried many times to upload the chunk
            case 'jobFailed':
            {
                var workerUploaded = data.chunk.start;
                var job = data;

                this.setMinUploadedOffsetFromActiveWorkers( job );
                
                window.filesender.log('terasender_failed_after_many_retries'
                                      + ' offset ' + data.chunk.start + ' fileid ' + data.file.id );
                this.error({message: 'failed_after_many_retries' });
                this.stop();
                
            } break;
            
            case 'securityTokenChanged' :
                this.security_token = data; // Will be sent to workers along with next jobs
                filesender.client.updateSecurityToken(data);
                break;
                
            case 'log' :
                this.log(data, 'worker:' + worker_id);
                break;
            
            case 'maintenance' :
                this.log('Worker ' + (data ? 'entering' : 'leaving') + ' maintenance mode', 'worker:' + worker_id);
                filesender.ui.maintenance(data);
                this.transfer.maintenance(data);
                break;
            
            case 'error' :
                this.error(data, 'worker:' + worker_id);
                
                // Worker can't continue, transfer is broken, stop everyone
                this.stop();
                break;
            
            case 'onPBKDF2Starting':
                if( this.crypto_pbkdf2_status == this.crypto_pbkdf2_states.pbkdf2_nokey )
                {
                    this.crypto_pbkdf2_status = this.crypto_pbkdf2_states.pbkdf2_generating;
                    window.filesender.onPBKDF2Starting();
                }             
                break;
            case 'onPBKDF2Ended':
            console.log("pbkdf2 workers that have gen ", this.crypto_pbkdf2_workers_that_have_generated );
            console.log("pbkdf2 workers that will gen ", this.crypto_pbkdf2_workers_that_will_generate );
            console.log("pbkdf2 wc   ", filesender.config.terasender_worker_count );
                if( this.crypto_pbkdf2_status == this.crypto_pbkdf2_states.pbkdf2_generating )
                {
                    this.crypto_pbkdf2_status = this.crypto_pbkdf2_states.pbkdf2_generated;
                    this.crypto_pbkdf2_workers_that_have_generated++;
                    window.filesender.onPBKDF2Ended();

                    if( this.crypto_pbkdf2_workers_that_have_generated == this.crypto_pbkdf2_workers_that_will_generate )
                    {
                        window.filesender.onPBKDF2AllEnded();
                    }
                }
                if( this.crypto_pbkdf2_status == this.crypto_pbkdf2_states.pbkdf2_generated )
                {
                    this.crypto_pbkdf2_workers_that_have_generated++;
                    if( this.crypto_pbkdf2_workers_that_have_generated == this.crypto_pbkdf2_workers_that_will_generate )
                    {
                        window.filesender.onPBKDF2AllEnded();
                    }
                }
                break;
        }
    },
    
    /**
     * Create a new worker
     */
    createWorker: function() {
        var id = this.workers.length;
        
        var workerinterface = new Worker(filesender.config.terasender_worker_file);
        workerinterface.id = id;
        workerinterface.file_id = null;
        workerinterface.offset = 0;
        workerinterface.fine_progress = 0;
        workerinterface.status = 'running';
        
        var terasender = this;
        workerinterface.onmessage = function(e) {
            terasender.onMessage(e.data, e.target);
        };
        
        // Send id to worker
        this.sendCommand(workerinterface, 'start', id);
        
        this.log('Worker ' + id + ' created');

        this.workers.push(workerinterface);
    },
    
    
    /**
     * Start upload/download
     * 
     * @return bool indicating wether the transfer started (true) or that driver is already buzy (false)
     */
    startMode: function(transfer,mode) {
        this.log('terasender.start(top)   mode ' + mode );
        this.log('terasender.start(top) status ' + this.status );

        if(this.status != '' && this.status != 'done') return false;

        if(!transfer) {
            this.error({message: 'no_transfer'});
            return;
        }

        if( mode == '' || mode < 1 ) {
            mode = this.mode_values.sender;
        }
        this.mode = mode;
        
        this.security_token = filesender.client.security_token;
        
        this.log('Starting transfer ' + transfer.id + ' with ' + transfer.files.length + ' files (' + transfer.size + ' bytes)');
        
        this.status = 'running';
        this.transfer = transfer;
        var ts = this;

        // Safety
        if(isNaN(parseInt(filesender.config.terasender_worker_max_count))) {
            // clamp the max to the default
            filesender.config.terasender_worker_max_count = 30;
        }
        var wcnt = parseInt(filesender.config.terasender_worker_count);
        if(isNaN(wcnt)) {
            // Bad setting is set to something that will work
            // ( 3 was the previous default in this case )
            wcnt = 3;
        }
        if( wcnt < 1 ) {
            // too low is set to the min
            wcnt = 1;
        }
        if( wcnt > filesender.config.terasender_worker_max_count ) {
            // clamp this to the desired max_count
            wcnt = filesender.config.terasender_worker_max_count;
        }
        filesender.config.terasender_worker_count = wcnt;
        
        // Work out if we have less chunks to upload than the wcnt value.
        this.crypto_pbkdf2_workers_that_will_generate = filesender.config.terasender_worker_count;
        var chunksToUpload = 0;
        var files = this.transfer.files;
        for(var i=0; i<files.length; i++) {
            if(files[i].uploaded < files[i].size) {
                var remainingBytes = files[i].size - files[i].uploaded;
                var remainingChunks = Math.ceil( remainingBytes / filesender.config.upload_chunk_size );
                chunksToUpload += remainingChunks;
                if( chunksToUpload > wcnt ) {
                    break;
                }
            }
        }
        console.log("pbkdf2 chunksToUpload", chunksToUpload );
        console.log("pbkdf2 wcnt", wcnt );
        // only some workers will ever try to generate a key.
        if( chunksToUpload < wcnt ) {
            this.crypto_pbkdf2_workers_that_will_generate = chunksToUpload;
        }

        if( this.isReceiver()) {
            // this will be expanded in a future PR
            wcnt = 1;
        }

        this.workers = [];
        this.crypto_pbkdf2_status = this.crypto_pbkdf2_states.pbkdf2_nokey;
        this.crypto_pbkdf2_workers_that_have_generated = 0;
        
        this.workers_start_monitor_id = window.setTimeout( function() { noWorkersHaveStarted(ts) },
                                                           filesender.config.terasender_worker_start_must_complete_within_ms );
        
        for(i=0; i<wcnt; i++)
            this.createWorker();
        
        return true;
    },

    start: function(transfer) {
        this.startMode(transfer,this.mode_values.sender);
    },
    startReceiver: function(transfer) {
        this.startMode(transfer,this.mode_values.receiver);
    },
    
    /**
     * Retry upload
     */
    retry: function() {
        for(var i=0; i<this.workers.length; i++)
            this.workers[i].terminate();
        
        for(var i=0; i<this.transfer.files.length; i++) {
            this.transfer.files[i].uploaded = this.transfer.files[i].min_uploaded_offset;
            if( isNaN(this.transfer.files[i].uploaded))
                this.transfer.files[i].uploaded = 0;
        }
        
        this.workers = [];
        this.crypto_pbkdf2_status = this.crypto_pbkdf2_states.pbkdf2_nokey;
        this.crypto_pbkdf2_workers_that_have_generated = 0;

        var ts = this;
        if( this.workers_start_monitor_id ) {
            window.clearTimeout( this.workers_start_monitor_id );
            this.workers_start_monitor_id = 0;
        }
        this.workers_start_monitor_id = window.setTimeout( function() { noWorkersHaveStarted(ts) },
                                                           filesender.config.terasender_worker_start_must_complete_within_ms );
        
        for(i=0; i<filesender.config.terasender_worker_count; i++)
            this.createWorker();

        var ts = this;
        this.status = 'running';
        
    },
    
    /**
     * Stop everything (crash)
     */
    stop: function() {
        this.log('Stopping workers');
        
        for(var i=0; i<this.workers.length; i++) {
            this.workers[i].terminate();
        }
        
        
        this.workers = [];
        this.status = '';
        this.crypto_pbkdf2_status = this.crypto_pbkdf2_states.pbkdf2_nokey;
        this.crypto_pbkdf2_workers_that_have_generated = 0;
    },
    
    /**
     * Pause upload
     */
    pause: function() {
        if(this.status == 'running') {
            this.status = 'paused';
        }
    },
    
    /**
     * Restart upload
     */
    restart: function() {
        if(this.status == 'paused') {
            this.status = 'running';
            this.crypto_pbkdf2_status = this.crypto_pbkdf2_states.pbkdf2_nokey;
            this.crypto_pbkdf2_workers_that_have_generated = 0;
        }
    }
};
