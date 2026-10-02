
    
const { subtle } = require('crypto').webcrypto;
const { Blob } = require('buffer');
const { LocalStorage } = require("node-localstorage");
const { randombytes } = require('node-get-random-values');
var FileReader = require('filereader');
global.FileReader = FileReader;

const http = require('https');
const fs = require('fs');
const ini = require('ini');

var requireFromUrl = require('require-from-url/sync');
const XRegExp = require('xregexp');
global.XRegExp = XRegExp;

var XMLHttpRequest = require('xhr2');
global.XMLHttpRequest = XMLHttpRequest;

if (!('config' in global))
    global.config = {};


//get the users home directory
const home = process.env.HOME || process.env.USERPROFILE;
const user_config_file = fs.readFileSync(home + '/.filesender/filesender.py.ini', 'utf8');
const user_config = ini.parse(user_config_file);
const base_url = user_config['system']['base_url'].replace(/[/]rest.php$/,"");
const default_transfer_days_valid = user_config['system']['default_transfer_days_valid'];
const username = user_config['user']['username'];
const apikey = user_config['user']['apikey'];

const { JSDOM } = require( "jsdom" );
const { window } = new JSDOM( "", {url: base_url + "/?s=upload"} );
global.$ = global.jQuery = require( "jquery" )( window );
global.window = window;
console.log("loading configuration from " + base_url + "/filesender-config.js.php" );
var config = requireFromUrl(base_url + "/filesender-config.js.php");
global.config = window.filesender.config;


var enc = new TextEncoder();


require('../../../www/js/client.js');
require('../../../www/js/filesender.js');
require('../../../www/js/transfer.js');
require('../../../www/js/crypter/crypto_common.js');
require('../../../www/js/crypter/crypto_app.js');
require('../../../www/js/crypter/crypto_blob_reader.js');


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
global.window.filesender.ui.validators = {};
global.window.filesender.ui.validators.email = /^[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_\`\{|\}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z]{2,})$/i

//global.window.location = {}
global.window.location.href = base_url + "/?s=upload";

global.localStorage = new LocalStorage('/tmp/localstorage',1000000);
global.window.localStorage = global.localStorage;

global.filesender = window.filesender;
global.subtle = subtle;
global.crypto.subtle = subtle;

crypto.getRandomValues(new Uint8Array(32));



//create a new transfer
var transfer = new global.window.filesender.transfer()
global.window.filesender.client.from = username;
global.window.filesender.client.remote_user = username;
transfer.from = username;
global.transfer = transfer;
global.username = username;

//Turn on reader support for API transfers
global.window.filesender.supports.reader = true;
global.window.filesender.client.api_key = apikey;


transfer.encryption_key_version = filesender.config.encryption_key_version_new_files;


module.exports = function() { 
    this.transfer = transfer;
}

window.filesender.client.setupSSLOptions = function( settings ) {
    if( !user_config['system']['strictssl'] ) {
        if( settings.context._resourceLoader ) {
            settings.context._resourceLoader._strictSSL = false;
        }
    }
}
