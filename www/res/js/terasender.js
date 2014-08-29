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
            
            if(mode == 'multiple') { // Look for file that is not started yet
                for(var i=0; i<files.length && !file; i++)
                    if(files[i].uploaded == 0) file = files[i];
            }
            
            if(!file) // or mode is "single"
                for(var i=0; i<files.length && !file; i++)
                    if(files[i].uploaded < files[i].size) file = files[i];
        }
        
        if(!file) return null; // Nothing to do
        
        if(!file.endpoint) file.endpoint = filesender.config.terasender_upload_endpoint.replace('{file_id}', file.id).replace('{key}', file.uid);
        
        var job = {
            chunk: {
                start: file.uploaded,
                end: file.uploaded + filesender.config.terasender_chunk_size
            }
        };
        
        file.uploaded += filesender.config.terasender_chunk_size;
        if(file.uploaded > file.size) file.uploaded = file.size;
        
        if(file.id != worker.file_id) job.file = file;
        
        if(worker.file_id === null || worker.file_id != file.id) worker.file_id = file.id;
        
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
            return;
        }
        
        this.log('Giving job to worker:' + worker_id);
        
        this.jobAllocationLocked = true;
        
        var worker = this.workers[worker_id];
        
        // Get job from pool
        var job = this.allocateJob(worker);
        
        if(job) {
            this.log('Found job file:' + (job.file ? job.file.id : worker.file_id) + '[' + job.chunk.start + '...' + job.chunk.end + ']');
            this.sendCommand(workerinterface, 'executeJob', job);
        }else{
            this.log('No jobs remaining, terminating');
            
            workerinterface.status = 'done';
            this.sendCommand(workerinterface, 'done'); // Or workerinterface.terminate()
        }
        
        this.jobAllocationLocked = false;
    },
    
    /**
     * Log to console if enabled
     * 
     * @param string message
     * @param string origin "driver" (default) or "worker"
     */
    log: function(message, origin) {
        if(filesender.config.log) console.log('[terasender ' + (origin ? origin : 'driver') + '] ' + message);
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
    evalProgress: function(worker_id, data) {
        var file = null;
        var chunks_pending = false;
        for(var i=0; i<this.transfer.files.length; i++) {
            if(this.transfer.files[i].id == data.file.id)
                file = this.transfer.files[i];
            
            if(this.transfer.files[i].uploaded < this.transfer.files[i].size)
                chunks_pending = true;
        }
        
        if(!file) {
            this.error({message: 'unknown_file', details: {id: data.file.id}});
            this.stop();
        }
        
        var workers_on_same_file = 0;
        var workers_running = 0;
        
        for(var i=0; i<this.workers.length; i++) {
            if(this.workers[i].status != 'running') continue;
            if(this.workers[i].id == worker_id) continue;
            
            workers_running++;
            if(this.workers[i].file_id == data.file.id)
                workers_on_same_file++;
        }
        
        this.transfer.reportProgress(file, workers_on_same_file == 0);
        
        if(!chunks_pending && !workers_running) { // Not all done
            this.transfer.reportComplete();
            this.status = 'done';
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
            case 'jobExecuted' :
                this.log('Worker executed job', 'worker:' + worker_id);
                this.evalProgress(worker_id, data);
            
            case 'requestJob' :
                this.giveJob(worker_id, workerinterface);
                break;
            
            case 'log' :
                this.log(data, 'worker:' + worker_id);
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
        
        this.log('Starting transfer ' + transfer.id + ' with ' + transfer.files.length + ' files (' + transfer.size + ' bytes)');
        
        this.status = 'running';
        this.workers = [];
        
        this.transfer = transfer;
        
        for(i=0; i<filesender.config.terasender_worker_count; i++)
            this.createWorker();
        
        return true;
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
