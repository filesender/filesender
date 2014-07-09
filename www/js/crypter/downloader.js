/**
 * Download and decrypt data from the server, uses range download when available
 * and falls back to single chunk download.
 *
 * USAGE:
 *
 *  var downloader = new Downloader(chunksize, {
 *  	progress: function( percentage ) { progressbar... },
 *  	onComplete: function(fileStorage) { ... },
 *  	onError: function(error) { ... }
 *  });
 *  downloader.start( url, filename, originalFileSize );
 *
 */
var Downloader = (function() {
    var setStatus = function(statusText, callback) {
        if (typeof callback !== 'function') return;
        callback(statusText);
    },
        setProgress = function(progress, callback) {
            if (typeof callback !== 'function') return;
            callback(progress);
        },
        abortOnError = function(options, msg) {
        	console.log("ERROR DOWNLOAD"+(msg?":"+msg:""));
            if (options && (typeof options.onError === 'function')) {
                options.onError('ERROR DOWNLOAD'+(msg?":"+msg:""));
                return;
            }
            alert("There was an error attempting to download the file: "+msg);
            return;
        },
        abortOnIntegrity = function(options,msg) {
        	console.log("ERROR DOWNLOAD INTEGRITY FAIL"+(msg?":"+msg:""));
            if (options && (typeof options.onError === 'function')) {
                options.onError('Integrity check failed, aborting download'+(msg?":"+msg:"."));
                return;
            }
        	alert("There was an integrity error when decrypting the file:"+msg);
        	return;
        }, validateChunk = function(cryptChunk, chunkSize) {
            var final = cryptChunk.charAt(cryptChunk.length - 1);

            if (final == "}") return (cryptChunk.length);
            
            if (final != "}") {
                var pos = cryptChunk.indexOf("}");

                if (pos == "-1") {
                	abortOnError(that.options, "no } in chunk, chunkSize (" + chunkSize + ") too small or corrupted data");
                    return;
                } else {
                	console.log("chunk ends early; adjusting size of this chunk to "+(pos+1));
                    return (pos+1);
                };
            }
        }, decrypt = function(key, cryptChunk, chunkSize, dloptions) {
            var base64Chunk = null,
                rp={};

            var rcs = validateChunk(cryptChunk, chunkSize);

            try {
            	if (rcs!=cryptChunk.length) cryptChunk=cryptChunk.substring(0,rcs);	// adjust when needed
                base64Chunk = sjcl.decrypt(key, cryptChunk, null, rp);
            } catch (e) {
            	console.log("Caught sjcl.decrypt() exception: "+e.message);
            	abortOnError(dloptions, "Wrong key?");
                return null;
            };

            // convert base64 to raw binary data held in a string
            // doesn't handle URLEncoded DataURIs
            var byteString = atob(base64Chunk);

            // write the bytes of the string to an ArrayBuffer
            var plainChunk = new ArrayBuffer(byteString.length);

            var ia = new Uint8Array(plainChunk);

            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            };

            return {
                    plain: plainChunk,
                    adata: rp.adata,
                    rcvsize : rcs		// return chunksize that was actually processed
                };
        };

    /** The downloader object, used for the complete download process
     */
    var Downloader = function(chunkSize, options) {
        // set options for later
        this.options = options;
        this.chunkSize = chunkSize;
        this.xhr = new XMLHttpRequest();
    };

    Downloader.prototype = {
        fileUrl: null,
        useRange: true,
        completed: 0,	// completed bytes received
        completedplain: 0,	// completed bytes plaintext decrypted
        fileid: null,	// if non-null: must match adata[1]
        fileStorage: null,

        /**
         * Start downloading from fileUrl. Size is actual byte size of the eventual
         * file, not the encrypted size.
         */
        start: function(fileUrl, size, key) {
            var that = this;

            setStatus("starting download", this.options.onStatus);

            this.fileUrl = fileUrl;
            this.size = total_cryptlen(size, this.chunkSize);

            this.completed = 0;
            this.completedplain = 0;
            this.fileStorage = new FileStorage();

            this.xhr.addEventListener("load", function(evt) {
                that._requestComplete(evt, key)
            }, false);

            this.xhr.addEventListener("error", function() {
                abortOnError(that.options, "ERROR DOWNLOAD");
            }, false);

            this.xhr.addEventListener("abort", function() {
                if (typeof that.options.onAbort === 'function') {
                    that.options.onAbort('ABORT DOWNLOAD');
                    return;
                }

                alert("The upload has been canceled by the user or the browser dropped the connection.");
            }, false);


            var fnRready = this.options.onComplete || function() {};

            this.fileStorage.ready = function() {
                if (that.completed < that.size) {
                    if (that.useRange) that._nextDownload();
                    return;
                }

                setProgress(100, that.options.progress);
                fnRready(that.fileStorage);
            };

            setProgress(0, this.options.progress);
            
            // initialize chunk-schema from metadata file from server to trigger the download process
            this._initChunkSchema(size);
        },

        _initChunkSchema: function(size) {
        	// the chunkschema is an array of elements [ {s:0, e:size}, {s:size, e:size+size}, ... ]
        	// on success: create chunkschema from metadata
        	// if no chunkschema exists, let chunksize dynamically set chunksizes
        	this.chunkSchema = null;
        	
        	$.ajax({
    			type: "GET",
    			url: this.fileUrl+"&metadata=true",
    			downloader: this,
    			filesize: size,
    			success: function(data) {
    				var parsedData = parseJSON(data);
    				if (!parsedData || parsedData.error)
    					console.log("Error fetching metadata for file:"+(parsedData===undefined?"undefined":parsedData.error)+"; trying to ignore metadata");
    				else
    					this.downloader.chunkSchema = parsedData;
    			}
        	}).always(function() {
        		this.downloader.fileStorage.start(null, this.filesize);
        	});
        },
        
        _encryptedChunkSize: function() {
            return cryptLen(base64Len(this.chunkSize));
        },

        /** called when the file storage is ready for some action
         *	CONSIDER: is this actually called? [MD]
         */
        _writerReady: function() {
            if (this.completed < this.size) {
                if (this.useRange) {
                    this._nextDownload();
                    return;
                }
                else this._processSingleRequest();
                
                return;
            }

            setProgress(100, this.options.progress);
            var fnReady = this.options.onComplete || function() {};

            fnReady(this.fileStorage);
        },

        /** Download the next chunk from the server
         *
         */
        _nextDownload: function() {
            setStatus("downloading chunk", this.options.onStatus);
            var nextchunk = null;
            if (this.chunkSchema) {
            	for (var i=0; i<this.chunkSchema.length; i++) {
            		if (this.chunkSchema[i].s==(this.completed+1)) {
            			nextchunk = this.chunkSchema[i]; break;
            		}
            	}
            }
            if (! nextchunk) {
            	nextchunk={};
            	nextchunk.s=this.completed+1;
            	// allow 512 bytes slack for dynamic header length; will usually be enough 
            	nextchunk.e=Math.min(this.completed + this._encryptedChunkSize() + 512, this.size);
            }
            this.xhr.open("GET", this.fileUrl);
            this.xhr.setRequestHeader("Range", "bytes=" + ((nextchunk.s)-1) + "-" + ((nextchunk.e)-1));
            this.xhr.send();
        },
        /**
         * Adjust this.size from the response from the server  
         */
        _adjustSizeFromResponse: function(range) {
        	if (!range) return;
        	if (range.indexOf("/")<0) return;
        	var reportedsize = range.substring( range.indexOf("/")+1 );
        	var i=parseInt(reportedsize);
        	if (i) { this.size = i; }
        },

        /** called when the chunk is completely downloaded
         *
         */
        _requestComplete: function(evt, key) {
            if (evt.target.status == 200) {
                console.log("server doesn't support range request");

                this.useRange = false;

                // the expected size can not be established client side
                // instead, follow what we were provided with:
                this.size = evt.target.response.length;
                
                this._processSingleRequest(key, evt.target.response);
                return;
            }
            if (evt.target.status == 206) {
                this.useRange = true;
                this._adjustSizeFromResponse(evt.target.getResponseHeader('Content-Range'));

                setStatus("decrypting chunk", this.options.onStatus);
                decrypted = decrypt(key, evt.target.response, this.chunkSize, this.options);
                
                if (decrypted==null) return;	// stop here
                
                if (!this._checkSliceOrder(evt.target.response, decrypted)) {
                	abortOnIntegrity(this.options);
                	console.log("Integrity check failed: sliceOrder failed to verify; report and abort.");
                	return;
                }
                
                plainChunk = decrypted.plain; 

                if (plainChunk === null) {
                    abortOnError(this.options, "No or empty chunk decrypted.");
                    return;
                }

                this.completed = Math.min(this.completed + decrypted.rcvsize, this.size);
                this.completedplain+=plainChunk.byteLength;
                this.fileStorage.append(plainChunk);

                setProgress(Math.round(this.completed / this.size * 100), this.options.progress);
                return;
            }
            abortOnError(this.option, "Server reported an error (" + evt.target.status + ")");
            return;
        },
        _checkSliceOrder: function(r, decrypted) {
        	var adata=decrypted.adata;
        	var plainlength=decrypted.plain.byteLength;
        	var a=adata.split(";");		// a[0]:fileid, a[1]:start, a[2]:end
        	if (this.fileid!=null) {
        		if (a[0] != this.fileid) { console.log("Inconsistent fileid reported, expected:"+this.fileid+", received:"+a[0]); return false; }
        	} else { this.fileid=a[0]}
        	if (a.length<3) { console.log("Invalid adata:'"+adata+"'"); return false; }
        	if (a[1] != (this.completedplain+1)) { console.log("Invalid begin reported:"+a[1]+"; expected:"+this.completedplain); return false; }
        	var end=this.completedplain+plainlength;
        	if (a[2] != end) { console.log("Invalid end reported:"+a[2]+"; expected:"+end); return false;}
        	return true;
        },

        /** Process blob in chunked, used when the HTTP server doesn't support Range
         *
         */
        _processSingleRequest: function(key, crypted) {
            var that = this,
                processSingleChunk = function() {
                    var start = that.completed;
                    var end = Math.min(that.completed + that._encryptedChunkSize()+512, crypted.length);
                    
                    if (that.completed < end) {
	                    var cryptChunk = crypted.slice(start, end);
	
	                    setStatus("decrypting chunk", that.options.onStatus);
	
	                    decrypted = decrypt(key, cryptChunk, that.chunkSize, that.options);
	                    
	                    if (decrypted == null) return;	// stop
	                    
	                    plainchunk = decrypted.plain;
	                    
	                    if (!that._checkSliceOrder(cryptChunk, decrypted)) {
	                    	abortOnIntegrity(that.options, "Integrity check failed: sliceOrder failed to verify.");
	                    	console.log("Integrity check failed: sliceOrder failed to verify.");
	                    	return;
	                    }
	                    
	                    that.completed += decrypted.rcvsize;
	                    that.completedplain += plainchunk.byteLength;

	                    that.fileStorage.append(plainchunk);
                    }

                    setProgress(Math.round(that.completed / that.size * 100), that.options.progress);
                };

            // overwrite the ready callback, if defined.
            // this way, we keep it local, no need to pass
            // around the key if it can be a closure
            this.fileStorage.ready = function() {
            	if (that.completed < that.size) {
                	processSingleChunk(key, crypted);
            	} else {
            		setProgress(100, that.options.progress);
                    var fnReady = that.options.onComplete || function() {};

                    fnReady(that.fileStorage);
            	}
            };

            processSingleChunk(key, crypted);
        },
    };

    return Downloader;
})();
