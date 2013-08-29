// JavaScript Document

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS'
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

// HTML5 Upload functions
// when cancelling an upload we need to wait till the chunk is complete before allowing the cancel to happen
// setting cancel upload to true will trigger the upload to stop before uploading the next chunk
// JavaScript Document


// -----------------------------------------------------------------------------
// Globals
// Major version of Flash required
var requiredMajorVersion = 10;
// Minor version of Flash required
var requiredMinorVersion = 0;
// Minor version of Flash required
var requiredRevision = 0;
// -----------------------------------------------------------------------------
// -->

//var bytesUploaded = 0;
//var bytesTotal = 0;
//var previousBytesLoaded = 0;
var intervalTimer = 0;
//var currentlocation = 0;
//var filename = '';
//var chunksize = 2000000;
var uploadURI = 'fs_multi_upload.php';
var fileData = []; // array of each file to be uploaded
var n = -1; // file int currently uploading

var tsunami;

// a unique is created for each file that is uploaded.
// An object with the unique stores all relevant information about the file upload

// Used for aggregate upload bar
var totalFileLengths = 0;
var totalBytesLoaded = 0;
var percentageComplete = 0;

// Used for calculating average upload speed in updateProgressBar
var initialStartTime = 0;
var pauseTime;
var resumeTime;
var timeSpentPaused =0;

// Used for undoing a clear-all call
var filesToRestore;

var pausedUpload = false;
var vid = '';

function browse()
{
    $('#fileToUpload').click();
}

function fileSelected()
{
    // multiple files selected
    // loop through all files and show their values
    if (document.readyState != 'complete' && document.readyState != 'interactive') {
        return;
    }

    var files = document.getElementById('fileToUpload').files;
    if (typeof files !== 'undefined') {
        addFiles(files);
    }
}

// Loops through an array of files and adds them to the upload queue & creates file-boxes for them
// Used in multiupload.php as well
function addFiles(files)
{
    for (var i = 0; i < files.length; i++) {
        var dupFound = false;

        if (n >= maxUploads) {
            openErrorDialog('You have reached the max number of uploads of: ' + maxUploads);
            return;
        }

        // Loops through list of files already in the list and prevents any duplicates from being created
        for (var j = 0; j < fileData.length; j++) {
            if (fileData[j].filename == files.item(i).name) {
                dupFound = true;
                break;
            }
        }

        if (totalFileLengths + files.item(i).size > maxHTML5UploadSize) {
            openErrorDialog(errmsg_disk_space);
            continue;
        }

        if (!dupFound) {
            n = n + 1;

            fileData[n] = new Array(n);
            fileData[n].filegroupid = groupID;
            fileData[n].filetrackingcode = trackingCode;
            fileData[n].file = files[i];
            fileData[n].fileSize = fileData[n].file.size;
            fileData[n].bytesTotal = fileData[n].file.size;
            fileData[n].bytesUploaded = 0;
            fileData[n].previousBytesLoaded = 0;
            fileData[n].intervalTimer = 0;
            fileData[n].currentlocation = 0;
            fileData[n].filename = fileData[n].file.name;
            fileData[n].name = fileData[n].file.name;
            fileData[n].filetype = fileData[n].file.type;
            fileData[n].valid = false; // assume invalid until checked
            fileData[n].status = true; // to allow removal of file from upload list

            // Update total file lengths to cater for the new file
            totalFileLengths += fileData[n].fileSize;
            // Give each file its own file box + info
            $('#filestoupload').append(generateFileBoxHtml());
            updateBoxStats();
        }
    }

    // enable the use of the 'Clear all' button only if there is something to clear
    if (n > -1) {
        $('#fileInfoView').show();
        $('#clearallbtn').button('enable');
        $('#draganddropmsg').hide();
        $('#uploadbutton').show();
        setButtonToClear();
        filesToRestore = "";
    }

}

