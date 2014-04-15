var status = 'stopped';
var file = null;
var uri;
var func;
var wid;
var paused = false;

self.addEventListener('message' , function(e) {
    var data = e.data;
    
    if(!data.cmd)
        return;
    
    switch (data.cmd) {
        case 'setFile':
            file = data.file;
            func = (file.slice ? 'slice' : (file.mozSlice ? 'mozSlice' : (file.webkitSlice ? 'webkitSlice' : 'slice')));
            postMessage({
                'cmd':'log',
                'message':'File set on worker '+wid
            });
            break;
        case 'setUri':
            uri = data.uri;
            postMessage({
                'cmd':'log',
                'message':'Uri set on worker '+wid
            });
            break;
        case 'start':
            postMessage({
                'cmd':'log',
                'message':'Starting '+wid
            });
            // Message that we're ready
            postMessage({
                'cmd': 'ready',
                'hasFile': (file == "undefined")
            });
            break;
        case 'uploadChunk':
            postMessage({
                'cmd':'log',
                'message':wid+': start from '+data.startByte+' till '+data.endByte
            });
            uploadChunk(data.startByte,data.endByte);
            break;
        case 'setId':
            wid = data.id;
            break;
        case 'pause':
            paused = true;
            postMessage({
                'cmd':'log',
                'message':wid+': Will terminate after chunk'
            });

    }
});


function uploadChunk(startByte, endByte)
{   
    var uploaded = 0;
    
    var blob = file[func](startByte, endByte);
    
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = processReqChange;
    //xhr.onprogress = updateProgress;
    xhr.open("POST", uri, true); //Open a request to the web address set
    xhr.setRequestHeader("Content-Disposition"," attachment; name='fileToUpload'"); 
    xhr.setRequestHeader("Content-Type", "application/octet-stream");
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('X-Start-Byte', startByte);
    xhr.setRequestHeader('X-File-Size', file.size);
    //Set up the body of the POST data includes the name & file data.
      try
        {
             xhr.send(blob);
        }
        catch(err)
        {
            // error
             postMessage({
                    'cmd':'errormsg',
                    'message': 'Your source file is no longer available.\nYou will need to reload the page and re-select your file/s.'
                });
        }

    function updateProgress(e){
        postMessage({
            'cmd' : 'progress',
            'uploaded': (e.loaded - uploaded)
        });
        uploaded = e.loaded;
    }

    function processReqChange(){
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                if(xhr.responseText == "ErrorAuth")
                {
                    postMessage({
                        'cmd': 'autherror'
                    });
                    return;			
                }
                postMessage({
                   'cmd': 'progress',
                   'uploaded': endByte - startByte
                });
                postMessage({
                    'cmd':'ready'
                });
                if(paused){
                    postMessage({
                        'cmd':'log',
                        'message':wid+': Paused'
                    });

                    postMessage({
                        'cmd':'paused'
                    });
                    self.close();
                }
            } else {
                postMessage({
                    'cmd':'error',
                    'message': 'There was a problem retrieving the data:\n' + xhr.statusText
                });
            }
        }
    }
}