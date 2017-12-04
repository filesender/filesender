if (typeof window === 'undefined')
    window = {}; // dummy window for use in webworkers

if (!('filesender' in window))
    window.filesender = {};
if (!('ui' in window.filesender)) {
    window.filesender.ui = {};
    window.filesender.ui.log = function(e) {
        console.log(e);
    }
}

window.filesender.crypto_app = function () {
    return {
        crypto_is_supported: true,
        crypto_chunk_size: window.filesender.config.upload_chunk_size,
        crypto_iv_len: window.filesender.config.crypto_iv_len,
        crypto_crypt_name: window.filesender.config.crypto_crypt_name,
        crypto_crypt_length: window.filesender.config.crypto_crypt_length,
        crypto_hash_name: window.filesender.config.crypto_hash_name,
        crypto_hash_iterations: window.filesender.config.crypto_hash_iterations,

        // Shameless copy from:
        // https://git.daplie.com/Daplie/unibabel-js/blob/master/index.js
        utf8ToBuffer: function utf8ToBuffer(str) {
            var binstr = this.utf8ToBinaryString(str);
            var buf = this.binaryStringToBuffer(binstr);
            return buf;
        },
        utf8ToBinaryString: function utf8ToBinaryString(str) {
            var escstr = encodeURIComponent(str);
            // replaces any uri escape sequence, such as %0A,
            // with binary escape, such as 0x0A
            var binstr = escstr.replace(/%([0-9A-F]{2})/g, function(match, p1) {
                return String.fromCharCode(parseInt(p1, 16));
            });

            return binstr;
        },
        binaryStringToBuffer: function binaryStringToBuffer(binstr) {
            var buf;

            if ('undefined' !== typeof Uint8Array) {
                buf = new Uint8Array(binstr.length);
            } else {
                buf = [];
            }

            Array.prototype.forEach.call(binstr, function (ch, i) {
                buf[i] = ch.charCodeAt(0);
            });

            return buf;
        },

        generateKey: function generateKey(password, callback) {
            var $this = this;

            // TODO BAD CODE ALL NULL SALT!!!!!!!!!!!!!!
            var saltBuffer = new Uint8Array(0);

            var passphraseKey = this.utf8ToBuffer(password);

            crypto.subtle.importKey(
                'raw', // format
                passphraseKey, // keyData
                { // algo
                    name: 'PBKDF2'
                },
                false, // extractable
                [ // usages
                    'deriveBits', 'deriveKey'
                ]
            ).then(function(key) {
                return crypto.subtle.deriveKey(
                    { // algorithm
                        name: 'PBKDF2',
                        salt: saltBuffer,
                        iterations: $this.crypto_hash_iterations,
                        hash: $this.crypto_hash_name
                    },
                    key, // masterKey

                    // For AES the length required to be 128 or 256 bits (not bytes)
                    { // derivedKeyAlgorithm
                        name: $this.crypto_crypt_name,
                        length: $this.crypto_crypt_length,
                    },

                    // Whether or not the key is extractable (less secure) or not (more secure)
                    // when false, the key can only be passed as a web crypto object, not inspected
                    false, // extractable

                    // this web crypto object will only be allowed for these functions
                    [ // keyUsages
                        'encrypt',
                        'decrypt',
                    ]
                )
            }, window.filesender.ui.log).then(callback, window.filesender.ui.log);
        },
        encryptBlob: function encryptBlob(value, password, callback) {
            // NOTE: This function is used per chunk.
            // FileSender separates file uploads in chunks of a few megabytes each.
            // Each chunk will thus have its own initialization vector,
            // which is included in the output.

            var $this = this;

            // Derive key from password and use it for encryption
            this.generateKey(password, function (key) {
                // Use 32 bytes, or 256 bits, because SHA-256.
                var iv = crypto.getRandomValues(new Uint8Array($this.crypto_iv_len));
                // Do the actual encryption
                // Will call the callback with a bytestring consisting of the IV and the ciphertext
                crypto.subtle.encrypt(
                    { // Algorithm
                        name: $this.crypto_crypt_name,
                        iv: iv
                    },
                    key, // The derived key
                    value // The plaintext to encrypt
                ).then(
                        // encrypt success
                        function (result) {

                            var joinedData = window.filesender.crypto_common().joinIvAndData(iv, new Uint8Array(result));

                            // this is the base64 variant. this will result in a larger string to send
                            var btoaData = btoa(
                                // This string contains all kind of weird characters
                                window.filesender.crypto_common().convertArrayBufferViewtoString(
                                        joinedData
                                    )
                                );

                            callback(btoaData);
                        },
                        // encrypt failed
                        function (e) {
                            // error occured during crypt
                            window.filesender.ui.log(e);
                        }
                );


            });
        },
        decryptBlob: function (value, password, callbackDone, callbackProgress, callbackError) {
            // NOTE: This function is used per chunk.
            // FileSender separates file uploads in chunks of a few megabytes each.
            // Each chunk will thus have its own initialization vector,
            // which is included in the output.

            var $this = this;
            
            var encryptedChunks = value; // array of buffers, each starting with their own IV
            var blobArray = []; // array of buffers containing plaintext data

            // Derive key from password and use it for encryption
            this.generateKey(password, function (key) {
		var wrongPassword = false;
		function decryptLoop(chunk) {
                    // Show progress once per chunk
		    callbackProgress(chunk, encryptedChunks.length);
                    // Explode IV and ciphertext from chunk
                    var value = window.filesender.crypto_common().separateIvFromData(encryptedChunks[chunk]);
                    // Decrypt the chunk
                    crypto.subtle.decrypt(
                        { // Algorithm
                            name: $this.crypto_crypt_name,
                            iv: value.iv
                        },
                        key, // The derived key
                        value.data // The ciphertext
                    ).then(
                        // decrypt success
                        function (result) {
                            var blobArrayBuffer = new Uint8Array(result);
                            blobArray.push(blobArrayBuffer);
                            // Was this the last chunk?
                            if (blobArray.length === encryptedChunks.length) {
                                callbackDone(blobArray);
                            } else if (chunk<encryptedChunks.length) {
                                decryptLoop(chunk+1);
                            }
                        },
                        // decrypt failed
                        function (e) {
                            if (!wrongPassword) {
                                    wrongPassword=true;
                                    callbackError(e);
                            }
                        }
		    );
                };
		decryptLoop(0);
            });
        },
        decryptDownload: function (link, mime, name, progress) {
            var $this = this;
            
            var prompt = filesender.ui.prompt(window.filesender.config.language.file_encryption_enter_password, function (password) {
                var pass = $(this).find('input').val();

                // Decrypt the contents of the file
                var oReq = new XMLHttpRequest();
                oReq.open("GET", link, true);
                oReq.responseType = "arraybuffer";

                //Download progress
                oReq.addEventListener("progress", function(evt){
                        if (evt.lengthComputable) {
                                var percentComplete = Math.round(evt.loaded / evt.total *10000)/100;
                                if (progress) progress.html(window.filesender.config.language.downloading+": "+percentComplete.toFixed(2)+" %");
                        }
                }, false);

                //on file arrived
                oReq.onload = function (oEvent) {
                        if (progress){
                            progress.html(window.filesender.config.language.decrypting+"...");
                        }
                        // hands over to the decrypter
                        var arrayBuffer = new Uint8Array(oReq.response);
                        setTimeout(function(){
                            $this.decryptBlob(
                                window.filesender.crypto_blob_reader().sliceForDownloadBuffers(arrayBuffer),
                                pass,
                                function (decrypted) {
                                    var blob = new Blob(decrypted, {type: mime});
                                    saveAs(blob, name);
                                    if (progress) {
                                        progress.html("");
                                    }
                                },
                                function (i,c) {
                                    var percentComplete = Math.round(i / c *10000)/100;
                                    if (progress) {
                                        progress.html(window.filesender.config.language.decrypting+": "+percentComplete.toFixed(2)+" %");
                                    }
                                },
                                function (error) {
                                    alert(window.filesender.config.language.file_encryption_wrong_password);
                                    if (progress){
                                        progress.html(window.filesender.config.language.file_encryption_wrong_password);
                                    }
                                }
                            );
                        }, 300);
                };
                // create download
                oReq.send();

            }, function(){
                filesender.ui.notify('info', window.filesender.config.language.file_encryption_need_password);
            });

            // Add a field to the prompt
            var input = $('<input type="text" class="wide" />').appendTo(prompt);
            input.focus();
        }
    };
};
