if (typeof window === 'undefined')
    window = {}; // dummy window for use in webworkers
if (!('filesender' in window))
    window.filesender = {};
if (!('ui' in window.filesender)) {
    window.filesender.ui = {};
    window.filesender.ui.log = function(e) {
        console.log(e);
    }
}
window.filesender.log = function( msg ) {
    console.log( msg );
}


window.filesender.crypto_blob_reader = function () {
    return {
        reader: null,
        file: null,
        file_size: null,
        blobSlice: null,
        blob: null,
        chunkSize: window.filesender.config.upload_chunk_size, // 5 MB default
        cryptedChunkSize: window.filesender.config.upload_crypted_chunk_size, // 5mb + checksum + IV
        
        completed: 0,
        numberOfChunks: 0,
        size: null,
        in_progess: false,
        setBlob: function (blob) {
            this.blob = blob;
            this.size = blob.size;
            // making the slice generic between different browser vendors
            this.blob.slice = this.blob.slice || this.blob.mozSlice || this.blob.webkitSlice;
        },
        nextBlobSlice: function () {
            if (this.completed >= this.blob.size) {
                return false;
            }
            
            var start = this.completed;
            var end = Math.min(this.completed + this.chunkSize, this.blob.size);

            if ((start === 0 && end < this.chunkSize) || this.chunkSize === 0) {
                this.blobSlice = this.blob;
            } else {
                this.blobSlice = this.blob.slice(start, end);
            }

            this.completed = Math.min(this.blob.size, this.completed + this.chunkSize);

            this.numberOfChunks++;
            
            return true;
        },
        createReader: function (file, callback) {
            this.setBlob(file);
            this.reader = new FileReader();
            this.reader.onerror = function (evt) {
                switch (evt.target.error.code) {
                    case evt.target.error.NOT_FOUND_ERR:
                        alert('File Not Found!');
                        break;
                    case evt.target.error.NOT_READABLE_ERR:
                        alert('File is not readable');
                        break;
                    case evt.target.error.ABORT_ERR:
                        break; // noop
                    default:
                        alert('An error occurred reading this file.');
                }
            };
            this.reader.onprogress = function (evt) {
                if (evt.lengthComputable) {
                    var percentLoaded = Math.floor((evt.loaded / evt.total) * 100);
                }
            };
            
            this.reader.onabort = function (e) {
	        window.filesender.ui.log(e);
            };
            this.reader.onload = function (e) {
	        window.filesender.ui.log(e);
            };
            
            function abortRead() {
                this.reader.abort();
            }

            callback(this);
            
            return this;
        },

        arrayBuffer: function () {
            var $this = this;
            this.in_progess = true;

            return $this.blobSlice.arrayBuffer();
        },
        
        readArrayBuffer: function (callback) {
            var $this = this;
            this.in_progess = true;
           
            // If we use onloadend, we need to check the readyState.
            this.reader.onloadend = function (evt) {
                if (evt.target.readyState === FileReader.DONE) { // DONE == 2                  
                    $this.in_progess = false;
                    callback(evt.target.result);
                }
            };
            
            this.reader.readAsArrayBuffer($this.blobSlice);
        },
        
        readAllArrayBuffer: function (callback) {
            var $this = this;
            
            this.in_progess = true;

            if (!this.blobSlice) {
                var more = this.nextBlobSlice();
            }
            
            // If we use onloadend, we need to check the readyState.
            this.reader.onloadend = function (evt) {
                if (evt.target.readyState === FileReader.DONE) { // DONE == 2               
                    $this.in_progess = false;
                    // send the data to the callback
                    var more = $this.nextBlobSlice();
                    callback(evt.target.result, !more);
                    // repeat
                    if (more) {
                        $this.readAllArrayBuffer(callback);
                    }
                }
            };
            
            this.reader.readAsArrayBuffer($this.blobSlice);
        },
        sliceForDownload: function (largeBlob) {

            var largeBlobSize = largeBlob.size;
            var completed = 0;
            
            var returnBlobArray = [];
            
            var done = false;
            
            var i = 0;
            while (!done) {
                var start = completed;
                var end = Math.min(completed + this.cryptedChunkSize, largeBlobSize);
                var blobSlice = null;
                if ((start === 0 && end < this.cryptedChunkSize)) {
                    blobSlice = largeBlob;
                } else {
                    blobSlice = largeBlob.slice(start, end);
                }
                returnBlobArray.push(blobSlice);
                
                completed = Math.min(largeBlobSize, completed + this.cryptedChunkSize);
                if (completed === largeBlobSize) {
                    done = true;
                }
            }
            
            var reader = new FileReader();

            return returnBlobArray;
        },
        sliceForDownloadBuffers: function (largeBuffer) {
            if(typeof largeBuffer.slice === 'undefined'){
                largeBuffer.slice = largeBuffer.subarray;
            }
            var buffers = [];
            var number = Math.ceil(largeBuffer.length / (this.cryptedChunkSize));

            for (var x = 0; x < number; x++) {
                var start = (x * (this.cryptedChunkSize));
                var end = Math.min((x + 1) * (this.cryptedChunkSize), largeBuffer.length);
                buffers.push(largeBuffer.slice(start, end));
            }
            return buffers;
        }
    };
};
