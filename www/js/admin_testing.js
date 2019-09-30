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

$(function() {
    var section = $('#page.admin_page .testing_section');
    if(!section.length) return;

    var crypto = window.filesender.crypto_app();

    
    var hashperftable   = section.find('.password-hashing-performance');
    var cryptoperftable = section.find('.crypto-performance');
    
    section.find('[data-action="show-password-hashing-performance"]').on('click', function() {

        var currentSetting  = filesender.config.encryption_password_hash_iterations_new_files;
        var hash_iterations = [currentSetting,10000,100000,500000,1000000,5000000,16384000];
        var iv = crypto.decodeCryptoFileIV(crypto.generateCryptoFileIV());
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
        
    
});
