
/** Read a file and slice it
 * @param {Blob} blob or file to be slice
 * @param {int} chunkSize the size of the chunks in bytes. Set to 0 if you don't want to chunk
 */

function BlobSlicer(blob, chunkSize) {
    this.logger = console.log;
    this.blobSlice = null;
    this.blob = blob;
    this.chunkSize = chunkSize || (5 * 1024 * 1024); // 5 MB default
    this.completed = 0;
    this.size      = blob.size;

    // making the slice generic between different browser vendors
    this.blob.slice = this.blob.mozSlice || this.blob.webkitSlice || this.blob.slice;
};


/** make next blob slice, result will be in BlobSlicer.blobSlice
 *
 */
BlobSlicer.prototype.next = function() {
    if (this.completed >= this.blob.size) {
        return false;
    };

    var start = this.completed;
    var end = Math.min(this.completed + this.chunkSize, this.blob.size);

    if ((start == 0 && end < this.chunkSize) || this.chunkSize == 0) {
        this.blobSlice = this.blob;
    } else {
        this.blobSlice = this.blob.slice(start, end);
    };

    this.completed = Math.min(this.blob.size, this.completed + this.chunkSize);
    return true;
};
