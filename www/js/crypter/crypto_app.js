if (typeof window === 'undefined')
    window = {}; // dummy window for use in webworkers

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
            if (window.msCrypto) {
                window.crypto = window.msCrypto;
            }
            if (window.crypto && !window.crypto.subtle && window.crypto.webkitSubtle) {
                window.crypto.subtle = window.crypto.webkitSubtle;
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
                    filesender.ui.log(e);
                });
            }), function (e) {
                // error making a hash
                filesender.ui.log(e);
            };
        },
        encryptBlob: function (value, password, callback) {
            var $this = this;
            
            $this.init();
            
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
                            filesender.ui.log(e);
                        }
                );


            });
        },
        decryptBlob: function (value, password, callbackDone, callbackProgress, callbackError) {
            var $this = this;
            
            $this.init();
            
            var encryptedData = value; // array buffers array
            var blobArray = [];

            this.generateKey(password, function (key) {
                var wrongPassword = false;
                var decryptLoop = function (i) {
                    callbackProgress(i, encryptedData.length); //once per chunk
                    var value = window.filesender.crypto_common().separateIvFromData(encryptedData[i]);
                    crypto.subtle.decrypt({name: $this.crypto_crypt_name, iv: value.iv}, key, value.data).then(
                            function (result) {
                                var blobArrayBuffer = new Uint8Array(result);
                                blobArray.push(blobArrayBuffer);
                                // done
                                if (blobArray.length === encryptedData.length) {
                                    callbackDone(blobArray);
                                } else {
                                    if (i < encryptedData.length) {
                                        setTimeout(decryptLoop(i + 1), 300);
                                    }
                                }
                            },
                            function (e) {
                                if (!wrongPassword) {
                                    wrongPassword = true;
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
            
            $this.init();
            
            var prompt = filesender.ui.prompt(window.filesender.config.language.file_encryption_enter_password, function (password) {
                var pass = $(this).find('input').val();

                // Decrypt the contents of the file
                var oReq = new XMLHttpRequest();
                oReq.open("GET", link, true);
                oReq.responseType = "arraybuffer";

                //Download progress
                oReq.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round(evt.loaded / evt.total * 10000) / 100;
                        if (progress)
                            progress.html(window.filesender.config.language.downloading + ": " + percentComplete.toFixed(2) + " %");
                    }
                }, false);

                //on file arrived
                oReq.onload = function (oEvent) {
                    if (progress) {
                        progress.html(window.filesender.config.language.decrypting + "...");
                    }
                    // hands over to the decrypter
                    var arrayBuffer = new Uint8Array(oReq.response);
                    setTimeout(function () {
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
                                function (i, c) {
                                    var percentComplete = Math.round(i / c * 10000) / 100;
                                    if (progress) {
                                        progress.html(window.filesender.config.language.decrypting + ": " + percentComplete.toFixed(2) + " %");
                                    }
                                },
                                function (error) {
                                    alert(window.filesender.config.language.file_encryption_wrong_password);
                                    if (progress) {
                                        progress.html(window.filesender.config.language.file_encryption_wrong_password);
                                    }
                                }
                        );
                    }, 300);
                };
                // create download
                oReq.send();

            }, function () {
                filesender.ui.notify('info', window.filesender.config.language.file_encryption_need_password);
            });

            // Add a field to the prompt
            var input = $('<input type="text" class="wide" />').appendTo(prompt);
            input.focus();
        }
    };
};
