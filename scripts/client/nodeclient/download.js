
var filesenerapi = require('./filesenderapi');

const http = require('https');
const fs = require('fs'); 
const ini = require('ini');
const path = require('node:path');

var argv = require('minimist')(process.argv.slice(2));
var expireInDays = 7;
if( argv.expire && argv.expire >= 1 ) {
    if( argv.expire > config.max_transfer_days_valid ) {
        console.log("Error, you need to set the expire days to less than ", config.max_transfer_days_valid );
        return 1;
    }
    expireInDays = argv.expire;
}

// FIXME we should support generating a password if they do not supply anything.
if( !argv.password ) {
    console.log("please use the --password option to supply a password");
    return;
}
var password = argv.password;

global.alert = function(msg) { console.log(msg); }
if( !filesender.terasender ) {
    filesender.terasender = {};
}
filesender.terasender.stop = function() { console.log("EEEEE terasender.stop");}


async function downloadFiles( basepath, token, files )
{
    console.log("downloadFiles...!");
    files.forEach((dl) => {

        var crypto_app = window.filesender.crypto_app();

        window.filesender.ui.prompt = function() { return password; }
        // var progress = function() { };
        var progress = null;
        window.filesender.crypto_encrypted_archive_download = false;

        var filesize = dl.size;
        var mime = dl.mime;
        var cleanName = dl.name;
        cleanName = cleanName.replace(/^[.\/]+/, '');

        // ensure directory
        var filepath = basepath + "/" + path.dirname(cleanName);
        if (!fs.existsSync(filepath)){
            fs.mkdirSync(filepath, { recursive: true });
        }
        // make path full again
        var filepath = basepath + "/" + cleanName;

        

        fs.writeFileSync(filepath, Buffer.from(''),
                         {
                             encoding: "utf8",
                             flag: "w",
                             mode: 0o660
                         });                                                 
        
        var blobSinkLegacy = {
            blobArray: [],
            // keep a tally of bytes processed to make sure we get everything.
            bytesProcessed: 0,
            expected_size: filesize,
            //                                             callbackError: callbackError,
            name: function() { return "legacy"; },
            error: function(error) {
                console.log("");
                console.log(window.filesender.config.language.file_encryption_wrong_password);
                console.log("An error has occurred, most likely your password was incorrect.");
                process.exit(1);
            },
            visit: function(chunkid,decryptedData) {
                //                                                 window.filesender.log("SINK blobSinkLegacy visiting chunkid " + chunkid + "  data.len " + decryptedData.length );
                this.blobArray.push(decryptedData);
                this.bytesProcessed += decryptedData.length;
                var buffer = Buffer.from(decryptedData);
                fs.writeFileSync(filepath, buffer,
                                 {
                                     encoding: "utf8",
                                     flag: "a+",
                                     mode: 0o660
                                 });                                                 
                
            },
            done: function() {
                // window.filesender.log("SINK blobSinkLegacy.done()");
                // window.filesender.log("SINK blobSinkLegacy.done()      expected size " + filesize );
                // window.filesender.log("SINK blobSinkLegacy.done() decryped data size " + this.bytesProcessed );
                // window.filesender.log("SINK blobSinkLegacy.done()     blobarray size " + this.blobArray.length );

                if( this.expected_size != this.bytesProcessed ) {
                    window.filesender.log("blobSinkLegacy.done() size mismatch");
                    //                                                     this.callbackError('decrypted data size and expected data size do not match');
                    return;
                }
                console.log("Your files have been downloaded to ", basepath );
            }
        };
        
        var blobSink = blobSinkLegacy;
        var blobSinkStreamed = blobSinkLegacy;
        var link = config.site_url
            + 'download.php?token=' + token
            + '&files_ids=' + dl.id;

        // make sure aead is decoded.
        if( dl.fileaead ) {
            try {
                v = JSON.parse( dl.fileaead );
            } catch( e ) {
                dl.fileaead = atob(dl.fileaead);
            }
        }
        
        crypto_app.decryptDownloadToBlobSink( blobSink, password,
                                              dl.transferid, link,
                                              dl.mime, dl.name, dl.size, dl.encrypted_size,
                                              dl.key_version, dl.key_salt,
                                              dl.password_version, dl.password_encoding,
                                              dl.password_hash_iterations,
                                              dl.client_entropy,
                                              window.filesender.crypto_app().decodeCryptoFileIV(dl.fileiv,dl.key_version),
                                              dl.fileaead,
                                              progress );


        
        
    });
}

argv._.forEach((transferLink) => {
    console.log("Downloading from transfer ", transferLink);

    var rx = /token=([^&]+)/g;
    var token = rx.exec(transferLink)[1];

    if( token.length != 36 ) {
        console.log("Sorry, you have supplied a bad token in your download link. Expected token length is 36 and you have given ", token.length );
        return 1;
    }

    var basepath = "./" + token;
    if (!fs.existsSync(basepath)){
        fs.mkdirSync(basepath, { recursive: true });
    }    
    
    var options = { args: {'token': token}};
    window.filesender.client.get('/transfer/fileidsextended',
                                 function (files) {
                                     downloadFiles( basepath, token, files );
                                 }, options);    
    
});
