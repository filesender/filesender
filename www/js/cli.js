// Command line interface to be run through node.js


const http = require('https'); //used to download the config file
const fs = require('fs'); //used to save the config file

//Base url of the filesender instance we are connecting to
let base_url = 'https://cloudstor.aarnet.edu.au/sender'

const { JSDOM } = require( "jsdom" );
const { window } = new JSDOM( "", {url: base_url + "/?s=upload"} );
global.$ = global.jQuery = require( "jquery" )( window );

// Set up the global window object
global.window = global;

//get the config file
console.log("Downloading config...");
const file = fs.createWriteStream("filesender-config.js");
const request = http.get(base_url+"/filesender-config.js.php", function(response) {
   response.pipe(file);

   // after download completed close filestream
   file.on("finish", () => {
        file.close();
        console.log("Config downloaded");

        //get all the required files
        XRegExp = require('../lib/xregexp/xregexp-all.js');
        require('./filesender-config.js');
        require('./client.js');
        require('./filesender.js');
        require('./transfer.js');
        require('./lang.js');
        

        //add some required functions
        global.window.filesender.ui = {};
        global.window.filesender.ui.error = function(error,callback) {
            console.log('[error] ' + error.message);
            console.log(error);
        }
        global.window.filesender.ui.rawError = function(text) {
            console.log('[raw error] ' + text);
        }
        global.window.filesender.ui.log = function(message) {
            console.log('[log] ' + message);
        }
        global.window.filesender.ui.popup = function(message, buttons, options) {
            console.log('[popup] ' + message);
            return {
                text: function(text) {
                    console.log('[popup text] ' + text);
                }
            }
        }
        filesender.client.authentication_required.text
        global.window.filesender.ui.validators = {};
        global.window.filesender.ui.validators.email = /^[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z]{2,})$/i

        global.window.location = {}
        global.window.location.href = base_url + "/?s=upload";
        
        
        //create a new transfer
        var transfer = new global.window.filesender.transfer()

        //add a file to the transfer
        const blob = new Blob(['This file was generated as a test.']);
        transfer.addFile('test.txt', blob, undefined);

        //set the recipient
        transfer.addRecipient('someone@example.com', undefined);
    
        //set the expiry date for 7 days in the future
        let expiry = (new Date(Date.now() + 7 * 24 * 60 * 60 * 1000));
        //format as a string in the yyyy-mm-dd format
        transfer.expires = expiry.toISOString().split('T')[0];

        //set the security token
        global.window.filesender.client.security_token = "security token here";

        //start the transfer
        transfer.start();

   });
});




