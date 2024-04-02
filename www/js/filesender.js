if(!('filesender' in window)) window.filesender = {};

window.filesender.crypto_encrypted_archive_download = false;

/**
 * Supports (updated at end of script)
 */
window.filesender.supports = {
    localStorage: false,
    workers: false,
    crypto: false,
    workerCrypto: false,
};

window.filesender.supports.localStorage = typeof(localStorage) !== 'undefined';

window.filesender.supports.workers = typeof(Worker) !== 'undefined';

window.filesender.supports.reader = typeof(FileReader) !== 'undefined';

window.filesender.supports.crypto = typeof(crypto) !== 'undefined' && typeof(crypto.subtle) !== 'undefined'

if (window.filesender.supports.workers) {
    var w = new Worker('js/crypter/crypto_test.js');
    w.onmessage = function(event) {
        window.filesender.supports.workerCrypto = event.data;
    }
}

/**
 * This is a simple implementation to ensure something is available
 * before logger.js replaces things with a more in depth
 * implementation.
 */
window.filesender.log = function( msg ) {
    console.log( msg );
}

