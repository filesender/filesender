if(!('filesender' in window)) window.filesender = {};

window.filesender.crypto = function () {
    
    var cryptoApp = window.filesender.crypto_app;
    var cryptoBlobReader = window.filesender.crypto_blob_reader;
    
    var cryptedBlob = null;
    var cryptedMime = null;

    // Encrypt the contents of the field
    document.getElementById('btn_encrypt').onclick = function () {
        cryptoApp.encrypt(document.getElementById('plain_text').value, document.getElementById('password').value, function (encrypted) {
            document.getElementById('encrypted').value = encrypted;
        });
    };
    // Dencrypt the contents of the field
    document.getElementById('btn_decrypt').onclick = function () {
        cryptoApp.decrypt(document.getElementById('encrypted').value, document.getElementById('password').value, function (decrypted) {
            document.getElementById('plain_text').value = decrypted;
        });
    };


    // Attach the filereader to the file select of the browser

    // Show the content in the textarea (small files only please)

    // Encrypt the contents of the file
    document.getElementById('btn_encrypt_file').onclick = function () {
        
        var numberOfCryptedChunks = 0;
        
        for (var i = 0; i < document.getElementById('files').files.length; i++) {
            cryptoBlobReader.createReader(document.getElementById('files').files[i], function(blob){
                cryptedMime = document.getElementById('files').files[i].type;
                cryptoApp.encryptBlob(blob, document.getElementById('password').value, function (encrypted) {
                    cryptedBlob = encrypted;
                    console.log('klaar');
                });
            });
        }
    };
    
    // Dencrypt the contents of the file
    document.getElementById('btn_decrypt_file').onclick = function () {
        cryptoApp.decryptBlob(cryptedBlob, document.getElementById('password').value, function (decrypted) {
            console.log('decrypted, going for the save');
            var blob = new Blob(decrypted, {type: cryptedMime});
            saveAs(blob, "test.mp4");
        });
    };
};