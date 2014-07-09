

window.BlobBuilder = window.BlobBuilder || window.WebKitBlobBuilder || window.MozBlobBuilder || window.MSBlobBuilder;
window.requestFileSystem = window.requestFileSystem || window.webkitRequestFileSystem;


function fileErrorHandler(e) {
    var msg = '';

    switch (e.code) {
        case FileError.QUOTA_EXCEEDED_ERR:
            msg = 'QUOTA_EXCEEDED_ERR';
            break;
        case FileError.NOT_FOUND_ERR:
            msg = 'NOT_FOUND_ERR';
            break;
        case FileError.SECURITY_ERR:
            msg = 'SECURITY_ERR';
            break;
        case FileError.INVALID_MODIFICATION_ERR:
            msg = 'INVALID_MODIFICATION_ERR';
            break;
        case FileError.INVALID_STATE_ERR:
            msg = 'INVALID_STATE_ERR';
            break;
        default:
            msg = 'Unknown Error';
            break;
    };

    console.log('Error: ' + msg);
};


function FileStorage() {
    this.fileWriter = null;
    this.fileEntry = null;
    this.useFileSystemApi = true;
    this.blobBuilder = null;
};


FileStorage.prototype.start = function(fileName, size) {
    this.fileName = fileName;
    this.size = size;

    if (window.requestFileSystem) {
        this.useFileSystemApi = true;
        this._initFsApi();
	}

    this.useFileSystemApi = false;
        this.ready();
    };

 
FileStorage.prototype._initFsApi = function() {
    var that = this;

    function fsGranted(fs) {
        fs.root.getFile(that.fileName, {create: true, exclusive: false}, function(fileEntry) {
            console.log("file created, creating writer");
            that.fileEntry = fileEntry;
            fileEntry.createWriter(function(fileWriter) {
                console.log("writer created");

                fileWriter.onwriteend = function(trunc) {
                    console.log("file writing finished");
                    that.ready();
                };

                fileWriter.onerror = fileErrorHandler;

                if (fileWriter.length > 0) {
                    console.log("file truncated");
                    fileWriter.truncate(0);
                };

                that.fileWriter = fileWriter;
                that.ready();
            });

        });
    };


    function quotaGranted(quota) {
        window.requestFileSystem(window.TEMPORARY, quota, fsGranted, fileErrorHandler);
    };

    window.webkitStorageInfo.requestQuota(window.TEMPORARY, this.size, quotaGranted, fileErrorHandler);
};




FileStorage.prototype.finalize = function() {
    this.fileEntry.remove(function () {console.log("file removed")}, function () {console.log("can't remove file")});
}


FileStorage.prototype.append = function(data) {

    // file system api: supported by chrome and ie10
    if (this.useFileSystemApi) {
	var blob;

	if( BlobBuilder ){
	    this.blobBuilder = new BlobBuilder();
	    this.blobBuilder.append(data);

	    blob = this.blobBuilder.getBlob();
	}
	else if( Int8Array ){
            var content = new Int8Array(data);
	    blob = new Blob([content]);
	}
	else{
	    blob = new Blob([data]);
	}

        this.fileWriter.write( blob );
	return;
    }

    // simply store stuff in memory, in an array
    // propaby better to use indexedDB here
    if( Int8Array ) {
	this._parts = this._parts || [];

	this._parts.push( new Int8Array(data) );
	//this._partsInInt8Array = this._partsInInt8Array || [];
	//this._partsInInt8Array.push( new Int8Array(data) );
        this.ready();
	return;
    }

    if( BlobBuilder ) {
	this._legacyBlobBuilder = this._legacyBlobBuilder || new BlobBuilder();
	this._legacyBlobBuilder.append( data );
	this.ready();
	return;
    }
};

FileStorage.prototype.getURL = function() {
    // Google chrome support for file api
    if (this.useFileSystemApi) {
        return this.fileEntry.toURL();
    }

    // almost certainly firefox > 19 supports this
    if( this['_parts'] ) {
	return window.URL.createObjectURL(new Blob( this._parts ));
    }

    // workaround for older firefoxes
    // the ternary operator is propably untested
    if( BlobBuilder ){
	console.log('dit dus');
    	var blob = this.blobBuilder.getBlob(),
	    url = ( blob.toURL ) ? blob.getBlob().toURL() : window.URL.createObjectURL(blob);

	return url;
    }

    throw 'No supported API found, are you use you using a compatible browser?';
};

FileStorage.prototype.getBlob = function() {
    // IE10 createObjectURL does not work, return the real blob
    // so handler can use msSaveBlob
    if( this['_parts'] ){
	return new Blob( this._parts );
    }

    throw 'Unsupported method in context: no parts to create a blob from';
}





/** callback called when file is ready for writing
 *
 */
FileStorage.prototype.ready = function() {
    console.log("writer ready");
};
