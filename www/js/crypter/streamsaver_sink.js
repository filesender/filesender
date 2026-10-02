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
        init: async function() {
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
        visit: async function(chunkid,decryptedData) {
            var $this = this;
            await $this.init();
            
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
        done: async function() {
            var $this = this;
            window.filesender.log("blobSinkStreamed.done(top)");
            $this.complete = true;
        }
    }
};



