
/** The uploaded object, used for the complete upload progress
 *
 */
uploader = {
    file: null,
    completed: 0,
    doCrypt: true,
    slicer: null,
    fileid: null,
    key: null
};


/** Called when a file is selected
 *
 */
uploader.selected = function() {
    this.file = document.getElementById('id_file').files[0];

    if(this.file) {
        document.getElementById('fileName').innerHTML = 'Name: ' + this.file.name;
        document.getElementById('fileSize').innerHTML = 'Size: ' + formatSize(this.file.size);
        document.getElementById('fileType').innerHTML = 'Type: ' + (this.file.type || "unkown");
    }
};


/** Set status for user
 *
 */
uploader.setStatus = function(status) {
    document.getElementById('status').innerHTML = 'Status: ' + status;
};


uploader.setProgress = function(number) {
    document.getElementById('progressNumber').innerHTML = "" + number + "%";
};



/*Initialises the upload progress
 *
 */
uploader.start = function() {
    this.selected();

    if(!this.file) {
        alert("no file selected");
        return;
    };

    this.key = document.getElementById('id_key').value;
    if(!this.key) {
        alert("no key");
        return;
    }

    this.setStatus("process started");
    this.setProgress(0);

    // TODO: set doCrypt

    this.crypter = new BlobCrypter();

    var that = this;
    this.crypter.oncrypt = function() {
        that.uploadChunk();
    };

    this.slicer = new BlobSlicer(this.file, chunkSize);

    this._nextChunk();
};


uploader._nextChunk = function() {
    if (this.slicer.next()) {
        this.setStatus("encrypting chunk");
        this.crypter.crypt(this.slicer.blobSlice, this.key);
    } else {
        this.setStatus("upload complete!");
        this.setProgress(100);
        alert("upload complete!");
    }
};


/** Send a chunk to the server
 *
 */
uploader.uploadChunk = function() {
    this.setStatus("Uploading chunk");
    var xhr = new XMLHttpRequest();
    var fd = new FormData();

    var that = this;


    /** Called to update the upload progress indicator
     *
     */
    xhr.addEventListener("progress", function(evt) {
        var total = cryptLen(base64Len(uploader.file.size));
        var progress = cryptLen(base64Len(that.slicer.completed)) + evt.loaded;
        var percentComplete = Math.round((progress * 100 )/ total);
        that.setProgress(percentComplete);
    }, false);


    /** Called when a chunk is uploaded
     *
     */
    xhr.addEventListener("load", function(evt) {
        if (evt.target.status != 200) {
            alert("server gave an error (" + evt.target.status + ")");
            return;
        };

        try {
            var response = JSON.parse(evt.target.responseText);
        }catch(e) {
            alert("can't parse server response, upload failed");
            return;
        };

        if (response['status'] == 'error') {
            alert("server gave an error (" + response['message'] + ")");
            return;
        };

        if(!that.fileid) {
            if (!response['fileid']) {
                alert("didnt receive a fileID after first chunk");
                return;
            };
            that.fileid = response['fileid'];
        };

        that._nextChunk();
    }, false);

    xhr.addEventListener("error", function(evt) {
        alert("There was an error attempting to upload the file.");
    }, false);

    xhr.addEventListener("abort", function(evt) {
        alert("The upload has been canceled by the user or the browser dropped the connection.");
    }, false);

    fd.append("file", this.crypter.cryptBlob);

    // needed for django cross site scripting prevention
    fd.append("csrfmiddlewaretoken", document.getElementsByName('csrfmiddlewaretoken')[0].value);

    if(this.fileid) {
        xhr.open("POST", "/bigfiles/append.json/" + this.fileid + "/");
    } else {
        fd.append("receiver", document.getElementById('id_receiver').value);
        fd.append("message", document.getElementById('id_message').value);
        fd.append("filename_overwrite", this.file.name + ".crypted");
        xhr.open("POST", "/bigfiles/upload.json/");
    }

    xhr.send(fd);
};