// Part of the undo clear button, re-adds files which may have been cleared accidentally.
function reAddFiles(files)
{
    for (var i = 0; i < files.length; i++) {
        if(files[i].filename == undefined) continue;

        n = n + 1;
        fileData[n] = new Array(n);
        fileData[n].filegroupid = files[i].filegroupid;
        fileData[n].filetrackingcode = files[i].filetrackingcode;
        fileData[n].file = files[i].file;
        fileData[n].fileSize = files[i].fileSize;
        fileData[n].bytesTotal = files[i].bytesTotal;
        fileData[n].bytesUploaded = 0;
        fileData[n].previousBytesLoaded = 0;
        fileData[n].intervalTimer = 0;
        fileData[n].currentlocation = 0;
        fileData[n].filename = files[i].filename;
        fileData[n].name = files[i].name;
        fileData[n].filetype = files[i].filetype;
        fileData[n].valid = false; // assume invalid until checked
        fileData[n].status = true; // to allow removal of file from upload list

        // Update total file lengths to cater for the new file
        totalFileLengths += fileData[n].fileSize;
        // Give each file its own file box + info
        $('#filestoupload').append(generateFileBoxHtml());
        updateBoxStats();
    }

    if (n > -1) {
        $('#fileInfoView').show();
        $('#clearallbtn').button('enable');
        $('#draganddropmsg').hide();
        $('#uploadbutton').show();
    }
}

function generateFileBoxHtml()
{
    var validfile = '';
    if (validate_file(n)) {
        fileData[n].valid = true;
    } else {
        validfile = '<img style="float:left;padding-right:6px;" src="images/information.png" border=0 title="This file is invalid and will not be uploaded"/>';
    }

    var file_info = validfile + ' ' + fileData[n].filename + ' : ' + readablizebytes(fileData[n].fileSize);

    return '<div id="file_' + n + '" class="fileBox valid' + fileData[n].valid + '">' +
        '<span class="filebox_string" title="' + file_info + '">' + file_info + '</span>' +
        '<span class="delbtn" id="file_del_' + n + '" onclick="removeItem(' + n + ');">' +
        '<img src="images/delete.png" width="16" height="16" border="0" align="absmiddle" style="cursor:pointer"/>' +
        '</span>' +
        '<div class="progress_bar" id="progress_bar-' + n + '"></div>' +
        '</div>';
}

function startUpload()
{
    // check if file is validated before uploading
    if (validate_file(n) && fileData[n].status) {
        $('#file_del_' + n).hide();
        fileData[n].bytesUploaded = 0;
        fileData[n].bytesTotal = fileData[n].fileSize;
        // validate form data and return filesize or validation error
        // load form into json array
        var query = $('#form1').serializeArray(), json = {};

        for (var i in query) {
            json[query[i].name] = query[i].value;
        }

        // add file information fields
        json['fileoriginalname'] = fileData[n].filename;
        json['filesize'] = parseInt(fileData[n].fileSize);
        json['vid'] = fileData[n].vid;
        json['filegroupid'] = fileData[n].filegroupid;
        json['filetrackingcode'] = fileData[n].filetrackingcode;
        json['fileto'] = $('#fileto').val();
        json['filevoucheruid'] = fileData[n].filevoucheruid;


        var firstFile = '';
        if (n == 0 && !pausedUpload) {
            firstFile = '&firstfile=true';
            initialStartTime = new Date().getTime();
        }

        $.ajax({
            type: 'POST',
            url: uploadURI + '?type=validateupload&vid=' + vid + '&n=' + n + firstFile,
            data: {myJson: JSON.stringify(json)}
        }).success(function (data) {
            if (data == '') {
                alert('No response from server');
                return;
            }

            if (data == 'ErrorAuth') {
                $('#dialog-autherror').dialog('open');
                return;
            }

            var data = JSON.parse(data);

            if (data.errors) {
                $.each(data.errors, function (i, result) {
                    // token missing or error
                    if (result == 'err_token') {
                        $('#dialog-tokenerror').dialog('open');
                    }
                    // not authenticated
                    if (result == 'err_notauthenticated') {
                        $('#dialog-autherror').dialog('open');
                    }
                    // missing email data
                    if (result == 'err_tomissing') {
                        $('#fileto_msg').show();
                    }
                    // missing expiry date
                    if (result == 'err_expmissing') {
                        $('#expiry_msg').show();
                    }
                    // expiry date out of range
                    if (result == 'err_exoutofrange') {
                        $('#expiry_msg').show();
                    }
                    // 1 or more emails invalid
                    if (result == 'err_invalidemail') {
                        $('#fileto_msg').show();
                    }
                    // invalid filename
                    if (result == 'err_invalidfilename') {
                        $('#file_msg').show();
                    }
                    //  invalid extension
                    if (result == 'err_invalidextension') {
                        $('#extension_msg').show();
                    }
                    // not enough disk space on server
                    if (result == 'err_nodiskspace') {
                        openErrorDialog(errmsg_disk_space);
                    }
                });
                $('#uploadbutton').find('a').attr('onclick', 'validate()');
            }
            if (data.status && data.status == 'complete') {
                $('#fileToUpload').hide();// hide Browse
                $('#selectfile').hide();// hide Browse message
                $('#uploadbutton').hide(); // hide upload
                $('#cancelbutton').show(); // show cancel
                // no error so use result as current bytes uploaded for file resume
                vid = data.vid;
                fileData[n].bytesUploaded = parseFloat(data.filesize);

                if (!pausedUpload) {
                    updateProgressBar(fileData[n].bytesUploaded, fileData[n].bytesTotal, fileData[n].bytesUploaded);
                } else {
                    pausedUpload = false;
                }

                // validated so upload all files

                startTime = new Date().getTime();

                if (html5webworkers) {
                    uploadFileWebworkers();
                } else {
                    uploadFile();
                }
            }
        });
    }
}

