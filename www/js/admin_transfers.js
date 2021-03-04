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
    var section = $('#page.admin_page .transfers_section');
    if(!section.length) return;

    var idbutton   = section.find('.search [name="idbutton"]').prop('disabled', true);
    var idmin      = section.find('.search [name="idmin"]');
    var idmax      = section.find('.search [name="idmax"]');

    var idbuttonse  = section.find('.search [name="idbuttonse"]').prop('disabled', false);
    var senderemail = section.find('.search [name="senderemail"]');
    var senderemail_full_match = section.find('.search [name="senderemail_full_match"]');
    
    var idsearch = function() {
        //alert(idmin.val());
        filesender.ui.redirect( filesender.config.base_path
                                + '?s=admin&as=transfers'
                                + '&idmin=' + idmin.val()
                                + '&idmax=' + idmax.val()
                              );
        
    };

    var ideval = function() {
        idbutton.prop('disabled',
                      idmin.val().length < 1
                      || idmax.val().length < 1
                      || Number(idmax.val()) < Number(idmin.val()));
    };

    idmin.on('change, input', function() {
        ideval();
    }).on('keyup', function(e) {
        if(e.keyCode === 13 && $(this).val().length >= 3)
            idsearch();
    });
    idmax.on('change, input', function() {
        ideval();
    }).on('keyup', function(e) {
        if(e.keyCode === 13 && $(this).val().length >= 3)
            idsearch();
    });

    idbutton.on('click',   function() { idsearch(); });


    var search_senderemail = function() {
        var full_match = senderemail_full_match.is(':checked');
        
        filesender.ui.redirect( filesender.config.base_path
                                + '?s=admin&as=transfers'
                                + '&senderemail=' + senderemail.val()
                                + '&senderemail_full_match=' + full_match
                              );
    }
    
    idbuttonse.on('click', function() { search_senderemail(); } );
    senderemail.keypress(function (e) {
        if (e.which == 13) {
            search_senderemail();
            return false;
        }
    });    
    ideval();
});
