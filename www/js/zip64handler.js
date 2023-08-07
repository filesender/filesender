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

///////
// raw functions to write data of specific size
// to Streams

    var sswrite = async function (writer,data) {
        await writer.write(data);
    }

    var sswritestr = async function (writer,data) {
        await writer.write(window.filesender.crypto_common().convertStringToArrayBufferView(data));
    }

    var sswriteu64 = async function (writer,n) {
        var lob = n & 0xFFFFFFFF;
        var hib = n - lob;
        await writer.write(new Uint8Array([(lob)&0xFF,(lob>>8)&0xFF,(lob>>16)&0xFF,(lob>>24)&0xFF,
                                           (hib)&0xFF,(hib>>8)&0xFF,(hib>>16)&0xFF,(hib>>24)&0xFF]));
    }
    var sswriteu32 = async function (writer,n) {
        await writer.write(new Uint8Array([(n)&0xFF,(n>>8)&0xFF,(n>>16)&0xFF,(n>>24)&0xFF]));
    }
    var sswriteu16 = async function (writer,n) {
        await writer.write(new Uint8Array([(n)&0xFF,(n>>8)&0xFF]));
    }

///////


window.filesender.zip64handler = function() {

    return {
        filename: '',
        writer: null,
        bytesProcessed: 0,
        VERSION: 45,
        crc: 0,
        filesize: 0,
        coffset: 0,
        zip64_start_of_central_directory_record_offset: 0,
        zip64_end_of_central_directory_record_offset: 0,
        zip64_end_of_central_directory_record_offset2: 22,
        zip64_central_directory_record_length: 0,
        files: [],
        fileStream: null,
        
        init: async function( filename ) {
            console.log("zip64 init (top)");
            
            var $this = this;
            this.filename = filename;
            this.coffset = 0;

            window.filesender.log('zip64 handler newer streaming API information.'
                                  + ' Use streamsaver: ' + window.filesender.config.use_streamsaver
                                  + ' use FileSystemWritableFileStream (FSWF) ' + window.filesender.config.useFileSystemWritableFileStreamForDownload());
            if( window.filesender.config.useFileSystemWritableFileStreamForDownload()) {
                
                if( !$this.fileStream ) {
                    console.log("FileSystemWritableFileStream zinit (p1)");
                    $this.fileStream = await window.showSaveFilePicker({
                        suggestedName: this.filename,
                        startIn: 'downloads',
                    });
                    console.log("FileSystemWritableFileStream zinit (p2)");
                }
                if( !$this.writer ) {
                    $this.writer = await $this.fileStream.createWritable();
                }
                console.log("FileSystemWritableFileStream zip64 zinit (end)");
                
            } else if( window.filesender.config.use_streamsaver ) {
                const ponyfill = window.WebStreamsPolyfill || {};
                streamSaver.WritableStream = ponyfill.WritableStream;
                streamSaver.mitm = window.filesender.config.streamsaver_mitm_url;
                streamSaver.WritableStream = ponyfill.WritableStream;

                streamSaver.mitm = window.filesender.config.streamsaver_mitm_url;
                var fileStream = streamSaver.createWriteStream( filename );
                $this.writer = fileStream.getWriter();
            }            


            $this.files = [];
        },

        write: function (data) {
            var $this = this;
            sswrite( $this.writer, data );
            this.coffset += data.length;
            return this.coffset;
        },

        writestr: function (data) {
            var $this = this;
            sswritestr( $this.writer, data );
            this.coffset += data.length;
            return this.coffset;
        },
        
        writeu64: function (n) {
            var $this = this;
            sswriteu64( $this.writer, n );
            this.coffset += 8;
            return this.coffset;
        },
        writeu32: function (n) {
            var $this = this;
            sswriteu32( $this.writer, n );
            this.coffset += 4;
            return this.coffset;
        },
        writeu16: function (n) {
            var $this = this;
            sswriteu16( $this.writer, n );
            this.coffset += 2;
            return this.coffset;
        },

        
        // @param d is javascript Date object.
        // @return 32bit dostime integer
	dostime: function(d) {

            var y = d.getFullYear();
            if( y < 1980 ) {
                d.setFullYear(1980);
                d.setMonth(0);
                d.setDate(0);
            }
            return(
                (d.getFullYear()-1980)<<25
                    | d.getMonth()   << 21
                    | d.getDate()    << 16
                    | d.getHours()   << 11
                    | d.getMinutes() << 5
                    | d.getSeconds() / 2
            );
        },
        openFile: function(filename) {
            var $this = this;

            var filename_array = window.filesender.crypto_common().convertStringToArrayBufferView(filename);

            var genb = 0x0808;
            var dts = $this.dostime(new Date());

            var local_file_header_offset = $this.coffset;
            // V = 32, v = 16
            this.writeu32( 0x04034b50 );  // magic number for this header
            this.writeu16( $this.VERSION );
            this.writeu16( genb       );  // utf8 and crc and size in data descriptor following the data
            this.writeu16( 0          );  // no compression
            this.writeu32( dts );
            this.writeu32( 0          );  // no crc-32 yet
            this.writeu32( 0xFFFFFFFF );  // no size yet
            this.writeu32( 0xFFFFFFFF );  // ...
            this.writeu16( filename_array.length );
            this.writeu16( 0          );  // no extra data

            $this.write( filename_array );

            $this.crc = 0;
            $this.filesize = 0;

            $this.files.push( { name: filename, filename: filename, size: 0,
                                genb: genb, dts: dts, crc: 0, local_file_header_offset: local_file_header_offset } );

        },
        visit: function(data) {
            var $this = this;
            $this.write( data );
            $this.crc = window.filesender.crc32handler( $this.crc, data, data.length, 0 );
            $this.filesize += data.length;
        },
        closeFile: function() {
            var $this = this;

            $this.writeu32( 0x08074b50 );  // magic number for this header
            $this.writeu32( $this.crc );
            $this.writeu64( $this.filesize );
            $this.writeu64( $this.filesize );

            // update the size and crc info from the bytes written
            this.files[this.files.length-1].crc  = $this.crc;
            this.files[this.files.length-1].size = $this.filesize;
        },
        add_cdr_file: function(f) {
            var $this = this;

            var filename = window.filesender.crypto_common().convertStringToArrayBufferView(f.name);

            var startOffset = this.coffset;
            $this.writeu32( 0x02014b50 );  // magic number for this header
            $this.writeu16( $this.VERSION );  
            $this.writeu16( $this.VERSION );  
            $this.writeu16( f.genb );  
            $this.writeu16( 0 );           // compression
            $this.writeu32( f.dts );       // dos time stamp
            $this.writeu32( f.crc );
            $this.writeu32( 0xFFFFFFFF );
            $this.writeu32( 0xFFFFFFFF );
            $this.writeu16( filename.length );
            $this.writeu16( 32 );          // extra data
            $this.writeu16( 0 );           // comment
            $this.writeu16( 0 );          
            $this.writeu16( 0 );          
            $this.writeu32( 0x20 );          
            $this.writeu32( 0xFFFFFFFF );  // relative offset of local header (zip64 - look in extra)

            

            $this.write( filename );

            // extra data
            $this.writeu16( 1 ); 
            $this.writeu16( 28 ); 
            $this.writeu64( f.size ); 
            $this.writeu64( f.size ); 
            $this.writeu64( f.local_file_header_offset ); 
            $this.writeu32( 0 ); 

            
            var endOffset = this.coffset;
            this.zip64_central_directory_record_length += (endOffset-startOffset);
        },
	add_cdr_eof_zip64: function() {
            var $this = this;

            var entryCount = this.files.length;
            var cdr_ofs = 67;

            $this.zip64_end_of_central_directory_record_offset = $this.coffset;
            $this.writeu32( 0x06064b50 );  // magic number for this header
            $this.writeu32( 44         );  // size
            $this.writeu32( 0          );  // size hb
            $this.writeu16( $this.VERSION );  
            $this.writeu16( $this.VERSION );  
            $this.writeu32( 0          );  
            $this.writeu32( 0          );  
            $this.writeu64( entryCount );  
            $this.writeu64( entryCount );  
            $this.writeu64( $this.zip64_central_directory_record_length );  
            $this.writeu64( $this.zip64_start_of_central_directory_record_offset );  


        },
	add_cdr_eof_locator_zip64: function() {
            var $this = this;

            $this.writeu32( 0x07064b50 );  // magic number for this header
            $this.writeu32( 0 );           // this disk number
            $this.writeu64( $this.zip64_end_of_central_directory_record_offset );
            $this.writeu32( 1 );           // number of disks
        },
        write_end_cdr_record: function() {
            var $this = this;

            var comment = 'created by FileSender';

            $this.writeu32( 0x06054b50 );  // magic number for this header
            $this.writeu16( 0xFFFF );
            $this.writeu16( 0xFFFF );
            $this.writeu16( 0xFFFF );
            $this.writeu16( 0xFFFF );
            $this.writeu32( 0xFFFFFFFF );
            $this.writeu32( 0xFFFFFFFF );
            $this.writeu16( comment.length );
            $this.writestr( comment );
        },
        complete: async function() {
            var $this = this;

            console.log("ziphandler complete()!");
            $this.zip64_start_of_central_directory_record_offset = $this.coffset;
            for (let i = 0; i < this.files.length; i++) {
                $this.add_cdr_file(this.files[i])
            }            

	    $this.add_cdr_eof_zip64();
	    $this.add_cdr_eof_locator_zip64();
            $this.write_end_cdr_record();
            console.log("ziphandler complete() closing writer");
            await $this.writer.close();
            console.log("ziphandler complete() closed writer");
        },
        abort: function() {
            var $this = this;
            $this.writer.abort();
        },
        lastfunc: function() {
            var $this = this;
        }
    }
};

        
