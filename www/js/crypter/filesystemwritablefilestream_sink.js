// JavaScript Document

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2023, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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


if (typeof window === 'undefined')
    window = {}; // dummy window for use in webworkers

if (!('filesender' in window))
    window.filesender = {};

window.filesender.filesystemwritablefilestream_sink = function ( arg_name, arg_expected_size, arg_callbackError ) {
    return {
        filename:       arg_name,
        expected_size:  arg_expected_size,
        fileStream:     null,
        writer:         null,
        complete:       false,
        // keep a tally of bytes processed to make sure we get everything.
        bytesProcessed: 0,
        callbackError:  arg_callbackError,
        name: function() { return "FileSystemWritableFileStream"; },
        
        init: async function() {
            var $this = this;
            console.log("FileSystemWritableFileStream init (top)");
            
            if( !$this.fileStream ) {
                $this.fileStream = await window.showSaveFilePicker({
                    suggestedName: this.filename,
                    startIn: 'downloads',
                });
            }
            if( !$this.writer ) {
                $this.writer = await $this.fileStream.createWritable();
            }

            console.log("FileSystemWritableFileStream init (bottom)");
        },
        error: function(error) {
        },
        visit: async function(chunkid,decryptedData) {
            var $this = this;
            await $this.init();
            
            window.filesender.log("FileSystemWritableFileStream visit chunkid " + chunkid + "  data.len " + decryptedData.length );
            await $this.writer.write( decryptedData );            
        },
        done: async function() {
            var $this = this;
            window.filesender.log("FileSystemWritableFileStream done()");
            await $this.writer.close();            
            $this.complete = true;
        }
    }
};



