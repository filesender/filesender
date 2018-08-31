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
        crypto_chunk_size:   window.filesender.config.upload_chunk_size,
        crypto_iv_len:       window.filesender.config.crypto_iv_len,
        crypto_crypt_name:   window.filesender.config.crypto_crypt_name,
        crypto_hash_name:    window.filesender.config.crypto_hash_name,
        crypto_key_version:  1,
        // constant values for crypto_key_version
        // newest version first, some metadata about the process
        // taken. The year (and maybe month) should give indication
        // that the later years are also the most desired version.
        crypto_key_version_const_2018_importKey_deriveKey: 1,
        crypto_key_version_const_2017_digest_importKey:    0,
        
        generateVector: function () {
            return crypto.getRandomValues(new Uint8Array(16));
        },
        generateKey: function (encryption_details, callback) {
            var iv = this.generateVector();
            var $this = this;
            var password    = encryption_details.password;
            var key_version = encryption_details.key_version;
            var salt        = encryption_details.salt;

            if( key_version == $this.crypto_key_version_const_2018_importKey_deriveKey ) {

                var efunc = function (e) {
                    // error making a hash
                    filesender.ui.log(e);
                };

                var saltBuffer = window.filesender.crypto_common().convertStringToArrayBufferView(salt);
                
                crypto.subtle.importKey(
                    'raw', 
                    window.filesender.crypto_common().convertStringToArrayBufferView(password), 
                    {name: 'PBKDF2'}, 
                    false, 
                    ['deriveBits', 'deriveKey']
                ).then(function(dkey) {

                    crypto.subtle.deriveKey(
                        { "name": 'PBKDF2',
                          "hash": 'SHA-256',
                          "iterations": 150000,
                          "salt":       saltBuffer,
                        },
                        dkey,
                        { "name":   'AES-CBC',
                          "length": 256,
                          iv:       iv
                        },
                        false,                   // key is not extractable
                        [ "encrypt", "decrypt" ] // features desired
                    ).then(function (key) {
                    
                        callback(key, iv);
                    }, efunc );
                }, efunc );
            }

            if( key_version == $this.crypto_key_version_const_2017_digest_importKey ) {
                // yes, the formatting is jumbled, this is on purpose to show it is not changed from previous
                // it will be reformatted in a subsequent PR
            crypto.subtle.digest({name: this.crypto_hash_name}, window.filesender.crypto_common().convertStringToArrayBufferView(password)).then(function (key) {
                crypto.subtle.importKey("raw", key, {name: $this.crypto_crypt_name, iv: iv}, false, ["encrypt", "decrypt"]).then(function (key) {
                    callback(key, iv);
                }, function (e) {
                    // error making a key
                    filesender.ui.log(e);
                });
            }), function (e) {
                // error making a hash
                filesender.ui.log(e);
            };
            }
        },
        encryptBlob: function (value, encryption_details, callback) {
            var $this = this;
            
            this.generateKey(encryption_details, function (key, iv) {
                crypto.subtle.encrypt({name: $this.crypto_crypt_name, iv: iv}, key, value).then(
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
                        function (e) {
                            // error occured during crypt
                            filesender.ui.log(e);
                        }
                );


            });
        },
        decryptBlob: function (value, encryption_details, callbackDone, callbackProgress, callbackError) {
            var $this = this;

            var encryptedData = value; // array buffers array
            var blobArray = [];

            this.generateKey(encryption_details, function (key) {
		var wrongPassword = false;
		var decryptLoop = function(i) {
		    callbackProgress(i,encryptedData.length); //once per chunk
                    var value = window.filesender.crypto_common().separateIvFromData(encryptedData[i]);
                    crypto.subtle.decrypt({name: $this.crypto_crypt_name, iv: value.iv}, key, value.data).then(
                        function (result) {
                            var blobArrayBuffer = new Uint8Array(result);
                            blobArray.push(blobArrayBuffer);
                            // done
                            if (blobArray.length === encryptedData.length) {
                                callbackDone(blobArray);
                            } else {
                                if (i<encryptedData.length){
                                    setTimeout(decryptLoop(i+1),300);
                                }
                            }
                        },
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
        decryptDownload: function (link, mime, name, key_version, salt, progress) {
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
                                { password: pass, key_version: key_version, salt: salt },
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
        },
        /**
         * Get secure random bytes of a given length
         * @param number of octets of random data to get
         * @return Uint8Array containing your random data of random data
         */
        generateSecureRandomBytes: function( len ) {
            var entropybuf = new Uint8Array(len);
            window.crypto.getRandomValues(entropybuf);
            return entropybuf;
        },
        /**
         * This should encode to 'HelloWorld'
         */
//        encodeToAscii85( [0x86, 0x4F, 0xD2, 0x6F, 0xB5, 0x59, 0xF7, 0x5B] );
        /**
         * binary data to ascii 85 converter using the Z85 encoding. 
         * This encodes 4 octets into 5 bytes of presentable text.
         *
         * Note that bindata will be padded with 0 bytes if it was not an even
         * multiple of 4 bytes.
         *
         * https://en.wikipedia.org/wiki/Ascii85
         * 
         * @param bindata Uint8Array containing data binary data to convert. 
         * @return a Z85 encoded string containing bindata 
         * @see encodeToString() for a dispatch function
         */
        encodeToAscii85: function (bindata) {

            var a85encTable = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.-:+=^!/*?&<>()[]{}@%$#";

            // allow for zero padding to cater for
            // data that is not an array length of mulitples of 4
            var datalen = bindata.length;
            var paddinglength = 0;
            if( datalen % 4 ) {
                paddinglength = 4 - ( datalen % 4 );
                datalen += paddinglength;
            }

            // allocate with padding (zeros) and copy
            // bindata over the start of the array
            var data = new Uint8Array(datalen);
            data.set( bindata );
            
            var size = data.length;
            var encodedSize = data.length * 5/4;
            var encoded = "";
            var value = 0;
            var i = 0;

            // transform 4 bytes of data at a time to 5 bytes of output
            for( i=0; i<size; i+= 4 ) {

                value = data[i]*256*256*256 + data[i+1]*256*256 + data[i+2]*256 + data[i+3];
                var divisor = 85 * 85 * 85 * 85;
                while (divisor >= 1) {
                    encoded += a85encTable[ Math.floor(value / divisor) % 85 ];

                    // do not go fractional
                    if( divisor==1 ) {
                        break;
                    }
                    divisor /= 85;
                }
            }

            return encoded;
        },
        /**
         * convert array to base64 encoded string
         * @param bindata Uint8Array containing data binary data to convert. 
         * @return a base64 encoded string containing bindata 
         * @see encodeToString() for a dispatch function
         */
        encodeToBase64: function (bindata) {
            return btoa(String.fromCharCode.apply(null, bindata)); 
        },
        /**
         * encode the bindata using the named encoding or base64 by default.
         * @param bindata Uint8Array containing data binary data to convert. 
         * @param encoding ascii85 or base64 as a string
         */
        encodeToString: function( bindata, encoding ) {
            var $this = this;
            if( encoding == "ascii85" ) {
                return $this.encodeToAscii85( bindata );
            }
            return $this.encodeToBase64( bindata );
        }
    };
};
