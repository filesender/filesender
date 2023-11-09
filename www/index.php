<?php

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
 *  notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *  notice, this list of conditions and the following disclaimer in the
 *  documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *  names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.
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

try {
    require_once('../includes/init.php');

    Logger::setProcess(ProcessTypes::GUI);

    try { // At that point we can render exceptions using nice html
        $pre_header_template_exception = null;
        try {
            Auth::isAuthenticated(); // Preload auth state
            // Security that applies to all page requests
            Security::addHTTPHeaders();

            // if configured, ensure no nasty CSRF is going on
            Security::validateAgainstCSRF();
        } catch (Exception $e) {
            // Catch any of these exceptions so we can still print
            // the page header template before throwing the exception
            $pre_header_template_exception = $e;
        }
        Template::display('!!header');

        if ($pre_header_template_exception !== null) {
            // Throw any exception that occurred before the page header
            throw $pre_header_template_exception;
        }

        $page = GUI::currentPage();
        $vars = array();

        if(!GUI::isUserAllowedToAccessPage($page)) {
            if(Auth::isAuthenticated())
                throw new GUIAccessForbiddenException($page);

            GUI::currentPage('logon');
            $vars['access_forbidden'] = true;

            if(Config::get('auth_sp_autotrigger')) AuthSP::trigger();
        }

        // Service level AUP
        if( Config::get('service_aup_min_required_version') > 0 ) {

            $principal = Auth::getPrincipal();

            // If the user just "got a link" they are neither a guest
            // or a user of the system so we can't really ask them to
            // accept a service wide AUP because they have no account
            // to record that information into.
            if( $principal &&
                $principal->service_aup_accepted_version < Config::get('service_aup_min_required_version'))
            {
                // new service wide AUP to accept for this user/guest
                GUI::currentPage('service_aup');
            }
        }

        if(!in_array($page, array('maintenance'))) {
            Template::display('menu');
        }

        Template::display('page', array('vars' => $vars));

    } catch(Exception $e) {
        Template::display('exception', array('exception' => $e));
    }

    if(!in_array($page, array('maintenance'))) {
        Template::display('!!footer');
    }

} catch(Exception $e) {
    // If all exceptions are catched as expected we should not get there
    die('An exception happened : '.$e->getMessage());
}
