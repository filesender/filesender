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
    var page = $('.user_page');
    if(!page.length) return;

    $('.send_client_logs a').button().on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        window.filesender.logger.log('user profile page / send client logs');
        window.filesender.logger.send(
            function(e) {
                filesender.ui.notify('success', lang.tr('client_logs_sent'));
            });
        
        return false;
    });

    $('.clear_client_logs a').button().on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        window.filesender.logger.clear();
        window.filesender.logger.log('user profile page / clear client logs');
        filesender.ui.notify('success', lang.tr('client_logs_cleared'));
        
        return false;
    });
    

    $('.delete_my_account a').button().on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        var redirect_url = filesender.config.logoff_url;
        filesender.ui.confirm(lang.tr('confirm_delete_my_account'), function() {
            filesender.client.deleteUserAccount('@me', function() {
                filesender.ui.alert('success',
                                    lang.tr('user_deleted'),
                                    function() { filesender.ui.redirect(redirect_url) });

            });
        });
        
        return false;
    });

    $('.api_secret_delete a').button().on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        var p = {};
        p['apisecretdelete'] = '1';
        
        filesender.client.updateUserPreferences(p, function() {
            filesender.ui.notify('success', lang.tr('preferences_updated'));
            filesender.ui.reload();
        });
        
        return false;
    });


    $('.api_secret_create a').button().on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        var secret_create = function() {
            var p = {};
            p['apisecretcreate'] = '1';
            
            filesender.client.updateUserPreferences(p, function() {
                filesender.ui.notify('success', lang.tr('preferences_updated'));
                filesender.ui.reload();
            });
        };

        if( filesender.config.api_secret_aup_enabled ) {
            filesender.ui.confirm(lang.tr('confirm_api_secret_create_aup'), function() {
                secret_create();
            });
        } else {
            secret_create();
        }
        
        return false;
    });
    
    
    page.find(':input').on('change', function() {
        var i = $(this);
        var name = i.attr('name');
        if(name.substr(0, 5) != 'user_') return;
        name = name.substr(5);
        
        var p = {};
        p[name] = i.val();
        
        filesender.client.updateUserPreferences(p, function() {
            filesender.ui.notify('success', lang.tr('preferences_updated'));
        });
    });

    var user_lang = page.find('select[name="user_lang"]');
    if( user_lang.length ) {
        user_lang.on('change', function() {
            var i = $(this);
            var menu_language_selector = $('#language_selector');
            if( menu_language_selector ) {
                menu_language_selector.val( i.val() );
            }
        });
    }
    
    var rc = page.find('span[data-info="remote_config"]');
    if(rc.length) $('<button />').text(lang.tr('get_full_user_remote_config')).button().on('click', function() {
        var p = filesender.ui.popup(
            lang.tr('copy_text'),
            {close: null},
            {width: $('#wrap').width()}
        );
        var t = $('<textarea class="wide" />').val(rc.html()).appendTo(p);
        t.focus().select();
    }).insertAfter(rc);
    
    var rasr = page.find('[data-remote-auth-sync-request]');
    if(rasr.length) {
        filesender.ui.alert('info', lang.tr('remote_auth_sync_request').r({
            remote: rasr.text(),
            code: rasr.attr('data-remote-auth-sync-request')
        }), function() {
            filesender.ui.goToPage('home');
        });
    }
});
