if (typeof window === 'undefined')
    window = {}; // dummy window
if (!('filesender' in window))
    window.filesender = {};

window.filesender.crypto_app = function () {
    return {
        crypto_is_supported: true,
        crypto_chunk_size: window.filesender.config.upload_chunk_size,
        crypto_iv_len: window.filesender.config.crypto_iv_len,
        crypto_crypt_name: window.filesender.config.crypto_crypt_name,
        crypto_hash_name: window.filesender.config.crypto_hash_name,
        
        init: function () {
            if (crypto.subtle)
            {
            } else
            {
                filesender.ui.notify('info', lang.tr('encryption_api_unsupported'));
                this.crypto_is_supported = false;
            }
        },
        generateVector: function () {
            return crypto.getRandomValues(new Uint8Array(16));
        },
        generateKey: function (password, callback) {
            var iv = this.generateVector();
            var $this = this;
            crypto.subtle.digest({name: this.crypto_hash_name}, window.filesender.crypto_common().convertStringToArrayBufferView(password)).then(function (key) {
                crypto.subtle.importKey("raw", key, {name: $this.crypto_crypt_name, iv: iv}, false, ["encrypt", "decrypt"]).then(function (key) {
                    callback(key, iv);
                }, function (e) {
                    // error making a key
                    console.log(e);
                });
            }), function (e) {
                // error making a hash
                console.log(e);
            };
        },
        encryptBlob: function (value, password, callback) {
            var $this = this;
            this.generateKey(password, function (key, iv) {
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
                            console.log(e);
                        }
                );


            });
        },
        decryptBlob: function (value, password, callback) {
            var $this = this;
            var encryptedData = value; // array buffers array
            var blobArray = [];
            var error = false;
            this.generateKey(password, function (key) {
                console.log(encryptedData.length);
                for (var i = 0; i < encryptedData.length; i++) {
                    if (error) {
                        break;
                    }
                    //console.log('chunk ' + i + ' pre prepared');
                    var value = window.filesender.crypto_common().separateIvFromData(encryptedData[i]);
                    //console.log('chunk ' + i + ' prepared');
                    crypto.subtle.decrypt({name: $this.crypto_crypt_name, iv: value.iv}, key, value.data).then(
                            function (result) {
                                //console.log('chunk' + i + 'done');
                                var blobArrayBuffer = new Uint8Array(result);
                                blobArray.push(blobArrayBuffer);
                                // done
                                if (blobArray.length === encryptedData.length) {
                                    callback(blobArray);
                                }
                            },
                            function (e) {
                                if (!error) {
                                    filesender.ui.notify('info', lang.tr('encryption_incorrect_password'));
                                    error = true;
                                }
                            });
                    //console.log('chunk ' + i + ' done preparing');

                }
            });
        },
        decryptDownload: function (link, mime, name) {
            var $this = this;

            // Decrypt the contents of the file
            var oReq = new XMLHttpRequest();
            oReq.open("GET", link, true);
            oReq.responseType = "arraybuffer";

            oReq.onprogress = function update_progress(e)
            {
                if (e.lengthComputable)
                {
                    var percentage = Math.round((e.loaded / e.total) * 100);
                    console.log("percent " + percentage + '%');
                } else
                {
                    console.log("Unable to compute progress information since the total size is unknown");
                }
            };

            oReq.onload = function (oEvent) {
                // Create a prompt to ask for the password
                var prompt = filesender.ui.prompt('Geef een wachtwoord op', function (password) {
                    $this.decryptBlob(
                            window.filesender.crypto_blob_reader().sliceForDownloadBuffers(new Uint8Array(oReq.response)),
                            $(this).find('input').val(),
                            function (decrypted) {
                                var blob = new Blob(decrypted, {type: mime});
                                saveAs(blob, name);
                            }
                    );
                }, function () {
                    filesender.ui.notify('info', lang.tr('encryption_missing_password'));
                });

                // Add a field to the prompt
                var input = $('<input type="password" class="wide" />').appendTo(prompt);
                input.focus();
            };

            // create download
            oReq.send();
        }
    };
};