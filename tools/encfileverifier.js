/**
 * This is a encrypted upload decrypter tool for upload verification purposes
 * 
 * needs 2 arguments:
 * 
 * 	1. input file
 *  2. output file
 *  
 *  uses 'password' as key, might be useful to include this in argv someday
 *  
 */

var fs = require('fs'),
	sjcl = require('./sjcl.js'),
	input = process.argv[2],
	output = process.argv[3];


/**
 * calculates base64 length given input length
 */
var base64Len = function(input_length) {
    return Math.round((input_length+1)/3.0) * 4;
}


/**
 * calculates crypted length given input length
 */
var cryptLen = function(input_length) {
    return 72 + Math.ceil((input_length - 1) * 4/3);
}

var chunksize = 2000000,
	enc_chunksize = cryptLen(base64Len(chunksize));

fs.open(input, 'r', function(err,fdin) { // open input file for reading
	fs.open(output, 'w', function(err,fdout) { // open output file for writing
		while(true) {
			// tmp encrypted chunksize buffer to read into
			var tmpbuffer = new Buffer(enc_chunksize), 
			// read into tmpbuffer
				bytesread = fs.readSync(fdin, tmpbuffer, 0, enc_chunksize, null),
			// 'resize' tmpbuffer into buffer of acurate size
				buffer = tmpbuffer.slice(0, bytesread);			

			if(bytesread) {
				console.log("bytes read:", bytesread);
				try {
					var bytesdecrypted = new Buffer(sjcl.decrypt('password', buffer.toString()), 'base64');
				} catch (err) {
					// dump slize to console for debugging
					console.log(err, buffer.toString());
				}
				// write slice to output
				fs.writeSync(fdout, bytesdecrypted, 0, bytesdecrypted.length, null);
			} else {
				// if done reading
				break;
			}
		}
		fs.close(fdout);
	});
});