function doUploadComplete()
{
    var end = new Date().getTime();
    var time = end - startTime;
    var speed = fileData[n].bytesTotal / (time / 1000) / 1024 / 1024 * 8;

    console.log('Upload time:' + (time / 1000) + 'sec');
    console.log('Speed: ' + speed.toFixed(2) + 'Mbit/s');

    var moreFiles = n < fileData.length - 1 ? '&morefiles=true' : '';

    var query = $('#form1').serializeArray(), json = {};

    for (var i in query) {
        json[query[i].name] = query[i].value;
    }

    json['email-inform-download'] = $('#email-inform-download').prop('checked');
    json['email-enable-confirmation'] = $('#email-enable-confirmation').prop('checked');
    json['email-inform-daily'] = $('#email-inform-daily').prop('checked');
    $.ajax({
        type: 'POST',
        url: uploadURI + '?type=uploadcomplete&vid=' + vid + '&n=' + n + moreFiles,
        data: {myJson: JSON.stringify(json)},
        success: function (data) {

            var data = JSON.parse(data);
            if (data.errors) {
                $.each(data.errors, function (i, result) {
                    switch(result) {
                        case 'err_token':
                            $('#dialog-tokenerror').dialog('open');
                            break;
                        case 'err_cannotrenamefile':
                            window.location.href = 'index.php?s=uploaderror';
                            break;
                        case 'err_emailnotsent':
                            window.location.href = 'index.php?s=emailsenterror';
                            break;
                        case 'err_filesizeincorrect':
                            window.location.href = 'index.php?s=filesizeincorrect';
                            break;
                    }
                });
            } else if (n < fileData.length - 1) {
                n += 1;
                startUpload();
            } else {
                transactionComplete(data['gid']);
            }
        }, error: function (xhr, err) {
            // error function to display error message e.g.404 page not found
            ajaxerror(xhr.readyState, xhr.status, xhr.responseText);
        }
    });
}

function transactionComplete(gid)
{
    var query = $('#form1').serializeArray(), json = {};

    for (var i in query) {
        json[query[i].name] = query[i].value;
    }

    json['rtnemail'] = $('#rtnemail').prop('checked');
    json['email-upload-complete'] = $('#email-upload-complete').prop('checked');

    $.ajax({
        type: 'POST',
        url: uploadURI + '?type=transactioncomplete&gid=' + gid,
        data: {myJson: JSON.stringify(json)},
        success: function (data) {
            clearForm();
            var data = JSON.parse(data);
            console.log("Transaction complete successful");
            window.location.href = 'index.php?s=complete&gid=' + data['gid'] +
                '&start=' + Math.floor(initialStartTime / 1000) +
                '&end=' + Math.floor(new Date().getTime() / 1000) +
                '&timepaused=' + Math.floor(timeSpentPaused / 1000);
        }, error: function (xhr, err) {
            ajaxerror(xhr.readyState, xhr.status, xhr.responseText);
        }
    });
}

