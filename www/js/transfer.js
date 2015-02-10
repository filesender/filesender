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
    this.options = [];
    this.time = 0;
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

    /**
     * Max count divergence (the maximum chunk count a process can be late over the global average)
     */
    this.watchdog_max_count_divergence = 7;
    
    /**
     * Max duration divergence (the maximum ratio between a process's current upload time and the global average)
     */
    this.watchdog_max_duration_divergence = 5;
    
    /**
     * Max automatic retries
     */
    this.max_automatic_retries = 3;
    
    this.watchdog_processes = {};
    
    /**
     * Add a file to the file list
     * 
     * @param object file HTML input / FileList / File
     * 
     * @return mixed int file index or false if it was a duplicate or that there was an error
     */
    this.addFile = function(file, errorhandler, source_node) {
        if (!errorhandler)
            errorhandler = filesender.ui.error;

        if (!file)
            return errorhandler({message: 'no_file_given'});

        if ('parentNode' in file) // HTML file input
            file = file.files;

        if ('length' in file) { // FileList
            if (!file.length) {
                errorhandler({message: 'no_file_given'});
                return false;
            }

            for (var i = 0; i < file.length; i++)
                this.addFile(file[i]);

            return;
        }

        if (!('type' in file)) {
            errorhandler({message: 'no_file_given'});
            return false;
        }

        var blob = file;
        var file = {
            id: null,
            key: null,
            blob: blob,
            size: blob.size,
            uploaded: 0,
            complete: false,
            name: blob.name,
            mime_type: blob.type,
            node: source_node,
            transfer: this
        };

        // Look for dup
        for (var i = 0; i < this.files.length; i++) {
            if (this.files[i].name == file.name && this.files[i].size == file.size) {
                errorhandler({message: 'duplicate_file', details: {name: file.name, size: file.size}});
                return false;
            }
        }

        if (this.files.length >= filesender.config.max_transfer_files) {
            errorhandler({message: 'max_transfer_files_exceeded', details: {max: filesender.config.max_transfer_files}});
            return false;
        }

        if (!/^[^\\\/:;\*\?\"<>|]+(\.[^\\\/:;\*\?\"<>|]+)*$/.test(file.name)) {
            errorhandler({message: 'invalid_file_name'});
            return false;
        }

        if (!file.size) {
            errorhandler({message: 'empty_file'});
            return false;
        }

        if (typeof filesender.config.ban_extension == 'string') {
            var banned = filesender.config.ban_extension.replace(/\s+/g, '');
            banned = new RegExp('^(' + banned.replace(',', '|') + ')$', 'g');
            var extension = file.name.split('.').pop();
            if (extension.match(banned)) {
                errorhandler({message: 'banned_extension', details: {extension: extension, filename: file.name, banned: filesender.config.ban_extension}});

                return false;
            }
        }

        if (this.size + file.size > filesender.config.max_transfer_size) {
            errorhandler({message: 'max_transfer_size_exceeded', details: {size: file.size, max: filesender.config.max_transfer_size}});
            return false;
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
                this.files.splice(i, 1);
                return;
            }
        }
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
                errorhandler({message: 'duplicate_recipient', details: {email: email}});
                return false;
            }

        if (this.recipients.length >= filesender.config.max_transfer_recipients) {
            errorhandler({message: 'max_transfer_recipients_exceeded', details: {max: filesender.config.max_transfer_recipients}});
            return false;
        }

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
        return ('localStorage' in window) && (window['localStorage'] !== null) && filesender.supports.reader;
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
            guest_token: null
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
        
        this.failed_transfer_restart = true;
        
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
        
        this.time = (new Date()).getTime();
        
        // Start uploading chunks
        if (filesender.config.terasender_enabled && filesender.supports.workers) {
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
            started: null
        };
    };
    
    /**
     * Record chunk upload started from process
     * 
     * @param string id process identifier
     */
    this.recordUploadStartedInWatchdog = function(id) {
        if(!(id in this.watchdog_processes)) this.registerProcessInWatchdog(id);
        
        this.watchdog_processes[id].started = (new Date()).getTime();
    };
    
    /**
     * Record chunk upload from process
     * 
     * @param string id process identifier
     */
    this.recordUploadedInWatchdog = function(id) {
        if(!(id in this.watchdog_processes)) this.registerProcessInWatchdog(id);
        
        if(this.watchdog_processes[id].started == null) return;
        
        this.watchdog_processes[id].count++;
        this.watchdog_processes[id].durations.push((new Date()).getTime() - this.watchdog_processes[id].started);
        while(this.watchdog_processes[id].durations.length > 5) this.watchdog_processes[id].durations.shift();
        
        this.watchdog_processes[id].started = null;
    };
    
    /**
     * Look for stalled processes
     */
    this.getStalledProcesses = function() {
        var stalled = [];
        
        // Compute average upload time and progress
        var avg_count = 0;
        var pcnt = 0;
        var avg_duration = 0;
        var dcnt = 0;
        for(var id in this.watchdog_processes) {
            pcnt++;
            avg_count += this.watchdog_processes[id].count;
            for(var i=0; i<this.watchdog_processes[id].durations.length; i++) {
                avg_duration += this.watchdog_processes[id].durations[i];
                dcnt++;
            }
        }
        avg_count /= pcnt;
        avg_duration /= dcnt;
        
        // Look for processes that seems "late"
        for(var id in this.watchdog_processes) {
            if(this.watchdog_processes[id].count < avg_count - this.watchdog_max_count_divergence) {
                // Process is too late in terms of number of uploaded chunks
                stalled.push(id);
                continue;
            }
            
            if(this.watchdog_processes[id].started == null) continue;
            
            var duration = (new Date()).getTime() - this.watchdog_processes[id].started;
            
            if(duration > avg_duration * this.watchdog_max_duration_divergence) {
                // Process is too late in terms of number of upload duration
                stalled.push(id);
                continue;
            }
        }
        
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
        } else if(this.guest_token) {
            args.vid = this.guest_token;
        }
        
        var q = [];
        for(var k in args) q.push(k + '=' + args[k]);
        
        if(q.length) resource += (resource.match(/\?/) ? '&' : '?') + q.join('&');
        
        return resource;
    };
    
    /**
     * Report progress
     * 
     * @param object file
     * @param bool complete is file done
     */
    this.reportProgress = function(file, complete) {
        if (filesender.config.log) {
            if (complete) {
                console.log('File ' + file.name + ' (' + file.size + ' bytes) uploaded');
            } else {
                console.log('Uploading ' + file.name + ' (' + file.size + ' bytes) : ' + (100 * file.uploaded / file.size).toFixed(2) + '%');
            }
        }

        if (complete) {
            var transfer = this;
            filesender.client.fileComplete(file, undefined, function(data) {
                transfer.updateFileInRestartTracker(file);
                
                if (transfer.onprogress)
                    transfer.onprogress.call(transfer, file, true);
            });
        } else if (this.onprogress) {
            this.updateFileInRestartTracker(file);
            this.onprogress.call(this, file, false);
        }
    };

    /**
     * Report transfer complete
     */
    this.reportComplete = function() {
        this.status = 'done';

        var time = (new Date()).getTime() - this.time; // ms

        if (filesender.config.log) {
            console.log('Transfer ' + this.id + ' (' + this.size + ' bytes) complete, took ' + (time / 1000) + 's');
        }

        var transfer = this;
        
        filesender.client.transferComplete(this, undefined, this.guest_token, function(data) {
            transfer.removeFromRestartTracker();
            
            if (transfer.oncomplete)
                transfer.oncomplete.call(transfer, time);
        });
    };

    /**
     * Report transfer error
     */
    this.reportError = function(error) {
        if (filesender.config.log) {
            console.log('Transfer ' + this.id + ' (' + this.size + ' bytes) failed');
        }

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
        
        if (this.files.length >= filesender.config.max_transfer_files) {
            return errorhandler({message: 'max_transfer_files_exceeded', details: {max: filesender.config.max_transfer_files}});
        }
        
        if (this.size > filesender.config.max_transfer_size) {
            return errorhandler({message: 'max_transfer_size_exceeded', details: {size: file.size, max: filesender.config.max_transfer_size}});
        }
        
        var today = Math.floor((new Date()).getTime() / (24 * 3600 * 1000));
        var minexpires = today - 1;
        var maxexpires = today + filesender.config.default_days_valid + 1;
        var exp = this.expires / (24 * 3600);
        
        if (exp < minexpires || exp > maxexpires) {
            return errorhandler({message: 'bad_expire'});
        }
        
        this.time = (new Date()).getTime();
        
        var transfer = this;
        filesender.client.postTransfer(this, function(path, data) {
            transfer.id = data.id;

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
            
            if((',' + transfer.options.join(',') + ',').match(/,get_a_link,/))
                transfer.download_link = data.recipients[0].download_url;
            
            transfer.createRestartTracker();
            
            if(filesender.supports.reader) {
                // Start uploading chunks
                if (filesender.config.terasender_enabled && filesender.supports.workers) {
                    filesender.terasender.start(transfer);
                } else {
                    // Chunk by chunk upload
                    transfer.registerProcessInWatchdog('main');
                    transfer.uploadChunk();
                }
            } else {
                // Legacy upload
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

        if (filesender.config.terasender_enabled && filesender.supports.workers)
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

        if (filesender.config.terasender_enabled && filesender.supports.workers)
            filesender.terasender.restart();
    };

    /**
     * Stop upload
     */
    this.stop = function(callback) {
        this.status = 'stopped';
        
        if (filesender.config.terasender_enabled && filesender.supports.workers)
            filesender.terasender.stop();
        
        this.removeFromRestartTracker();
        
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
        if(manual) this.retries = 0;
        if(this.retries >= this.max_automatic_retries) return false;
        this.retries++;
        
        if (filesender.config.terasender_enabled && filesender.supports.workers) {
            filesender.terasender.retry();
        } else {
            this.uploader.abort();
            this.files[this.file_index].uploaded -= filesender.config.upload_chunk_size;
            this.uploadChunk();
        }
        
        return true;
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
        
        var slicer = file.blob.slice ? 'slice' : (file.blob.mozSlice ? 'mozSlice' : (file.blob.webkitSlice ? 'webkitSlice' : 'slice'));
        
        var offset = file.uploaded;
        var blob = file.blob[slicer](offset, offset + filesender.config.upload_chunk_size);
        
        file.uploaded += filesender.config.upload_chunk_size;
        if (file.uploaded > file.size)
            file.uploaded = file.size;
        
        var last = file.uploaded >= file.size;
        if (last)
            this.file_index++;
        
        this.recordUploadStartedInWatchdog('main');
        
        this.uploader = filesender.client.putChunk(file, blob, offset, function() {
            transfer.recordUploadedInWatchdog('main');
            
            if (last) { // File done
                transfer.reportProgress(file, true);
            } else {
                transfer.reportProgress(file);
            }
            
            if(! last || transfer.file_index < transfer.files.length) {
                transfer.uploadChunk();
            } else {
                transfer.reportComplete();
            }
        }, function(error) {
            transfer.reportError(error);
        });
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
                            console.log('Got progress info : ' + JSON.stringify(data));
                            if(!data) { // Tracking does not work or upload ended for file
                                console.log('No upload progress info');
                                return;
                            }
                            
                            transfer.tracking.file.uploaded = data.bytes_processed;
                            transfer.reportProgress(transfer.tracking.file, transfer.tracking.file.uploaded >= transfer.tracking.file.size);
                        },
                        function() { // Error, tracking does not work
                            console.log('Upload progress fetching failed', arguments);
                        });
                }, filesender.config.legacy_upload_progress_refresh_period * 1000);
            } else transfer.tracking = null;
        }
        
        if(transfer.tracking) transfer.tracking.file = file;
        
        if(!this.legacy.iframe) {
            this.legacy.uid = 'transfer_' + transfer.id + '_' + (new Date()).getTime();
            this.legacy.iframe = $('<iframe name="' + this.legacy.uid + '"/>').appendTo($('<div id="legacy_uploader" />').appendTo('body'));
            window.legacyUploadResultHandler = function(data) {
                console.log('Upload frame done : ' + JSON.stringify(data));
                if(data.message && data.uid) { // Seems to be an error
                    filesender.ui.error(data);
                    return;
                }
                
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
        
        if(transfer.tracking) transfer.tracking.field.clone().appendTo(this.legacy.form); // MUST be before file element
        $(file.node).clone().attr({name: 'file'}).appendTo(this.legacy.form);
        
        this.legacy.form.submit();
    };
};
