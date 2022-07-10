if (typeof window === 'undefined')
    window = {}; // dummy window for use in webworkers

if (!('filesender' in window))
    window.filesender = {};

window.filesender.streamsaver_sink = function ( arg_name, arg_expected_size, arg_callbackError ) {
    return {
        filename: arg_name,
        expected_size: arg_expected_size,
        fileStream: null,
        writer: null,
        complete: false,
        // keep a tally of bytes processed to make sure we get everything.
        bytesProcessed: 0,
        callbackError: arg_callbackError,
        name: function() { return "streamsaver"; },
        init: function() {
            var $this = this;
            
            if( !$this.fileStream ) {
                streamSaver.mitm = window.filesender.config.streamsaver_mitm_url;
                $this.fileStream = streamSaver.createWriteStream( $this.filename );
            }
            if( !$this.writer ) {
                $this.writer = $this.fileStream.getWriter();

                window.onunload = () => {
                    window.filesender.log('user navigated away from page');
                    if($this.writer) {
                        $this.writer.abort();
                    }
                }

                window.onbeforeunload = evt => {
                    if (!$this.complete) {
                        evt.returnValue = lang.tr('confirm_leave_download_page');
                    }
                }
            }
        },
        error: function(error) {
        },
        visit: function(chunkid,decryptedData) {
            var $this = this;
            $this.init();
            
            window.filesender.log("blobSinkStreamed chunkid " + chunkid + "  data.len " + decryptedData.length );
            const readableStream = new Response( decryptedData ).body;
            const reader = readableStream.getReader();
            const pump = () => reader.read()
                  .then(res => {
                      if( res.done ) {
                          if( $this.complete ) {
                              
                              window.filesender.log("wrote chunk(complete) expected_size " + $this.expected_size
                                                     + " bytesProcessed " + $this.bytesProcessed
                                                   );

                              if( $this.expected_size != $this.bytesProcessed ) {
                                  $this.callbackError('decrypted data size and expected data size do not match');
                              }
                              
                              $this.writer.close();
                              $this.writer = null;
                          }
                      }
                      else {
                          $this.writer.write(res.value).then(pump);
                          $this.bytesProcessed += res.value.length;
                      }
                  });
            
            pump();
        },
        done: function() {
            var $this = this;
            window.filesender.log("blobSinkStreamed.done(top)");
            $this.complete = true;
        }
    }
};



window.filesender.streamsaver_sink_zip64 = function ( cryptoapp, link, transferid, archiveName, pass, selectedFiles, arg_callbackError ) {
    return {
        complete: false,
        // keep a tally of bytes processed to make sure we get everything.
        bytesProcessed: 0,
        callbackError: arg_callbackError,
        zip:null,
        isFileOpen: false,
        selectedFiles: selectedFiles,
        cryptoapp: cryptoapp,
        link: link,
        transferid: transferid,
        pass: pass,
        activeFileID: null,
        progress: null,
        archiveName: archiveName,
        crypto_encrypted_archive_download_fileidlist: '',
        onOpen:  function( blobSink, fileid ) { },
        onClose: function( blobSink, fileid ) { },
        onComplete: function( blobSink ) {},

        currentFileNumber: 0,
        totalFilesToDownload: 0,
        
        init: function() {
            var $this = this;

            if( !$this.zip ) {
                $this.zip = window.filesender.zip64handler();
                $this.zip.init($this.archiveName);

                $this.totalFilesToDownload = selectedFiles.length;

                $this.cryptoapp.setDownloadFileidlist( $this.selectedFiles );
                $this.crypto_encrypted_archive_download_fileidlist = window.filesender.crypto_encrypted_archive_download_fileidlist;
                window.filesender.crypto_encrypted_archive_download_fileidlist = '';
            }

            
        },
        error: function(error) {
            var $this = this;

            window.filesender.log('zip64 sink error()');
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
                window.filesender.log("blobSinkStreamedzip64 no more files to add to archive... done");
                $this.complete = true;
                $this.zip.closeFile();
                $this.closeZip();
                $this.onComplete( $this );
            }
            else
            {
                var f = $this.selectedFiles.shift();
                window.filesender.log("blobSinkStreamedzip64 adding next file with name " + f.filename );
                $this.openFile(f.filename,f.fileid);

                // last file in selection, tell server we are almost done.
                if( $this.selectedFiles.length == 0 ) {
                    window.filesender.log("aaa blobSinkStreamedzip64 downloadNext(sel==1?) selfiles.len " + $this.selectedFiles.length );
                    window.filesender.crypto_encrypted_archive_download_fileidlist = $this.crypto_encrypted_archive_download_fileidlist;

                    var transfer = new filesender.transfer();
                    if (transfer.canUseTeraReceiver()) {
                        window.filesender.crypto_encrypted_archive_download_fileidlist = '';
                        if( window.filesender.terasender  ) {
                            window.filesender.terasender.crypto_encrypted_archive_download_fileidlist = $this.crypto_encrypted_archive_download_fileidlist;
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
            var $this = this;
            if( $this.isFileOpen ) {
                $this.closeFile();
            }
            $this.zip.complete();
        },
        visit: function(chunkid,decryptedData) {
            var $this = this;
            $this.init();
            
//            window.filesender.log("BBB blobSinkStreamedzip64.visit chunkid " + chunkid + "  data.len " + decryptedData.length );
            $this.zip.visit(decryptedData);
        },
        done: function() {
            var $this = this;
            $this.downloadNext();
        }
    }
};