function clearForm()
{
    clearFileBox();
    $("#fileto").val("");
    $("#filesubject").val("");
    $("#filemessage").val("");
}

function updateBoxStats()
{
    var numFiles = n + 1;
    if (n >= 0) {
        $('#uploadBoxStats').html('Number of Files: ' + numFiles + '/' + maxUploads + '<br /> Size: ' + readablizebytes(totalFileLengths) + '/' + readablizebytes(maxHTML5UploadSize));

    } else {
        $('#uploadBoxStats').html('');
    }
}

function getFiles()
{
    var files = [];

    for (var i = n; i < fileData.length; i++) {
        files.push(fileData[i].file);
    }

    return files;
}

function uploadFileWebworkers()
{
    var files = getFiles();

    //var files = document.getElementById('fileToUpload').files;
    var path = document.location.pathname;
    var dir = path.substring(0, path.lastIndexOf('/'));

    $('head').append('<script type="text/javascript" src="lib/tsunami/js/tsunami.js"></script>');

    if (fileData[n].bytesUploaded > fileData[n].bytesTotal - 1) {
        doUploadComplete();
        return;
    }

    chunksize = parseInt($('#chunksize').val()) * 1024 * 1024;
    console.log('Chunksize: ' + chunksize);

    workerCount = parseInt($('#workerCount').val());

    console.log('Using ' + workerCount + ' worker(s)');
    jobsPerWorker = parseInt($('#jobsPerWorker').val());
    console.log('Setting ' + jobsPerWorker + ' job(s) per worker');

    tsunami = new Tsunami({
        uri: dir + '/' + uploadURI + '?type=tsunami&vid=' + vid + '&n=' + n,
        simultaneousUploads: workerCount,
        jobsPerWorker: jobsPerWorker,
        chunkSize: chunksize,
        workerFile: 'lib/tsunami/js/tsunami_worker.js',
        log: true,
        onComplete: doUploadComplete,
        onProgress: updateProgressBar
    });
    tsunami.addFiles(files);
    tsunami.upload();
}

function uploadFile()
{

    // move to next chunk
    var file = fileData[n].file;
    var transferSize = chunksize;

    if (fileData[n].bytesUploaded > fileData[n].bytesTotal - 1) {
        // COMPLETE THIS ONE
        var query = $('#form1').serializeArray(), json = {};
        $.ajax({
            type: 'POST',
            url: uploadURI + '?type=uploadcomplete&vid=' + vid + '&n=' + n + '&rtnemail=' + $('#rtnemail').prop('checked')
        }).success(function (data) {
                if (data == 'err_cannotrenamefile') {
                    window.location.href = 'index.php?s=uploaderror';
                    return;
                } else if (data == 'err_filesizeincorrect') {
                    window.location.href = 'index.php?s=filesizeincorrect';
                    return;
                }
                // IF MORE FILES NEED UPLOADING THEN
                if (n < fileData.length - 1) {
                    n += 1;
                    startUpload();
                } else {
                    window.location.href = 'index.php?s=complete&gid='+data['gid'];
                }
            });
        return;
    }

    // Lowers the transfer size to amount left to download if there is less than a chunks worth to go
    if (fileData[n].bytesUploaded + transferSize > fileData[n].fileSize) {
        transferSize = fileData[n].fileSize - fileData[n].bytesUploaded;
    }

    var blob;
    // check if firefox or Chrome slice supported, otherwise use standard version
    if (file && file.webkitSlice) {
        blob = file.webkitSlice(fileData[n].bytesUploaded, transferSize + fileData[n].bytesUploaded);
    } else if (file && file.mozSlice) {
        blob = file.mozSlice(fileData[n].bytesUploaded, transferSize + fileData[n].bytesUploaded);
    } else {
        blob = file.slice(fileData[n].bytesUploaded, transferSize + fileData[n].bytesUploaded);
    }

    var boundary = 'fileboundary'; //Boundary name
    var uri = (uploadURI + '?type=chunk&vid=' + vid + '&n=' + n); //Path to script for handling the file sent
    var xhr = new XMLHttpRequest(); //Create the object to handle async requests
    xhr.onreadystatechange = processReqChange;
    xhr.upload.addEventListener('progress', uploadProgress, false);
    xhr.open('POST', uri, true); //Open a request to the web address set
    xhr.setRequestHeader('Content-Disposition', " attachment; name='fileToUpload'");
    xhr.setRequestHeader('Content-Type', 'application/octet-stream');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    //Set up the body of the POST data includes the name & file data.
    xhr.send(blob);

    function processReqChange() {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                if (xhr.responseText == 'ErrorAuth') {
                    $('#dialog-autherror').dialog('open');
                    return;
                }
                fileData[n].bytesUploaded = parseFloat(xhr.responseText);
                updateProgressBar(fileData[n].bytesUploaded, fileData[n].bytesTotal, 0);
                uploadFile();
            } else {
                openErrorDialog('There was a problem retrieving the data:\n' + req.statusText);
            }
        }
    }
    return true;
}

