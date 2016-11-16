/**
 * Part of the Filesender software.
 * See http://filesender.org
 */

if(!('filesender' in window)) window.filesender = {};

window.filesender.terasender = {
    /**
     * Status
     */
    status: '',
    
    /**
     * Job allocation lock
     */
    jobAllocationLocked: false,
    
    /**
     * Workers stack
     */
    workers: [],
    
    /**
     * Current transfer
     */
    transfer: null,
    
    /**
     * Security token
     */
    security_token: null,
    
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
        
        var job = {
            chunk: {
                start: file.uploaded,
                end: Math.min(file.uploaded + filesender.config.upload_chunk_size, file.size) //MD last chunk was too big
            },
	    encryption: this.transfer.encryption, //MD
	    encryption_password: this.transfer.encryption_password //MD
        };
        
        file.uploaded += filesender.config.upload_chunk_size;
        if(file.uploaded >= file.size) file.uploaded = file.size ? file.size : 1; // Protect against empty files creating loops
        
        if(file.id != worker.file_id) {
            job.file = {
                id: file.id,
                name: file.name,
                size: file.size,
                blob: file.blob,
                endpoint: file.endpoint
            };
            worker.file_id = file.id;
            worker.fine_progress = 0;
        }
        
        job.security_token = this.security_token;
        
        worker.offset = file.uploaded;
        
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
            this.log('Asking worker:' + worker_id + ' to come back later');
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
            this.sendCommand(workerinterface, 'done'); // Or workerinterface.terminate()
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
            console.log('[terasender ' + (origin ? origin : 'driver') + ' error] ' + error.message + (error.details ? ', details follow :' : ''));
            if(error.details) console.log(error.details); // Whatever type it is ...
        }
        
        error.message = 'terasender_' + error.message;
        
        // Trigger global error
        if(this.transfer) {
            this.transfer.reportError(error);
        }else{
            filesender.ui.error(error);
        }
    },
    
    /**
     * Evaluate progress and report to transfer
     * 
     * @param worker_id worker that reported the progress
     * @param object data progress data
     */
    evalProgress: function(worker_id, job, ratio) {
        var file = null;
        for(var i=0; i<this.transfer.files.length; i++)
            if(this.transfer.files[i].id == job.file.id)
                file = this.transfer.files[i];
        
        if(!file) {
            this.error({message: 'unknown_file', details: {id: job.file.id}});
            this.stop();
            return;
        }
        
        var workers_on_same_file = false;
        var min_offset = file.uploaded;
        var fine_progress = 0;
        
        for(var i=0; i<this.workers.length; i++) {
            if(!this.workers[i].status.match(/^(running|uploading)$/)) continue;
            
            if(this.workers[i].id == worker_id) {
                if(ratio >= 1)
                    this.workers[i].status = 'running';
                
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
        
        var done = (file.uploaded >= file.size) && !workers_on_same_file;
        
        file.fine_progress = done ? file.size : /*file.min_uploaded_offset +*/ fine_progress; //MD not sure why we are adding file.min_uploaded_offset
        
        var t = this;
        var complete = done ? function() {
            var chunks_pending = false;
            for(var i=0; i<t.transfer.files.length; i++)
                if(t.transfer.files[i].uploaded < t.transfer.files[i].size)
                    chunks_pending = true;
            
            var workers_uploading = false;
            for(var i=0; i<t.workers.length; i++)
                if(t.workers[i].status == 'uploading')
                    workers_uploading = true;
            
            if(chunks_pending || workers_uploading) return;
            
            // Notify all done
            t.transfer.reportComplete();
            t.status = 'done';
        } : false;
        
        this.transfer.reportProgress(file, complete);
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
                this.evalProgress(worker_id, data);
                break;
                
            case 'jobExecuted' :
                this.log('Worker executed job', 'worker:' + worker_id);
                this.transfer.recordUploadedInWatchdog('worker:' + worker_id);
                this.evalProgress(worker_id, data, 1);
                // No break here as we give new job asap
                
            case 'requestJob' :
                var gave = this.giveJob(worker_id, workerinterface);
                if(gave) this.transfer.recordUploadStartedInWatchdog('worker:' + worker_id);
                break;
            
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
                
                // Worker can't continue, upload is broken, stop everyone
                this.stop();
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
     * Start upload
     * 
     * @return bool indicating wether the transfer started (true) or that driver is already buzy (false)
     */
    start: function(transfer) {
        if(this.status != '' && this.status != 'done') return false;
        
        if(!transfer) {
            this.error({message: 'no_transfer'});
            return;
        }
        
        this.security_token = filesender.client.security_token;
        
        this.log('Starting transfer ' + transfer.id + ' with ' + transfer.files.length + ' files (' + transfer.size + ' bytes)');
        
        this.status = 'running';
        this.workers = [];
        
        this.transfer = transfer;
        
        // Safety
        var wcnt = parseInt(filesender.config.terasender_worker_count);
        if(isNaN(wcnt) || wcnt < 1 || wcnt > 30)
            wcnt = 3;
        
        for(i=0; i<filesender.config.terasender_worker_count; i++)
            this.createWorker();
        
        return true;
    },
    
    /**
     * Retry upload
     */
    retry: function() {
        for(var i=0; i<this.workers.length; i++)
            this.workers[i].terminate();
        
        for(var i=0; i<this.transfer.files.length; i++)
            this.transfer.files[i].uploaded = this.transfer.files[i].min_uploaded_offset;
        
        this.workers = [];
        
        for(i=0; i<filesender.config.terasender_worker_count; i++)
            this.createWorker();
    },
    
    /**
     * Stop everything (crash)
     */
    stop: function() {
        this.log('Stopping workers');
        
        for(var i=0; i<this.workers.length; i++)
            this.workers[i].terminate();
        
        this.workers = [];
        this.status = '';
    },
    
    /**
     * Pause upload
     */
    pause: function() {
        if(this.status == 'running') this.status = 'paused';
    },
    
    /**
     * Restart upload
     */
    restart: function() {
        if(this.status == 'paused') this.status = 'running';
    }
};
