/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * *    Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *     names of its contributors may be used to endorse or promote products
 *     derived from this software without specific prior written permission.
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

import * as asmCrypto from './asmcrypto.all.es5.js';

if(!('filesender' in window)) window.filesender = {};

/**
 * AJAX webservice client
 */
window.filesender.asmcrypto = function() { return {

    test: function(password) {

        var salt = '1234';
        var iterations = 150000;
        var dklen = 32;

        console.log(window.filesender.asmcryptoR);
        
        const encoder = new TextEncoder();
        var k = window.filesender.asmcryptoR.Pbkdf2HmacSha256( encoder.encode("password"),
                                            window.filesender.crypto_common().convertStringToArrayBufferView(salt),
                                            iterations, dklen );
        console.log(k);
//        console.log(k.buffer);
        
        return password;
    },

    importKeyFromPasswordUsingPBKDF2: function(iv,password, salt, iterations, successcb, failcb ) {

        var dklen = 32;

        console.log('calling key derivation function.');
        console.log(asmCrypto);

        var t0 = performance.now();            
        var k = window.filesender.asmcryptoR.Pbkdf2HmacSha256( password, salt, iterations, dklen );
        var t1 = performance.now();            
        console.log('DONE calling key derivation function.');
        console.log('that took ' + Number(t1-t0).toLocaleString() + ' ms');
        
        crypto.subtle.importKey("raw", k.buffer,
                                { "name": "AES-CBC", "length": 256},
                                true,
                                ["encrypt", "decrypt"]
                               ).then( function (key)
                                       {
                                           successcb(key,iv);
                                       },
                                       function (e) {
                                           console.log('ERROR   -----   ERROR -----   e1 ' + e );
                                           failcb(e);
                                       }
                                     );
    
    },
    
}};