// TODO: Can go?
function updateTransferSpeed() {
    var currentBytes = bytesUploaded + (chunksize * (chunk_id - 1));
    var bytesDiff = currentBytes - chunksize * (chunk_id - 1);//previousBytesLoaded;
    if (bytesDiff == 0) return;
    previousBytesLoaded = currentBytes;
    bytesDiff = bytesDiff * 2;
    var bytesRemaining = bytesTotal - previousBytesLoaded;
    var secondsRemaining = bytesRemaining / bytesDiff;
    var speed = '';
    if (bytesDiff > 1024 * 1024)
        speed = (Math.round(bytesDiff * 100 / (1024 * 1024)) / 100).toString() + 'MBps';
    else if (bytesDiff > 1024)
        speed = (Math.round(bytesDiff * 100 / 1024) / 100).toString() + 'kBps';
    else
        speed = bytesDiff.toString() + 'Bps';
    $('#transferSpeedInfo').html(speed);
}

// TODO: can go?
function uploadProgress(evt) {
}

// TODO: can go?
function uploadFailed(evt) {
    clearInterval(intervalTimer);
    openErrorDialog('An error occurred while uploading the file.');
}

// TODO: can go?
function uploadCanceled(evt) {
    clearInterval(intervalTimer);
    erorDialog('The upload has been canceled by the user or the browser dropped the connection.');
}

// remove file from upload array
function removeItem(fileID)
{
    // Updates the combined file lengths
    totalFileLengths -= fileData[fileID].fileSize;
    $('#file_' + fileID).remove();
    fileData[fileID] = [];
    //fileData[fileID].status = false;
    //fileData.splice(fileID, 1);
    n--;
    if (n < 0) {
        $('#fileToUpload').val(''); // Needed to allow reselection of files.
        $('#draganddropmsg').show();
        $('#clearallbtn').button('disable');
    }
    updateBoxStats();
}

// clears the contents of the files-to-upload
function clearFileBox()
{
    if(n == -1) return;
    filesToRestore = fileData.slice();
    var temp = fileData.length;
    for (var i = 0; i < fileData.length; i++) {
        if (fileData[i].fileSize != null){
            removeItem(i);
        }
    }
    // value of n should be this if the box is empty.
    n = -1;
    fileData = [];
    setButtonToUndo();
    updateBoxStats();
}

function undoClearFileBox()
{
    reAddFiles(filesToRestore);
    setButtonToClear();
}


// Functions which change the onclick event on the button based on the previous click event.
function setButtonToClear()
{
    var clearAll = $('#clearallbtn');
    clearAll.find('.ui-button-text').html("Clear all");
    clearAll.attr('onclick', 'clearFileBox()');
}

function setButtonToUndo()
{
    var clearAll = $('#clearallbtn');
    clearAll.button('enable');
    clearAll.find('.ui-button-text').html("Undo clear");
    clearAll.attr('onclick', 'undoClearFileBox()');
}

function pauseUpload()
{
    tsunami.pauseUpload();
    $('.progress_bar').css('background-color', '#FF8800');
    $('#progress_string').html('Pausing...');
    vid = fileData[n].filevoucheruid;
    pausedUpload = true;

}

function resumeUpload() {
    $('#progress_string').html(percentComplete + '%');
    startUpload();
    $('.progress_bar').css('background-color', '#5c5');
    resumeTime = new Date().getTime();
    timeSpentPaused += resumeTime - pauseTime;
}

function uploadPaused()
{
    $('#progress_string').html('Paused');
    pauseTime = new Date().getTime();
}
