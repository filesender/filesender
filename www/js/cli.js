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
   });
});


require('./filesender-config.js');
require('./filesender.js');
require('./transfer.js');

//delete the config file
fs.unlinkSync("./filesender-config.js");

