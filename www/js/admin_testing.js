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
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
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

if (typeof window === 'undefined')
    window = {}; // dummy window for use in webworkers

if (!('filesender' in window))
    window.filesender = {};



var getPBKDF2IterationCountForYear = function( wantedyear )
{
    var baseyear=2009;
    var nbase=1000;
    return Math.ceil(nbase * Math.pow(2.0, ( (wantedyear - baseyear)*2.0/3)));
}


$(function() {
    var section = $('#page.admin_page .testing_section');
    if(!section.length) return;

    var crypto = window.filesender.crypto_app();

    
    var hashperftable   = section.find('.password-hashing-performance');
    var cryptoperftable = section.find('.crypto-performance');
    var pbkdf2table     = section.find('.pbkdf2-performance');
    
    section.find('[data-action="show-password-hashing-performance"]').on('click', function() {

        var key_version = window.filesender.config.encryption_key_version_new_files;
        var currentSetting  = filesender.config.encryption_password_hash_iterations_new_files;
        var hash_iterations = [currentSetting,10000,100000,500000,1000000,5000000,16384000];
        var iv = crypto.decodeCryptoFileIV(crypto.generateCryptoFileIV(),key_version);
        var file = { size: 100, iv: iv };
        hash_iterations.map( function( n ) {
            var encryption_details = {
                password: 'abcdef',
                key_version: filesender.config.encryption_key_version_new_files,
                salt: '123456',
                password_version: crypto.crypto_password_version_constants.v2018_text_password,
                password_encoding: 'none',
                password_hash_iterations: n,
                fileiv: iv,
                aead: crypto.encodeAEAD( crypto.generateAEAD( file ))
            };

            var chunkid = 0;
            var t0 = performance.now();
            crypto.generateKey(chunkid,encryption_details, function (key, iv) {
                var t1 = performance.now();
                var l = hashperftable.find('.tpl').clone().removeClass('tpl').addClass('benchmark');
                l.find('.rounds').text(Number(n).toLocaleString());
                l.find('.milliseconds').text(Number(t1-t0).toLocaleString());
                if( n==currentSetting ) {
                    l.find('.active').text("1");
                    l.find('.rounds').addClass('active');
                    l.find('.milliseconds').addClass('active');
                }
                l.appendTo(hashperftable);
            });
            return n;
        })
    });
    

    section.find('[data-action="show-chunk-crypto-performance"]').on('click', function() {
        var cfg  = window.filesender.config;
        var datastr = 'a'.repeat(cfg.upload_chunk_size);
        let utf8enc = new TextEncoder();
        let utf8dec = new TextDecoder();
        var data = utf8enc.encode(datastr);
        
        var encryption_details = {
            password:          'abcdef',
            password_encoding: 'none',
            password_version:  crypto.crypto_password_version_constants.v2018_text_password,
            key_version:       crypto.crypto_key_version_constants.v2018_importKey_deriveKey,
            salt:              '123456789',
            password_hash_iterations: cfg.encryption_password_hash_iterations_new_files
        };

        var chunkid = 0;
        var t0 = performance.now();            
        crypto.encryptBlob( data, chunkid, encryption_details, function(dataenc) {
            var t1 = performance.now();

            var l = cryptoperftable.find('.tpl').clone().removeClass('tpl').addClass('benchmark');
            l.find('.action').text('encrypt');
            l.find('.milliseconds').text(Number(t1-t0).toLocaleString());
            l.appendTo(cryptoperftable);

            var dataencab = window.filesender.crypto_common().convertStringToArrayBufferView( dataenc);
            dataencab = window.filesender.crypto_blob_reader().sliceForDownloadBuffers(dataencab);
            t0 = performance.now();

            // getting the right combination of slice/splits above is still a TODO
//            console.log(dataencab);
            // crypto.decryptBlob( dataencab, encryption_details, function(roundtrip) {

            //     alert('decr!');
            //     var t1 = performance.now();
            //     var l = cryptoperftable.find('.tpl').clone().removeClass('tpl').addClass('benchmark');
            //     l.find('.action').text('decrypt');
            //     l.find('.milliseconds').text(Number(t1-t0).toLocaleString());
            //     l.appendTo(cryptoperftable);
            // }, function (progress,len) {}, function(err) {alert('err ' + err );} );
        });
    });



    section.find('[data-action="show-pbkdf2-crypto-performance"]').on('click', function() {
        var cfg  = window.filesender.config;
        var datastr = 'a'.repeat(2);
        var salt = 'rwefedfsdfdsfsdfsd';
        var saltBuffer     = window.filesender.crypto_common().convertStringToArrayBufferView(salt);
        var password       = 'aa';
        var password_version = 1;
        var password_encoding = '';
        var decoded        = crypto.decodePassword( password, password_version, password_encoding );
        var passwordBuffer = decoded.raw;

        var currentSetting  = 2009;
        var hash_years = [ 2010,2011,2012,2013,2014,2015,2016,2017,2018,2019,
                           2020,2022,2023,2024,2025,2026,2027,2028,2029,2030 ];

        var efunc = function (e) {
            alert(e);
        };

        
        hash_years.map( function( n ) {

            var hashRounds = getPBKDF2IterationCountForYear( n );
            var t0 = performance.now();
                window.crypto.subtle.importKey(
                    'raw', 
                    passwordBuffer,
                    {name: 'PBKDF2'}, 
                    false, 
                    ['deriveBits', 'deriveKey']
                ).then(function(dkey) {

                    window.crypto.subtle.deriveKey(
                        { "name": 'PBKDF2',
                          "hash": 'SHA-256',
                          "iterations": hashRounds,
                          "salt":       saltBuffer,
                        },
                        dkey,
                        { "name":   'AES-CBC',
                          "length": 256
                        },
                        false,                   // key is not extractable
                        [ "encrypt", "decrypt" ] // features desired
                    ).then(function (key) {
                        
                        var t1 = performance.now();

                        var l = pbkdf2table.find('.tpl').clone().removeClass('tpl').addClass('benchmark');
                        l.find('.year').text( n );
                        l.find('.iterations').text( hashRounds );
                        l.find('.seconds').text(Number(Math.ceil((t1-t0)/1000)).toLocaleString());
                        if( n==currentSetting ) {
                            l.find('.year').addClass('active');
                            l.find('.iterations').addClass('active');
                            l.find('.seconds').addClass('active');
                        }
                        
                        l.appendTo(pbkdf2table);
                        
                    }, efunc );
                }, efunc );

            return n;
            
        });
    });

    var callbackError = function (error) {
        window.filesender.log(error);
        window.filesender.ui.error(error);
    };

    section.find('[data-action="generate-test-ss"]').on('click', function() {

        const ponyfill = window.WebStreamsPolyfill || {};
        streamSaver.WritableStream = ponyfill.WritableStream;
        streamSaver.mitm = window.filesender.config.streamsaver_mitm_url;
        streamSaver.WritableStream = ponyfill.WritableStream;
        
        var filename = 'filesendertest.txt';
        streamSaver.mitm = window.filesender.config.streamsaver_mitm_url;
        var fileStream = streamSaver.createWriteStream( filename );
        var writer = fileStream.getWriter();

        const blob = new Blob(['This file was generated with FileSender using StreamSaver.']);
        const readableStream = new Response( blob ).body;

        const reader = readableStream.getReader();
        const pump = () => reader.read()
              .then(res => res.done
                    ? writer.close()
                    : writer.write(res.value).then(pump));
        
        pump();
    });

    
    section.find('[data-action="generate-test-zip64"]').on('click', function() {

        var file1contents = 'abcdef';
        
        var zip = window.filesender.zip64handler();
        zip.init('filesendertestzip.zip');
        zip.openFile( 'abcdef.txt' );
        zip.visit( window.filesender.crypto_common().convertStringToArrayBufferView(file1contents));
        zip.closeFile();

        zip.openFile( 'z123.txt' );
        zip.visit( window.filesender.crypto_common().convertStringToArrayBufferView('z1'));
        zip.visit( window.filesender.crypto_common().convertStringToArrayBufferView('23'));
        zip.closeFile();
        
        zip.complete();
    });

    /////////////////////////////////
    /////////////////////////////////
    /////////////////////////////////
    /////////////////////////////////
    
    section.find('[data-action="show-bs-alert-success"]').on('click', function() {
        filesender.ui.alert('success', 'this is the main part of the message');
    });

    
    section.find('[data-action="show-bs-test"]').on('click', function() {

        filesender.ui.alert('success', 'this is the main part of the message');
    });

    section.find('[data-action="show-bs-test-bootbox"]').on('click', function() {

        if( false ) {
            filesender.ui.alert('success', 'this is the main part of the message');
            bootbox.alert({
                title: 'hi there',
                message: 'this is the main part of the message',
                centerVertical: true
            });
        }

        var onClose = function() {
        };
        var p = filesender.ui.alert('success', lang.tr('done_uploading'), onClose);
        
        var t = null;
        var dl = $('<div class="download_link" />').text(lang.tr('download_link') + ' :').appendTo(p);
        t = $('<textarea class="wide" readonly="readonly" />').appendTo(dl);
        t.val('http://127.0.0.1/not-a-link').focus().select();
        if(t) t.on('click', function() {
            $(this).focus().select();
        });


        var c = lang.tr('done_uploading') + '<br/>';
        c += '<div class="download_link" />' + lang.tr('download_link') + ' :' + '<br/>';
        c += '<textarea class="wide" readonly="readonly" />';
        c += 'http://127.0.0.1/not-a-link';

        // bootbox.alert({
        //     title: 'Success',
        //     message: c,
        //     className: 'warning-dialog',
        //     centerVertical: true
        // });

        filesender.ui.alertbs('error', lang.tr('fdfdfdfd'), function() {});

        
    });

    var onClose = function() {
        console.log('show-bs-test-alertbs onclose...');
    };
    
    section.find('[data-action="show-bs-test-alertbs"]').on('click', function() {

        var popup = filesender.ui.alertbs('success', 'this is the main part of the message', onClose );
        $('<p>').text('hi there').appendTo(popup.find('.bootbox-body'));

    });
       

    section.find('[data-action="show-bs-maint1"]').on('click', function() {

        filesender.ui.maintenance(true);

        setTimeout(function () {
            filesender.ui.maintenance(false);
        }, 2000 );
        
    });
    section.find('[data-action="show-bs-maint2"]').on('click', function() {
        filesender.ui.maintenance(false);
    });

    section.find('[data-action="show-bs-test-error"]').on('click', function() {


        filesender.ui.confirm('this is the test error', function() {}, function() {} );

        var buttons = {
            extend: {
                callback: function() {
                    console.log('extend');
                }
            }
        }
        buttons.extend_and_remind = {
            callback: function() {
                console.log('extend 2');
            }
        };
        buttons.cancel = {};
        

        // filesender.ui.dialogWithButtons( 'confirm_dialog', 'confirm',
        //                                  lang.tr('confirm_extend_expiry').r({days: 30}).out(),
        //                                  buttons );
        
        

        // filesender.ui.confirmTitle(lang.tr('authentication_required'),
        //                            lang.tr('authentication_required_explanation'),
        //                            function() {
        //                                console.log("onclose ..." );
        //                            });
                           

        
        // var authentication_required = filesender.ui.popup(
        //                 lang.tr('authentication_required'),
        //                 filesender.config.logon_url ? {
        //                     logon: function() {
        //                         filesender.ui.redirect(filesender.config.logon_url);
        //                     }
        //                 } : {
        //                     ok: function() {}
        //                 },
        //                 {noclose: true}
        //             );
        // authentication_required.text(lang.tr('authentication_required_explanation'));
        

//        filesender.ui.confirmbs(lang.tr('confirm_close_transfer'), function() { alert('yes'); }, function() { alert('no'); });
        
        // bootbox.confirm({
        //     message: "This is a confirm with custom button text and color! Do you like it?",
        //     buttons: {
        //         confirm: {
        //             label: 'Yes',
        //             className: 'btn-success'
        //         },
        //         cancel: {
        //             label: 'No',
        //             className: 'btn-danger'
        //         }
        //     },
        //     callback: function (result) {
        //         console.log('This was logged in the callback: ' + result);
        //     }
        // });
        
    });
    

});
