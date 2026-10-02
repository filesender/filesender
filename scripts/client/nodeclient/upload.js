
var filesenerapi = require('./filesenderapi');

const http = require('https');
const fs = require('fs');
const ini = require('ini');
var recursive = require("recursive-readdir");

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
    

transfer.encryption = true;
transfer.encryption_password = password;
transfer.disable_terasender = true;

var addFile = function( filename )
{
    console.log("Adding file: ", filename );

    var displayPath = filename.replace(/^[\.\/]*/, '');
    var data = fs.readFileSync(filename);
    
    var blob = new Blob([data]);
    var errorHandler;
    transfer.addRecipient(username, undefined);
    transfer.addFile(displayPath, blob, errorHandler);
}



var filelist = [].concat(argv._);
var expandedlist = [];

/**
 * If -R is enabled then expand any directory name given into the full
 * recursive list of files in that directory
 *
 * @return expandedlist contains the output from the input filelist.
 */
async function expandFileList( filename, filelist )
{
    if( !filename ) {
        return;
    }

    var isDir = fs.lstatSync(filename).isDirectory();
    if( !isDir ) {
        expandedlist.push( filename );
        expandFileList( filelist.pop(), filelist );
    } else {
        if( argv.recursive || argv.R ) {
            await recursive(filename).then(
                async function(files) {
                    files.forEach( (x) => { expandedlist.push( x ); } );
                    await expandFileList( filelist.pop(), filelist );
                },
                function(error) {
                    console.error("something bad happened", error);
                    process.exit(1);
                }
            );
        } else {
            console.log("WARNING: please use -R/--recursive if you wish to upload an entire directory");
            process.exit(1);
        }
    }
    
}





    
async function setupFiles( filelist ) {

    filelist.forEach( async function(filename) {
        addFile( filename );
    });
}



async function upload() {

    await expandFileList( filelist.pop(), filelist );
    
    setupFiles(expandedlist);
    let expiry = (new Date(Date.now() + expireInDays * 24 * 60 * 60 * 1000));
    transfer.expires = Math.floor(expiry.getTime()/1000);    
    transfer.options.get_a_link = true;

    transfer.oncomplete = function(transfer, time) {
        console.log("Your download link: '" + global.transfer.download_link + "'" );
    }

    transfer.start();
    
}


//console.log("key version new files:" +  window.filesender.config.encryption_key_version_new_files );

upload();

