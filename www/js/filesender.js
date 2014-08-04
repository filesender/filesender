/**
 * This file contains Filesender's REST client.
 * 
 * See : http://filesender.org
 */

window.filesender = {};

/**
 * AJAX webservice client
 */
window.filesenderclient = {
    // REST service base path
    base_path: null,
    
    // Send a request to the webservice
    call: function(method, resource, args, data, callback, options) {
        if(!this.base_path) {
            var path = window.location.pathname;
            path = path.split('/');
            path.pop();
            path = path.join('/');
            this.base_path = path + '/rest.php';
        }
        
        if(!args) args = {};
        args._ = (new Date()).getTime(); // Defeat cache
        var urlargs = [];
        for(var k in args) urlargs.push(k + '=' + args[k]);
        
        var settings = {
            cache: false,
            contentType: 'application/json;charset=',
            context: (options && options.context) ? options.context : window,
            data: data ? JSON.stringify(data) : undefined,
            dataType: 'json',
            error: (options && options.error) ? options.error : this.error,
            success: callback,
            type: method.toUpperCase(),
            url: this.base_path + resource + '?' + urlargs.join('&')
        };
        
        jQuery.ajax(settings);
    },
    
    // Error handler
    error: function(xhr, status, error) {
        var msg = xhr.responseText;
        
        alert('error : ' + msg);
    },
    
    get: function(resource, args, callback, options) {
        this.call('get', resource, undefined, undefined, callback, options);
    },

    post: function(resource, data, callback, options) {
        this.call('post', resource, undefined, data, function(data, status, xhr) {
            callback.call(this, xhr.getResponseHeader('Location'), data);
        }, options);
    },

    put: function(resource, data, callback, options) {
        this.call('put', resource, undefined, data, callback, options);
    },

    delete: function(resource, callback, options) {
        this.call('delete', resource, undefined, undefined, callback, options);
    }
};

/**
 * Get public info about the Filesender instance
 */
window.filesender.getInfo = function(callback) {
    this.client.get('/info', null, callback);
};

/**
 * Start a transfer
 * 
 * @param array files array of file objects with name, size and sha1 properties
 * @param array recipients array of recipients addresses
 * @param string subject optionnal subject
 * @param string message optionnal message
 * @param string expires expiry date (yyyy-mm-dd or unix timestamp)
 * @param array options array of selected option identifiers
 * @param callable callback function to call with transfer path and transfer info once done
 */
window.filesender.startTransfer = function(files, recipients, subject, message, expires, options, callback) {
    this.client.post('/transfer', {
        files: files,
        recipients: recipients,
        subject: subject,
        message: message,
        expires: expires,
        options: options
    }, callback);
};

