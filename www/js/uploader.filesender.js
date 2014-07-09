/**
 * Handle main logic for (encrypted) file uploads.
 * All functions are wrapped in a scope block to prevent leaking globals.
 * uploadFile is exposed ad the public interface.
 */
var uploadFile = (function($){
    var ChunkedXHR = (function() {
        return function(uri, progressHandler) {
            var boundary = "fileboundary";
            var xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function() {
                progressHandler(xhr);
            };

            xhr.open("POST", uri, true);
            xhr.setRequestHeader("Content-Disposition", " attachment; name='fileToUpload'");
            xhr.setRequestHeader("Content-Type", "application/octet-stream");
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            return xhr;
        };
    })();

    var ChunkedUploader = (function() {
        var sendChunk = function(uploader, blob) {
            var xhr = new ChunkedXHR(uploader.target, function(xhr) {
                if (xhr.readyState !== 4) return;

                if (xhr.status !== 200) {
                    uploader.error.call(uploader, xhr);
                    return;
                }

                if (!uploader.chunkComplete.call(uploader, xhr)) {
                    return;
                }

                uploader.uploadNextChunk();
            })

            xhr.send(blob);
        },
            constructor = function(file, options) {
                var properties = ['chunkSize', 'password', 'fileSize', 'useEncryption',
                        'chunkComplete', 'progress', 'complete', 'error', 'target'
                ];

                for (var i = 0; i < properties.length; i++) {
                    var key = properties[i];
                    this[key] = options[key];
                }

                this.file = file;
                this.fileid = sha1hexstring(file);
                this.slicer = new BlobSlicer(file, this.chunkSize);
                this.crypter = new BlobCrypter( this.chunkSize );
            };

        constructor.prototype = {
            uploadNextChunk: function() {
                if (!this.slicer.next()) {
                    this.complete.call(this);
						return;
                }

                if (this.useEncryption) {
                    var self = this;

                    this.crypter.oncrypt = function() {
                        sendChunk(self, self.crypter.cryptBlob);
                    }
                    
                    var sliceend = this.slicer.completed,
                    	slicebegin = this.slicer.completed-(this.slicer.blobSlice.size-1); 
                    var adata=this.fileid+";"+slicebegin+";"+sliceend;
                    
                    this.crypter.crypt(this.slicer.blobSlice, this.password, adata);
                    return;
                }

                sendChunk(this, this.slicer.blobSlice);
                return;
            }
        };

        return constructor;
    })();

    function uploadFile(password, vid, progressCallback ) {
        var file = document.getElementById("fileToUpload").files[0];
        var txferSize = chunksize;

        if (fdata[n].bytesUploaded > fdata[n].bytesTotal - 1) {
            onUploadComplete(fdata);
            return true;
        }

        var uploader = new ChunkedUploader(file, {
            chunkSize: chunksize,
            target: (uploadURI + "?type=chunk&vid=" + vid),
            password: password,
            fileSize: fdata[n].fileSize,
            useEncryption: $("#fileencryption").is(':checked'),
            chunkComplete: function(xhr) {
                if (xhr.responseText == "ErrorAuth") {
                    // todo add as error handler
                    $("#dialog-autherror").dialog("open");
                    return false;
                }
                
                // callback progress after bytes are uploaded
                this.progress.call(this, this.slicer.completed, this.slicer.size);

                return true;
            },
            progress: progressCallback,
            complete: function() {
                onUploadComplete(fdata);
            },
            error: function(xhr) {
                console.log("There was a problem retrieving the data:\n" + xhr.statusText);
                errorDialog("There was a problem retrieving the data:\n" + xhr.statusText);
            }
        });

        uploader.uploadNextChunk();
        return true;
    }

    function onUploadComplete(fdata) {
        var query = $("#form1").serializeArray(),
            json = {};

        $.ajax({
            type: "POST",
            url: "fs_upload.php?type=uploadcomplete&vid=" + vid,
            success: function(data) {
                var parsedData = parseJSON(data);

                if (parsedData.errors) {
                    $.each(parsedData.errors, function(i, result) {
                        if (result == "err_token") {
                            $("#dialog-tokenerror").dialog("open");
                        } // token missing or error
                        if (result == "err_cannotrenamefile") {
                            window.location.href = "index.php?s=uploaderror";
                            return;
                        } //
                        if (result == "err_emailnotsent") {
                            window.location.href = "index.php?s=emailsenterror";
                            return;
                        } //
                        if (result == "err_filesizeincorrect") {
                            window.location.href = "index.php?s=filesizeincorrect";
                            return;
                        } //
                    })
                } else {
                	var status = (parsedData.status?parsedData.status:null);
                	var enc = (parsedData.encryption?"true":"false");
                    if (status == "complete") {
                        window.location.href = "index.php?s=complete&enc="+enc;
                        return;
                    }
                    if (status == "completev") {
                        window.location.href = "index.php?s=completev&enc="+enc;
                        return;
                    }
                }
            },
            error: function(xhr, err) {
                // error function to display error message e.g.404 page not found
                ajaxerror(xhr.readyState, xhr.status, xhr.responseText);
            }
        });

    }

    return uploadFile;
})(jQuery);

