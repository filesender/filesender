// JavaScript Document

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2023, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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


if (typeof window === 'undefined')
    window = {}; // dummy window for use in webworkers

if (!('filesender' in window))
    window.filesender = {};

window.filesender.archive_sink = function ( cryptoapp, link, transferid, archiveName, pass, selectedFiles, arg_callbackError ) {
    return {
        complete:       false,
        // keep a tally of bytes processed to make sure we get everything.
        bytesProcessed: 0,
        callbackError:  arg_callbackError,
        zip:            null,
        isFileOpen:     false,
        selectedFiles:  selectedFiles,
        cryptoapp:      cryptoapp,
        link:           link,
        transferid:     transferid,
        pass:           pass,
        activeFileID:   null,
        progress:       null,
        archiveName:    archiveName,
        crypto_encrypted_archive_download_fileidlist: '',
        onOpen:  function( blobSink, fileid ) { },
        onClose: function( blobSink, fileid ) { },
        onComplete: function( blobSink ) {},

        currentFileNumber:    0,
        totalFilesToDownload: 0,
        
        init: async function() {
            var $this = this;
            console.log("archive_sink init (top)");

            if( !$this.zip ) {
                $this.zip = window.filesender.zip64handler();
                await $this.zip.init($this.archiveName);
                console.log("archive_sink zip init complete ()");

                $this.totalFilesToDownload = selectedFiles.length;                
                $this.cryptoapp.setDownloadFileidlist( $this.selectedFiles );
                $this.crypto_encrypted_archive_download_fileidlist = window.filesender.crypto_encrypted_archive_download_fileidlist;
                window.filesender.crypto_encrypted_archive_download_fileidlist = '';
            }
        },
        error: function(error) {
            var $this = this;

            window.filesender.log('archive sink error()');
            if($this.zip) {
                $this.zip.abort();
            }
        },
        openFile: function(filename,fileid) {
            var $this = this;
            if( $this.isFileOpen ) {
                $this.closeFile();
            }
            $this.zip.openFile(filename);
            $this.isFileOpen = true;
            $this.activeFileID = fileid;
            $this.onOpen( $this, $this.activeFileID );
            $this.currentFileNumber++;
        },
        closeFile: function() {
            var $this = this;
            $this.zip.closeFile();
            $this.isFileOpen = false;
            $this.onClose( $this, $this.activeFileID );
            $this.activeFileID = null;
        },

        downloadNext: function() {
            var $this = this;

            
            if( $this.selectedFiles.length == 0 ) {
                window.filesender.log("archive_sink blobSinkStreamedzip64 no more files to add to archive... done");
                $this.complete = true;
                $this.zip.closeFile();
                window.filesender.log("archive_sink calling close zip...");
                $this.closeZip();
                $this.onComplete( $this );
            }
            else
            {
                var f = $this.selectedFiles.shift();
                window.filesender.log("archive_sink zip adding next file with name " + f.filename );
                $this.openFile(f.filename,f.fileid);

                // last file in selection, tell server we are almost done.
                if( $this.selectedFiles.length == 0 ) {
                    window.filesender.log("archive_sink zip downloadNext(sel==1?) selfiles.len " + $this.selectedFiles.length );
                    window.filesender.crypto_encrypted_archive_download_fileidlist = $this.crypto_encrypted_archive_download_fileidlist;

                    var transfer = new filesender.transfer();
                    if (transfer.canUseTeraReceiver()) {
                        window.filesender.crypto_encrypted_archive_download_fileidlist = '';
                        if( window.filesender.terasender  ) {
                            window.filesender.crypto_encrypted_archive_download_fileidlist = $this.crypto_encrypted_archive_download_fileidlist;
                        }
                        
                    }

                    
                }
                
                $this.cryptoapp.decryptDownloadToBlobSink( $this, pass, $this.transferid,
                                                           $this.link+f.fileid,
                                                           f.mime, f.filename, f.filesize, f.encrypted_filesize,
                                                           f.key_version, f.salt,
                                                           f.password_version, f.password_encoding, f.password_hash_iterations,
                                                           f.client_entropy, f.fileiv, f.fileaead,
                                                           $this.progress);
            }
            
        },
        closeZip: function() {
            console.log("archive_sink closeZip");
            var $this = this;
            if( $this.isFileOpen ) {
                $this.closeFile();
            }
            $this.zip.complete();
        },
        visit: async function(chunkid,decryptedData) {
            var $this = this;
            await $this.init();
            $this.zip.visit(decryptedData);
        },
        done: function() {
            var $this = this;
            $this.downloadNext();
        }
    }
};

