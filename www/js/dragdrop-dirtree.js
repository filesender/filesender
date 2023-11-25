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

// Recursive drag-n-drop files
filesender.dragdrop = {

    recurseTree: function(item, path) {
        path = path || "";
        if (item.isFile) {
            item.file(function(file) {
              filesender.ui.files.addFile(path + file.name, file);
            });
        }
        else if (item.isDirectory) {
            // Get folder contents
            let dirReader = item.createReader();

            // In chrome you have to keep calling readEntries() until zero are returned
            // if you want to get them all.
            var handleitems = function(entries) {
                if( !entries.length ) {
                    return;
                }
                for (let i=0; i<entries.length; i++) {
                    filesender.dragdrop.recurseTree(entries[i], path + item.name + "/");
                }
                dirReader.readEntries( handleitems );
            };
                
            dirReader.readEntries(handleitems);
        }
    },

    addTree: function(dataTransfer) {
        if(typeof dataTransfer.items !== "object") return false;

        let items = dataTransfer.items;
        
        if(!items.length) return false;
        if(typeof items[0].webkitGetAsEntry !== "function") return false;
        
        for (let i=0; i<items.length; i++) {
            // webkitGetAsEntry enables the recursive dirtree magic
            let tree = items[i].webkitGetAsEntry();
            if (tree) {
                filesender.dragdrop.recurseTree(tree);
            }
        }
        window.setTimeout(
            function() { filesender.ui.evalUploadEnabled(); },
            100 );

        // calling this directly doesn't seem to sort on Firefox/Linux 2018
        window.setTimeout(
            function() { filesender.ui.files.sortErrorLinesToTop(); },
            1000 );
        
        return true;
    },
};
