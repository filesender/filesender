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
        // random passwords should be 32 octects (256 bits) of entropy.
        crypto_random_password_octets: 32,
        crypto_key_version_constants: {
            // constant values for crypto_key_version
            // newest version first, some metadata about the process
            // taken. The year (and maybe month) should give indication
            // that the later years are also the most desired version.
            v2018_importKey_deriveKey: 1,
            v2017_digest_importKey:    0
        },
        crypto_password_version_constants: {
            // constant values for crypto_password_version_constants
            // newest version last, some metadata about the process
            // taken. The year (and maybe month) should give indication
            // that the later years are also the most desired version.
            //
            //
            // This uses the password text as it is given. This is the right
            // choice for a password that is entered by the user for example.
            // It is assumed that encoding to base64 or whatnot is not needed.
            //
            v2018_text_password: 1,
            //
            //
            // This version is for random generated passwords of 256 bits (32 octets)
            // in length. Encoding from this full octet range is performed to base64
            // and decoding will be done in decodePassword() to the original octet array.
            // This version also allows for the use of less password hashing rounds
            // because it is assumed that the password is already a good length random value.
            // As such, more or less hashing will not impact security.
            //
            v2019_generated_password_that_is_full_256bit: 2
        },

        
        generateVector: function () {
            return crypto.getRandomValues(new Uint8Array(16));
        },
        generateKey: function (encryption_details, callback) {
            var iv = this.generateVector();
            var $this = this;
            var password    = encryption_details.password;
            var key_version = encryption_details.key_version;
            var salt        = encryption_details.salt;
            var password_encoding = encryption_details.password_encoding;
            var password_version  = encryption_details.password_version;

            var decoded        = $this.decodePassword( password, password_version, password_encoding );
            var passwordBuffer = decoded.raw;
            var hashRounds     = window.filesender.config.encryption_password_hash_iterations_new_files;
            if( encryption_details.password_hash_iterations ) {
                hashRounds = encryption_details.password_hash_iterations;
            }
            var saltBuffer     = window.filesender.crypto_common().convertStringToArrayBufferView(salt);
            
            if( key_version == $this.crypto_key_version_constants.v2018_importKey_deriveKey )
            {
                var efunc = function (e) {
                    // error making a hash
                    filesender.ui.log(e);
                };

                crypto.subtle.importKey(
                    'raw', 
                    passwordBuffer,
                    {name: 'PBKDF2'}, 
                    false, 
                    ['deriveBits', 'deriveKey']
                ).then(function(dkey) {

                    crypto.subtle.deriveKey(
                        { "name": 'PBKDF2',
                          "hash": 'SHA-256',
                          "iterations": hashRounds,
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

            if( key_version == $this.crypto_key_version_constants.v2017_digest_importKey )
            {
                crypto.subtle.digest(
                    {name: this.crypto_hash_name},
                    passwordBuffer
                ).then( function (key) {
                    crypto.subtle.importKey("raw", key,
                                            {name: $this.crypto_crypt_name, iv: iv},
                                            false,
                                            ["encrypt", "decrypt"]
                                           ).then( function (key) {
                                               callback(key, iv);
                                           }, function (e) {
                                               // error making a key
                                               filesender.ui.log(e);
                                           });
                }),
                function (e) {
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

            try {
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
            }
            catch(e) {
                callbackError(e);                
            }
        },
        decryptDownload: function (link, mime, name, key_version, salt,
                                   password_version, password_encoding, password_hash_iterations,
                                   progress) {
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
                                { password: pass,
                                  key_version: key_version, salt: salt,
                                  password_version:  password_version,
                                  password_encoding: password_encoding,
                                  password_hash_iterations: password_hash_iterations
                                },
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
         * Genereate a random password that is of a good length
         * for the encryption being used and encode it. 
         * @return an object with the length, password encoding version,
         * and encoded and raw password. 
         *
         * Note that you will need to pass the following back to decodePassword()
         * in order to recalculate the ret.raw values.
         * List of items to store/restore.
         *    ret.value, 
         *    ret.encoding, 
         *    ret.version to 
         * 
         * Example return value.
         * {
         *    version:      1,
         *    encoding:     'base64',
         *    raw:          Buffer <88, 39,...>,
         *    raw_length:   32,
         *    value:        'string encoded version of raw',
         *    value_length: 64
         * }
         */
        generateRandomPassword: function()
        {
            var $this = this;
            var ret = new Object();
            var password = 'error';
            var entropybuf;
            var encoding = filesender.config.encryption_generated_password_encoding;

            var desired_version = filesender.config.encryption_random_password_version_new_files;
            if( $this.crypto_password_version_constants.v2018_text_password == desired_version ) {
                // This is the password generation in place through 
                // the first half of 2019.
                var desiredPassLen = filesender.config.encryption_generated_password_length;
                entropybuf = $this.generateSecureRandomBytes( desiredPassLen );
                password = $this.encodeToString( entropybuf, encoding );
                password = password.substr(0,desiredPassLen);
            }
            else if( $this.crypto_password_version_constants.v2019_generated_password_that_is_full_256bit == desired_version ) {

                // A 32 byte (256 bit) random password
                // encoded using the administrators desired encoding
                encoding = 'base64';
                var entropybuf = $this.generateSecureRandomBytes( $this.crypto_random_password_octets );
                password = $this.encodeToString( entropybuf, encoding );
            }
            else {
                filesender.ui.rawError('{bad password encoding set, you should never see this error}')
            }
            
            ret.version      = desired_version;
            ret.raw          = entropybuf;
            ret.raw_length   = entropybuf.length;
            ret.encoding     = encoding;
            ret.value        = password;
            ret.value_length = ret.value.length;
            
            return ret;
        },

        /**
         * Decode an object that was generated with generateRandomPassword
         * or a raw string as it is presented by using version == 1
         *
         * Example passed input object.
         * {
         *    version:      2,
         *    encoding:     'base64',
         *    value:        'string encoded version of raw',
         * }
         *
         * The output will have raw and raw_length set from input.
         */
        decodePassword: function( value, version, encoding )
        {
            var $this = this;
            var ret = new Object();
            var raw = new Uint8Array(0);
            
            if( $this.crypto_password_version_constants.v2018_text_password == version ) {
                raw = window.filesender.crypto_common().convertStringToArrayBufferView(value);
            }
            else if( $this.crypto_password_version_constants.v2019_generated_password_that_is_full_256bit == version ) {
                if( encoding == 'base64' ) {
                    try {
                        var decoded = atob( value );
                        raw = new Uint8Array( $this.crypto_random_password_octets );
                        raw.forEach((_, i) => {
                            raw[i] = decoded.charCodeAt(i);
                        });
                    } catch(e) {
                        // we know the password is invalid bad if we can not base64 decode it
                        // after all, we base64 encoded it in generateRandomPassword().
                        throw(window.filesender.config.language.file_encryption_wrong_password);
                    }
                }
            }
            else {
                filesender.ui.rawError('{bad password encoding set, you should never see this error}')
            }
            
            ret.version      = version;
            ret.raw          = raw;
            ret.raw_length   = raw.length;
            ret.encoding     = encoding;
            ret.value        = value;
            ret.value_length = ret.value.length;
                
            return ret;
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
