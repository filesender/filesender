/**
 * Part of the Filesender software.
 * See http://filesender.org
 */

if (typeof window === 'undefined')
    window = {}; // dummy window for use in webworkers

if (!('filesender' in window))
    window.filesender = {};
if (!('ui' in window.filesender)) {
    window.filesender.ui = {};
    window.filesender.ui.log = function(e) {
        window.filesender.log(e);
    }
}
window.filesender.log = function( msg ) {
    console.log( msg );
}

Uint8Array.prototype.equals = function (a) {
    return this.length === a.length && this.every(function(value, index) { return value === a[index]});
}

if (!('key_cache' in window.filesender)) {
    window.filesender.key_cache = new Map();
}

if (!('onPBKDF2Starting' in window.filesender)) {
    window.filesender.onPBKDF2Starting = function() {
        window.filesender.log("crypto_app onPBKDF2Starting()");
    };
}
if (!('onPBKDF2Ended' in window.filesender)) {
    window.filesender.onPBKDF2Ended = function() {
        window.filesender.log("crypto_app onPBKDF2Ended()");
    };
}
if (!('onPBKDF2AllEnded' in window.filesender)) {
    window.filesender.onPBKDF2AllEnded = function() {
        window.filesender.log("crypto_app onPBKDF2AllEnded()");
    };
}

window.filesender.crypto_app_downloading = false;

// list of fileid for this encrypted download
window.filesender.crypto_encrypted_archive_download_fileidlist = '';

window.filesender.crypto_last_password_succeeded = false;
window.filesender.crypto_last_password = '';


/*
 * Main entry points
 *   decryptDownload()
 */
