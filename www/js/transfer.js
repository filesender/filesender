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
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS'
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
 * Track progress of an active chunk upload. This collects
 * information as an xhr call is progressing to send a
 * chunk of data that is (upload_chunk_size or equiv)
 * bytes. The idea is that we can track the bytes as they
 * go over and allow the user or FileSender itself to do
 * something smart if a chunk has not sent much or any
 * data for a chunk for a given time. 
 * 
 * you will want to call clear() when a chunk starts,
 * remember() or setDisabled() when progress on a chunk is
 * reported, and isOffending() to check if this chunk is
 * deemed to have not enough recent transfer activity
 * ("the danger zone").
 */
window.filesender.progresstracker = function() {

    this.stamp = (new Date()).getTime();
    this.mem = [];
    this.memToKeep = 5;
    this.disabled = false;

    /**
     * Reset the tracker for a fresh chunk
     */
    this.clear = function() {
        this.mem = [];
        this.disabled = false;
        this.stamp = (new Date()).getTime();
    };
                
    /**
     * remember the reported fine_progress and take a timestamp
     * when this is called.
     */
    this.remember = function( fine_progress ) {
        this.stamp = (new Date()).getTime();
        this.disabled = false;
        if( !this.mem.length ) {
            this.mem[0] = 0;
            
        }
        var d = fine_progress - this.mem[this.mem.length-1];
        this.mem.push( d );
        if( this.mem.length >= this.memToKeep )
            this.mem.pop();

    };

    /**
     * A chunkCompleted call can be made after a chunk is uploaded
     * to stop this worker being considered slow when there are no
     * more chunks to upload.
     */
    this.chunkCompleted = function() {
        this.stamp = (new Date()).getTime();
        this.disabled = true;
        this.mem = [];
    }

    /**
     * This disables isOffending() from ever returning true.
     */
    this.setDisabled = function() {
        this.disabled = true;
    };

    /**
     * How many bytes were transfered between the last two
     * calls to remember().
     */
    this.latest = function() {
        return this.mem[this.mem.length-1];
    };

    /**
     * For the current configuration is this worker
     * "offending"ly slow.
     * 
     * The implementation could taken many forms, a simple
     * one being if no progress has been reported for 10
     * seconds it might have stalled. Or the number of
     * bytes transfered could also be considered.
     */
    this.isOffending = function() {
        if( this.disabled )
            return false;

        var tooSlow = filesender.config.upload_considered_too_slow_if_no_progress_for_seconds;
        if( tooSlow==0 )
            return false;

        
        if( (new Date()).getTime() - this.stamp > (tooSlow*1000) ) {
            return true;
        }
        return false;

        // play with bytes?
        // var sum = this.mem.reduce(function(a, b) { return a + b; }, 0);
        // return sum == 0;
    };

    /**
     * Just makes this.log() available.
     */
    this.log = function(message, origin) {
        filesender.ui.log('[progressTracker ' + origin + '] ' + message);
    };
};

/**
 * Transfer pseudoclass
 */
