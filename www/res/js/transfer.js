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
    this.expires = null;
    this.options = [];
    
    this.time = 0;
    this.file_index = 0;
    this.status = 'new';
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
     * @return mixed int file index or false if it was a duplicate or that there was an error
     */
    this.addFile = function(file, errorhandler) {
        if(!errorhandler) errorhandler = filesender.ui.error;
        
        if(!file)
            return errorhandler({message: 'no_file_given'});
        
        if('parentNode' in file) // HTML file input
            file = file.files;
        
        if('length' in file) { // FileList
            if(!file.length) {
                errorhandler({message: 'no_file_given'});
                return false;
            }
            
            for(var i=0; i<file.length; i++)
                this.addFile(file[i]);
            
            return;
        }
        
        if(!('type' in file)) {
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
            name: blob.name,
            mime_type: blob.type
        };
        
        // Look for dup
        for(var i=0; i<this.files.length; i++) {
            if(this.files[i].name == file.name && this.files[i].size == file.size) {
                errorhandler({message: 'duplicate_file', details: {name: file.name, size: file.size}});
                return false;
            }
        }
        
        if(this.files.length >= filesender.config.max_html5_uploads) {
            errorhandler({message: 'max_html5_uploads_exceeded', details: {max: filesender.config.max_html5_uploads}});
            return false;
        }
        
        if(!/^[^\\\/:;\*\?\"<>|]+(\.[^\\\/:;\*\?\"<>|]+)*$/.test(file.name)) {
            errorhandler({message: 'invalid_file_name', details: {max: filesender.config.max_html5_uploads}});
            return false;
        }
        
        if(!file.size) {
            errorhandler({message: 'empty_file'});
            return false;
        }
        
        if(typeof filesender.config.ban_extension == 'string') {
            var banned = filesender.config.ban_extension.replace(/\s+/g, '');
            banned = new RegExp('^(' + banned.replace(',', '|') + ')$', 'g');
            var extension = file.name.split('.').pop();
            if(extension.match(banned)) {
                errorhandler({message: 'banned_extension', details: {extension: extension, banned: filesender.config.ban_extension}});
                return false;
            }
        }
        
        if(this.size + file.size > filesender.config.max_html5_upload_size) {
            errorhandler({message: 'max_html5_upload_size_exceeded', details: {size: file.size, max: filesender.config.max_html5_upload_size}});
            return false;
        }
        
        this.size += file.size;
        
        this.files.push(file);
        
        return this.files.length - 1;
    };
    
    /**
     * Remove a file from list
     * 
     * @param int file index
     */
    this.removeFile = function(name, size) {
        for(var i=0; i<this.files.length; i++) {
            if(this.files[i].name == name && this.files[i].size == size) {
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
        if(!errorhandler) errorhandler = filesender.ui.error;
        
        if(!email.match(filesender.ui.validators.email)) {
            errorhandler({message: 'invalid_recipient', details: {email: email}});
            return false;
        }
        
        for(var i=0; i<this.recipients.length; i++)
            if(this.recipients[i] == email) {
                errorhandler({message: 'duplicate_recipient', details: {email: email}});
                return false;
            }
        
        if(this.recipients.length >= filesender.config.max_email_recipients) {
            errorhandler({message: 'max_email_recipients_exceeded', details: {max: filesender.config.max_email_recipients}});
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
        for(var i=0; i<this.recipients.length; i++)
            if(this.recipients[i] == email) {
                this.recipients.splice(i, 1);
                return;
            }
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
                if(transfer.onprogress) transfer.onprogress.call(transfer, file, true);
            });
        }else if(this.onprogress) {
            this.onprogress.call(this, file, complete);
        }
    };
    
    /**
     * Report transfer complete
     */
    this.reportComplete = function() {
        this.status = 'done';
        
        var time = (new Date()).getTime() - this.time; // ms
        
        if(filesender.config.log) {
            console.log('Transfer ' + this.id + ' (' + this.size + ' bytes) complete, took ' + (time / 1000) + 's');
        }
        
        var transfer = this;
        filesender.client.transferComplete(this, undefined, function(data) {
            if(transfer.oncomplete) transfer.oncomplete.call(transfer, time);
        });
    };
    
    /**
     * Report transfer error
     */
    this.reportError = function(error) {
        if(filesender.config.log) {
            console.log('Transfer ' + this.id + ' (' + this.size + ' bytes) failed');
        }
        
        if(this.onerror) {
            this.onerror.call(this, error);
        }else{
            filesender.ui.error(error);
        }
    };
    
    /**
     * Start upload
     */
    this.start = function(errorhandler) {
        if(!errorhandler) errorhandler = filesender.ui.error;
        
        this.status = 'running';
        
        // Redo sanity checks
        
        if(this.files.length >= filesender.config.max_html5_uploads) {
            return errorhandler({message: 'max_html5_uploads_exceeded', details: {max: filesender.config.max_html5_uploads}});
        }
        
        if(this.size > filesender.config.max_html5_upload_size) {
            return errorhandler({message: 'max_html5_upload_size_exceeded', details: {size: file.size, max: filesender.config.max_html5_upload_size}});
        }
        
        // Prepare files
        var files_dfn = [];
        for(var i=0; i<this.files.length; i++) files_dfn.push({
            name: this.files[i].name,
            size: this.files[i].size,
            mime_type: this.files[i].mime_type
        });
        
        this.time = (new Date()).getTime();
        
        var transfer = this;
        filesender.client.postTransfer(this.from, files_dfn, this.recipients, this.subject, this.message, this.expires, this.options, function(path, data) {
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
                
                if(!transfer.files[i].id) return errorhandler({message: 'file_not_in_response', details: {file: transfer.files[i]}});
            }
            
            // Start uploading chunks
            if(filesender.config.terasender_enabled && filesender.supports.workers) {
                filesender.terasender.start(transfer);
            }else{
                // Chunk by chunk upload
                transfer.uploadChunk();
            }
        }, function(error) {
            transfer.reportError(error);
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
        
        var transfer = this;
        window.setTimeout(function() { // Small delay to let workers stop
            filesender.client.deleteTransfer(transfer, callback);
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
        if(file.uploaded > file.size) file.uploaded = file.size;
        
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
                transfer.reportComplete();
            }
        }, function(error) {
            transfer.reportError(error);
        });
    };
};