window.filesender.crypto_app = function () {
    return {
        crypto_is_supported: true,
        crypto_chunk_size:   window.filesender.config.upload_chunk_size,
        crypto_iv_len:       window.filesender.config.crypto_iv_len,
        crypto_crypt_name:   window.filesender.config.crypto_crypt_name,
        crypto_hash_name:    window.filesender.config.crypto_hash_name,
        upload_crypted_chunk_size: window.filesender.config.upload_crypted_chunk_size,
        // random passwords should be 32 octects (256 bits) of entropy.
        crypto_client_entropy_octets: 32,
        crypto_random_password_octets: 32,
        crypto_gcm_per_file_iv_octet_size: 12, // used in v2019_gcm_* key_versions
        crypto_cbc_per_file_iv_octet_size: 16, // 128bits for CBC
        crypto_key_version_constants: {
            // constant values for crypto_key_version
            // newest version first, some metadata about the process
            // taken. The year (and maybe month) should give indication
            // that the later years are also the most desired version.
            v2019_gcm_importKey_deriveKey: 3, // AES-GCM otherwise same as v2018_importKey_deriveKey
            v2019_gcm_digest_importKey:    2, // AES-GCM otherwise same as v2017_digest_importKey
            v2018_importKey_deriveKey:     1, // AES-CBC
            v2017_digest_importKey:        0  // AES-CBC
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
        // Allow client of this class to force the use of streamsaver off
        // for example, to respect a checkbox from the UI
        disable_streamsaver: false,


        useStreamSaver: function() {
            // Should we use streamsaver for this download?
            window.filesender.config.use_streamsaver = window.filesender.config.allow_streamsaver;
            if( this.disable_streamsaver ) {
                window.filesender.config.use_streamsaver = false;
            }
            return window.filesender.config.use_streamsaver;
        },

        /**
         * This turns a filesender chunkid into a 4 byte array
         * that can be used in GCM encryption. 
         */
        createChunkIDArray: function( chunkid ) {
            var ret = new Uint8Array(4);

            // convert the encoded chunkid into 4 array octets.
            ret[0] = chunkid>>0  & 0xFF;
            ret[1] = chunkid>>8  & 0xFF;
            ret[2] = chunkid>>16 & 0xFF;
            ret[3] = chunkid>>24 & 0xFF;
            return ret;
        },

        extractChunkIDFromIV: function( iv ) {

            if( iv.length != 16 ) {
                return -1;
            }

            // convert 4 array octets back into an encoded chunkid
            var id = 0;
            id |= (iv[12] <<  0);
            id |= (iv[13] <<  8);
            id |= (iv[14] << 16);
            id |= (iv[15] << 24);

            return id;
        },
        
        /**
         * Create and return an IV of 16 octets (128 bits) constructed as follows:
         *    12 octets of entropy
         *     4 octets containing the chunkid
         *
         * This is based on Page 19 of OpenFortress 2018 document:
         *   "The suggested procedure for the case of FileSender is to combine 96 bits
         *    of random material with a 32-bit chunk counter to form a 128-bit IV."
         */
        createIVGCM: function( chunkid, encryption_details ) {
            var $this = this;

            if( !encryption_details.fileiv ||
                encryption_details.fileiv.length != $this.crypto_gcm_per_file_iv_octet_size ) {
                throw ({message: 'gcm_encryption_found_invalid_iv_length',
                        details: {}});
            }
            // 96 bits of entropy
            var ivrandom = encryption_details.fileiv;

            // 32 bits of counter from chunkid
            var ivcounter = $this.createChunkIDArray(chunkid);

            // merge these into return value
            var iv = new Uint8Array(16);
            iv.set(ivrandom);
            iv.set(ivcounter, ivrandom.length );

            return iv;
        },

        
        // generate numOctets 8bit bytes of of entropy, encoded as base64 for storage/transmission
        generateBase64EncodedEntropy: function( numOctets ) {
            var $this = this;
            var entropy = crypto.getRandomValues(new Uint8Array(numOctets));
            var encoding = 'base64';
            var ret = $this.encodeToString( entropy, encoding );
            return ret;
        },
        // decode the base64 encoded entropy string into an array for local use
        decodeBase64EncodedEntropy: function( b64data, numOctets ) {
            var $this = this;
            var decoded = atob( b64data );
            var raw = new Uint8Array( numOctets );
            var i = 0;
            for( ; i < raw.length; i++ ) {
                raw[i] = decoded.charCodeAt(i);
            }
            return raw;
        },
        generateClientEntropy: function() {
            var $this = this;
            return $this.generateBase64EncodedEntropy($this.crypto_client_entropy_octets);
        },
        decodeClientEntropy: function( b64data ) {
            var $this = this;
            return $this.decodeBase64EncodedEntropy(b64data,$this.crypto_client_entropy_octets);
        },
        getNumberOctetsForIV: function( key_version ) {
            var $this = this;
            var numOctets = $this.crypto_gcm_per_file_iv_octet_size;
            if( key_version == $this.crypto_key_version_constants.v2018_importKey_deriveKey ||
                key_version == $this.crypto_key_version_constants.v2017_digest_importKey )
            {
                numOctets = $this.crypto_cbc_per_file_iv_octet_size;
            }
            return numOctets;
        },        
        generateCryptoFileIV: function() {
            var $this = this;
            var numOctets = $this.getNumberOctetsForIV(
                window.filesender.config.encryption_key_version_new_files);
            return $this.generateBase64EncodedEntropy(numOctets);
        },
        decodeCryptoFileIV: function( b64data, key_version ) {
            var $this = this;
            var numOctets = $this.getNumberOctetsForIV(key_version);
            return $this.decodeBase64EncodedEntropy(b64data,numOctets);
        },
        /**
         * Note that if you are using an IV for your encrytoion you
         * should set file.iv = generateCryptoFileIV() before calling this.
         */
        generateAEAD: function( file ) {
            var $this = this;

            var key_version = window.filesender.config.encryption_key_version_new_files;
            
            // AES-GCM modes can make use of AEAD
            if( key_version == $this.crypto_key_version_constants.v2019_gcm_digest_importKey ||
                key_version == $this.crypto_key_version_constants.v2019_gcm_importKey_deriveKey )
            {
                return {
                    chunkcount: Math.ceil( file.size / window.filesender.config.upload_chunk_size ),
                    chunksize: window.filesender.config.upload_chunk_size,
                    iv: file.iv
                };
            }
            return null;
        },
        /**
         * This uses direct printing to produce a canonical string representation
         * without reordering members or whitespace.
         *
         * The aeadversion should be the first member and is there to allow
         * future expansion so that the client can get this member and know
         * what fields should exist for that version of file.
         * 
         * The aeadterminator serves no semantic purpose, it is there to ensure that
         * the data structure does not end in a comma before the closing bracket
         * as new fields are added in the future.
         */
        encodeAEAD: function( aead ) {
            if( !aead ) {
                return '';
            }
            
            var $this = this;

            var ret = "{";
            ret += '"aeadversion":1,';
            ret += '"chunkcount":'  + aead.chunkcount  +',';
            ret += '"chunksize":'   + aead.chunksize   +',';
            ret += '"iv":'          + '"' + aead.iv + '"' + ',';
            ret += '"aeadterminator":1';   // no comma on last item
            ret += '}';
            return ret;
            
        },
        
        
        generateVector: function () {
            return crypto.getRandomValues(new Uint8Array(16));
        },
        generateIV: function( chunkid, encryption_details )
        {
            var $this = this;
            var key_version = encryption_details.key_version;
            
            var iv = this.generateVector();
            if( key_version == $this.crypto_key_version_constants.v2019_gcm_importKey_deriveKey )
            {
                // IV has a predefined mix of entropy and chunk counter
                iv = $this.createIVGCM( chunkid, encryption_details );
            }
            return iv;
        },
        generateKey: function (chunkid, encryption_details, callback, callbackError) {
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
            var efunc = function (e) {
                // error making a hash
                callbackError(e);
            };

            $this.setCipherAlgorithm( key_version );
            
            if( key_version == $this.crypto_key_version_constants.v2018_importKey_deriveKey )
            {
                window.filesender.onPBKDF2Starting();

                //
                // The is set in filesender-config.js.php based on the browser
                //
                if( window.filesender.config.crypto_use_custom_password_code ) 
                {
                    setTimeout(
                        function(){
                    
                            window.filesender.log("***** USING CUSTOM CODE ON PASSWORD ****");
                            
                            window.filesender.asmcrypto().importKeyFromPasswordUsingPBKDF2(
                                passwordBuffer,
                                saltBuffer,
                                hashRounds,                                
                                function(key) {
                                    window.filesender.onPBKDF2Ended();
                                    callback(key);
                                },
                                function(e) {
                                    window.filesender.onPBKDF2Ended();
                                    efunc(e);
                                }
                            );
                        },
                        window.filesender.config.crypto_pbkdf2_dialog_custom_webasm_delay
                    );
                    
                    return;
                }
 

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
                          "length": 256
                        },
                        false,                   // key is not extractable
                        [ "encrypt", "decrypt" ] // features desired
                    ).then(function (key) {
                        window.filesender.onPBKDF2Ended();
                    
                        callback(key);
                    }, efunc );
                }, efunc );
            }

            if( key_version == $this.crypto_key_version_constants.v2019_gcm_importKey_deriveKey )
            {
                window.filesender.onPBKDF2Starting();
                
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
                        { "name":   'AES-GCM',
                          "length": 256
                        },
                        false,                   // key is not extractable
                        [ "encrypt", "decrypt" ] // features desired
                    ).then(function (key) {

                        window.filesender.onPBKDF2Ended();
                        
                        callback(key);
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
                                            {name: $this.crypto_crypt_name},
                                            false,
                                            ["encrypt", "decrypt"]
                                           ).then( function (key) {
                                               callback(key);
                                           }, function (e) {
                                               // error making a key
                                               window.filesender.ui.log(e);
                                           });
                }),
                function (e) {
                    // error making a hash
                    window.filesender.ui.log(e);
                };
            }


            if( key_version == $this.crypto_key_version_constants.v2019_gcm_digest_importKey )
            {
                crypto.subtle.digest(
                    {name: this.crypto_hash_name},
                    passwordBuffer
                ).then( function (key) {
                    crypto.subtle.importKey("raw", key,
                                            { "name":   'AES-GCM', "length": 256 },
                                            false,
                                            ["encrypt", "decrypt"]
                                           ).then( function (key) {
                                               callback(key);
                                           }, function (e) {
                                               // error making a key
                                               window.filesender.ui.log(e);
                                           });
                }),
                function (e) {
                    // error making a hash
                    window.filesender.ui.log(e);
                };
            }
            
            
        },

        setCipherAlgorithm: function(key_version) {
            var $this = this;

            //
            // Force the GCM/CBC crypt_name at the top to make this change explicit before
            // the code that does the key generation is executed. Doing this here makes the
            // crypto code less cuttered below.
            //
            if( key_version == $this.crypto_key_version_constants.v2019_gcm_importKey_deriveKey 
                || key_version == $this.crypto_key_version_constants.v2019_gcm_digest_importKey )
            {
                window.filesender.config.crypto_crypt_name = "AES-GCM";
                this.crypto_crypt_name = window.filesender.config.crypto_crypt_name;
            }
            else
            {
                window.filesender.config.crypto_crypt_name = "AES-CBC";
                this.crypto_crypt_name = window.filesender.config.crypto_crypt_name;
            }
        },

        /**
         * This puts a cache between the call to generateKey() only
         * doing the work if the key has not already been generated.
         */
        obtainKey: function (chunkid, encryption_details, callback, callbackError) {
            var $this = this;

            var key_version = encryption_details.key_version;
            $this.setCipherAlgorithm( key_version );
            

            var keydesc = JSON.stringify(encryption_details);
            window.filesender.log("keygen: keydesc cache size " + window.filesender.key_cache.size );
            
            var k = window.filesender.key_cache.get( keydesc );
            if( k ) {
                window.filesender.log("keygen: reusing existing key");
                callback( k );
                return;
            }

            // there was no key, really generate one and set it in the
            // cache before calling the passed 'ok' callback.
            window.filesender.log("keygen: generating key for this thread/worker");
            this.generateKey(chunkid, encryption_details,
                             function (key) {
                                 window.filesender.key_cache.set(keydesc, key );
                                 callback( key );
                             },
                             function (e) {
                                 callbackError(e);
                             });
                                     
        },

        
        encryptBlob: function (value, chunkid, encryption_details, callback, callbackError ) {
            var $this = this;
            var key_version = encryption_details.key_version;

            
            // GCM checks
            if( key_version == $this.crypto_key_version_constants.v2019_gcm_digest_importKey ||
                key_version == $this.crypto_key_version_constants.v2019_gcm_importKey_deriveKey )
            {
                // If the user tries to upload too many bytes
                // than we should for this encryption technique
                // then do not allow it to happen.
                // Other checks should stop the code before this code can
                // run, but more checks are always better
                if( value.byteLength + chunkid*$this.crypto_chunk_size
                    > window.filesender.config.crypto_gcm_max_file_size )
                {
                    return callbackError({message: 'maximum_encrypted_file_size_exceeded',
                                          details: {}});
                }

                //
                // Chunks have an effective max size too
                //
                if( value.byteLength > window.filesender.config.crypto_gcm_max_chunk_size ) {
                    return callbackError({message: 'maximum_encrypted_file_size_exceeded',
                                          details: {}});
                }

                //
                // Not too many chunks.
                //
                if( chunkid > window.filesender.config.crypto_gcm_max_chunk_count ) {
                    return callbackError({message: 'maximum_encrypted_file_size_exceeded',
                                          details: {}});
                }
            }

            
            this.obtainKey(chunkid, encryption_details, function (key) {

                var iv = $this.generateIV( chunkid, encryption_details );

                /*
                 * The algorithm parameters include the algorithm name to use
                 * and common information like the IV to use.
                 * https://www.w3.org/TR/WebCryptoAPI/#algorithm-concepts-naming
                 *
                 * Some algorithms offer other parameters so this is broken out 
                 * into a variable here to allow for AEAD to be used when 
                 * available for example.
                 *
                 */
                var encryptParams = {
                    name: $this.crypto_crypt_name,
                    iv: iv
                };
                
                /*
                 * AES-GCM offers AEAD which we will use
                 */
                if( key_version == $this.crypto_key_version_constants.v2019_gcm_digest_importKey ||
                    key_version == $this.crypto_key_version_constants.v2019_gcm_importKey_deriveKey )
                {
                    encryptParams.additionalData = window.filesender.crypto_common().convertStringToArrayBufferView(
                        encryption_details.fileaead);
                }
                
                crypto.subtle.encrypt(encryptParams, key, value).then(
                    function (result) {
                        
                            var joinedData = window.filesender.crypto_common().joinIvAndData(iv, new Uint8Array(result));

                            var btoaData = joinedData;
                            if( window.filesender.config.encryption_encode_encrypted_chunks_in_base64_during_upload ) {
                                // this is the base64 variant. this will result in a larger string to send
                                btoaData = btoa(
                                    // This string contains all kind of weird characters
                                    window.filesender.crypto_common().convertArrayBufferViewtoString(
                                        joinedData
                                    )
                                );
                            }
                            callback(btoaData);
                        },
                    function (e) {
                            // error occured during crypt
                            window.filesender.ui.log(e);
                        }
                );
            },
            function (e) {
                // error occured during obtainkey
                window.filesender.ui.log(e);
            });
        },
        //
        // These are checks to be performed on every crypted chunk
        //
        // i is the chunkid
        // value is from separateIvFromData()
        // decryptParams can be modified for example to add AEAD to the object
        //
        decryptBlobSpecificCryptoChunkChecks: function( i, value, encryption_details, decryptParams, callbackError )
        {
            var $this = this;
            var key_version = encryption_details.key_version;

            window.filesender.log("decryptBlobSpecificCryptoChunkChecks(enter)");

            // GCM checks
            if( key_version == $this.crypto_key_version_constants.v2019_gcm_digest_importKey ||
                key_version == $this.crypto_key_version_constants.v2019_gcm_importKey_deriveKey )
            {

                // Check IV random 96 bits are the same
                if( !encryption_details.expected_fixed_chunk_iv.equals(
                    value.iv.slice(0,$this.crypto_gcm_per_file_iv_octet_size))  )
                {
                    window.filesender.log("decryptBlobSpecificCryptoChunkChecks() invalid iv");
                    return callbackError({message: 'decryption_verification_failed_invalid_iv',
                                          details: {}});
                }
                
                // Check that chunkid from IV matches expected chunkid
                var ivchunkid = $this.extractChunkIDFromIV( value.iv );
                if( ivchunkid == -1 ) {
                    window.filesender.log("decryptBlobSpecificCryptoChunkChecks() bad iv chunkid");
                    return callbackError({message: 'decryption_verification_failed_bad_ivchunkid',
                                          details: {}});
                }
                if( i != ivchunkid ) {
                    window.filesender.log("decryptBlobSpecificCryptoChunkChecks() unexpected iv chunkid");
                    return callbackError({message: 'decryption_verification_failed_unexpected_ivchunkid',
                                          details: {}});
                }
            }

            if( key_version == $this.crypto_key_version_constants.v2019_gcm_digest_importKey ||
                key_version == $this.crypto_key_version_constants.v2019_gcm_importKey_deriveKey )
            {
                decryptParams.additionalData = window.filesender.crypto_common().convertStringToArrayBufferView(
                    encryption_details.fileaead);
            }
            
            window.filesender.log("decryptBlobSpecificCryptoChunkChecks(exit)");
        },

        /*
         * These are final validation checks for a crypted file download. 
         */
        decryptBlobSpecificFinalChunkChecks: function( i, value, encryption_details, callbackError )
        {
            var $this = this;
            var key_version = encryption_details.key_version;

            // AES-GCM: a final check to see that we are stopping at the correct chunk
            // number and the server has not sent fewer chunks than we expect
            // We have it all, and only it all. No less, No more.
            if( key_version == $this.crypto_key_version_constants.v2019_gcm_digest_importKey ||
                key_version == $this.crypto_key_version_constants.v2019_gcm_importKey_deriveKey )
            {
                // first chunk is i=0, but there is 1 chunk at that point.
                var expectedchunkcount = i+1;
                if( encryption_details.aead.chunkcount != expectedchunkcount ) {
                    window.filesender.log("decryptBlobSpecificFinalChunkChecks() bad aead 1");
                    return callbackError({message: 'decryption_verification_failed_bad_aead',
                                          details: {}});
                }
            }
            
        },
        decryptBlob: function (chunkid,encryptedChunk, encryption_details, key, blobSink, callbackNext, callbackDone, callbackError) {
            var $this = this;
            var key_version = encryption_details.key_version;
            var blobArray = [];
	    var wrongPassword = false;
            var nonStreamed = false;

            if( chunkid==0 ) {
                if( !window.filesender.config.use_streamsaver ) {
                    window.filesender.log("decryptBlob() first chunk of non streamed download");
                    nonStreamed = true;
                }
            }
            
            try {
                    
                var value = encryptedChunk;
                var decryptParams = {
                    // See the comment for encryptParams above for info.
                    name: $this.crypto_crypt_name,
                    iv: value.iv
                };

                $this.decryptBlobSpecificCryptoChunkChecks( chunkid, value, encryption_details,
                                                            decryptParams,
                                                            callbackError );

                window.filesender.log("decryptBlob() about to really decrypt() nonStreamed " + nonStreamed );

                var decryptAndContinue = async function() {
                    var result = await crypto.subtle.decrypt(decryptParams, key, value.data);
                    var blobArrayBuffer = new Uint8Array(result);
                    window.filesender.log("decryptBlob() about to visit the data" );
                    await blobSink.visit(chunkid,blobArrayBuffer);
                    window.filesender.log("decryptBlob() visited the data" );
                    // done
                    if( chunkid == encryption_details.chunkcount ) {
                                        
                        window.filesender.log("decryptBlob() done" );
                        $this.decryptBlobSpecificFinalChunkChecks( chunkid,
                                                                   encryption_details,
                                                                   callbackError );
                        callbackDone(blobSink);
                    }
                    else
                    {
                        callbackNext();
                    }
                };

                // dispatch into an async function for await -> catch()
                decryptAndContinue()
                    .then( function() {} )
                    .catch( e => {
                            window.filesender.log("decrypt(e) nonStreamed " + nonStreamed );
                            window.filesender.log(e);
                            if(nonStreamed) {
                                e = new Error();
                            }
                            if (!wrongPassword) {
                                wrongPassword=true;
                                window.filesender.log("decrypt(e5) nonStreamed " + nonStreamed );
                                window.filesender.log("decrypt(e5) msg " + e.message );
                                filesender.client.decryptionFailedForTransfer( encryption_details.transferid );
                                callbackError(e);
                            }
                    });
            }
            catch(e) {
                callbackError(e);                
            }            
        },
        /*
         * This method mainly focuses on creating an XMLHttpRequest to download the
         * byte range for the desired chunk. There are some adjustments for padding 
         * that have to be done which are handled by this method.
         *
         * Once a chunk has been downloaded decryptBlob() is called to decrypt and process it.
         * In order to keep the download progressing, the callbackNext for decryptBlob is
         * set to call ourself (downloadAndDecryptChunk) with the next chunkid.
         * 
         * So offset ranges and network are handled in this method, decryption is handled in
         * decryptBlob() which this method calls.
         */
        downloadAndDecryptChunk: function (chunkid, link, progress,
                                           encryption_details, key,
                                           blobSink,
                                           callbackDone, callbackProgress, callbackError)
        {
            var $this = this;
            window.filesender.log("downloadAndDecryptChunk(top) chunkid " + chunkid + " of " + encryption_details.chunkcount );
            
            // Decrypt the contents of the file
            var oReq = new XMLHttpRequest();
            oReq.open("GET", link, true);
            oReq.responseType = "arraybuffer";
            var chunksz     = 1 * $this.crypto_chunk_size;
            var startoffset = 1 * (chunkid * chunksz);
            var endoffset   = 1 * (chunkid * chunksz + (1*$this.upload_crypted_chunk_size)-1);
            var legacyChunkPadding = 0;
            oReq.setRequestHeader('X-FileSender-Encrypted-Archive-Download', filesender.crypto_encrypted_archive_download );

            
            //
            // There are some extra things to do for streaming legacy type files
            //
            if( encryption_details.key_version == $this.crypto_key_version_constants.v2018_importKey_deriveKey ||
                encryption_details.key_version == $this.crypto_key_version_constants.v2017_digest_importKey )
            {
                legacyChunkPadding = 1;
            }

            //
            // Handle last chunk details, some offsets might need to change slightly
            //
            if( chunkid == encryption_details.chunkcount ) {
                var padding = (1*$this.upload_crypted_chunk_size) - (1* $this.crypto_chunk_size);
                var blockPad = 32;

                window.filesender.log("downloadAndDecryptChunk(last chunk offset adjustment) "
                                      + " legacyPadding " + legacyChunkPadding
                                      + " ccs "  + $this.crypto_chunk_size
                                      + " uccs " + $this.upload_crypted_chunk_size
                                      + " soffset " + startoffset
                                      + " soffsetcc " + (1 * (chunkid * $this.upload_crypted_chunk_size))
                                     );
                window.filesender.log("downloadAndDecryptChunk(last chunk offset adjustment) "
                                      + " eoffset " + endoffset
                                      + " fs " + encryption_details.filesize
                                      + " efs " + encryption_details.encrypted_filesize
                                     );
                
                endoffset = (1*encryption_details.filesize) + blockPad - 1;
                if( encryption_details.key_version < 2 ) {
                    endoffset -= 4;
                }
                if( !chunkid ) {
                    endoffset = encryption_details.encrypted_filesize - 1;
                }
                if( chunkid > 0 && legacyChunkPadding ) {

                    var fs = (1*encryption_details.filesize);
                    fs = fs % chunksz;
                    if( fs == 0 ) {
                        fs = chunksz;
                    }
                    
                    endoffset = 1 * (chunkid * chunksz + fs + blockPad - (fs%16)) -1;
                    window.filesender.log("downloadAndDecryptChunk(legacyPadding) new eoffset " + endoffset );
                    
                }

                window.filesender.log("downloadAndDecryptChunk(adjustments done) "
                                      + " eoffset " + endoffset
                                      + " padding " + padding );

                oReq.setRequestHeader('X-FileSender-Encrypted-Archive-Contents', window.filesender.crypto_encrypted_archive_download_fileidlist );
                window.filesender.crypto_encrypted_archive_download_fileidlist = '';
                
            }
            
            var brange = 'bytes=' + startoffset + '-' + endoffset;
            oReq.setRequestHeader('Range', brange);

            //Download progress
            oReq.addEventListener("progress", function(evt){
                window.filesender.log("downloadAndDecryptChunk(progress) chunkid " + chunkid
                                      + " loaded " + evt.loaded + " of total " + evt.total );
                if (evt.lengthComputable) {
                    var percentComplete = Math.round(evt.loaded / (1*$this.upload_crypted_chunk_size) *10000) / 100;
                    var percentOfFileComplete = 100*((chunkid*$this.crypto_chunk_size + evt.loaded) / encryption_details.filesize );
                    
                    if (progress) {

                        var msg = lang.tr('download_chunk_progress').r({chunkid: chunkid,
                                                                        chunkcount: encryption_details.chunkcount,
                                                                        percentofchunkcomplete: percentComplete.toFixed(2),
                                                                        percentOffilecomplete: percentOfFileComplete.toFixed(2)
                                                                       }).out();
                        progress.html(msg);
                    }
                }
            }, false);

            var transferError = function (error) {
                window.filesender.log(error);
                window.filesender.ui.error(error);
                if (progress){
                    progress.html("");
                }
            };

            //
            // When bad things happen
            //
            oReq.addEventListener("error", function(evt) {
                window.filesender.log("oReq error: " + evt.toString());
                transferError(lang.tr('download_error').out());
                return;
            });
            oReq.addEventListener("abort", function(evt) {
                transferError(lang.tr('download_error_abort').out());
                return;
            });
            

            //
            // Primary path
            // When we get the chunk data (or an XHR error)
            //
            oReq.onload = function (oEvent) {
                window.filesender.log("ddChunk(onload)");
                
                // check for a redirect containing and error and halt if so
                if( $this.handleXHRError( oReq, link, 'file_encryption_wrong_password' )) {
                    return;
                }

                //
                // call decryptBlob to handle this chunk and pass a "next"
                // function to decryptBlob which will call us for the next chunk.
                var arrayBuffer = new Uint8Array(oReq.response);
                setTimeout(function(){

                    var sliced = window.filesender.crypto_blob_reader().sliceForDownloadBuffers(arrayBuffer);
                    var encryptedChunk = window.filesender.crypto_common().separateIvFromData(sliced[0]);
                    
                    $this.decryptBlob(
                        chunkid,
                        encryptedChunk,
                        encryption_details, key,
                        blobSink,
                        function() {
                            $this.downloadAndDecryptChunk( chunkid+1, link, progress,
                                                           encryption_details, key,
                                                           blobSink,
                                                           callbackDone, callbackProgress, callbackError);
                        },
                        callbackDone, callbackError );
                }, 20);
            };
            
            // start downloading this chunk
            oReq.send();
        },

        /**
         * Some functions like handleXHRError() want to call alert()
         * but that can not happen from a web worker. By making this
         * alert() a callback function it allows such an alert() call
         * to be sent back to the main thread by an error() type message
         * channel. The callback must match the alert() function signature.
         */
        alertcb: window.alert,
        
        /**
         * Display an error message to the user in has the XHR error
         * is fatal.
         *
         * @return true if there was an error and code should halt.
         */
        handleXHRError: function( xhr, link, defaultMsg )
        {
            var $this = this;
            
            if(xhr.responseURL && xhr.responseURL.includes("/?s=exception&"))
            {
                window.filesender.log("handleXHRError(XHR ERROR DETECTED)");
                window.filesender.log("handleXHRError link " + link );
                window.filesender.log("handleXHRError got  " + xhr.responseURL );

                var message = defaultMsg;
                var url = new URL(xhr.responseURL);
                var c = url.searchParams.get("exception");
                if( c ) {
                    try {
                        var jc = JSON.parse(atob(c));
                        window.filesender.log("handleXHRError jc " + jc );
                        
                        if( jc ) {
                            message = jc.message;
                            window.filesender.log("have untranslated message: " + message );
                        }
                    } catch( e ) {
                        // use default message if base64 decode failed.
                    }
                }

                if( window.filesender.config.language[message] ) {
                    $this.alertcb.call( window, window.filesender.config.language[message] );
                } else {
                    $this.alertcb.call( window, window.filesender.config.language[defaultMsg] );
                }                            
                return true;
            }
            return false;
        },
        /**
         * This is the main entry point to download an encrypted file.
         * The method sets up an encryption_details object, does some crypto checks that can be 
         * done early, for example, making sure AEAD data seems valid (data present and some checks).
         * 
         * Then a password is requested, a key is setup, and a sink is created to save decrypted data
         * depending on which features the browser supports. 
         * Finally downloadAndDecryptChunk() is used to start streaming the chunks down to this system.
         * 
         * @param fileiv is the decoded fileiv. Decoding can be done with decodeCryptoFileIV()
         */
        decryptDownloadToBlobSink: function (blobSink, pass, transferid,
                                             link, mime, name, filesize, encrypted_filesize,
                                             key_version, salt,
                                             password_version, password_encoding, password_hash_iterations,
                                             client_entropy, fileiv, fileaead,
                                             progress) {
            var $this = this;
            var encryption_details = { password:           pass,
                                       filesize:           filesize,
                                       encrypted_filesize: encrypted_filesize,
                                       // zero based count.
                                       chunkcount: Math.ceil( filesize / (1* $this.crypto_chunk_size))-1,
                                       key_version:       key_version,
                                       salt:              salt,
                                       password_version:  password_version,
                                       password_encoding: password_encoding,
                                       password_hash_iterations: password_hash_iterations,
                                       client_entropy:    client_entropy,
                                       fileiv:            fileiv,
                                       fileaead:          fileaead,
                                       transferid:        transferid
                                     };
            // For GCM this will be the fileiv (96 bits of fixed entropy).
            encryption_details.expected_fixed_chunk_iv = new Uint8Array(16);
            encryption_details.aead = {};
            encryption_details.client_entropy_decoded = new Uint8Array(16);

            // Should we use streamsaver for this download?
            var use_streamsaver = $this.useStreamSaver();
            window.filesender.log('StreamSaver info. config.allow ' + window.filesender.config.allow_streamsaver
                                  + ' this.disable ' + this.disable_streamsaver
                                  + ' config.use ' + use_streamsaver );

            if( use_streamsaver ) {
                const ponyfill = window.WebStreamsPolyfill || {};
                streamSaver.WritableStream = ponyfill.WritableStream;
                streamSaver.mitm = window.filesender.config.streamsaver_mitm_url;
                streamSaver.WritableStream = ponyfill.WritableStream;
            }


            /*
             * This error callback allows for more detail to be given if one of the
             * prior to crypto Algorithm specific assertions fails.
             */
            var callbackError = function (error) {
                window.filesender.log(error);
                window.filesender.ui.error(error);
                if (progress){
                    progress.html("");
                }
                blobSink.error( error );
            };

            /*
             * Algorithm specific assertions.
             */
            if( key_version == $this.crypto_key_version_constants.v2019_gcm_digest_importKey ||
                key_version == $this.crypto_key_version_constants.v2019_gcm_importKey_deriveKey )
            {
                if( encryption_details.fileiv.length != $this.crypto_gcm_per_file_iv_octet_size )
                {
                    return callbackError({message: 'decryption_verification_failed_bad_fixed_iv',
                                          details: {}});
                }
                encryption_details.expected_fixed_chunk_iv = encryption_details.fileiv;

                // assert that we know the version of AEAD structure
                // and the the chunk size has not changed, and we expect
                // the same number of chunks as the AEAD stipulates
                if( !encryption_details.fileaead || !encryption_details.fileaead.length  ) {
                    window.filesender.log("decryptBlobSpecificFinalChunkChecks() bad aead 2");
                    return callbackError({message: 'decryption_verification_failed_bad_aead',
                                          details: {}});
                }
                encryption_details.aead = JSON.parse(encryption_details.fileaead);
                if( encryption_details.aead.aeadversion != 1 ) {
                    window.filesender.log("decryptBlobSpecificFinalChunkChecks() bad aead 3");
                    return callbackError({message: 'decryption_verification_failed_bad_aead',
                                          details: {}});
                }
                if( encryption_details.aead.chunksize != window.filesender.config.upload_chunk_size ) {
                    window.filesender.log("decryptBlobSpecificFinalChunkChecks() bad aead 4");
                    return callbackError({message: 'decryption_verification_failed_bad_aead',
                                          details: {}});
                }
                if( !encryption_details.aead.iv ) {
                    window.filesender.log("decryptBlobSpecificFinalChunkChecks() bad aead 5");
                    return callbackError({message: 'decryption_verification_failed_bad_aead',
                                          details: {}});
                }

                // Make sure that the 96bits of entropy from the file iv contained in
                // AEAD matches the 96bits of the expected IV that was sent
                // from the server
                var aeadiv = $this.decodeCryptoFileIV(encryption_details.aead.iv,key_version);
                if( !encryption_details.expected_fixed_chunk_iv.equals(aeadiv)) {
                    window.filesender.log("decryptBlobSpecificFinalChunkChecks() bad aead 6");
                    return callbackError({message: 'decryption_verification_failed_bad_aead',
                                          details: {}});
                }
                
            }

            // decode client entropy if we have it
            if( encryption_details.client_entropy &&
                encryption_details.client_entropy.length )
            {
                encryption_details.client_entropy_decoded =
                    $this.decodeClientEntropy(
                        encryption_details.client_entropy );
            }

            
            //
            // Perhaps we should change the default policy from converting errors
            // to assumed password failures.
            //
            var callbackError = function (error) {
                window.filesender.log("decryptDownloadToBlobSink() explicit error " + error);
                window.filesender.log(error);
                window.filesender.crypto_app_downloading = false;
                var msg = window.filesender.config.language.file_encryption_wrong_password;
                
                if( error && error.message && error.message != "" ) {
                    msg = error.message;
                } 
                filesender.ui.alert( "error", msg );

                if (progress){
                    progress.html( msg );
                }
                if( msg == window.filesender.config.language.file_encryption_wrong_password ) {
                    filesender.terasender.stop();
                }
                blobSink.error( error );
            };

            var onProgressCallback = function( ts, chunkid, totalrecv, percentComplete, percentOfFileComplete ) {

                var msg = lang.tr('download_chunk_progress').r({chunkid: chunkid,
                                                                chunkcount: encryption_details.chunkcount,
                                                                percentofchunkcomplete: percentComplete.toFixed(2),
                                                                percentOffilecomplete:  percentOfFileComplete.toFixed(2)
                                                               }).out();
                progress.html(msg);
            }
            
                encryption_details.password = pass;

                $this.obtainKey(
                    0, encryption_details,
                    function (key) {
                        var chunkid = 0;

                        
                        
                        var callbackDone = function (blobSink) {

                            window.filesender.log("callbackDone()");
                            window.filesender.crypto_app_downloading = false;
                                       
                            if( progress ) {
                                progress.html(window.filesender.config.language.download_complete);
                            }
                            blobSink.done();

                            window.filesender.crypto_last_password_succeeded = true;
                        };
                        
                        var callbackProgress = function (i,c) {
                            var percentComplete = Math.round(i / c *10000)/100;
                            if (progress) {
                                progress.html(window.filesender.config.language.decrypting+": "+percentComplete.toFixed(2)+" %");
                            }
                        };

                        window.filesender.crypto_app_downloading = true;

                        var transfer = new filesender.transfer();
                        if (transfer.canUseTeraReceiver()) {

                            transfer.id = transferid;
                            transfer.encryption = 1;

                            var decryptCallback = function( job ) {
                                $this.decryptBlob(
                                    job.chunkid,
                                    job.encryptedChunk,
                                    job.encryption_details,
                                    key,
                                    filesender.terasender.receiver.blobSink,
                                    function() {
                                        // callbackNext()
                                    },
                                    callbackDone, callbackError );
                            };

                            filesender.terasender.crypto_app = this;
                            filesender.terasender.receiver = {
                                chunkid: chunkid,
                                link: link,
                                progress: progress,
                                encryption_details: encryption_details,
                                key: key,
                                blobSink: blobSink,
                                onChunkSuccess: decryptCallback,
                                onProgress: onProgressCallback,
                                onError: callbackError
                            };

                            filesender.terasender.startReceiver( transfer );
                            
                        } else {
                            $this.downloadAndDecryptChunk( chunkid, link, progress,
                                                           encryption_details, key,
                                                           blobSink,
                                                           callbackDone, callbackProgress, callbackError );
                        }
                    },
                    function (e) {
                        // error occured during obtainkey
                        window.filesender.ui.log(e);
                    }
                );
 
        },
        decryptDownload: function (link, transferid, mime, name, filesize, encrypted_filesize,
                                   key_version, salt,
                                   password_version, password_encoding, password_hash_iterations,
                                   client_entropy, fileiv, fileaead,
                                   progress)
        {
            var $this = this;

            filesender.crypto_encrypted_archive_download = false;
            
            var callbackError = function (error) {
                window.filesender.log(error);
                window.filesender.crypto_app_downloading = false;
                filesender.ui.alert("error",window.filesender.config.language.file_encryption_wrong_password);
                if (progress){
                    progress.html(window.filesender.config.language.file_encryption_wrong_password);
                }
            };

            // Should we use streamsaver for this download?
            var use_streamsaver = $this.useStreamSaver();
            
            
            /*
             * This is a blob visitor that performs a legacy (as of mid 2020)
             * chunked download. The legacy code has been brought forward to allow
             * older browsers to continue to work. The old style code creates a blob array
             * with all the data and saves it in one go at the end of download.
             * 
             * As decrypted data is visit()ed it is pushed onto a blob array.
             * When done() is called on this object the collected blob array 
             * is handed off to saveAs() for storage.
             * 
             * This is very RAM intensive but where browsers do not support more modern
             * features it will at least work up to the point that encrypted files are too
             * large to be temporarily held in RAM. 
             */
            var blobSinkLegacy = {
                blobArray: [],
                // keep a tally of bytes processed to make sure we get everything.
                bytesProcessed: 0,
                expected_size: filesize,
                callbackError: callbackError,
                name: function() { return "legacy"; },
                error: function(error) {
                },
                visit: function(chunkid,decryptedData) {
                    window.filesender.log("blobSinkLegacy visiting chunkid " + chunkid + "  data.len " + decryptedData.length );
                    this.blobArray.push(decryptedData);
                    this.bytesProcessed += decryptedData.length;
                },
                done: function() {
                    window.filesender.log("blobSinkLegacy.done()");
                    window.filesender.log("blobSinkLegacy.done()      expected size " + filesize );
                    window.filesender.log("blobSinkLegacy.done() decryped data size " + this.bytesProcessed );

                    if( this.expected_size != this.bytesProcessed ) {
                        window.filesender.log("blobSinkLegacy.done() size mismatch");
                        this.callbackError('decrypted data size and expected data size do not match');
                        return;
                    }
                    
                    var blob = new Blob(this.blobArray, {type: mime});
                    window.filesender.log("blobSinkLegacy.done() using saveas to write blob" );
                    saveAs(blob, name);
                }
            };
            var blobSink = blobSinkLegacy;
            var blobSinkStreamed = blobSinkLegacy;

            window.filesender.log('Newer streaming API information.'
                                  + ' Use streamsaver: ' + window.filesender.config.use_streamsaver
                                  + ' use FileSystemWritableFileStream (FSWF) ' + window.filesender.config.useFileSystemWritableFileStreamForDownload());
            /*
             * As of 2023 we can use either streamsaver or FileSystemWritableFileStream
             * to stream the contents to a file on disk. Note that the final else case is already
             * attended to above as blobSink is already a blobSinkLegacy
             */
            if( window.filesender.config.useFileSystemWritableFileStreamForDownload()) {
                window.filesender.log('Using FSWF code for storing data...' );
                blobSinkStreamed = window.filesender.filesystemwritablefilestream_sink( name, filesize, callbackError );
                blobSink = blobSinkStreamed;
            } else if( window.filesender.config.use_streamsaver ) {
                window.filesender.log('Using new StreamSaver code for storing data...' );
                blobSinkStreamed = window.filesender.streamsaver_sink( name, filesize, callbackError );
                blobSink = blobSinkStreamed;
            }

            var defaultPasswordValue = '';
            if( window.filesender.crypto_last_password_succeeded ) {
                defaultPasswordValue = window.filesender.crypto_last_password;
            }
            
            window.filesender.log("Using blobSink " + blobSink.name());
            var prompt = window.filesender.ui.promptPassword(window.filesender.config.language.file_encryption_enter_password, function (pass) {
            
                window.filesender.crypto_last_password = pass;
                $this.decryptDownloadToBlobSink( blobSink, pass, transferid,
                                                 link, mime, name, filesize, encrypted_filesize,
                                                 key_version, salt,
                                                 password_version, password_encoding, password_hash_iterations,
                                                 client_entropy, fileiv, fileaead,
                                                 progress);
            }, function(){
                window.filesender.ui.notify('info', window.filesender.config.language.file_encryption_need_password);
            }, defaultPasswordValue );

            
            // Add a field to the prompt
            var trshowhide = window.filesender.config.language.file_encryption_show_password;
            var toggleView = $('<br/><div class="custom-control custom-switch " ><input class="custom-control-input"  type="checkbox" id="showdlpass" name="showdlpass" value="false"><label class="custom-control-label" for="showdlpass">' + trshowhide + '</label></div>');

            if( window.filesender.crypto_last_password_succeeded ) {
                $('<p>' + lang.tr('previous_password_shown_for_next_action').out() + '</p>').appendTo(prompt);
            }                        
            
            window.filesender.crypto_last_password_succeeded = false;
            prompt.append(toggleView);
            $('#showdlpass').on(
                "click",
                function() {
                    var v = $('#showdlpass').is(':checked');
                    if( v ) { $('.bootbox-input').attr('type','text'); }
                    else    { $('.bootbox-input').attr('type','password'); }
                }
            );
                
        },
        // Note that this can not include : in the time part as that
        // does not work on Win in Edge.
        getArchiveFileName: function(link,selectedFiles,archiveFormat) {
            var d = new Date();
            var archiveName = "FileSenderDownload_" + 
                d.getDate() + "-" + (d.getMonth()+1)  + "-" + d.getFullYear() + "__"  
                + d.getHours() + "-" + d.getMinutes() + "-" + d.getSeconds()
                + "." + archiveFormat;
            return archiveName;
        },
        setDownloadFileidlist: function( selectedFiles ) {
            var fileidlist = '';
            for(var i=0; i<selectedFiles.length; i++) {
                var f = selectedFiles[i];
                fileidlist += f.fileid;
                fileidlist += ',';
            }
            window.filesender.crypto_encrypted_archive_download_fileidlist = fileidlist;
        },
        decryptDownloadToZip: function(link,transferid,selectedFiles,progress,onFileOpen,onFileClose,onComplete) {

            var $this = this;

            var callbackError = function (error) {
                window.filesender.log(error);
                window.filesender.crypto_app_downloading = false;
                filesender.ui.alert("error",window.filesender.config.language.file_encryption_wrong_password);
                if (progress){
                    progress.html(window.filesender.config.language.file_encryption_wrong_password);
                }
            };

            var defaultPasswordValue = '';
            if( window.filesender.crypto_last_password_succeeded ) {
                defaultPasswordValue = window.filesender.crypto_last_password;
            }
            
            var prompt = window.filesender.ui.promptPassword(window.filesender.config.language.file_encryption_enter_password, function (pass) {
                window.filesender.crypto_last_password = pass;

                var archiveName = $this.getArchiveFileName(link,selectedFiles,"zip");

                window.filesender.log('Zip64 newer streaming API information.'
                                      + ' Use streamsaver: ' + window.filesender.config.use_streamsaver
                                      + ' use FileSystemWritableFileStream (FSWF) ' + window.filesender.config.useFileSystemWritableFileStreamForDownload());

                if( !$this.useStreamSaver()
                    && !window.filesender.config.useFileSystemWritableFileStreamForDownload())
                {
                    // no streaming method is available to use!
                    window.filesender.log("ERROR can not use any streaming tech to download into a zip file!");
                    return;
                }
                                                     
                
                var blobSinkStreamed = window.filesender.archive_sink( $this, link, transferid, archiveName, pass, selectedFiles, callbackError );
                var blobSink = blobSinkStreamed;
                blobSink.init()
                    .then( () => {
                        blobSink.progress = progress;
                        blobSink.onOpen   = onFileOpen;
                        blobSink.onClose  = onFileClose;
                        blobSink.onComplete = onComplete;
                        
                        // start downloading.
                        blobSink.downloadNext();
                    })
                    .catch( e => {
                        callbackError(e);
                    });

                
                
            }, function(){
                window.filesender.ui.notify('info', window.filesender.config.language.file_encryption_need_password);
            }, defaultPasswordValue );

            // Add a field to the prompt
            var trshowhide = window.filesender.config.language.file_encryption_show_password;
            var toggleView = $('<br/><div class="custom-control custom-switch " ><input class="custom-control-input" type="checkbox" id="showdlpass" name="showdlpass" value="false"><label class="custom-control-label" for="showdlpass">' + trshowhide + '</label></div>');

            if( window.filesender.crypto_last_password_succeeded ) {
                $('<p>' + lang.tr('previous_password_shown_for_next_action').out() + '</p>').appendTo(prompt);
            }                        
            window.filesender.crypto_last_password_succeeded = false;
            
            prompt.append(toggleView);
            $('#showdlpass').on(
                "click",
                function() {
                    var v = $('#showdlpass').is(':checked');
                    if( v ) { $('.bootbox-input').attr('type','text'); }
                    else    { $('.bootbox-input').attr('type','password'); }
                }
            );
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
            var encoding = window.filesender.config.encryption_generated_password_encoding;

            var desired_version = window.filesender.config.encryption_random_password_version_new_files;
            if( $this.crypto_password_version_constants.v2018_text_password == desired_version ) {
                // This is the password generation in place through 
                // the first half of 2019.
                var desiredPassLen = window.filesender.config.encryption_generated_password_length;
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
                window.filesender.ui.rawError('{bad password encoding set, you should never see this error}')
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
                        var i = 0;
                        for( i=0; i < raw.length; i++ ) {                        
                            raw[i] = decoded.charCodeAt(i);
                        }
                    } catch(e) {
                        window.filesender.log(e);
                        // we know the password is invalid bad if we can not base64 decode it
                        // after all, we base64 encoded it in generateRandomPassword().
                        throw(window.filesender.config.language.file_encryption_wrong_password);
                    }
                }
            }
            else {
                window.filesender.ui.rawError('{bad password encoding set, you should never see this error}')
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
        },

        /**
         * Check file size for encryption limits
         *
         * @return true if things are ok
         */
        isFileSizeValidForEncryption: function( size ) {
            var $this = this;

            var key_version = window.filesender.config.encryption_key_version_new_files;
        
            if( key_version == $this.crypto_key_version_constants.v2019_gcm_digest_importKey ||
                key_version == $this.crypto_key_version_constants.v2019_gcm_importKey_deriveKey )
            {
                if( size > window.filesender.config.crypto_gcm_max_file_size ) {
                    return false;
                }
            }
            
            return true;
        }
    };
};
