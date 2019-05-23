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
        Auth::isAuthenticated(); // Preload auth state

        // Security that applies to all page requests
        Security::addHTTPHeaders();

        // if configured, ensure no nasty CSRF is going on
        Security::validateAgainstCSRF();
        
        
        Template::display('!!header');
        
        $page = GUI::currentPage();
        $vars = array();
        
        if(!GUI::isUserAllowedToAccessPage($page)) {
            if(Auth::isAuthenticated())
                throw new GUIAccessForbiddenException($page);
        
            GUI::currentPage('logon');
            $vars['access_forbidden'] = true;
            
            if(Config::get('auth_sp_autotrigger')) AuthSP::trigger();
        }
        
        if(!in_array($page, array('download', 'maintenance')))
            Template::display('menu');
        
        Template::display('page', array('vars' => $vars));
        
    } catch(Exception $e) {
        Template::display('exception', array('exception' => $e));
    }
    
    Template::display('!!footer');
    
} catch(Exception $e) {
    // If all exceptions are catched as expected we should not get there
    die('An exception happened : '.$e->getMessage());
}
