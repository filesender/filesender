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

    const copyToClipboard = (value) => {
        navigator.clipboard.writeText(value).then((x) => {
            filesender.ui.notify('info', 'Copied to clipboard!');
        }).catch((e) => {
            console.error(e);
            filesender.ui.notify('error', 'Error copying to clipboard!');
        });
    }

    $('#send_client_logs').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        window.filesender.logger.log('user profile page / send client logs');
        window.filesender.logger.send(
            function(e) {
                filesender.ui.notify('success', lang.tr('client_logs_sent'));
            });

        return false;
    });

    $('#export_client_logs').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        window.filesender.logger.log('user profile page / export client logs');
        window.filesender.logger.export();

        return false;
    });

    $('#clear_client_logs').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        window.filesender.logger.clear();
        window.filesender.logger.log('user profile page / clear client logs');
        filesender.ui.notify('success', lang.tr('client_logs_cleared'));

        return false;
    });

    $('#clear_frequent_recipients').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        var p = {};
        p['clear_frequent_recipients'] = '1';

        filesender.client.updateUserPreferences(p, function() {
            filesender.ui.notify('success', lang.tr('database_updated'));
            filesender.ui.reload();
        });

        return false;
    });

    $('#clear_user_transfer_preferences').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        var p = {};
        p['clear_user_transfer_preferences'] = '1';

        filesender.client.updateUserPreferences(p, function() {
            filesender.ui.notify('success', lang.tr('database_updated'));
            filesender.ui.reload();
        });

        return false;
    });

    $('#delete_my_account').on('click', function(e) {
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

    $('#api_secret_delete').on('click', function(e) {
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

    $('#api_secret_create').on('click', function(e) {
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

    $('#change_password').on('click', function(e) {
        var saml_id = '@me';
        filesender.client.changeLocalAuthDBPassword( saml_id );
    });

    var savePreferences = () => {
        let hasError = false;

        const inputs = $(':input');

        const p = {};
        for (let i = 0; i < inputs.length; i++) {
            const element = $(inputs[i]);

            let name = element.attr('name');
            if (name) {
                if (name.substr(0, 5) == 'user_' || name.substr(0, 5) == 'save_') {
                    if (name.substr(0, 5) == 'user_') {
                        name = name.substr(5);
                        p[name] = element.val();
                    }
                    if (name.substr(0, 5) == 'save_') {
                        p[name] = element.is(':checked');
                    }

                }
            }
        }
        filesender.client.updateUserPreferences(p, function() {
            console.log('success');
        }).catch((e) => {
            hasError = true;
        });


        if (!hasError) {
            filesender.ui.notify('success', lang.tr('preferences_updated'));
            location.reload();
        } else {
            filesender.ui.notify('error', lang.tr('Could not save user preferences.'));
        }
    };

    var user_lang = page.find('select[name="user_lang"]');
    if( user_lang.length ) {
        user_lang.on('change', function() {
            var i = $(this);
            var menu_language_selector = $('#language_selector');
            if( menu_language_selector ) {
                menu_language_selector.val( i.val() );
            }
            savePreferences();
        });
    }

    var rc = page.find('span[data-info="remote_config"]');
    console.log(rc);
    if(rc.length) $('<button class="btn btn-secondary" />').text(lang.tr('get_full_user_remote_config')).button().on('click', function() {
        filesender.ui.wideInfoPopup('copy_text',
                                    $('<textarea class="w-100 wide desctxt" />').val(rc.html()), function() {});
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

    $('#copy-api-secret, #copy-python-command').on('click', function(e) {
        const element = this.parentElement.querySelector('span');
        if (element) {
            const value = element.textContent;
            copyToClipboard(value);
        }
    });

    $('#user_theme, #previous-settings, #save-recipients-emails').on('change', function(e) {
        savePreferences();
    });

    window.filesender.log("window.filesender.log() from user page ");
});
