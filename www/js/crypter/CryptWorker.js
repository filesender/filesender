/** This file should be run inside a WebWorker. It will encrypt a message in the background.
 *
 */

importScripts('sjcl.js');


var key = null;
var plainChunk = null;
var cryptChunk = null;
var adata = null;


/** Input from my master
 *
 */
self.onmessage = function(event) {
    debug("message received");
    plainChunk = event.data['data'] || null;
    key = event.data['key'] || null;
    adata = event.data['adata'] || null;

    if (typeof(plainChunk) != "string" || (plainChunk.length = 0)) {
        error("empty data received");
        return;
    };

    if (typeof(key) != "string" || (key.length = 0)) {
        error("empty key received");
        return;
    };

    doCrypt();
};


/** Start encryption
 *
 */
function doCrypt() {
    try {
        debug("starting encryption");
        var p={};
        if (adata) {
        	p.adata = adata;
        }
        cryptChunk = sjcl.encrypt(key, plainChunk, p);
        debug("encryption finished");
    } catch(e) {
        error("can't crypt: " + e.toString());
        return;
    };
    done();
};


/** Called when encryption is finished
 *
 */
function done() {
    debug("returning data");

    /**
     * MVH backwards compatiblity: older browsers do not support
     * the blob constructor directly. Newer browser (Chrome) don't like
     * big messages in postMessage unless they're in some smarter data structure
     * like Blob or ArrayBuffer
     */
    if( Blob ){
        debug('transferring data as a real blob');
        postMessage({'status': 'ok', 'data': new Blob([cryptChunk]) });
    }
    else{
        debug('transferring data as a base64 text');
        // attempt plain text postMessage
        // may be removed in future
    postMessage({'status': 'ok', 'data':cryptChunk});
    }


    debug("data was returned");
}


function error(e) {
    postMessage({'status': 'error', 'message':e});
};

function debug(e) {
    postMessage({'status': 'debug', 'message':e});
};