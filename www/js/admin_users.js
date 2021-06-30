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
    var section = $('#page.admin_page .users_section');
    if(!section.length) return;

    var go = section.find('.search [name="go"]').prop('disabled', true);
    var match = section.find('.search [name="match"]');
    var results = section.find('.results');
    var logs = section.find('.client-logs');
    var email = section.find('input[name="createusername"]');
    var pass  = section.find('input[name="createuserpassword"]');

    var clean_logs = function() {
        logs.find('.log').remove();
    };

    var add_log = function(log) {
        var l = logs.find('.tpl').clone().removeClass('tpl').addClass('log');
        l.attr({'data-id': log.id});
        l.find('.date').text(log.created.formatted);
        l.find('.message').text(log.message);
        l.appendTo(logs);
    };

    var show_logs = function(id) {
        logs.show().removeClass('no_results').addClass('searching');
        filesender.client.get('/clientlogs/' + id, function(found) {
            clean_logs();

            logs.toggleClass('no_results', !found.length);

            for(var i=found.length-1; i>=0; i--)
                add_log(found[i]);

            logs.removeClass('searching');
        });
    };

    var clean_users = function() {
        results.find('.user').remove();
    };

    var add_user = function(user) {
        var u = results.find('.tpl').clone().removeClass('tpl').addClass('user');
        u.attr({'data-id': user.id});
        u.attr({'data-saml-id': user.saml_id});
        u.find('.id').text(user.id);
        u.find('.saml_id').text(user.saml_id);
        u.find('.last_activity').text(user.last_activity.formatted);
        u.find('.event_count').text(user.eventcount);
        u.find('.ip').text(user.eventip);
        u.appendTo(results);
        u.find('[data-action="show-transfers"]').on('click', function() {
            var id = $(this).closest('.user').attr('data-id');
            filesender.ui.redirect( filesender.config.base_path
                                    + '?s=transfers'
                                    + '&uid=' + id );
            
        });
        
        u.find('[data-action="show-client-logs"]').on('click', function() {
            var id = $(this).closest('.user').attr('data-id');
            show_logs(id);
        });
        u.find('[data-action="delete-api-secret"]').on('click', function() {
            var id = $(this).closest('.user').attr('data-id');

            var p = {};
            p['apisecretdelete'] = '1';
        
            filesender.client.updateUserIDPreferences( id, p, function() {
                filesender.ui.notify('success', lang.tr('preferences_updated'));
            });
        });
        u.find('[data-action="set-local-authdb-password"]').on('click', function() {
            var id      = $(this).closest('.user').attr('data-id');
            var saml_id = $(this).closest('.user').attr('data-saml-id');

            filesender.client.changeLocalAuthDBPassword( saml_id );
        });
        u.find('[data-action="set-default-guest-expires"]').on('click', function() {
            var id = $(this).closest('.user').attr('data-id');
            filesender.client.setUserSpecificExpireDaysForNewGuesst(
                id, function() {
                    filesender.ui.notify('success', lang.tr('preferences_updated'));
                });
        });
    };

    section.find('[data-action="all-delete-api-secret"]').on('click', function() {
        var p = {};
        p['apisecretdelete'] = '1';
        filesender.ui.confirm(lang.tr('confirm_api_secret_delete_all'), function() {
            filesender.client.updateUserIDPreferences( '@all', p, function() {
                filesender.ui.notify('success', lang.tr('preferences_updated'));
            });
        });
    });

    section.find('[data-action="create-user"]').on('click', function() {
        filesender.client.createLocalDBAuthUser( email.val(), pass.val(), function() {
            filesender.ui.notify('success', lang.tr('user_created'));
            filesender.client.remindLocalAuthDBPassword(email.val(), pass.val(), function() {} );
        });
    });
    
    var search_user = function() {
        results.removeClass('no_results').addClass('searching');
        filesender.client.get('/user', function(matches) {
            clean_users();

            results.toggleClass('no_results', !matches.length);

            for(var i=0; i<matches.length; i++)
                add_user(matches[i]);

            results.removeClass('searching');
        }, {
            args: {match: match.val()}
        });
    };

    var eval_go = function() {
        go.prop('disabled', match.val().length < 3)
    };

    match.on('change, input', function() {
        eval_go();
    }).on('keyup', function(e) {
        if(e.keyCode === 13 && $(this).val().length >= 3)
            search_user();
    });

    go.on('click', function() {
        search_user();
    });

    var search_potential_abuse = function( key,keyc,ttype,since ) {
        results.removeClass('no_results').addClass('searching');

        filesender.client.get('/user', function(matches) {
            clean_users();

            results.toggleClass('no_results', !matches.length);

            for(var i=0; i<matches.length; i++)
                add_user(matches[i]);

            results.removeClass('searching');
        }, {
            args: {hitlimit: key, hitlimitbycount: keyc, ttype: ttype, since:since }
        });
    }

    var search_decryptfailed = function( since ) {
        results.removeClass('no_results').addClass('searching');

        filesender.client.get('/user', function(matches) {
            clean_users();

            results.toggleClass('no_results', !matches.length);

            for(var i=0; i<matches.length; i++)
                add_user(matches[i]);

            results.removeClass('searching');
        }, {
            args: { decryptfailed:true, since:since }
        });
    }

    
    section.find('.search [class="ab_hit_create_total_limit"]').on('click', function() {
        search_potential_abuse('guest_created_lh','','User',$(this).attr('data-since'));
    });
    
    section.find('.search [class="ab_hit_create_rate_limit"]').on('click', function() {
        search_potential_abuse('guest_created_rate_lh','','User',$(this).attr('data-since'));
    });
    section.find('.search [class="ab_hit_remind_rate_limit"]').on('click', function() {
        search_potential_abuse('guest_remind_rate_lh','','Guest',$(this).attr('data-since'));
    });
    section.find('.search [class="ab_guests_no_file"]').on('click', function() {
        search_potential_abuse('','guest_closed_unused','User',$(this).attr('data-since'));
    });
    section.find('.search [class="ab_guests_del"]').on('click', function() {
        search_potential_abuse('','guest_closed','User',$(this).attr('data-since'));
    });
    section.find('.search [class="ab_decryptfailed"]').on('click', function() {
        search_decryptfailed($(this).attr('data-since'));
    });


    
    eval_go();
});
