
var BlobBuilder = self.BlobBuilder || self.WebKitBlobBuilder || self.MozBlobBuilder;
var chunkSize = 5 * 1024*1024;
//var chunkSize = 1024;


/**
 * calculates base64 length given input length
 */
var base64Len = function(input_length) {
    return Math.round((input_length+1)/3.0) * 4;

    /*
    if (self.MozBlobBuilder) {
        //firefox format: data:application/octet-stream;base64,ALJfWO5zyqcM8ryQXX+Fl+Hny9o=
        return 37 + Math.round((input_length+1)/3.0) * 4;
    }

    if (self.WebKitBlobBuilder) {
        //chrome format: data:base64,ALJfWO5zyqcM8ryQXX+Fl+Hny9o=
        return 12 + Math.round((input_length+1)/3.0) * 4;
    }*/
}

var total_cryptlen = function(original_size, chunksize ) {
	var div = parseInt(original_size/chunksize),
		mod = original_size % chunksize;

	return (cryptLen(base64Len(chunksize)) * div) + cryptLen(base64Len(mod))
}


/**
 * calculates crypted length given input length
 */
var cryptLen = function(input_length) {
    //return 66 + Math.ceil((input_length - 1) * 4/3);
    return 72 + Math.ceil((input_length - 1) * 4/3);
}


/**
 * Helper function to format file size
 */
var formatSize = function(size) {
    if(size > 1024 * 1024 * 1024)
        return (Math.round(size * 100 / (1024 * 1024 * 1024)) / 100).toString() + 'GB';
    else if(size > 1024 * 1024)
        return (Math.round(size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
    else
        return (Math.round(size * 100 / 1024) / 100).toString() + 'KB';
}

/**
 * Helper function to establish a hexstring representation of the
 * sha1 hash of the provided value; sha1 calculation uses sjcl.hash.sha1
 */
var sha1hexstring = function(value) {
    var h = sjcl.hash.sha1.hash(value), hs='';
    for (var i = 0; i < h.length; i++) {
        hs += ((h[i] >> 16) & 0x0000ffff).toString(16)
            + ((h[i])       & 0x0000ffff).toString(16);
    }
    return hs;
};