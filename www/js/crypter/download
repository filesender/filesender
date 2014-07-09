

/** The downloader object, used for the complete download process
 *
 */
Downloader = function() {
    this.crypted = null;
    this.base64Chunk = null;
    this.plainChunk = null;
    this.cryptChunk = null;
    this.fileUrl = null;
    this.key  = null;
    this.useRange = true;

    this.byteString = null;
    this.chunkSize = chunkSize;
    this.completed = 0;
    this.fileStorage = null;

    this.xhr = new XMLHttpRequest();

    var that = this;
    this.xhr.addEventListener("progress", this.progress, false);
    this.xhr.addEventListener("load", function(evt) { that._requestComplete(evt)}, false);
    this.xhr.addEventListener("error", this.failed, false);
    this.xhr.addEventListener("abort", this.canceled, false);
};


/** Set status for user
 *
 */
Downloader.prototype.setStatus = function(status) {
    document.getElementById('status').innerHTML = 'Status: ' + status;
};


Downloader.prototype.setProgress = function(number) {
    document.getElementById('progressNumber').innerHTML = "" + number + "%";
};


/** Initialise the download. For now you need to specify the filename separately so we can use that for saving
 *
 */
Downloader.prototype.start = function(fileUrl, filename, size) {
    this.key = prompt("Give key used for file encryption", "password");

    this.setStatus("starting download");
    this.setProgress(0);
    this.fileUrl = fileUrl;
    this.filename = filename.slice(0,-8); // remove .crypted postfix
    this.size = size;
    this.completed = 0;
    this.fileStorage = new FileStorage();

    var that = this;
    this.fileStorage.ready = function() {
        that._writerReady();
    };

    this.fileStorage.start(this.filename, size);
};

/** called when the file storage is ready for some action
 *
 */
Downloader.prototype._writerReady = function() {
    if(this.completed < this.size) {
        if(this.useRange) {
            this._nextDownload();
        } else {
            this._nextChunk();
        };
    } else {
        this._final();
    };
};

/** Download the next chunk from the server
 *
 */
Downloader.prototype._nextDownload = function() {
    this.setStatus("downloading chunk");
    var start = this.completed;
    var end = Math.min(this.completed + cryptLen(base64Len(this.chunkSize)), this.size);
    this.xhr.open("GET", this.fileUrl);
    this.xhr.setRequestHeader("Range", "bytes=" + start + "-" + (end-1));
    this.xhr.send();
};



/** called when the chunk is completely downloaded
 *
 */
Downloader.prototype._requestComplete = function (evt) {
    if (evt.target.status == 200) {
        console.log("server doesn't support range request");
        this.useRange = false;
        if (evt.target.response.length != this.size) {
            alert("unexpected response size, expected " + this.size + ",  received " + evt.target.response.length);
            return;
        };
        this.crypted = evt.target.response;
        this._nextChunk();

    } else if (evt.target.status == 206) {
        this.useRange = true;
        var l = cryptLen(base64Len(this.chunkSize));
        if (evt.target.response.length != l) {
            alert("unexpected response size, expected " + l + ", received " + evt.target.response.length);
            return;
        };
        this.cryptChunk = evt.target.response;
        this._decryptChunk();
        this.completed = Math.min(this.completed + cryptLen(base64Len(this.chunkSize)), this.size);
        this.fileStorage.append(this.plainChunk);
    } else {
        alert("server gave an error (" + evt.target.status + ")");
        return;
    };
};



/** Process blob in chunked, used when the HTTP server doesn't support Range
 *
 */
Downloader.prototype._nextChunk = function() {
    var start = this.completed;
    var end = Math.min(this.completed + cryptLen(base64Len(chunkSize)), this.crypted.length);
    this.cryptChunk = this.crypted.slice(start, end);
    this._decryptChunk();
    this.completed = end;
    this.fileStorage.append(this.plainChunk);
};


/** Decrypt a chunk
 *
 */
Downloader.prototype._decryptChunk = function () {

    this.setStatus("decrypting chunk");
    var percentComplete = Math.round(this.completed / this.size * 100);
    this.setProgress(percentComplete);

    var final = this.cryptChunk.charAt(this.cryptChunk.length-1);
    if (final != "}") {
        var pos = this.cryptChunk.indexOf("}");
        if (pos == "-1") {
            alert("no } in chunk, chunkSize (" + chunkSize + ") too small or corrupted data");
            return;
        } else {
            alert("} found at " + pos + "but chunkSize is " + this.chunkSize);
            return;
        };


    }

    try {
        this.base64Chunk = sjcl.decrypt(this.key, this.cryptChunk);
    } catch(e) {
        alert("wrong key");
        return;
    };

    // convert base64 to raw binary data held in a string
    // doesn't handle URLEncoded DataURIs
    this.byteString = atob(this.base64Chunk);

    // write the bytes of the string to an ArrayBuffer
    this.plainChunk = new ArrayBuffer(this.byteString.length);
    var ia = new Uint8Array(this.plainChunk);
    for (var i = 0; i < this.byteString.length; i++) {
        ia[i] = this.byteString.charCodeAt(i);
    };
};


/**
 *
 */
Downloader.prototype._final = function() {
    this.setStatus("download complete");
    this.setProgress(100);
	
	if ( window.navigator.msSaveBlob) {
		window.navigator.msSaveBlob(this.fileStorage.blobBuilder.getBlob(), this.fileStorage.fileName);
		console.log("file offered to user");
	} else {
		window.location = this.fileStorage.getUrl();
	};

};


/** called to update the progress indicator
 *
 */
Downloader.progress = function (evt) {
    if (evt.lengthComputable) {
        var percentComplete = evt.loaded / evt.total;
    } else {
        pass;
    };
};


Downloader.failed = function(evt) {
    alert("There was an error attempting to download the file.");
};


Downloader.canceled = function(evt) {
    alert("The upload has been canceled by the user or the browser dropped the connection.");
};



var downloader = new Downloader();
