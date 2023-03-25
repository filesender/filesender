// Command line interface to be run through node.js


const http = require('https'); //used to download the config file
const fs = require('fs'); //used to save the config file

//Base url of the filesender instance we are connecting to
let base_url = 'https://cloudstor.aarnet.edu.au/sender'

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

        //add some required functions
        window.filesender.ui = {};
        window.filesender.ui.error = function(error,callback) {
            console.log('[error] ' + error.message);
            console.log(error);
        }
        window.filesender.ui.log = function(message) {
            console.log('[log] ' + message);
        }
        window.filesender.ui.validators = {};
        window.filesender.ui.validators.email = /^[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z]{2,})$/i

       
       
        //create a new transfer
        var transfer = new window.filesender.transfer()

        //add a file to the transfer
        const blob = new Blob(['This file was generated as a test.']);
        transfer.addFile('test.txt', blob, undefined);

        //set the recipient
        transfer.addRecipient('joey@joeyn.dev', undefined);
    


   });
});