window.filesender.transfer = function() {
    this.id = null;
    
    this.size = 0;
    this.files = [];
    this.recipients = [];
    this.from = null;
    this.subject = null;
    this.message = null;
    this.lang = null;
    this.expires = null;
    this.options = {};
    this.time = 0;
    this.encryption = 0;
    this.encryption_password = '';
    this.encryption_password_encoding = 'none';
    this.encryption_password_version  = 1;

    this.encryption_key_version = 0;
    this.encryption_salt = '';
    this.encryption_password_hash_iterations = filesender.config.encryption_password_hash_iterations_new_files;
    this.encryption_client_entropy = '';
    this.disable_terasender = 0;
    this.pause_time = 0;
    this.pause_length = 0;
    this.file_index = 0;
    this.status = 'new';
    this.legacy = {};
    this.onprogress = null;
    this.oncomplete = null;
    this.onerror = null;
    this.guest_token = null;
    this.failed_transfer_restart = false;
    this.uploader = null;
    this.aup_checked = false;
    this.roundtriptoken = '';
    
    this.watchdog_processes = {};
    
    // Load and analyse stalling detection config
    var cfg = filesender.config.stalling_detection;
    if(cfg) {
        if(typeof cfg != 'object') cfg = {};
        
        /**
         * Max count divergence (the maximum chunk count a process can be late over the global average)
         */
        if(!cfg.count_divergence) cfg.count_divergence = 10;
        
        /**
         * Max duration divergence (the maximum ratio between a process's current upload time and the global average)
         * 
         * Assume the workers current duration is 'd' then a worker is late if:
         * 'd' is > d*average(worker duration history in durations_horizon) * duration_divergence
         *
         * there are extra provisions added to allow more time scope for workers when there isn't a full 
         * collection of durations_horizon as startup might be more volatile than later chunk uploads.
         */
        if(!cfg.duration_divergence) cfg.duration_divergence = 7;
        
        /**
         * Max automatic retries
         */
        if(!cfg.automatic_retries) cfg.automatic_retries = 3;

        /**
         * number of durations to keep for each worker
         */
        if(!cfg.durations_horizon) cfg.durations_horizon = 5;
    }
    
    this.stalling_detection = cfg;
    
    this.canUseTerasender = function() {
        var enable = filesender.config.terasender_enabled && filesender.supports.workers;
        enable &= !this.encryption || filesender.supports.workerCrypto;
        enable &= !this.disable_terasender;
        return enable;
    };
    this.canUseTeraReceiver = function() {
        var enable = filesender.config.terareceiver_enabled && filesender.supports.workers;
        enable &= !this.encryption || filesender.supports.workerCrypto;
        enable &= !this.disable_terasender;
        return enable;
    }


    
    this.getExtention = function(file) {
        var fileSplit = file.name.replace(/^.+[\/\\]/, '').split('.');
        if (fileSplit.length>1) {
            return fileSplit.pop();
        }
        return '';
    };


    
    this.getEncryptionMetadata = function( file ) {
        var key_version = this.encryption_key_version;
	var ret = {
            password:          this.encryption_password,
            password_encoding: this.encryption_password_encoding,
            password_version:  this.encryption_password_version,
            key_version:       this.encryption_key_version,
            salt:              this.encryption_salt,
            password_hash_iterations: this.encryption_password_hash_iterations,
            client_entropy:    this.encryption_client_entropy
        };
        
        if( this.encryption ) {
            ret['fileiv']   = window.filesender.crypto_app().decodeCryptoFileIV(file.iv,key_version);
            ret['fileaead'] = file.aead;
        }
        
        return ret;
    };

    /**
     * Check if a file is still considered "valid"
     *
     * Things can change, for example, if you turn on/off encryption then the 
     * maximum file size can change.
     *
     * Instead of purely using the return value, an ok and fail callback are used
     * so that the error callback can be called with additional information about
     * the violation.
     */
    this.checkFileAsStillValid = function(file, okHandler, errorhandler) {
        
        if( !this.encryption ) {
            var v = filesender.config.max_transfer_file_size;
            
            if( v && file.size > v ) {
                errorhandler({message: 'maximum_file_size_exceeded', details: {size: file.size, max: v  }});
                return false;
            }
        } else {
            var v = filesender.config.max_transfer_encrypted_file_size;
            
            if( v && file.size > v ) {
                errorhandler({message: 'maximum_encrypted_file_size_exceeded', details: {size: file.size, max: v  }});
                return false;
            }
            if( !this.checkIsValidFileSize( file, errorhandler )) {
                return false;
            }
            
        }
        okHandler(true);
        return true;
    };

    /**
     * Check file size for encryption limits
     *
     * @return true if things are ok
     */
    this.checkIsValidFileSize = function( file, errorhandler ) {

        if( this.encryption )
        {
            if( !window.filesender.crypto_app().isFileSizeValidForEncryption( file.size ))
            {
                errorhandler({message: 'maximum_encrypted_file_size_exceeded',
                              details: {filename: file.name, size: file.size}});
                return false;
            }
        }
        return true;
    };
    
    /**
     * Add a file to the file list
     * 
     * @param object file HTML input / FileList / File
     * 
     * @return mixed int file index or false if it was a duplicate or that there was an error
     */
    this.addFile = function(file_name, blob, errorhandler, source_node) {
        if (!errorhandler)
            errorhandler = filesender.ui.error;
        
        if (!blob)
            return errorhandler({message: 'no_file_given'});
        
        if ('parentNode' in blob) // HTML file input
            blob = blob.files;
        
        if ('length' in blob) { // FileList
            if (!blob.length) {
                errorhandler({message: 'no_file_given'});
                return false;
            }
            
            for (var i = 0; i < blob.length; i++)
                this.addFile(blob[i].name, blob[i]);

            return;
        }
        
        if (!('type' in blob)) {
            errorhandler({message: 'no_file_given'});
            return false;
        }
        
        var file = {
            id: null,
            key: null,
            blob: blob,
            size: blob.size,
            uploaded: 0,
            complete: false,
            name: file_name,
            mime_type: blob.type,
            node: source_node,
            transfer: this
        };

        // Look for dup
        for (var i = 0; i < this.files.length; i++) {
            if (this.files[i].name == file.name && this.files[i].size == file.size) {
                errorhandler({message: 'duplicate_file', details: {filename: file.name, size: file.size}});
                return false;
            }
        }
        
        if (filesender.config.max_transfer_files > 0 &&
            this.files.length >= filesender.config.max_transfer_files) {
            errorhandler({message: 'transfer_too_many_files', details: {max: filesender.config.max_transfer_files}});
            return false;
        }

        if (!file.size) {
            errorhandler({message: 'empty_file'});
            return false;
        }
        if( !this.checkIsValidFileSize( file, errorhandler )) {
            return false;
        }
                
        if (typeof window.filesender.config.ban_extension == 'string') {
            filesender.ui.log("TESTING WITH ban_extension  " + window.filesender.config.ban_extension);
            filesender.ui.log("TESTING WITH test2  " + window.filesender.config.test2);
            var banned = window.filesender.config.ban_extension.replace(/\s+/g, '');
            banned = new RegExp('^(' + banned.replace(/,/g, '|') + ')$', 'g');
            var extension = this.getExtention(file);
            if (extension.match(banned)) {
                errorhandler({message: 'banned_extension', details: {extension: extension, filename: file.name, banned: filesender.config.ban_extension}});
                
                return false;
            }
        }

        if (typeof filesender.config.extension_whitelist_regex == 'string') {
            var extension_whitelist = filesender.config.extension_whitelist_regex;
            var regex = new RegExp(extension_whitelist);
            var extension = this.getExtention(file);
            if (!extension.match(regex)) {
                errorhandler({ message: 'banned_extension_includes_bad_characters',
			       details: { extension: extension,
					  filename: file.name,
					  banned: filesender.config.extension_whitelist_regex}});
                
                return false;
            }
        }

        if (typeof filesender.config.valid_filename_regex == 'string') {
            var regexstr = filesender.config.valid_filename_regex;
            var r = XRegExp(regexstr,'g');
            var testResult = r.test(file.name);
            var lastIndex = r.lastIndex;
            if (lastIndex != file.name.length) {
                var badEnding = file.name.substring(lastIndex);
                window.filesender.log("invalid_file_name error raised for file name " + file.name
                                      + " with len " + file.name.length
                                      + " got lastIndex of " + r.lastIndex 
                                      + " badEnding will be " + badEnding );
                errorhandler({ message: 'invalid_file_name',
                               details: { filename: file.name, badoffset: lastIndex, badEnding: badEnding }});
                
                return false;
            }
        }
        
        if (this.size + file.size > filesender.config.max_transfer_size) {
            errorhandler({message: 'transfer_maximum_size_exceeded', details: {size: file.size, max: filesender.config.max_transfer_size}});
            return false;
        }

        
        var t = this.checkFileAsStillValid(file, function() {}, errorhandler);
        if( t === false )
            return t;
        
        if(filesender.config.quota && filesender.config.quota.available) {
            if (this.size + file.size > filesender.config.quota.available) {
                errorhandler({message: 'transfer_user_quota_exceeded', details: {available: filesender.config.quota.available}});
                return false;
            }
        }
        
        var files_cids = {};
        for(var i=0; i<this.files.length; i++) files_cids[this.files[i].cid] = true;
        
        var cid = 'file_' + (new Date()).getTime() + '_' + file.name.length + '_' + file.size + '_';
        var rnd = null;
        do {
            rnd = Math.round(Math.random() * 999999);
        } while(files_cids[cid + rnd]);
        cid += rnd;
        
        file.cid = cid;
        
        this.size += file.size;
        
        this.files.push(file);
        
        filesender.ui.log('Registered file ' + file.name + ' with size ' + file.size);
        
        return cid;
    };

    /**
     * Remove a file from list
     * 
     * @param int file index
     */
    this.removeFile = function(cid) {
        for (var i = 0; i < this.files.length; i++) {
            if (this.files[i].cid == cid) {
                this.size -= this.files[i].size;
                this.files.splice(i, 1);
                return;
            }
        }
    };

    /**
     * Remove a file from list
     * 
     * @param int file index
     */
    this.fileCIDToIndex = function(cid) {
        for (var i = 0; i < this.files.length; i++) {
            if (this.files[i].cid == cid) {
                return i;
            }
        }
        return -1;
    };
    
    /**
     * Add a recipient
     * 
     * @param string email address
     * 
     * @return bool indicates if the email was added (false means it was a duplicate or that there was an error)
     */
    this.addRecipient = function(email, errorhandler) {
        if (!errorhandler)
            errorhandler = filesender.ui.error;
        
        if (!email.match(filesender.ui.validators.email)) {
            errorhandler({message: 'invalid_recipient', details: {email: email}});
            return false;
        }
        
        for (var i = 0; i < this.recipients.length; i++)
            if (this.recipients[i] == email) {
                //errorhandler({message: 'duplicate_recipient', details: {email: email}});
                //return false;
                return true;
            }
        
        if (this.recipients.length >= filesender.config.max_transfer_recipients) {
            errorhandler({message: 'transfer_too_many_recipients', details: {max: filesender.config.max_transfer_recipients}});
            return false;
        }
        
        filesender.ui.log('Registered recipient with email ' + email);
        
        this.recipients.push(email);
    };

    /**
     * Remove a recipient from list
     * 
     * @param string email address
     */
    this.removeRecipient = function(email) {
        for (var i = 0; i < this.recipients.length; i++)
            if (this.recipients[i] == email) {
                this.recipients.splice(i, 1);
                return;
            }
    };
    
    /**
     * Check if restart is supported (local storage is available and html5 upload as well)
     */
    this.isRestartSupported = function() {
        try {
            return ('localStorage' in window) && (window['localStorage'] !== null) && filesender.supports.reader;
        } catch (e) {
            // Internet Explorer may throw an exception when trying to use localStorage
            // while it is forbidden for security reasons.  Returning false will keep
            // FileSender running, just disabling features that Internet Explorer won't
            // let us use.
            return false;
        }
    };
    
    /**
     * Get stored tracker
     * 
     * @param int optionnal transfer id
     * 
     * @return object
     */
    this.getRestartTracker = function(id) {
        if(!this.isRestartSupported()) return;
        
        var tracker = localStorage.getItem('restart_tracker');
        if(!tracker) tracker = '{}';
        
        tracker = JSON.parse(tracker);
        
        if(!id) return tracker;
        
        if(!tracker['transfer_' + id]) return null;
        
        return tracker['transfer_' + id];
    };
    
    /**
     * Store tracker
     * 
     * @param object tracker whole tracker or just transfer
     */
    this.storeRestartTracker = function(tracker) {
        if(!this.isRestartSupported()) return;
        
        if(tracker.id) { // Transfer
            var trk = this.getRestartTracker();
            trk['transfer_' + tracker.id] = tracker;
            this.storeRestartTracker(trk);
            return;
        }
        
        localStorage.setItem('restart_tracker', JSON.stringify(tracker));
    };
    
    /**
     * Remove tracker
     * 
     * @param int transfer id
     */
    this.removeRestartTracker = function(id) {
        if(!this.isRestartSupported()) return;
        
        var tracker = this.getRestartTracker();
        
        if(!tracker['transfer_' + id]) return;
        
        delete tracker['transfer_' + id];
        
        this.storeRestartTracker(tracker);
    };
    
    /**
     * Create local storage
     */
    this.createRestartTracker = function() {
        if(!this.isRestartSupported() || !this.id) return;
        
        var stored = {
            id: this.id,
            size: this.size,
            from: this.from,
            subject: this.subject,
            message: this.message,
            expires: this.expires,
            status: this.status,
            recipients: this.recipients,
            options: this.options,
            files: [],
            file_index: 0,
            guest_token: null,
            download_link: this.download_link,
            roundtriptoken: this.roundtriptoken
        };
        
        for(var i=0; i<this.files.length; i++) {
            stored.files.push({
                id: this.files[i].id,
                uid: this.files[i].uid,
                cid: this.files[i].cid,
                size: this.files[i].size,
                uploaded: 0,
                complete: false,
                name: this.files[i].name,
                mime_type: this.files[i].mime_type
            });
        }
        
        this.storeRestartTracker(stored);
    };
    
    /**
     * Update file in storage
     * 
     * @param object file
     */
    this.updateFileInRestartTracker = function(file) {
        if(!this.isRestartSupported() || !this.id) return;
        
        var tracker = this.getRestartTracker(this.id);
        if(!tracker) return;
        
        for(var i=0; i<tracker.files.length; i++) if(tracker.files[i].id == file.id) {
            tracker.files[i].uploaded = file.min_uploaded_offset ? file.min_uploaded_offset : file.uploaded;
            tracker.files[i].complete = file.complete;
        }
        
        this.storeRestartTracker(tracker);
    };
    
    /**
     * Remove transfer from local storage (complete / user request)
     * 
     * @param int id
     */
    this.removeFromRestartTracker = function(id) {
        if(!this.isRestartSupported()) return;
        
        if(!id) id = this.id;
        if(!id) return;
        
        this.removeRestartTracker(id);
    };
    
    /**
     * Check if there is a failed transfer in storage
     */
    this.isThereFailedInRestartTracker = function() {
        if(!this.isRestartSupported()) return null;
        
        var tracker = this.getRestartTracker();
        
        for(var key in tracker) return tracker[key]; // TODO multi fail support, now only takes 1st one
        
        return null;
    };
    
    /**
     * Load failed transfer from storage
     * 
     * @param int id
     */
    this.loadFailedFromRestartTracker = function(id) {
        if(!this.isRestartSupported()) return;
        
        var tracker = this.getRestartTracker(id);
        if(!tracker) return;
        
        for(var prop in tracker) switch(prop) {
            case 'files': break;
            default: this[prop] = tracker[prop];
        }
        this.download_link = tracker.download_link;
        
        this.failed_transfer_restart = true;
        
        filesender.ui.log('Loaded failed transfer from tracker');
        
        return tracker.files;
    };
    
    /**
     * Restart failed transfer
     */
    this.restartFailedTransfer = function() {
        if(!this.isRestartSupported() || !this.id) return;
        
        var tracker = this.getRestartTracker(this.id);
        if(!tracker) return;
        
        // Check files
        for(var i=0; i<tracker.files.length; i++) {
            for(var j=0; j<this.files.length; j++) {
                if(
                    (this.files[j].name == tracker.files[i].name) &&
                    (this.files[j].size == tracker.files[i].size)
                ) {
                    for(var prop in tracker.files[i])
                        this.files[j][prop] = tracker.files[i][prop];
                    
                    this.files[j].transfer = this;
                }
            }
        }
        
        for(var i=0; i<tracker.files.length; i++) {
            if(!tracker.files[i].id) {
                // We are missing a file
                filesender.ui.alert('error', lang.tr('missing_files_for_restart'));
                return false;
            }
        }
        
        // From here we should have everything we need to restart
        filesender.ui.log('Restarting failed transfer #' + this.id);
        for(var i=0; i<tracker.files.length; i++) {
            filesender.ui.log('uploaded: ' + tracker.files[i].uploaded);
        }

        this.roundtriptoken = tracker.roundtriptoken;
        this.time = (new Date()).getTime();
        
        // Start uploading chunks
        if (this.canUseTerasender()) {
            filesender.terasender.start(this);
        } else {
            // Chunk by chunk upload
            this.uploadChunk();
        }
        
        return true;
    };

    /**
     * Register an uploading process
     * 
     * @param string id process identifier
     */
    this.registerProcessInWatchdog = function(id) {
        this.watchdog_processes[id] = {
            count: 0,
            durations: [],
            started: null,
            file: null,
            progressTracker: new window.filesender.progresstracker(),
        };
    };
    
    /**
     * Record chunk upload started for worker
     * 
     * @param string id process identifier
     */
    this.recordUploadStartedInWatchdog = function(id,file) {
        if(!(id in this.watchdog_processes)) this.registerProcessInWatchdog(id);
        
        this.watchdog_processes[id].started = (new Date()).getTime();
        this.watchdog_processes[id].file = file;
        this.watchdog_processes[id].progressTracker.clear();
        
    };
    this.touchAllUploadStartedInWatchdog = function() {
        
        for(var id in this.watchdog_processes) {
            this.watchdog_processes[id].started = (new Date()).getTime();
            this.watchdog_processes[id].progressTracker.clear();
        }
    };

    /**
     * Record chunk upload progress for worker
     */
    this.recordUploadProgressInWatchdog = function(id,fine_progress) {
        this.watchdog_processes[id].progressTracker.remember( fine_progress );
    };
    
    /**
     * Record chunk upload from worker
     * 
     * @param string id process identifier
     */
    this.recordUploadedInWatchdog = function(id) {
        
        if(!(id in this.watchdog_processes)) this.registerProcessInWatchdog(id);
        
        if(this.watchdog_processes[id].started == null) return;

        this.watchdog_processes[id].progressTracker.chunkCompleted();
        
        this.watchdog_processes[id].count++;
        this.watchdog_processes[id].durations.push((new Date()).getTime() - this.watchdog_processes[id].started);
        while(this.watchdog_processes[id].durations.length > this.stalling_detection.durations_horizon) {
            this.watchdog_processes[id].durations.shift();
        }
        this.watchdog_processes[id].started = null;    
    };

    /**
     * for a nominated file
     * return an array with entries for each worker 
     * (only one "worker" for non terasender)
     *
     * each item is either -1 if the worker is not active on the file
     * or the duraction that the worker has been active on the current
     * chunk for the nominated file
     * 
     * the return value will be "stable" for an active transfer. So
     * that worker 0 will always be the first worker in the return
     * value this helps you present the information to the UI.
     */
    this.getMostRecentChunkDurations = function(file) {
        var d = [];
        var i = 0;
        for(var id in this.watchdog_processes) {
            d[i] = -1;
            if(this.watchdog_processes[id].started != null) {
                if( this.watchdog_processes[id].file.name == file.name ) {
                    var duration = (new Date()).getTime() - this.watchdog_processes[id].started;
                    d[i] = duration;
                }
            }
            i++;
        }
        return d;
    };

    /**
     * for a nominated file
     * return an array with entries for each worker 
     * (only one "worker" for non terasender)
     *
     * each item is the number of bytes transfered between the current and previous progress reported
     * by the worker
     * 
     * the return value will be "stable" for an active transfer. So
     * that worker 0 will always be the first worker in the return
     * value this helps you present the information to the UI.
     */
    this.getMostRecentChunkFineBytes = function(file) {
        var d = [];
        var i = 0;
        for(var id in this.watchdog_processes) {
            d[i] = -1;
            if(this.watchdog_processes[id].started != null) {
                if( this.watchdog_processes[id].file.name == file.name ) {
                    var bc = this.watchdog_processes[id].progressTracker.latest();
                    d[i] = bc;
                }
            }
            i++;
        }
        return d;
    };

    /**
     * for a nominated file
     * return an array with entries for each worker 
     * (only one "worker" for non terasender)
     *
     * each item is true if the worker has not progressed enough
     * recently. the user might like to know or the whole chunk could
     * be restarted for the user.
     * 
     * the return value will be "stable" for an active transfer. So
     * that worker 0 will always be the first worker in the return
     * value this helps you present the information to the UI.
     */
    this.getIsWorkerOffending = function(file) {
        var d = [];
        var i = 0;
        for(var id in this.watchdog_processes) {
            d[i] = false;
            if(this.watchdog_processes[id].started != null) {
                if( this.watchdog_processes[id].file.name == file.name ) {
                    var v = this.watchdog_processes[id].progressTracker.isOffending();
                    d[i] = v;
                }
            }
            i++;
        }
        return d;
    };
    
    /**
     * Look for stalled processes
     */
    this.getStalledProcesses = function() {
        if(this.status != 'running') return null;
        
        if(!this.stalling_detection) return null;
        
        var stalled = [];

        // Compute average upload time and progress
        var avg_count = 0;
        var pcnt = 0;
        for(var id in this.watchdog_processes) {
            pcnt++;
            avg_count += this.watchdog_processes[id].count;
        }        
        for(var id in this.watchdog_processes) {
            // clear old caches
            this.watchdog_processes[id].durations_average = 0;
            this.watchdog_processes[id].durations_count   = 0;
            this.watchdog_processes[id].durations_max     = 0;

            // prepare to calc average for process
            // keep the max time a recent chunk took as well
            for(var i=0; i<this.watchdog_processes[id].durations.length; i++) {
                this.watchdog_processes[id].durations_average += this.watchdog_processes[id].durations[i];
                this.watchdog_processes[id].durations_max = Math.max(this.watchdog_processes[id].durations_max,
                                                                     this.watchdog_processes[id].durations[i]);
            }
            
            // convert the sum to the mean
            if( this.watchdog_processes[id].durations.length ) {
                this.watchdog_processes[id].durations_average /= this.watchdog_processes[id].durations.length;
            }
        }
        if(pcnt) avg_count /= pcnt;
        
        var way_too_late = false;
        
        // Look for processes that seems "late"
        for(var id in this.watchdog_processes) {
            
            if(this.watchdog_processes[id].started == null) continue;
            
            var duration = (new Date()).getTime() - this.watchdog_processes[id].started;
            
            if(duration > 3600 * 1000) // 1h, CSRF token lifetime
                way_too_late++;

            //
            // the purpose of early_scale is to allow the system to be
            // more open to the duration varying when we do not
            // already have time data for a lot of chunks.
            //
            // On the first chunk or two we allow multiple times the
            // stalling_detection.duration_divergence to pass before
            // we think something is wrong because we have little
            // support to know if current performance is a divergence
            // from the past.
            //
            // over time early_scale will tend towards being "1" this
            // will be when we have all entries up to
            // durations_horizon so at that time we do not add any
            // extra slack to the system
            //
            var early_scale = this.stalling_detection.durations_horizon - this.watchdog_processes[id].durations.length + 1;
            var duration_divergence = this.stalling_detection.duration_divergence * early_scale;
            
            if( this.watchdog_processes[id].durations_count ) {
                if(duration > this.watchdog_processes[id].durations_average * duration_divergence) {
                    // Process is too late in terms of number of upload duration
                    stalled.push(id);
                    continue;
                }
            }
        }
        
        if(way_too_late == pcnt) { // All processes are did nothing during the last hour, CSRF token may have expired
            filesender.ui.log('Nothing happened over more than 1h, refreshing security token the hard way');
            
            filesender.client.get('', function(html) {
                var m = html.match(/<body\s[^>]*data-security-token="([0-9a-f-]+)"/);
                if(!m) return;
                
                filesender.client.updateSecurityToken(m[1]);
            }, {
                url: window.location.href,
                dataType: 'html',
                async: false
            });
            
            if(filesender.ui.transfer) {
                filesender.ui.transfer.retry();
                return null; // Avoid default stalled message => silent restart
            }
        }
        
        if(stalled.length) filesender.ui.log('Stalled processes detected = ' + stalled.length);
        
        return stalled.length ? stalled : null;
    };
    
    /**
     * Reset watchdog
     */
    this.resetWatchdog = function() {
        this.watchdog_processes = {};
    };
    
    /**
     * Get path with authentication args if needed
     * 
     * @param string resource
     * @param object file if file context
     * 
     * @return string
     */
    this.authenticatedEndpoint = function(resource, file) {
        var args = {};
        if(filesender.config.chunk_upload_security == 'key' && (file || (this.files.length && this.files[0].uid))) {
            if(file) {
                args.key = file.uid;
            } else if(this.files.length && this.files[0].uid) {
                args.key = this.files[0].uid;
            }
        }
        
        if(this.guest_token)
            args.vid = this.guest_token;

        if(this.roundtriptoken)
            args.roundtriptoken = this.roundtriptoken;
        
        var q = [];
        for(var k in args) q.push(k + '=' + args[k]);
        
        if(q.length) resource += (resource.match(/\?/) ? '&' : '?') + q.join('&');
        
        return resource;
    };
    
    /**
     * Report progress
     * 
     * @param object file
     * @param callable complete is file done
     */
    this.reportProgress = function(file, complete) {
        var now = (new Date()).getTime();
        
        file.progress_reported = now;
        
        if(complete && file.status == 'done') return; // Already reported
        
        if(complete) file.status = 'done';
        
        if (complete) {
            filesender.ui.log('File ' + file.name + ' (' + file.size + ' bytes) uploaded');
        } else {
            var uploaded = file.fine_progress ? file.fine_progress : file.uploaded;
            filesender.ui.log('Uploading ' + file.name + ' (' + file.size + ' bytes) : ' + (100 * uploaded / file.size).toFixed(2) + '%');
        }
        
        if (complete) {
            var transfer = this;
            window.setTimeout(function() {
                filesender.client.fileComplete(file, undefined, function(data) {
                    transfer.updateFileInRestartTracker(file);
                    
                    if (transfer.onprogress)
                        transfer.onprogress.call(transfer, file, true);
                    
                    complete();
                }, function(error) {
                    window.filesender.log("transfer encountered an error: " + JSON.stringify(error));
                    if (error.message === 'file_integrity_check_failed') {
                        // reset the file progress to make it retry the whole file
                        file.fine_progress = 0;
                        file.fine_progress_done = 0;
                        file.progress_reported = 0;
                        file.uploaded = 0;
                        file.status = '';
                        file.complete = false;
                        file.min_uploaded_offset = 0;
                        transfer.updateFileInRestartTracker(file);
                        transfer.reportError(error);
                    }
                });
            }, 100);//750);
        } else if (this.onprogress) {
            this.updateFileInRestartTracker(file);
            this.onprogress.call(this, file, false);
        } else {
	    window.filesender.log("transfer has not onprogress");
	}
    };
    
    /**
     * Report transfer complete
     */
    this.reportComplete = function() {
        if(this.status == 'done') return; // Already reported
        
        this.status = 'done';
        
        var time = (new Date()).getTime() - this.time; // ms
        
        filesender.ui.log('Transfer ' + this.id + ' (' + this.size + ' bytes) complete, took ' + (time / 1000) + 's');
        
        var transfer = this;
        window.setTimeout(function() {
            filesender.client.transferComplete(transfer, undefined, function(data) {
                transfer.removeFromRestartTracker();
                
                if (transfer.oncomplete)
                    transfer.oncomplete.call(transfer, time);
            });
        }, 300);//1500); //so it doesnt miss the last chunk
    };
    
    /**
     * Report transfer error
     */
    this.reportError = function(error) {
        filesender.ui.log('Transfer ' + this.id + ' (' + this.size + ' bytes) failed');
        
        if (this.onerror) {
            this.onerror.call(this, error);
        } else {
            filesender.ui.error(error);
        }
    };
    
    /**
     * Start upload
     */
    this.start = function(errorhandler) {
        if (!errorhandler)
            errorhandler = filesender.ui.error;
        
        this.status = 'running';

        if(this.failed_transfer_restart) {
            return this.restartFailedTransfer(errorhandler);
        }
        
        // Redo sanity checks
        if (filesender.config.max_transfer_files > 0 &&
            this.files.length > filesender.config.max_transfer_files) {
            return errorhandler({message: 'transfer_too_many_files', details: {max: filesender.config.max_transfer_files}});
        }
        for (var i = 0; i < this.files.length; i++) {
            if( !this.checkIsValidFileSize( this.files[i], errorhandler )) {
                return false;
            }
        }

        // No AEAD unless we explicitly set some below for encrypted files.
        for (var i = 0; i < this.files.length; i++) {
            this.files[i].aead = '';
        }
        
        if( this.encryption ) {
            var crypter = window.filesender.crypto_app();
            
            // Generate IV for each crypted file.
            for (var i = 0; i < this.files.length; i++) {
                this.files[i].iv = crypter.generateCryptoFileIV();
            }

            // Setup AEAD if we can use it
            for (var i = 0; i < this.files.length; i++) {
                var aead = crypter.generateAEAD( this.files[i] );
                if( aead ) {
                    this.files[i].aead = crypter.encodeAEAD( aead );
                }
            }
            
        }
        
        if (this.size > filesender.config.max_transfer_size) {
            return errorhandler({message: 'transfer_maximum_size_exceeded', details: {size: file.size, max: filesender.config.max_transfer_size}});
        }
        
        var today = Math.floor((new Date()).getTime() / (24 * 3600 * 1000));
        var minexpires = today - 1;
        var maxexpires = today + filesender.config.max_transfer_days_valid + 1;
        var exp = this.expires / (24 * 3600);
        
        if (exp < minexpires || exp > maxexpires) {
            return errorhandler({message: 'bad_expire'});
        }
        
        filesender.ui.log('Creating transfer');
        
        this.time = (new Date()).getTime();

        if( this.encryption ) {
            this.encryption_client_entropy = window.filesender.crypto_app().generateClientEntropy();
        }
        
        var transfer = this;
        filesender.client.postTransfer(this, function(path, data) {
            transfer.id = data.id;
            transfer.encryption_salt = data.salt;
            transfer.roundtriptoken  = data.roundtriptoken;
            
            for (var i = 0; i < transfer.files.length; i++) {
                for (var j = 0; j < data.files.length; j++) {
                    if(
                        (
                            data.files[j].cid
                            && transfer.files[i].cid
                            && (data.files[j].cid == transfer.files[i].cid)
                        ) || (
                            (data.files[j].name == transfer.files[i].name) &&
                            (data.files[j].size == transfer.files[i].size)
                        )
                    ) {
                        transfer.files[i].id = data.files[j].id;
                        transfer.files[i].uid = data.files[j].uid;
                    }
                }
                
                if (!transfer.files[i].id)
                    return errorhandler({message: 'file_not_in_response', details: {file: transfer.files[i]}});
            }

            if('get_a_link' in transfer.options && transfer.options.get_a_link) {
                transfer.download_link = data.recipients[0].download_url;
            }
            
            transfer.createRestartTracker();
            
            filesender.ui.log('Transfer created, staring upload');
            
            if(filesender.supports.reader) {
                // Start uploading chunks
                if (transfer.canUseTerasender()) {
                    filesender.terasender.start(transfer);
                } else {
                    // Chunk by chunk upload
                    window.filesender.log('*** Not using terasender ***');
                    transfer.registerProcessInWatchdog('main');
                    transfer.uploadChunk();
                }
            } else {
                // Legacy upload
                window.filesender.log('*** Warning: using legacy upload ***');
                transfer.uploadWhole();
            }
        }, function(error) {
            transfer.reportError(error);
        });
    };
    
    /**
     * Pause upload
     */
    this.pause = function() {
        if (this.status != 'running')
            return;
        
        this.pause_time = (new Date()).getTime();
        
        this.status = 'paused';
        
        if (this.canUseTerasender())
            filesender.terasender.pause();
    };

    /**
     * Resume upload
     */
    this.resume = function() {
        if (this.status != 'paused')
            return;
        
        this.pause_length += (new Date()).getTime() - this.pause_time;
        
        this.status = 'running';
        
        if (this.canUseTerasender())
            filesender.terasender.restart();
    };

    /**
     * Stop upload
     */
    this.stop = function(callback) {
        this.status = 'stopped';
        
        if (this.canUseTerasender())
            filesender.terasender.stop();
        
        this.removeFromRestartTracker();
        
        filesender.ui.log('Transfer stopped, deleting created data');
        
        var transfer = this;
        window.setTimeout(function() { // Small delay to let workers stop
            filesender.client.deleteTransfer(transfer, callback);
        }, 1000);
    };

    /**
     * Retry upload
     */
    this.retries = 0;
    this.retry = function(manual) {

        // ensure in paused state
        this.pause();
        
        if(manual) {
            this.retries = 0;
            
        } else {
            if(this.stalling_detection)
                if(this.retries >= this.stalling_detection.automatic_retries)
                    return false;
        }
        this.retries++;
        
        filesender.ui.log('Data sending failed, retrying from last known offsets');
        
        this.status = 'running';
        if (this.canUseTerasender()) {
            filesender.terasender.retry();
        } else {
            this.uploader.abort();
            this.files[this.file_index].uploaded -= filesender.config.upload_chunk_size;
            this.uploadChunk();
        }
        
        return true;
    };

    
    /**
     * Enter/exit maintenance mode
     * 
     * @param bool state
     */
    this.maintenance = function(state) {
        if(!state && this.maintenance_status) {
            this.status = this.maintenance_status;
            this.maintenance_status = null;
        }
        
        if(state && !this.maintenance_status) {
            this.maintenance_status = this.status;
            this.status = 'maintenance';
            
            // Wipe watchdog as timings won't be relevant upon restart
            for(var id in this.watchdog_processes) {
                this.watchdog_processes[id].count = 0;
                this.watchdog_processes[id].durations = [];
            }
        }
    };
    
    /**
     * Chunk by chunk upload
     */
    this.uploadChunk = function() {

        if (this.status == 'stopped')
            return;
        
        var transfer = this;
        if (this.status == 'paused') {
            window.setTimeout(function() {
                transfer.uploadChunk();
            }, 500);
            return;
        }
        
        var file = this.files[this.file_index];
        var offset = file.uploaded;
        var end = offset + filesender.config.upload_chunk_size;
        
        filesender.ui.log('Uploading chunk [' + offset + ' .. ' + end + '] from file ' + file.name);
        
        var slicer = file.blob.slice ? 'slice' : (file.blob.mozSlice ? 'mozSlice' : (file.blob.webkitSlice ? 'webkitSlice' : 'slice'));

        var blob = file.blob[slicer](offset, end, "application/octet-stream");
        var file_uploaded_when_chunk_complete = end;
        if (file_uploaded_when_chunk_complete > file.size)
            file_uploaded_when_chunk_complete = file.size;
        
        var last = file_uploaded_when_chunk_complete >= file.size;
        var fncache = file.name;
        if (last)
            this.file_index++;
        var was_last_file = transfer.file_index >= transfer.files.length;

        // this saves having to think about the id for the watchdog calls.
        var worker_id = 'main';
        this.recordUploadStartedInWatchdog(worker_id,file);

        var encryption_details = transfer.getEncryptionMetadata( file );
        
        this.uploader = filesender.client.putChunk(
            file, blob, offset,
            function(ratio) { // Progress
                var chunk_size = Math.min(file.size - file_uploaded_when_chunk_complete, filesender.config.upload_chunk_size);
                file.fine_progress = Math.floor(file_uploaded_when_chunk_complete + ratio * chunk_size);
                transfer.recordUploadProgressInWatchdog(worker_id,ratio * chunk_size);
                transfer.reportProgress(file);
            },
            function() { // Done
                transfer.recordUploadedInWatchdog(worker_id);
                file.uploaded = file_uploaded_when_chunk_complete;
                
                if (last) { // File done
                    transfer.reportProgress(file, function() {
                        if(was_last_file) {
                            transfer.reportComplete();
                        }
                    });
                    
                    
                } else {
                    transfer.reportProgress(file);
                }
                
                if(!last || transfer.file_index < transfer.files.length)
                    transfer.uploadChunk();
            },
            function(error) {
                transfer.reportError(error);
            },
            transfer.encryption, encryption_details
        );
    };

    /**
     * Legacy whole file upload
     */
    this.uploadWhole = function() {
        if (this.status == 'stopped')
            return;

        if(this.file_index >= this.files.length) { // Done
            this.reportComplete();
            return;
        }
        
        var file = this.files[this.file_index];
        this.file_index++;
        
        filesender.ui.log('Uploading whole file ' + file.name + ' with size ' + file.size + ' using legacy mode');
        
        var transfer = this;
        
        if(typeof this.tracking == 'undefined') {
            var keyfield = $(':input[data-role="legacy_upload_tracking_key"]');
            if(keyfield.length) {
                transfer.tracking = {key: keyfield.val(), field: keyfield, file: null, timer: null};
                
                transfer.tracking.timer = window.setInterval(function() {
                    if(!transfer.tracking.file) return;
                    if(transfer.tracking.file.uploaded >= transfer.tracking.file.size) return;
                    
                    filesender.client.getLegacyUploadProgress(
                        transfer.tracking.key,
                        function(data) {
                            filesender.ui.log('Got progress info : ' + JSON.stringify(data));
                            if(!data) { // Tracking does not work or upload ended for file
                                filesender.ui.log('No upload progress info');
                                return;
                            }
                            
                            transfer.tracking.file.uploaded = data.bytes_processed;
                            transfer.reportProgress(transfer.tracking.file, transfer.tracking.file.uploaded >= transfer.tracking.file.size);
                        },
                        function() { // Error, tracking does not work
                            filesender.ui.log('Upload progress fetching failed', arguments);
                        });
                }, filesender.config.legacy_upload_progress_refresh_period * 1000);
            } else transfer.tracking = null;
        }
        
        if(transfer.tracking) transfer.tracking.file = file;
        
        if(!this.legacy.iframe) {
            this.legacy.uid = 'transfer_' + transfer.id + '_' + (new Date()).getTime();
            this.legacy.iframe = $('<iframe name="' + this.legacy.uid + '"/>').appendTo($('<div id="legacy_uploader" />').appendTo('body'));
            window.legacyUploadResultHandler = function(data) {
                filesender.ui.log('Upload frame done : ' + JSON.stringify(data));
                if(data.message && data.uid) { // Seems to be an error
                    filesender.ui.error(data);
                    return;
                }
                
                if(data.security_token) filesender.client.security_token = data.security_token;
                
                transfer.tracking.file.uploaded = transfer.tracking.file.size;
                transfer.reportProgress(transfer.tracking.file, true);
                transfer.uploadWhole();
            };
        }
        
        if(this.legacy.form) this.legacy.form.remove();
        
        var url = this.authenticatedEndpoint(filesender.config.legacy_upload_endpoint.replace(/\{file_id\}/g, file.id), file);
        url += (url.match(/\?/) ? '&' : '?') + 'iframe_callback=legacyUploadResultHandler';
        
        this.legacy.form = $('<form method="post" enctype="multipart/form-data" />').attr({
            action: url,
            target: this.legacy.uid
        }).appendTo(this.legacy.iframe.parent());
        
        $('<input type="hidden" />').attr({
            name: 'security-token',
            value: filesender.client.security_token
        }).appendTo(this.legacy.form);
        
        if(transfer.tracking) $('<input type="hidden" />').attr({
            name: transfer.tracking.field.attr('name'),
            value: transfer.tracking.field.val()
        }).appendTo(this.legacy.form);
        
        this.legacy.form.append(file.node);
        
        this.legacy.form.submit();
    };

    /**
     * Total size of this transfer
     */
    this.getTotalSize = function() {

        var total_size = 0;
        for(var j=0; j < this.files.length; j++)
            total_size += this.files[j].size;
        return total_size;
    };
    this.getFileCount = function() {
        return this.files.length;
    };
};
