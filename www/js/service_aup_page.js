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
    var page = $('.service_aup_page');
    if(!page.length) return;

    $('.service_aup_accept a').button().on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();


        var aup_version = page.find(".serviceaup").attr('data-service-aup-version');
        window.filesender.log("Principal has accepted service AUP version " + aup_version );

        filesender.client.serviceAUPAccept(aup_version, function() {
            filesender.ui.notify('success', lang.tr('terms_accepted'));

            var url = new URL(location);
            var page = url.searchParams.get("s");
            if( !page ) {
                page = 'upload';
            }
            var args = {};
            if( url.searchParams.get("vid") ) {
                args = {
                    vid: url.searchParams.get("vid")
                };
            }
            if( url.searchParams.get("token") ) {
                args = {
                    token: url.searchParams.get("token")
                };
            }
            filesender.ui.goToPage( page, args, null );
        });
        
        return false;
    });

    window.filesender.log("window.filesender.log() from service aup page ");    
});
