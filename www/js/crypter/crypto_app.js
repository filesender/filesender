if (typeof window === 'undefined')
    window = {}; // dummy window
if (!('filesender' in window))
    window.filesender = {};

window.filesender.crypto_app = function () {
    return {
        crypto_is_supported: true,
        crypto_chunk_size: 5 * 1024 * 1024,
        crypto_iv_len: 16,
        crypto_crypt_name: "AES-CBC",
        crypto_hash_name: "SHA-256",
        init: function () {
            if (crypto.subtle)
            {
                alert("Cryptography API Supported");
            } else
            {
                alert("Cryptography API not Supported");
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
        encrypt: function (value, password, callback) {
            var $this = this;
            this.generateKey(password, function (key, iv) {
                crypto.subtle.encrypt({name: $this.crypto_crypt_name, iv: iv}, key, window.filesender.crypto_common().convertStringToArrayBufferView(value)).then(
                        function (result) {
                            callback(
                                    btoa(
                                            window.filesender.crypto_common().convertArrayBufferViewtoString(
                                            window.filesender.crypto_common().joinIvAndData(iv, new Uint8Array(result))
                                            )
                                            )
                                    );
                        },
                        function (e) {
                            // error occured during crypt
                            console.log(e);
                        }
                );
            });
        },
        decrypt: function (value, password, callback) {
            var $this = this;
            var encryptedData = value;
            this.generateKey(password, function (key) {
                var value = window.filesender.crypto_common().separateIvFromData(
                        window.filesender.crypto_common().convertStringToArrayBufferView(
                            atob(encryptedData)
                        )
                    );
                crypto.subtle.decrypt({name: $this.crypto_crypt_name, iv: value.iv}, key, value.data).then(
                        function (result) {
                            callback(window.filesender.crypto_common().convertArrayBufferViewtoString(new Uint8Array(result)));
                        },
                        function (e) {
                            alert('wrong password');
                        });
            });
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

            this.generateKey(password, function (key) {
                console.log(encryptedData.length);
                for (var i = 0; i < encryptedData.length; i++) {
                    var value = window.filesender.crypto_common().separateIvFromData(encryptedData[i]);
                    crypto.subtle.decrypt({name: $this.crypto_crypt_name, iv: value.iv}, key, value.data).then(
                            function (result) {
                                var blobArrayBuffer = new Uint8Array(result);
                                blobArray.push(blobArrayBuffer);
                                // done
                                if (blobArray.length === encryptedData.length) {
                                    callback(blobArray);
                                }
                            },
                            function (e) {
                                alert('Foutief wachtwoord');
                            });
                  

                }
            });
        },
        decryptDownload: function (link, mime, name) {
            var $this = this;

            // Decrypt the contents of the file
            var oReq = new XMLHttpRequest();
            oReq.open("GET", link, true);
            oReq.responseType = "arraybuffer";

            oReq.onload = function (oEvent) {
                // hands over to the decrypter
                var arrayBuffer = new Uint8Array(oReq.response);
                // Create a prompt to ask for the password
                var prompt = filesender.ui.prompt('Geef een wachtwoord op', function(password){
                     $this.decryptBlob(
                        window.filesender.crypto_blob_reader().sliceForDownloadBuffers(arrayBuffer),
                        $(this).find('input').val(),
                        function (decrypted) {
                            var blob = new Blob(decrypted, {type: mime});
                            saveAs(blob, name);
                        }
                    );
                }, function(){
                    filesender.ui.notify('info', 'Zonder wachtwoord kan dit bestand niet worden geopend');
                });
                
                // Add a field to the prompt
                var input = $('<input type="text" class="wide" />').appendTo(prompt);
                input.focus();
            };
            
            // create download
            oReq.send();
        }
    };
};